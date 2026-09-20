<?php

namespace App\Services\Devis;

/**
 * Moteur de devis du nouveau formulaire.
 *
 * Portage fidèle du moteur décrit dans le dossier fonctionnel (feuille
 * CATALOGUE_TARIFS + fonctions `construire()` et `calculer()`) : les lignes du
 * devis sont déduites des réponses du client, puis les totaux, l'échéancier,
 * les frais récurrents et le délai de réalisation sont calculés.
 *
 * Les clés du tableau retourné suivent la feuille de calcul d'origine : ce
 * payload est un instantané de document, restitué tel quel côté client et côté
 * administration.
 */
class DevisBuilder
{
    public function __construct(
        private DevisCatalogue $catalogue,
    ) {}

    /**
     * Libellé d'objet du devis pour un type de projet.
     */
    public function objet(?string $type): string
    {
        return (string) config("project_types.types.{$type}.objet", 'Prestation numérique');
    }

    /**
     * Une ligne de devis, ou null si la référence est inconnue ou la quantité nulle.
     *
     * @return array<string, mixed>|null
     */
    public function ligne(string $ref, int|float $quantite, ?string $niveau = null): ?array
    {
        $niveau ??= $this->niveauDefaut();
        $prestation = $this->catalogue->get($ref);

        if ($prestation === null || $quantite <= 0) {
            return null;
        }

        return [
            'ref' => $ref,
            'nom' => $prestation['prestation'],
            'inclus' => $prestation['inclus'],
            'unite' => $prestation['unite'],
            'categorie' => $prestation['categorie'],
            'niveau' => $niveau,
            'quantite' => (int) round($quantite),
            'prix_unitaire' => $this->catalogue->price($ref, $niveau),
            'jours' => (int) $prestation['jours'],
            'recurrent' => $this->catalogue->isRecurrente($ref),
        ];
    }

