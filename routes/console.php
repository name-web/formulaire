<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Designate (or remove) the Super Admin role on an existing account.
 *
 * This is the supported way to create the first Super Admin without
 * promoting every general administrator: `admin` and `super_admin` stay two
 * distinct roles (see config/private_spaces.php).
 */
Artisan::command('super-admin:promote {email : Email du compte concerné} {--revoke : Redonne le rôle admin général}', function (string $email) {
    $user = User::where('email', $email)->first();

    if ($user === null) {
        $this->components->error("Aucun compte ne correspond à l'email « {$email} ».");

        return 1;
    }

    $role = $this->option('revoke') ? 'admin' : 'super_admin';

    if ($user->role === $role) {
        $this->components->warn("Le compte « {$user->name} » possède déjà le rôle « {$role} ».");

        return 0;
    }

    $user->update(['role' => $role]);

    $this->components->info("Le compte « {$user->name} » ({$user->email}) a maintenant le rôle « {$role} ».");

    return 0;
})->purpose('Désigne ou retire le rôle Super Administrateur pour un compte existant');
