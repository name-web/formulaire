<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Client;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\QuestionOption;
use App\Services\Questionnaire\ConditionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConditionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConditionService;
    }

    public function test_question_without_conditions_is_always_visible(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $question = Question::create([
            'title' => 'Test Question',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $this->assertTrue($this->service->isVisible($question, $project->id));
    }

    public function test_equals_condition_with_option_value(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $sourceQuestion = Question::create([
            'title' => 'Source',
            'type' => 'select',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $option = QuestionOption::create([
            'question_id' => $sourceQuestion->id,
            'label' => 'Vitrine',
            'value' => 'vitrine',
            'sort_order' => 1,
        ]);

        $targetQuestion = Question::create([
            'title' => 'Target',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 2,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion->id,
            'operator' => 'equals',
            'option_value_id' => $option->id,
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $sourceQuestion->id,
        ])->answerOptions()->create([
            'question_option_id' => $option->id,
        ]);

        $this->assertTrue($this->service->isVisible($targetQuestion, $project->id));
    }

    public function test_equals_condition_with_text_value(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $sourceQuestion = Question::create([
            'title' => 'Source',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $targetQuestion = Question::create([
            'title' => 'Target',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 2,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion->id,
            'operator' => 'equals',
            'text_value' => 'yes',
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $sourceQuestion->id,
            'text_value' => 'yes',
        ]);

        $this->assertTrue($this->service->isVisible($targetQuestion, $project->id));
    }

    public function test_not_equals_condition(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $sourceQuestion = Question::create([
            'title' => 'Source',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $targetQuestion = Question::create([
            'title' => 'Target',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 2,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion->id,
            'operator' => 'not_equals',
            'text_value' => 'yes',
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $sourceQuestion->id,
            'text_value' => 'no',
        ]);

        $this->assertTrue($this->service->isVisible($targetQuestion, $project->id));
    }

    public function test_contains_condition(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $sourceQuestion = Question::create([
            'title' => 'Source',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $targetQuestion = Question::create([
            'title' => 'Target',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 2,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion->id,
            'operator' => 'contains',
            'text_value' => 'Abidjan',
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $sourceQuestion->id,
            'text_value' => 'Je suis à Abidjan',
        ]);

        $this->assertTrue($this->service->isVisible($targetQuestion, $project->id));
    }

    public function test_not_contains_condition(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $sourceQuestion = Question::create([
            'title' => 'Source',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $targetQuestion = Question::create([
            'title' => 'Target',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 2,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion->id,
            'operator' => 'not_contains',
            'text_value' => 'Paris',
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $sourceQuestion->id,
            'text_value' => 'Je suis à Abidjan',
        ]);

        $this->assertTrue($this->service->isVisible($targetQuestion, $project->id));
    }

    public function test_multiple_conditions_in_and(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $sourceQuestion1 = Question::create([
            'title' => 'Source 1',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $sourceQuestion2 = Question::create([
            'title' => 'Source 2',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 2,
        ]);

        $targetQuestion = Question::create([
            'title' => 'Target',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 3,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion1->id,
            'operator' => 'equals',
            'text_value' => 'yes',
            'sort_order' => 1,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion2->id,
            'operator' => 'contains',
            'text_value' => 'test',
            'sort_order' => 2,
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $sourceQuestion1->id,
            'text_value' => 'yes',
        ]);

        Answer::create([
            'project_id' => $project->id,
            'question_id' => $sourceQuestion2->id,
            'text_value' => 'Ceci est un test',
        ]);

        $this->assertTrue($this->service->isVisible($targetQuestion, $project->id));
    }

    public function test_condition_fails_when_answer_missing(): void
    {
        $project = Client::create(['name' => 'Test', 'email' => 'test@test.com'])
            ->projects()->create(['project_type' => 'site_vitrine', 'status' => 'draft']);

        $sourceQuestion = Question::create([
            'title' => 'Source',
            'type' => 'yes_no',
            'project_types' => ['site_vitrine'],
            'sort_order' => 1,
        ]);

        $targetQuestion = Question::create([
            'title' => 'Target',
            'type' => 'text',
            'project_types' => ['site_vitrine'],
            'sort_order' => 2,
        ]);

        QuestionCondition::create([
            'question_id' => $targetQuestion->id,
            'depends_on_question_id' => $sourceQuestion->id,
            'operator' => 'equals',
            'text_value' => 'yes',
        ]);

        $this->assertFalse($this->service->isVisible($targetQuestion, $project->id));
    }
}
