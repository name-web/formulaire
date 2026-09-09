<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Quote;
use App\Models\QuoteCustomField;
use App\Models\QuoteSection;
use App\Services\QuoteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QuoteController extends Controller
{
    public function __construct(private QuoteService $quoteService) {}

    public function index(Request $request): View
    {
        $query = Quote::with('client');

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $quotes = $query->latest('issued_at')->latest('id')->paginate(10)->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    public function create(Request $request): View
    {
        $client = null;

        if ($request->filled('client_id')) {
            $client = Client::findOrFail($request->client_id);
        }

        $quote = new Quote([
            'issued_at' => now(),
            'subject' => config('quote_template.subject'),
            'description' => config('quote_template.description'),
            'payment_terms' => config('quote_template.payment_terms'),
            'notes' => config('quote_template.notes'),
        ]);

        $statuses = Quote::getStatuses();
        $sectionTypes = QuoteSection::getTypes();
        $fieldTypes = QuoteCustomField::getTypes();

        // Modèle de base (devis.html DOMINIUM) : les nouveaux devis partent de
        // la structure et des textes par défaut, modifiables librement.
        $templateItems = old('items', $quote->exists ? [] : config('quote_template.items'));
        $templateSections = old('sections', $quote->exists ? [] : config('quote_template.sections'));
        $templatePrestations = old('prestations', $quote->exists ? ($quote->prestations ?? []) : config('quote_template.prestations', []));

        return view('admin.quotes.form', compact(
            'client', 'quote', 'statuses', 'sectionTypes', 'fieldTypes', 'templateItems', 'templateSections', 'templatePrestations'
        ));
    }

    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        $client = Client::findOrFail($request->client_id);

        $quote = $this->quoteService->create($client, $this->quoteData($request));

        return redirect()->route('admin.quotes.show', $quote)
            ->with('success', 'Devis créé avec succès.');
    }

    public function show(Quote $quote): View
    {
        $quote->load(['client', 'items', 'sections', 'customFields']);

        return view('admin.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        $quote->load(['client', 'items', 'sections', 'customFields']);

        $statuses = Quote::getStatuses();
        $sectionTypes = QuoteSection::getTypes();
        $fieldTypes = QuoteCustomField::getTypes();

        $templatePrestations = old('prestations', $quote->prestations ?? []);

        return view('admin.quotes.form', compact('quote', 'statuses', 'sectionTypes', 'fieldTypes', 'templatePrestations'));
    }

    public function update(UpdateQuoteRequest $request, Quote $quote): RedirectResponse
    {
        $this->quoteService->update($quote, $this->quoteData($request));

        return redirect()->route('admin.quotes.show', $quote)
            ->with('success', 'Devis mis à jour.');
    }

    public function updateStatus(Request $request, Quote $quote): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Quote::getStatuses()))],
        ]);

        $quote->update(['status' => $request->status]);

        return back()->with('success', 'Statut du devis mis à jour.');
    }

    public function preview(Quote $quote): View
    {
        $quote->load(['client', 'items', 'sections', 'customFields']);
        $company = CompanySetting::get();

        return view('admin.quotes.preview', compact('quote', 'company'));
    }

    public function pdf(Quote $quote): Response
    {
        $quote->load(['client', 'items', 'sections', 'customFields']);
        $company = CompanySetting::get();

        $pdf = Pdf::setPaper('a4', 'portrait')->loadView('admin.quotes.pdf', [
            'quote' => $quote,
            'company' => $company,
        ]);

        $dompdf = $pdf->getDomPDF();

        $dompdf->getOptions()->setChroot([
            public_path(),
            storage_path(),
        ]);

        $dompdf->render();

        $this->stampPaginationFooter($dompdf, $quote, $company);

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="devis-'.$quote->number.'.pdf"',
        ]);
    }

    /**
     * Ajoute le pied de page répété sur chaque page (identité, numéro de
     * devis, pagination) via l'API canvas de Dompdf.
     */
    private function stampPaginationFooter(Dompdf $dompdf, Quote $quote, CompanySetting $company): void
    {
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        $font = 'helvetica';
        $size = 9;
        $color = [0.53, 0.53, 0.53];
        $width = $canvas->get_width();

        // Zones fixes en bas de page (coordonnées depuis le haut) :
        // la marge basse de 26mm garantit qu'aucun contenu ne les chevauche.
        $ruleY = $canvas->get_height() - 62;
        $identityY = $canvas->get_height() - 50;
        $footerY = $canvas->get_height() - 39;
        $pagingY = $canvas->get_height() - 27;

        $canvas->page_line(40, $ruleY, $width - 40, $ruleY, [0.93, 0.93, 0.93], 0.5);

        $identity = collect([$company->name, $company->city, $company->country])->filter()->implode(' — ');
        $identityWidth = $fontMetrics->getTextWidth($identity, $font, $size);
        $canvas->page_text(($width - $identityWidth) / 2, $identityY, $identity, $font, $size, $color);

        if (filled($company->footer)) {
            $footerWidth = $fontMetrics->getTextWidth($company->footer, $font, $size);
            $canvas->page_text(($width - $footerWidth) / 2, $footerY, $company->footer, $font, $size, $color);
        }

        $reference = 'Devis N° '.$quote->number.' — Page {PAGE_NUM}/{PAGE_COUNT}';
        $referenceWidth = $fontMetrics->getTextWidth($reference, $font, $size);
        $canvas->page_text(($width - $referenceWidth) / 2, $pagingY, $reference, $font, $size, $color);
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->route('admin.quotes.index')->with('success', 'Devis supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function quoteData(StoreQuoteRequest $request): array
    {
        return [
            'number' => $request->input('number'),
            'client_id' => (int) $request->input('client_id'),
            'issued_at' => $request->date('issued_at'),
            'due_at' => $request->date('due_at'),
            'subject' => $request->input('subject'),
            'description' => $request->input('description'),
            'payment_terms' => $request->input('payment_terms'),
            'notes' => $request->input('notes'),
            'status' => $request->input('status', Quote::STATUS_DRAFT),
            'discount' => (int) $request->input('discount', 0),
            'items' => $request->input('items', []),
            'sections' => $request->input('sections', []),
            'custom_fields' => $request->input('custom_fields', []),
            'prestations' => $request->input('prestations', []),
        ];
    }
}
