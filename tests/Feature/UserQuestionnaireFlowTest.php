<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PendingSubmission;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserQuestionnaireFlowTest extends TestCase
{
    use RefreshDatabase;

    private function completeJourney(): void
    {
        $this->post(route('questionnaire.start'), [
            'nom' => 'Awa Traoré',
            'entreprise' => 'Boutique Awa',
            'ville' => 'Bouaké',
            'tel' => '+2250708091011',
            'email' => 'awa@exemple.com',
        ]);

        $this->post(route('questionnaire.step', 2), ['type' => 'ecommerce']);
        $this->post(route('questionnaire.step', 3), [
            'produits' => 40,
            'pages' => 4,
            'paiement' => 'mobile',
            'identite' => 'logo',
            'opt' => ['notifs'],
        ]);
        $this->post(route('questionnaire.step', 4), [
            'domaine' => 'ci',
            'hebergement' => 'mutualise',
            'emails' => 2,
            'maintenance' => 'essentiel',
            'opt' => ['sauvegarde'],
        ]);
        $this->post(route('questionnaire.step', 5), [
            'niveau' => 'STANDARD',
            'delai' => 'normal',
            'message' => 'Boutique de tissus à Bouaké.',
        ]);
    }

    public function test_user_completes_the_journey_and_gets_a_provisional_quote(): void
    {
        $this->completeJourney();

        $this->get(route('questionnaire.quote'))
            ->assertOk()
            ->assertSee('DEVIS PROVISOIRE')
            ->assertSee("Création d'une boutique en ligne avec paiement mobile")
            ->assertSee('TOTAL À PAYER TTC');

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseCount('quotes', 1);

        $project = Project::first();

        $this->assertSame(['ecommerce'], $project->project_type);
        $this->assertTrue($project->usesCurrentForm());
        $this->assertSame(40, $project->form_data['produits']);
    }

    public function test_user_moves_forward_step_by_step(): void
    {
        $this->post(route('questionnaire.start'), [
            'nom' => 'Awa Traoré',
            'tel' => '+2250708091011',
            'email' => 'awa@exemple.com',
        ])->assertRedirect(route('questionnaire.step', 2));

        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine'])
            ->assertRedirect(route('questionnaire.step', 3));

        $this->post(route('questionnaire.step', 3), ['pages' => 5, 'contenus' => 'fournis'])
            ->assertRedirect(route('questionnaire.step', 4));

        $this->post(route('questionnaire.step', 4), ['domaine' => 'com', 'hebergement' => 'mutualise'])
            ->assertRedirect(route('questionnaire.step', 5));

        $this->post(route('questionnaire.step', 5), ['niveau' => 'PREMIUM', 'delai' => 'accelere'])
            ->assertRedirect(route('questionnaire.quote'));
    }

    /**
     * Revenir en arrière réaffiche les réponses déjà données.
     */
    public function test_going_back_keeps_the_answers_already_given(): void
    {
        $this->post(route('questionnaire.start'), [
            'nom' => 'Awa Traoré',
            'tel' => '+2250708091011',
            'email' => 'awa@exemple.com',
        ]);
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);
        $this->post(route('questionnaire.step', 3), [
            'pages' => 7,
            'contenus' => 'rediger',
            'opt' => ['blog'],
        ]);

        $this->get(route('questionnaire.step', 3))
            ->assertOk()
            ->assertSee('value="7"', false);

        // Le type choisi reste coché sur l'étape précédente.
        $response = $this->get(route('questionnaire.step', 2))->assertOk();

        $this->assertMatchesRegularExpression(
            '/value="site_vitrine"[\s\S]{0,90}?checked/',
            $response->getContent(),
            'Le type de projet sélectionné doit rester coché.'
        );
    }

    public function test_journey_is_not_converted_before_the_last_step(): void
    {
        $this->post(route('questionnaire.start'), [
            'nom' => 'Awa Traoré',
            'tel' => '+2250708091011',
            'email' => 'awa@exemple.com',
        ]);
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('quotes', 0);
        $this->assertSame('collecting', PendingSubmission::first()->status);
    }

    public function test_submitted_journey_is_marked_as_imported(): void
    {
        $this->completeJourney();

        $this->assertSame('imported', PendingSubmission::first()->status);

        $client = Client::first();
        $this->assertSame('Awa Traoré', $client->name);
        $this->assertSame('awa@exemple.com', $client->email);

        $quote = Quote::first();
        $this->assertSame($client->id, $quote->client_id);
        $this->assertTrue($quote->isFromForm());
    }
}
