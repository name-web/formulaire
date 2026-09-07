<?php

namespace Tests\Unit;

use App\Services\Geo\IpGeolocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IpGeolocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private IpGeolocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IpGeolocationService;
        IpGeolocationService::resetReader();
    }

    public function test_private_ip_returns_default_country(): void
    {
        config(['geo.default_country' => null]);

        $this->assertNull($this->service->getCountry('127.0.0.1'));
    }

    public function test_loopback_ipv6_returns_default_country(): void
    {
        config(['geo.default_country' => null]);

        $this->assertNull($this->service->getCountry('::1'));
    }

    public function test_private_10_x_x_x_returns_default_country(): void
    {
        config(['geo.default_country' => null]);

        $this->assertNull($this->service->getCountry('10.0.0.1'));
    }

    public function test_private_192_168_returns_default_country(): void
    {
        config(['geo.default_country' => null]);

        $this->assertNull($this->service->getCountry('192.168.1.1'));
    }

    public function test_private_172_16_returns_default_country(): void
    {
        config(['geo.default_country' => null]);

        $this->assertNull($this->service->getCountry('172.16.0.1'));
    }

    public function test_empty_ip_returns_default_country(): void
    {
        config(['geo.default_country' => null]);

        $this->assertNull($this->service->getCountry(''));
    }

    public function test_missing_database_returns_default_country(): void
    {
        config(['geo.database_path' => '/nonexistent/path.mmdb']);
        config(['geo.default_country' => null]);

        $this->assertNull($this->service->getCountry('8.8.8.8'));
    }

    public function test_default_country_is_used_on_failure(): void
    {
        config(['geo.database_path' => '/nonexistent/path.mmdb']);
        config(['geo.default_country' => 'Test Country']);

        $this->assertSame('Test Country', $this->service->getCountry('8.8.8.8'));
    }

    public function test_valid_public_ip_with_real_database(): void
    {
        $dbPath = config('geo.database_path');

        if (! $dbPath || ! file_exists($dbPath)) {
            $this->markTestSkipped('GeoLite2 database not available.');
        }

        // Google DNS — should resolve to United States
        $country = $this->service->getCountry('8.8.8.8');

        $this->assertIsString($country);
        $this->assertNotEmpty($country);
    }
}
