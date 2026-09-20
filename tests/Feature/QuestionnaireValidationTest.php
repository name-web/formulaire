<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireValidationTest extends TestCase
{
    use RefreshDatabase;

    private function startDraft(): void
    {
        $this->post(route('questionnaire.start'), [
            'nom' => 'Jean Dupont',
            'tel' => '+2250708091011',
            'email' => 'jean@exemple.com',
        ]);
    }

    public function test_email_must_be_a_valid_address(): void
    {
        $this->post(route('questionnaire.start'), [
            'nom' => 'Jean Dupont',
            'tel' => '+2250708091011',
            'email' => 'pas-une-adresse',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_project_type_must_be_offered_by_the_form(): void
    {
        $this->startDraft();

        $this->post(route('questionnaire.step', 2), ['type' => 'type_inconnu'])
            ->assertSessionHasErrors('type');
    }

    public function test_module_must_belong_to_the_chosen_variant(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'logiciel']);

        $this->post(route('questionnaire.step', 3), [
            'modules' => ['module_inconnu'],
        ])->assertSessionHasErrors('modules.0');
    }

    public function test_module_from_another_variant_is_rejected(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'logiciel']);

        // « notes » est un module de la plateforme scolaire, pas du logiciel de gestion.
        $this->post(route('questionnaire.step', 3), [
            'modules' => ['notes'],
        ])->assertSessionHasErrors('modules.0');
    }

    public function test_pages_must_be_at_least_one(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);

        $this->post(route('questionnaire.step', 3), [
            'pages' => 0,
        ])->assertSessionHasErrors('pages');
    }

    public function test_level_must_be_one_of_the_three_levels(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);

        $this->post(route('questionnaire.step', 5), [
            'niveau' => 'ULTIME',
        ])->assertSessionHasErrors('niveau');
    }

    public function test_invalid_option_is_rejected(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);

        $this->post(route('questionnaire.step', 3), [
            'pages' => 5,
            'opt' => ['option_inconnue'],
        ])->assertSessionHasErrors('opt.0');
    }

    public function test_invalid_data_does_not_overwrite_stored_answers(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);
        $this->post(route('questionnaire.step', 3), ['pages' => 9, 'contenus' => 'fournis']);

        $this->post(route('questionnaire.step', 3), ['pages' => 0])
            ->assertSessionHasErrors('pages');

        $this->assertSame(9, PendingSubmission::first()->answers_data['pages']);
    }
}
