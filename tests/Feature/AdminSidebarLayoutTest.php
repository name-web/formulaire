<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-sidebar-test@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_dashboard_renders_sidebar_element(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('id="adminSidebar"', false);
    }

    public function test_admin_dashboard_renders_hamburger_toggle(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('id="sidebarToggle"', false);
    }

    public function test_admin_dashboard_renders_sidebar_overlay(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('id="sidebarOverlay"', false);
    }

    public function test_admin_dashboard_renders_sidebar_close_button(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('id="sidebarClose"', false);
    }

    public function test_sidebar_has_no_overflow_hidden_in_html(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        // The sidebar element should not have inline overflow:hidden
        $response->assertDontSee('class="sidebar" style="', false);
    }

    public function test_admin_dashboard_contains_sidebar_open_class_in_javascript(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee("classList.add('open')", false);
        $response->assertSee("classList.remove('open')", false);
    }
}
