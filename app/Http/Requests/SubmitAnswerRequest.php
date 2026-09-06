<?php

namespace App\Http\Requests;

use App\Models\QuestionOption;
use App\Services\Questionnaire\PendingSubmissionService;
use App\Services\Questionnaire\QuestionnaireService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    private ?QuestionOption $autreOption = null;

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $optionIds = $this->input('option_ids');

        if (is_array($optionIds)) {
            $this->merge([
                'option_ids' => array_values(array_filter($optionIds, fn ($id) => $id !== null && $id !== '')),
            ]);
        }
    }

    public function rules(): array
    {
        $step = (int) $this->route('step');

        if ($step < 1) {
            return [];
        }

        $pendingUuid = session('pending_submission_uuid');

        if ($pendingUuid) {
            return $this->resolveRulesForPending($pendingUuid, $step);
        }

        return [];
    }

    private function resolveRulesForPending(string $pendingUuid, int $step): array
    {
        $pendingService = app(PendingSubmissionService::class);
        $questionnaireService = app(QuestionnaireService::class);

        $pending = $pendingService->find($pendingUuid);
        if (! $pending) {
            return [];
        }

        $visibleQuestions = $questionnaireService->getVisibleQuestionsForPending($pending);
        $answeredIds = $pendingService->getAnsweredQuestionIds($pending);
        $isEditing = $this->query('edit') === '1';

        if ($isEditing) {
            $question = $visibleQuestions->get($step - 1);
        } else {
            $unansweredIndex = $step - 1;
            while ($unansweredIndex < $visibleQuestions->count()) {
                $candidate = $visibleQuestions->get($unansweredIndex);
                if ($candidate && ! in_array($candidate->id, $answeredIds)) {
                    break;
                }
                $unansweredIndex++;
            }
            $question = $visibleQuestions->get($unansweredIndex);
        }

        if (! $question) {
            return [];
        }

        return $this->buildRulesForQuestion($question);
    }

    private function buildRulesForQuestion($question): array
    {
        $rules = [];
        $presence = $question->is_required ? 'required' : 'nullable';

        switch ($question->type) {
            case 'text':
                $rules['value'] = [$presence, 'string', 'max:255'];
                break;

            case 'textarea':
                $rules['value'] = [$presence, 'string', 'max:5000'];
                break;

            case 'number':
                $rules['value'] = [$presence, 'numeric'];
                break;

            case 'date':
                $rules['value'] = [$presence, 'date'];
                break;

            case 'yes_no':
                $rules['value'] = [$presence, 'string', 'in:yes,no'];
                break;

            case 'select':
                $rules['option_ids'] = $question->is_required
                    ? [$presence, 'array', 'min:1']
                    : [$presence, 'array'];
                $rules['option_ids.*'] = ['integer', 'exists:question_options,id'];
                $otherOptionSelect = $question->options()->where('value', 'autre')->first();

                if ($otherOptionSelect) {
                    $this->autreOption = $otherOptionSelect;
                }

                $rules['other_value'] = ['nullable', 'string', 'max:500'];
                break;

            case 'multiselect':
                $rules['option_ids'] = $question->is_required
                    ? [$presence, 'array', 'min:1']
                    : [$presence, 'array'];
                $rules['option_ids.*'] = ['integer', 'exists:question_options,id'];
                $otherOption = $question->options()->where('value', 'autre')->first();

                if ($otherOption) {
                    $this->autreOption = $otherOption;
                }

                $rules['other_value'] = ['nullable', 'string', 'max:500'];
                break;

            case 'file':
                $rules['file'] = [$presence, 'file', 'max:5120', 'mimes:png,jpg,jpeg,gif,pdf,svg,doc,docx,xls,xlsx,zip'];
                break;
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $option = $this->autreOption;

            $selectedIds = array_map('intval', (array) $this->input('option_ids', []));

            if ($option && in_array($option->id, $selectedIds, true) && trim((string) $this->input('other_value')) === '') {
                $validator->errors()->add('other_value', 'Veuillez préciser votre choix "Autre".');
            }
        });
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Veuillez fournir une réponse.',
            'value.numeric' => 'Veuillez saisir un nombre valide.',
            'value.date' => 'Veuillez saisir une date valide.',
            'value.in' => 'Veuillez sélectionner une réponse valide.',
            'option_ids.required' => 'Veuillez sélectionner au moins une option.',
            'option_ids.*.exists' => 'L\'option sélectionnée n\'est pas valide.',
            'file.required' => 'Veuillez joindre un fichier.',
            'file.file' => 'Le fichier n\'est pas valide.',
            'file.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
            'file.mimes' => 'Le format du fichier n\'est pas accepté.',
            'other_value.required' => 'Veuillez préciser votre choix "Autre".',
        ];
    }
}
