<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo 'users='.DB::table('users')->count().' | questions='.DB::table('questions')->count().PHP_EOL;

$admin = User::where('email', 'kamallareleve233@gmail.com')->first();

if ($admin) {
    echo 'Admin OK: role='.$admin->role.' | actif='.($admin->is_active ? 'oui' : 'non').PHP_EOL;
    echo 'Mot de passe "Kamal222.com" valide: '.(Hash::check('Kamal222.com', $admin->password) ? 'OUI' : 'NON').PHP_EOL;
} else {
    echo 'ADMIN INTROUVABLE'.PHP_EOL;
}
