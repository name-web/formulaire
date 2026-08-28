<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kamallareleve233@gmail.com'],
            [
                'name' => 'Kamal Admin',
                'password' => Hash::make('Kamal222.com'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->call(QuestionSeeder::class);
    }
}
