<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Question;
use App\Services\ExternalSubmission\ExternalSubmissionService;
use App\Services\Questionnaire\ProjectService;
use App\Services\Questionnaire\QuestionnaireService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase5VerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['external_submission.import_secret' => 'test-secret-key']);
    }

    // ──────────────────────────────────────────────
    // 1. Flux complet : soumission → import
    // ──────────────────────────────────────────────

    public function test_full_flow_start_to_import_creates_correct_data(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);

        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'phone' => '+22501020304',
            'project_types' => ['site_vitrine'],
        ]);

        $pending = PendingSubmission::first();
        $this->assertNotNull($pending);
        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);

        $this->post('/questionnaire/submit');
        $pending->refresh();
        $this->assertEquals('sent', $pending->status);

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-External-Submission-Secret' => 'test-secret-key',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('clients', ['name' => 'Jean Dupont']);
        $this->assertDatabaseHas('projects', ['status' => 'submitted']);

        $pending->refresh();
        $this->assertEquals('imported', $pending->status);
    }

    // ──────────────────────────────────────────────
    // 3. Aucun Client/Project avant l'import
    // ──────────────────────────────────────────────

    public function test_no_client_or_project_during_collecting_phase(): void
    {
        $this->post('/questionnaire/start', [
            'name' => 'Test',
            'phone' => '+225000000',
            'project_types' => ['site_vitrine'],
        ]);

        $question = Question::where('type', 'textarea')->first();
        if ($question) {
            $this->post('/questionnaire/wizard', [
                'answers' => [$question->id => ['value' => 'Réponse test']],
            ]);
        }

        $this->get('/questionnaire/summary');

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('answers', 0);
    }

    // ──────────────────────────────────────────────
    // 5. Gestion des erreurs
    // ──────────────────────────────────────────────

    public function test_external_system_unavailable_marks_as_failed(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);
        $result = $service->submit($pending);

        $this->assertFalse($result);
        $pending->refresh();
        $this->assertEquals('failed', $pending->status);
        $this->assertStringContainsString('ConnectionException', $pending->failure_reason);
    }

    public function test_external_system_timeout_marks_as_failed(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out'));

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);
        $result = $service->submit($pending);

        $this->assertFalse($result);
        $pending->refresh();
        $this->assertEquals('failed', $pending->status);
    }

    public function test_import_rejects_missing_secret(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'sent',
        ]);

        $response = $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
        ]);

        $response->assertStatus(401);
    }

    public function test_import_rejects_nonexistent_submission(): void
    {
        $response = $this->postJson('/internal/import', [
            'submission_uuid' => '00000000-0000-0000-0000-000000000000',
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
        ], [
            'X-External-Submission-Secret' => 'test-secret-key',
        ]);

        $response->assertStatus(404);
    }

    // ──────────────────────────────────────────────
    // 6. Idempotence
    // ──────────────────────────────────────────────

    public function test_same_import_called_twice_does_not_duplicate(): void
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
        ];
        $headers = ['X-External-Submission-Secret' => 'test-secret-key'];

        $this->postJson('/internal/import', $data, $headers)->assertOk();
        $this->postJson('/internal/import', $data, $headers)->assertOk();

        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseCount('clients', 1);
    }

    public function test_same_submission_sent_twice_does_not_duplicate(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);

        $service->submit($pending);
        $pending->refresh();
        $this->assertEquals('sent', $pending->status);

        $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
        ], [
            'X-External-Submission-Secret' => 'test-secret-key',
        ])->assertOk();

        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseCount('clients', 1);
    }

    // ──────────────────────────────────────────────
    // 7. Sécurité — sessions isolées
    // ──────────────────────────────────────────────

    public function test_cannot_access_pending_submission_from_different_session(): void
    {
        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Owner'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $otherUuid = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $response = $this->withSession(['pending_submission_uuid' => $otherUuid])
            ->get('/questionnaire/wizard');

        $response->assertStatus(404);
    }

    // ──────────────────────────────────────────────
    // 8. Fichiers
    // ──────────────────────────────────────────────

    public function test_import_copies_files_to_permanent_storage(): void
    {
        Storage::fake('local');

        $tempDir = 'pending-submissions/test-uuid';
        $tempFile = 'test-file.pdf';
        Storage::disk('local')->put($tempDir.'/'.$tempFile, 'fake content');

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [
                [
                    'original_name' => 'test-file.pdf',
                    'stored_path' => $tempDir.'/'.$tempFile,
                    'mime_type' => 'application/pdf',
                    'size' => 1024,
                ],
            ],
            'status' => 'sent',
        ]);

        $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
        ], [
            'X-External-Submission-Secret' => 'test-secret-key',
        ])->assertOk();

        $project = Project::first();
        $this->assertNotNull($project);

        $this->assertDatabaseHas('project_files', [
            'project_id' => $project->id,
            'original_name' => 'test-file.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
        ]);
    }

    public function test_import_preserves_files_on_failure(): void
    {
        Storage::fake('local');

        $tempDir = 'pending-submissions/test-uuid';
        $tempFile = 'test-file.pdf';
        Storage::disk('local')->put($tempDir.'/'.$tempFile, 'fake content');

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [
                [
                    'original_name' => 'test-file.pdf',
                    'stored_path' => $tempDir.'/'.$tempFile,
                    'mime_type' => 'application/pdf',
                    'size' => 1024,
                ],
            ],
            'status' => 'collecting',
        ]);

        $this->postJson('/internal/import', [
            'submission_uuid' => $pending->uuid,
            'client_data' => ['name' => 'Test'],
            'project_data' => ['project_types' => ['site_vitrine']],
        ], [
            'X-External-Submission-Secret' => 'test-secret-key',
        ])->assertStatus(422);

        Storage::disk('local')->assertExists($tempDir.'/'.$tempFile);
    }

    // ──────────────────────────────────────────────
    // 9. Aucun chemin de création directe
    // ──────────────────────────────────────────────

    public function test_project_service_has_no_create_draft(): void
    {
        $service = new ProjectService;
        $this->assertFalse(method_exists($service, 'createDraft'));
    }

    public function test_questionnaire_service_has_no_start_session(): void
    {
        $service = app(QuestionnaireService::class);
        $this->assertFalse(method_exists($service, 'startSession'));
    }

    // ──────────────────────────────────────────────
    // 10. Pas de référence au nom du système externe
    // ──────────────────────────────────────────────

    public function test_no_ayoosi_reference_in_codebase(): void
    {
        $files = collect([
            app_path('Http/Controllers/ImportController.php'),
            app_path('Services/ExternalSubmission/ImportService.php'),
            app_path('Services/ExternalSubmission/ExternalSubmissionService.php'),
            app_path('Services/ExternalSubmission/ExternalSubmissionClient.php'),
            config_path('external_submission.php'),
        ]);

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->assertStringNotContainsString('Ayoosi', $content, "Found 'Ayoosi' in {$file}");
            $this->assertStringNotContainsString('ayoosi', $content, "Found 'ayoosi' in {$file}");
        }
    }
}
