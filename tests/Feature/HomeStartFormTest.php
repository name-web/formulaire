<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeStartFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_index_loads_with_the_new_form(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertOk();
        $response->assertSee('Recevez votre devis en 2 minutes');
        $response->assertSee('name="nom"', false);
        $response->assertSee('name="email"', false);
    }

    /**
     * L'ancien formulaire soumettait un champ caché `project_types` : le
     * nouveau parcours le remplace par une étape dédiée.
     */
    public function test_index_no_longer_exposes_the_legacy_project_types_field(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertOk();
        $response->assertDontSee('name="project_types"', false);
    }

    public function test_start_accepts_coordinator_data(): void
    {
        $response = $this->post(route('questionnaire.start'), [
            'nom' => 'Jean Dupont',
            'tel' => '+2250708091011',
            'email' => 'jean@exemple.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('questionnaire.step', 2));
    }
}
