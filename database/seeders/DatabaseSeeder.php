<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedGeneralAdmin();

        $this->call(QuestionSeeder::class);
    }

    /**
     * Create the general administrator account on a fresh installation.
     *
     * Credentials come from the environment (config/admin.php → ADMIN_PASSWORD),
     * so no password is ever versioned. Nothing is created when ADMIN_PASSWORD
     * is not set, an existing account is never modified, and the seeded role is
     * always `admin`: the Super Admin role is only ever granted explicitly with
     * `php artisan super-admin:promote <email>`.
     */
    private function seedGeneralAdmin(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! $email || ! $password) {
            return;
        }

        if (User::where('email', $email)->exists()) {
            return;
        }

        User::create([
            'name' => 'Kamal Admin',
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
