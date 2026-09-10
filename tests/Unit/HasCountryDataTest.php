<?php

namespace Tests\Unit;

use App\Traits\HasCountryData;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HasCountryDataTest extends TestCase
{
    public function test_ssl_options_never_disable_verification_when_no_ca_bundle_is_found(): void
    {
        config(['services.countriesnow.ca_bundle' => null]);

        $traitUser = new class
        {
            use HasCountryData;

            protected function findCaBundle(): ?string
            {
                return null;
            }

            public function exposedSslOptions(): array
            {
                return $this->sslOptions();
            }
        };

        // Regression test: the previous implementation returned
        // ['verify' => false] here, silently disabling TLS verification.
        $this->assertSame([], $traitUser->exposedSslOptions());
        $this->assertArrayNotHasKey('verify', $traitUser->exposedSslOptions());
    }

    public function test_ssl_options_verify_against_detected_ca_bundle(): void
    {
        config(['services.countriesnow.ca_bundle' => null]);

        $caBundle = $this->createTemporaryFile();

        $traitUser = new class($caBundle)
        {
            use HasCountryData;

            public function __construct(private readonly string $bundle) {}

            protected function findCaBundle(): ?string
            {
                return $this->bundle;
            }

            public function exposedSslOptions(): array
            {
                return $this->sslOptions();
            }
        };

        $this->assertSame(['verify' => $caBundle], $traitUser->exposedSslOptions());
    }

    public function test_ssl_options_use_configured_ca_bundle_override(): void
    {
        $caBundle = $this->createTemporaryFile();

        config(['services.countriesnow.ca_bundle' => $caBundle]);

        $traitUser = new class
        {
            use HasCountryData;

            public function exposedSslOptions(): array
            {
                return $this->sslOptions();
            }
        };

        $this->assertSame(['verify' => $caBundle], $traitUser->exposedSslOptions());
    }

    public function test_ssl_options_fall_back_to_strict_verification_when_configured_bundle_is_missing(): void
    {
        config(['services.countriesnow.ca_bundle' => __DIR__.'/does-not-exist-'.uniqid().'.pem']);

        $traitUser = new class
        {
            use HasCountryData;

            protected function findCaBundle(): ?string
            {
                return null;
            }

            public function exposedSslOptions(): array
            {
                return $this->sslOptions();
            }
        };

        // A misconfigured path must not re-enable the verify => false bypass.
        $this->assertSame([], $traitUser->exposedSslOptions());
    }

    public function test_fetch_all_country_data_applies_name_overrides(): void
    {
        config(['services.countriesnow.ca_bundle' => null]);

        Http::fake([
            'countriesnow.space/api/v0.1/countries' => Http::response([
                'error' => false,
                'data' => [
                    ['country' => 'United States', 'iso2' => 'US', 'cities' => ['New York']],
                    ['country' => 'Burkina', 'iso2' => 'BF', 'cities' => ['Ouagadougou']],
                ],
            ]),
        ]);

        $traitUser = new class
        {
            use HasCountryData;

            public function exposedFetchAllCountryData(): array
            {
                return $this->fetchAllCountryData();
            }
        };

        $data = $traitUser->exposedFetchAllCountryData();

        $this->assertSame('États-Unis', $data[0]['country']);
        $this->assertSame('Burkina Faso', $data[1]['country']);
        $this->assertSame(['New York'], $data[0]['cities']);
    }

    private function createTemporaryFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ca-bundle-test-');

        $this->assertNotFalse($path);
        $this->assertNotFalse(file_put_contents($path, '-----BEGIN CERTIFICATE-----'));

        return $path;
    }
}
