<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityJournalTest extends TestCase
{
    use RefreshDatabase;

    private function createCommercial(string $name = 'Aminata Diallo'): User
    {
        return User::factory()->commercial()->create(['name' => $name]);
    }

    private function createAssignedProspect(User $commercial, string $name = 'Jean Dupont'): Client
    {
        return Client::factory()->assignedToCommercial($commercial)->create(['name' => $name]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['name' => 'Admin Général', 'role' => 'admin']);
    }

    private function createSuperAdmin(): User
    {
        return User::factory()->superAdmin()->create(['name' => 'Super Admin']);
    }

    // ── 1. Actions du Commercial : enregistrement côté serveur ──────────────

    public function test_opening_an_assigned_prospect_is_recorded_in_the_journal(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $this->actingAs($commercial)
            ->get('/commercial/prospects/'.$prospect->id)
            ->assertOk();

        $this->assertDatabaseHas('client_activities', [
            'client_id' => $prospect->id,
            'user_id' => $commercial->id,
            'type' => ClientActivity::TYPE_VIEWED,
        ]);
    }

    public function test_reloading_the_same_record_does_not_fill_the_journal(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $this->actingAs($commercial)->get('/commercial/prospects/'.$prospect->id);
        $this->actingAs($commercial)->get('/commercial/prospects/'.$prospect->id);
        $this->actingAs($commercial)->get('/commercial/prospects/'.$prospect->id);

        $this->assertSame(1, ClientActivity::where('type', ClientActivity::TYPE_VIEWED)->count());
    }

    public function test_the_journal_never_duplicates_the_prospect_row(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial, 'Prospect Unique');

        $this->actingAs($commercial)->get('/commercial/prospects/'.$prospect->id);
        $this->actingAs($commercial)->post('/commercial/prospects/'.$prospect->id.'/comments', [
            'content' => 'Premier appel.',
        ]);
        $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/follow-up', [
            'next_action' => 'Envoyer le devis',
            'next_action_date' => now()->addWeek()->format('Y-m-d'),
        ]);

        $this->assertSame(1, Client::where('name', 'Prospect Unique')->count());
        $this->assertSame(3, ClientActivity::where('client_id', $prospect->id)->count());
    }

    public function test_adding_a_note_is_recorded_with_its_excerpt(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $this->actingAs($commercial)->post('/commercial/prospects/'.$prospect->id.'/comments', [
            'content' => 'Le prospect demande une relance lundi.',
        ]);

        $activity = ClientActivity::where('type', ClientActivity::TYPE_COMMENT_ADDED)->first();

        $this->assertNotNull($activity);
        $this->assertSame($prospect->id, $activity->client_id);
        $this->assertSame($commercial->id, $activity->user_id);
        $this->assertStringContainsString('Le prospect demande une relance lundi.', $activity->description);
    }

    public function test_scheduling_a_follow_up_is_recorded(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);
        $date = now()->addDays(3)->format('Y-m-d');

        $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/follow-up', [
            'next_action' => 'Envoyer le devis',
            'next_action_date' => $date,
        ]);

        $activity = ClientActivity::where('type', ClientActivity::TYPE_FOLLOW_UP_SCHEDULED)->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Envoyer le devis', $activity->description);
        $this->assertSame($date, $activity->meta['date']);
    }

    public function test_closing_a_follow_up_is_recorded_as_completed(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/follow-up', [
            'next_action' => 'Appeler le prospect',
            'next_action_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/follow-up', [
            'next_action' => null,
            'next_action_date' => null,
        ]);

        $activity = ClientActivity::where('type', ClientActivity::TYPE_FOLLOW_UP_COMPLETED)->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Appeler le prospect', $activity->description);
    }

    public function test_rewriting_the_next_action_is_recorded_as_a_business_update(): void
    {
        $commercial = $this->createCommercial();
        $date = now()->addDays(2)->format('Y-m-d');
        $prospect = Client::factory()->assignedToCommercial($commercial)->create([
            'next_action' => 'Appeler le prospect',
            'next_action_date' => $date,
        ]);

        $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/follow-up', [
            'next_action' => 'Appeler le prospect et sa secrétaire',
            'next_action_date' => $date,
        ]);

        $activity = ClientActivity::where('type', ClientActivity::TYPE_UPDATED)->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Appeler le prospect et sa secrétaire', $activity->description);
    }

    public function test_saving_an_unchanged_follow_up_writes_nothing_new(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);
        $payload = [
            'next_action' => 'Envoyer le devis',
            'next_action_date' => now()->addDays(4)->format('Y-m-d'),
        ];

        $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/follow-up', $payload);
        $this->actingAs($commercial)->patch('/commercial/prospects/'.$prospect->id.'/follow-up', $payload);

        $this->assertSame(1, ClientActivity::where('client_id', $prospect->id)->count());
    }

    public function test_converting_a_prospect_is_recorded(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $this->actingAs($commercial)
            ->patch('/commercial/prospects/'.$prospect->id.'/convert')
            ->assertRedirect(route('commercial.prospects.show', $prospect));

        $this->assertDatabaseHas('client_activities', [
            'client_id' => $prospect->id,
            'user_id' => $commercial->id,
            'type' => ClientActivity::TYPE_CONVERTED,
        ]);
    }

    public function test_a_commercial_never_writes_on_another_commercials_prospect(): void
    {
        $commercial = $this->createCommercial('Aminata Diallo');
        $other = $this->createCommercial('Bakary Traoré');
        $foreign = $this->createAssignedProspect($other, 'Prospect Bakary');

        $this->actingAs($commercial)->get('/commercial/prospects/'.$foreign->id)->assertNotFound();
        $this->actingAs($commercial)
            ->post('/commercial/prospects/'.$foreign->id.'/comments', ['content' => 'Tentative'])
            ->assertNotFound();

        $this->assertSame(0, ClientActivity::count());
    }

    // ── 2. Espace Admin ─────────────────────────────────────────────────────

    public function test_admin_activity_page_renders_the_journal(): void
    {
        $admin = $this->createAdmin();
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial, 'Prospect Suivi');

        ClientActivity::factory()->forClient($prospect)->by($commercial)->ofType(
            ClientActivity::TYPE_COMMENT_ADDED,
            'Note de suivi ajoutée : « Premier appel ».',
        )->create();

        $response = $this->actingAs($admin)->get('/admin/activities');

        $response->assertOk();
        $response->assertSee('Journal d\'activité', false);
        $response->assertSee('Prospect Suivi');
        $response->assertSee('Note de suivi ajoutée');
        $response->assertSee($commercial->name);
        $response->assertSee('Activités');
    }

    public function test_admin_activity_page_lists_entries_chronologically(): void
    {
        $admin = $this->createAdmin();
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_VIEWED, 'Activite la plus ancienne.')
            ->create(['created_at' => now()->subHours(3)]);

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_FOLLOW_UP_SCHEDULED, 'Activite intermediaire.')
            ->create(['created_at' => now()->subHour()]);

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_FOLLOW_UP_COMPLETED, 'Activite la plus recente.')
            ->create(['created_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/activities');

        $response->assertOk();
        $response->assertSeeInOrder([
            'Activite la plus recente.',
            'Activite intermediaire.',
            'Activite la plus ancienne.',
        ]);
    }

    public function test_admin_activity_page_filters_by_type_and_family(): void
    {
        $admin = $this->createAdmin();
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_CONVERTED, 'Activite de conversion.')
            ->create();

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_VIEWED, 'Activite de consultation.')
            ->create();

        $byType = $this->actingAs($admin)->get('/admin/activities?type=converted');
        $byType->assertOk();
        $byType->assertSee('Activite de conversion.');
        $byType->assertDontSee('Activite de consultation.');

        $byGroup = $this->actingAs($admin)->get('/admin/activities?group=conversion');
        $byGroup->assertOk();
        $byGroup->assertSee('Activite de conversion.');
        $byGroup->assertDontSee('Activite de consultation.');
    }

    public function test_admin_activity_page_filters_by_author_search_and_period(): void
    {
        $admin = $this->createAdmin();
        $commercial = $this->createCommercial('Aminata Diallo');
        $other = $this->createCommercial('Bakary Traoré');

        $prospect = $this->createAssignedProspect($commercial, 'Prospect Aminata');
        $foreign = $this->createAssignedProspect($other, 'Prospect Bakary');

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_VIEWED, 'Activite Aminata.')
            ->create(['created_at' => now()->subDays(2)]);

        ClientActivity::factory()->forClient($foreign)->by($other)
            ->ofType(ClientActivity::TYPE_VIEWED, 'Activite Bakary.')
            ->create(['created_at' => now()->subDays(10)]);

        $byAuthor = $this->actingAs($admin)->get('/admin/activities?user_id='.$other->id);
        $byAuthor->assertOk();
        $byAuthor->assertSee('Activite Bakary.');
        $byAuthor->assertDontSee('Activite Aminata.');

        $bySearch = $this->actingAs($admin)->get('/admin/activities?search='.urlencode('Prospect Aminata'));
        $bySearch->assertOk();
        $bySearch->assertSee('Activite Aminata.');
        $bySearch->assertDontSee('Activite Bakary.');

        $byPeriod = $this->actingAs($admin)->get('/admin/activities?from='.now()->subDays(3)->format('Y-m-d'));
        $byPeriod->assertOk();
        $byPeriod->assertSee('Activite Aminata.');
        $byPeriod->assertDontSee('Activite Bakary.');
    }

    public function test_admin_dashboard_previews_the_latest_activities(): void
    {
        $admin = $this->createAdmin();
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial, 'Prospect Dashboard');

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_FOLLOW_UP_SCHEDULED, 'Relance programmee depuis le dashboard.')
            ->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Activités récentes');
        $response->assertSee('Prospect Dashboard');
        $response->assertSee('Voir toutes les activités');
    }

    public function test_admin_converting_a_prospect_is_recorded(): void
    {
        $admin = $this->createAdmin();
        $prospect = Client::factory()->create(['name' => 'Prospect Admin']);

        $this->actingAs($admin)
            ->patch('/admin/clients/'.$prospect->id.'/convert')
            ->assertRedirect(route('admin.client-clients.show', $prospect));

        $this->assertDatabaseHas('client_activities', [
            'client_id' => $prospect->id,
            'user_id' => $admin->id,
            'type' => ClientActivity::TYPE_CONVERTED,
        ]);
    }

    public function test_project_status_change_is_recorded_on_the_client_journal(): void
    {
        $admin = $this->createAdmin();
        $client = Client::factory()->create(['name' => 'Client Projet']);

        $project = Project::create([
            'client_id' => $client->id,
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch('/admin/projects/'.$project->id.'/status', ['status' => 'in_progress'])
            ->assertRedirect(route('admin.projects.show', $project));

        $activity = ClientActivity::where('type', ClientActivity::TYPE_STATUS_CHANGED)->first();

        $this->assertNotNull($activity);
        $this->assertSame($client->id, $activity->client_id);
        $this->assertStringContainsString('En cours', $activity->description);
        $this->assertSame($project->id, $activity->meta['project_id']);
    }

    public function test_journal_entry_survives_the_deletion_of_its_author(): void
    {
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        ClientActivity::factory()->forClient($prospect)->by($commercial)->create();

        $commercial->delete();

        $this->assertDatabaseHas('client_activities', [
            'client_id' => $prospect->id,
            'user_id' => null,
        ]);
    }

    // ── 3. Espace Super Admin ───────────────────────────────────────────────

    public function test_super_admin_reaches_the_global_journal(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial, 'Prospect Global');

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_FOLLOW_UP_COMPLETED, 'Relance effectuee par le commercial.')
            ->create();

        $response = $this->actingAs($superAdmin)->get('/super-admin/activities');

        $response->assertOk();
        $response->assertSee('Prospect Global');
        $response->assertSee('Relance effectuee par le commercial.');
        $response->assertSee($commercial->name);
    }

    public function test_super_admin_dashboard_previews_the_latest_activities(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial, 'Prospect Dashboard SA');

        ClientActivity::factory()->forClient($prospect)->by($commercial)
            ->ofType(ClientActivity::TYPE_COMMENT_ADDED, 'Note visible par le Super Admin.')
            ->create();

        $response = $this->actingAs($superAdmin)->get('/super-admin');

        $response->assertOk();
        $response->assertSee('Activités récentes');
        $response->assertSee('Prospect Dashboard SA');
        $response->assertSee('Note visible par le Super Admin.');
        $response->assertSee('Journal global');
    }

    public function test_super_admin_assignment_is_recorded(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $commercial = $this->createCommercial('Aminata Diallo');
        $prospect = Client::factory()->create(['name' => 'Prospect À Attribuer']);

        $this->actingAs($superAdmin)->patch('/super-admin/prospects/'.$prospect->id.'/assignment', [
            'assigned_commercial_id' => $commercial->id,
        ]);

        $activity = ClientActivity::where('type', ClientActivity::TYPE_ASSIGNMENT_CHANGED)->first();

        $this->assertNotNull($activity);
        $this->assertSame($superAdmin->id, $activity->user_id);
        $this->assertStringContainsString('Aminata Diallo', $activity->description);
        $this->assertSame('Aminata Diallo', $activity->meta['commercial_to']);
    }

    public function test_unchanged_assignment_writes_nothing(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $commercial = $this->createCommercial();

        $prospect = Client::factory()->assignedToCommercial($commercial)->create(['name' => 'Prospect Stable']);

        $this->actingAs($superAdmin)->patch('/super-admin/prospects/'.$prospect->id.'/assignment', [
            'assigned_commercial_id' => $commercial->id,
        ]);

        $this->assertSame(0, ClientActivity::count());
    }

    public function test_removing_an_assignment_is_recorded(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $commercial = $this->createCommercial('Bakary Traoré');
        $prospect = Client::factory()->assignedToCommercial($commercial)->create(['name' => 'Prospect Retiré']);

        $this->actingAs($superAdmin)->patch('/super-admin/prospects/'.$prospect->id.'/assignment', [
            'assigned_commercial_id' => null,
        ]);

        $activity = ClientActivity::where('type', ClientActivity::TYPE_ASSIGNMENT_CHANGED)->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('non attribué', $activity->description);
    }

    public function test_super_admin_viewing_a_prospect_does_not_fill_the_commercial_journal(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $commercial = $this->createCommercial();
        $prospect = $this->createAssignedProspect($commercial);

        $this->actingAs($superAdmin)->get('/super-admin/prospects/'.$prospect->id)->assertOk();

        $this->assertSame(0, ClientActivity::count());
    }

    // ── 4. Permissions ──────────────────────────────────────────────────────

    public function test_admin_journal_permissions(): void
    {
        $commercial = $this->createCommercial();
        $developer = User::factory()->developer()->create();

        $this->get('/admin/activities')->assertRedirect(route('login'));
        $this->actingAs($commercial)->get('/admin/activities')->assertForbidden();
        $this->actingAs($developer)->get('/admin/activities')->assertForbidden();
        $this->actingAs($this->createAdmin())->get('/admin/activities')->assertOk();
        $this->actingAs($this->createSuperAdmin())->get('/admin/activities')->assertOk();
    }

    public function test_super_admin_journal_permissions(): void
    {
        $commercial = $this->createCommercial();
        $developer = User::factory()->developer()->create();

        $this->get('/super-admin/activities')->assertRedirect(route('super_admin.login'));
        $this->actingAs($commercial)->get('/super-admin/activities')->assertForbidden();
        $this->actingAs($developer)->get('/super-admin/activities')->assertForbidden();
        $this->actingAs($this->createAdmin())->get('/super-admin/activities')->assertForbidden();
        $this->actingAs($this->createSuperAdmin())->get('/super-admin/activities')->assertOk();
    }

    public function test_the_admin_sidebar_exposes_the_activities_entry(): void
    {
        $response = $this->actingAs($this->createAdmin())->get('/admin');

        $response->assertOk();
        $response->assertSee(route('admin.activities.index'), false);
    }

    public function test_the_super_admin_sidebar_exposes_the_activities_entry(): void
    {
        $response = $this->actingAs($this->createSuperAdmin())->get('/super-admin');

        $response->assertOk();
        $response->assertSee(route('super_admin.activities.index'), false);
    }

    public function test_the_commercial_space_is_unchanged(): void
    {
        $commercial = $this->createCommercial();
        $this->createAssignedProspect($commercial, 'Prospect Commercial');

        $this->actingAs($commercial)->get('/commercial')->assertOk();
        $this->actingAs($commercial)->get('/commercial/prospects')->assertOk();
        $this->actingAs($commercial)->get('/commercial/prospects')->assertSee('Prospect Commercial');
        $this->actingAs($commercial)->get('/commercial/converted')->assertOk();
        $this->actingAs($commercial)->get('/commercial/activities')->assertNotFound();
    }
}
