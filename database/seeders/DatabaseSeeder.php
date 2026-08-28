<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kamallareleve233@gmail.com'],
            [
                'name' => 'Kamal Admin',
                'password' => 'Kamal222.com',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->call(QuestionSeeder::class);
    }
}
