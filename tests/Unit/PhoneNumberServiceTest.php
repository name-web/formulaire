<?php

namespace Tests\Unit;

use App\Services\Phone\PhoneNumberService;
use Tests\TestCase;

class PhoneNumberServiceTest extends TestCase
{
    private PhoneNumberService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PhoneNumberService;
    }

    public function test_normalizes_ci_number_to_e164(): void
    {
        $this->assertSame('+2250708091011', $this->service->normalizeToE164('+2250708091011', 'CI'));
    }

    public function test_normalizes_fr_number_to_e164(): void
    {
        $this->assertSame('+33612345678', $this->service->normalizeToE164('+33612345678', 'FR'));
    }

    public function test_normalizes_us_number_to_e164(): void
    {
        $this->assertSame('+14155552671', $this->service->normalizeToE164('+14155552671', 'US'));
    }

    public function test_normalizes_uk_number_to_e164(): void
    {
        $this->assertSame('+447911123456', $this->service->normalizeToE164('+447911123456', 'GB'));
    }

    public function test_normalizes_local_format_with_selected_country(): void
    {
        $this->assertSame('+2250708091011', $this->service->normalizeToE164('0708091011', 'CI'));
    }

    public function test_returns_null_for_invalid_number(): void
    {
        $this->assertNull($this->service->normalizeToE164('12345', 'CI'));
    }

    public function test_returns_null_for_arbitrary_text(): void
    {
        $this->assertNull($this->service->normalizeToE164('abc', 'CI'));
    }

    public function test_returns_null_for_unknown_dial_code(): void
    {
        $this->assertNull($this->service->normalizeToE164('+999123456', 'CI'));
    }

    public function test_returns_null_for_number_incompatible_with_selected_country(): void
    {
        $this->assertNull($this->service->normalizeToE164('+33612345678', 'CI'));
    }

    public function test_returns_null_for_unsupported_region(): void
    {
        $this->assertNull($this->service->normalizeToE164('+2250708091011', 'ZZ'));
    }

    public function test_countries_list_contains_french_names_and_dial_codes(): void
    {
        $countries = $this->service->countries();

        $this->assertArrayHasKey('CI', $countries);
        $this->assertSame(225, $countries['CI']['dial_code']);
        $this->assertStringContainsString('Ivoire', $countries['CI']['name']);
        $this->assertSame(33, $countries['FR']['dial_code']);
        $this->assertSame('France', $countries['FR']['name']);

        // Sorted by French name (same comparator as the service).
        $names = array_column($countries, 'name');
        $sorted = $names;
        usort($sorted, fn (string $a, string $b): int => strcmp(strtolower($a), strtolower($b)));
        $this->assertSame($sorted, $names);
    }
}
