<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project Status Configuration
    |--------------------------------------------------------------------------
    |
    | Each status is defined with a unique key, label, CSS class, and icon.
    | Add/remove/edit statuses here — the entire admin adapts automatically.
    |
    */

    'statuses' => [

        'draft' => [
            'label' => 'Brouillon',
            'class' => 'badge-user',
            'icon' => 'fa-file',
        ],

        'submitted' => [
            'label' => 'Soumis',
            'class' => 'badge-submitted',
            'icon' => 'fa-inbox',
        ],

        'pending' => [
            'label' => 'En attente',
            'class' => 'badge-pending',
            'icon' => 'fa-clock',
        ],

        'in_progress' => [
            'label' => 'En cours',
            'class' => 'badge-in_progress',
            'icon' => 'fa-spinner',
        ],

        'completed' => [
            'label' => 'Terminé',
            'class' => 'badge-completed',
            'icon' => 'fa-check-circle',
        ],

        'cancelled' => [
            'label' => 'Annulé',
            'class' => 'badge-cancelled',
            'icon' => 'fa-ban',
        ],

    ],

];
