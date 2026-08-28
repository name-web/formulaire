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
            'color' => '#3b82f6',
            'bg' => 'rgba(59,130,246,0.12)',
            'label' => 'Projets totaux',
        ],

        [
            'key' => 'submitted_projects',
            'icon' => 'fa-solid fa-inbox',
            'color' => '#a855f7',
            'bg' => 'rgba(168,85,247,0.12)',
            'label' => 'Nouvelles soumissions',
        ],

        [
            'key' => 'pending_projects',
            'icon' => 'fa-solid fa-clock',
            'color' => '#fbbf24',
            'bg' => 'rgba(245,158,11,0.12)',
            'label' => 'En attente',
        ],

        [
            'key' => 'in_progress_projects',
            'icon' => 'fa-solid fa-spinner',
            'color' => '#60a5fa',
            'bg' => 'rgba(59,130,246,0.12)',
            'label' => 'En cours',
        ],

        [
            'key' => 'completed_projects',
            'icon' => 'fa-solid fa-check-circle',
            'color' => '#34d399',
            'bg' => 'rgba(16,185,129,0.12)',
            'label' => 'Terminés',
        ],

        [
            'key' => 'total_clients',
            'icon' => 'fa-solid fa-users',
            'color' => '#a78bfa',
            'bg' => 'rgba(139,92,246,0.12)',
            'label' => 'Clients',
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
