<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Client;
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

        $matching = Client::create(['name' => 'Jean Dupont', 'phone' => '+22501020304']);
        $matching->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'submitted']);

        // Client dont le nom matche la recherche mais sans projet soumis
        Client::create(['name' => 'Autre Personne', 'phone' => '+22509999999']);

        $response = $this->actingAs($admin)->get('/admin/clients?search=jean');

        $response->assertStatus(200);
        $response->assertSee('Jean Dupont');
        $response->assertDontSee('Autre Personne');
    }

    public function test_project_search_respects_status_filter(): void
    {
        $admin = $this->createAdmin();

        Client::create(['name' => 'Client A', 'phone' => '+22501111111'])->projects()->create([
            'title' => 'Site ecommerce',
            'project_type' => ['ecommerce'],
            'status' => 'draft',
        ]);

        Client::create(['name' => 'Client B', 'phone' => '+22502222222'])->projects()->create([
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

    public function test_legacy_questionnaire_admin_routes_are_not_registered(): void
    {
        $admin = $this->createAdmin();

        // L'ancien questionnaire n'est plus administrable : ses routes ont
        // disparu au profit du nouveau formulaire de devis. Les tables
        // historiques restent en place pour consulter les anciennes réponses.
        $this->assertFalse(app('router')->has('admin.questions.index'));
        $this->assertFalse(app('router')->has('admin.questions.options.store'));

        $this->actingAs($admin)->get('/admin/questions')->assertNotFound();
    }

    public function test_projects_by_type_counts_each_project(): void
    {
        Client::create(['name' => 'A', 'phone' => '+22501111111'])->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'submitted']);
        Client::create(['name' => 'B', 'phone' => '+22502222222'])->projects()->create(['project_type' => ['site_vitrine', 'ecommerce'], 'status' => 'submitted']);
        Client::create(['name' => 'C', 'phone' => '+22503333333'])->projects()->create(['project_type' => ['ecommerce'], 'status' => 'submitted']);

        $controller = app(DashboardController::class);
        $method = new \ReflectionMethod($controller, 'getProjectsByType');
        $result = $method->invoke($controller);

        $this->assertSame(2, $result->firstWhere('type', 'site_vitrine')['count']);
        $this->assertSame(2, $result->firstWhere('type', 'ecommerce')['count']);
    }

    public function test_admin_layout_displays_real_logo(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('brand-logo', false);
        $response->assertSee('IMAGES/Logo dominium.png', false);
    }
}
