<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminLoginFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Kamal',
            'email' => 'kamallareleve233@gmail.com',
            'password' => 'AdminTest2026!',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('DOMINIUM TECH');
    }

    public function test_admin_can_login_and_reaches_dashboard(): void
    {
        $user = $this->createAdmin();

        $response = $this->post('/login', [
            'email' => 'kamallareleve233@gmail.com',
            'password' => 'AdminTest2026!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->createAdmin();

        $response = $this->post('/login', [
            'email' => 'kamallareleve233@gmail.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect();
    }

    public function test_inactive_admin_is_rejected(): void
    {
        $user = User::create([
            'name' => 'Inactive',
            'email' => 'inactive@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    public function test_logout_works(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->post('/logout');
        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_admin_login_sets_remember_cookie(): void
    {
        $user = $this->createAdmin();

        $response = $this->post('/login', [
            'email' => 'kamallareleve233@gmail.com',
            'password' => 'AdminTest2026!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertCookie(Auth::guard()->getRecallerName());
    }

    public function test_admin_stays_authenticated_across_requests_without_relogin(): void
    {
        $user = $this->createAdmin();

        $this->post('/login', [
            'email' => 'kamallareleve233@gmail.com',
            'password' => 'AdminTest2026!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/projects')->assertOk();
        $this->get('/admin/clients')->assertOk();
        $this->get('/admin/questions')->assertOk();
        $this->get('/admin/users')->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_full_flow_end_to_end(): void
    {
        $user = $this->createAdmin();

        // 1. Guest tries /admin → redirected to login
        $this->get('/admin')->assertRedirect(route('login'));

        // 2. Login page renders
        $this->get('/login')->assertStatus(200);

        // 3. POST login with correct credentials → redirected to admin dashboard
        $this->post('/login', [
            'email' => 'kamallareleve233@gmail.com',
            'password' => 'AdminTest2026!',
        ])->assertRedirect(route('admin.dashboard'));

        // 4. Now authenticated → can access /admin
        $this->get('/admin')->assertStatus(200);

        // 5. Can access /admin/projects
        $this->get('/admin/projects')->assertStatus(200);

        // 6. Can access /admin/clients
        $this->get('/admin/clients')->assertStatus(200);

        // 7. Can access /admin/questions
        $this->get('/admin/questions')->assertStatus(200);

        // 8. Can access /admin/users
        $this->get('/admin/users')->assertStatus(200);

        // 9. Logout → guest again
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        // 10. After logout → /admin redirects to login again
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_admin_clients_index(): void
    {
        $user = $this->createAdmin();
        $response = $this->actingAs($user)->get('/admin/clients');
        $response->assertStatus(200);
    }

    public function test_admin_questions_index(): void
    {
        $user = $this->createAdmin();
        $response = $this->actingAs($user)->get('/admin/questions');
        $response->assertStatus(200);
    }

    public function test_admin_users_index(): void
    {
        $user = $this->createAdmin();
        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_admin_questions_create(): void
    {
        $user = $this->createAdmin();
        $response = $this->actingAs($user)->get('/admin/questions/create');
        $response->assertStatus(200);
    }
}