    /**
     * Construit la liste des lignes du devis à partir des réponses du formulaire.
     *
     * @param  array<string, mixed>  $answers
     * @return array<int, array<string, mixed>>
     */
    public function lignes(array $answers): array
    {
        $niveau = $this->niveau($answers);
        $type = $answers['type'] ?? null;
        $options = $this->options($answers);
        $modules = $this->options($answers, 'modules');

        /** @var array<int, array<string, mixed>> $lignes */
        $lignes = [];

        $ajouter = function (string $ref, int|float $quantite, ?string $ligneNiveau = null) use (&$lignes, $niveau): void {
            $ligne = $this->ligne($ref, $quantite, $ligneNiveau ?? $niveau);

            if ($ligne !== null) {
                $lignes[] = $ligne;
            }
        };

        // L'étude de cadrage ouvre toujours le devis, quel que soit le projet.
        $ajouter('A-01', 1);
        $ajouter('A-02', 1);

        // Identité visuelle : commune à tous les types qui la proposent.
        $identite = $answers['identite'] ?? 'aucune';
        if ($identite === 'logo') {
            $ajouter('B-03', 1);
        }
        if ($identite === 'logo_charte') {
            $ajouter('B-03', 1);
            $ajouter('B-04', 1);
        }

        if ($type === 'site_vitrine' || $type === 'refonte') {
            $pages = $this->entierPositif($answers, 'pages', 5);

            if ($type === 'refonte') {
                $ajouter('C-16', 1);
            }
            if ($niveau !== 'ESSENTIEL') {
                $ajouter('A-05', 1);
            }

            $ajouter('B-01', 1);
            // Essentiel : un seul gabarit intérieur décliné. Sinon jusqu'à 3.
            $ajouter('B-02', $niveau === 'ESSENTIEL' ? 1 : min($pages - 1, 3));

            if ($niveau !== 'ESSENTIEL') {
                $ajouter('B-07', 1);
            }

            $ajouter('C-01', 1);
            // La page Réalisations est facturée à part (C-03) : on ne la compte pas deux fois.
            $ajouter('C-02', $pages - 1 - (in_array('galerie', $options, true) ? 1 : 0));

            if (in_array('galerie', $options, true)) {
                $ajouter('C-03', 1);
            }
            if (in_array('blog', $options, true)) {
                $ajouter('C-04', 1);
            }

            $ajouter('C-05', 1);

            if (in_array('form_avance', $options, true)) {
                $ajouter('C-06', 1);
            }
            if (in_array('rdv', $options, true)) {
                $ajouter('C-08', 1);
            }
            if (in_array('membre', $options, true)) {
                $ajouter('C-09', 1);
            }
            if (in_array('multilingue', $options, true)) {
                $ajouter('C-10', 1);
            }

            $ajouter('C-07', 1);

            // Une page sur deux porte un vrai texte de fond.
            if (($answers['contenus'] ?? 'fournis') === 'fournis') {
                $ajouter('C-11', 1);
            } else {
                $ajouter('C-12', (int) ceil($pages / 2));
            }

            $ajouter('C-13', 1);

            if ($niveau !== 'ESSENTIEL') {
                $ajouter('C-14', 1);
            }

            $ajouter('I-01', 1);

            if ($niveau !== 'ESSENTIEL') {
                $ajouter('I-03', 1);
            }
            if ($niveau === 'PREMIUM') {
                $ajouter('I-02', 1);
            }

            $ajouter('C-15', 1);

            if ($niveau !== 'ESSENTIEL') {
                $ajouter('C-17', 1);
            }

            $ajouter('L-01', 1);

            if ($niveau !== 'ESSENTIEL') {
                $ajouter('L-03', 1);
            }

            $ajouter('C-18', 1);
        }

        if ($type === 'ecommerce') {
            $pages = $this->entierPositif($answers, 'pages', 4);
            $produits = $this->entierPositif($answers, 'produits', 50);

            $ajouter('B-01', 1);
            $ajouter('B-02', min($pages - 1, 3));
            $ajouter('B-07', 1);
            $ajouter('C-01', 1);
            $ajouter('C-02', $pages - 1);
            $ajouter('D-01', 1);

            $paiement = $answers['paiement'] ?? 'mobile';
            if ($paiement === 'mobile' || $paiement === 'les_deux') {
                $ajouter('D-02', 1);
            }
            if ($paiement === 'carte' || $paiement === 'les_deux') {
                $ajouter('D-03', 1);
            }

            $ajouter('D-04', $produits);

            if (in_array('photos_produits', $options, true)) {
                $ajouter('D-09', $produits);
            }

            $ajouter('D-05', 1);

            if (in_array('promos', $options, true)) {
                $ajouter('D-06', 1);
            }
            if (in_array('notifs', $options, true)) {
                $ajouter('D-07', 1);
            }
            if (in_array('dashboard', $options, true)) {
                $ajouter('D-08', 1);
            }

            $ajouter('C-13', 1);
            $ajouter('C-14', 1);
            $ajouter('I-01', 1);
            $ajouter('C-15', 1);
            $ajouter('C-17', 1);
            $ajouter('L-01', 1);
            $ajouter('L-03', 1);
            $ajouter('C-18', 1);
        }

        if ($type === 'logiciel') {
            $ajouter('F-01', 1);

            $mapping = [
                'stock' => 'F-02',
                'caisse' => 'F-03',
                'facturation' => 'F-04',
                'crm' => 'F-05',
                'rh' => 'F-06',
                'compta' => 'F-07',
            ];

            foreach ($modules as $module) {
                if (isset($mapping[$module])) {
                    $ajouter($mapping[$module], 1);
                }
            }

            $ajouter('F-08', 1);
            $ajouter('F-09', 1);

            if (($answers['reprise'] ?? 'non') === 'oui') {
                $ajouter('F-10', 1);
            }

            $ajouter('F-11', $this->entierPositif($answers, 'postes', 3));
            $ajouter('L-02', $this->entierPositif($answers, 'formation', 1));
            $ajouter('L-03', 1);

            if (in_array('demarrage', $options, true)) {
                $ajouter('L-05', 1);
            }

            $ajouter('C-18', 1);
        }

        if ($type === 'scolaire') {
            $ajouter('G-01', 1);

            $mapping = [
                'eleves' => 'G-02',
                'notes' => 'G-03',
                'bulletins' => 'G-04',
                'absences' => 'G-05',
                'parents' => 'G-06',
                'paiements' => 'G-07',
                'edt' => 'G-08',
                'sms' => 'G-09',
            ];

            foreach ($modules as $module) {
                if (isset($mapping[$module])) {
                    $ajouter($mapping[$module], 1);
                }
            }

            $ajouter('F-09', 1);

            if (($answers['reprise'] ?? 'non') === 'oui') {
                $ajouter('F-10', 1);
            }

            $ajouter('L-02', 2);
            $ajouter('L-03', 1);
            $ajouter('L-05', 2);
            $ajouter('C-18', 1);
        }

        if ($type === 'application_mobile') {
            $ecrans = $this->entierPositif($answers, 'ecrans', 10);

            $ajouter('E-01', $ecrans);

            $plateforme = $answers['plateforme'] ?? 'android';
            if ($plateforme === 'android' || $plateforme === 'les_deux') {
                $ajouter('E-02', $ecrans);
            }
            if ($plateforme === 'ios' || $plateforme === 'les_deux') {
                $ajouter('E-03', $ecrans);
            }

            $ajouter('E-04', 1);

            if (in_array('push', $options, true)) {
                $ajouter('E-05', 1);
            }
            if (in_array('horsligne', $options, true)) {
                $ajouter('E-06', 1);
            }

            $ajouter('E-07', 1);
            $ajouter('L-02', 1);
            $ajouter('L-04', 3);
            $ajouter('C-18', 1);
        }

        // Frais annuels : hors coût de création, présentés séparément.
        $domaine = $answers['domaine'] ?? 'com';
        if ($domaine === 'com') {
            $ajouter('H-01', 1);
        }
        if ($domaine === 'ci') {
            $ajouter('H-02', 1);
        }

        $hebergement = $answers['hebergement'] ?? 'mutualise';
        if ($hebergement === 'mutualise') {
            $ajouter('H-03', 1);
        }
        if ($hebergement === 'vps') {
            $ajouter('H-04', 1);
        }
        if ($type === 'application_mobile') {
            $ajouter('H-08', 1);
        }
        if ($hebergement !== 'aucun') {
            $ajouter('H-05', 1);
        }

        $boites = max(0, (int) ($answers['emails'] ?? 0));
        if ($boites > 0) {
            $ajouter('H-06', $boites);
        }

        if (in_array('sauvegarde', $options, true)) {
            $ajouter('H-07', 1);
        }

        $maintenance = $answers['maintenance'] ?? 'aucune';
        if ($maintenance !== 'aucune') {
            $abonnements = ['essentiel' => 'K-01', 'business' => 'K-02', 'partenaire' => 'K-03'];
            if (isset($abonnements[$maintenance])) {
                $ajouter($abonnements[$maintenance], 12);
            }
        }

        return $lignes;
    }

