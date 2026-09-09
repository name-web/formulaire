<?php

namespace App\Http\Requests;

use App\Services\Phone\PhoneNumberService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        $phone = $this->input('phone');
        $country = $this->input('phone_country', 'CI');

        if (filled($phone) && filled($country)) {
            $phoneService = app(PhoneNumberService::class);
            $normalized = $phoneService->normalizeToE164($phone, $country);

            if ($normalized !== null) {
                $this->merge(['phone' => $normalized]);
            }
        }
    }

    public function rules(): array
    {
        $allowedTypes = array_keys(config('project_types.types'));
        $phoneService = app(PhoneNumberService::class);
        $supportedRegions = $phoneService->supportedRegions();

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'phone_country' => ['nullable', 'string', Rule::in($supportedRegions)],
            'project_types' => ['required', 'array', 'min:1'],
            'project_types.*' => ['required', 'string', 'in:'.implode(',', $allowedTypes)],
            'other_description' => [
                'nullable', 'string', 'max:500',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $phone = $this->input('phone');
            $country = $this->input('phone_country', 'CI');

            if (empty($phone) || empty($country)) {
                return;
            }

            $phoneService = app(PhoneNumberService::class);
            $normalized = $phoneService->normalizeToE164($phone, $country);

            if ($normalized === null) {
                $validator->errors()->add('phone', 'Le numéro de téléphone n\'est pas valide pour le pays sélectionné.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Veuillez saisir votre nom.',
            'phone.required' => 'Veuillez saisir votre numéro de téléphone.',
            'phone_country.in' => 'Le pays sélectionné n\'est pas supporté.',
            'project_types.required' => 'Veuillez sélectionner un type de projet.',
            'project_types.min' => 'Veuillez sélectionner au moins un type de projet.',
            'project_types.*.in' => 'Le type de projet sélectionné n\'est pas valide.',
        ];
    }
}
