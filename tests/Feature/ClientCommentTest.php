<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCommentTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-comment-test@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createProspect(): Client
    {
        return Client::create([
            'name' => 'Prospect Test',
            'status' => 'prospect',
        ]);
    }

    public function test_prospect_detail_page_shows_comments_section(): void
    {
        $user = $this->createAdmin();
        $client = $this->createProspect();

        $response = $this->actingAs($user)->get(route('admin.clients.show', $client));

        $response->assertOk();
        $response->assertSee('Commentaires de suivi');
        $response->assertSee('Ajouter le commentaire');
    }

    public function test_client_detail_page_shows_comments_section(): void
    {
        $user = $this->createAdmin();
        $client = Client::create([
            'name' => 'Client Test',
            'status' => 'client',
        ]);

        $response = $this->actingAs($user)->get(route('admin.client-clients.show', $client));

        $response->assertOk();
        $response->assertSee('Commentaires de suivi');
        $response->assertSee('Ajouter le commentaire');
    }

    public function test_authenticated_user_can_add_comment(): void
    {
        $user = $this->createAdmin();
        $client = $this->createProspect();

        $response = $this->actingAs($user)->post(route('admin.clients.comments.store', $client), [
            'content' => 'Appel effectué, le prospect est intéressé.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Commentaire ajouté.');

        $this->assertDatabaseHas('client_comments', [
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Appel effectué, le prospect est intéressé.',
        ]);
    }

    public function test_comment_requires_content(): void
    {
        $user = $this->createAdmin();
        $client = $this->createProspect();

        $response = $this->actingAs($user)->post(route('admin.clients.comments.store', $client), [
            'content' => '',
        ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_comment_content_is_persisted(): void
    {
        $user = $this->createAdmin();
        $client = $this->createProspect();

        $this->actingAs($user)->post(route('admin.clients.comments.store', $client), [
            'content' => 'Relance prévue lundi.',
        ]);

        $comment = ClientComment::where('client_id', $client->id)->first();
        $this->assertEquals('Relance prévue lundi.', $comment->content);
        $this->assertEquals($user->id, $comment->user_id);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = $this->createAdmin();
        $client = $this->createProspect();
        $comment = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Commentaire à supprimer.',
        ]);

        $response = $this->actingAs($user)->delete(route('admin.clients.comments.destroy', [$client, $comment]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Commentaire supprimé.');
        $this->assertDatabaseMissing('client_comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_other_users_comment(): void
    {
        $owner = $this->createAdmin();
        $other = User::create([
            'name' => 'Other User',
            'email' => 'other-comment@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $client = $this->createProspect();
        $comment = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $owner->id,
            'content' => 'Commentaire du propriétaire.',
        ]);

        $response = $this->actingAs($other)->delete(route('admin.clients.comments.destroy', [$client, $comment]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('client_comments', ['id' => $comment->id]);
    }

    public function test_comments_displayed_newest_first(): void
    {
        $user = $this->createAdmin();
        $client = $this->createProspect();

        $c1 = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Premier commentaire.',
        ]);
        sleep(1);
        $c2 = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Deuxième commentaire.',
        ]);

        $response = $this->actingAs($user)->get(route('admin.clients.show', $client));

        $response->assertOk();
        $response->assertSeeInOrder([$c2->content, $c1->content]);
    }

    public function test_guest_cannot_add_comment(): void
    {
        $client = $this->createProspect();

        $response = $this->post(route('admin.clients.comments.store', $client), [
            'content' => 'Test',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_comment_cannot_be_deleted_through_another_client_url(): void
    {
        $user = $this->createAdmin();
        $clientA = $this->createProspect();
        $clientB = $this->createProspect();
        $comment = ClientComment::create([
            'client_id' => $clientA->id,
            'user_id' => $user->id,
            'content' => 'Commentaire du client A.',
        ]);

        $response = $this->actingAs($user)->delete(route('admin.clients.comments.destroy', [$clientB, $comment]));

        $response->assertStatus(404);
        $this->assertDatabaseHas('client_comments', ['id' => $comment->id]);
    }

    public function test_comment_belongs_to_client(): void
    {
        $user = $this->createAdmin();
        $client = $this->createProspect();

        $this->actingAs($user)->post(route('admin.clients.comments.store', $client), [
            'content' => 'Test relationship.',
        ]);

        $client->load('comments');
        $this->assertCount(1, $client->comments);
        $this->assertEquals('Test relationship.', $client->comments->first()->content);
    }
}
