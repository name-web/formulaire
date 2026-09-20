<?php

namespace App\Services\Devis;

/**
 * Accès en lecture au catalogue tarifaire (config/catalogue_prestations.php).
 *
 * Le catalogue est la feuille CATALOGUE_TARIFS du dossier fonctionnel : chaque
 * prestation porte un prix par niveau (ESSENTIEL / STANDARD / PREMIUM) et une
 * durée de référence.
 */
class DevisCatalogue
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $prestations = [];

    /**
     * @var array<int, string>
     */
    private array $recurrentes = [];

    public function __construct()
    {
        foreach (config('catalogue_prestations.prestations', []) as $row) {
            $this->prestations[$row['ref']] = $row;
        }

        $this->recurrentes = config('catalogue_prestations.recurrentes', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->prestations;
    }

    public function has(string $ref): bool
    {
        return isset($this->prestations[$ref]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $ref): ?array
    {
        return $this->prestations[$ref] ?? null;
    }

    /**
     * Prix unitaire d'une prestation pour un niveau donné.
     */
    public function price(string $ref, string $niveau): int
    {
        $prestation = $this->get($ref);

        if ($prestation === null) {
            return 0;
        }

        $key = mb_strtolower($niveau);

        return (int) ($prestation[$key] ?? 0);
    }

    public function isRecurrente(string $ref): bool
    {
        return in_array($ref, $this->recurrentes, true);
    }

    /**
     * Références du catalogue triées par ordre alphabétique de référence.
     *
     * @return array<int, string>
     */
    public function references(): array
    {
        $references = array_keys($this->prestations);
        sort($references);

        return $references;
    }

    /**
     * Libellés des niveaux disponibles.
     *
     * @return array<int, string>
     */
    public function niveaux(): array
    {
        return array_keys(config('devis.niveaux', ['ESSENTIEL', 'STANDARD', 'PREMIUM']));
    }
}
