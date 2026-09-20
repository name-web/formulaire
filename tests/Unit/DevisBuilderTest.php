<?php

namespace Tests\Unit;

use App\Services\Devis\DevisBuilder;
use App\Services\Devis\DevisCatalogue;
use Tests\TestCase;

class DevisBuilderTest extends TestCase
{
    private DevisBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new DevisBuilder(new DevisCatalogue);
    }

    /**
     * Site vitrine Standard de 5 pages, sans option ni frais récurrents.
     * Toutes les valeurs attendues sont la somme du catalogue officiel.
     */
    public function test_site_vitrine_standard_quote_matches_reference_calculation(): void
    {
        $devis = $this->builder->construire([
            'type' => 'site_vitrine',
            'niveau' => 'STANDARD',
            'pages' => 5,
            'identite' => 'aucune',
            'contenus' => 'fournis',
            'opt' => [],
            'domaine' => 'aucun',
            'hebergement' => 'aucun',
            'emails' => 0,
            'maintenance' => 'aucune',
            'accelere' => false,
        ]);

        $this->assertSame(790000, $devis['sous_total_ht']);
        $this->assertSame(0, $devis['majoration']);
        $this->assertSame(790000, $devis['net_ht']);
        $this->assertSame(142200, $devis['tva']);
        $this->assertSame(932200, $devis['ttc']);

        $this->assertSame(372880, $devis['acompte']);
        $this->assertSame(279660, $devis['deuxieme']);
        $this->assertSame(279660, $devis['solde']);

        $this->assertSame(23, $devis['jours']);
        $this->assertSame([], $devis['recurrentes']);

        // Le cadrage et le cahier des charges ouvrent toujours le devis.
        $references = array_column($devis['creation'], 'ref');
        $this->assertContains('A-01', $references);
        $this->assertContains('A-02', $references);
    }

    /**
     * Application mobile Standard : livraison prioritaire, remise commerciale
     * et frais annuels récurrents.
     */
    public function test_mobile_quote_applies_acceleration_discount_and_annual_costs(): void
    {
        $devis = $this->builder->construire([
            'type' => 'application_mobile',
            'niveau' => 'STANDARD',
            'ecrans' => 2,
            'plateforme' => 'android',
            'opt' => [],
            'domaine' => 'com',
            'hebergement' => 'mutualise',
            'emails' => 1,
            'maintenance' => 'aucune',
            'accelere' => true,
            'remise' => 0.05,
        ]);

        // 900 000 HT de création + 30 % de majoration (270 000).
        $this->assertSame(900000, $devis['sous_total_ht']);
        $this->assertSame(270000, $devis['majoration']);
        $this->assertSame(58500, $devis['remise']);
        $this->assertSame(1111500, $devis['net_ht']);
        $this->assertSame(200070, $devis['tva']);
        $this->assertSame(1311570, $devis['ttc']);

        $this->assertSame(524628, $devis['acompte']);
        $this->assertSame(393471, $devis['deuxieme']);
        $this->assertSame(393471, $devis['solde']);

        // Domaine, hébergement, serveur applicatif, SSL et une boîte e-mail.
        $this->assertSame(463000, $devis['recurrent_ht']);
        $this->assertSame(546340, $devis['recurrent_ttc']);

        $recurrentReferences = array_column($devis['recurrentes'], 'ref');
        $this->assertContains('H-01', $recurrentReferences);
        $this->assertContains('H-03', $recurrentReferences);
        $this->assertContains('H-08', $recurrentReferences);
        $this->assertContains('H-06', $recurrentReferences);
    }

    /**
     * Le niveau Essentiel écarte les prestations réservées aux niveaux supérieurs.
     */
    public function test_essentiel_level_excludes_advanced_lines(): void
    {
        $devis = $this->builder->construire([
            'type' => 'site_vitrine',
            'niveau' => 'ESSENTIEL',
            'pages' => 5,
            'identite' => 'aucune',
            'contenus' => 'fournis',
            'opt' => [],
            'domaine' => 'aucun',
            'hebergement' => 'aucun',
            'emails' => 0,
            'maintenance' => 'aucune',
            'accelere' => false,
        ]);

        $references = array_column($devis['creation'], 'ref');

        foreach (['A-05', 'B-07', 'C-14', 'I-03', 'C-17', 'L-03'] as $excluded) {
            $this->assertNotContains($excluded, $references);
        }

        // Un seul gabarit intérieur est décliné au niveau Essentiel.
        $interiorTemplate = collect($devis['creation'])->firstWhere('ref', 'B-02');
        $this->assertSame(1, $interiorTemplate['quantite']);
    }

    /**
     * Les options cochées alimentent le devis en lignes dédiées.
     */
    public function test_selected_options_add_catalogue_lines(): void
    {
        $devis = $this->builder->construire([
            'type' => 'site_vitrine',
            'niveau' => 'STANDARD',
            'pages' => 6,
            'identite' => 'logo_charte',
            'contenus' => 'rediger',
            'opt' => ['blog', 'galerie', 'multilingue'],
            'domaine' => 'aucun',
            'hebergement' => 'aucun',
            'emails' => 0,
            'maintenance' => 'aucune',
            'accelere' => false,
        ]);

        $references = array_column($devis['creation'], 'ref');

        $this->assertContains('B-03', $references);
        $this->assertContains('B-04', $references);
        $this->assertContains('C-04', $references);
        $this->assertContains('C-03', $references);
        $this->assertContains('C-10', $references);

        // La page Réalisations est facturée à part : elle n'est pas comptée deux fois.
        $interiorPages = collect($devis['creation'])->firstWhere('ref', 'C-02');
        $this->assertSame(4, $interiorPages['quantite']);

        // Une page sur deux porte un texte de fond quand DOMINIUM rédige.
        $content = collect($devis['creation'])->firstWhere('ref', 'C-12');
        $this->assertSame(3, $content['quantite']);
    }
}
