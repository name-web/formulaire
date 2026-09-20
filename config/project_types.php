<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project Types Configuration
    |--------------------------------------------------------------------------
    |
    | Each project type is defined with a unique key, label, icon (Font Awesome),
    | color, and description. This config is the single registry of project
    | types: it labels both the new questionnaire (config/devis.php,
    | `form_types`) and the historical projects created with the previous
    | form. The `objet` key is the quote subject used on the provisional quote.
    |
    */

    'types' => [

        'site_vitrine' => [
            'label' => 'Site vitrine',
            'icon' => 'fa-solid fa-globe',
            'color' => '#3b82f6',
            'description' => 'Présentez votre activité et vos services avec un site professionnel.',
            'objet' => "Création d'un site internet professionnel",
        ],

        'ecommerce' => [
            'label' => 'Boutique en ligne',
            'icon' => 'fa-solid fa-bag-shopping',
            'color' => '#10b981',
            'description' => 'Vendez vos produits en ligne avec une boutique rapide et sécurisée.',
            'objet' => "Création d'une boutique en ligne avec paiement mobile",
        ],

        'application_mobile' => [
            'label' => 'Application mobile',
            'icon' => 'fa-solid fa-mobile-screen',
            'color' => '#8b5cf6',
            'description' => 'iOS et Android',
            'objet' => "Développement d'une application mobile",
        ],

        'logiciel' => [
            'label' => 'Logiciel / ERP / CRM',
            'icon' => 'fa-solid fa-gears',
            'color' => '#f59e0b',
            'description' => 'Solution sur mesure',
            'objet' => "Développement d'un logiciel de gestion",
        ],

        'scolaire' => [
            'label' => 'Plateforme scolaire',
            'icon' => 'fa-solid fa-graduation-cap',
            'color' => '#0ea5e9',
            'description' => 'Élèves, notes, bulletins, espace parents, scolarité.',
            'objet' => "Mise en place d'une plateforme scolaire",
        ],

        'refonte' => [
            'label' => "Refonte d'un site existant",
            'icon' => 'fa-solid fa-rotate',
            'color' => '#f97316',
            'description' => 'Vous avez déjà un site et vous voulez le reprendre à neuf.',
            'objet' => "Refonte d'un site internet existant",
        ],

    ],

];
