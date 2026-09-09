<?php

namespace App\Http\Requests;

use App\Models\Quote;
use App\Models\QuoteCustomField;
use App\Models\QuoteSection;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    protected bool $prepared = false;

    public function authorize(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'max:20', 'unique:quotes,number'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(Quote::getStatuses()))],
            'discount' => ['nullable', 'integer', 'min:0'],

            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.designation' => ['required_with:items', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_price' => ['required_with:items', 'integer', 'min:0', 'max:9999999999'],

            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.type' => ['required_with:sections', 'in:'.implode(',', array_keys(QuoteSection::getTypes()))],
            'sections.*.title' => ['required_with:sections', 'string', 'max:255'],
            'sections.*.content.text' => ['nullable', 'string'],
            'sections.*.content.items' => ['nullable', 'array'],
            'sections.*.content.items.*' => ['nullable', 'string'],
            'sections.*.content.headers' => ['nullable', 'array', 'max:8'],
            'sections.*.content.headers.*' => ['nullable', 'string', 'max:100'],
            'sections.*.content.rows' => ['nullable', 'array', 'max:100'],
            'sections.*.content.rows.*' => ['nullable', 'array', 'max:8'],
            'sections.*.content.rows.*.*' => ['nullable', 'string', 'max:1000'],

            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*.id' => ['nullable', 'integer'],
            'custom_fields.*.name' => ['required_with:custom_fields', 'string', 'max:255'],
            'custom_fields.*.type' => ['required_with:custom_fields', 'in:'.implode(',', array_keys(QuoteCustomField::getTypes()))],
            'custom_fields.*.value' => ['nullable', 'string', 'max:5000'],

            'prestations' => ['nullable', 'array'],
            'prestations.*.designation' => ['required_with:prestations', 'string', 'max:1000'],
            'prestations.*.detail' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function validationData(): array
    {
        $this->prepareInput();

        return $this->all();
    }

    protected function prepareForValidation(): void
    {
        $this->prepareInput();
    }

    private function prepareInput(): void
    {
        if ($this->prepared) {
            return;
        }
        $this->prepared = true;

        $items = collect($this->input('items', []))
            ->filter(fn ($item) => is_array($item) && trim((string) ($item['designation'] ?? '')) !== '')
            ->values()
            ->all();

        $sections = collect($this->input('sections', []))
            ->filter(fn ($section) => is_array($section) && trim((string) ($section['title'] ?? '')) !== '')
            ->values()
            ->all();

        $customFields = collect($this->input('custom_fields', []))
            ->filter(fn ($field) => is_array($field) && trim((string) ($field['name'] ?? '')) !== '')
            ->values()
            ->all();

        $prestationsInput = $this->input('prestations', []);
        // Le formulaire soumet les prestations sous forme de chaîne JSON (input caché).
        if (is_string($prestationsInput)) {
            $prestationsInput = json_decode($prestationsInput, true) ?: [];
        }

        // Une prestation = un élément individuel {designation, detail} ; les
        // anciens formats (chaînes simples) sont normalisés, les lignes vides ignorées.
        $prestations = collect($prestationsInput)
            ->map(fn ($p) => is_array($p)
                ? ['designation' => trim((string) ($p['designation'] ?? '')), 'detail' => trim((string) ($p['detail'] ?? ''))]
                : ['designation' => trim((string) $p), 'detail' => ''])
            ->filter(fn ($p) => $p['designation'] !== '')
            ->values()
            ->all();

        $this->merge([
            'items' => $items,
            'sections' => $sections,
            'custom_fields' => $customFields,
            'prestations' => $prestations,
        ]);
    }
}
