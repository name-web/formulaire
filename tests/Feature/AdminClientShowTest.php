<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientShowTest extends TestCase
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

    public function test_client_show_displays_ip_address(): void
    {
        $admin = $this->createAdmin();
        $client = Client::create([
            'name' => 'Jean Dupont',
            'phone' => '+22501020304',
            'ip_address' => '8.8.8.8',
        ]);

        $response = $this->actingAs($admin)->get('/admin/clients/'.$client->id);

        $response->assertOk();
        $response->assertSee('Adresse IP');
        $response->assertSee('8.8.8.8');
    }

    public function test_client_show_displays_non_disponible_when_ip_is_null(): void
    {
        $admin = $this->createAdmin();
        $client = Client::create([
            'name' => 'Jean Dupont',
            'phone' => '+22501020304',
        ]);

        $response = $this->actingAs($admin)->get('/admin/clients/'.$client->id);

        $response->assertOk();
        $response->assertSee('Non disponible');
    }

    public function test_guest_is_redirected_to_login_on_client_show(): void
    {
        $client = Client::create([
            'name' => 'Jean Dupont',
            'phone' => '+22501020304',
        ]);

        $response = $this->get('/admin/clients/'.$client->id);

        $response->assertRedirect(route('login'));
    }
}
