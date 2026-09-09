<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Question;
use Database\Seeders\QuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserQuestionnaireFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(QuestionSeeder::class);
    }

    public function test_index_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_questionnaire_index_page_loads(): void
    {
        $response = $this->get('/questionnaire');
        $response->assertStatus(200);
        $response->assertSee('questionnaire');
    }

    public function test_start_creates_session_and_redirects_to_wizard(): void
    {
        $response = $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertRedirect(route('questionnaire.wizard'));
        $this->assertDatabaseHas('pending_submissions', [
            'status' => 'collecting',
        ]);
    }

    public function test_wizard_loads_after_start(): void
    {
        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'project_types' => ['site_vitrine'],
        ]);

        $response = $this->get('/questionnaire/wizard');
        $response->assertStatus(200);
    }

    public function test_wizard_submit_answers(): void
    {
        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'project_types' => ['site_vitrine'],
        ]);

        $questions = Question::orderBy('sort_order')->get();

        $answers = [];
        foreach ($questions as $q) {
            if ($q->type === 'yes_no') {
                $answers[$q->id] = ['value' => 'yes'];
            } elseif ($q->type === 'select') {
                $firstOption = $q->options()->first();
                if ($firstOption) {
                    $answers[$q->id] = ['option_ids' => [$firstOption->id]];
                }
            } elseif ($q->type === 'multiselect') {
                $firstOption = $q->options()->first();
                if ($firstOption) {
                    $answers[$q->id] = ['option_ids' => [$firstOption->id]];
                }
            } elseif ($q->type === 'file') {
                continue;
            } else {
                $answers[$q->id] = ['value' => 'Test answer'];
            }
        }

        $response = $this->post('/questionnaire/wizard', ['answers' => $answers]);
        $response->assertRedirect(route('questionnaire.summary'));
    }

    public function test_summary_loads(): void
    {
        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'project_types' => ['site_vitrine'],
        ]);

        $response = $this->get('/questionnaire/summary');
        $response->assertStatus(200);
    }

    public function test_full_flow_start_to_thankyou(): void
    {
        // Start
        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'project_types' => ['site_vitrine'],
        ]);

        // Wizard page
        $this->get('/questionnaire/wizard')->assertStatus(200);

        // Submit all answers
        $questions = Question::orderBy('sort_order')->get();
        $answers = [];
        foreach ($questions as $q) {
            if ($q->type === 'file') {
                continue;
            }
            if ($q->type === 'yes_no') {
                $answers[$q->id] = ['value' => 'yes'];
            } elseif ($q->type === 'select') {
                $opt = $q->options()->first();
                if ($opt) {
                    $answers[$q->id] = ['option_ids' => [$opt->id]];
                }
            } elseif ($q->type === 'multiselect') {
                $opt = $q->options()->first();
                if ($opt) {
                    $answers[$q->id] = ['option_ids' => [$opt->id]];
                }
            } else {
                $answers[$q->id] = ['value' => 'Test answer'];
            }
        }
        $this->post('/questionnaire/wizard', ['answers' => $answers])
            ->assertRedirect(route('questionnaire.summary'));

        // Summary
        $this->get('/questionnaire/summary')->assertStatus(200);

        // Submit
        $this->post('/questionnaire/submit')
            ->assertRedirect(route('questionnaire.thankyou'));

        // Thank you
        $this->get('/questionnaire/thankyou')->assertStatus(200);

        // Pending submission is marked as imported
        $pending = PendingSubmission::first();
        $this->assertEquals('imported', $pending->status);

        // Client + Project are created immediately
        $this->assertDatabaseHas('clients', ['name' => 'Jean Dupont']);
        $this->assertDatabaseHas('projects', ['status' => 'submitted']);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertNotNull($project->submitted_at);
    }
}
