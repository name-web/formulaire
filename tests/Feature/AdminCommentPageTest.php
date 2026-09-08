<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminCommentPageTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-comment-page-test@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_comments_page_requires_auth(): void
    {
        $response = $this->get(route('admin.comments.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_comments_page_renders_for_admin(): void
    {
        $user = $this->createAdmin();
        $response = $this->actingAs($user)->get(route('admin.comments.index'));
        $response->assertOk();
        $response->assertSee('Commentaires');
    }

    public function test_comments_page_shows_all_comments(): void
    {
        $user = $this->createAdmin();
        $client = Client::create(['name' => 'Test Client', 'status' => 'prospect']);
        $comment = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Appel de suivi effectué.',
        ]);

        $response = $this->actingAs($user)->get(route('admin.comments.index'));
        $response->assertOk();
        $response->assertSee('Appel de suivi effectué.');
        $response->assertSee('Test Client');
    }

    public function test_search_filters_by_client_name(): void
    {
        $user = $this->createAdmin();
        $client1 = Client::create(['name' => 'Alice Corp', 'status' => 'prospect']);
        $client2 = Client::create(['name' => 'Bob Inc', 'status' => 'client']);
        ClientComment::create(['client_id' => $client1->id, 'user_id' => $user->id, 'content' => 'Comment Alice']);
        ClientComment::create(['client_id' => $client2->id, 'user_id' => $user->id, 'content' => 'Comment Bob']);

        $response = $this->actingAs($user)->get(route('admin.comments.index', ['search' => 'Alice']));
        $response->assertOk();
        $response->assertSee('Comment Alice');
        $response->assertDontSee('Comment Bob');
    }

    public function test_type_filter_by_prospect(): void
    {
        $user = $this->createAdmin();
        $prospect = Client::create(['name' => 'Prospect One', 'status' => 'prospect']);
        $client = Client::create(['name' => 'Client One', 'status' => 'client']);
        ClientComment::create(['client_id' => $prospect->id, 'user_id' => $user->id, 'content' => 'Prospect comment']);
        ClientComment::create(['client_id' => $client->id, 'user_id' => $user->id, 'content' => 'Client comment']);

        $response = $this->actingAs($user)->get(route('admin.comments.index', ['type' => 'prospect']));
        $response->assertOk();
        $response->assertSee('Prospect comment');
        $response->assertDontSee('Client comment');
    }

    public function test_type_filter_by_client(): void
    {
        $user = $this->createAdmin();
        $prospect = Client::create(['name' => 'Prospect Two', 'status' => 'prospect']);
        $client = Client::create(['name' => 'Client Two', 'status' => 'client']);
        ClientComment::create(['client_id' => $prospect->id, 'user_id' => $user->id, 'content' => 'Prospect comment 2']);
        ClientComment::create(['client_id' => $client->id, 'user_id' => $user->id, 'content' => 'Client comment 2']);

        $response = $this->actingAs($user)->get(route('admin.comments.index', ['type' => 'client']));
        $response->assertOk();
        $response->assertSee('Client comment 2');
        $response->assertDontSee('Prospect comment 2');
    }

    public function test_user_filter(): void
    {
        $user1 = $this->createAdmin();
        $user2 = User::create([
            'name' => 'Other User',
            'email' => 'other-comment-page@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $client = Client::create(['name' => 'Filter Client', 'status' => 'prospect']);
        ClientComment::create(['client_id' => $client->id, 'user_id' => $user1->id, 'content' => 'By user1']);
        ClientComment::create(['client_id' => $client->id, 'user_id' => $user2->id, 'content' => 'By user2']);

        $response = $this->actingAs($user1)->get(route('admin.comments.index', ['user_id' => $user2->id]));
        $response->assertOk();
        $response->assertSee('By user2');
        $response->assertDontSee('By user1');
    }

    private function setCreatedAt(Model $model, Carbon $date): void
    {
        $model->timestamps = false;
        $model->created_at = $date;
        $model->saveQuietly();
        $model->timestamps = true;
    }

    public function test_date_filter(): void
    {
        $user = $this->createAdmin();
        $client = Client::create(['name' => 'Date Client', 'status' => 'prospect']);
        $old = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Old comment',
        ]);
        $this->setCreatedAt($old, now()->subDays(10));
        $recent = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Recent comment',
        ]);
        $this->setCreatedAt($recent, now()->subDays(2));

        $response = $this->actingAs($user)->get(route('admin.comments.index', [
            'from' => now()->subDays(5)->format('Y-m-d'),
        ]));
        $response->assertOk();
        $response->assertSee('Recent comment');
        $response->assertDontSee('Old comment');
    }

    public function test_comments_ordered_newest_first(): void
    {
        $user = $this->createAdmin();
        $client = Client::create(['name' => 'Order Client', 'status' => 'prospect']);
        $first = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'First comment',
        ]);
        $this->setCreatedAt($first, now()->subDays(5));
        $second = ClientComment::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Second comment',
        ]);
        $this->setCreatedAt($second, now()->subDays(1));

        $response = $this->actingAs($user)->get(route('admin.comments.index'));
        $response->assertOk();
        $response->assertSeeInOrder(['Second comment', 'First comment']);
    }

    public function test_comments_page_shows_filters_form(): void
    {
        $user = $this->createAdmin();
        $response = $this->actingAs($user)->get(route('admin.comments.index'));
        $response->assertOk();
        $response->assertSee('Recherche');
        $response->assertSee('Type');
        $response->assertSee('Auteur');
        $response->assertSee('Du');
        $response->assertSee('Au');
    }

    public function test_pagination_works(): void
    {
        $user = $this->createAdmin();
        $client = Client::create(['name' => 'Pag Client', 'status' => 'prospect']);

        for ($i = 0; $i < 20; $i++) {
            ClientComment::create([
                'client_id' => $client->id,
                'user_id' => $user->id,
                'content' => "Comment number {$i}",
            ]);
        }

        $response = $this->actingAs($user)->get(route('admin.comments.index'));
        $response->assertOk();
        $response->assertSee('Comment number 0');
        $response->assertSee('Comment number 14');
        $response->assertDontSee('Comment number 15');
    }
}
