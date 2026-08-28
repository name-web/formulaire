<?php

namespace App\Http\Requests;

use App\Traits\HasCountryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartQuestionnaireRequest extends FormRequest
{
    use HasCountryData;

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
                // Plain string like 'ecommerce' — wrap in array
                $this->merge(['project_types' => [$projectTypes]]);
            }
        }
    }

    public function rules(): array
    {
        $allowedTypes = array_keys(config('project_types.types'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'project_types' => ['required', 'array', 'min:1'],
            'project_types.*' => ['required', 'string', 'in:'.implode(',', $allowedTypes)],
            'other_description' => [
                Rule::requiredIf(fn () => in_array('autre', $this->input('project_types', []))),
                'nullable', 'string', 'max:500',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $country = $this->input('country');
            $city = $this->input('city');

            if (! $country || ! $city) {
                return;
            }

            $allData = $this->fetchAllCountryData();

            $foundCountry = collect($allData)->first(
                fn (array $item) => strcasecmp($item['country'], $country) === 0
            );

            if (! $foundCountry) {
                $validator->errors()->add('country', 'Le pays sélectionné n\'est pas valide.');

                return;
            }

            $cities = $foundCountry['cities'] ?? [];
            $cityExists = collect($cities)->contains(
                fn (string $c) => strcasecmp($c, $city) === 0
            );

            if (! $cityExists) {
                $validator->errors()->add('city', 'La ville sélectionnée n\'appartient pas au pays choisi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Veuillez saisir votre nom.',
            'email.required' => 'Veuillez saisir votre adresse e-mail.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'phone.required' => 'Veuillez saisir votre numéro de téléphone.',
            'country.required' => 'Veuillez sélectionner un pays.',
            'city.required' => 'Veuillez sélectionner une ville.',
            'project_types.required' => 'Veuillez sélectionner au moins un type de projet.',
            'project_types.min' => 'Veuillez sélectionner au moins un type de projet.',
            'project_types.*.in' => 'Un des types de projet sélectionnés n\'est pas valide.',
            'other_description.required' => 'Veuillez préciser votre choix car vous avez sélectionné "Autre".',
        ];
    }
}
