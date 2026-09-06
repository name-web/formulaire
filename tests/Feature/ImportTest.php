<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Question;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['external_submission.import_secret' => 'test-secret-key']);
    }

    public function test_import_creates_client_and_project(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('clients', ['name' => 'Jean Dupont']);
        $this->assertDatabaseHas('projects', ['status' => 'submitted']);
    }

    public function test_import_creates_answers(): void
    {
        $question = Question::where('type', 'textarea')->first();

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [
                (string) $question->id => [
                    'text_value' => 'Ma réponse de test',
                ],
            ],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'text_value' => 'Ma réponse de test',
        ]);
    }

    public function test_import_sets_status_to_imported(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $pending->refresh();
        $this->assertEquals('imported', $pending->status);
        $this->assertNotNull($pending->imported_at);
    }

    public function test_import_prevents_duplicates(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $data = [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ];
        $headers = ['X-Import-Secret' => 'test-secret-key'];

        $this->postJson('/internal/import', $data, $headers);
        $this->postJson('/internal/import', $data, $headers);

        $this->assertDatabaseCount('projects', 1);
    }

    public function test_import_rejects_wrong_secret(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'wrong-secret',
        ]);

        $response->assertStatus(401);
    }

    public function test_import_rejects_missing_secret(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_import_rejects_nonexistent_submission(): void
    {
        $response = $this->postJson('/internal/import', [
            'submission_uuid' => '00000000-0000-0000-0000-000000000000',
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $response->assertStatus(404);
    }

    public function test_import_rejects_wrong_status_collecting(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $response->assertStatus(422);
    }

    public function test_import_rejects_wrong_status_failed(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'failed',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $response->assertStatus(422);
    }

    public function test_import_already_imported_returns_idempotent(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'imported',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_import_creates_client_with_correct_email(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $this->assertDatabaseHas('clients', [
            'email' => '22501020304@dominium.tech',
        ]);
    }

    public function test_import_creates_project_with_submitted_status(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        $project = Project::first();
        $this->assertEquals('submitted', $project->status);
        $this->assertNotNull($project->submitted_at);
        $this->assertEquals(['site_vitrine'], $project->project_type);
    }

    public function test_import_no_regression_on_existing_projects(): void
    {
        $this->assertDatabaseHas('questions', ['title' => 'Quel est votre objectif principal ?']);
    }

    // ──────────────────────────────────────────────
    // CSRF exemption
    // ──────────────────────────────────────────────

    public function test_import_works_without_csrf_token(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'CSRF Test', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'CSRF Test', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'test-secret-key',
        ]);

        // Must NOT be 419 (TokenMismatchException / CSRF)
        $response->assertOk();
    }

    public function test_import_wrong_secret_returns_401(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Bad Secret', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Bad Secret', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-Import-Secret' => 'wrong-secret',
        ]);

        $response->assertStatus(401);
    }

    public function test_import_missing_secret_returns_401(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'No Secret', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'No Secret', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_import_is_idempotent(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Idempotent', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $data = [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Idempotent', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ];
        $headers = ['X-Import-Secret' => 'test-secret-key'];

        $this->postJson('/internal/import', $data, $headers)->assertOk();
        $this->postJson('/internal/import', $data, $headers)->assertOk();

        $this->assertDatabaseCount('projects', 1);
    }

    // ──────────────────────────────────────────────
    // Route-level CSRF exclusion verification
    // ──────────────────────────────────────────────

    public function test_import_route_excludes_prevent_request_forgery(): void
    {
        $route = app('router')->getRoutes()->match(
            request()->create('/internal/import', 'POST')
        );

        $this->assertNotNull($route, 'Route /internal/import not found');
        $this->assertContains(
            PreventRequestForgery::class,
            $route->excludedMiddleware(),
            'PreventRequestForgery must be in excludedMiddleware for /internal/import'
        );
    }

    public function test_web_routes_still_have_prevent_request_forgery(): void
    {
        $route = app('router')->getRoutes()->match(
            request()->create('/questionnaire/start', 'POST')
        );

        $this->assertNotNull($route, 'Route /questionnaire/start not found');
        $this->assertNotContains(
            PreventRequestForgery::class,
            $route->excludedMiddleware(),
            'PreventRequestForgery must NOT be excluded from /questionnaire/start'
        );
    }
}
