<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->client = Client::create([
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'status' => 'client',
        ]);
    }

    // ── Création ──

    public function test_create_form_is_prefilled_from_client(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/quotes/create?client_id='.$this->client->id);

        $response->assertOk();
        $response->assertSee('Jean Dupont');
        $response->assertSee('value="'.$this->client->id.'"', false);
    }

    public function test_quote_can_be_created_from_a_client(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('quotes', [
            'client_id' => $this->client->id,
            'subject' => 'Gestion communication digitale',
            'total' => 50000,
        ]);
    }

    public function test_quote_number_is_generated_automatically_and_unique(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload());
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload());

        $numbers = Quote::orderBy('id')->pluck('number')->all();

        $this->assertCount(2, $numbers);
        $this->assertNotEquals($numbers[0], $numbers[1]);
        $this->assertMatchesRegularExpression('/^DT-\d{4}-\d{4}$/', $numbers[0]);
    }

    public function test_quote_number_does_not_change_on_update(): void
    {
        $quote = Quote::factory()->for($this->client)->create();
        $originalNumber = $quote->number;

        $this->actingAs($this->admin)->patch("/admin/quotes/{$quote->id}", $this->validPayload([
            'subject' => 'Nouvel objet',
        ]));

        $quote->refresh();
        $this->assertSame($originalNumber, $quote->number);
        $this->assertSame('Nouvel objet', $quote->subject);
    }

    // ── Lignes ──

    public function test_items_are_calculated_server_side(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload([
            'items' => [
                ['designation' => 'Frais de gestion', 'description' => 'Gestion digitale', 'quantity' => 2, 'unit_price' => 20000],
                ['designation' => 'Budget promotion', 'description' => 'Campagnes', 'quantity' => 1, 'unit_price' => 30000],
            ],
        ]));

        $quote = Quote::latest('id')->first();

        $this->assertSame(70000, $quote->subtotal);
        $this->assertSame(70000, $quote->total);
        $this->assertDatabaseHas('quote_items', ['designation' => 'Frais de gestion', 'total' => 40000]);
    }

    public function test_client_submitted_totals_are_ignored(): void
    {
        $payload = $this->validPayload();
        $payload['items'][0]['quantity'] = 99; // falsifié côté client

        $this->actingAs($this->admin)->post('/admin/quotes', $payload);

        $quote = Quote::latest('id')->first();
        $this->assertDatabaseHas('quote_items', ['designation' => 'Frais de gestion', 'total' => 99 * 20000]);
        $this->assertSame(99 * 20000 + 30000, $quote->total);
    }

    public function test_items_can_be_updated_and_deleted(): void
    {
        $quote = Quote::factory()->for($this->client)->hasItems(2)->create();
        $firstItem = $quote->items->first();

        $secondItemId = $quote->items->skip(1)->first()->id;

        $this->actingAs($this->admin)->patch("/admin/quotes/{$quote->id}", $this->validPayload([
            'items' => [
                ['id' => $firstItem->id, 'designation' => 'Ligne modifiée', 'quantity' => 3, 'unit_price' => 1000],
                ['designation' => 'Nouvelle ligne', 'quantity' => 1, 'unit_price' => 500],
            ],
        ]));

        $quote->refresh();
        $this->assertDatabaseHas('quote_items', ['id' => $firstItem->id, 'designation' => 'Ligne modifiée', 'total' => 3000]);
        $this->assertDatabaseMissing('quote_items', ['id' => $secondItemId]);
        $this->assertCount(2, $quote->items()->get());
        $this->assertSame(3500, $quote->total);
    }

    // ── Modèle de devis par défaut (devis.html DOMINIUM) ──

    public function test_create_form_prefills_dominiium_template(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/quotes/create?client_id='.$this->client->id);

        $response->assertOk();
        // Textes du modèle original présents comme valeurs initiales modifiables.
        $response->assertSee('Prestations incluses');
        $response->assertSee('Frais de gestion Dominium Tech');
        $response->assertSee('Répartition du budget');
        $response->assertSee('Tisane Spéciale Anti-Palu');
    }

    public function test_create_form_prefills_exactly_two_prestations(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/quotes/create?client_id='.$this->client->id);

        $response->assertOk();
        // Le modèle fournit exactement 2 prestations, une par ligne (input caché JSON).
        $this->assertCount(2, config('quote_template.prestations'));
        // @json échappe les accents (é -> \u00e9) : on cible des fragments sans accent.
        $response->assertSee('optimisation des pages Facebook, TikTok et WhatsApp Business', false);
        $response->assertSee('Mise en avant des num', false);
        // Bouton d'ajout dynamique d'une ligne de prestation.
        $response->assertSee('addPrestationBtn');
        $response->assertSee('Ajouter une ligne');
    }

    public function test_prestations_are_persisted_as_two_individual_items(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload([
            'prestations' => json_encode([
                ['designation' => 'Gestion des réseaux sociaux'],
                ['designation' => 'Suivi des performances'],
            ]),
        ]));

        $quote = Quote::latest('id')->first();

        $this->assertSame(
            [
                ['designation' => 'Gestion des réseaux sociaux', 'detail' => ''],
                ['designation' => 'Suivi des performances', 'detail' => ''],
            ],
            collect($quote->prestations)->map(fn ($p) => ['designation' => $p['designation'], 'detail' => $p['detail']])->values()->all(),
            'Les 2 prestations doivent être enregistrées comme éléments individuels.'
        );
    }

    public function test_prestations_removal_is_persisted_as_empty(): void
    {
        $quote = Quote::factory()->for($this->client)->create(['prestations' => [
            ['designation' => 'Prestation un', 'detail' => ''],
            ['designation' => 'Prestation deux', 'detail' => ''],
        ]]);

        $this->actingAs($this->admin)->patch("/admin/quotes/{$quote->id}", $this->validPayload([
            'prestations' => json_encode([['designation' => '']]),
        ]));

        $quote->refresh();

        $this->assertSame([], array_values($quote->prestations ?? []));
    }

    public function test_edit_form_does_not_reinstate_template_on_existing_quote(): void
    {
        $quote = Quote::factory()->for($this->client)->create(); // devis vide

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/edit");

        $response->assertOk();
        $response->assertDontSee('Frais de gestion Dominium Tech');
    }

    public function test_budget_section_renders_financial_table_at_its_position(): void
    {
        $quote = Quote::factory()->for($this->client)->hasItems(1)->create();
        $quote->sections()->create(['type' => 'text', 'title' => 'Analyse', 'content' => ['text' => 'Analyse texte'], 'sort_order' => 0]);
        $quote->sections()->create(['type' => 'budget', 'title' => 'Répartition du budget', 'content' => null, 'sort_order' => 1]);

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/preview");

        $response->assertOk();
        $content = $response->getContent();
        $this->assertLessThan(
            mb_strpos($content, 'Répartition du budget'),
            mb_strpos($content, 'Analyse texte'),
            'Le bloc texte doit précéder le bloc budgétaire (ordre utilisateur).'
        );
    }

    public function test_quotes_without_budget_block_still_show_financial_table(): void
    {
        $quote = Quote::factory()->for($this->client)->hasItems(2)->create(); // aucune section

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/preview");

        $response->assertOk();
        $response->assertSee('Répartition du budget');
        $response->assertSee('TOTAL');
    }

    public function test_budget_section_is_persisted_from_payload(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload([
            'sections' => [
                ['type' => 'budget', 'title' => 'Répartition du budget'],
                ['type' => 'list', 'title' => 'Prestations incluses', 'content' => ['items' => ['Suivi des performances']]],
            ],
        ]));

        $quote = Quote::latest('id')->first();

        $this->assertSame(['budget', 'list'], $quote->sections()->get()->pluck('type')->all());
        $this->assertNull($quote->sections()->first()->content);
    }

    // ── Analyse des produits & cible recommandée ──

    public function test_create_form_prefills_analysis_section_with_template_text(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/quotes/create?client_id='.$this->client->id);

        $response->assertOk();
        // Section dédiée, éditable, préremplie avec le texte du modèle.
        $response->assertSee('ANALYSE DES PRODUITS');
        $response->assertSee('name="sections[0][content][text]"', false);
        $response->assertSee('de la marque Santé Naturelle et Vie', false);
    }

    public function test_analysis_text_is_persisted_as_text_section(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload([
            'sections' => [
                ['type' => 'text', 'title' => 'Analyse des produits & cible recommandée', 'content' => ['text' => 'Analyse personnalisée du devis']],
            ],
        ]));

        $quote = Quote::latest('id')->first();

        $this->assertDatabaseHas('quote_sections', [
            'quote_id' => $quote->id,
            'type' => 'text',
            'title' => 'Analyse des produits & cible recommandée',
        ]);
        $this->assertSame('Analyse personnalisée du devis', $quote->sections()->first()->content['text']);
    }

    public function test_edit_form_restores_saved_analysis_text(): void
    {
        $quote = Quote::factory()->for($this->client)->create();
        $section = $quote->sections()->create([
            'type' => 'text',
            'title' => 'Analyse des produits & cible recommandée',
            'content' => ['text' => 'Analyse sauvegardée du devis'],
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/edit");

        $response->assertOk();
        // Valeur sauvegardée restaurée, pas le texte du modèle.
        $response->assertSee('Analyse sauvegardée du devis', false);
        $response->assertSee('name="sections[0][id]" value="'.$section->id.'"', false);
    }

    public function test_analysis_section_is_updated_in_place_on_edit(): void
    {
        $quote = Quote::factory()->for($this->client)->create();
        $section = $quote->sections()->create([
            'type' => 'text',
            'title' => 'Analyse des produits & cible recommandée',
            'content' => ['text' => 'Ancien texte'],
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin)->patch("/admin/quotes/{$quote->id}", $this->validPayload([
            'sections' => [
                ['id' => $section->id, 'type' => 'text', 'title' => 'Analyse des produits & cible recommandée', 'content' => ['text' => 'Nouveau texte']],
            ],
        ]));

        $quote->refresh();
        $this->assertSame(1, $quote->sections()->count());
        $this->assertSame('Nouveau texte', $quote->sections()->first()->content['text']);
    }

    // ── Champs personnalisés ──

    public function test_custom_fields_are_saved(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload([
            'custom_fields' => [
                ['name' => 'Durée de la campagne', 'type' => 'text', 'value' => '30 jours'],
                ['name' => 'Budget publicitaire', 'type' => 'amount', 'value' => '50000'],
            ],
        ]));

        $quote = Quote::latest('id')->first();

        $this->assertDatabaseHas('quote_custom_fields', ['quote_id' => $quote->id, 'name' => 'Durée de la campagne', 'value' => '30 jours']);
        $this->assertCount(2, $quote->customFields()->get());
    }

    public function test_custom_fields_can_be_removed(): void
    {
        $quote = Quote::factory()->for($this->client)->create();
        $quote->customFields()->create(['name' => 'À supprimer', 'type' => 'text', 'value' => 'x']);

        $this->actingAs($this->admin)->patch("/admin/quotes/{$quote->id}", $this->validPayload([
            'custom_fields' => [
                ['name' => 'Conservé', 'type' => 'text', 'value' => 'y'],
            ],
        ]));

        $this->assertDatabaseMissing('quote_custom_fields', ['name' => 'À supprimer']);
        $this->assertDatabaseHas('quote_custom_fields', ['name' => 'Conservé']);
    }

    // ── Sections ──

    public function test_sections_are_saved_with_order(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload([
            'sections' => [
                ['type' => 'text', 'title' => 'Objet détaillé', 'content' => ['text' => 'Contenu libre']],
                ['type' => 'list', 'title' => 'Prestations incluses', 'content' => ['items' => ['Visuels', 'Community management']]],
                ['type' => 'product_table', 'title' => 'Produits & cibles', 'content' => ['headers' => ['Produit', 'Cible'], 'rows' => [['Tisane', 'Familles']]]],
            ],
        ]));

        $quote = Quote::latest('id')->first();
        $sections = $quote->sections()->get();

        $this->assertCount(3, $sections);
        $this->assertSame([0, 1, 2], $sections->pluck('sort_order')->all());
        $this->assertSame(['items' => ['Visuels', 'Community management']], $sections[1]->content);
    }

    public function test_sections_can_be_reordered_and_deleted(): void
    {
        $quote = Quote::factory()->for($this->client)->create();
        $a = $quote->sections()->create(['type' => 'text', 'title' => 'A', 'content' => ['text' => 'a']]);
        $b = $quote->sections()->create(['type' => 'text', 'title' => 'B', 'content' => ['text' => 'b']]);

        $this->actingAs($this->admin)->patch("/admin/quotes/{$quote->id}", $this->validPayload([
            'sections' => [
                ['id' => $b->id, 'type' => 'text', 'title' => 'B', 'content' => ['text' => 'b']],
                ['id' => $a->id, 'type' => 'text', 'title' => 'A', 'content' => ['text' => 'a']],
            ],
        ]));

        $quote->refresh();
        $this->assertSame([$b->id, $a->id], $quote->sections()->get()->pluck('id')->all());
    }

    // ── Devis ──

    public function test_quote_status_can_be_changed(): void
    {
        $quote = Quote::factory()->for($this->client)->create();

        $this->actingAs($this->admin)->patch("/admin/quotes/{$quote->id}/status", ['status' => 'paid']);

        $quote->refresh();
        $this->assertSame('paid', $quote->status);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $quote = Quote::factory()->for($this->client)->create();

        $this->actingAs($this->admin)
            ->from("/admin/quotes/{$quote->id}")
            ->patch("/admin/quotes/{$quote->id}/status", ['status' => 'hacked']);

        $quote->refresh();
        $this->assertNotSame('hacked', $quote->status);
    }

    public function test_discount_cannot_exceed_subtotal(): void
    {
        $this->actingAs($this->admin)->post('/admin/quotes', $this->validPayload([
            'discount' => 999999,
        ]));

        $quote = Quote::latest('id')->first();
        $this->assertSame(0, $quote->total);
        $this->assertSame($quote->subtotal, $quote->discount);
    }

    // ── Aperçu / PDF ──

    public function test_preview_page_renders_dynamic_data(): void
    {
        $quote = Quote::factory()->for($this->client)->paid()->create();

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/preview");

        $response->assertOk();
        $response->assertSee($quote->number);
        $response->assertSee('Jean Dupont');
        $response->assertSee('DEVIS RÉGLÉ — VALANT FACTURE');
    }

    public function test_paid_badge_is_hidden_for_draft_quote(): void
    {
        $quote = Quote::factory()->for($this->client)->create(); // draft

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/preview");

        $response->assertOk();
        $response->assertDontSee('DEVIS RÉGLÉ');
    }

    public function test_pdf_can_be_generated(): void
    {
        $quote = Quote::factory()->for($this->client)->hasItems(2)->create();

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/%PDF-\d\.\d/', $response->getContent());
        $this->assertMatchesRegularExpression('/MediaBox\s*\[\s*0(?:\.0+)? 0(?:\.0+)? 595\.2+80 841\.8+90?\s*\]/', $response->getContent());
    }

    public function test_pdf_spans_multiple_pages_for_long_quote(): void
    {
        $quote = Quote::factory()->for($this->client)->create();

        foreach (range(1, 30) as $i) {
            $quote->items()->create([
                'designation' => "Prestation {$i}",
                'description' => "Détail de la prestation numéro {$i} avec un libellé suffisamment long pour occuper de l'espace sur la page.",
                'quantity' => 1,
                'unit_price' => 1000,
                'total' => 1000,
                'sort_order' => $i,
            ]);
        }

        $response = $this->actingAs($this->admin)->get("/admin/quotes/{$quote->id}/pdf");

        $response->assertOk();
        $pageCount = preg_match_all('/\/Type\s*\/Page(?!s)[\s>]/', $response->getContent());
        $this->assertGreaterThanOrEqual(2, $pageCount);
    }

    // ── Sécurité ──

    public function test_guest_cannot_access_quotes(): void
    {
        $this->get('/admin/quotes')->assertRedirect(route('login'));
        $this->post('/admin/quotes', $this->validPayload())->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_quotes(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin/quotes')->assertForbidden();
        $this->actingAs($user)->post('/admin/quotes', $this->validPayload())->assertForbidden();
    }

    public function test_validation_rejects_invalid_data(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/quotes', [
            'client_id' => 999999,
            'issued_at' => 'not-a-date',
            'subject' => '',
            'items' => [['designation' => 'x', 'quantity' => -1, 'unit_price' => 'abc']],
        ]);

        $response->assertSessionHasErrors(['client_id', 'issued_at', 'subject', 'items.0.quantity', 'items.0.unit_price']);
    }

    // ── Helpers ──

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'client_id' => $this->client->id,
            'issued_at' => '2026-09-08',
            'subject' => 'Gestion communication digitale',
            'status' => 'draft',
            'items' => [
                ['designation' => 'Frais de gestion', 'description' => 'Gestion digitale', 'quantity' => 1, 'unit_price' => 20000],
                ['designation' => 'Budget promotion', 'description' => 'Campagnes', 'quantity' => 1, 'unit_price' => 30000],
            ],
            'sections' => [
                ['type' => 'text', 'title' => 'Objet de la prestation', 'content' => ['text' => 'Communication digitale']],
            ],
            'custom_fields' => [],
        ], $overrides);
    }
}
