<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function createPending(array $clientData = [], array $projectTypes = ['site_vitrine']): PendingSubmission
    {
        $defaults = [
            'name' => 'Test',
            'email' => 'test@test.com',
            'phone' => '+225000000',
        ];

        return PendingSubmission::create([
            'client_data' => array_merge($defaults, $clientData),
            'project_data' => ['project_types' => $projectTypes],
            'answers_data' => [],
            'files_data' => [],
            'status' => 'collecting',
        ]);
    }

    public function test_questionnaire_index_page_loads(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertStatus(200);
    }

    public function test_start_creates_pending_submission(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+225 07070707',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pending_submissions', [
            'status' => 'collecting',
        ]);

        $pending = PendingSubmission::first();
        $this->assertEquals('Jean Dupont', $pending->client_data['name']);
        $this->assertEquals('+225 07070707', $pending->client_data['phone']);
        $this->assertEquals(['site_vitrine'], $pending->project_data['project_types']);
    }

    public function test_start_accepts_multiple_project_types(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+225 07070707',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile'],
        ]);

        $response->assertRedirect();

        $pending = PendingSubmission::first();
        $this->assertEquals(['site_vitrine', 'ecommerce', 'application_mobile'], $pending->project_data['project_types']);
    }

    public function test_project_type_site_vitrine_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test Vitrine',
            'phone' => '+22501020304',
            'project_types' => ['site_vitrine'],
        ]);

        $pending = PendingSubmission::first();
        $this->assertEquals(['site_vitrine'], $pending->project_data['project_types']);
    }

    public function test_project_type_ecommerce_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test E-commerce',
            'phone' => '+22501020304',
            'project_types' => ['ecommerce'],
        ]);

        $pending = PendingSubmission::first();
        $this->assertEquals(['ecommerce'], $pending->project_data['project_types']);
    }

    public function test_project_type_application_mobile_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test Mobile',
            'phone' => '+22501020304',
            'project_types' => ['application_mobile'],
        ]);

        $pending = PendingSubmission::first();
        $this->assertEquals(['application_mobile'], $pending->project_data['project_types']);
    }

    public function test_project_type_logiciel_stored_correctly(): void
    {
        $this->post(route('questionnaire.start'), [
            'name' => 'Test Logiciel',
            'phone' => '+22501020304',
            'project_types' => ['logiciel'],
        ]);

        $pending = PendingSubmission::first();
        $this->assertEquals(['logiciel'], $pending->project_data['project_types']);
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
        $pending = $this->createPending();

        Question::create([
            'title' => 'Quel type de projet ?',
            'type' => 'select',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $response = $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->get(route('questionnaire.step', 1));

        $response->assertStatus(200);
    }

    public function test_step_page_loads_with_multiple_project_types(): void
    {
        $pending = $this->createPending(projectTypes: ['site_vitrine', 'ecommerce']);

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
            'pending_submission_uuid' => $pending->uuid,
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
        $pending = $this->createPending();

        $question = Question::create([
            'title' => 'Décrivez votre projet',
            'type' => 'textarea',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $response = $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'value' => 'Je veux un site vitrine',
        ]);

        $response->assertRedirect();

        $pending->refresh();
        $this->assertEquals('Je veux un site vitrine', $pending->answers_data[$question->id]['text_value']);
    }

    public function test_store_answer_for_select_question(): void
    {
        $pending = $this->createPending();

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
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$option->id],
        ]);

        $response->assertRedirect();

        $pending->refresh();
        $this->assertEquals([$option->id], $pending->answers_data[$question->id]['option_ids']);
    }

    public function test_store_answer_for_multiselect_with_other(): void
    {
        $pending = $this->createPending();

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
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$option1->id, $optionOther->id],
            'other_value' => 'Page portfolio',
        ]);

        $response->assertRedirect();

        $pending->refresh();
        $this->assertCount(2, $pending->answers_data[$question->id]['option_ids']);
        $this->assertEquals('Page portfolio', $pending->answers_data[$question->id]['other_value']);
    }

    public function test_previous_route_redirects_correctly(): void
    {
        $pending = $this->createPending();

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

        $pending->update(['answers_data' => [
            $question1->id => [
                'question_id' => $question1->id,
                'text_value' => 'Réponse 1',
            ],
        ]]);

        $response = $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->get(route('questionnaire.previous'));

        $response->assertRedirect();
    }

    public function test_summary_page_loads(): void
    {
        $pending = $this->createPending();

        $question = Question::create([
            'title' => 'Question 1',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $pending->update(['answers_data' => [
            $question->id => [
                'question_id' => $question->id,
                'text_value' => 'Réponse test',
            ],
        ]]);

        $response = $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->get(route('questionnaire.summary'));

        $response->assertStatus(200);
    }

    public function test_submit_changes_pending_status(): void
    {
        $pending = $this->createPending();

        $response = $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.submit'));

        $response->assertRedirect(route('questionnaire.thankyou'));

        $pending->refresh();
        $this->assertNotEquals('collecting', $pending->status);
    }

    public function test_submit_clears_session(): void
    {
        $pending = $this->createPending();

        $response = $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.submit'));

        $response->assertRedirect(route('questionnaire.thankyou'));

        $session = $this->app['session.store'];
        $this->assertNull($session->get('pending_submission_uuid'));
        $this->assertNull($session->get('project_type'));
    }

    public function test_thankyou_page_loads(): void
    {
        $response = $this->get(route('questionnaire.thankyou'));

        $response->assertStatus(200);
    }
}
