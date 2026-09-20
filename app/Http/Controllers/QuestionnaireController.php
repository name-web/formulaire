<?php

namespace App\Http\Controllers;

use App\Models\PendingSubmission;
use App\Models\Quote;
use App\Services\Devis\DevisFormService;
use App\Services\Devis\DevisSubmissionService;
use App\Services\Geo\IpGeolocationService;
use App\Services\Phone\PhoneNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Parcours client du nouveau formulaire de devis.
 *
 * Cinq étapes : coordonnées, type de projet, détail du besoin, hébergement et
 * suivi, niveau et délai. Le brouillon est porté par PendingSubmission ; la
 * dernière étape convertit le brouillon en client, projet et devis provisoire.
 */
class QuestionnaireController extends Controller
{
    private const SESSION_PENDING = 'devis_pending_uuid';

    private const SESSION_QUOTE = 'devis_quote_id';

    public function __construct(
        private DevisFormService $formService,
        private DevisSubmissionService $submissionService,
    ) {}

    public function index(): View
    {
        return view('questionnaire.index', $this->stepViewData(DevisFormService::STEP_COORDONNEES, $this->currentAnswers()));
    }

    public function start(Request $request, IpGeolocationService $geoService): RedirectResponse
    {
        $validated = $request->validate(
            $this->formService->rulesForStep(DevisFormService::STEP_COORDONNEES),
            $this->formService->messages(),
        );

        $clientData = [
            'name' => $validated['nom'],
            'company_name' => $validated['entreprise'] ?? null,
            'city' => $validated['ville'] ?? null,
            'phone' => $this->normalizePhone($validated['tel']),
            'email' => $validated['email'],
            'country' => $geoService->getCountry($request->ip()),
            'ip_address' => $request->ip(),
        ];

        $pending = $this->submissionService->createDraft($clientData);
        $this->submissionService->saveStep($pending, DevisFormService::STEP_COORDONNEES, $validated);

        session([
            self::SESSION_PENDING => $pending->uuid,
            self::SESSION_QUOTE => null,
        ]);

        return redirect()->route('questionnaire.step', DevisFormService::STEP_PROJET);
    }

    public function show(int $step): View|RedirectResponse
    {
        $redirect = $this->guardStep($step);

        if ($redirect instanceof RedirectResponse) {
            return $redirect;
        }

        return view('questionnaire.step', $this->stepViewData($step, $this->currentAnswers()));
    }

    public function store(Request $request, int $step): RedirectResponse
    {
        $pending = $this->pending();
        abort_unless($pending, 404);

        $redirect = $this->guardStep($step, $pending);

        if ($redirect instanceof RedirectResponse) {
            return $redirect;
        }

        $answers = $this->submissionService->answers($pending);

        $validated = $request->validate(
            $this->formService->rulesForStep($step, $answers),
            $this->formService->messages(),
        );

        $this->submissionService->saveStep($pending, $step, $validated);

        if ($step < DevisFormService::TOTAL_STEPS) {
            return redirect()->route('questionnaire.step', $step + 1);
        }

        $quote = $this->submissionService->submit($pending);

        session([
            self::SESSION_PENDING => null,
            self::SESSION_QUOTE => $quote->id,
        ]);

        return redirect()->route('questionnaire.quote');
    }

    public function quote(): View|RedirectResponse
    {
        $quote = $this->currentQuote();

        if (! $quote) {
            return redirect()->route('questionnaire.index');
        }

        return view('questionnaire.quote', [
            'quote' => $quote,
            'calculation' => $quote->calculation ?? [],
            'client' => $quote->client,
            'company' => config('devis'),
        ]);
    }

    public function restart(): RedirectResponse
    {
        session()->forget([self::SESSION_PENDING, self::SESSION_QUOTE]);

        return redirect()->route('questionnaire.index');
    }

    /**
     * Empêche d'accéder à une étape sans brouillon ou sans type de projet.
     */
    private function guardStep(int $step, ?PendingSubmission $pending = null): ?RedirectResponse
    {
        if ($step < 1 || $step > DevisFormService::TOTAL_STEPS) {
            return redirect()->route('questionnaire.index');
        }

        $pending ??= $this->pending();

        if (! $pending) {
            return redirect()->route('questionnaire.index');
        }

        if ($step > DevisFormService::STEP_PROJET) {
            $answers = $this->submissionService->answers($pending);

            if ($answers['type'] === '') {
                return redirect()->route('questionnaire.step', DevisFormService::STEP_PROJET);
            }
        }

        return null;
    }

    /**
     * Données partagées par les vues d'étape.
     *
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    private function stepViewData(int $step, array $answers): array
    {
        $variant = $this->formService->variantFor($answers['type']);

        return [
            'step' => $step,
            'totalSteps' => DevisFormService::TOTAL_STEPS,
            'stepTitle' => $this->formService->stepLabel($step),
            'steps' => $this->formService->steps(),
            'answers' => $answers,
            'variant' => $variant,
            'projectTypes' => config('project_types.types'),
            'formTypes' => $this->formService->types(),
            'choices' => config('devis_form'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function currentAnswers(): array
    {
        $pending = $this->pending();

        return $pending
            ? $this->submissionService->answers($pending)
            : $this->formService->defaults();
    }

    private function pending(): ?PendingSubmission
    {
        $uuid = session(self::SESSION_PENDING);

        return $uuid ? $this->submissionService->findPending($uuid) : null;
    }

    private function currentQuote(): ?Quote
    {
        $id = session(self::SESSION_QUOTE);

        return $id ? Quote::with('client')->find($id) : null;
    }

    /**
     * Numéro joignable normalisé au format international.
     *
     * La Côte d'Ivoire sert de pays par défaut, comme le prévoyait l'ancien
     * parcours : le rapprochement des prospects (même numéro = même fiche)
     * repose sur une écriture unique du numéro.
     */
    private function normalizePhone(string $phone): string
    {
        $service = app(PhoneNumberService::class);

        // Un numéro écrit au format international est accepté tel quel, quel
        // que soit son pays ; les autres sont interprétés comme des numéros
        // ivoiriens (marché principal de DOMINIUM TECH).
        $normalized = str_starts_with(ltrim($phone), '+')
            ? ($service->normalizeToE164AnyRegion($phone) ?? $service->normalizeToE164($phone, 'CI'))
            : $service->normalizeToE164($phone, 'CI');

        if ($normalized === null) {
            throw ValidationException::withMessages([
                'tel' => "Le numéro de téléphone n'est pas valide.",
            ]);
        }

        return $normalized;
    }
}
