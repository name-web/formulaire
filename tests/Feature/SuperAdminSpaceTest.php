<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSpaceTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        return User::factory()->superAdmin()->create(['name' => 'Super Admin']);
    }

    private function createDeveloper(string $name = 'Awa Traoré'): User
    {
        return User::factory()->developer()->create(['name' => $name]);
    }

    // ── Accès à l'espace privé ───────────────────────────────────────────────

    public function test_super_admin_reaches_his_private_space(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertOk();
        $response->assertSee('Super Admin');
        $response->assertSee('Vue globale');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/super-admin')->assertRedirect(route('super_admin.login'));
    }

    public function test_non_super_admin_user_is_forbidden(): void
    {
        $developer = $this->createDeveloper();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($developer)->get('/super-admin')->assertForbidden();
        $this->actingAs($admin)->get('/super-admin')->assertForbidden();
    }

    public function test_super_admin_routes_are_forbidden_for_other_roles_even_with_valid_urls(): void
    {
        $developer = $this->createDeveloper();
        $client = Client::factory()->assignedTo($developer)->create();

        $this->actingAs($developer)->get('/super-admin/developers')->assertForbidden();
        $this->actingAs($developer)->get('/super-admin/developers/'.$developer->id)->assertForbidden();
        $this->actingAs($developer)->get('/super-admin/prospects')->assertForbidden();
        $this->actingAs($developer)->get('/super-admin/prospects/'.$client->id)->assertForbidden();
        $this->actingAs($developer)
            ->patch('/super-admin/prospects/'.$client->id.'/assignment', ['assigned_developer_id' => null])
            ->assertForbidden();
    }

    public function test_inactive_super_admin_is_logged_out(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $superAdmin->update(['is_active' => false]);

        $this->actingAs($superAdmin)->get('/super-admin')->assertRedirect(route('super_admin.login'));
        $this->assertGuest();
    }

    // ── Création d'un développeur (nom uniquement) ───────────────────────────

    public function test_super_admin_creates_a_developer_from_a_name_only(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post('/super-admin/developers', ['name' => 'Awa Traoré']);

        $response->assertRedirect(route('super_admin.developers.index'));
        $response->assertSessionHasNoErrors();

        $developer = User::where('name', 'Awa Traoré')->first();

        $this->assertNotNull($developer);
        $this->assertSame('developer', $developer->role);
        $this->assertTrue($developer->is_active);
        $this->assertSame('awa-traore@developpeur.local', $developer->email);
    }

    public function test_generated_password_is_hashed_and_displayed_once(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)->post('/super-admin/developers', ['name' => 'Ibrahim Koné']);

        $developer = User::where('name', 'Ibrahim Koné')->firstOrFail();
        $password = session('generated_password');

        // Mot de passe sécurisé généré automatiquement.
        $this->assertIsString($password);
        $this->assertSame(16, strlen($password));

        // Seul le hash est stocké en base.
        $this->assertNotSame($password, $developer->getAttributes()['password']);
        $this->assertTrue(Hash::check($password, $developer->getAttribute('password')));

        // Affiché une fois au Super Admin sur la page de gestion.
        $this->actingAs($superAdmin)
            ->get('/super-admin/developers')
            ->assertOk()
            ->assertSee($password)
            ->assertSee('Ibrahim Koné');

        // Non réaffiché lors de la requête suivante.
        $this->actingAs($superAdmin)
            ->get('/super-admin/developers')
            ->assertOk()
            ->assertDontSee($password);
    }

    public function test_developer_name_must_be_unique_among_developers(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $this->createDeveloper('Doublon Test');

        $response = $this->actingAs($superAdmin)->post('/super-admin/developers', ['name' => 'Doublon Test']);

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, User::where('name', 'Doublon Test')->count());
    }

    public function test_developer_name_is_required(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->post('/super-admin/developers', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_super_admin_lists_every_developer(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $first = $this->createDeveloper('Awa Traoré');
        $second = $this->createDeveloper('Bakary Diallo');

        Client::factory()->assignedTo($first)->create();
        Client::factory()->client()->assignedTo($second)->create();

        $response = $this->actingAs($superAdmin)->get('/super-admin/developers');

        $response->assertOk();
        $response->assertSee('Awa Traoré');
        $response->assertSee('Bakary Diallo');
    }

    // ── Attribution / réattribution des prospects ────────────────────────────

    public function test_super_admin_assigns_a_prospect_to_a_developer(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $developer = $this->createDeveloper();
        $client = Client::factory()->create(['name' => 'Prospect À Attribuer']);

        $this->assertNull($client->assigned_developer_id);

        $response = $this->actingAs($superAdmin)
            ->patch('/super-admin/prospects/'.$client->id.'/assignment', [
                'assigned_developer_id' => $developer->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'assigned_developer_id' => $developer->id,
        ]);
    }

    public function test_super_admin_reassigns_a_prospect_to_another_developer(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $first = $this->createDeveloper('Awa Traoré');
        $second = $this->createDeveloper('Bakary Diallo');
        $client = Client::factory()->assignedTo($first)->create();

        $this->actingAs($superAdmin)
            ->patch('/super-admin/prospects/'.$client->id.'/assignment', [
                'assigned_developer_id' => $second->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'assigned_developer_id' => $second->id,
        ]);
    }

    public function test_super_admin_can_remove_an_assignment(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $developer = $this->createDeveloper();
        $client = Client::factory()->assignedTo($developer)->create();

        $this->actingAs($superAdmin)
            ->patch('/super-admin/prospects/'.$client->id.'/assignment', [
                'assigned_developer_id' => null,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'assigned_developer_id' => null,
        ]);
    }

    public function test_prospect_can_only_be_assigned_to_a_developer(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->patch('/super-admin/prospects/'.$client->id.'/assignment', [
                'assigned_developer_id' => $admin->id,
            ]);

        $response->assertSessionHasErrors('assigned_developer_id');
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'assigned_developer_id' => null,
        ]);
    }

    public function test_guest_cannot_assign_a_prospect(): void
    {
        $client = Client::factory()->create();

        $this->patch('/super-admin/prospects/'.$client->id.'/assignment', [
            'assigned_developer_id' => null,
        ])->assertRedirect(route('super_admin.login'));
    }

    // ── Vision globale ──────────────────────────────────────────────────────

    public function test_super_admin_sees_all_prospects_clients_and_follow_ups(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $first = $this->createDeveloper('Awa Traoré');
        $second = $this->createDeveloper('Bakary Diallo');

        $prospect = Client::factory()->assignedTo($first)->create(['name' => 'Prospect Awa']);
        Client::factory()->client()->assignedTo($second)->create(['name' => 'Client Bakary']);
        Client::factory()->create(['name' => 'Prospect Non Attribué']);

        ClientComment::factory()->create([
            'client_id' => $prospect->id,
            'user_id' => $first->id,
            'content' => 'Premier appel effectué.',
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertOk();
        $response->assertSee('Prospect Awa');
        $response->assertSee('Client Bakary');
        $response->assertSee('Prospect Non Attribué');
        $response->assertSee('Premier appel effectué.');

        // Vision globale : tous les prospects, tous les clients, tous les suivis.
        $this->assertSame(3, Client::count());
        $this->assertSame(2, Client::prospects()->count());
        $this->assertSame(1, Client::clients()->count());
        $this->assertSame(1, Client::prospects()->whereNull('assigned_developer_id')->count());
        $this->assertSame(1, ClientComment::count());
    }

    public function test_super_admin_consults_the_dashboard_of_each_developer(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $developer = $this->createDeveloper('Awa Traoré');
        $other = $this->createDeveloper('Bakary Diallo');

        $mine = Client::factory()->assignedTo($developer)->create(['name' => 'Prospect Awa']);
        Client::factory()->assignedTo($other)->create(['name' => 'Prospect Bakary']);

        ClientComment::factory()->create([
            'client_id' => $mine->id,
            'user_id' => $developer->id,
            'content' => 'Suivi enregistré par Awa.',
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/developers/'.$developer->id);

        $response->assertOk();
        $response->assertSee('Prospect Awa');
        $response->assertSee('Suivi enregistré par Awa.');
        // Le Super Admin voit le suivi complet, mais la vue reste en lecture seule.
        $response->assertDontSee('Enregistrer la prochaine action');
    }

    public function test_developer_board_is_not_reachable_for_non_developer_accounts(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin)
            ->get('/super-admin/developers/'.$admin->id)
            ->assertNotFound();
    }

    public function test_super_admin_consults_every_prospect_detail(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $developer = $this->createDeveloper();
        $client = Client::factory()->assignedTo($developer)->create(['name' => 'Prospect Détail']);

        $response = $this->actingAs($superAdmin)->get('/super-admin/prospects/'.$client->id);

        $response->assertOk();
        $response->assertSee('Prospect Détail');
    }

    public function test_prospect_detail_opened_from_the_super_admin_stays_in_the_private_space(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $developer = $this->createDeveloper();
        $client = Client::factory()->assignedTo($developer)->create(['name' => 'Prospect Contexte']);

        $project = Project::create([
            'client_id' => $client->id,
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/prospects/'.$client->id);

        $response->assertOk();
        $response->assertSee('Prospect Contexte');

        // Le retour pointe vers la liste Super Admin, pas vers le dashboard général.
        $this->assertMatchesRegularExpression(
            '@<a href="[^"]*super-admin/prospects"[^>]*>.*Retour@',
            $response->getContent()
        );

        // Page d'observation : les actions de gestion restent hors de cet espace.
        $response->assertDontSee('Créer un devis');
        $response->assertDontSee('Rendre client');
        $response->assertDontSee('Ajouter le commentaire');
        $response->assertDontSee(route('admin.projects.show', $project));
    }

    public function test_general_dashboard_prospect_detail_keeps_its_actions_and_return_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create(['name' => 'Prospect Général']);

        $response = $this->actingAs($admin)->get('/admin/clients/'.$client->id);

        $response->assertOk();
        $response->assertSee('Rendre client');
        $response->assertSee('Créer un devis');
        $response->assertSee('Ajouter le commentaire');
        $this->assertMatchesRegularExpression(
            '@<a href="[^"]*admin/clients"[^>]*>.*Retour@',
            $response->getContent()
        );
    }

    public function test_super_admin_lists_prospects_with_assignment_information(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $developer = $this->createDeveloper('Awa Traoré');
        Client::factory()->assignedTo($developer)->create(['name' => 'Prospect Listé']);

        $response = $this->actingAs($superAdmin)->get('/super-admin/prospects');

        $response->assertOk();
        $response->assertSee('Prospect Listé');
        $response->assertSee('Awa Traoré');
    }

    public function test_prospect_list_can_be_filtered_by_developer(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $first = $this->createDeveloper('Awa Traoré');
        $second = $this->createDeveloper('Bakary Diallo');

        Client::factory()->assignedTo($first)->create(['name' => 'Prospect Awa']);
        Client::factory()->assignedTo($second)->create(['name' => 'Prospect Bakary']);

        $response = $this->actingAs($superAdmin)->get('/super-admin/prospects?developer='.$first->id);

        $response->assertOk();
        $response->assertSee('Prospect Awa');
        $response->assertDontSee('Prospect Bakary');
    }
}
