<?php

return [

    /*
    |--------------------------------------------------------------------------
    | First Super Admin accounts
    |--------------------------------------------------------------------------
    |
    | The general `admin` role and the `super_admin` role are two distinct
    | roles: an administrator keeps the general dashboard and NEVER gains the
    | private Super Admin space.
    |
    | List here the email addresses of the accounts that must own the private
    | Super Admin space. Nothing is promoted automatically: the list is empty
    | by default and the migration leaves every administrator untouched.
    |
    | To designate an account afterwards, use the console command:
    |     php artisan super-admin:promote admin@example.com
    |     php artisan super-admin:promote admin@example.com --revoke
    |
    */

    'super_admin_emails' => [],

    /*
    |--------------------------------------------------------------------------
    | Private Spaces Navigation
    |--------------------------------------------------------------------------
    |
    | The general dashboard sidebar is defined in config/admin_brand.php and is
    | intentionally left untouched. Each private space (Super Admin, developer)
    | declares its own navigation here; the shared admin layout renders the
    | first space that matches the current route.
    |
    | The keys are resolved from the current route name by the shared admin
    | layout (components/layouts/admin.blade.php).
    |
    | Rule: a private space only links to its own routes. The Super Admin keeps a
    | global vision from « Vue globale », « Développeurs » and « Prospects &
    | attribution » (all prospects, all clients, every developer's follow-ups) and
    | never leaves his space from the sidebar; the general dashboard stays
    | reachable through its own URL, exactly as before.
    |
    */

    'super_admin' => [

        'heading' => 'Super Admin',

        'nav_items' => [
            [
                'route' => 'super_admin.dashboard',
                'route_pattern' => 'super_admin.dashboard',
                'icon' => 'fa-solid fa-shield-halved',
                'label' => 'Vue globale',
            ],
            [
                'route' => 'super_admin.developers.index',
                'route_pattern' => 'super_admin.developers.*',
                'icon' => 'fa-solid fa-laptop-code',
                'label' => 'Développeurs',
            ],
            [
                'route' => 'super_admin.prospects.index',
                'route_pattern' => 'super_admin.prospects.*',
                'icon' => 'fa-solid fa-user-clock',
                'label' => 'Prospects & attribution',
            ],
        ],

    ],

    'developer' => [

        'heading' => 'Développeur',

        'nav_items' => [
            [
                'route' => 'developer.dashboard',
                'route_pattern' => 'developer.dashboard',
                'icon' => 'fa-solid fa-list-check',
                'label' => 'Mes suivis',
            ],
            [
                'route' => 'developer.prospects.index',
                'route_pattern' => 'developer.prospects.*',
                'icon' => 'fa-solid fa-user-clock',
                'label' => 'Mes prospects',
            ],
        ],

    ],

];
