<?php

namespace App\Services\Devis;

/**
 * Définition du parcours client du nouveau formulaire.
 *
 * Les cinq étapes, leurs valeurs par défaut et leurs règles de validation
 * découlent du dossier fonctionnel (parcours client). La structure des choix
 * vit dans config/devis_form.php et les types de projet dans
 * config/project_types.php.
 */
class DevisFormService
{
    public const TOTAL_STEPS = 5;

    public const STEP_COORDONNEES = 1;

    public const STEP_PROJET = 2;

    public const STEP_DETAIL = 3;

    public const STEP_HERBERGEMENT = 4;

    public const STEP_NIVEAU = 5;

    /**
     * Libellés des étapes, dans l'ordre du parcours.
     *
     * @return array<int, string>
     */
    public function steps(): array
    {
        return [
            self::STEP_COORDONNEES => 'Vos coordonnées',
            self::STEP_PROJET => 'Votre projet',
            self::STEP_DETAIL => 'Détail du besoin',
            self::STEP_HERBERGEMENT => 'Hébergement et suivi',
            self::STEP_NIVEAU => 'Niveau et délai',
        ];
    }

    public function stepLabel(int $step): string
    {
        return $this->steps()[$step] ?? '';
    }

    /**
     * Types de projet proposés par le nouveau formulaire, dans l'ordre.
     *
     * @return array<int, string>
     */
    public function types(): array
    {
        return array_values(array_filter(
            config('devis.form_types', []),
            fn (string $type): bool => config("project_types.types.{$type}") !== null,
        ));
    }

    /**
     * Variante de l'étape 3 pour un type de projet.
     */
    public function variantFor(?string $type): ?string
    {
        return config("devis_form.variantes.{$type}");
    }

    /**
     * Réponses initiales du formulaire.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'nom' => '',
            'entreprise' => '',
            'ville' => '',
            'tel' => '',
            'email' => '',
            'type' => '',
            'pages' => 5,
            'produits' => 50,
            'postes' => 3,
            'ecrans' => 10,
            'formation' => 1,
            'eleves' => '',
            'opt' => [],
            'modules' => [],
            'identite' => 'aucune',
            'contenus' => 'fournis',
            'paiement' => 'mobile',
            'plateforme' => 'android',
            'reprise' => 'non',
            'domaine' => 'com',
            'hebergement' => 'mutualise',
            'emails' => 3,
            'maintenance' => 'essentiel',
            'niveau' => 'STANDARD',
            'accelere' => false,
            'delai' => 'normal',
            'message' => '',
            'remise' => 0.0,
        ];
    }

    /**
     * Règles de validation d'une étape.
     *
     * @param  array<string, mixed>  $answers  réponses déjà collectées
     * @return array<string, array<int, mixed>>
     */
    public function rulesForStep(int $step, array $answers = []): array
    {
        return match ($step) {
            self::STEP_COORDONNEES => [
                'nom' => ['required', 'string', 'max:255'],
                'entreprise' => ['nullable', 'string', 'max:255'],
                'ville' => ['nullable', 'string', 'max:255'],
                'tel' => ['required', 'string', 'max:50'],
                'email' => ['required', 'string', 'email', 'max:255'],
            ],
            self::STEP_PROJET => [
                'type' => ['required', 'string', 'in:'.implode(',', $this->types())],
            ],
            self::STEP_DETAIL => $this->detailRules($answers),
            self::STEP_HERBERGEMENT => $this->hostingRules($answers),
            self::STEP_NIVEAU => [
                'niveau' => ['required', 'string', 'in:'.implode(',', array_keys(config('devis.niveaux', [])))],
                'delai' => ['nullable', 'string', 'in:normal,accelere'],
                'message' => ['nullable', 'string', 'max:2000'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function detailRules(array $answers): array
    {
        $variant = $this->variantFor($answers['type'] ?? null);

        $rules = [
            'opt' => ['nullable', 'array'],
            'opt.*' => ['string', 'in:'.implode(',', array_merge($this->optionValues($variant), ['sauvegarde']))],
        ];

        if ($variant === 'site') {
            $rules['pages'] = ['nullable', 'integer', 'min:1', 'max:100'];
            $rules['contenus'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('contenus'))];
            $rules['identite'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('identite'))];
        }

        if ($variant === 'ecommerce') {
            $rules['pages'] = ['nullable', 'integer', 'min:1', 'max:100'];
            $rules['produits'] = ['nullable', 'integer', 'min:1', 'max:100000'];
            $rules['paiement'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('paiement'))];
            $rules['identite'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('identite'))];
        }

        if ($variant === 'logiciel' || $variant === 'scolaire') {
            $moduleValues = $this->moduleValues($variant);

            $rules['modules'] = ['required', 'array', 'min:1'];
            $rules['modules.*'] = ['string', 'in:'.implode(',', $moduleValues)];
            $rules['reprise'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('reprise'))];
        }

