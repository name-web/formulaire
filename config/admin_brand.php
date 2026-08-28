<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Brand Configuration
    |--------------------------------------------------------------------------
    |
    | Logo, brand name, and other branding elements for the admin panel.
    |
    */

    'logo' => 'IMAGES/Logo dominium.png',
    'brand_name' => 'DOMINIUM',
    'app_name' => 'DOMINIUM TECH',

    'nav_items' => [
        [
            'route' => 'admin.dashboard',
            'route_pattern' => 'admin.dashboard',
            'icon' => 'fa-solid fa-chart-line',
            'label' => 'Dashboard',
        ],
        [
            'route' => 'admin.projects.index',
            'route_pattern' => 'admin.projects.*',
            'icon' => 'fa-solid fa-folder-open',
            'label' => 'Projets',
        ],
        [
            'route' => 'admin.clients.index',
            'route_pattern' => 'admin.clients.*',
            'icon' => 'fa-solid fa-users',
            'label' => 'Clients',
        ],
        [
            'route' => 'admin.questions.index',
            'route_pattern' => 'admin.questions.*',
            'icon' => 'fa-solid fa-circle-question',
            'label' => 'Questions',
        ],
        [
            'route' => 'admin.users.index',
            'route_pattern' => 'admin.users.*',
            'icon' => 'fa-solid fa-user-shield',
            'label' => 'Utilisateurs',
        ],
    ],

];
