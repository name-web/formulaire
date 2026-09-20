<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteE2EFlowTest extends TestCase
{
    use RefreshDatabase;

    // ── Données fixes partagées par l'ensemble du parcours ──────────────────
    private string $adminEmail = 'admin@domitium.test';

    private string $adminPassword = 'Dominium2026';

    /** Objet du devis : requis par StoreQuoteRequest, fourni par le champ caché du formulaire. */
    private string $quoteSubject = 'Gestion complète de la présence digitale';

    protected function setUp(): void
    {
        parent::setUp();

        // Un admin réel avec mot de passe non trivial (évite les filtres de
        // mot de passe trop simples).
        User::create([
            'name' => 'Admin Dominium',
            'email' => $this->adminEmail,
            'password' => $this->adminPassword,
            'role' => 'admin',
            'is_active' => true,
        ]);

        Client::create([
            'name' => 'Cabinet Koffi & Associés',
            'phone' => '+2250501010101',
            'status' => 'prospect',
        ]);
    }

    private function loginAdmin(): void
    {
        $this->post('/login', [
            'email' => $this->adminEmail,
            'password' => $this->adminPassword,
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', $this->adminEmail)->first());
    }

    // ── 1. Connexion admin → dashboard ──────────────────────────────────────

    public function test_admin_can_login_and_reaches_dashboard(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));

        $this->loginAdmin();
    }

    public function test_dashboard_returns_200_after_login(): void
    {
        $this->loginAdmin();

        $this->get(route('admin.dashboard'))->assertOk();
    }

    // ── 2. Création d'un nouveau devis complet ──────────────────────────────

    public function test_full_quote_creation_flow(): void
    {
        $this->loginAdmin();

        // 2.1 Accès à la liste des devis (route index)
        $this->get(route('admin.quotes.index'))->assertOk();

        $clientId = Client::where('name', 'Cabinet Koffi & Associés')->value('id');

        // 2.2 Création du devis avec toutes les sections par payload
        $payload = $this->fullQuotePayload($clientId);

        $storeResponse = $this->post(route('admin.quotes.store'), $payload);

        $storeResponse->assertRedirect();
        $responseLocation = $storeResponse->headers->get('Location');
        $this->assertMatchesRegularExpression('@/admin/quotes/(\d+)@', $responseLocation);

        $quote = Quote::find((int) preg_replace('@.*quotes/@', '', $responseLocation));
        $this->assertNotNull($quote);
        $this->assertNotNull($quote->number);
        $this->assertMatchesRegularExpression('@^DT-\d{4}-\d{4}$@', $quote->number);
        $this->assertSame('Cabinet Koffi & Associés', $quote->client->name);
        $this->assertSame('Gestion complète de la présence digitale', $quote->subject);
        // Quote::getStatuses() retourne des constantes ; vérifier la forme brute.
        $statusValue = $quote->status instanceof \BackedEnum ? $quote->status->value : $quote->status;
        $this->assertSame('draft', $statusValue);

        // 2.3 Section budget
        $budgetSection = $quote->sections()->where('type', 'budget')->first();
        $this->assertNotNull($budgetSection);
        $this->assertSame('Répartition du budget', $budgetSection->title);

        // 2.4 Section prestations (liste)
        $prestations = $quote->sections()->where('type', 'list')->first();
        $this->assertNotNull($prestations);
        $this->assertSame('Prestations incluses', $prestations->title);
        $this->assertCount(2, $prestations->content['items']);

        // 2.5 Section analyse texte
        $analysis = $quote->sections()->where('type', 'text')->where('title', 'Analyse des produits & cible recommandée')->first();
        $this->assertNotNull($analysis);
        $this->assertSame('Analyse poussée des produits Santé Naturelle et Vie :…', $analysis->content['text']);

        // 2.6 Tableau produits/cibles/canaux
        $productTable = $quote->sections()->where('type', 'product_table')->first();
        $this->assertNotNull($productTable);
        $this->assertSame('Produits / Cible prioritaire / Canaux conseillés', $productTable->title);
        $this->assertCount(3, $productTable->content['rows']);
        $this->assertSame('Tisane Spéciale Anti-Palu (3 000 F)', $productTable->content['rows'][0][0]);

        // 2.7 Champs personnalisés
        $this->assertCount(2, $quote->customFields);
    }

    // ── 3. Ouvrir le devis → vérifier la persistance des données ────────────

    public function test_created_quote_persists_in_show_page(): void
    {
        $this->loginAdmin();

        $quote = $this->createFullQuote('Cabinet Koffi & Associés');

        $show = $this->get(route('admin.quotes.show', $quote));

        $show->assertOk();
        $show->assertSee($quote->number);
        $show->assertSee($quote->subject);
        $show->assertSee($quote->client->name);
        // Persistance du texte d'analyse dans le show
        $analysisSection = $quote->sections()->where('type', 'text')->where('title', 'Analyse des produits & cible recommandée')->first();
        if ($analysisSection) {
            $show->assertSee($analysisSection->content['text']);
        }
    }

    // ── 4. Édition → modifier quelques données → sauvegarde → persistance ──

    public function test_quote_can_be_edited_and_changes_persist(): void
    {
        $this->loginAdmin();

        $quote = $this->createFullQuote('Cabinet Koffi & Associés');

        // Modification : objet + note + ligne de prestation
        $editPayload = [
            'subject' => $this->quoteSubject,
            'client_id' => Client::where('name', 'Cabinet Koffi & Associés')->value('id'),
            'issued_at' => '2026-09-11',
            'description' => 'Gestion complète de la présence digitale — version révisée',
            'prestations' => json_encode([
                ['designation' => 'Création et optimisation des pages Facebook, TikTok et WhatsApp Business'],
                ['designation' => 'Suivi des performances et diffusion ciblée'],
            ]),
            'sections' => [
                ['type' => 'text', 'title' => 'Analyse des produits & cible recommandée', 'content' => ['text' => 'Analyse mise à jour après édition']],
            ],
            'custom_fields' => [
                ['name' => 'Durée de la campagne', 'type' => 'text', 'value' => '45 jours'],
                ['name' => 'Budget publicitaire', 'type' => 'amount', 'value' => '50000'],
            ],
        ];

        $editResponse = $this->patch(route('admin.quotes.update', $quote), $editPayload);

        $editResponse->assertRedirect();
        $editResponse->assertRedirect(route('admin.quotes.show', $quote));

        $quote->refresh();
        $this->assertSame('Gestion complète de la présence digitale — version révisée', $quote->description);
        $this->assertSame('Analyse mise à jour après édition', $quote->sections()->where('type', 'text')->where('title', 'Analyse des produits & cible recommandée')->first()->content['text']);
        $this->assertSame('45 jours', $quote->customFields()->where('name', 'Durée de la campagne')->first()->value);
    }

    // ── 5. Aperçu → PDF ─────────────────────────────────────────────────────

    public function test_preview_page_renders_quote_data(): void
    {
        $this->loginAdmin();

        $quote = $this->createFullQuote('Cabinet Koffi & Associés');

        $preview = $this->get(route('admin.quotes.preview', $quote));

        $preview->assertOk();
        $preview->assertSee($quote->number);
        $preview->assertSee($quote->subject);
        $preview->assertSee($quote->client->name);
    }

    public function test_pdf_is_generated_for_full_quote(): void
    {
        $this->loginAdmin();

        $quote = $this->createFullQuote('Cabinet Koffi & Associés');

        $pdfResponse = $this->get(route('admin.quotes.pdf', $quote));

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
        // Le nom du fichier porte la référence du devis généré.
        $pdfResponse->assertHeader('Content-Disposition', 'inline; filename="devis-'.$quote->number.'.pdf"');

        $content = $pdfResponse->getContent();
        $this->assertStringStartsWith('%PDF-', $content);

        // Les flux PDF sont compressés : la référence se lit dans les métadonnées
        // (titre encodé en UTF-16BE par Dompdf).
        $this->assertSame(1, preg_match('@/Title \((.*?)\)@s', $content, $matches), 'Le titre PDF doit être présent.');
        $rawTitle = str_starts_with($matches[1], "\xfe\xff") ? substr($matches[1], 2) : $matches[1];
        $title = trim(mb_convert_encoding($rawTitle, 'UTF-8', 'UTF-16BE'));
        $this->assertSame('Devis '.$quote->number, $title);
    }

    // ── 6. L'ancien questionnaire n'est plus administrable

    public function test_legacy_questionnaire_admin_routes_are_removed(): void
    {
        $this->loginAdmin();

        // Le nouveau formulaire de devis remplace l'ancien questionnaire : sa
        // gestion (questions, options, conditions) n'a plus lieu d'être. Les
        // données historiques restent stockées et consultables.
        foreach (['admin.questions.index', 'admin.questions.store', 'admin.questions.options.store', 'admin.questions.conditions.store'] as $route) {
            $this->assertFalse(app('router')->has($route), "La route {$route} ne doit plus exister.");
        }
    }

    // ── 7. Déconnexion → reconnexion → session fonctionnelle ────────────────

    public function test_logout_and_relogin_keep_session_working(): void
    {
        // 7.1 Déconnexion
        $this->loginAdmin();

        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();

        // 7.2 Reconnexion
        $this->loginAdmin();

        // 7.3 Session fonctionnelle : accès dashboard + création rapide devis
        $this->get(route('admin.dashboard'))->assertOk();

        $clientId = Client::where('name', 'Cabinet Koffi & Associés')->value('id');
        $payload = $this->fullQuotePayload($clientId);

        $storeResponse = $this->post(route('admin.quotes.store'), $payload);
        $storeResponse->assertRedirect();
        $location = $storeResponse->headers->get('Location');
        $this->assertMatchesRegularExpression('@/admin/quotes/(\d+)@', $location);
        $storeResponse->assertSessionHasNoErrors();
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function fullQuotePayload(int $clientId): array
    {
        return [
            'client_id' => $clientId,
            'issued_at' => '2026-09-11',
            'status' => 'draft',
            'subject' => $this->quoteSubject,
            'description' => 'Gestion complète de la présence digitale',
            'items' => [
                ['designation' => 'Frais de gestion Dominium Tech', 'description' => 'Stratégie, création de visuels, animation', 'quantity' => 1, 'unit_price' => 20000],
                ['designation' => 'Budget promotion / diffusion', 'description' => 'Boost, campagnes ciblées', 'quantity' => 1, 'unit_price' => 30000],
            ],
            'prestations' => json_encode([
                ['designation' => 'Création et optimisation des pages Facebook, TikTok et WhatsApp Business'],
                ['designation' => 'Suivi des performances et diffusion ciblée'],
            ]),
            'sections' => [
                ['type' => 'budget', 'title' => 'Répartition du budget'],
                ['type' => 'list', 'title' => 'Prestations incluses', 'content' => ['items' => ['Création et/ou optimisation des pages Facebook, TikTok et WhatsApp Business', 'Suivi des performances et diffusion ciblée']]],
                ['type' => 'text', 'title' => 'Analyse des produits & cible recommandée', 'content' => ['text' => 'Analyse poussée des produits Santé Naturelle et Vie :…']],
                ['type' => 'product_table', 'title' => 'Produits / Cible prioritaire / Canaux conseillés', 'content' => ['headers' => ['Produit', 'Cible prioritaire', 'Canaux conseillés'], 'rows' => [
                    ['Tisane Spéciale Anti-Palu (3 000 F)', 'Grand public, familles avec enfants', 'Facebook, WhatsApp'],
                    ['Santé des Femmes (3 000 F)', 'Femmes 22-45 ans', 'TikTok & Facebook, WhatsApp'],
                    ['Nettoyage du Côlon (5 000 F)', 'Adultes 25-50 ans, bien-être', 'Facebook & TikTok, WhatsApp'],
                ]]],
            ],
            'custom_fields' => [
                ['name' => 'Durée de la campagne', 'type' => 'text', 'value' => '30 jours'],
                ['name' => 'Budget publicitaire', 'type' => 'amount', 'value' => '50000'],
            ],
        ];
    }

    private function createFullQuote(string $clientName): Quote
    {
        $clientId = Client::where('name', $clientName)->value('id');
        $payload = $this->fullQuotePayload($clientId);

        $response = $this->post(route('admin.quotes.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $location = $response->headers->get('Location');
        $this->assertMatchesRegularExpression('@/admin/quotes/(\d+)@', $location);

        $quoteId = (int) preg_replace('@.*quotes/@', '', $location);
        $quote = Quote::find($quoteId);
        $this->assertNotNull($quote);

        return $quote;
    }

    private function fullQuotePayloadWithoutSections(int $clientId): array
    {
        return [
            'client_id' => $clientId,
            'issued_at' => '2026-09-11',
            'status' => 'draft',
            'subject' => $this->quoteSubject,
            'description' => 'Gestion complète de la présence digitale',
            'items' => [
                ['designation' => 'Frais de gestion Dominium Tech', 'description' => 'Stratégie, création de visuels, animation', 'quantity' => 1, 'unit_price' => 20000],
                ['designation' => 'Budget promotion / diffusion', 'description' => 'Boost, campagnes ciblées', 'quantity' => 1, 'unit_price' => 30000],
            ],
            'prestations' => json_encode([
                ['designation' => 'Création et optimisation des pages Facebook, TikTok et WhatsApp Business'],
                ['designation' => 'Suivi des performances et diffusion ciblée'],
            ]),
            'custom_fields' => [
                ['name' => 'Durée de la campagne', 'type' => 'text', 'value' => '30 jours'],
                ['name' => 'Budget publicitaire', 'type' => 'amount', 'value' => '50000'],
            ],
        ];
    }
}
