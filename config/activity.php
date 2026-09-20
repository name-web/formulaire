<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Journal d'activité central
    |--------------------------------------------------------------------------
    |
    | Chaque type d'activité enregistré dans `client_activities` est décrit ici :
    | son libellé, son icône et le badge réutilisé depuis la charte existante.
    | Le journal est en lecture seule : on ajoute une ligne, on n'en modifie
    | jamais, et un prospect ou un client reste une donnée unique en base.
    |
    | `group` permet de filtrer par famille d'action depuis la page Activités.
    |
    */

    'types' => [

        'viewed' => [
            'label' => 'Consultation',
            'group' => 'consultation',
            'icon' => 'fa-solid fa-eye',
            'badge' => 'badge-user',
        ],

        'comment_added' => [
            'label' => 'Note ajoutée',
            'group' => 'suivi',
            'icon' => 'fa-solid fa-comment-dots',
            'badge' => 'badge-in_progress',
        ],

        'status_changed' => [
            'label' => 'Changement de statut',
            'group' => 'statut',
            'icon' => 'fa-solid fa-arrows-rotate',
            'badge' => 'badge-pending',
        ],

        'follow_up_scheduled' => [
            'label' => 'Relance programmée',
            'group' => 'relance',
            'icon' => 'fa-solid fa-calendar-plus',
            'badge' => 'badge-pending',
        ],

        'follow_up_completed' => [
            'label' => 'Relance effectuée',
            'group' => 'relance',
            'icon' => 'fa-solid fa-calendar-check',
            'badge' => 'badge-completed',
        ],

        'converted' => [
            'label' => 'Conversion en client',
            'group' => 'conversion',
            'icon' => 'fa-solid fa-user-check',
            'badge' => 'badge-client',
        ],

        'updated' => [
            'label' => 'Modification métier',
            'group' => 'modification',
            'icon' => 'fa-solid fa-pen',
            'badge' => 'badge-user',
        ],

        'assignment_changed' => [
            'label' => 'Attribution',
            'group' => 'attribution',
            'icon' => 'fa-solid fa-user-gear',
            'badge' => 'badge-submitted',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-doublon des consultations
    |--------------------------------------------------------------------------
    |
    | Une fiche consultée puis rechargée ne doit pas remplir le journal : une
    | consultation identique (même commercial, même prospect) n'est enregistrée
    | qu'une fois par fenêtre.
    |
    */

    'view_dedup_minutes' => 30,

    /*
    |--------------------------------------------------------------------------
    | Aperçus des dashboards
    |--------------------------------------------------------------------------
    */

    'dashboard_preview_count' => 6,

    'per_page' => 20,

];
