<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PendingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    private function startPayload(string $phone, string $country = 'CI'): array
    {
        return [
            'name' => 'Jean Dupont',
            'phone' => $phone,
            'phone_country' => $country,
            'project_types' => ['site_vitrine'],
        ];
    }

    private function startAndGetPending(string $phone, string $country = 'CI'): PendingSubmission
    {
        $this->post('/questionnaire/start', $this->startPayload($phone, $country))
            ->assertRedirect(route('questionnaire.wizard'));

        return PendingSubmission::firstOrFail();
    }

    public function test_start_accepts_ci_number_and_normalizes_to_e164(): void
    {
        $pending = $this->startAndGetPending('+2250708091011', 'CI');

        $this->assertSame('+2250708091011', $pending->client_data['phone']);
    }

    public function test_start_accepts_fr_number_and_normalizes_to_e164(): void
    {
        $pending = $this->startAndGetPending('+33612345678', 'FR');

        $this->assertSame('+33612345678', $pending->client_data['phone']);
    }

    public function test_start_accepts_us_number_and_normalizes_to_e164(): void
    {
        $pending = $this->startAndGetPending('+14155552671', 'US');

        $this->assertSame('+14155552671', $pending->client_data['phone']);
    }

    public function test_start_accepts_uk_number_and_normalizes_to_e164(): void
    {
        $pending = $this->startAndGetPending('+447911123456', 'GB');

        $this->assertSame('+447911123456', $pending->client_data['phone']);
    }

    public function test_start_accepts_local_format_with_selected_country(): void
    {
        $pending = $this->startAndGetPending('0708091011', 'CI');

        $this->assertSame('+2250708091011', $pending->client_data['phone']);
    }

    public function test_start_accepts_local_format_with_country_selected_but_typed_dial_code(): void
    {
        $pending = $this->startAndGetPending('+225 07 08 09 10 11', 'CI');

        $this->assertSame('+2250708091011', $pending->client_data['phone']);
    }

    public function test_start_rejects_invalid_number(): void
    {
        $this->post('/questionnaire/start', $this->startPayload('12345', 'CI'))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_start_rejects_arbitrary_text(): void
    {
        $this->post('/questionnaire/start', $this->startPayload('abc', 'CI'))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_start_rejects_unknown_dial_code(): void
    {
        $this->post('/questionnaire/start', $this->startPayload('+999123456', 'CI'))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_start_rejects_number_incompatible_with_selected_country(): void
    {
        $this->post('/questionnaire/start', $this->startPayload('+33612345678', 'CI'))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_start_rejects_unsupported_country_code(): void
    {
        $this->post('/questionnaire/start', $this->startPayload('+2250708091011', 'ZZ'))
            ->assertSessionHasErrors('phone_country');

        $this->assertDatabaseCount('pending_submissions', 0);
    }

    public function test_start_does_not_store_selected_country_in_client_data(): void
    {
        $pending = $this->startAndGetPending('0708091011', 'CI');

        $this->assertArrayNotHasKey('phone_country', $pending->client_data);
    }

    public function test_two_representations_of_same_number_deduplicate(): void
    {
        // Local format.
        $first = $this->startAndGetPending('0708091011', 'CI');
        session(['pending_submission_uuid' => $first->uuid]);
        $this->post('/questionnaire/submit')->assertRedirect(route('questionnaire.thankyou'));

        // Same number in full international format.
        $second = $this->startAndGetPending('+2250708091011', 'CI');
        session(['pending_submission_uuid' => $second->uuid]);
        $this->post('/questionnaire/submit')->assertRedirect(route('questionnaire.thankyou'));

        $this->assertDatabaseCount('clients', 1);
        $this->assertSame(1, Client::where('phone', '+2250708091011')->count());
    }
}
