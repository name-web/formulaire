<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Stat Cards Configuration
    |--------------------------------------------------------------------------
    |
    | Each stat card shown on the admin dashboard.
    | The 'key' matches the stat key in DashboardController.
    | Add/remove/reorder cards here — the dashboard adapts automatically.
    |
    */

    'stat_cards' => [

        [
            'key' => 'total_projects',
            'icon' => 'fa-solid fa-folder-open',
            'color' => '#000000',
            'bg' => '#ffb703',
            'label' => 'Projets totaux',
        ],

        [
            'key' => 'submitted_projects',
            'icon' => 'fa-solid fa-inbox',
            'color' => '#000000',
            'bg' => '#ffb703',
            'label' => 'Nouvelles soumissions',
        ],

        [
            'key' => 'pending_projects',
            'icon' => 'fa-solid fa-clock',
            'color' => '#000000',
            'bg' => '#ffb703',
            'label' => 'En attente',
        ],

        [
            'key' => 'in_progress_projects',
            'icon' => 'fa-solid fa-spinner',
            'color' => '#000000',
            'bg' => '#ffb703',
            'label' => 'En cours',
        ],

        [
            'key' => 'completed_projects',
            'icon' => 'fa-solid fa-check-circle',
            'color' => '#000000',
            'bg' => '#ffb703',
            'label' => 'Terminés',
        ],

        [
            'key' => 'total_clients',
            'icon' => 'fa-solid fa-users',
            'color' => '#000000',
            'bg' => '#ffb703',
            'label' => 'Prospects',
        ],

    ],

    'recent_projects_count' => 8,
    'recent_clients_count' => 5,

    'months' => [
        '01' => 'Jan', '02' => 'Fév', '03' => 'Mar', '04' => 'Avr',
        '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Aoû',
        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Déc',
    ],

    'months_back' => 6,

];
