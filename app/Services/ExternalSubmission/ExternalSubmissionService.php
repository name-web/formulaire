<?php

namespace App\Services\ExternalSubmission;

use App\Models\PendingSubmission;
use App\Models\Question;
use App\Services\Questionnaire\PendingSubmissionService;
use Illuminate\Support\Facades\Log;

class ExternalSubmissionService
{
    public function __construct(
        private ExternalSubmissionClient $client,
        private PendingSubmissionService $pendingSubmissionService,
    ) {}

    public function submit(PendingSubmission $pending): bool
    {
        if (! $this->client->isConfigured()) {
            Log::warning('External submission system is not configured.', [
                'pending_uuid' => $pending->uuid,
            ]);

            $this->pendingSubmissionService->markAsFailed(
                $pending,
                'External system not configured'
            );

            return false;
        }

        $this->pendingSubmissionService->markAsSending($pending);

        $this->ensureSnapshotExists($pending);

        try {
            $data = $this->buildSubmissionData($pending);
            $files = $pending->files_data ?? [];

            $response = $this->client->sendSubmission($data, $files);

            if ($response->successful()) {
                $this->pendingSubmissionService->markAsSent($pending);

                Log::info('External submission sent successfully.', [
                    'pending_uuid' => $pending->uuid,
                    'status_code' => $response->status(),
                ]);

                return true;
            }

            $this->handleFailure(
                $pending,
                'HTTP '.$response->status(),
                $response->body()
            );

            return false;
        } catch (\Exception $e) {
            $this->handleFailure(
                $pending,
                get_class($e),
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Ensure a frozen snapshot exists on the PendingSubmission.
     * On first send, builds and persists the snapshot.
     * On retry, reuses the existing snapshot without reconstruction.
     */
    private function ensureSnapshotExists(PendingSubmission $pending): void
    {
        if ($pending->snapshot_data !== null) {
            Log::info('Reusing existing snapshot for retry.', [
                'pending_uuid' => $pending->uuid,
            ]);

            return;
        }

        $snapshot = $this->buildSnapshot($pending);
        $pending->update(['snapshot_data' => $snapshot]);
        $pending->refresh();

        Log::info('Snapshot created for submission.', [
            'pending_uuid' => $pending->uuid,
        ]);
    }

    /**
     * Build a complete, denormalized snapshot of the questionnaire as filled by the client.
     *
     * This freezes all question metadata, option labels, answers, and file references
     * so that retries always send identical data regardless of subsequent admin edits.
     */
    private function buildSnapshot(PendingSubmission $pending): array
    {
        $answersData = $pending->answers_data ?? [];
        $questionIds = array_map('intval', array_keys($answersData));

        if (empty($questionIds)) {
            return [
                'uuid' => $pending->uuid,
                'client_data' => $pending->client_data,
                'project_data' => $pending->project_data,
                'answers_data' => [],
                'files_data' => $pending->files_data ?? [],
                'frozen_at' => now()->toIso8601String(),
            ];
        }

        $allQuestions = Question::with('options')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $frozenAnswers = [];

        foreach ($answersData as $questionId => $answerData) {
            $questionId = (int) $questionId;
            $question = $allQuestions->get($questionId);

            if (! $question) {
                // Question was deleted or not found — preserve raw data as-is
                $frozenAnswers[$questionId] = array_merge($answerData, [
                    'question_title' => null,
                    'question_description' => null,
                    'question_type' => null,
                    'group_label' => null,
                    'placeholder' => null,
                    'help_text' => null,
                    'is_required' => null,
                    'sort_order' => null,
                    'options' => [],
                    'selected_option_labels' => [],
                ]);

                continue;
            }

            $allOptions = $question->options
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($opt) => [
                    'id' => $opt->id,
                    'label' => $opt->label,
                    'value' => $opt->value,
                    'sort_order' => $opt->sort_order,
                ])
                ->toArray();

            $selectedOptionIds = $answerData['option_ids'] ?? [];
            $selectedOptionLabels = $question->options
                ->whereIn('id', $selectedOptionIds)
                ->pluck('label', 'id')
                ->toArray();

            $frozenAnswers[$questionId] = [
                'question_id' => $questionId,
                'question_title' => $question->title,
                'question_description' => $question->description,
                'question_type' => $question->type,
                'group_label' => $question->group_label,
                'placeholder' => $question->placeholder,
                'help_text' => $question->help_text,
                'is_required' => $question->is_required,
                'sort_order' => $question->sort_order,
                'text_value' => $answerData['text_value'] ?? null,
                'other_value' => $answerData['other_value'] ?? null,
                'file_path' => $answerData['file_path'] ?? null,
                'selected_option_ids' => $selectedOptionIds,
                'selected_option_labels' => $selectedOptionLabels,
                'options' => $allOptions,
            ];
        }

        return [
            'uuid' => $pending->uuid,
            'client_data' => $pending->client_data,
            'project_data' => $pending->project_data,
            'answers_data' => $frozenAnswers,
            'files_data' => $pending->files_data ?? [],
            'frozen_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Build the HTTP payload from the frozen snapshot if available,
     * falling back to raw PendingSubmission data for legacy records.
     */
    private function buildSubmissionData(PendingSubmission $pending): array
    {
        if ($pending->snapshot_data !== null) {
            return $this->buildPayloadFromSnapshot($pending->snapshot_data);
        }

        Log::warning('No snapshot found for pending submission, using raw data fallback.', [
            'pending_uuid' => $pending->uuid,
        ]);

        return [
            'uuid' => $pending->uuid,
            'client_data' => $pending->client_data,
            'project_data' => $pending->project_data,
            'answers_data' => $pending->answers_data,
            'files_data' => collect($pending->files_data ?? [])->map(function ($file) {
                return [
                    'original_name' => $file['original_name'] ?? '',
                    'mime_type' => $file['mime_type'] ?? null,
                    'size' => $file['size'] ?? 0,
                ];
            })->toArray(),
        ];
    }

    /**
     * Build the HTTP payload from a frozen snapshot.
     *
     * Passes through the full snapshot answers_data (including all question metadata,
     * option labels, and answer values) so the external system receives a complete,
     * self-contained representation of the submission.
     */
    private function buildPayloadFromSnapshot(array $snapshot): array
    {
        return [
            'uuid' => $snapshot['uuid'],
            'client_data' => $snapshot['client_data'],
            'project_data' => $snapshot['project_data'],
            'answers_data' => $snapshot['answers_data'] ?? [],
            'files_data' => collect($snapshot['files_data'] ?? [])->map(function ($file) {
                return [
                    'original_name' => $file['original_name'] ?? '',
                    'mime_type' => $file['mime_type'] ?? null,
                    'size' => $file['size'] ?? 0,
                ];
            })->toArray(),
        ];
    }

    private function handleFailure(PendingSubmission $pending, string $reason, ?string $error = null): void
    {
        $this->pendingSubmissionService->markAsFailed($pending, $reason, $error);

        Log::error('External submission failed.', [
            'pending_uuid' => $pending->uuid,
            'reason' => $reason,
            'error' => $error,
        ]);
    }
}
