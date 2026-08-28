<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Client;
use App\Models\Project;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuestionnaireRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the CountriesNow API cache so tests can submit country/city
     * without hitting the real external API.
     */
    private function seedCountriesCache(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with('countriesnow_all', 86400, \Mockery::type('callable'))
            ->andReturn([
                [
                    'iso2' => 'CI',
                    'iso3' => 'CIV',
                    'country' => "Côte d'Ivoire",
                    'cities' => ['Abidjan', 'Bouaké', 'Yamoussoukro'],
                ],
                [
                    'iso2' => 'FR',
                    'iso3' => 'FRA',
                    'country' => 'France',
                    'cities' => ['Paris', 'Marseille', 'Lyon'],
                ],
            ]);
    }

    public function test_questionnaire_index_page_loads(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertStatus(200);
    }

    public function test_start_creates_client_and_project(): void
    {
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
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
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine', 'ecommerce', 'application_mobile'],
        ]);

        $response->assertRedirect();

        $project = Project::first();
        $this->assertEquals(['site_vitrine', 'ecommerce', 'application_mobile'], $project->project_type);
    }

    public function test_start_requires_at_least_one_project_type(): void
    {
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => [],
        ]);

        $response->assertSessionHasErrors(['project_types']);
    }

    public function test_start_rejects_invalid_project_type(): void
    {
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['invalid_type'],
        ]);

        $response->assertSessionHasErrors(['project_types.0']);
    }

    public function test_start_requires_phone(): void
    {
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_start_requires_country_and_city(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertSessionHasErrors(['country', 'city']);
    }

    public function test_start_rejects_city_from_different_country(): void
    {
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Paris',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertSessionHasErrors(['city']);
    }

    public function test_start_requires_other_description_when_autre_selected(): void
    {
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine', 'autre'],
        ]);

        $response->assertSessionHasErrors(['other_description']);
    }

    public function test_start_accepts_other_description_when_autre_selected(): void
    {
        $this->seedCountriesCache();

        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine', 'autre'],
            'other_description' => 'Une application sur mesure',
        ]);

        $response->assertRedirect();

        $project = Project::first();
        $this->assertEquals(['site_vitrine', 'autre'], $project->project_type);
    }

    public function test_start_requires_name_and_email(): void
    {
        $response = $this->post(route('questionnaire.start'), []);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'country', 'city', 'project_types']);
    }

    public function test_step_page_loads_with_valid_session(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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

    public function test_countries_api_returns_list(): void
    {
        Http::fake([
            'countriesnow.space/api/v0.1/countries' => Http::response([
                'error' => false,
                'data' => [
                    ['iso2' => 'CI', 'iso3' => 'CIV', 'country' => "Côte d'Ivoire", 'cities' => ['Abidjan']],
                    ['iso2' => 'FR', 'iso3' => 'FRA', 'country' => 'France', 'cities' => ['Paris']],
                ],
            ]),
        ]);

        $response = $this->get(route('api.countries'));

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => "Côte d'Ivoire", 'code' => 'CI']);
        $response->assertJsonFragment(['name' => 'France', 'code' => 'FR']);
    }

    public function test_cities_api_returns_cities_for_country(): void
    {
        Http::fake([
            'countriesnow.space/api/v0.1/countries' => Http::response([
                'error' => false,
                'data' => [
                    ['iso2' => 'CI', 'iso3' => 'CIV', 'country' => "Côte d'Ivoire", 'cities' => ['Abidjan', 'Bouaké']],
                    ['iso2' => 'FR', 'iso3' => 'FRA', 'country' => 'France', 'cities' => ['Paris']],
                ],
            ]),
        ]);

        $response = $this->get(route('api.cities', 'CI'));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'cities');
        $response->assertJsonFragment(['cities' => ['Abidjan', 'Bouaké']]);
    }

    public function test_cities_api_returns_404_for_unknown_country(): void
    {
        Http::fake([
            'countriesnow.space/api/v0.1/countries' => Http::response([
                'error' => false,
                'data' => [],
            ]),
        ]);

        $response = $this->get(route('api.cities', 'XX'));

        $response->assertStatus(404);
    }
}
