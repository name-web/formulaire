<?php

namespace App\Services\Devis;

use App\Models\Client;
use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Quote;
use App\Services\Questionnaire\PendingSubmissionService;
use App\Services\QuoteService;
use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Brouillon et conversion du nouveau formulaire.
 *
 * Le brouillon reste porté par PendingSubmission, comme pour l'ancien
 * parcours : la conversion finale produit un Client, un Project marqué de la
 * génération du formulaire, et un devis administratif en brouillon contenant
 * le calcul provisoire complet.
 */
class DevisSubmissionService
{
    public function __construct(
        private PendingSubmissionService $pendingSubmissionService,
        private SubmissionService $submissionService,
        private DevisFormService $formService,
        private DevisBuilder $builder,
        private QuoteService $quoteService,
    ) {}

    /**
     * Ouvre un brouillon pour le nouveau parcours.
     *
     * @param  array<string, mixed>  $clientData
     */
    public function createDraft(array $clientData): PendingSubmission
    {
        return $this->pendingSubmissionService->createDraft(
            $clientData,
            [],
            Project::FORM_VERSION_CURRENT,
        );
    }

    public function findPending(string $uuid): ?PendingSubmission
    {
        return $this->pendingSubmissionService->find($uuid);
    }

    /**
     * Réponses du brouillon, complétées par les valeurs par défaut.
     *
     * @return array<string, mixed>
     */
    public function answers(PendingSubmission $pending): array
    {
        $answers = $pending->answers_data ?? [];

        return array_replace($this->formService->defaults(), $answers);
    }

    /**
     * Enregistre les réponses d'une étape en repartant de celles déjà collectées.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function saveStep(PendingSubmission $pending, int $step, array $validated): array
    {
        $answers = $this->mergeStepAnswers($step, $validated, $this->answers($pending));

        $projectData = $pending->project_data ?? [];
        $projectData['project_types'] = $answers['type'] !== '' ? [$answers['type']] : [];

        $pending->update([
            'answers_data' => $answers,
            'project_data' => $projectData,
        ]);

        return $answers;
    }

    /**
     * Fusionne les champs d'une étape dans les réponses du brouillon.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $previous
     * @return array<string, mixed>
     */
    public function mergeStepAnswers(int $step, array $validated, array $previous): array
    {
        $allowed = array_keys($this->formService->defaults());
        $answers = array_replace(
            $previous,
            array_intersect_key($validated, array_flip($allowed)),
        );

        // Les listes de choix sont remplacées à l'étape 3 : décocher doit vider.
        if ($step === DevisFormService::STEP_DETAIL) {
            $answers['opt'] = array_values($validated['opt'] ?? []);
            $answers['modules'] = array_values($validated['modules'] ?? []);
        }

        // La sauvegarde externalisée s'ajoute aux options sans écraser celles du type.
        if ($step === DevisFormService::STEP_HERBERGEMENT) {
            $others = array_values(array_diff($previous['opt'] ?? [], ['sauvegarde']));
            $sauvegarde = in_array('sauvegarde', $validated['opt'] ?? [], true) ? ['sauvegarde'] : [];

            $answers['opt'] = array_values(array_unique(array_merge($others, $sauvegarde)));
        }

        if ($step === DevisFormService::STEP_NIVEAU) {
            $answers['accelere'] = ($validated['delai'] ?? 'normal') === 'accelere';
        }

        return $answers;
    }

    /**
     * Convertit un brouillon complet en client, projet et devis provisoire.
     */
    public function submit(PendingSubmission $pending): Quote
    {
        $quote = DB::transaction(function () use ($pending) {
            $answers = $this->answers($pending);
            $client = $this->submissionService->findOrCreateClient($pending->client_data ?? []);
            $devis = $this->builder->construire($answers);
            $objet = $this->builder->objet($answers['type']);

            $project = Project::create([
                'client_id' => $client->id,
                'title' => $objet,
                'project_type' => [$answers['type']],
                'description' => $answers['message'] ?: null,
                'status' => 'submitted',
                'form_version' => Project::FORM_VERSION_CURRENT,
                'form_data' => $answers,
                'submitted_at' => now(),
            ]);

            $quote = $this->createProvisionalQuote($client, $project, $answers, $devis, $objet);

            $pending->markAsImported();

            Log::info('Nouveau formulaire converti en client, projet et devis provisoire.', [
                'pending_uuid' => $pending->uuid,
                'client_id' => $client->id,
                'project_id' => $project->id,
                'quote_id' => $quote->id,
            ]);

            return $quote;
        });

        return $quote->fresh(['items', 'sections', 'customFields']);
    }

    /**
     * Crée le devis brouillon alimenté par le calcul provisoire.
     *
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>  $devis
     */
    private function createProvisionalQuote(
        Client $client,
        Project $project,
        array $answers,
        array $devis,
        string $objet,
    ): Quote {
        $issuedAt = now();
        $dueAt = $issuedAt->copy()->addDays((int) config('devis.validite_jours'));
        $numberData = $this->quoteService->generateNumber($issuedAt->format('Y'));

        $items = array_map(fn (array $ligne): array => [
            'designation' => $ligne['nom'],
            'description' => $ligne['inclus'],
            'quantity' => $ligne['quantite'],
            'unit_price' => $ligne['prix_unitaire'],
        ], $devis['creation']);

        // La majoration devient une ligne du devis : le montant reste ajustable
        // dans l'administration avec les règles existantes.
        if ($devis['majoration'] > 0) {
            $items[] = [
                'designation' => 'Majoration livraison prioritaire (30 %)',
                'description' => "Mobilisation d'équipe renforcée pour une livraison prioritaire.",
                'quantity' => 1,
                'unit_price' => $devis['majoration'],
            ];
        }

        $prestations = array_map(fn (array $ligne): array => [
            'designation' => $ligne['nom'],
            'detail' => $ligne['inclus'],
        ], $devis['creation']);

        $calculation = $devis + [
            'type' => $answers['type'],
            'objet' => $objet,
            'niveau' => $answers['niveau'],
            'accelere' => (bool) $answers['accelere'],
            'remise_taux' => (float) $answers['remise'],
            'message' => $answers['message'] ?: null,
            'date' => $issuedAt->toIso8601String(),
            'valide_jusqu_au' => $dueAt->toIso8601String(),
        ];

        return $this->quoteService->create($client, [
            'number' => $numberData['number'],
            'project_id' => $project->id,
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'subject' => $objet,
            'description' => $answers['message'] ?: null,
            'prestations' => $prestations,
            'status' => Quote::STATUS_DRAFT,
            'discount' => $devis['remise'],
            'items' => $items,
            'calculation' => $calculation,
        ]);
    }
}
