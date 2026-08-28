<?php

namespace App\Http\Requests;

use App\Services\Questionnaire\QuestionnaireService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = session('project_id');
        $projectType = session('project_type');
        $step = (int) $this->route('step');

        if (! $projectId || ! $projectType || $step < 1) {
            return [];
        }

        $questionnaireService = app(QuestionnaireService::class);
        $visibleQuestions = $questionnaireService->getVisibleQuestions($projectId, $projectType);
        $isEditing = $this->query('edit') === '1';

        if ($isEditing) {
            $question = $visibleQuestions->get($step - 1);
        } else {
            // Apply same skip logic as controller: skip already-answered questions
            $answeredService = app(\App\Services\Questionnaire\AnswerService::class);
            $answeredIds = $answeredService->getAnsweredQuestionIds($projectId);
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

        $rules = [];

        switch ($question->type) {
            case 'text':
                $rules['value'] = ['required', 'string', 'max:255'];
                break;

            case 'textarea':
                $rules['value'] = ['required', 'string', 'max:5000'];
                break;

            case 'number':
                $rules['value'] = ['required', 'numeric'];
                break;

            case 'date':
                $rules['value'] = ['required', 'date'];
                break;

            case 'yes_no':
                $rules['value'] = ['required', 'string', 'in:yes,no'];
                break;

            case 'select':
                $rules['option_ids'] = ['required', 'array', 'min:1'];
                $rules['option_ids.*'] = ['integer', 'exists:question_options,id'];
                $otherOptionSelect = $question->options()->where('value', 'autre')->first();
                if ($otherOptionSelect) {
                    $rules['other_value'] = [
                        'nullable',
                        'string',
                        'max:500',
                        function ($attribute, $value, $fail) use ($otherOptionSelect) {
                            $selectedIds = $this->input('option_ids', []);
                            if (in_array($otherOptionSelect->id, $selectedIds) && empty(trim($value))) {
                                $fail('Veuillez préciser votre choix "Autre".');
                            }
                        },
                    ];
                } else {
                    $rules['other_value'] = ['nullable', 'string', 'max:500'];
                }
                break;

            case 'multiselect':
                $rules['option_ids'] = ['required', 'array', 'min:1'];
                $rules['option_ids.*'] = ['integer', 'exists:question_options,id'];
                $otherOption = $question->options()->where('value', 'autre')->first();
                if ($otherOption) {
                    $rules['other_value'] = [
                        'nullable',
                        'string',
                        'max:500',
                        function ($attribute, $value, $fail) use ($otherOption) {
                            $selectedIds = $this->input('option_ids', []);
                            if (in_array($otherOption->id, $selectedIds) && empty(trim((string) ($value ?? '')))) {
                                $fail('Veuillez préciser votre choix "Autre".');
                            }
                        },
                    ];
                } else {
                    $rules['other_value'] = ['nullable', 'string', 'max:500'];
                }
                break;

            case 'file':
                $rules['file'] = ['required', 'file', 'max:5120', 'mimes:png,jpg,jpeg,gif,pdf,svg,doc,docx,xls,xlsx,zip'];
                break;
        }

        if (! $question->is_required) {
            foreach ($rules as $key => $rule) {
                $rules[$key] = array_map(function ($r) {
                    return str_replace('required', 'nullable', $r);
                }, (array) $rule);
            }
        }

        return $rules;
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
