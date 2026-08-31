<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Client;
use App\Models\Project;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_index_page_loads(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertStatus(200);
    }

    public function test_start_creates_client_and_project(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+225 07070707',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name' => 'Jean Dupont',
            'phone' => '+225 07070707',
        ]);

        $this->assertDatabaseHas('projects', [
            'status' => 'draft',
        ]);

        $project = Project::first();
        $this->assertIsArray($project->project_type);
        $this->assertEquals(['site_vitrine'], $project->project_type);
    }

    public function test_start_accepts_multiple_project_types(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+225 07070707',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile'],
        ]);

        $response->assertRedirect();

        $project = Project::first();
        $this->assertEquals(['site_vitrine', 'ecommerce', 'application_mobile'], $project->project_type);
    }

    public function test_project_type_site_vitrine_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test Vitrine',
            'phone' => '+22501020304',
            'project_types' => ['site_vitrine'],
        ]);

        $project = Project::first();
        $this->assertEquals(['site_vitrine'], $project->project_type);
        $this->assertEquals('Site vitrine', $project->type_labels);
    }

    public function test_project_type_ecommerce_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test E-commerce',
            'phone' => '+22501020304',
            'project_types' => ['ecommerce'],
        ]);

        $project = Project::first();
        $this->assertEquals(['ecommerce'], $project->project_type);
        $this->assertEquals('Boutique en ligne', $project->type_labels);
    }

    public function test_project_type_application_mobile_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test Mobile',
            'phone' => '+22501020304',
            'project_types' => ['application_mobile'],
        ]);

        $project = Project::first();
        $this->assertEquals(['application_mobile'], $project->project_type);
        $this->assertEquals('Application mobile', $project->type_labels);
    }

    public function test_project_type_logiciel_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test Logiciel',
            'phone' => '+22501020304',
            'project_types' => ['logiciel'],
        ]);

        $project = Project::first();
        $this->assertEquals(['logiciel'], $project->project_type);
        $this->assertEquals('Logiciel / ERP / CRM', $project->type_labels);
    }

    public function test_project_type_autre_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test Autre',
            'phone' => '+22501020304',
            'project_types' => ['autre'],
        ]);

        $project = Project::first();
        $this->assertEquals(['autre'], $project->project_type);
        $this->assertEquals('Autre', $project->type_labels);
    }

    public function test_start_requires_at_least_one_project_type(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+225 07070707',
            'project_types' => [],
        ]);

        $response->assertSessionHasErrors(['project_types']);
    }

    public function test_start_rejects_invalid_project_type(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+225 07070707',
            'project_types' => ['invalid_type'],
        ]);

        $response->assertSessionHasErrors(['project_types.0']);
    }

    public function test_start_requires_phone(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_start_requires_name_and_phone(): void
    {
        $response = $this->post(route('questionnaire.start'), []);

        $response->assertSessionHasErrors(['name', 'phone', 'project_types']);
    }

    public function test_step_page_loads_with_valid_session(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        Question::create([
            'title' => 'Quel type de projet ?',
            'type' => 'select',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->get(route('questionnaire.step', 1));

        $response->assertStatus(200);
    }

    public function test_step_page_loads_with_multiple_project_types(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine', 'ecommerce'], 'status' => 'draft']);

        Question::create([
            'title' => 'Question pour site vitrine',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        Question::create([
            'title' => 'Question pour ecommerce',
            'type' => 'text',
            'project_types' => ['ecommerce'],
            'sort_order' => 2,
        ]);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine', 'ecommerce'],
        ])->get(route('questionnaire.step', 1));

        $response->assertStatus(200);
    }

    public function test_step_page_returns_404_without_session(): void
    {
        $response = $this->get(route('questionnaire.step', 1));

        $response->assertStatus(404);
    }

    public function test_store_answer_for_text_question(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        $question = Question::create([
            'title' => 'Décrivez votre projet',
            'type' => 'textarea',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'value' => 'Je veux un site vitrine',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('answers', [
            'project_id' => $project->id,
            'question_id' => $question->id,
            'text_value' => 'Je veux un site vitrine',
        ]);
    }

    public function test_store_answer_for_select_question(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        $question = Question::create([
            'title' => 'Quel type de projet ?',
            'type' => 'select',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $option = QuestionOption::create([
            'question_id' => $question->id,
            'label' => 'Vitrine',
            'value' => 'vitrine',
            'sort_order' => 1,
        ]);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$option->id],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('answer_options', [
            'question_option_id' => $option->id,
        ]);
    }

    public function test_store_answer_for_multiselect_with_other(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        $question = Question::create([
            'title' => 'Quelles pages souhaitez-vous ?',
            'type' => 'multiselect',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $option1 = QuestionOption::create([
            'question_id' => $question->id,
            'label' => 'Accueil',
            'value' => 'accueil',
            'sort_order' => 1,
        ]);

        $optionOther = QuestionOption::create([
            'question_id' => $question->id,
            'label' => 'Autre',
            'value' => 'autre',
            'sort_order' => 2,
        ]);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$option1->id, $optionOther->id],
            'other_value' => 'Page portfolio',
        ]);

        $response->assertRedirect();

        $answer = Answer::where('project_id', $project->id)
            ->where('question_id', $question->id)
            ->first();

        $this->assertNotNull($answer);
        $this->assertEquals('Page portfolio', $answer->other_value);
        $this->assertCount(2, $answer->answerOptions);
    }

    public function test_previous_route_redirects_correctly(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        $question1 = Question::create([
            'title' => 'Question 1',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        Question::create([
            'title' => 'Question 2',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 2,
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $question1->id,
            'text_value' => 'Réponse 1',
        ]);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->get(route('questionnaire.previous'));

        $response->assertRedirect();
    }

    public function test_summary_page_loads(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        $question = Question::create([
            'title' => 'Question 1',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $question->id,
            'text_value' => 'Réponse test',
        ]);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->get(route('questionnaire.summary'));

        $response->assertStatus(200);
    }

    public function test_submit_changes_project_status(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.submit'));

        $response->assertRedirect(route('questionnaire.thankyou'));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'submitted',
        ]);
    }

    public function test_submit_clears_session(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'phone' => '+225000000']);
        $project = $client->projects()->create(['project_type' => ['site_vitrine'], 'status' => 'draft']);

        $response = $this->withSession([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.submit'));

        $response->assertRedirect(route('questionnaire.thankyou'));

        $session = $this->app['session.store'];
        $this->assertNull($session->get('project_id'));
        $this->assertNull($session->get('client_id'));
        $this->assertNull($session->get('project_type'));
    }

    public function test_thankyou_page_loads(): void
    {
        $response = $this->get(route('questionnaire.thankyou'));

        $response->assertStatus(200);
    }
}
