<?php

namespace App\Services\Questionnaire;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Collection;

class QuestionService
{
    public function getForProjectType(string $projectType): Collection
    {
        return Question::where('is_active', true)
            ->whereNull('deleted_at')
            ->whereJsonContains('project_types', $projectType)
            ->orderBy('sort_order')
            ->get();
    }

    public function getForProjectTypes(array $projectTypes): Collection
    {
        $query = Question::where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('sort_order');

        $query->where(function ($q) use ($projectTypes) {
            foreach ($projectTypes as $type) {
                $q->orWhereJsonContains('project_types', $type);
            }
        });

        return $query->get();
    }

    public function getWithAnswers(int $projectId, string $projectType): Collection
    {
        $questions = $this->getForProjectType($projectType);

        return $questions->load([
            'options' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'answers' => fn ($q) => $q->where('project_id', $projectId),
            'answers.answerOptions.questionOption',
            'conditions',
        ]);
    }

    public function getOptions(int $questionId): Collection
    {
        return QuestionOption::where('question_id', $questionId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function find(int $questionId): ?Question
    {
        return Question::find($questionId);
    }

    public function findActive(int $questionId): ?Question
    {
        return Question::where('id', $questionId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();
    }
}
