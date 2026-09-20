<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le brouillon PendingSubmission reste le mécanisme de collecte, comme pour
 * l'ancien questionnaire : seul le contenu des réponses change.
 */
class PendingSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    private function startDraft(): void
    {
        $this->post('/questionnaire/start', [
            'nom' => 'Jean Dupont',
            'tel' => '+2250708091011',
            'email' => 'jean@exemple.com',
        ]);
    }

    public function test_start_creates_pending_submission_and_not_client_or_project(): void
    {
        $response = $this->post('/questionnaire/start', [
            'nom' => 'Jean Dupont',
            'tel' => '+2250708091011',
            'email' => 'jean@exemple.com',
        ]);

        $response->assertRedirect(route('questionnaire.step', 2));

        $this->assertDatabaseHas('pending_submissions', ['status' => 'collecting']);
        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_start_stores_pending_submission_uuid_in_session(): void
    {
        $this->startDraft();

        $pending = PendingSubmission::firstOrFail();

        $this->assertSame($pending->uuid, (string) session('devis_pending_uuid'));
    }

    public function test_start_stores_client_data_in_pending_submission(): void
    {
        $this->post('/questionnaire/start', [
            'nom' => 'Jean Dupont',
            'entreprise' => 'Quincaillerie Yop',
            'ville' => 'Abidjan',
            'tel' => '0708091011',
            'email' => 'jean@exemple.com',
        ]);

        $pending = PendingSubmission::firstOrFail();

        $this->assertSame('Jean Dupont', $pending->client_data['name']);
        $this->assertSame('Quincaillerie Yop', $pending->client_data['company_name']);
        $this->assertSame('Abidjan', $pending->client_data['city']);
        $this->assertSame('+2250708091011', $pending->client_data['phone']);
        $this->assertSame('jean@exemple.com', $pending->client_data['email']);
    }

    public function test_start_stores_country_from_ip_geolocation(): void
    {
        $this->post('/questionnaire/start', [
            'nom' => 'Jean Dupont',
            'tel' => '+2250708091011',
            'email' => 'jean@exemple.com',
        ]);

        // La valeur peut être nulle sans base GeoIP : la clé doit exister.
        $this->assertArrayHasKey('country', PendingSubmission::firstOrFail()->client_data);
    }

    public function test_step_page_requires_pending_submission_in_session(): void
    {
        $this->get('/questionnaire/step/2')
            ->assertRedirect(route('questionnaire.index'));
    }

    public function test_each_step_stores_its_answers_in_the_pending_submission(): void
    {
        $this->startDraft();

        $this->post('/questionnaire/step/2', ['type' => 'logiciel']);
        $this->post('/questionnaire/step/3', [
            'modules' => ['stock', 'caisse'],
            'postes' => 4,
            'formation' => 2,
            'reprise' => 'oui',
        ]);
        $this->post('/questionnaire/step/4', [
            'domaine' => 'com',
            'hebergement' => 'vps',
            'emails' => 3,
            'maintenance' => 'business',
        ]);

        $answers = PendingSubmission::firstOrFail()->answers_data;

        $this->assertSame('logiciel', $answers['type']);
        $this->assertSame(['stock', 'caisse'], $answers['modules']);
        $this->assertSame(4, $answers['postes']);
        $this->assertSame('vps', $answers['hebergement']);
        $this->assertSame('business', $answers['maintenance']);

        // Rien n'est encore converti : les réponses n'existent que dans le brouillon.
        $this->assertDatabaseCount('answers', 0);
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_last_step_converts_the_draft_into_business_records(): void
    {
        $this->startDraft();
        $this->post('/questionnaire/step/2', ['type' => 'scolaire']);
        $this->post('/questionnaire/step/3', [
            'eleves' => 400,
            'modules' => ['eleves', 'notes'],
            'reprise' => 'non',
        ]);
        $this->post('/questionnaire/step/4', [
            'domaine' => 'ci',
            'hebergement' => 'vps',
            'emails' => 2,
            'maintenance' => 'essentiel',
        ]);
        $this->post('/questionnaire/step/5', [
            'niveau' => 'STANDARD',
            'delai' => 'accelere',
        ])->assertRedirect(route('questionnaire.quote'));

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseCount('quotes', 1);

        $project = Project::firstOrFail();
        $this->assertSame(['scolaire'], $project->project_type);
        $this->assertSame('v2', $project->form_version);
        $this->assertSame(400, $project->form_data['eleves']);

        // La livraison prioritaire majore le devis provisoire.
        $this->assertGreaterThan(0, Quote::firstOrFail()->calculation['majoration']);
    }

    public function test_submit_is_idempotent_after_conversion(): void
    {
        $this->startDraft();
        $this->post('/questionnaire/step/2', ['type' => 'site_vitrine']);
        $this->post('/questionnaire/step/3', ['pages' => 5, 'contenus' => 'fournis']);
        $this->post('/questionnaire/step/4', ['domaine' => 'aucun', 'hebergement' => 'aucun', 'emails' => 0, 'maintenance' => 'aucune']);
        $this->post('/questionnaire/step/5', ['niveau' => 'STANDARD', 'delai' => 'normal']);

        // Un rechargement de la page finale ne recrée aucun enregistrement.
        $this->get('/questionnaire/quote')->assertOk();

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseCount('quotes', 1);
        $this->assertSame('imported', PendingSubmission::firstOrFail()->status);

        // La session de brouillon est consommée : retenter la dernière étape échoue.
        $this->post('/questionnaire/step/5', ['niveau' => 'STANDARD'])->assertNotFound();
        $this->assertDatabaseCount('projects', 1);
        $this->assertSame(Client::query()->count(), 1);
    }
}
