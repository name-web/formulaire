<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeStartFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_index_loads_with_project_types(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertStatus(200);
        $response->assertSee('project_types');
    }

    public function test_start_accepts_project_type_string(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'project_types' => 'site_vitrine',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }

    public function test_start_accepts_project_type_json_string(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'name' => 'Jean Dupont',
            'phone' => '+2250708091011',
            'project_types' => '["site_vitrine","ecommerce"]',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }
}
