<?php

namespace App\Services\Questionnaire;

use App\Models\Answer;
use App\Models\Client;
use App\Models\Project;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function createDraft(array $clientData, string|array $projectTypes): Project
    {
        $types = is_array($projectTypes) ? $projectTypes : [$projectTypes];

        return DB::transaction(function () use ($clientData, $types) {
            $client = Client::create($clientData);

            return Project::create([
                'client_id' => $client->id,
                'project_type' => $types,
                'status' => 'draft',
            ]);
        });
    }

    public function find(int $projectId): ?Project
    {
        return Project::find($projectId);
    }

    public function getClient(int $projectId): ?Client
    {
        $project = Project::find($projectId);

        return $project?->client;
    }

    public function submit(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        $project->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function isSubmitted(int $projectId): bool
    {
        return Project::where('id', $projectId)
            ->where('status', 'submitted')
            ->exists();
    }

    public function updateProjectType(int $projectId, string|array $newType): void
    {
        $project = Project::findOrFail($projectId);

        $types = is_array($newType) ? $newType : [$newType];
        $oldTypes = is_array($project->project_type) ? $project->project_type : [$project->project_type];

        if ($oldTypes === $types) {
            return;
        }

        $project->update(['project_type' => $types]);

        $this->cleanIncompatibleAnswers($projectId, $oldTypes, $types);
    }

    private function cleanIncompatibleAnswers(int $projectId, array $oldTypes, array $newTypes): void
    {
        $oldQuestions = Question::where(function ($query) use ($oldTypes) {
            foreach ($oldTypes as $type) {
                $query->orWhereJsonContains('project_types', $type);
            }
        })
            ->where('is_active', true)
            ->pluck('id');

        $newQuestions = Question::where(function ($query) use ($newTypes) {
            foreach ($newTypes as $type) {
                $query->orWhereJsonContains('project_types', $type);
            }
        })
            ->where('is_active', true)
            ->pluck('id');

        $incompatibleQuestionIds = $oldQuestions->diff($newQuestions);

        if ($incompatibleQuestionIds->isNotEmpty()) {
            Answer::where('project_id', $projectId)
                ->whereIn('question_id', $incompatibleQuestionIds)
                ->delete();
        }
    }
}
