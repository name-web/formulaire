<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createPending(array $clientData = [], array $projectTypes = ['site_vitrine']): PendingSubmission
    {
        $defaults = [
            'name' => 'Jean Test',
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

    private function createSelectQuestion(array $attributes = []): Question
    {
        return Question::create(array_merge([
            'title' => 'Question select',
            'type' => 'select',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ], $attributes));
    }

    public function test_wizard_ignores_options_from_other_questions(): void
    {
        $pending = $this->createPending();

        $questionA = $this->createSelectQuestion(['title' => 'Q A', 'sort_order' => 1]);
        $optionA = $questionA->options()->create(['label' => 'Opt A', 'value' => 'opt_a', 'sort_order' => 1, 'is_active' => true]);

        $questionB = $this->createSelectQuestion(['title' => 'Q B', 'sort_order' => 2]);
        $optionB = $questionB->options()->create(['label' => 'Opt B', 'value' => 'opt_b', 'sort_order' => 1, 'is_active' => true]);

        $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.wizard.submit'), [
            'answers' => [$questionA->id => ['option_ids' => [$optionB->id]]],
        ])->assertRedirect(route('questionnaire.summary'));

        $pending->refresh();
        $this->assertArrayNotHasKey((string) $questionA->id, $pending->answers_data ?? []);

        $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.wizard.submit'), [
            'answers' => [$questionA->id => ['option_ids' => [$optionA->id]]],
        ])->assertRedirect(route('questionnaire.summary'));

        $pending->refresh();
        $this->assertArrayHasKey((string) $questionA->id, $pending->answers_data);
        $this->assertEquals([$optionA->id], $pending->answers_data[$questionA->id]['option_ids']);
    }

    public function test_wizard_rejects_overlong_value(): void
    {
        $pending = $this->createPending();

        $question = Question::create([
            'title' => 'Question texte',
            'type' => 'textarea',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.wizard.submit'), [
            'answers' => [$question->id => ['value' => str_repeat('a', 5001)]],
        ])->assertSessionHasErrors();

        $pending->refresh();
        $this->assertEmpty($pending->answers_data);
    }

    public function test_optional_select_with_autre_option_requires_precise_value(): void
    {
        $pending = $this->createPending();

        $question = $this->createSelectQuestion(['is_required' => false]);
        $autre = $question->options()->create(['label' => 'Autre', 'value' => 'autre', 'sort_order' => 1, 'is_active' => true]);

        $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$autre->id],
            'other_value' => '',
        ])->assertSessionHasErrors('other_value');

        $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$autre->id],
            'other_value' => 'Ma précision',
        ])->assertRedirect(route('questionnaire.summary'));

        $pending->refresh();
        $this->assertArrayHasKey((string) $question->id, $pending->answers_data);
        $this->assertEquals('Ma précision', $pending->answers_data[$question->id]['other_value']);
    }

    public function test_optional_select_accepts_empty_selection(): void
    {
        $pending = $this->createPending();

        $question = $this->createSelectQuestion(['is_required' => false]);
        $question->options()->create(['label' => 'Choix 1', 'value' => 'choix_1', 'sort_order' => 1, 'is_active' => true]);

        $this->withSession([
            'pending_submission_uuid' => $pending->uuid,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [''],
        ])->assertSessionHasNoErrors();
    }

    public function test_start_is_rate_limited(): void
    {
        $payload = [
            'name' => 'Jean Rate',
            'phone' => '+225000000',
            'project_types' => ['site_vitrine'],
        ];

        foreach (range(1, 5) as $i) {
            $payload['phone'] = "+22500000{$i}";
            $this->post(route('questionnaire.start'), $payload)->assertRedirect();
        }

        $payload['phone'] = '+225000006';
        $this->post(route('questionnaire.start'), $payload)->assertStatus(429);
    }
}
