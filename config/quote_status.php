<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quote Status Configuration
    |--------------------------------------------------------------------------
    |
    | Chaque statut est défini avec une clé unique, un libellé, une classe CSS
    | et une icône. Ajouter/modifier/supprimer des statuts ici — toute
    | l'interface admin s'adapte automatiquement.
    |
    */

    'statuses' => [

        'draft' => [
            'label' => 'Brouillon',
            'class' => 'badge-user',
            'icon' => 'fa-file',
        ],

        'sent' => [
            'label' => 'Envoyé',
            'class' => 'badge-submitted',
            'icon' => 'fa-paper-plane',
        ],

        'accepted' => [
            'label' => 'Accepté',
            'class' => 'badge-completed',
            'icon' => 'fa-check-circle',
        ],

        'rejected' => [
            'label' => 'Refusé',
            'class' => 'badge-cancelled',
            'icon' => 'fa-circle-xmark',
        ],

        'paid' => [
            'label' => 'Réglé',
            'class' => 'badge-client',
            'icon' => 'fa-money-bill-wave',
        ],

        'cancelled' => [
            'label' => 'Annulé',
            'class' => 'badge-cancelled',
            'icon' => 'fa-ban',
        ],

    ],

];
