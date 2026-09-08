<?php

namespace App\Services\Submission;

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

class SubmissionService
{
    /**
     * Convert a PendingSubmission into permanent records:
     * Client, Project, Answers, AnswerOptions, and ProjectFiles.
     *
     * Marks the PendingSubmission as 'imported' on success.
     * All operations run inside a DB transaction for atomicity.
     */
    public function submitPendingSubmission(PendingSubmission $pending): Project
    {
        return DB::transaction(function () use ($pending) {
            $client = $this->findOrCreateClient($pending->client_data);
            $project = $this->createProject($client, $pending->project_data);
            $this->createAnswers($project, $pending->answers_data);
            $this->createProjectFiles($project, $pending->files_data);

            $pending->markAsImported();

            Log::info('PendingSubmission converted to direct records.', [
                'pending_uuid' => $pending->uuid,
                'project_id' => $project->id,
                'client_id' => $client->id,
            ]);

            return $project;
        });
    }

    /**
     * Find an existing Client by phone, or create a new one.
     */
    public function findOrCreateClient(array $clientData): Client
    {
        $phone = trim($clientData['phone'] ?? '');
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $phone);

        $client = $normalizedPhone
            ? Client::where('phone', 'like', '%'.$normalizedPhone.'%')->first()
            : null;

        if ($client) {
            $client->update(array_filter([
                'name' => $clientData['name'] ?? null,
                'phone' => $clientData['phone'] ?? null,
                'company_name' => $clientData['company_name'] ?? null,
                'whatsapp' => $clientData['whatsapp'] ?? null,
                'city' => $clientData['city'] ?? null,
                'country' => $clientData['country'] ?? null,
                'ip_address' => $clientData['ip_address'] ?? null,
            ], fn ($v) => $v !== null));
        } else {
            $client = Client::create([
                'name' => $clientData['name'] ?? '',
                'status' => 'prospect',
                'phone' => $clientData['phone'] ?? '',
                'company_name' => $clientData['company_name'] ?? null,
                'whatsapp' => $clientData['whatsapp'] ?? null,
                'city' => $clientData['city'] ?? null,
                'country' => $clientData['country'] ?? null,
                'ip_address' => $clientData['ip_address'] ?? null,
            ]);
        }

        return $client;
    }

    /**
     * Create a Project linked to the given Client.
     */
    public function createProject(Client $client, array $projectData): Project
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

    /**
     * Create Answer and AnswerOption records from answers_data JSON.
     */
    public function createAnswers(Project $project, ?array $answersData): void
    {
        if (empty($answersData)) {
            return;
        }

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

    /**
     * Copy files from pending storage to permanent project storage and create ProjectFile records.
     */
    public function createProjectFiles(Project $project, ?array $filesData): void
    {
        if (empty($filesData)) {
            return;
        }

        foreach ($filesData as $fileData) {
            $storedPath = $fileData['stored_path'] ?? '';
            $originalName = $fileData['original_name'] ?? 'file';

            $newPath = 'project-files/'.$project->id.'/'.$originalName;
            $fullOldPath = Storage::disk('local')->path($storedPath);

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
