<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project Types Configuration
    |--------------------------------------------------------------------------
    |
    | Each project type is defined with a unique key, label, icon (Font Awesome),
    | color, and description. This config drives the start form and can be
    | extended by adding new entries here.
    |
    */

    'types' => [

        'site_vitrine' => [
            'label' => 'Site vitrine',
            'icon' => 'fa-solid fa-globe',
            'color' => '#3b82f6',
            'description' => 'Présentez votre activité et vos services avec un site professionnel.',
        ],

        'ecommerce' => [
            'label' => 'Boutique en ligne',
            'icon' => 'fa-solid fa-bag-shopping',
            'color' => '#10b981',
            'description' => 'Vendez vos produits en ligne avec une boutique rapide et sécurisée.',
        ],

        'application_mobile' => [
            'label' => 'Application mobile',
            'icon' => 'fa-solid fa-mobile-screen',
            'color' => '#8b5cf6',
            'description' => 'iOS et Android',
        ],

        'logiciel' => [
            'label' => 'Logiciel / ERP / CRM',
            'icon' => 'fa-solid fa-gears',
            'color' => '#f59e0b',
            'description' => 'Solution sur mesure',
        ],

        'autre' => [
            'label' => 'Autre',
            'icon' => 'fa-solid fa-wand-magic-sparkles',
            'color' => '#ec4899',
            'description' => 'Décrivez votre vision',
        ],

    ],

];
