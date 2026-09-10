<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Kamal',
            'email' => 'kamallareleve233@gmail.com',
            'password' => 'Kamal222.com',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_question_edit_page_renders(): void
    {
        $admin = $this->createAdmin();

        $question = Question::create([
            'title' => 'QA Edit Page',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        // Regression: this page returned a 500 while the option/condition
        // route names referenced by the view did not exist.
        $this->actingAs($admin)
            ->get(route('admin.questions.edit', $question))
            ->assertOk();
    }

    public function test_option_can_be_stored_through_named_route(): void
    {
        $admin = $this->createAdmin();

        $question = Question::create([
            'title' => 'QA Option Store',
            'type' => 'select',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.questions.options.store', $question), [
                'label' => 'QA Option',
                'value' => 'qa_option',
            ]);

        $response->assertRedirect(route('admin.questions.edit', $question));
        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'value' => 'qa_option',
        ]);
    }

    public function test_option_can_be_deleted_through_named_route(): void
    {
        $admin = $this->createAdmin();

        $question = Question::create([
            'title' => 'QA Option Delete',
            'type' => 'select',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $option = $question->options()->create([
            'label' => 'QA Option',
            'value' => 'qa_option',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.questions.options.destroy', [$question, $option]));

        $response->assertRedirect(route('admin.questions.edit', $question));
        $this->assertDatabaseMissing('question_options', ['id' => $option->id]);
    }
}
