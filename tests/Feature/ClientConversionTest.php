<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientConversionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createClientWithProject(array $clientAttrs = []): Client
    {
        $client = Client::create(array_merge([
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'status' => 'prospect',
        ], $clientAttrs));

        Project::create([
            'client_id' => $client->id,
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $client;
    }

    public function test_new_client_has_prospect_status_by_default(): void
    {
        $client = Client::create([
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
        ]);

        $this->assertSame('prospect', $client->fresh()->status);
    }

    public function test_prospect_can_be_converted_to_client(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->patch("/admin/clients/{$client->id}/convert");

        $response->assertRedirect(route('admin.client-clients.show', $client));
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'status' => 'client']);
    }

    public function test_conversion_does_not_duplicate_client(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $this->actingAs($admin)->patch("/admin/clients/{$client->id}/convert");

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Jean Dupont', 'status' => 'client']);
    }

    public function test_conversion_preserves_client_data(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject([
            'company_name' => 'Dupont Corp',
            'city' => 'Abidjan',
            'country' => "Côte d'Ivoire",
            'ip_address' => '8.8.8.8',
        ]);

        $this->actingAs($admin)->patch("/admin/clients/{$client->id}/convert");

        $client->refresh();
        $this->assertSame('Jean Dupont', $client->name);
        $this->assertSame('+2250708091011', $client->phone);
        $this->assertSame('Dupont Corp', $client->company_name);
        $this->assertSame('Abidjan', $client->city);
        $this->assertSame("Côte d'Ivoire", $client->country);
        $this->assertSame('8.8.8.8', $client->ip_address);
    }

    public function test_converted_client_appears_in_clients_page(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject(['status' => 'client']);

        $response = $this->actingAs($admin)->get('/admin/client-clients');

        $response->assertOk();
        $response->assertSee('Jean Dupont');
    }

    public function test_converted_client_disappears_from_prospects_page(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject(['status' => 'client']);

        $response = $this->actingAs($admin)->get('/admin/clients');

        $response->assertOk();
        $response->assertDontSee('Jean Dupont');
    }

    public function test_cannot_convert_already_a_client(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject(['status' => 'client']);

        $response = $this->actingAs($admin)->patch("/admin/clients/{$client->id}/convert");

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ce prospect est déjà un client.');
    }

    public function test_prospect_show_page_has_convert_button(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get("/admin/clients/{$client->id}");

        $response->assertOk();
        $response->assertSee('Rendre client');
    }

    public function test_client_show_page_does_not_have_convert_button(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject(['status' => 'client']);

        $response = $this->actingAs($admin)->get("/admin/clients/{$client->id}");

        $response->assertOk();
        $response->assertDontSee('Rendre client');
    }

    public function test_clients_page_is_accessible(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/client-clients');

        $response->assertOk();
        $response->assertSee('Clients');
    }

    public function test_guest_cannot_access_clients_page(): void
    {
        $response = $this->get('/admin/client-clients');

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_clients_page(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/client-clients');

        $response->assertForbidden();
    }

    public function test_guest_cannot_convert_prospect(): void
    {
        $client = $this->createClientWithProject();

        $response = $this->patch("/admin/clients/{$client->id}/convert");

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_convert_prospect(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);

        $client = $this->createClientWithProject();

        $response = $this->actingAs($user)->patch("/admin/clients/{$client->id}/convert");

        $response->assertForbidden();
    }

    public function test_prospect_shows_prospect_badge(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get("/admin/clients/{$client->id}");

        $response->assertOk();
        $response->assertSee('PROSPECT');
    }

    public function test_client_shows_client_badge(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject(['status' => 'client']);

        $response = $this->actingAs($admin)->get("/admin/client-clients/{$client->id}");

        $response->assertOk();
        $response->assertSee('CLIENT');
    }
}
