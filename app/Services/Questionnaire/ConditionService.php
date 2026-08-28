<?php

namespace App\Services\Questionnaire;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionCondition;

class ConditionService
{
    public function isVisible(Question $question, int $projectId): bool
    {
        $conditions = $question->conditions;

        if ($conditions->isEmpty()) {
            return true;
        }

        foreach ($conditions as $condition) {
            if (! $this->evaluateCondition($condition, $projectId)) {
                return false;
            }
        }

        return true;
    }

    public function evaluateCondition(QuestionCondition $condition, int $projectId): bool
    {
        $answer = Answer::with('answerOptions')
            ->where('project_id', $projectId)
            ->where('question_id', $condition->depends_on_question_id)
            ->first();

        if (! $answer) {
            return false;
        }

        return match ($condition->operator) {
            'equals' => $this->evaluateEquals($answer, $condition),
            'not_equals' => ! $this->evaluateEquals($answer, $condition),
            'contains' => $this->evaluateContains($answer, $condition),
            'not_contains' => ! $this->evaluateContains($answer, $condition),
            default => false,
        };
    }

    private function evaluateEquals(Answer $answer, QuestionCondition $condition): bool
    {
        if ($condition->option_value_id) {
            return $answer->answerOptions
                ->contains('question_option_id', $condition->option_value_id);
        }

        if ($condition->text_value !== null) {
            return $answer->text_value === $condition->text_value;
        }

        return false;
    }

    private function evaluateContains(Answer $answer, QuestionCondition $condition): bool
    {
        if ($condition->option_value_id) {
            return $answer->answerOptions
                ->contains('question_option_id', $condition->option_value_id);
        }

        if ($condition->text_value !== null && $answer->text_value) {
            return str_contains($answer->text_value, $condition->text_value);
        }

        return false;
    }
}
