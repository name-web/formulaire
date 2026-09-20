<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CommercialSpaceTest extends TestCase
{
    use RefreshDatabase;

    private string $commercialPassword = 'CommercialPass2026!';

    private function createCommercial(string $name = 'Aminata Diallo'): User
    {
        return User::factory()->commercial()->create([
            'name' => $name,
            'password' => $this->commercialPassword,
        ]);
    }

    private function createAssignedProspect(User $commercial, string $name = 'Jean Dupont'): Client
    {
        return Client::factory()->assignedToCommercial($commercial)->create(['name' => $name]);
    }

    private function loginCommercial(User $commercial): void
    {
        $this->post('/commercial/login', [
            'name' => $commercial->name,
            'password' => $this->commercialPassword,
        ])->assertRedirect(route('commercial.dashboard'));

        $this->assertAuthenticatedAs($commercial);
    }

    // ── 1. Création par le Super Admin ──────────────────────────────────────

    public function test_super_admin_can_create_commercial(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $name = 'Nouveau Commercial';

        $response = $this->actingAs($superAdmin)
            ->post('/super-admin/commercials', ['name' => $name]);

        $response->assertRedirect(route('super_admin.commercials.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');
        $response->assertSessionHas('generated_password_for', $name);

        $commercial = User::where('name', $name)->first();
        $this->assertNotNull($commercial);
        $this->assertSame('commercial', $commercial->role);
        $this->assertTrue($commercial->isActive());
        $this->assertTrue(Hash::check(session('generated_password'), $commercial->password));

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'role' => 'commercial',
        ]);
    }

    public function test_commercial_name_must_be_unique(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->createCommercial('Doumbia');

        $response = $this->actingAs($superAdmin)
            ->from('/super-admin/commercials')
            ->post('/super-admin/commercials', ['name' => 'Doumbia']);

        $response->assertSessionHasErrors('name');
    }

    public function test_super_admin_can_view_commercial_list(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->createCommercial('Ibrahim Keita');

        $response = $this->actingAs($superAdmin)->get('/super-admin/commercials');

        $response->assertOk();
        $response->assertSee('Ibrahim Keita');
    }

    public function test_super_admin_can_view_commercial_detail(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $commercial = $this->createCommercial('Fatoumata Sy');

        $response = $this->actingAs($superAdmin)->get("/super-admin/commercials/{$commercial->id}");

        $response->assertOk();
        $response->assertSee('Fatoumata Sy');
    }

    public function test_super_admin_cannot_view_non_commercial_as_commercial_detail(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get("/super-admin/commercials/{$superAdmin->id}");

        $response->assertNotFound();
    }

    // ── 2. Connexion / commercial/login ─────────────────────────────────────

    public function test_commercial_login_page_renders(): void
    {
        $response = $this->get('/commercial/login');

        $response->assertOk();
        $response->assertSee('Espace commercial');
    }

    public function test_commercial_logs_in_with_name_and_password(): void
    {
        $commercial = $this->createCommercial();

        $this->loginCommercial($commercial);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $commercial = $this->createCommercial();

        $response = $this->post('/commercial/login', [
            'name' => $commercial->name,
            'password' => 'mauvais-mot-de-passe',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('name');
    }

    public function test_administrator_cannot_use_the_commercial_login(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Déguisé',
            'password' => $this->commercialPassword,
        ]);

        $this->post('/commercial/login', [
            'name' => $admin->name,
            'password' => $this->commercialPassword,
        ])->assertSessionHasErrors('name');

        $this->assertGuest();
    }

    public function test_developer_cannot_use_the_commercial_login(): void
    {
        $developer = User::factory()->developer()->create([
            'name' => 'Dev Déguisé',
            'password' => $this->commercialPassword,
        ]);

        $this->post('/commercial/login', [
            'name' => $developer->name,
            'password' => $this->commercialPassword,
        ])->assertSessionHasErrors('name');

        $this->assertGuest();
    }

    public function test_inactive_commercial_cannot_sign_in(): void
    {
        $commercial = $this->createCommercial();
        $commercial->update(['is_active' => false]);

        $response = $this->post('/commercial/login', [
            'name' => $commercial->name,
            'password' => $this->commercialPassword,
        ]);

        $response->assertRedirect(route('commercial.login'));
        $this->assertGuest();
    }

    public function test_commercial_login_scoped_to_commercial_role(): void
    {
        $admin = User::create([
            'name' => 'Aminata Dupont',
            'email' => 'aminata@test.com',
            'password' => $this->commercialPassword,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->from('/commercial/login')
            ->post('/commercial/login', [
                'name' => 'Aminata Dupont',
                'password' => $this->commercialPassword,
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    public function test_commercial_login_page_redirects_if_already_logged_in(): void
    {
        $commercial = $this->createCommercial();
        $this->actingAs($commercial);

        $response = $this->get('/commercial/login');

        $response->assertRedirect(route('commercial.dashboard'));
    }

    // ── 3. Isolation ────────────────────────────────────────────────────────

    public function test_guest_is_redirected_to_the_commercial_login(): void
    {
        $this->get('/commercial')->assertRedirect(route('commercial.login'));
        $this->get('/commercial/prospects')->assertRedirect(route('commercial.login'));
    }

    public function test_commercial_reaches_his_dashboard(): void
    {
        $commercial = $this->createCommercial();
        $this->createAssignedProspect($commercial, 'Prospect Assigné');

        $response = $this->actingAs($commercial)->get('/commercial');

        $response->assertOk();
        $response->assertSee('Prospect Assigné');
    }

    public function test_commercial_only_sees_his_own_prospects(): void
    {
        $commercial = $this->createCommercial('Aminata Diallo');
        $other = $this->createCommercial('Bakary Traoré');

        $this->createAssignedProspect($commercial, 'Prospect Aminata');
        $this->createAssignedProspect($other, 'Prospect Bakary');
        Client::factory()->create(['name' => 'Prospect Sans Commercial']);

        $response = $this->actingAs($commercial)->get('/commercial/prospects');

        $response->assertOk();
        $response->assertSee('Prospect Aminata');
        $response->assertDontSee('Prospect Bakary');
        $response->assertDontSee('Prospect Sans Commercial');
    }

    public function test_commercial_cannot_open_another_commercials_prospect(): void
    {
        $commercial = $this->createCommercial('Aminata Diallo');
        $other = $this->createCommercial('Bakary Traoré');

        $foreign = Client::factory()->assignedToCommercial($other)->create(['name' => 'Prospect Bakary']);

        $this->actingAs($commercial)
            ->get('/commercial/prospects/'.$foreign->id)
            ->assertNotFound();
    }

    public function test_commercial_cannot_write_on_another_commercials_prospect(): void
    {
        $commercial = $this->createCommercial('Aminata Diallo');
        $other = $this->createCommercial('Bakary Traoré');

        $foreign = Client::factory()->assignedToCommercial($other)->create(['name' => 'Prospect Bakary']);

        $this->actingAs($commercial)
            ->post('/commercial/prospects/'.$foreign->id.'/comments', ['content' => 'Tentative'])
            ->assertNotFound();

        $this->actingAs($commercial)
            ->patch('/commercial/prospects/'.$foreign->id.'/follow-up', [
                'next_action' => 'Tentative',
                'next_action_date' => now()->addDay()->format('Y-m-d'),
            ])
            ->assertNotFound();

        $this->actingAs($commercial)
            ->patch('/commercial/prospects/'.$foreign->id.'/convert')
            ->assertNotFound();

        $this->assertSame(0, ClientComment::count());
        $this->assertNull($foreign->fresh()->next_action);
        $this->assertSame('prospect', $foreign->fresh()->status);
    }

    public function test_commercial_cannot_reach_developer_space(): void
    {
        $commercial = $this->createCommercial();

        $this->actingAs($commercial)->get('/developer')->assertForbidden();
        $this->actingAs($commercial)->get('/developer/prospects')->assertForbidden();
    }

    public function test_commercial_cannot_reach_super_admin_space(): void
    {
        $commercial = $this->createCommercial();

        $this->actingAs($commercial)->get('/super-admin')->assertForbidden();
        $this->actingAs($commercial)->get('/super-admin/developers')->assertForbidden();
        $this->actingAs($commercial)->get('/super-admin/commercials')->assertForbidden();
    }

    public function test_commercial_cannot_reach_admin_space(): void
    {
        $commercial = $this->createCommercial();

        $this->actingAs($commercial)->get('/admin')->assertForbidden();
        $this->actingAs($commercial)->get('/admin/clients')->assertForbidden();
        $this->actingAs($commercial)->get('/dashboard')->assertForbidden();
    }

    public function test_unassigned_prospect_not_visible_to_commercial(): void
    {
        $commercial = $this->createCommercial();
        Client::factory()->create(['name' => 'Prospect Orphelin']);

        $response = $this->actingAs($commercial)->get('/commercial/prospects');

        $response->assertOk();
        $response->assertDontSee('Prospect Orphelin');
    }

    // ── 4. Attribution par le Super Admin ───────────────────────────────────

    public function test_super_admin_can_assign_prospect_to_commercial(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $commercial = $this->createCommercial();
        $prospect = Client::factory()->create(['name' => 'Prospect À Attribuer']);

        $response = $this->actingAs($superAdmin)
            ->from("/super-admin/prospects/{$prospect->id}")
            ->patch("/super-admin/prospects/{$prospect->id}/assignment", [
                'assigned_commercial_id' => $commercial->id,
            ]);

        $response->assertRedirect();
        $prospect->refresh();
        $this->assertSame($commercial->id, $prospect->assigned_commercial_id);
    }

    public function test_super_admin_can_remove_commercial_assignment(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $commercial = $this->createCommercial();
        $prospect = Client::factory()->assignedToCommercial($commercial)->create();

        $response = $this->actingAs($superAdmin)
            ->from("/super-admin/prospects/{$prospect->id}")
            ->patch("/super-admin/prospects/{$prospect->id}/assignment", [
                'assigned_commercial_id' => null,
            ]);

        $response->assertRedirect();
        $prospect->refresh();
        $this->assertNull($prospect->assigned_commercial_id);
    }

    public function test_super_admin_can_reassign_prospect_to_different_commercial(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $commercial1 = $this->createCommercial('Aminata Diallo');
        $commercial2 = $this->createCommercial('Bakary Traoré');
        $prospect = Client::factory()->assignedToCommercial($commercial1)->create(['name' => 'Prospect Réattribué']);

        $response = $this->actingAs($superAdmin)
            ->from("/super-admin/prospects/{$prospect->id}")
            ->patch("/super-admin/prospects/{$prospect->id}/assignment", [
                'assigned_commercial_id' => $commercial2->id,
            ]);

        $response->assertRedirect();
        $prospect->refresh();
        $this->assertSame($commercial2->id, $prospect->assigned_commercial_id);
    }

    // ── 5. Suivi du prospect ────────────────────────────────────────────────

    public function test_commercial_adds_a_follow_up_comment(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $response = $this->actingAs($commercial)
            ->post('/commercial/prospects/'.$prospect->id.'/comments', [
                'content' => 'Appel du 15/09 : le prospect souhaite un devis.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('client_comments', [
            'client_id' => $prospect->id,
            'user_id' => $commercial->id,
            'content' => 'Appel du 15/09 : le prospect souhaite un devis.',
        ]);
    }

    public function test_follow_up_comment_is_required(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $this->actingAs($commercial)
            ->post('/commercial/prospects/'.$prospect->id.'/comments', ['content' => ''])
            ->assertSessionHasErrors('content');

        $this->assertSame(0, ClientComment::count());
    }

    public function test_commercial_defines_the_next_action_and_date(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $response = $this->actingAs($commercial)
            ->patch('/commercial/prospects/'.$prospect->id.'/follow-up', [
                'next_action' => 'Envoyer le devis',
                'next_action_date' => now()->addDays(2)->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $prospect->refresh();
        $this->assertSame('Envoyer le devis', $prospect->next_action);
        $this->assertSame(now()->addDays(2)->format('Y-m-d'), $prospect->next_action_date->format('Y-m-d'));
    }

    // ── 6. Conversion prospect → client ─────────────────────────────────────

    public function test_commercial_converts_his_prospect_and_keeps_the_history(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial, 'Prospect Gagné');

        $response = $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/convert');

        $response->assertRedirect(route('commercial.prospects.show', $prospect));
        $response->assertSessionHasNoErrors();

        $prospect->refresh();
        $this->assertSame('client', $prospect->status);
        $this->assertSame($commercial->id, $prospect->assigned_commercial_id);
        $this->assertSame(1, Client::count());
    }

    public function test_cannot_convert_already_client(): void
    {
        $commercial = $this->createCommercial();
        $client = Client::factory()->client()->assignedToCommercial($commercial)->create();

        $response = $this->actingAs($commercial)
            ->from('/commercial/prospects/'.$client->id)
            ->patch('/commercial/prospects/'.$client->id.'/convert');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ce prospect est déjà un client.');
    }

    // ── 7. Clients convertis ──────────────────────────────────────────────────

    public function test_commercial_can_view_converted_clients_page(): void
    {
        $commercial = $this->createCommercial();
        $this->createAssignedProspect($commercial, 'Prospect Actif');
        $converted = Client::factory()->client()->assignedToCommercial($commercial)->create(['name' => 'Client Converti']);

        $response = $this->actingAs($commercial)->get('/commercial/converted');

        $response->assertOk();
        $response->assertSee('Clients convertis');
        $response->assertSee('Client Converti');
    }

    public function test_commercial_sees_only_his_converted_clients(): void
    {
        $commercial = $this->createCommercial('Aminata Diallo');
        $other = $this->createCommercial('Bakary Traoré');

        $this->createAssignedProspect($commercial, 'Prospect Actif');
        $myConverted = Client::factory()->client()->assignedToCommercial($commercial)->create(['name' => 'Mon Client Converti']);
        Client::factory()->client()->assignedToCommercial($other)->create(['name' => 'Autre Client Converti']);

        $response = $this->actingAs($commercial)->get('/commercial/converted');

        $response->assertOk();
        $response->assertSee('Mon Client Converti');
        $response->assertDontSee('Autre Client Converti');
    }

    public function test_commercial_cannot_access_converted_without_auth(): void
    {
        $this->get('/commercial/converted')->assertRedirect(route('commercial.login'));
    }

    public function test_converted_page_shows_conversion_date_and_info(): void
    {
        $commercial = $this->createCommercial();
        $converted = Client::factory()->client()->assignedToCommercial($commercial)->create([
            'name' => 'Client Converti',
            'company_name' => 'Ma Entreprise',
            'phone' => '+2250708091011',
        ]);

        $response = $this->actingAs($commercial)->get('/commercial/converted');

        $response->assertOk();
        $response->assertSee('Client Converti');
        $response->assertSee('Ma Entreprise');
        $response->assertSee('+2250708091011');
        $response->assertSee($converted->updated_at->format('d/m/Y'));
    }

    public function test_converted_page_search_works(): void
    {
        $commercial = $this->createCommercial();
        Client::factory()->client()->assignedToCommercial($commercial)->create(['name' => 'Client Alpha']);
        Client::factory()->client()->assignedToCommercial($commercial)->create(['name' => 'Client Beta']);

        $response = $this->actingAs($commercial)->get('/commercial/converted?search=Alpha');

        $response->assertOk();
        $response->assertSee('Client Alpha');
        $response->assertDontSee('Client Beta');
    }

    public function test_sidebar_shows_converted_link(): void
    {
        $commercial = $this->createCommercial();

        $response = $this->actingAs($commercial)->get('/commercial');

        $response->assertOk();
        $response->assertSee('Clients convertis');
        $response->assertSee(route('commercial.converted.index'));
    }
}
