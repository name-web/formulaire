<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeamSpacesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    // ── Bouton ESPACES ÉQUIPE sur le dashboard ──────────────────────────────

    public function test_admin_dashboard_shows_team_spaces_button(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('ESPACES ÉQUIPE');
        $response->assertSee(route('admin.team-spaces'));
    }

    public function test_team_spaces_button_links_to_correct_route(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertOk();
        $response->assertSee(route('admin.team-spaces'));
    }

    // ── Page espaces équipe ─────────────────────────────────────────────────

    public function test_team_spaces_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/team-spaces');

        $response->assertOk();
        $response->assertSee('Espaces équipe');
        $response->assertSee('ESPACE DÉVELOPPEUR');
        $response->assertSee('ESPACE COMMERCIAL');
    }

    public function test_team_spaces_page_shows_developer_login_link(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/team-spaces');

        $response->assertOk();
        $response->assertSee(route('developer.login'));
    }

    public function test_team_spaces_page_shows_commercial_login_link(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/team-spaces');

        $response->assertOk();
        $response->assertSee(route('commercial.login'));
    }

    public function test_team_spaces_developer_card_has_onclick_to_login(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/team-spaces');

        $response->assertOk();
        $response->assertSee('window.location');
        $response->assertSee('developer/login');
    }

    public function test_team_spaces_commercial_card_has_onclick_to_login(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/team-spaces');

        $response->assertOk();
        $response->assertSee('window.location');
        $response->assertSee('commercial/login');
    }

    // ── Sécurité : pas d'auto-login ─────────────────────────────────────────

    public function test_team_spaces_does_not_create_developer_session(): void
    {
        $this->actingAs($this->admin)->get('/admin/team-spaces');

        // L'admin reste connecté en tant qu'admin, pas en tant que développeur.
        $this->assertAuthenticatedAs($this->admin);
        $this->assertSame('admin', $this->admin->fresh()->role);
    }

    public function test_team_spaces_does_not_create_commercial_session(): void
    {
        $this->actingAs($this->admin)->get('/admin/team-spaces');

        // L'admin reste connecté en tant qu'admin, pas en tant que commercial.
        $this->assertAuthenticatedAs($this->admin);
        $this->assertSame('admin', $this->admin->fresh()->role);
    }

    public function test_developer_login_route_exists_and_renders(): void
    {
        $response = $this->get('/developer/login');

        $response->assertOk();
        $response->assertSee('Espace développeur');
    }

    public function test_commercial_login_route_exists_and_renders(): void
    {
        $response = $this->get('/commercial/login');

        $response->assertOk();
        $response->assertSee('Espace commercial');
    }

    public function test_team_spaces_page_does_not_bypass_middleware(): void
    {
        $this->get('/admin/team-spaces')->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_team_spaces(): void
    {
        $this->get('/admin/team-spaces')->assertRedirect(route('login'));
    }

    public function test_developer_cannot_access_admin_team_spaces(): void
    {
        $developer = User::factory()->developer()->create();

        $this->actingAs($developer)->get('/admin/team-spaces')->assertForbidden();
    }

    public function test_commercial_cannot_access_admin_team_spaces(): void
    {
        $commercial = User::factory()->commercial()->create();

        $this->actingAs($commercial)->get('/admin/team-spaces')->assertForbidden();
    }
}
