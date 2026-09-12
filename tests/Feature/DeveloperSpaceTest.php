<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\Project;
use App\Models\User;
use App\Services\FollowUp\FollowUpBoardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperSpaceTest extends TestCase
{
    use RefreshDatabase;

    private string $developerPassword = 'DeveloperPass2026!';

    private function createDeveloper(string $name = 'Awa Traoré'): User
    {
        return User::factory()->developer()->create([
            'name' => $name,
            'password' => $this->developerPassword,
        ]);
    }

    private function createAssignedProspect(User $developer, string $name = 'Jean Dupont'): Client
    {
        return Client::factory()->assignedTo($developer)->create(['name' => $name]);
    }

    private function loginDeveloper(User $developer): void
    {
        $this->post('/developer/login', [
            'name' => $developer->name,
            'password' => $this->developerPassword,
        ])->assertRedirect(route('developer.dashboard'));

        $this->assertAuthenticatedAs($developer);
    }

    // ── Connexion par nom ───────────────────────────────────────────────────

    public function test_developer_login_page_renders(): void
    {
        $response = $this->get('/developer/login');

        $response->assertOk();
        $response->assertSee('Espace développeur');
    }

    public function test_developer_logs_in_with_his_name_and_password(): void
    {
        $developer = $this->createDeveloper();

        $this->loginDeveloper($developer);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $developer = $this->createDeveloper();

        $response = $this->post('/developer/login', [
            'name' => $developer->name,
            'password' => 'mauvais-mot-de-passe',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('name');
    }

    public function test_administrator_cannot_use_the_developer_login(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Déguisé',
            'password' => $this->developerPassword,
        ]);

        $this->post('/developer/login', [
            'name' => $admin->name,
            'password' => $this->developerPassword,
        ])->assertSessionHasErrors('name');

        $this->assertGuest();
    }

    public function test_inactive_developer_cannot_sign_in(): void
    {
        $developer = $this->createDeveloper();
        $developer->update(['is_active' => false]);

        $response = $this->post('/developer/login', [
            'name' => $developer->name,
            'password' => $this->developerPassword,
        ]);

        $response->assertRedirect(route('developer.login'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_the_developer_login(): void
    {
        $this->get('/developer')->assertRedirect(route('developer.login'));
        $this->get('/developer/prospects')->assertRedirect(route('developer.login'));
    }

    // ── Espace privé ────────────────────────────────────────────────────────

    public function test_developer_reaches_his_dashboard(): void
    {
        $developer = $this->createDeveloper();
        $this->createAssignedProspect($developer, 'Prospect Assigné');

        $response = $this->actingAs($developer)->get('/developer');

        $response->assertOk();
        $response->assertSee('Mes suivis');
        $response->assertSee('Prospect Assigné');
    }

    public function test_developer_only_sees_the_prospects_assigned_to_him(): void
    {
        $developer = $this->createDeveloper('Awa Traoré');
        $other = $this->createDeveloper('Bakary Diallo');

        $this->createAssignedProspect($developer, 'Prospect Awa');
        $this->createAssignedProspect($other, 'Prospect Bakary');
        Client::factory()->create(['name' => 'Prospect Sans Développeur']);

        $response = $this->actingAs($developer)->get('/developer/prospects');

        $response->assertOk();
        $response->assertSee('Prospect Awa');
        $response->assertDontSee('Prospect Bakary');
        $response->assertDontSee('Prospect Sans Développeur');
    }

    public function test_developer_cannot_open_another_developers_prospect(): void
    {
        $developer = $this->createDeveloper('Awa Traoré');
        $other = $this->createDeveloper('Bakary Diallo');

        Client::factory()->assignedTo($developer)->create(['name' => 'Prospect Awa']);
        $foreign = Client::factory()->assignedTo($other)->create(['name' => 'Prospect Bakary']);

        // URL forgée vers le prospect d'un autre développeur.
        $this->actingAs($developer)
            ->get('/developer/prospects/'.$foreign->id)
            ->assertNotFound();

        // Et vers un prospect non attribué.
        $unassigned = Client::factory()->create();
        $this->actingAs($developer)
            ->get('/developer/prospects/'.$unassigned->id)
            ->assertNotFound();
    }

    public function test_developer_cannot_write_on_another_developers_prospect(): void
    {
        $developer = $this->createDeveloper('Awa Traoré');
        $other = $this->createDeveloper('Bakary Diallo');

        $foreign = Client::factory()->assignedTo($other)->create(['name' => 'Prospect Bakary']);

        $this->actingAs($developer)
            ->post('/developer/prospects/'.$foreign->id.'/comments', ['content' => 'Tentative'])
            ->assertNotFound();

        $this->actingAs($developer)
            ->patch('/developer/prospects/'.$foreign->id.'/follow-up', [
                'next_action' => 'Tentative',
                'next_action_date' => now()->addDay()->format('Y-m-d'),
            ])
            ->assertNotFound();

        $this->actingAs($developer)
            ->patch('/developer/prospects/'.$foreign->id.'/convert')
            ->assertNotFound();

        $this->assertSame(0, ClientComment::count());
        $this->assertNull($foreign->fresh()->next_action);
        $this->assertSame('prospect', $foreign->fresh()->status);
    }

    public function test_developer_cannot_reach_the_other_spaces(): void
    {
        $developer = $this->createDeveloper();

        $this->actingAs($developer)->get('/admin')->assertForbidden();
        $this->actingAs($developer)->get('/admin/clients')->assertForbidden();
        $this->actingAs($developer)->get('/super-admin')->assertForbidden();
        $this->actingAs($developer)->get('/dashboard')->assertForbidden();
    }

    // ── Suivi du prospect ───────────────────────────────────────────────────

    public function test_developer_adds_a_follow_up_comment(): void
    {
        $developer = $this->createDeveloper();
        $prospect = $this->createAssignedProspect($developer);

        $response = $this->actingAs($developer)
            ->post('/developer/prospects/'.$prospect->id.'/comments', [
                'content' => 'Appel du 12/09 : le prospect souhaite un devis.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('client_comments', [
            'client_id' => $prospect->id,
            'user_id' => $developer->id,
            'content' => 'Appel du 12/09 : le prospect souhaite un devis.',
        ]);

        // Persistance : le suivi est visible dans la fiche du prospect.
        $this->actingAs($developer)
            ->get('/developer/prospects/'.$prospect->id)
            ->assertOk()
            ->assertSee('Appel du 12/09 : le prospect souhaite un devis.');
    }

    public function test_follow_up_comment_is_required(): void
    {
        $developer = $this->createDeveloper();
        $prospect = $this->createAssignedProspect($developer);

        $this->actingAs($developer)
            ->post('/developer/prospects/'.$prospect->id.'/comments', ['content' => ''])
            ->assertSessionHasErrors('content');

        $this->assertSame(0, ClientComment::count());
    }

    public function test_developer_defines_the_next_action_and_date(): void
    {
        $developer = $this->createDeveloper();
        $prospect = $this->createAssignedProspect($developer);

        $response = $this->actingAs($developer)
            ->patch('/developer/prospects/'.$prospect->id.'/follow-up', [
                'next_action' => 'Envoyer le devis',
                'next_action_date' => now()->addDays(2)->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $prospect->refresh();
        $this->assertSame('Envoyer le devis', $prospect->next_action);
        $this->assertSame(now()->addDays(2)->format('Y-m-d'), $prospect->next_action_date->format('Y-m-d'));
    }

    public function test_next_action_date_must_be_a_date(): void
    {
        $developer = $this->createDeveloper();
        $prospect = $this->createAssignedProspect($developer);

        $this->actingAs($developer)
            ->patch('/developer/prospects/'.$prospect->id.'/follow-up', [
                'next_action' => 'Appeler',
                'next_action_date' => 'pas-une-date',
            ])
            ->assertSessionHasErrors('next_action_date');
    }

    // ── Tableau de bord des suivis ──────────────────────────────────────────

    public function test_board_splits_follow_ups_today_overdue_in_progress_and_converted(): void
    {
        $developer = $this->createDeveloper();
        $other = $this->createDeveloper('Bakary Diallo');

        $today = $this->createAssignedProspect($developer, 'Suivi Aujourd hui');
        $today->update(['next_action' => 'Appeler', 'next_action_date' => today()]);

        $late = $this->createAssignedProspect($developer, 'Suivi En Retard');
        $late->update(['next_action' => 'Relancer', 'next_action_date' => today()->subDays(3)]);

        $converted = Client::factory()->client()->assignedTo($developer)->create(['name' => 'Client Converti']);

        // Prospection d'un autre développeur : jamais comptée ici.
        $foreign = $this->createAssignedProspect($other, 'Suivi Autre Dev');
        $foreign->update(['next_action_date' => today()]);

        $response = $this->actingAs($developer)->get('/developer');

        $response->assertOk();
        $response->assertSee('Suivi Aujourd hui');
        $response->assertSee('Suivi En Retard');
        $response->assertSee('Client Converti');
        $response->assertDontSee('Suivi Autre Dev');

        $board = app(FollowUpBoardService::class)->forDeveloper($developer);

        $this->assertSame(['Suivi Aujourd hui'], $board['dueToday']->pluck('name')->all());
        $this->assertSame(['Suivi En Retard'], $board['overdue']->pluck('name')->all());
        $this->assertSame(2, $board['inProgress']->count());
        $this->assertSame(['Client Converti'], $board['converted']->pluck('name')->all());
        $this->assertSame($converted->id, $board['converted']->first()->id);
    }

    // ── Conversion prospect → client ─────────────────────────────────────────

    public function test_developer_converts_his_prospect_and_keeps_the_history(): void
    {
        $developer = $this->createDeveloper();
        $prospect = $this->createAssignedProspect($developer, 'Prospect Gagné');

        Project::create([
            'client_id' => $prospect->id,
            'project_type' => ['site_vitrine'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        ClientComment::factory()->create([
            'client_id' => $prospect->id,
            'user_id' => $developer->id,
            'content' => 'Premier contact téléphonique.',
        ]);

        $response = $this->actingAs($developer)->patch('/developer/prospects/'.$prospect->id.'/convert');

        $response->assertRedirect(route('developer.prospects.show', $prospect));
        $response->assertSessionHasNoErrors();

        $prospect->refresh();
        $this->assertSame('client', $prospect->status);
        // Traçabilité du développeur responsable conservée.
        $this->assertSame($developer->id, $prospect->assigned_developer_id);

        // Aucun doublon client : la même ligne est conservée avec son historique.
        $this->assertSame(1, Client::count());
        $this->assertSame(1, $prospect->projects()->count());
        $this->assertDatabaseHas('client_comments', [
            'client_id' => $prospect->id,
            'content' => 'Premier contact téléphonique.',
        ]);
    }

    public function test_cannot_convert_an_already_converted_client(): void
    {
        $developer = $this->createDeveloper();
        $client = Client::factory()->client()->assignedTo($developer)->create();

        $response = $this->actingAs($developer)->patch('/developer/prospects/'.$client->id.'/convert');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ce prospect est déjà un client.');
    }
}
