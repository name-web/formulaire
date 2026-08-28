<?php

namespace App\Services\Questionnaire;

use App\Models\Answer;
use App\Models\AnswerOption;
use App\Models\Question;
use Illuminate\Support\Collection;

class AnswerService
{
    public function save(int $projectId, int $questionId, array $data): Answer
    {
        $question = Question::findOrFail($questionId);

        $answer = Answer::updateOrCreate(
            ['project_id' => $projectId, 'question_id' => $questionId],
            $this->buildAnswerData($question, $data)
        );

        $this->syncAnswerOptions($answer, $question, $data);
        $this->handleOtherValue($answer, $question, $data);

        return $answer->fresh(['answerOptions.questionOption']);
    }

    public function getForQuestion(int $projectId, int $questionId): ?Answer
    {
        return Answer::where('project_id', $projectId)
            ->where('question_id', $questionId)
            ->with(['answerOptions.questionOption'])
            ->first();
    }

    public function getAllForProject(int $projectId): Collection
    {
        return Answer::where('project_id', $projectId)
            ->with(['question', 'answerOptions.questionOption'])
            ->get();
    }

    public function hasAnswer(int $projectId, int $questionId): bool
    {
        return Answer::where('project_id', $projectId)
            ->where('question_id', $questionId)
            ->exists();
    }

    public function deleteForQuestion(int $projectId, int $questionId): void
    {
        Answer::where('project_id', $projectId)
            ->where('question_id', $questionId)
            ->delete();
    }

    public function getAnsweredQuestionIds(int $projectId): array
    {
        return Answer::where('project_id', $projectId)
            ->pluck('question_id')
            ->toArray();
    }

    private function buildAnswerData(Question $question, array $data): array
    {
        $textValue = null;
        $filePath = null;

        // Set text_value for any type that provides a 'value' key
        // (covers text, textarea, number, date, yes_no, select, multiselect)
        if (isset($data['value'])) {
            $textValue = $data['value'];
        }

        if ($question->type === 'file' && isset($data['file_path'])) {
            $filePath = $data['file_path'];
        }

        return [
            'text_value' => $textValue,
            'file_path' => $filePath,
        ];
    }

    private function syncAnswerOptions(Answer $answer, Question $question, array $data): void
    {
        if (! in_array($question->type, ['select', 'multiselect'])) {
            $answer->answerOptions()->delete();

            return;
        }

        $optionIds = $data['option_ids'] ?? [];

        $answer->answerOptions()->delete();

        foreach ($optionIds as $optionId) {
            AnswerOption::create([
                'answer_id' => $answer->id,
                'question_option_id' => $optionId,
            ]);
        }
    }

    private function handleOtherValue(Answer $answer, Question $question, array $data): void
    {
        if (! in_array($question->type, ['select', 'multiselect'])) {
            $answer->update(['other_value' => null]);

            return;
        }

        $otherOption = $question->options()
            ->where('value', 'autre')
            ->first();

        if (! $otherOption) {
            $answer->update(['other_value' => null]);

            return;
        }

        $optionIds = $data['option_ids'] ?? [];
        $hasOther = in_array($otherOption->id, $optionIds);

        $otherText = ($hasOther && ! empty($data['other_value']))
            ? $data['other_value']
            : null;

        $answer->update(['other_value' => $otherText]);
    }
}
