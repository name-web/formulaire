<?php

namespace Tests\Feature;

use App\Models\Question;
use Database\Seeders\QuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserQuestionnaireFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(QuestionSeeder::class);
    }

    private function seedCountriesCache(): void
    {
        Http::fake([
            'countriesnow.space/api/v0.1/countries' => Http::response([
                'error' => false,
                'data' => [
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
                ],
            ]),
        ]);
    }

    public function test_index_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_questionnaire_index_page_loads(): void
    {
        $response = $this->get('/questionnaire');
        $response->assertStatus(200);
        $response->assertSee('questionnaire');
    }

    public function test_start_creates_session_and_redirects_to_wizard(): void
    {
        $this->seedCountriesCache();

        $response = $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'email' => 'jean@test.com',
            'phone' => '+22501020304',
            'whatsapp' => '',
            'company_name' => '',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        $response->assertRedirect(route('questionnaire.wizard'));
        $this->assertDatabaseHas('clients', ['email' => 'jean@test.com']);
        $this->assertDatabaseHas('projects', ['status' => 'draft']);
    }

    public function test_wizard_loads_after_start(): void
    {
        $this->seedCountriesCache();

        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'email' => 'jean@test.com',
            'phone' => '+22501020304',
            'whatsapp' => '',
            'company_name' => '',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        $response = $this->get('/questionnaire/wizard');
        $response->assertStatus(200);
    }

    public function test_step1_loads(): void
    {
        $this->seedCountriesCache();

        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'email' => 'jean@test.com',
            'phone' => '+22501020304',
            'whatsapp' => '',
            'company_name' => '',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        $response = $this->get('/questionnaire/step/1');
        $response->assertStatus(200);
    }

    public function test_wizard_submit_answers(): void
    {
        $this->seedCountriesCache();

        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'email' => 'jean@test.com',
            'phone' => '+22501020304',
            'whatsapp' => '',
            'company_name' => '',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        $questions = Question::orderBy('sort_order')->get();

        $answers = [];
        foreach ($questions as $q) {
            if ($q->type === 'yes_no') {
                $answers[$q->id] = ['value' => 'yes'];
            } elseif ($q->type === 'select') {
                $firstOption = $q->options()->first();
                if ($firstOption) {
                    $answers[$q->id] = ['option_ids' => [$firstOption->id]];
                }
            } elseif ($q->type === 'multiselect') {
                $firstOption = $q->options()->first();
                if ($firstOption) {
                    $answers[$q->id] = ['option_ids' => [$firstOption->id]];
                }
            } elseif ($q->type === 'file') {
                continue;
            } else {
                $answers[$q->id] = ['value' => 'Test answer'];
            }
        }

        $response = $this->post('/questionnaire/wizard', ['answers' => $answers]);
        $response->assertRedirect(route('questionnaire.summary'));
    }

    public function test_summary_loads(): void
    {
        $this->seedCountriesCache();

        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'email' => 'jean@test.com',
            'phone' => '+22501020304',
            'whatsapp' => '',
            'company_name' => '',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        $response = $this->get('/questionnaire/summary');
        $response->assertStatus(200);
    }

    public function test_full_flow_start_to_thankyou(): void
    {
        $this->seedCountriesCache();

        // Start
        $this->post('/questionnaire/start', [
            'name' => 'Jean Dupont',
            'email' => 'jean@test.com',
            'phone' => '+22501020304',
            'whatsapp' => '',
            'company_name' => '',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => ['site_vitrine'],
        ]);

        // Wizard page
        $this->get('/questionnaire/wizard')->assertStatus(200);

        // Submit all answers
        $questions = Question::orderBy('sort_order')->get();
        $answers = [];
        foreach ($questions as $q) {
            if ($q->type === 'file') {
                continue;
            }
            if ($q->type === 'yes_no') {
                $answers[$q->id] = ['value' => 'yes'];
            } elseif ($q->type === 'select') {
                $opt = $q->options()->first();
                if ($opt) {
                    $answers[$q->id] = ['option_ids' => [$opt->id]];
                }
            } elseif ($q->type === 'multiselect') {
                $opt = $q->options()->first();
                if ($opt) {
                    $answers[$q->id] = ['option_ids' => [$opt->id]];
                }
            } else {
                $answers[$q->id] = ['value' => 'Test answer'];
            }
        }
        $this->post('/questionnaire/wizard', ['answers' => $answers])
            ->assertRedirect(route('questionnaire.summary'));

        // Summary
        $this->get('/questionnaire/summary')->assertStatus(200);

        // Submit
        $this->post('/questionnaire/submit')
            ->assertRedirect(route('questionnaire.thankyou'));

        // Thank you
        $this->get('/questionnaire/thankyou')->assertStatus(200);

        // Project is now submitted
        $this->assertDatabaseHas('projects', [
            'status' => 'submitted',
        ]);
    }
}