    /**
     * Calcule les totaux, l'échéancier, les frais récurrents et le délai.
     *
     * @param  array{lignes: array<int, array<string, mixed>>, accelere?: bool, remise?: float}  $devis
     * @return array<string, mixed>
     */
    public function calculer(array $devis): array
    {
        $lignes = $devis['lignes'] ?? [];

        $creation = array_values(array_filter($lignes, fn (array $l): bool => empty($l['recurrent'])));
        $recurrentes = array_values(array_filter($lignes, fn (array $l): bool => ! empty($l['recurrent'])));

        $montantLigne = fn (array $l): int => (int) $l['quantite'] * (int) $l['prix_unitaire'];

        $sousTotalHt = array_sum(array_map($montantLigne, $creation));

        $accelere = (bool) ($devis['accelere'] ?? false);
        $majoration = $accelere ? (int) round($sousTotalHt * (float) config('devis.majoration_accelere')) : 0;
        $remise = (int) round(($sousTotalHt + $majoration) * (float) ($devis['remise'] ?? 0));
        $netHt = $sousTotalHt + $majoration - $remise;

        $tvaActive = (bool) config('devis.tva_active');
        $tva = $tvaActive ? (int) round($netHt * (float) config('devis.tva')) : 0;
        $ttc = $netHt + $tva;

        $recurrentHt = array_sum(array_map($montantLigne, $recurrentes));
        $recurrentTtc = $recurrentHt + ($tvaActive ? (int) round($recurrentHt * (float) config('devis.tva')) : 0);

        $echeancier = config('devis.echeancier');
        $acompte = (int) round($ttc * (float) $echeancier['acompte']);
        $deuxieme = (int) round($ttc * (float) $echeancier['deuxieme']);
        // Le solde absorbe les arrondis des deux premiers versements.
        $solde = $ttc - $acompte - $deuxieme;

        return [
            'creation' => $creation,
            'recurrentes' => $recurrentes,
            'sous_total_ht' => $sousTotalHt,
            'majoration' => $majoration,
            'remise' => $remise,
            'net_ht' => $netHt,
            'tva' => $tva,
            'ttc' => $ttc,
            'recurrent_ht' => $recurrentHt,
            'recurrent_ttc' => $recurrentTtc,
            'acompte' => $acompte,
            'deuxieme' => $deuxieme,
            'solde' => $solde,
            'jours' => $this->delai($creation, $accelere),
        ];
    }

