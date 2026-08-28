<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuestionnaireValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createProject(): Project
    {
        $client = Client::create([
            'name' => 'Jean Test',
            'email' => 'jean@test.com',
            'phone' => '+225000000',
            'country' => 'CI',
            'city' => 'Abidjan',
        ]);

        return $client->projects()->create([
            'project_type' => ['site_vitrine'],
            'status' => 'draft',
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
        $project = $this->createProject();

        $questionA = $this->createSelectQuestion(['title' => 'Q A', 'sort_order' => 1]);
        $optionA = $questionA->options()->create(['label' => 'Opt A', 'value' => 'opt_a', 'sort_order' => 1, 'is_active' => true]);

        $questionB = $this->createSelectQuestion(['title' => 'Q B', 'sort_order' => 2]);
        $optionB = $questionB->options()->create(['label' => 'Opt B', 'value' => 'opt_b', 'sort_order' => 1, 'is_active' => true]);

        $this->withSession([
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.wizard.submit'), [
            'answers' => [$questionA->id => ['option_ids' => [$optionB->id]]],
        ])->assertRedirect(route('questionnaire.summary'));

        // L'option appartient à une autre question : aucune réponse ne doit être enregistrée
        $this->assertDatabaseMissing('answers', [
            'project_id' => $project->id,
            'question_id' => $questionA->id,
        ]);

        $this->assertDatabaseMissing('answer_options', [
            'question_option_id' => $optionB->id,
        ]);

        // Sanity check : une option valide est bien acceptée
        $this->withSession([
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.wizard.submit'), [
            'answers' => [$questionA->id => ['option_ids' => [$optionA->id]]],
        ])->assertRedirect(route('questionnaire.summary'));

        $this->assertDatabaseHas('answers', [
            'project_id' => $project->id,
            'question_id' => $questionA->id,
        ]);
    }

    public function test_wizard_rejects_overlong_value(): void
    {
        $project = $this->createProject();

        $question = Question::create([
            'title' => 'Question texte',
            'type' => 'textarea',
            'project_types' => ['site_vitrine'],
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->withSession([
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.wizard.submit'), [
            'answers' => [$question->id => ['value' => str_repeat('a', 5001)]],
        ])->assertSessionHasErrors();

        $this->assertDatabaseCount('answers', 0);
    }

    public function test_optional_select_with_autre_option_requires_precise_value(): void
    {
        $project = $this->createProject();

        $question = $this->createSelectQuestion(['is_required' => false]);
        $autre = $question->options()->create(['label' => 'Autre', 'value' => 'autre', 'sort_order' => 1, 'is_active' => true]);

        // "Autre" sélectionné sans précision → la règle Closure doit être appliquée
        $this->withSession([
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$autre->id],
            'other_value' => '',
        ])->assertSessionHasErrors('other_value');

        // Avec précision → la réponse est enregistrée
        $this->withSession([
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [$autre->id],
            'other_value' => 'Ma précision',
        ])->assertRedirect(route('questionnaire.summary'));

        $this->assertDatabaseHas('answers', [
            'project_id' => $project->id,
            'question_id' => $question->id,
            'other_value' => 'Ma précision',
        ]);
    }

    public function test_optional_select_accepts_empty_selection(): void
    {
        $project = $this->createProject();

        $question = $this->createSelectQuestion(['is_required' => false]);
        $question->options()->create(['label' => 'Choix 1', 'value' => 'choix_1', 'sort_order' => 1, 'is_active' => true]);

        // L'option vide "" du menu déroulant ne doit pas provoquer d'erreur de validation
        $this->withSession([
            'project_id' => $project->id,
            'project_type' => ['site_vitrine'],
        ])->post(route('questionnaire.step.store', 1), [
            'option_ids' => [''],
        ])->assertSessionHasNoErrors();
    }

    public function test_start_is_rate_limited(): void
    {
        Http::fake([
            'countriesnow.space/*' => Http::response([
                'error' => false,
                'data' => [
                    ['iso2' => 'CI', 'iso3' => 'CIV', 'country' => "Côte d'Ivoire", 'cities' => ['Abidjan']],
                ],
            ]),
        ]);

        $payload = [
            'name' => 'Jean Rate',
            'email' => 'rate1@test.com',
            'phone' => '+225000000',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ];

        foreach (range(1, 5) as $i) {
            $payload['email'] = "rate{$i}@test.com";
            $this->post(route('questionnaire.start'), $payload)->assertRedirect();
        }

        $payload['email'] = 'rate6@test.com';
        $this->post(route('questionnaire.start'), $payload)->assertStatus(429);
    }
}
