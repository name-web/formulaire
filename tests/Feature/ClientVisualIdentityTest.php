<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientVisualIdentityTest extends TestCase
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
            'status' => 'client',
        ], $clientAttrs));

        Project::create([
            'client_id' => $client->id,
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $client;
    }

    private function createProspectWithProject(array $clientAttrs = []): Client
    {
        $client = Client::create(array_merge([
            'name' => 'Marie Martin',
            'phone' => '+2250708091012',
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

    private function hasHtmlClass(string $html, string $className): bool
    {
        return (bool) preg_match('/class="[^"]*\b'.preg_quote($className, '/').'\b[^"]*"/', $html);
    }

    // ── Client-clients index ──

    public function test_client_index_shows_green_name_and_avatar(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get(route('admin.client-clients.index'));
        $response->assertOk();

        $html = $response->content();
        $this->assertTrue($this->hasHtmlClass($html, 'client-name-is-client'));
        $this->assertTrue($this->hasHtmlClass($html, 'client-avatar-is-client'));
    }

    public function test_client_index_shows_badge_client(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get(route('admin.client-clients.index'));
        $response->assertOk();

        $html = $response->content();
        $this->assertTrue($this->hasHtmlClass($html, 'badge-client'));
    }

    // ── Client-clients show ──

    public function test_client_show_has_green_name_and_client_badge(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get(route('admin.client-clients.show', $client));
        $response->assertOk();

        $html = $response->content();
        $this->assertTrue($this->hasHtmlClass($html, 'client-name-is-client'));
        $this->assertTrue($this->hasHtmlClass($html, 'badge-client'));
        $this->assertTrue($this->hasHtmlClass($html, 'side-heading-client'));
    }

    // ── Dashboard ──

    public function test_dashboard_shows_green_identity_for_client_projects(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $html = $response->content();
        $this->assertTrue($this->hasHtmlClass($html, 'client-name-is-client'));
        $this->assertTrue($this->hasHtmlClass($html, 'client-avatar-is-client'));
    }

    public function test_dashboard_does_not_show_green_for_prospect_projects(): void
    {
        $admin = $this->createAdmin();
        $prospect = $this->createProspectWithProject();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $html = $response->content();
        $this->assertFalse($this->hasHtmlClass($html, 'client-name-is-client'));
        $this->assertFalse($this->hasHtmlClass($html, 'client-avatar-is-client'));
    }

    // ── Projects index ──

    public function test_projects_index_shows_green_for_client_column(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get(route('admin.projects.index'));
        $response->assertOk();

        $html = $response->content();
        $this->assertTrue($this->hasHtmlClass($html, 'client-name-is-client'));
        $this->assertTrue($this->hasHtmlClass($html, 'client-avatar-is-client'));
    }

    public function test_projects_index_does_not_show_green_for_prospect(): void
    {
        $admin = $this->createAdmin();
        $prospect = $this->createProspectWithProject();

        $response = $this->actingAs($admin)->get(route('admin.projects.index'));
        $response->assertOk();

        $html = $response->content();
        $this->assertFalse($this->hasHtmlClass($html, 'client-name-is-client'));
        $this->assertFalse($this->hasHtmlClass($html, 'client-avatar-is-client'));
    }

    // ── Projects show ──

    public function test_projects_show_shows_client_heading_for_client(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get(route('admin.projects.show', $client->projects->first()));
        $response->assertOk();

        $html = $response->content();
        $this->assertTrue($this->hasHtmlClass($html, 'side-heading-client'));
        $this->assertTrue($this->hasHtmlClass($html, 'badge-client'));
        $this->assertStringContainsString('Voir le profil client', $html);
    }

    public function test_projects_show_shows_prospect_heading_for_prospect(): void
    {
        $admin = $this->createAdmin();
        $prospect = $this->createProspectWithProject();

        $response = $this->actingAs($admin)->get(route('admin.projects.show', $prospect->projects->first()));
        $response->assertOk();

        $html = $response->content();
        $this->assertFalse($this->hasHtmlClass($html, 'side-heading-client'));
        $this->assertFalse($this->hasHtmlClass($html, 'badge-client'));
        $this->assertStringContainsString('Voir le profil prospect', $html);
    }

    // ── CSS classes exist in layout ──

    public function test_admin_layout_contains_client_css_classes(): void
    {
        $admin = $this->createAdmin();
        $client = $this->createClientWithProject();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $html = $response->content();
        $this->assertStringContainsString('.badge-client', $html);
        $this->assertStringContainsString('.client-name-is-client', $html);
        $this->assertStringContainsString('.client-avatar-is-client', $html);
        $this->assertStringContainsString('.side-heading-client', $html);
    }
}
