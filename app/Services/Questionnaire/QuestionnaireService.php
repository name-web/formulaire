<?php

namespace App\Services\Questionnaire;

use App\Models\Answer;
use App\Models\AnswerOption;
use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuestionnaireService
{
    public function __construct(
        private QuestionService $questionService,
        private ConditionService $conditionService,
        private AnswerService $answerService,
        private ProjectService $projectService,
        private PendingSubmissionService $pendingSubmissionService,
    ) {}

    public function startSession(array $clientData, string|array $projectTypes): Project
    {
        return $this->projectService->createDraft($clientData, $projectTypes);
    }

    public function getCurrentStep(int $projectId, string|array $projectType): ?Question
    {
        $questions = $this->getVisibleQuestions($projectId, $projectType);
        $answeredIds = $this->answerService->getAnsweredQuestionIds($projectId);

        foreach ($questions as $question) {
            if (! in_array($question->id, $answeredIds)) {
                return $question;
            }
        }

        return null;
    }

    public function getVisibleQuestions(int $projectId, string|array $projectType): Collection
    {
        $types = is_array($projectType) ? $projectType : [$projectType];
        $questions = $this->questionService->getForProjectTypes($types);

        return $questions->filter(function (Question $question) use ($projectId) {
            return $this->conditionService->isVisible($question, $projectId);
        })->values();
    }

    public function getVisibleQuestionsForPending(PendingSubmission $pending): Collection
    {
        $projectTypes = $this->pendingSubmissionService->getProjectTypes($pending);
        $types = is_array($projectTypes) ? $projectTypes : [$projectTypes];
        $questions = $this->questionService->getForProjectTypes($types);
        $answers = $this->pendingSubmissionService->getAnswersCollection($pending);

        return $questions->filter(function (Question $question) use ($answers) {
            return $this->conditionService->isVisibleWithAnswers($question, $answers);
        })->values();
    }

    public function getSummary(int $projectId): array
    {
        $project = $this->projectService->find($projectId);
        $client = $this->projectService->getClient($projectId);
        $answers = $this->answerService->getAllForProject($projectId);
        $visibleQuestions = $this->getVisibleQuestions($projectId, $project->project_type);

        $questionsWithAnswers = $visibleQuestions->map(function (Question $question) use ($answers) {
            $answer = $answers->firstWhere('question_id', $question->id);

            return [
                'question' => $question,
                'answer' => $answer,
                'display_value' => $this->formatAnswerForDisplay($question, $answer),
            ];
        });

        return [
            'project' => $project,
            'client' => $client,
            'questions' => $questionsWithAnswers,
        ];
    }

    public function getSummaryForPending(PendingSubmission $pending): array
    {
        $clientData = $this->pendingSubmissionService->getClientData($pending);
        $projectTypes = $this->pendingSubmissionService->getProjectTypes($pending);
        $answers = $this->pendingSubmissionService->getAnswersCollection($pending);
        $visibleQuestions = $this->getVisibleQuestionsForPending($pending);

        $questionsWithAnswers = $visibleQuestions->map(function (Question $question) use ($answers) {
            $answer = $answers->firstWhere('question_id', $question->id);

            return [
                'question' => $question,
                'answer' => $answer,
                'display_value' => $this->formatAnswerForPendingDisplay($question, $answer),
            ];
        });

        return [
            'project' => (object) [
                'project_type' => $projectTypes,
                'status' => 'collecting',
            ],
            'client' => (object) $clientData,
            'questions' => $questionsWithAnswers,
        ];
    }

    public function submitProject(int $projectId): void
    {
        DB::transaction(function () use ($projectId) {
            $this->projectService->submit($projectId);
        });
    }

    public function getProgress(int $projectId, string|array $projectType): array
    {
        $visibleQuestions = $this->getVisibleQuestions($projectId, $projectType);
        $total = $visibleQuestions->count();
        $answeredIds = $this->answerService->getAnsweredQuestionIds($projectId);

        $answered = $visibleQuestions->filter(function (Question $question) use ($answeredIds) {
            return in_array($question->id, $answeredIds);
        })->count();

        return [
            'total' => $total,
            'answered' => $answered,
            'percentage' => $total > 0 ? round(($answered / $total) * 100) : 0,
        ];
    }

    public function getProgressForPending(PendingSubmission $pending): array
    {
        $visibleQuestions = $this->getVisibleQuestionsForPending($pending);
        $total = $visibleQuestions->count();
        $answeredIds = $this->pendingSubmissionService->getAnsweredQuestionIds($pending);

        $answered = $visibleQuestions->filter(function (Question $question) use ($answeredIds) {
            return in_array($question->id, $answeredIds);
        })->count();

        return [
            'total' => $total,
            'answered' => $answered,
            'percentage' => $total > 0 ? round(($answered / $total) * 100) : 0,
        ];
    }

    public function getPreviousQuestion(int $projectId, string|array $projectType): ?Question
    {
        $visibleQuestions = $this->getVisibleQuestions($projectId, $projectType);
        $answeredIds = $this->answerService->getAnsweredQuestionIds($projectId);
        $currentStep = $this->getCurrentStep($projectId, $projectType);

        $previous = null;

        foreach ($visibleQuestions as $question) {
            if ($question->id === ($currentStep?->id)) {
                break;
            }

            if (in_array($question->id, $answeredIds)) {
                $previous = $question;
            }
        }

        return $previous;
    }

    public function getPreviousQuestionForPending(PendingSubmission $pending): ?Question
    {
        $visibleQuestions = $this->getVisibleQuestionsForPending($pending);
        $answeredIds = $this->pendingSubmissionService->getAnsweredQuestionIds($pending);

        $currentStep = null;
        foreach ($visibleQuestions as $question) {
            if (! in_array($question->id, $answeredIds)) {
                $currentStep = $question;
                break;
            }
        }

        $previous = null;

        foreach ($visibleQuestions as $question) {
            if ($question->id === ($currentStep?->id)) {
                break;
            }

            if (in_array($question->id, $answeredIds)) {
                $previous = $question;
            }
        }

        return $previous;
    }

    private function formatAnswerForDisplay(Question $question, ?Answer $answer): ?string
    {
        if (! $answer) {
            return null;
        }

        return match ($question->type) {
            'select' => $this->formatSelectAnswer($answer) ?? $answer->text_value,
            'multiselect' => $this->formatMultiselectAnswer($answer) ?? $answer->text_value,
            'yes_no' => match ($answer->text_value) {
                'yes', 'Oui', 'oui' => 'Oui',
                'no', 'Non', 'non' => 'Non',
                default => $answer->text_value ?? 'Non',
            },
            'file' => $answer->file_path ? 'Fichier joint' : null,
            default => $answer->text_value,
        };
    }

    private function formatAnswerForPendingDisplay(Question $question, ?object $answer): ?string
    {
        if (! $answer) {
            return null;
        }

        $textValue = $answer->text_value ?? null;
        $filePath = $answer->file_path ?? null;
        $optionIds = $answer->option_ids ?? [];
        $otherValue = $answer->other_value ?? null;

        return match ($question->type) {
            'select' => $this->formatSelectAnswerForPending($question, $optionIds, $otherValue) ?? $textValue,
            'multiselect' => $this->formatMultiselectAnswerForPending($question, $optionIds, $otherValue) ?? $textValue,
            'yes_no' => match ($textValue) {
                'yes', 'Oui', 'oui' => 'Oui',
                'no', 'Non', 'non' => 'Non',
                default => $textValue ?? 'Non',
            },
            'file' => $filePath ? 'Fichier joint' : null,
            default => $textValue,
        };
    }

    private function formatSelectAnswer(Answer $answer): ?string
    {
        $option = $answer->answerOptions->first()?->questionOption;

        if (! $option) {
            return null;
        }

        if ($option->value === 'autre' && $answer->other_value) {
            return 'Autre : '.$answer->other_value;
        }

        return $option->label;
    }

    private function formatSelectAnswerForPending(Question $question, array $optionIds, ?string $otherValue): ?string
    {
        if (empty($optionIds)) {
            return null;
        }

        $option = $question->options()->whereIn('id', $optionIds)->first();

        if (! $option) {
            return null;
        }

        if ($option->value === 'autre' && $otherValue) {
            return 'Autre : '.$otherValue;
        }

        return $option->label;
    }

    private function formatMultiselectAnswer(Answer $answer): ?string
    {
        $labels = $answer->answerOptions
            ->map(function (AnswerOption $ao) use ($answer) {
                $option = $ao->questionOption;

                if ($option && $option->value === 'autre' && $answer->other_value) {
                    return 'Autre : '.$answer->other_value;
                }

                return $option?->label;
            })
            ->filter()
            ->toArray();

        return ! empty($labels) ? implode(', ', $labels) : null;
    }

    private function formatMultiselectAnswerForPending(Question $question, array $optionIds, ?string $otherValue): ?string
    {
        if (empty($optionIds)) {
            return null;
        }

        $labels = $question->options()
            ->whereIn('id', $optionIds)
            ->map(function ($option) use ($otherValue) {
                if ($option->value === 'autre' && $otherValue) {
                    return 'Autre : '.$otherValue;
                }

                return $option->label;
            })
            ->filter()
            ->toArray();

        return ! empty($labels) ? implode(', ', $labels) : null;
    }
}
