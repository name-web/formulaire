<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Services\ExternalSubmission\ExternalSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_submission_is_sent_successfully(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => ['1' => ['text_value' => 'Test answer']],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);
        $result = $service->submit($pending);

        $this->assertTrue($result);

        $pending->refresh();
        $this->assertEquals('sent', $pending->status);
        $this->assertNotNull($pending->sent_at);
    }

    public function test_submission_fails_on_http_error(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['error' => 'Server error'], 500),
        ]);

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
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
        $this->assertNotNull($pending->failure_reason);
    }

    public function test_submission_fails_when_not_configured(): void
    {
        config(['external_submission.url' => '']);
        config(['external_submission.token' => '']);

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
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

    public function test_submission_does_not_create_client_or_project(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('answers', 0);
    }

    public function test_uuid_is_correctly_transmitted(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        Http::assertSent(function ($request) use ($pending) {
            return $request->url() === 'https://external.example.com/api/submissions'
                && str_contains($request->body(), $pending->uuid);
        });
    }

    public function test_client_data_is_correctly_transmitted(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);

        $clientData = ['name' => 'Jean Dupont', 'phone' => '+22501020304'];
        $pending = PendingSubmission::create([
            'client_data' => $clientData,
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Jean Dupont')
                && str_contains($request->body(), '+22501020304');
        });
    }

    public function test_authentication_is_used(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'my-secret-token']);

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

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization')
                && str_contains($request->header('Authorization')[0], 'my-secret-token');
        });
    }

    public function test_uuid_is_idempotent(): void
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

        $pending->update(['status' => 'collecting']);
        $service->submit($pending);
        $pending->refresh();
        $this->assertEquals('sent', $pending->status);

        $this->assertDatabaseCount('pending_submissions', 1);
    }

    public function test_no_regression_on_existing_projects(): void
    {
        $this->assertDatabaseHas('questions', ['title' => 'Quel est votre objectif principal ?']);
    }
}
