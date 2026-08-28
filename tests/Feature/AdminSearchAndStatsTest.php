<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Client;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchAndStatsTest extends TestCase
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

    public function test_client_search_does_not_return_clients_without_submitted_projects(): void
    {
        $admin = $this->createAdmin();

        $matching = Client::create(['name' => 'Jean Dupont', 'email' => 'jean@match.com']);
        $matching->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'submitted']);

        // Client dont l'email matche la recherche mais sans projet soumis
        Client::create(['name' => 'Autre Personne', 'email' => 'jean-autre@match.com']);

        $response = $this->actingAs($admin)->get('/admin/clients?search=jean');

        $response->assertStatus(200);
        $response->assertSee('Jean Dupont');
        $response->assertDontSee('Autre Personne');
    }

    public function test_project_search_respects_status_filter(): void
    {
        $admin = $this->createAdmin();

        Client::create(['name' => 'Client A', 'email' => 'clienta@test.com'])->projects()->create([
            'title' => 'Site ecommerce',
            'project_type' => ['ecommerce'],
            'status' => 'draft',
        ]);

        Client::create(['name' => 'Client B', 'email' => 'clientb@test.com'])->projects()->create([
            'title' => 'Vitrine informatique',
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
        ]);

        // La recherche "Client" matche les deux clients : le filtre statut doit
        // exclure le projet draft malgré le orWhere de la recherche
        $response = $this->actingAs($admin)->get('/admin/projects?status=submitted&search=Client');

        $response->assertStatus(200);
        $response->assertSee('Client B');
        $response->assertDontSee('Client A');
    }

    public function test_questions_show_route_is_not_registered(): void
    {
        $admin = $this->createAdmin();

        $question = Question::create([
            'title' => 'Question test',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'is_active' => true,
        ]);

        // La route "show" n'est plus enregistrée : GET sur cette URL tombe sur
        // la route PATCH update → 405 Method Not Allowed
        $this->actingAs($admin)
            ->get('/admin/questions/'.$question->id)
            ->assertStatus(405);
    }

    public function test_projects_by_type_counts_each_project(): void
    {
        Client::create(['name' => 'A', 'email' => 'a@test.com'])->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);
        Client::create(['name' => 'B', 'email' => 'b@test.com'])->projects()->create(['project_type' => ['site_vitrine', 'ecommerce'], 'status' => 'draft']);
        Client::create(['name' => 'C', 'email' => 'c@test.com'])->projects()->create(['project_type' => ['ecommerce'], 'status' => 'draft']);

        $controller = new DashboardController;
        $method = new \ReflectionMethod($controller, 'getProjectsByType');
        $result = $method->invoke($controller);

        $this->assertSame(2, $result->firstWhere('type', 'site_vitrine')['count']);
        $this->assertSame(2, $result->firstWhere('type', 'ecommerce')['count']);
    }
}
