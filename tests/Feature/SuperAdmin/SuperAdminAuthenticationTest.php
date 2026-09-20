<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/super-admin/login');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_authenticate(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->post('/super-admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('super_admin.dashboard', absolute: false));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post('/super-admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_general_admin_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/super-admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_developer_is_rejected(): void
    {
        $user = User::factory()->developer()->create();

        $this->post('/super-admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_super_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/super-admin');

        $response->assertRedirect(route('super_admin.login'));
        $this->assertGuest();
    }

    public function test_general_login_still_works_for_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }
}
