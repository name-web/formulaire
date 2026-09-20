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
            'label' => 'Prospects',
        ],
        [
            'route' => 'admin.client-clients.index',
            'route_pattern' => 'admin.client-clients.*',
            'icon' => 'fa-solid fa-user-check',
            'label' => 'Clients',
        ],
        [
            'route' => 'admin.comments.index',
            'route_pattern' => 'admin.comments.*',
            'icon' => 'fa-solid fa-comments',
            'label' => 'Commentaires',
        ],
        [
            'route' => 'admin.activities.index',
            'route_pattern' => 'admin.activities.*',
            'icon' => 'fa-solid fa-clock-rotate-left',
            'label' => 'Activités',
        ],
        [
            'route' => 'admin.quotes.index',
            'route_pattern' => 'admin.quotes.*',
            'icon' => 'fa-solid fa-file-invoice-dollar',
            'label' => 'Devis',
        ],
        [
            'route' => 'admin.users.index',
            'route_pattern' => 'admin.users.*',
            'icon' => 'fa-solid fa-user-shield',
            'label' => 'Utilisateurs',
        ],
    ],

];
