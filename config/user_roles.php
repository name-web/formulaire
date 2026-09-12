<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Roles Configuration
    |--------------------------------------------------------------------------
    |
    | All available user roles with their labels and badge CSS classes.
    | The admin create/edit forms, table badges, and validation use this list.
    |
    */

    'roles' => [

        'super_admin' => [
            'label' => 'Super Administrateur',
            'class' => 'badge-admin',
        ],

        'admin' => [
            'label' => 'Administrateur',
            'class' => 'badge-admin',
        ],

        'developer' => [
            'label' => 'Développeur',
            'class' => 'badge-user',
        ],

        'user' => [
            'label' => 'Utilisateur',
            'class' => 'badge-user',
        ],

    ],

    'default_role' => 'user',

];
