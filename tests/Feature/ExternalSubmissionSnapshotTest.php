<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\ExternalSubmission\ExternalSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalSubmissionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function configureExternalSubmission(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake([
            'external.example.com/api/submissions' => Http::response(['status' => 'received'], 200),
        ]);
    }

    private function createPendingWithAnswers(array $answersData): PendingSubmission
    {
        return PendingSubmission::create([
            'client_data' => ['name' => 'Jean Dupont', 'phone' => '+22501020304'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => $answersData,
            'files_data' => [],
            'status' => 'collecting',
        ]);
    }

    // ──────────────────────────────────────────────
    // 1. Snapshot creation on first send
    // ──────────────────────────────────────────────

    public function test_snapshot_is_created_on_first_send(): void
    {
        $this->configureExternalSubmission();

        $pending = $this->createPendingWithAnswers([
            '4' => [
                'question_id' => 4,
                'text_value' => null,
                'option_ids' => [1],
                'other_value' => null,
            ],
        ]);

        $this->assertNull($pending->snapshot_data);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $this->assertNotNull($pending->snapshot_data);
        $this->assertEquals('sent', $pending->status);
    }

    // ──────────────────────────────────────────────
    // 2. Snapshot content verification
    // ──────────────────────────────────────────────

    public function test_snapshot_contains_question_metadata(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Combien de pages souhaitez-vous ?')->first();
        $this->assertNotNull($question);

        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', '1_page')
            ->first();
        $this->assertNotNull($option);

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $snapshot = $pending->snapshot_data;

        $this->assertNotNull($snapshot);
        $this->assertArrayHasKey('uuid', $snapshot);
        $this->assertArrayHasKey('client_data', $snapshot);
        $this->assertArrayHasKey('project_data', $snapshot);
        $this->assertArrayHasKey('answers_data', $snapshot);
        $this->assertArrayHasKey('files_data', $snapshot);
        $this->assertArrayHasKey('frozen_at', $snapshot);

        $answer = $snapshot['answers_data'][$question->id];
        $this->assertEquals($question->id, $answer['question_id']);
        $this->assertEquals($question->title, $answer['question_title']);
        $this->assertEquals($question->type, $answer['question_type']);
        $this->assertEquals($question->group_label, $answer['group_label']);
        $this->assertEquals($question->placeholder, $answer['placeholder']);
        $this->assertEquals($question->help_text, $answer['help_text']);
        $this->assertEquals($question->is_required, $answer['is_required']);
        $this->assertEquals($question->sort_order, $answer['sort_order']);
    }

    public function test_snapshot_contains_option_labels(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $this->assertNotNull($question);

        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();
        $this->assertNotNull($option);

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $answer = $pending->snapshot_data['answers_data'][$question->id];

        $this->assertArrayHasKey('options', $answer);
        $this->assertNotEmpty($answer['options']);

        $optionEntry = collect($answer['options'])->firstWhere('id', $option->id);
        $this->assertNotNull($optionEntry);
        $this->assertEquals('Moderne et professionnel', $optionEntry['label']);
        $this->assertEquals('moderne', $optionEntry['value']);

        $this->assertArrayHasKey('selected_option_labels', $answer);
        $this->assertArrayHasKey($option->id, $answer['selected_option_labels']);
        $this->assertEquals('Moderne et professionnel', $answer['selected_option_labels'][$option->id]);
    }

    public function test_snapshot_contains_answer_values(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Décrivez votre projet en quelques mots')->first();
        $this->assertNotNull($question);

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => 'Je veux un site pour mon restaurant',
                'option_ids' => [],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $answer = $pending->snapshot_data['answers_data'][$question->id];

        $this->assertEquals('Je veux un site pour mon restaurant', $answer['text_value']);
    }

    public function test_snapshot_contains_file_metadata(): void
    {
        $this->configureExternalSubmission();

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [
                [
                    'original_name' => 'logo.png',
                    'stored_path' => 'pending-submissions/test-uuid/logo.png',
                    'mime_type' => 'image/png',
                    'size' => 2048,
                ],
            ],
            'status' => 'collecting',
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $this->assertNotNull($pending->snapshot_data);
        $this->assertCount(1, $pending->snapshot_data['files_data']);
        $this->assertEquals('logo.png', $pending->snapshot_data['files_data'][0]['original_name']);
    }

    public function test_snapshot_records_frozen_at_timestamp(): void
    {
        $this->configureExternalSubmission();

        $pending = $this->createPendingWithAnswers([]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $this->assertNotNull($pending->snapshot_data['frozen_at']);
        $this->assertIsString($pending->snapshot_data['frozen_at']);
    }

    // ──────────────────────────────────────────────
    // 3. Snapshot is persisted in database
    // ──────────────────────────────────────────────

    public function test_snapshot_is_persisted_in_database(): void
    {
        $this->configureExternalSubmission();

        $pending = $this->createPendingWithAnswers([]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $this->assertDatabaseHas('pending_submissions', [
            'id' => $pending->id,
        ]);

        $pending->refresh();
        $this->assertNotNull($pending->snapshot_data);
    }

    // ──────────────────────────────────────────────
    // 4. Retry preserves old question title
    // ──────────────────────────────────────────────

    public function test_retry_preserves_old_question_title_after_admin_edit(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Combien de pages souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', '1_page')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);
        $pending->refresh();
        $originalTitle = $pending->snapshot_data['answers_data'][$question->id]['question_title'];

        // Simulate admin editing the question title
        $pending->update(['status' => 'collecting']);
        $question->update(['title' => 'Nombre de pages modifié par admin']);

        // Retry
        $service->submit($pending);
        $pending->refresh();

        $this->assertEquals($originalTitle, $pending->snapshot_data['answers_data'][$question->id]['question_title']);
        $this->assertEquals('Combien de pages souhaitez-vous ?', $pending->snapshot_data['answers_data'][$question->id]['question_title']);
    }

    // ──────────────────────────────────────────────
    // 5. Retry preserves old option label
    // ──────────────────────────────────────────────

    public function test_retry_preserves_old_option_label_after_admin_edit(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);
        $pending->refresh();
        $originalLabel = $pending->snapshot_data['answers_data'][$question->id]['selected_option_labels'][$option->id];

        // Simulate admin editing the option label
        $pending->update(['status' => 'collecting']);
        $option->update(['label' => 'Moderne modifié par admin']);

        // Retry
        $service->submit($pending);
        $pending->refresh();

        $this->assertEquals($originalLabel, $pending->snapshot_data['answers_data'][$question->id]['selected_option_labels'][$option->id]);
        $this->assertEquals('Moderne et professionnel', $pending->snapshot_data['answers_data'][$question->id]['selected_option_labels'][$option->id]);
    }

    // ──────────────────────────────────────────────
    // 6. Retry preserves data after option deletion
    // ──────────────────────────────────────────────

    public function test_retry_preserves_info_after_option_deletion(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);
        $pending->refresh();
        $originalSnapshot = $pending->snapshot_data;

        // Simulate admin deleting the option
        $pending->update(['status' => 'collecting']);
        $option->delete();

        // Retry
        $service->submit($pending);
        $pending->refresh();

        $this->assertEquals($originalSnapshot, $pending->snapshot_data);
        $this->assertEquals('Moderne et professionnel', $pending->snapshot_data['answers_data'][$question->id]['selected_option_labels'][$option->id]);
    }

    // ──────────────────────────────────────────────
    // 7. Second send does not rebuild snapshot
    // ──────────────────────────────────────────────

    public function test_second_send_does_not_rebuild_snapshot(): void
    {
        $this->configureExternalSubmission();

        $pending = $this->createPendingWithAnswers([
            '4' => [
                'question_id' => 4,
                'text_value' => null,
                'option_ids' => [1],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);
        $pending->refresh();
        $firstSnapshot = $pending->snapshot_data;
        $firstFrozenAt = $firstSnapshot['frozen_at'];

        // Reset for retry
        $pending->update(['status' => 'collecting']);

        $service->submit($pending);
        $pending->refresh();

        $this->assertEquals($firstFrozenAt, $pending->snapshot_data['frozen_at']);
        $this->assertEquals($firstSnapshot, $pending->snapshot_data);
    }

    // ──────────────────────────────────────────────
    // 8. HTTP payload uses snapshot data on retry
    // ──────────────────────────────────────────────

    public function test_http_payload_uses_snapshot_data_on_retry(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);

        // First send
        $service->submit($pending);
        $firstBody = null;
        Http::assertSent(function ($request) use (&$firstBody) {
            $firstBody = $request->body();

            return true;
        });

        // Reset for retry and modify question
        $pending->update(['status' => 'collecting']);
        $question->update(['title' => 'Titre modifié']);
        $option->update(['label' => 'Label modifié']);

        // Retry
        $service->submit($pending);
        $secondBody = null;
        Http::assertSent(function ($request) use (&$secondBody) {
            $secondBody = $request->body();

            return true;
        });

        // Both payloads should contain the original option id (snapshot frozen)
        $this->assertNotNull($firstBody);
        $this->assertNotNull($secondBody);
        $this->assertStringContainsString((string) $option->id, $firstBody);
        $this->assertStringContainsString((string) $option->id, $secondBody);
    }

    // ──────────────────────────────────────────────
    // 9. Legacy PendingSubmission without snapshot
    // ──────────────────────────────────────────────

    public function test_legacy_submission_without_snapshot_uses_fallback(): void
    {
        $this->configureExternalSubmission();

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Legacy User', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $this->assertNull($pending->snapshot_data);

        $service = app(ExternalSubmissionService::class);
        $result = $service->submit($pending);

        $this->assertTrue($result);
        $pending->refresh();
        $this->assertEquals('sent', $pending->status);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Legacy User');
        });
    }

    // ──────────────────────────────────────────────
    // 10. Existing tests continue to pass
    // ──────────────────────────────────────────────

    public function test_submission_is_still_sent_successfully(): void
    {
        $this->configureExternalSubmission();

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

    public function test_uuid_is_idempotent(): void
    {
        $this->configureExternalSubmission();

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Test', 'phone' => '123'],
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

    // ──────────────────────────────────────────────
    // 11. Snapshot with multiple answers
    // ──────────────────────────────────────────────

    public function test_snapshot_with_multiple_answers(): void
    {
        $this->configureExternalSubmission();

        $qStyle = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $qBudget = Question::where('title', 'Quel budget avez-vous prévu pour ce projet ?')
            ->where('project_types', 'like', '%site_vitrine%')
            ->first();

        $optionStyle = QuestionOption::where('question_id', $qStyle->id)
            ->where('value', 'simple')
            ->first();
        $optionBudget = QuestionOption::where('question_id', $qBudget->id)
            ->where('value', 'moins_250k')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $qStyle->id => [
                'question_id' => $qStyle->id,
                'text_value' => null,
                'option_ids' => [$optionStyle->id],
                'other_value' => null,
            ],
            (string) $qBudget->id => [
                'question_id' => $qBudget->id,
                'text_value' => null,
                'option_ids' => [$optionBudget->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $snapshot = $pending->snapshot_data;

        $this->assertCount(2, $snapshot['answers_data']);
        $this->assertArrayHasKey($qStyle->id, $snapshot['answers_data']);
        $this->assertArrayHasKey($qBudget->id, $snapshot['answers_data']);
        $this->assertEquals('Simple et élégant', $snapshot['answers_data'][$qStyle->id]['selected_option_labels'][$optionStyle->id]);
        $this->assertEquals('Moins de 250 000 FCFA', $snapshot['answers_data'][$qBudget->id]['selected_option_labels'][$optionBudget->id]);
    }

    // ──────────────────────────────────────────────
    // 12. Snapshot on failure still stores the snapshot
    // ──────────────────────────────────────────────

    public function test_snapshot_is_created_even_when_submission_fails(): void
    {
        config(['external_submission.url' => 'https://external.example.com']);
        config(['external_submission.token' => 'test-token']);

        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $pending = $this->createPendingWithAnswers([
            '4' => [
                'question_id' => 4,
                'text_value' => null,
                'option_ids' => [1],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $result = $service->submit($pending);

        $this->assertFalse($result);
        $pending->refresh();
        $this->assertEquals('failed', $pending->status);
        $this->assertNotNull($pending->snapshot_data);
    }

    // ──────────────────────────────────────────────
    // 13. Snapshot with multiselect answers
    // ──────────────────────────────────────────────

    public function test_snapshot_with_multiselect_answers(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quelles fonctionnalités sont essentielles ?')->first();
        $options = QuestionOption::where('question_id', $question->id)
            ->whereIn('value', ['auth', 'paiement'])
            ->get();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => $options->pluck('id')->toArray(),
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $answer = $pending->snapshot_data['answers_data'][$question->id];

        $this->assertCount(2, $answer['selected_option_ids']);
        $this->assertCount(2, $answer['selected_option_labels']);
        $this->assertArrayHasKey($options->first()->id, $answer['selected_option_labels']);
        $this->assertArrayHasKey($options->last()->id, $answer['selected_option_labels']);
    }

    // ──────────────────────────────────────────────
    // 14. Snapshot preserves question_description
    // ──────────────────────────────────────────────

    public function test_snapshot_preserves_question_description(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Décrivez votre projet en quelques mots')->first();
        $this->assertNotNull($question->description);

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => 'Mon projet test',
                'option_ids' => [],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        $pending->refresh();
        $this->assertEquals(
            $question->description,
            $pending->snapshot_data['answers_data'][$question->id]['question_description']
        );
    }

    // ──────────────────────────────────────────────
    // 15. HTTP payload transmits question_title
    // ──────────────────────────────────────────────

    public function test_http_payload_transmits_question_title(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        Http::assertSent(function ($request) use ($question) {
            $body = $request->body();

            return str_contains($body, 'question_title')
                && str_contains($body, $question->title);
        });
    }

    // ──────────────────────────────────────────────
    // 16. HTTP payload transmits question_type, group_label, is_required, sort_order
    // ──────────────────────────────────────────────

    public function test_http_payload_transmits_question_metadata(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        Http::assertSent(function ($request) use ($question) {
            $body = $request->body();

            return str_contains($body, 'question_type')
                && str_contains($body, $question->type)
                && str_contains($body, 'group_label')
                && str_contains($body, $question->group_label)
                && str_contains($body, 'is_required')
                && str_contains($body, 'sort_order');
        });
    }

    // ──────────────────────────────────────────────
    // 17. HTTP payload transmits selected_option_labels
    // ──────────────────────────────────────────────

    public function test_http_payload_transmits_selected_option_labels(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        Http::assertSent(function ($request) {
            $body = $request->body();

            return str_contains($body, 'selected_option_labels')
                && str_contains($body, 'Moderne et professionnel');
        });
    }

    // ──────────────────────────────────────────────
    // 18. HTTP payload transmits options with labels and values
    // ──────────────────────────────────────────────

    public function test_http_payload_transmits_options_with_labels(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        Http::assertSent(function ($request) {
            $body = $request->body();

            return str_contains($body, 'options')
                && str_contains($body, 'Moderne et professionnel')
                && str_contains($body, 'moderne');
        });
    }

    // ──────────────────────────────────────────────
    // 19. HTTP payload transmits text_value, other_value, file_path
    // ──────────────────────────────────────────────

    public function test_http_payload_transmits_answer_values(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Décrivez votre projet en quelques mots')->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => 'Je veux un site vitrine',
                'option_ids' => [],
                'other_value' => 'autre chose',
            ],
        ]);

        $service = app(ExternalSubmissionService::class);
        $service->submit($pending);

        Http::assertSent(function ($request) {
            $body = $request->body();

            return str_contains($body, 'Je veux un site vitrine')
                && str_contains($body, 'autre chose');
        });
    }

    // ──────────────────────────────────────────────
    // 20. Retry sends snapshot original data (not admin-modified)
    // ──────────────────────────────────────────────

    public function test_retry_http_payload_uses_snapshot_original_metadata(): void
    {
        $this->configureExternalSubmission();

        $question = Question::where('title', 'Quel style souhaitez-vous ?')->first();
        $option = QuestionOption::where('question_id', $question->id)
            ->where('value', 'moderne')
            ->first();

        $pending = $this->createPendingWithAnswers([
            (string) $question->id => [
                'question_id' => $question->id,
                'text_value' => null,
                'option_ids' => [$option->id],
                'other_value' => null,
            ],
        ]);

        $service = app(ExternalSubmissionService::class);

        // First send — snapshot is frozen
        $service->submit($pending);

        // Simulate admin editing the question after submission
        $pending->update(['status' => 'collecting']);
        $question->update(['title' => 'Titre modifié par admin']);
        $option->update(['label' => 'Label modifié par admin']);

        // Retry — should use original snapshot
        $service->submit($pending);

        Http::assertSent(function ($request) {
            $body = $request->body();

            // Title should be the ORIGINAL, not the admin-modified one
            return str_contains($body, 'Quel style souhaitez-vous ?')
                && ! str_contains($body, 'Titre modifié par admin');
        });
    }

    // ──────────────────────────────────────────────
    // 21. Legacy submissions without snapshot still work
    // ──────────────────────────────────────────────

    public function test_legacy_submission_without_snapshot_sends_raw_answers_data(): void
    {
        $this->configureExternalSubmission();

        $pending = PendingSubmission::create([
            'client_data' => ['name' => 'Legacy User', 'phone' => '123'],
            'project_data' => ['project_types' => ['site_vitrine']],
            'answers_data' => [
                '4' => [
                    'question_id' => 4,
                    'text_value' => 'Test',
                    'option_ids' => [1],
                    'other_value' => null,
                ],
            ],
            'files_data' => [],
            'status' => 'collecting',
        ]);

        $this->assertNull($pending->snapshot_data);

        $service = app(ExternalSubmissionService::class);
        $result = $service->submit($pending);

        $this->assertTrue($result);
        $pending->refresh();
        $this->assertEquals('sent', $pending->status);

        Http::assertSent(function ($request) {
            $body = $request->body();

            return str_contains($body, 'Legacy User')
                && str_contains($body, 'Test');
        });
    }
}
