<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Fixtures déterministes du parcours E2E navigateur (tests/e2e).
 *
 * Le parcours pilote un vrai navigateur contre un vrai serveur : les comptes et
 * les prospects doivent donc être connus à l'avance. Ce seeder refuse de tourner
 * en production.
 */
class E2ESeeder extends Seeder
{
    public const SUPER_ADMIN_EMAIL = 'e2e-super@domitium.test';

    public const SUPER_ADMIN_PASSWORD = 'E2eSuper2026!';

    public const ADMIN_EMAIL = 'e2e-admin@domitium.test';

    public const ADMIN_PASSWORD = 'E2eAdmin2026!';

    public const DEVELOPER_B_NAME = 'E2E Dev Deux';

    public const DEVELOPER_B_PASSWORD = 'E2eDevB2026!';

    public const ASSIGNED_PROSPECT = 'E2E Prospect Dev Deux';

    public const UNASSIGNED_PROSPECT = 'E2E Prospect À Attribuer';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('E2ESeeder ne doit jamais tourner en production.');
        }

        User::updateOrCreate(
            ['email' => self::SUPER_ADMIN_EMAIL],
            [
                'name' => 'E2E Super Admin',
                'password' => self::SUPER_ADMIN_PASSWORD,
                'role' => 'super_admin',
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'E2E Admin Général',
                'password' => self::ADMIN_PASSWORD,
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        $developer = User::updateOrCreate(
            ['email' => 'e2e-dev-deux@developpeur.local'],
            [
                'name' => self::DEVELOPER_B_NAME,
                'password' => self::DEVELOPER_B_PASSWORD,
                'role' => 'developer',
                'is_active' => true,
            ],
        );

        $assigned = Client::updateOrCreate(
            ['name' => self::ASSIGNED_PROSPECT],
            [
                'status' => 'prospect',
                'phone' => '+2250700000002',
                'assigned_developer_id' => $developer->id,
            ],
        );

        $unassigned = Client::updateOrCreate(
            ['name' => self::UNASSIGNED_PROSPECT],
            [
                'status' => 'prospect',
                'phone' => '+2250700000001',
                'assigned_developer_id' => null,
            ],
        );

        // Un projet soumis rend les fiches et le dashboard général représentatifs.
        foreach ([$assigned, $unassigned] as $client) {
            if ($client->projects()->doesntExist()) {
                Project::create([
                    'client_id' => $client->id,
                    'project_type' => ['site_vitrine'],
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }
        }
    }
}
