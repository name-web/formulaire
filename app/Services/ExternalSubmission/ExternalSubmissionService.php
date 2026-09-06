<?php

namespace App\Services\ExternalSubmission;

use App\Models\PendingSubmission;
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

    private function buildSubmissionData(PendingSubmission $pending): array
    {
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
