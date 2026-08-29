<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HomeStartFormTest extends TestCase
{
    use RefreshDatabase;

    private function seedCountries(): void
    {
        Http::fake([
            'countriesnow.space/api/v0.1/countries' => Http::response([
                'error' => false,
                'data' => [
                    ['iso2' => 'CI', 'iso3' => 'CIV', 'country' => "Côte d'Ivoire", 'cities' => ['Abidjan']],
                ],
            ]),
        ]);
    }

    private function formPayload(string $projectTypes): array
    {
        return [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+225 07070707',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'project_types' => $projectTypes,
        ];
    }

    public function test_questionnaire_index_preselects_real_types_without_autre(): void
    {
        $response = $this->get(route('questionnaire.index'));

        $response->assertStatus(200);
        $response->assertSee(
            '<input type="hidden" name="project_types" id="projectTypesInput" value="[&quot;site_vitrine&quot;,&quot;ecommerce&quot;,&quot;application_mobile&quot;,&quot;logiciel&quot;]">',
            false
        );
    }

    public function test_start_accepts_json_string_as_sent_by_home_form(): void
    {
        $this->seedCountries();

        $response = $this->post(route('questionnaire.start'), $this->formPayload('["site_vitrine","ecommerce","application_mobile","logiciel"]'));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }

    public function test_start_does_not_fail_after_previous_validation_error(): void
    {
        $this->seedCountries();

        // Premier essai avec un autre champ invalide → la session garde l'input brut.
        $payload = $this->formPayload('["site_vitrine"]');
        $payload['phone'] = '';
        $failed = $this->post(route('questionnaire.start'), $payload);
        $failed->assertSessionHasErrors('phone');

        // Réessai avec tout valide : old() étant une string JSON, elle ne doit pas
        // être re-encodée de façon boguée.
        $response = $this->post(route('questionnaire.start'), $this->formPayload('["site_vitrine"]'));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }
}
