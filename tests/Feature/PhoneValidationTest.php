<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PendingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Le formulaire ne demande plus de pays : la Côte d'Ivoire fait office de
     * pays par défaut pour l'interprétation des numéros locaux.
     */
    private function startWithPhone(string $phone): TestResponse
    {
        return $this->post(route('questionnaire.start'), [
            'nom' => 'Jean Dupont',
            'tel' => $phone,
            'email' => 'jean@exemple.com',
        ]);
    }

    public function test_start_accepts_ci_number_and_normalizes_to_e164(): void
    {
        $this->startWithPhone('+2250708091011')->assertSessionHasNoErrors();

        $this->assertSame('+2250708091011', PendingSubmission::firstOrFail()->client_data['phone']);
    }

    public function test_start_normalizes_a_local_ivorian_number(): void
    {
        $this->startWithPhone('0708091011')->assertSessionHasNoErrors();

        $this->assertSame('+2250708091011', PendingSubmission::firstOrFail()->client_data['phone']);
    }

    public function test_start_normalizes_a_number_typed_with_separators(): void
    {
        $this->startWithPhone('+225 07 08 09 10 11')->assertSessionHasNoErrors();

        $this->assertSame('+2250708091011', PendingSubmission::firstOrFail()->client_data['phone']);
    }

    public function test_start_accepts_an_international_number(): void
    {
        $this->startWithPhone('+33612345678')->assertSessionHasNoErrors();

        $this->assertSame('+33612345678', PendingSubmission::firstOrFail()->client_data['phone']);
    }

    public function test_start_rejects_an_invalid_number(): void
    {
        $this->startWithPhone('12345')->assertSessionHasErrors('tel');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_start_rejects_arbitrary_text(): void
    {
        $this->startWithPhone('pas un numéro')->assertSessionHasErrors('tel');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    /**
     * Deux écritures du même numéro ne créent qu'une seule fiche prospect.
     */
    public function test_two_representations_of_the_same_number_deduplicate_on_submission(): void
    {
        $this->startWithPhone('0708091011');
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);
        $this->post(route('questionnaire.step', 3), ['pages' => 5, 'contenus' => 'fournis']);
        $this->post(route('questionnaire.step', 4), ['domaine' => 'aucun', 'hebergement' => 'aucun', 'emails' => 0, 'maintenance' => 'aucune']);
        $this->post(route('questionnaire.step', 5), ['niveau' => 'STANDARD', 'delai' => 'normal']);

        // Deuxième parcours, même personne, autre écriture du numéro.
        $this->post(route('questionnaire.restart'));
        $this->startWithPhone('+225 07 08 09 10 11');
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);
        $this->post(route('questionnaire.step', 3), ['pages' => 5, 'contenus' => 'fournis']);
        $this->post(route('questionnaire.step', 4), ['domaine' => 'aucun', 'hebergement' => 'aucun', 'emails' => 0, 'maintenance' => 'aucune']);
        $this->post(route('questionnaire.step', 5), ['niveau' => 'STANDARD', 'delai' => 'normal']);

        $this->assertDatabaseCount('clients', 1);
        $this->assertSame('+2250708091011', Client::first()->phone);
    }
}
