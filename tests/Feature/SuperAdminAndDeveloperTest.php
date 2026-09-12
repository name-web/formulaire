<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminAndDeveloperTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $developer;

    private Client $prospect;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'password',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->developer = User::create([
            'name' => 'Koffi',
            'email' => 'koffi@developpeur.local',
            'password' => 'password',
            'role' => 'developer',
            'is_active' => true,
        ]);

        $this->prospect = Client::create([
            'name' => 'Jean Toure',
            'phone' => '+2250708091011',
            'status' => 'prospect',
            'assigned_developer_id' => $this->developer->id,
        ]);
    }

    // ── Super Admin ──────────────────────────────────────────────────────

    public function test_super_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin');

        $response->assertOk();
        $response->assertSee('Super Admin');
        $response->assertSee('Vue globale');
    }

    public function test_super_admin_can_view_developers(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/developers');

        $response->assertOk();
        $response->assertSee('Koffi');
        $response->assertSee('développeur');
    }

    public function test_super_admin_can_create_developer(): void
    {
        $name = 'New Dev '.Str::random(4);
        $response = $this->actingAs($this->superAdmin)
            ->post('/super-admin/developers', ['name' => $name]);

        $response->assertRedirect(route('super_admin.developers.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');
        $response->assertSessionHas('generated_password_for', $name);

        $developer = User::where('name', $name)->first();
        $this->assertNotNull($developer);
        $this->assertEquals('developer', $developer->role);
        $this->assertTrue($developer->isActive());
        $this->assertTrue(Hash::check(session('generated_password'), $developer->password));

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'role' => 'developer',
        ]);
    }

    public function test_developer_name_must_be_unique(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->from('/super-admin/developers')
            ->post('/super-admin/developers', ['name' => 'Koffi']);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('users', 2); // superAdmin + existing developer
    }

    public function test_super_admin_can_view_developer_detail(): void
    {
        $response = $this->actingAs($this->superAdmin)->get("/super-admin/developers/{$this->developer->id}");

        $response->assertOk();
        $response->assertSee('Koffi');
    }

    public function test_super_admin_cannot_view_non_developer_as_developer_detail(): void
    {
        $response = $this->actingAs($this->superAdmin)->get("/super-admin/developers/{$this->superAdmin->id}");

        $response->assertNotFound();
    }

    public function test_super_admin_can_list_prospects(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/prospects');

        $response->assertOk();
        $response->assertSee('Jean Toure');
    }

    public function test_super_admin_can_assign_prospect_to_developer(): void
    {
        $other = User::factory()->developer()->create();

        $response = $this->actingAs($this->superAdmin)
            ->from("/super-admin/prospects/{$this->prospect->id}")
            ->patch("/super-admin/prospects/{$this->prospect->id}/assignment", [
                'assigned_developer_id' => $other->id,
            ]);

        $response->assertRedirect();
        $this->prospect->refresh();
        $this->assertEquals($other->id, $this->prospect->assigned_developer_id);
    }

    public function test_super_admin_can_remove_prospect_assignment(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->from("/super-admin/prospects/{$this->prospect->id}")
            ->patch("/super-admin/prospects/{$this->prospect->id}/assignment", [
                'assigned_developer_id' => null,
            ]);

        $response->assertRedirect();
        $this->prospect->refresh();
        $this->assertNull($this->prospect->assigned_developer_id);
    }

    public function test_admin_cannot_access_super_admin_space(): void
    {
        $admin = User::create([
            'name' => 'Regular Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/super-admin')->assertForbidden();
        $this->actingAs($admin)->get('/super-admin/developers')->assertForbidden();
    }

    public function test_developer_cannot_access_super_admin_space(): void
    {
        $this->actingAs($this->developer)->get('/super-admin')->assertForbidden();
    }

    // ── Developer auth ───────────────────────────────────────────────────

    public function test_developer_can_login_with_name(): void
    {
        $response = $this->post('/developer/login', [
            'name' => 'Koffi',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('developer.dashboard'));
        $this->assertAuthenticatedAs($this->developer);
    }

    public function test_developer_cannot_login_with_wrong_name(): void
    {
        $response = $this->from('/developer/login')
            ->post('/developer/login', [
                'name' => 'Wrong Name',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    public function test_admin_cannot_login_as_developer(): void
    {
        // Un compte administrateur ne peut jamais s'authentifier via l'espace
        // développeur, même en connaissant le nom d'un développeur : l'essai
        // est filtré sur le rôle `developer`.
        $admin = User::create([
            'name' => 'Koffi Admin',
            'email' => 'admin2@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->from('/developer/login')
            ->post('/developer/login', [
                'name' => 'Koffi Admin',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    public function test_developer_login_scoped_to_developer_role(): void
    {
        $admin = User::create([
            'name' => 'Koffi Dupont',
            'email' => 'koffi2@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Developer login uses exact name match + role filter
        $response = $this->from('/developer/login')
            ->post('/developer/login', [
                'name' => 'Koffi Dupont',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    public function test_inactive_developer_cannot_login(): void
    {
        $this->developer->update(['is_active' => false]);

        $response = $this->from('/developer/login')
            ->post('/developer/login', [
                'name' => 'Koffi',
                'password' => 'password',
            ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_developer_login_page_redirects_if_already_logged_in(): void
    {
        $this->actingAs($this->developer);

        $response = $this->get('/developer/login');

        $response->assertRedirect(route('developer.dashboard'));
    }

    public function test_developer_login_page_sends_admin_to_admin_dashboard(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get('/developer/login');

        $response->assertRedirect(route('admin.dashboard'));
    }

    // ── Developer space ──────────────────────────────────────────────────

    public function test_developer_can_access_own_dashboard(): void
    {
        $this->actingAs($this->developer);

        $response = $this->get('/developer');

        $response->assertOk();
        $response->assertSee('Mes suivis');
    }

    public function test_developer_sees_only_own_prospects(): void
    {
        $other = User::factory()->developer()->create();
        $otherProspect = Client::factory()->assignedTo($other)->create(['status' => 'prospect']);

        $this->actingAs($this->developer);

        $response = $this->get('/developer/prospects');

        $response->assertOk();
        $response->assertSee('Jean Toure');
        $response->assertDontSee($otherProspect->name);
    }

    public function test_developer_can_add_comment(): void
    {
        $this->actingAs($this->developer);

        $response = $this->post("/developer/prospects/{$this->prospect->id}/comments", [
            'content' => 'Premier suivi : appel de candidature.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('client_comments', [
            'client_id' => $this->prospect->id,
            'user_id' => $this->developer->id,
            'content' => 'Premier suivi : appel de candidature.',
        ]);
    }

    public function test_developer_can_update_follow_up(): void
    {
        $this->actingAs($this->developer);

        $response = $this->patch("/developer/prospects/{$this->prospect->id}/follow-up", [
            'next_action' => 'Envoyer le devis',
            'next_action_date' => '2026-09-20',
        ]);

        $response->assertRedirect();
        $this->prospect->refresh();
        $this->assertEquals('Envoyer le devis', $this->prospect->next_action);
        $this->assertEquals('2026-09-20', $this->prospect->next_action_date->format('Y-m-d'));
    }

    public function test_developer_can_convert_prospect_to_client(): void
    {
        $this->actingAs($this->developer);

        $response = $this->patch("/developer/prospects/{$this->prospect->id}/convert");

        $response->assertRedirect(route('developer.prospects.show', $this->prospect));
        $this->prospect->refresh();
        $this->assertTrue($this->prospect->isClient());
        $this->assertDatabaseHas('clients', [
            'id' => $this->prospect->id,
            'status' => 'client',
        ]);
    }

    public function test_developer_cannot_convert_already_client(): void
    {
        $this->prospect->update(['status' => 'client']);
        $this->actingAs($this->developer);

        $response = $this->from("/developer/prospects/{$this->prospect->id}")
            ->patch("/developer/prospects/{$this->prospect->id}/convert");

        $response->assertSessionHas('error');
        $this->assertTrue($this->prospect->isClient());
    }

    public function test_developer_cannot_access_other_developer_prospect(): void
    {
        $other = User::factory()->developer()->create();
        $otherProspect = Client::factory()->assignedTo($other)->create(['status' => 'prospect']);

        $this->actingAs($this->developer);

        $this->get("/developer/prospects/{$otherProspect->id}")
            ->assertNotFound();

        $this->post("/developer/prospects/{$otherProspect->id}/comments", [
            'content' => 'Hack attempt',
        ])->assertNotFound();

        $this->patch("/developer/prospects/{$otherProspect->id}/follow-up", [
            'next_action' => 'Hack',
        ])->assertNotFound();

        $this->patch("/developer/prospects/{$otherProspect->id}/convert")
            ->assertNotFound();
    }

    public function test_developer_cannot_see_other_developers_in_prospects_list(): void
    {
        $other = User::factory()->developer()->create();
        Client::factory()->assignedTo($other)->create(['status' => 'prospect']);

        $this->actingAs($this->developer);

        $response = $this->get('/developer/prospects');

        $response->assertOk();
        $response->assertDontSee($other->name);
    }

    // ── Guest access ─────────────────────────────────────────────────────

    public function test_guest_redirected_to_developer_login(): void
    {
        $this->get('/developer')->assertRedirect(route('developer.login'));
        $this->get('/developer/prospects')->assertRedirect(route('developer.login'));
    }

    public function test_super_admin_space_requires_auth(): void
    {
        $this->get('/super-admin')->assertRedirect(route('login'));
    }
}
