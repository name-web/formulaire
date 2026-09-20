<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Question;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevisQuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function coordinatorStep(): array
    {
        return [
            'nom' => 'Jean Dupont',
            'entreprise' => 'Quincaillerie Yop',
            'ville' => 'Abidjan',
            'tel' => '+2250708091011',
            'email' => 'jean@exemple.com',
        ];
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Déroule les cinq étapes du formulaire jusqu'au devis provisoire.
     */
    private function completeJourney(): void
    {
        $this->post(route('questionnaire.start'), $this->coordinatorStep());
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);
        $this->post(route('questionnaire.step', 3), [
            'pages' => 5,
            'identite' => 'aucune',
            'contenus' => 'fournis',
        ]);
        $this->post(route('questionnaire.step', 4), [
            'domaine' => 'aucun',
            'hebergement' => 'aucun',
            'emails' => 0,
            'maintenance' => 'aucune',
        ]);
        $this->post(route('questionnaire.step', 5), [
            'niveau' => 'STANDARD',
            'delai' => 'normal',
            'message' => 'Quincaillerie à Yopougon, 4 vendeurs.',
        ]);
    }

    public function test_first_step_is_displayed_with_coordinates(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertOk();
        $response->assertSee('Recevez votre devis en 2 minutes');
        $response->assertSee('name="nom"', false);
        $response->assertSee('name="tel"', false);
        $response->assertSee('name="email"', false);
        $response->assertDontSee('name="project_types"', false);
    }

    public function test_start_requires_name_phone_and_email(): void
    {
        $response = $this->post(route('questionnaire.start'), []);

        $response->assertSessionHasErrors(['nom', 'tel', 'email']);
        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_start_opens_a_draft_and_moves_to_the_project_step(): void
    {
        $response = $this->post(route('questionnaire.start'), $this->coordinatorStep());

        $response->assertRedirect(route('questionnaire.step', 2));

        $pending = PendingSubmission::first();

        $this->assertNotNull($pending);
        $this->assertSame(Project::FORM_VERSION_CURRENT, $pending->project_data['form_version']);
        $this->assertSame('jean@exemple.com', $pending->client_data['email']);

        // Rien n'est encore converti avant la dernière étape.
        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_step_three_requires_a_module_for_software_projects(): void
    {
        $this->post(route('questionnaire.start'), $this->coordinatorStep());
        $this->post(route('questionnaire.step', 2), ['type' => 'logiciel']);

        $response = $this->from(route('questionnaire.step', 3))->post(route('questionnaire.step', 3), [
            'modules' => [],
        ]);

        $response->assertSessionHasErrors('modules');
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_full_journey_creates_client_project_and_provisional_quote(): void
    {
        $this->completeJourney();

        $client = Client::first();

        $this->assertNotNull($client);
        $this->assertSame('Jean Dupont', $client->name);
        $this->assertSame('jean@exemple.com', $client->email);
        $this->assertSame('prospect', $client->status);

        $project = Project::first();

        $this->assertNotNull($project);
        $this->assertSame($client->id, $project->client_id);
        $this->assertSame(['site_vitrine'], $project->project_type);
        $this->assertSame(Project::FORM_VERSION_CURRENT, $project->form_version);
        $this->assertTrue($project->usesCurrentForm());
        $this->assertSame(5, $project->form_data['pages']);
        $this->assertSame('STANDARD', $project->form_data['niveau']);

        $quote = Quote::first();

        $this->assertNotNull($quote);
        $this->assertSame(Quote::STATUS_DRAFT, $quote->status);
        $this->assertSame($client->id, $quote->client_id);
        $this->assertSame($project->id, $quote->project_id);
        $this->assertTrue($quote->isFromForm());

        // Calcul fidèle au dossier fonctionnel (voir tests/Unit/DevisBuilderTest.php).
        $this->assertSame(790000, $quote->calculation['sous_total_ht']);
        $this->assertSame(790000, $quote->calculation['net_ht']);
        $this->assertSame(142200, $quote->calculation['tva']);
        $this->assertSame(932200, $quote->calculation['ttc']);
        $this->assertSame(23, $quote->calculation['jours']);

        // Le devis reste modifiable avec les règles existantes de l'administration.
        $this->assertSame(790000, $quote->subtotal);
        $this->assertNotNull($quote->prestations);
        $this->assertNotEmpty($quote->items);

        // Le brouillon est consommé.
        $this->assertSame('imported', PendingSubmission::first()->status);
    }

    public function test_client_can_see_the_provisional_quote_at_the_end(): void
    {
        $this->completeJourney();

        $quote = Quote::first();

        $this->get(route('questionnaire.quote'))
            ->assertOk()
            ->assertSee('DEVIS PROVISOIRE')
            ->assertSee($quote->number)
            ->assertSee('TOTAL À PAYER TTC')
            ->assertSee('932 200 FCFA')
            ->assertSee('372 880 FCFA')
            ->assertSee('23 JOURS OUVRÉS');
    }

    public function test_legacy_project_still_displays_historical_answers(): void
    {
        $admin = $this->createAdmin();

        $client = Client::create(['name' => 'Ancien Prospect', 'phone' => '+22501000000']);
        $project = $client->projects()->create([
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
        ]);

        $question = Question::create([
            'title' => 'Quel est votre budget ?',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 1,
            'group_label' => 'Budget',
            'is_active' => true,
        ]);

        $project->answers()->create([
            'question_id' => $question->id,
            'text_value' => '1 500 000 FCFA',
        ]);

        $this->assertSame(Project::FORM_VERSION_LEGACY, $project->fresh()->form_version);
        $this->assertTrue($project->fresh()->usesLegacyForm());

        $this->actingAs($admin)
            ->get(route('admin.projects.show', $project))
            ->assertOk()
            ->assertSee('Quel est votre budget ?')
            ->assertSee('1 500 000 FCFA')
            ->assertSee('Budget')
            ->assertDontSee('Détail du besoin');
    }

    public function test_new_form_project_displays_structured_answers_and_provisional_quote(): void
    {
        $admin = $this->createAdmin();

        $this->completeJourney();

        $project = Project::first();
        $quote = Quote::first();

        $this->actingAs($admin)
            ->get(route('admin.projects.show', $project))
            ->assertOk()
            ->assertSee('Détail du besoin')
            ->assertSee('Nombre de pages')
            ->assertSee('Niveau de finition')
            ->assertSee('Devis provisoire')
            ->assertSee($quote->number)
            ->assertDontSee('Aucune réponse enregistrée');
    }

    public function test_admin_quote_document_shows_the_provisional_calculation(): void
    {
        $admin = $this->createAdmin();

        $this->completeJourney();

        $quote = Quote::first();

        $this->actingAs($admin)
            ->get(route('admin.quotes.preview', $quote))
            ->assertOk()
            ->assertSee('Détail du devis provisoire')
            ->assertSee('Total TTC');
    }
}
