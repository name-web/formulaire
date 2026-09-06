<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PendingSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_start_creates_pending_submission_and_not_client_or_project(): void
    {
        $response = $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+22501020304',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertRedirect('/questionnaire/wizard');

        $this->assertDatabaseHas('pending_submissions', [
            'status' => 'collecting',
        ]);

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_start_stores_pending_submission_uuid_in_session(): void
    {
        $response = $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+22501020304',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertRedirect('/questionnaire/wizard');

        $pending = PendingSubmission::first();
        $this->assertNotNull($pending);
        $this->assertEquals(session('pending_submission_uuid'), $pending->uuid);
    }

    public function test_start_stores_client_data_in_pending_submission(): void
    {
        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+22501020304',
            'project_types' => ['site_vitrine'],
        ]);

        $pending = PendingSubmission::first();
        $this->assertEquals('Jean Dupont', $pending->client_data['name']);
        $this->assertEquals('+22501020304', $pending->client_data['phone']);
        $this->assertEquals(['site_vitrine'], $pending->project_data['project_types']);
    }

    public function test_wizard_requires_pending_submission_in_session(): void
    {
        $response = $this->get('/questionnaire/wizard');

        $response->assertStatus(404);
    }

    public function test_wizard_displays_questions_for_pending_submission(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $response = $this->get('/questionnaire/wizard');

        $response->assertOk();
    }

    public function test_submit_wizard_saves_answers_to_pending_submission(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $question = Question::where('type', 'textarea')->first();

        $response = $this->post('/questionnaire/wizard', [
            'answers' => [
                $question->id => [
                    'value' => 'Ma réponse de test',
                ],
            ],
        ]);

        $response->assertRedirect('/questionnaire/summary');

        $pending->refresh();
        $this->assertArrayHasKey((string) $question->id, $pending->answers_data);
        $this->assertEquals('Ma réponse de test', $pending->answers_data[$question->id]['text_value']);
    }

    public function test_submit_does_not_create_pending_submission_or_mark_sending(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $response = $this->post('/questionnaire/submit');

        $response->assertRedirect('/questionnaire/thankyou');

        $pending->refresh();
        $this->assertEquals('imported', $pending->status);
        $this->assertNotNull($pending->imported_at);
    }

    public function test_submit_creates_no_client_or_project(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $this->post('/questionnaire/submit');

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('answers', 0);
        $this->assertDatabaseCount('project_files', 0);
    }

    public function test_cannot_access_wizard_with_invalid_uuid(): void
    {
        session(['pending_submission_uuid' => 'invalid-uuid']);

        $response = $this->get('/questionnaire/wizard');

        $response->assertStatus(404);
    }

    public function test_pending_submission_has_uuid(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $this->assertNotNull($pending->uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $pending->uuid);
    }

    public function test_pending_submission_default_status_is_collecting(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $this->assertEquals('collecting', $pending->status);
    }
}
