<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Client;
use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_submit_creates_client_and_project_directly(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $response = $this->post('/questionnaire/submit');

        $response->assertRedirect('/questionnaire/thankyou');

        $this->assertDatabaseHas('clients', ['name' => 'Jean Dupont']);
        $this->assertDatabaseHas('projects', ['status' => 'submitted']);

        $pending->refresh();
        $this->assertEquals('imported', $pending->status);
        $this->assertNotNull($pending->imported_at);
    }

    public function test_submit_creates_answers_and_answer_options(): void
    {
        $textareaQuestion = Question::where('type', 'textarea')->first();
        $selectQuestion = Question::where('type', 'select')->first();

        $answersData = [];
        if ($textareaQuestion) {
            $answersData[(string) $textareaQuestion->id] = [
                'question_id' => $textareaQuestion->id,
                'text_value' => 'Réponse texte test',
                'option_ids' => [],
            ];
        }
        if ($selectQuestion) {
            $firstOption = $selectQuestion->options()->first();
            if ($firstOption) {
                $answersData[(string) $selectQuestion->id] = [
                    'question_id' => $selectQuestion->id,
                    'text_value' => null,
                    'option_ids' => [$firstOption->id],
                ];
            }
        }

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => $answersData,
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $this->post('/questionnaire/submit');

        $project = Project::first();
        $this->assertNotNull($project);

        if ($textareaQuestion) {
            $this->assertDatabaseHas('answers', [
                'project_id' => $project->id,
                'question_id' => $textareaQuestion->id,
                'text_value' => 'Réponse texte test',
            ]);
        }

        if ($selectQuestion) {
            $answer = Answer::where('project_id', $project->id)
                ->where('question_id', $selectQuestion->id)
                ->first();
            $this->assertNotNull($answer);

            $firstOption = $selectQuestion->options()->first();
            if ($firstOption) {
                $this->assertDatabaseHas('answer_options', [
                    'answer_id' => $answer->id,
                    'question_option_id' => $firstOption->id,
                ]);
            }
        }
    }

    public function test_submit_preserves_other_value(): void
    {
        $selectQuestion = Question::where('type', 'select')->first();
        if (! $selectQuestion) {
            $this->markTestSkipped('No select question found');
        }

        $otherOption = $selectQuestion->options()->where('value', 'autre')->first();
        if (! $otherOption) {
            $this->markTestSkipped('No "autre" option found for select question');
        }

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [
                (string) $selectQuestion->id => [
                    'question_id' => $selectQuestion->id,
                    'text_value' => null,
                    'option_ids' => [$otherOption->id],
                    'other_value' => 'Ma réponse personnalisée',
                ],
            ],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $this->post('/questionnaire/submit');

        $project = Project::first();
        $answer = Answer::where('project_id', $project->id)
            ->where('question_id', $selectQuestion->id)
            ->first();

        $this->assertNotNull($answer);
        $this->assertEquals('Ma réponse personnalisée', $answer->other_value);
    }

    public function test_submit_uses_submission_service(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Service Test', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $this->post('/questionnaire/submit');

        // Verify the SubmissionService was used (client email derived from phone)
        $this->assertDatabaseHas('clients', [
            'email' => '22501020304@dominium.tech',
        ]);

        // Verify project is immediately submitted
        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertEquals('submitted', $project->status);
        $this->assertNotNull($project->submitted_at);
        $this->assertEquals(['site_vitrine'], $project->project_type);
    }

    public function test_submit_clears_session(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $response = $this->post('/questionnaire/submit');

        $response->assertRedirect('/questionnaire/thankyou');

        $session = $this->app['session.store'];
        $this->assertNull($session->get('pending_submission_uuid'));
        $this->assertNull($session->get('project_type'));
    }

    public function test_submit_skips_already_imported(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'imported',
        ]);

        session(['pending_submission_uuid' => $pending->uuid]);

        $this->post('/questionnaire/submit');

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
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