    /**
     * Construit et calcule le devis complet à partir des réponses du formulaire.
     *
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    public function construire(array $answers): array
    {
        return $this->calculer([
            'lignes' => $this->lignes($answers),
            'accelere' => (bool) ($answers['accelere'] ?? false),
            'remise' => (float) ($answers['remise'] ?? 0),
        ]);
    }

    /**
     * Délai de réalisation estimé, en jours ouvrés.
     *
     * @param  array<int, array<string, mixed>>  $creation
     */
    private function delai(array $creation, bool $accelere): int
    {
        $config = config('devis.delai');
        $jour = 0.0;

        foreach ($creation as $ligne) {
            $quantite = (int) $ligne['quantite'];
            $poids = $quantite > 1
                ? min($quantite, (int) $config['plafond_lot']) * (float) $config['facteur_lot']
                : 1;

            $jour += (int) $ligne['jours'] * $poids;
        }

        $jours = max((int) $config['minimum'], (int) round($jour * (float) $config['facteur_global']));

        if ($accelere) {
            $jours = max((int) $config['minimum_accelere'], (int) round($jours * (float) $config['facteur_accelere']));
        }

        return $jours;
    }

    /**
     * @param  array<string, mixed>  $answers
     */
    private function niveau(array $answers): string
    {
        $niveau = mb_strtoupper((string) ($answers['niveau'] ?? ''));

        return array_key_exists($niveau, config('devis.niveaux', []))
            ? $niveau
            : $this->niveauDefaut();
    }

    private function niveauDefaut(): string
    {
        return (string) config('devis.niveau_defaut', 'STANDARD');
    }

    /**
     * Nombre entier issu des réponses, ramené au minimum 1 avec valeur par défaut.
     *
     * @param  array<string, mixed>  $answers
     */
    private function entierPositif(array $answers, string $key, int $defaut): int
    {
        $valeur = max(0, (int) ($answers[$key] ?? 0));

        return max(1, $valeur > 0 ? $valeur : $defaut);
    }

    /**
     * Liste de valeurs cochées (options ou modules).
     *
     * @param  array<string, mixed>  $answers
     * @return array<int, string>
     */
    private function options(array $answers, string $key = 'opt'): array
    {
        $valeurs = $answers[$key] ?? [];

        if (! is_array($valeurs)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $valeurs), fn (string $v): bool => $v !== ''));
    }
}
