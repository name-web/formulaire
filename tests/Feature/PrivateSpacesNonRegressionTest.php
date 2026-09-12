<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrivateSpacesNonRegressionTest extends TestCase
{
    use RefreshDatabase;

    private string $adminPassword = 'AdminPass2026!';

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Administrateur Général',
            'password' => $this->adminPassword,
        ]);
    }

    public function test_general_dashboard_sidebar_is_unchanged(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();

        foreach (['Dashboard', 'Projets', 'Prospects', 'Clients', 'Commentaires', 'Devis', 'Questions', 'Utilisateurs'] as $label) {
            $response->assertSee($label);
        }

        // Les espaces privés ne sont jamais exposés dans la navigation générale.
        $response->assertDontSee('Vue globale');
        $response->assertDontSee('Mes suivis');
        $response->assertDontSee('Prospects & attribution');
    }

    public function test_general_navigation_config_has_no_private_space_entry(): void
    {
        $routes = collect(config('admin_brand.nav_items'))->pluck('route');

        $this->assertFalse($routes->contains(
            fn (string $route) => str_starts_with($route, 'super_admin.') || str_starts_with($route, 'developer.')
        ));
    }

    public function test_private_space_navigation_only_links_to_its_own_routes(): void
    {
        foreach (['super_admin', 'developer'] as $space) {
            $items = config("private_spaces.{$space}.nav_items", []);

            $this->assertNotEmpty($items, "L'espace {$space} doit exposer au moins une entrée.");

            foreach ($items as $item) {
                $this->assertStringStartsWith(
                    $space.'.',
                    $item['route'],
                    "L'entrée « {$item['label']} » ne doit pas faire sortir de l'espace {$space}."
                );
            }
        }
    }

    public function test_general_dashboard_pages_still_work_for_an_admin(): void
    {
        $admin = $this->createAdmin();
        Client::factory()->create();

        foreach (['/admin', '/admin/projects', '/admin/clients', '/admin/client-clients', '/admin/comments', '/admin/users', '/admin/questions'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_super_admin_keeps_full_access_to_the_general_dashboard(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'email' => 'super@test.com',
            'password' => $this->adminPassword,
        ]);

        $this->actingAs($superAdmin)->get('/admin')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/clients')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/users')->assertOk();
    }

    public function test_super_admin_can_still_sign_in_through_the_general_login(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'email' => 'super@test.com',
            'password' => $this->adminPassword,
        ]);

        $this->post('/login', [
            'email' => 'super@test.com',
            'password' => $this->adminPassword,
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($superAdmin);
        $this->assertTrue(Hash::check($this->adminPassword, $superAdmin->fresh()->password));
    }

    public function test_general_dashboard_route_redirects_to_the_admin_dashboard(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_prospect_conversion_from_the_general_dashboard_still_works(): void
    {
        $admin = $this->createAdmin();

        $client = Client::factory()->create(['name' => 'Prospect Général']);
        Project::create([
            'client_id' => $client->id,
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch('/admin/clients/'.$client->id.'/convert')
            ->assertRedirect(route('admin.client-clients.show', $client));

        $this->assertSame('client', $client->fresh()->status);
    }

    public function test_general_comments_page_still_lists_follow_ups(): void
    {
        $admin = $this->createAdmin();
        $client = Client::factory()->create(['name' => 'Prospect Commenté']);

        $client->comments()->create([
            'user_id' => $admin->id,
            'content' => 'Commentaire du dashboard général.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/comments')
            ->assertOk()
            ->assertSee('Commentaire du dashboard général.');
    }

    public function test_every_role_is_declared_in_the_roles_config(): void
    {
        $roles = array_keys(config('user_roles.roles'));

        $this->assertContains('super_admin', $roles);
        $this->assertContains('admin', $roles);
        $this->assertContains('developer', $roles);
        $this->assertContains('user', $roles);
    }
}
