<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartQuestionnaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $projectTypes = $this->input('project_types');

        if (is_string($projectTypes)) {
            $decoded = json_decode($projectTypes, true);
            if (is_array($decoded)) {
                $this->merge(['project_types' => $decoded]);
            } else {
                $this->merge(['project_types' => [$projectTypes]]);
            }
        }
    }

    public function rules(): array
    {
        $allowedTypes = array_keys(config('project_types.types'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'project_types' => ['required', 'array', 'min:1'],
            'project_types.*' => ['required', 'string', 'in:'.implode(',', $allowedTypes)],
            'other_description' => [
                'nullable', 'string', 'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Veuillez saisir votre nom.',
            'phone.required' => 'Veuillez saisir votre numéro de téléphone.',
            'project_types.required' => 'Veuillez sélectionner un type de projet.',
            'project_types.min' => 'Veuillez sélectionner au moins un type de projet.',
            'project_types.*.in' => 'Le type de projet sélectionné n\'est pas valide.',
        ];
    }
}
