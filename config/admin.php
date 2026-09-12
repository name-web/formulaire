<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Unique Administrator Account
    |--------------------------------------------------------------------------
    |
    | Credentials for the single dashboard administrator. The email is fixed
    | and must always stay as-is. The password must come from the environment
    | (ADMIN_PASSWORD) and is never hardcoded in source code.
    |
    | DatabaseSeeder uses these values to create the account on a fresh
    | installation, but only when ADMIN_PASSWORD is set and the account does not
    | exist yet. The created role is `admin`: the Super Admin role is never
    | granted automatically — designate it explicitly with
    | `php artisan super-admin:promote <email>`.
    |
    */

    'email' => 'kamallareleve233@gmail.com',

    'password' => env('ADMIN_PASSWORD'),

];
