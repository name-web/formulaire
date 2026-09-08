<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the session driver is configured to file (not database).
     *
     * When SESSION_DRIVER=database, running migrate:fresh destroys all
     * sessions — forcing a re-login after every DB reset. File-based
     * sessions survive migrate:fresh because they live on disk.
     */
    public function test_session_driver_is_file_in_production_config(): void
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        $this->assertStringContainsString(
            'SESSION_DRIVER=file',
            $envContent,
            '.env should use SESSION_DRIVER=file to prevent session loss on migrate:fresh'
        );
    }

    public function test_admin_session_survives_across_requests(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();

        // A second request should also be authenticated
        $this->get('/admin')->assertOk();
        $this->assertAuthenticatedAs($user);
    }
}
