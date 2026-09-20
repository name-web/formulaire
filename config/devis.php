<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paramètres de l'émetteur (feuille PARAMETRES — claude file.md)
    |--------------------------------------------------------------------------
    |
    | Identité du prestataire reprise sur le devis provisoire et sur le devis
    | administratif. Ces valeurs sont la source de vérité fonctionnelle du
    | nouveau formulaire.
    |
    */

    'societe' => 'DOMINIUM GROUP SARL',

    'branche' => 'DOMINIUM TECH — Agence digitale',

    'adresse' => "Abidjan, Biabou Portail — 200 m derrière pharmacie Route d'Alépé",

    'telephone' => '+225 05 54 09 42 42 / +225 07 59 20 36 90',

    'email' => 'info@dominium-civ.com',

    'site_web' => 'www.dominium-civ.com',

    'devise' => 'FCFA',

    /*
    |--------------------------------------------------------------------------
    | Fiscalité et remise commerciale
    |--------------------------------------------------------------------------
    */

    'tva' => 0.18,

    'tva_active' => true,

    'remise_max' => 0.05,

    /*
    |--------------------------------------------------------------------------
    | Échéancier de paiement (parts du total TTC)
    |--------------------------------------------------------------------------
    |
    | Le solde n'est jamais un pourcentage arrondi : il absorbe les arrondis
    | des deux premiers versements (voir DevisBuilder::calculer()).
    |
    */

    'echeancier' => [
        'acompte' => 0.40,
        'deuxieme' => 0.30,
        'solde' => 0.30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Durées, majorations et pénalités
    |--------------------------------------------------------------------------
    */

    'validite_jours' => 30,

    'majoration_accelere' => 0.30,

    'penalite_mensuelle' => 0.02,

    'garantie_jours' => 30,

    /*
    |--------------------------------------------------------------------------
    | Niveaux de prestation
    |--------------------------------------------------------------------------
    |
    | Ces clés indexent les prix unitaires du catalogue (config/catalogue_prestations.php).
    |
    */

    'niveaux' => [
        'ESSENTIEL' => 'Essentiel',
        'STANDARD' => 'Standard',
        'PREMIUM' => 'Premium',
    ],

    'niveau_defaut' => 'STANDARD',

    /*
    |--------------------------------------------------------------------------
    | Calcul du délai de réalisation (jours ouvrés)
    |--------------------------------------------------------------------------
    |
    | Formule définie dans claude file.md : somme des jours pondérés par la
    | quantité, appliquée d'un facteur global, avec un plancher selon le mode
    | de livraison.
    |
    */

    'delai' => [
        'minimum' => 7,
        'minimum_accelere' => 5,
        'facteur_global' => 0.62,
        'facteur_accelere' => 0.7,
        'facteur_lot' => 0.55,
        'plafond_lot' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Types de projet proposés par le nouveau formulaire
    |--------------------------------------------------------------------------
    |
    | Ordre exact du parcours client. Les libellés, icônes et couleurs vivent
    | dans config/project_types.php, qui reste le registre unique des types
    | (les prospections historiques y puisent aussi leurs libellés).
    |
    */

    'form_types' => [
        'site_vitrine',
        'ecommerce',
        'logiciel',
        'scolaire',
        'application_mobile',
        'refonte',
    ],

];
