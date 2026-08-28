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

        'admin' => [
            'label' => 'Administrateur',
            'class' => 'badge-admin',
        ],

        'user' => [
            'label' => 'Utilisateur',
            'class' => 'badge-user',
        ],

    ],

    'default_role' => 'user',

];
