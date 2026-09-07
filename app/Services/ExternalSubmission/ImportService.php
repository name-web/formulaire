<?php

namespace App\Services\ExternalSubmission;

use App\Models\PendingSubmission;
use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportService
{
    public function __construct(
        private SubmissionService $submissionService,
    ) {}

    public function import(string $uuid, array $clientData, array $projectData, ?array $answersData = null, ?array $filesData = null): array
    {
        $pending = PendingSubmission::where('uuid', $uuid)->first();

        if (! $pending) {
            return ['success' => false, 'error' => 'Submission not found', 'code' => 404];
        }

        if ($pending->status === 'imported') {
            return ['success' => true, 'message' => 'Already imported', 'code' => 200];
        }

        if ($pending->status !== 'sent') {
            return ['success' => false, 'error' => 'Invalid status: '.$pending->status, 'code' => 422];
        }

        return DB::transaction(function () use ($pending, $clientData, $projectData, $answersData) {
            try {
                $lockKey = 'import_lock_'.$pending->uuid;
                $lock = DB::select('SELECT GET_LOCK(?, 10) AS acquired', [$lockKey]);

                if (! $lock[0]->acquired) {
                    return ['success' => false, 'error' => 'Import already in progress', 'code' => 409];
                }

                $pending->refresh();
                if ($pending->status === 'imported') {
                    DB::select('SELECT RELEASE_LOCK(?)', [$lockKey]);

                    return ['success' => true, 'message' => 'Already imported', 'code' => 200];
                }

                // Merge incoming data with pending data for the shared service.
                // files_data always comes from the pending (the external system
                // does not re-send file contents).
                $pending->update([
                    'client_data' => array_merge($pending->client_data ?? [], $clientData),
                    'project_data' => array_merge($pending->project_data ?? [], $projectData),
                    'answers_data' => $answersData ?? $pending->answers_data ?? [],
                ]);
                $pending->refresh();

                $project = $this->submissionService->submitPendingSubmission($pending);

                DB::select('SELECT RELEASE_LOCK(?)', [$lockKey]);

                Log::info('Submission imported successfully.', [
                    'uuid' => $pending->uuid,
                    'project_id' => $project->id,
                ]);

                return ['success' => true, 'project_id' => $project->id, 'code' => 200];
            } catch (\Exception $e) {
                Log::error('Import failed.', [
                    'uuid' => $pending->uuid,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }
}