        if ($variant === 'logiciel') {
            $rules['postes'] = ['nullable', 'integer', 'min:1', 'max:1000'];
            $rules['formation'] = ['nullable', 'integer', 'min:0', 'max:365'];
        }

        if ($variant === 'scolaire') {
            $rules['eleves'] = ['nullable', 'integer', 'min:0', 'max:200000'];
        }

        if ($variant === 'mobile') {
            $rules['plateforme'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('plateforme'))];
            $rules['ecrans'] = ['nullable', 'integer', 'min:1', 'max:500'];
            $rules['identite'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('identite'))];
        }

        return $rules;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function hostingRules(array $answers): array
    {
        $rules = [
            'maintenance' => ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('maintenance'))],
            'opt' => ['nullable', 'array'],
            'opt.*' => ['string', 'in:sauvegarde'],
        ];

        // L'application mobile ne demande ni domaine ni hébergement.
        if (($answers['type'] ?? null) !== 'application_mobile') {
            $rules['domaine'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('domaine'))];
            $rules['hebergement'] = ['nullable', 'string', 'in:'.implode(',', $this->configuredValues('hebergement'))];
            $rules['emails'] = ['nullable', 'integer', 'min:0', 'max:10000'];
        }

        return $rules;
    }

    /**
     * Messages de validation du parcours.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Indiquez vos nom et prénoms.',
            'tel.required' => 'Indiquez un numéro de téléphone.',
            'email.required' => "L'adresse e-mail est nécessaire pour recevoir votre devis.",
            'email.email' => "L'adresse e-mail n'est pas valide — c'est là que part le devis.",
            'type.required' => 'Choisissez un type de projet.',
            'modules.required' => 'Choisissez au moins un module.',
            'modules.min' => 'Choisissez au moins un module.',
            'niveau.required' => 'Choisissez un niveau de finition.',
        ];
    }

    /**
     * Valeurs autorisées d'une liste de choix (identite, contenus, paiement...).
     *
     * @return array<int, string>
     */
    public function configuredValues(string $key): array
    {
        return array_map(
            fn (array $choice): string => (string) $choice[0],
            config("devis_form.{$key}", []),
        );
    }

    /**
     * Valeurs des options cochables d'une variante.
     *
     * @return array<int, string>
     */
    public function optionValues(?string $variant): array
    {
        return array_map(
            fn (array $choice): string => (string) $choice[0],
            config("devis_form.options.{$variant}", []),
        );
    }

    /**
     * Valeurs des modules cochables d'une variante.
     *
     * @return array<int, string>
     */
    public function moduleValues(?string $variant): array
    {
        return array_map(
            fn (array $choice): string => (string) $choice[0],
            config("devis_form.modules.{$variant}", []),
        );
    }

    /**
     * Libellé d'un choix (identite, contenus, paiement, domaine...).
     * La valeur brute est retournée si elle n'est pas répertoriée.
     */
    public function choiceLabel(string $configKey, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        foreach ((array) config("devis_form.{$configKey}", []) as $choice) {
            if ((string) $choice[0] === (string) $value) {
                return (string) $choice[1];
            }
        }

        return (string) $value;
    }

    /**
     * Libellés des options cochées d'une variante.
     *
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    public function optionLabels(?string $variant, array $values): array
    {
        return $this->labelsFromChoices(config("devis_form.options.{$variant}", []), $values);
    }

    /**
     * Libellés des modules cochés d'une variante.
     *
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    public function moduleLabels(?string $variant, array $values): array
    {
        return $this->labelsFromChoices(config("devis_form.modules.{$variant}", []), $values);
    }

    /**
     * Normalise une valeur reçue (liste ou scalaire) en liste de chaînes.
     *
     * @return array<int, string>
     */
    public function toArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $value), fn (string $item): bool => $item !== ''));
    }

    /**
     * @param  array<int, array<int, string>>  $choices
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private function labelsFromChoices(array $choices, array $values): array
    {
        $labels = [];

        foreach ($choices as $choice) {
            if (in_array((string) $choice[0], $values, true)) {
                $labels[] = (string) $choice[1];
            }
        }

        return $labels;
    }
}
