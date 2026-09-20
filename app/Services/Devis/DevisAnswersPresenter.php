<?php

namespace App\Services\Devis;

/**
 * Met en forme les réponses du nouveau formulaire pour l'administration.
 *
 * Les sections suivent l'ordre logique du parcours client afin que la fiche
 * projet reste lisible pour l'équipe, exactement comme « Détail du besoin »
 * puis « Hébergement et suivi » dans le formulaire.
 */
class DevisAnswersPresenter
{
    public function __construct(
        private DevisFormService $formService,
    ) {}

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, array<int, array{label: string, value: string}>>
     */
    public function sections(array $answers): array
    {
        $type = $answers['type'] ?? null;
        $variant = $this->formService->variantFor($type);

        $sections = [
            'Projet' => array_values(array_filter([
                $this->line('Type de projet', config("project_types.types.{$type}.label", (string) $type)),
                $this->line('Niveau de finition', config('devis.niveaux.'.($answers['niveau'] ?? ''), (string) ($answers['niveau'] ?? ''))),
                $this->line('Délai souhaité', ($answers['accelere'] ?? false)
                    ? 'Livraison prioritaire (majoration de 30 %)'
                    : 'Délai normal'),
            ])),
        ];

        $detail = $this->detailLines($answers, $variant);

        if ($detail !== []) {
            $sections['Détail du besoin'] = $detail;
        }

        $sections['Hébergement et suivi'] = $this->hostingLines($answers, $type);

        if (! empty($answers['message'])) {
            $sections['Précisions du client'] = [
                $this->line('Message', (string) $answers['message']),
            ];
        }

        return $sections;
    }

    /**
     * Nombre d'informations affichées (compteur de la fiche projet).
     *
     * @param  array<string, mixed>  $answers
     */
    public function count(array $answers): int
    {
        return array_sum(array_map('count', $this->sections($answers)));
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<int, array{label: string, value: string}>
     */
    private function detailLines(array $answers, ?string $variant): array
    {
        $lines = [];

        if ($variant === 'site') {
            $lines[] = $this->line('Nombre de pages', (string) $answers['pages']);
            $lines[] = $this->line('Textes et photos', $this->formService->choiceLabel('contenus', $answers['contenus']));
        }

        if ($variant === 'ecommerce') {
            $lines[] = $this->line('Produits à mettre en ligne', (string) $answers['produits']);
            $lines[] = $this->line('Pages de présentation', (string) $answers['pages']);
            $lines[] = $this->line('Moyens de paiement', $this->formService->choiceLabel('paiement', $answers['paiement']));
        }

        if ($variant === 'logiciel') {
            $lines[] = $this->line('Postes à équiper', (string) $answers['postes']);
            $lines[] = $this->line('Jours de formation', (string) $answers['formation']);
            $lines[] = $this->line('Reprise des données', $this->formService->choiceLabel('reprise', $answers['reprise']));
        }

        if ($variant === 'scolaire') {
            if (($answers['eleves'] ?? '') !== '' && $answers['eleves'] !== null) {
                $lines[] = $this->line('Nombre d’élèves', (string) $answers['eleves']);
            }
            $lines[] = $this->line('Reprise des fichiers', $this->formService->choiceLabel('reprise', $answers['reprise']));
        }

        if ($variant === 'mobile') {
            $lines[] = $this->line('Plateformes', $this->formService->choiceLabel('plateforme', $answers['plateforme']));
            $lines[] = $this->line('Nombre d’écrans', (string) $answers['ecrans']);
        }

        if (in_array($variant, ['site', 'ecommerce', 'mobile'], true)) {
            $lines[] = $this->line('Identité visuelle', $this->formService->choiceLabel('identite', $answers['identite']));
        }

        $modules = $this->formService->moduleLabels($variant, $this->formService->toArray($answers['modules'] ?? []));

        if ($modules !== []) {
            $lines[] = $this->line('Modules choisis', implode(', ', $modules));
        }

        $options = $this->formService->optionLabels($variant, $this->formService->toArray($answers['opt'] ?? []));

        if ($options !== []) {
            $lines[] = $this->line('Options choisies', implode(', ', $options));
        }

        return array_values(array_filter($lines));
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<int, array{label: string, value: string}>
     */
    private function hostingLines(array $answers, ?string $type): array
    {
        $lines = [];

        if ($type !== 'application_mobile') {
            $lines[] = $this->line('Nom de domaine', $this->formService->choiceLabel('domaine', $answers['domaine']));
            $lines[] = $this->line('Hébergement', $this->formService->choiceLabel('hebergement', $answers['hebergement']));
            $lines[] = $this->line('Adresses e-mail professionnelles', (string) $answers['emails']);
        }

        $lines[] = $this->line('Suivi et maintenance', $this->formService->choiceLabel('maintenance', $answers['maintenance']));
        $lines[] = $this->line('Sauvegarde externalisée', in_array('sauvegarde', $this->formService->toArray($answers['opt'] ?? []), true)
            ? 'Ajoutée'
            : 'Non souscrite');

        return array_values(array_filter($lines));
    }

    /**
     * @return array{label: string, value: string}|null
     */
    private function line(string $label, ?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return ['label' => $label, 'value' => $value];
    }
}
