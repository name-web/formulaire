<?php

namespace App\Services\ExternalSubmission;

use App\Models\Answer;
use App\Models\AnswerOption;
use App\Models\Client;
use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    public function import(string $uuid, array $clientData, array $projectData, array $answersData, array $filesData): array
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

        return DB::transaction(function () use ($pending, $clientData, $projectData, $answersData, $filesData) {
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

                $client = $this->findOrCreateClient($clientData);
                $project = $this->createProject($client, $projectData);
                $this->createAnswers($project, $answersData);
                $this->createProjectFiles($project, $pending, $filesData);

                $pending->markAsImported();

                DB::select('SELECT RELEASE_LOCK(?)', [$lockKey]);

                Log::info('Submission imported successfully.', [
                    'uuid' => $pending->uuid,
                    'project_id' => $project->id,
                    'client_id' => $client->id,
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

    private function findOrCreateClient(array $clientData): Client
    {
        $phone = trim($clientData['phone'] ?? '');
        $email = ($phone ? preg_replace('/[^0-9]/', '', $phone) : 'noemail').'@dominium.tech';

        $client = Client::where('email', $email)->first();

        if ($client) {
            $client->update(array_filter([
                'name' => $clientData['name'] ?? null,
                'phone' => $clientData['phone'] ?? null,
                'company_name' => $clientData['company_name'] ?? null,
                'whatsapp' => $clientData['whatsapp'] ?? null,
                'city' => $clientData['city'] ?? null,
                'country' => $clientData['country'] ?? null,
            ], fn ($v) => $v !== null));
        } else {
            $client = Client::create([
                'name' => $clientData['name'] ?? '',
                'email' => $email,
                'phone' => $clientData['phone'] ?? '',
                'company_name' => $clientData['company_name'] ?? null,
                'whatsapp' => $clientData['whatsapp'] ?? null,
                'city' => $clientData['city'] ?? null,
                'country' => $clientData['country'] ?? "Cote d'Ivoire",
            ]);
        }

        return $client;
    }

    private function createProject(Client $client, array $projectData): Project
    {
        $types = $projectData['project_types'] ?? [];

        return Project::create([
            'client_id' => $client->id,
            'project_type' => $types,
            'title' => $projectData['title'] ?? null,
            'description' => $projectData['description'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    private function createAnswers(Project $project, array $answersData): void
    {
        foreach ($answersData as $questionId => $answerData) {
            $questionId = (int) $questionId;
            $question = Question::find($questionId);

            if (! $question) {
                continue;
            }

            $answer = Answer::create([
                'project_id' => $project->id,
                'question_id' => $questionId,
                'text_value' => $answerData['text_value'] ?? null,
                'file_path' => $answerData['file_path'] ?? null,
                'other_value' => $answerData['other_value'] ?? null,
            ]);

            $optionIds = $answerData['option_ids'] ?? [];
            foreach ($optionIds as $optionId) {
                AnswerOption::create([
                    'answer_id' => $answer->id,
                    'question_option_id' => (int) $optionId,
                ]);
            }
        }
    }

    private function createProjectFiles(Project $project, PendingSubmission $pending, array $filesData): void
    {
        $pendingFiles = $pending->files_data ?? [];

        foreach ($pendingFiles as $fileData) {
            $storedPath = $fileData['stored_path'] ?? '';
            $originalName = $fileData['original_name'] ?? 'file';

            $newPath = 'project-files/'.$project->id.'/'.$originalName;
            $fullOldPath = Storage::disk('local')->path($storedPath);
            $fullNewPath = Storage::disk('local')->path($newPath);

            if (file_exists($fullOldPath)) {
                Storage::disk('local')->copy($storedPath, $newPath);
            }

            ProjectFile::create([
                'project_id' => $project->id,
                'original_name' => $originalName,
                'stored_path' => $newPath,
                'mime_type' => $fileData['mime_type'] ?? null,
                'size' => $fileData['size'] ?? 0,
            ]);
        }
    }
}
