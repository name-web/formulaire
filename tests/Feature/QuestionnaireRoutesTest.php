<?php

namespace Tests\Feature;

use App\Models\PendingSubmission;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireRoutesTest extends TestCase
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

    public function test_start_creates_pending_submission(): void
    {
        $this->startDraft();

        $pending = PendingSubmission::first();

        $this->assertNotNull($pending);
        $this->assertSame('collecting', $pending->status);
        $this->assertSame(Project::FORM_VERSION_CURRENT, $pending->project_data['form_version']);
    }

    public function test_step_page_redirects_without_a_draft(): void
    {
        $this->get(route('questionnaire.step', 2))
            ->assertRedirect(route('questionnaire.index'));
    }

    public function test_out_of_range_step_redirects_to_the_start(): void
    {
        $this->startDraft();

        $this->get(route('questionnaire.step', 9))
            ->assertRedirect(route('questionnaire.index'));
    }

    public function test_detail_step_redirects_to_the_project_step_without_a_type(): void
    {
        $this->startDraft();

        $this->get(route('questionnaire.step', 3))
            ->assertRedirect(route('questionnaire.step', 2));
    }

    public function test_project_type_choices_are_displayed(): void
    {
        $this->startDraft();

        $this->get(route('questionnaire.step', 2))
            ->assertOk()
            ->assertSee('De quoi avez-vous besoin ?')
            ->assertSee('value="site_vitrine"', false)
            ->assertSee('value="ecommerce"', false)
            ->assertSee('value="logiciel"', false)
            ->assertSee('value="scolaire"', false)
            ->assertSee('value="application_mobile"', false)
            ->assertSee('value="refonte"', false);
    }

    /**
     * Le numéro d'étape du parcours ne doit pas être confondu avec le pas
     * (attribut HTML « step ») des champs numériques : un « step » erroné
     * bloquerait la validation du navigateur et empêcherait l'envoi.
     */
    public function test_number_fields_do_not_inherit_the_current_step_attribute(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);

        $response = $this->get(route('questionnaire.step', 3));

        $response->assertOk()->assertSee('id="pages"', false);

        $this->assertDoesNotMatchRegularExpression(
            '/id="pages"[^>]*\sstep="/',
            $response->getContent(),
        );
    }

    public function test_choosing_a_project_type_is_stored_in_the_draft(): void
    {
        $this->startDraft();

        $this->post(route('questionnaire.step', 2), ['type' => 'ecommerce'])
            ->assertRedirect(route('questionnaire.step', 3));

        $pending = PendingSubmission::first();

        $this->assertSame('ecommerce', $pending->answers_data['type']);
        $this->assertSame(['ecommerce'], $pending->project_data['project_types']);
    }

    public function test_detail_answers_are_stored_in_the_draft(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);

        $this->post(route('questionnaire.step', 3), [
            'pages' => 7,
            'identite' => 'logo',
            'contenus' => 'rediger',
            'opt' => ['blog', 'galerie'],
        ])->assertRedirect(route('questionnaire.step', 4));

        $answers = PendingSubmission::first()->answers_data;

        $this->assertSame(7, $answers['pages']);
        $this->assertSame('logo', $answers['identite']);
        $this->assertSame('rediger', $answers['contenus']);
        $this->assertSame(['blog', 'galerie'], $answers['opt']);
    }

    public function test_hosting_step_keeps_type_options_and_adds_the_backup_option(): void
    {
        $this->startDraft();
        $this->post(route('questionnaire.step', 2), ['type' => 'site_vitrine']);
        $this->post(route('questionnaire.step', 3), [
            'pages' => 5,
            'contenus' => 'fournis',
            'opt' => ['blog'],
        ]);

        $this->post(route('questionnaire.step', 4), [
            'domaine' => 'ci',
            'hebergement' => 'vps',
            'emails' => 2,
            'maintenance' => 'business',
            'opt' => ['sauvegarde'],
        ])->assertRedirect(route('questionnaire.step', 5));

        $answers = PendingSubmission::first()->answers_data;

        $this->assertSame('ci', $answers['domaine']);
        $this->assertSame('vps', $answers['hebergement']);
        $this->assertSame(2, $answers['emails']);
        $this->assertSame('business', $answers['maintenance']);

        // Les options du type sont conservées, la sauvegarde s'y ajoute.
        $this->assertContains('blog', $answers['opt']);
        $this->assertContains('sauvegarde', $answers['opt']);
    }

    public function test_quote_page_redirects_without_a_submitted_quote(): void
    {
        $this->get(route('questionnaire.quote'))
            ->assertRedirect(route('questionnaire.index'));
    }

    public function test_restart_clears_the_current_journey(): void
    {
        $this->startDraft();

        $this->get(route('questionnaire.restart'))
            ->assertRedirect(route('questionnaire.index'));

        $this->assertNull(session('devis_pending_uuid'));

        // Le brouillon quitté n'est pas converti.
        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('projects', 0);
    }
}
