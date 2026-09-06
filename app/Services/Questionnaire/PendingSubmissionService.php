<?php

namespace App\Services\Questionnaire;

use App\Models\PendingSubmission;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PendingSubmissionService
{
    public function createDraft(array $clientData, string|array $projectTypes): PendingSubmission
    {
        $types = is_array($projectTypes) ? $projectTypes : [$projectTypes];

        return PendingSubmission::create([
            'client_data' => $clientData,
            'project_data' => ['project_types' => $types],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);
    }

    public function find(string $uuid): ?PendingSubmission
    {
        return PendingSubmission::where('uuid', $uuid)->first();
    }

    public function findOrFail(string $uuid): PendingSubmission
    {
        return PendingSubmission::where('uuid', $uuid)->firstOrFail();
    }

    public function saveAnswer(PendingSubmission $pending, int $questionId, array $data): void
    {
        $answers = $pending->answers_data ?? [];

        $question = Question::find($questionId);
        if (! $question) {
            return;
        }

        $answerData = [
            'question_id' => $questionId,
            'text_value' => $data['value'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'other_value' => $data['other_value'] ?? null,
            'option_ids' => $data['option_ids'] ?? [],
        ];

        $answers[$questionId] = $answerData;

        $pending->update(['answers_data' => $answers]);
    }

    public function getAnswer(PendingSubmission $pending, int $questionId): ?array
    {
        $answers = $pending->answers_data ?? [];

        return $answers[$questionId] ?? null;
    }

    public function getAnswersCollection(PendingSubmission $pending): Collection
    {
        $answers = $pending->answers_data ?? [];

        return collect($answers)->map(function ($answerData) {
            return (object) [
                'question_id' => $answerData['question_id'],
                'text_value' => $answerData['text_value'] ?? null,
                'file_path' => $answerData['file_path'] ?? null,
                'other_value' => $answerData['other_value'] ?? null,
                'option_ids' => $answerData['option_ids'] ?? [],
            ];
        });
    }

    public function getAnsweredQuestionIds(PendingSubmission $pending): array
    {
        $answers = $pending->answers_data ?? [];

        return array_map('intval', array_keys($answers));
    }

    public function hasAnswer(PendingSubmission $pending, int $questionId): bool
    {
        $answers = $pending->answers_data ?? [];

        return isset($answers[$questionId]);
    }

    public function storeAnswerFile(PendingSubmission $pending, UploadedFile $file, int $questionId): string
    {
        $path = $file->store('pending-submissions/'.$pending->uuid.'/answers', 'local');

        $answers = $pending->answers_data ?? [];
        if (isset($answers[$questionId])) {
            $answers[$questionId]['file_path'] = $path;
            $pending->update(['answers_data' => $answers]);
        }

        return $path;
    }

    public function storeFile(PendingSubmission $pending, UploadedFile $file): array
    {
        $path = $file->store('pending-submissions/'.$pending->uuid, 'local');

        $fileData = [
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];

        $files = $pending->files_data ?? [];
        $files[] = $fileData;
        $pending->update(['files_data' => $files]);

        return $fileData;
    }

    public function deleteFile(PendingSubmission $pending, int $fileIndex): bool
    {
        $files = $pending->files_data ?? [];

        if (! isset($files[$fileIndex])) {
            return false;
        }

        $file = $files[$fileIndex];
        Storage::disk('local')->delete($file['stored_path']);

        array_splice($files, $fileIndex, 1);
        $pending->update(['files_data' => $files]);

        return true;
    }

    public function getFiles(PendingSubmission $pending): array
    {
        return $pending->files_data ?? [];
    }

    public function markAsSending(PendingSubmission $pending): void
    {
        $pending->update([
            'status' => 'sending',
        ]);
    }

    public function markAsSent(PendingSubmission $pending): void
    {
        $pending->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(PendingSubmission $pending, string $reason, ?string $error = null): void
    {
        $pending->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'last_error' => $error,
        ]);
    }

    public function markAsImported(PendingSubmission $pending): void
    {
        $pending->update([
            'status' => 'imported',
            'imported_at' => now(),
        ]);
    }

    public function getClientData(PendingSubmission $pending): array
    {
        return $pending->client_data ?? [];
    }

    public function getProjectTypes(PendingSubmission $pending): array
    {
        return $pending->project_data['project_types'] ?? [];
    }
}
