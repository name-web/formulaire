<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteCustomField;
use App\Models\QuoteItem;
use App\Models\QuoteSection;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    /**
     * Génère le prochain numéro de devis unique au format DT-YYYY-NNNN.
     */
    /**
     * @return array{number: string, sequence: int}
     */
    public function generateNumber(string $year): array
    {
        return DB::transaction(function () use ($year) {
            $last = Quote::whereRaw('SUBSTRING(number, 1, LENGTH(number) - 5) = ?', ["{$year}-"])
                ->lockForUpdate()
                ->orderByDesc('sequence')
                ->first();

            $sequence = $last ? $last->sequence + 1 : 1;

            do {
                $number = sprintf('%s-%s-%04d', config('quote.number_prefix'), $year, $sequence);
                $sequence++;
            } while (Quote::where('number', $number)->exists());

            return ['number' => $number, 'sequence' => $sequence - 1];
        });
    }

    /**
     * Crée un devis depuis un prospect ou un client, avec lignes, sections
     * et champs personnalisés. Les montants sont recalculés côté serveur.
     */
    public function create(Client $client, array $data): Quote
    {
        $numberData = isset($data['number']) && filled($data['number'])
            ? ['number' => $data['number'], 'sequence' => 0]
            : $this->generateNumber($data['issued_at']->format('Y'));

        $quote = DB::transaction(function () use ($client, $data, $numberData) {
            $quote = Quote::create([
                'number' => $numberData['number'],
                'sequence' => $numberData['sequence'],
                'client_id' => $client->id,
                // Renseignés par le nouveau formulaire (devis provisoire) ;
                // null pour les devis créés à la main dans l'administration.
                'project_id' => $data['project_id'] ?? null,
                'calculation' => $data['calculation'] ?? null,
                'issued_at' => $data['issued_at'],
                'due_at' => $data['due_at'] ?? null,
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'prestations' => $data['prestations'] ?? null,
                'status' => $data['status'] ?? Quote::STATUS_DRAFT,
                'discount' => $data['discount'] ?? 0,
            ]);

            $this->syncItems($quote, $data['items'] ?? []);
            $this->syncSections($quote, $data['sections'] ?? []);
            $this->syncCustomFields($quote, $data['custom_fields'] ?? []);
            $this->recalculate($quote);

            return $quote;
        });

        return $quote->fresh(['items', 'sections', 'customFields']);
    }

    /**
     * Met à jour un devis existant. Le numéro et la séquence restent inchangés.
     */
    public function update(Quote $quote, array $data): Quote
    {
        DB::transaction(function () use ($quote, $data) {
            $quote->update([
                'client_id' => $data['client_id'],
                'issued_at' => $data['issued_at'],
                'due_at' => $data['due_at'] ?? null,
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'prestations' => $data['prestations'] ?? null,
                'status' => $data['status'] ?? $quote->status,
                'discount' => $data['discount'] ?? $quote->discount,
            ]);

            $this->syncItems($quote, $data['items'] ?? []);
            $this->syncSections($quote, $data['sections'] ?? []);
            $this->syncCustomFields($quote, $data['custom_fields'] ?? []);
            $this->recalculate($quote);
        });

        return $quote->fresh(['items', 'sections', 'customFields']);
    }

    /**
     * Recalcule et persiste les montants du devis (source de vérité backend).
     */
    public function recalculate(Quote $quote): void
    {
        $subtotal = (int) $quote->items()->sum('total');
        $discount = min($quote->discount ?? 0, $subtotal);

        $quote->forceFill([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max($subtotal - $discount, 0),
        ])->save();
    }

    /**
     * Synchronise les lignes du devis : met à jour, crée et supprime.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function syncItems(Quote $quote, array $items): void
    {
        $keptIds = [];

        foreach (array_values($items) as $index => $itemData) {
            $itemData['sort_order'] = $index;

            if (! empty($itemData['id'])) {
                $item = QuoteItem::where('quote_id', $quote->id)->findOrFail($itemData['id']);
                $item->update($this->itemAttributes($itemData));
                $keptIds[] = $item->id;
            } else {
                $item = $quote->items()->create($this->itemAttributes($itemData));
                $keptIds[] = $item->id;
            }
        }

        $quote->items()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * Synchronise les sections du devis.
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    public function syncSections(Quote $quote, array $sections): void
    {
        $keptIds = [];

        foreach (array_values($sections) as $index => $sectionData) {
            $attributes = [
                'type' => $sectionData['type'] ?? QuoteSection::TYPE_TEXT,
                'title' => $sectionData['title'],
                'content' => $this->normalizeSectionContent($sectionData),
                'sort_order' => $index,
            ];

            if (! empty($sectionData['id'])) {
                $section = QuoteSection::where('quote_id', $quote->id)->findOrFail($sectionData['id']);
                $section->update($attributes);
                $keptIds[] = $section->id;
            } else {
                $section = $quote->sections()->create($attributes);
                $keptIds[] = $section->id;
            }
        }

        $quote->sections()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * Synchronise les champs personnalisés du devis.
     *
     * @param  array<int, array<string, mixed>>  $fields
     */
    public function syncCustomFields(Quote $quote, array $fields): void
    {
        $keptIds = [];

        foreach (array_values($fields) as $index => $fieldData) {
            $attributes = [
                'name' => $fieldData['name'],
                'type' => $fieldData['type'] ?? QuoteCustomField::TYPE_TEXT,
                'value' => $fieldData['value'] ?? null,
                'sort_order' => $index,
            ];

            if (! empty($fieldData['id'])) {
                $field = QuoteCustomField::where('quote_id', $quote->id)->findOrFail($fieldData['id']);
                $field->update($attributes);
                $keptIds[] = $field->id;
            } else {
                $field = $quote->customFields()->create($attributes);
                $keptIds[] = $field->id;
            }
        }

        $quote->customFields()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * Normalise le contenu JSON d'une section selon son type.
     */
    private function normalizeSectionContent(array $sectionData): ?array
    {
        $type = $sectionData['type'] ?? QuoteSection::TYPE_TEXT;
        $content = $sectionData['content'] ?? [];

        return match ($type) {
            QuoteSection::TYPE_TEXT => ['text' => (string) ($content['text'] ?? '')],
            // Les lignes financières vivent dans quote_items ; aucun contenu propre.
            QuoteSection::TYPE_BUDGET => null,
            QuoteSection::TYPE_LIST => ['items' => array_values(array_filter(array_map('strval', (array) ($content['items'] ?? [])), fn ($v) => trim($v) !== ''))],
            QuoteSection::TYPE_TABLE, QuoteSection::TYPE_PRODUCT_TABLE => $this->normalizeTableContent($content),
            QuoteSection::TYPE_CUSTOM_FIELDS => (array) $content,
            default => null,
        };
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string>>}|null
     */
    private function normalizeTableContent(array $content): ?array
    {
        $headers = array_values(array_filter(array_map('strval', (array) ($content['headers'] ?? [])), fn ($v) => trim($v) !== ''));
        $rows = [];

        foreach ((array) ($content['rows'] ?? []) as $row) {
            $cells = array_map('strval', (array) $row);
            if (implode('', array_map('trim', $cells)) === '') {
                continue;
            }
            $rows[] = array_values($cells);
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemAttributes(array $itemData): array
    {
        $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
        $unitPrice = max(0, (int) ($itemData['unit_price'] ?? 0));

        return [
            'designation' => $itemData['designation'],
            'description' => $itemData['description'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
            'sort_order' => (int) ($itemData['sort_order'] ?? 0),
        ];
    }
}
