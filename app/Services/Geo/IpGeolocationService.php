<?php

namespace App\Services\Geo;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\Log;

class IpGeolocationService
{
    private static ?Reader $reader = null;

    /**
     * Resolve a country name from an IP address.
     *
     * Returns the country name on success, or null on failure
     * (private IP, missing database, address not found, etc.).
     */
    public function getCountry(string $ip): ?string
    {
        if ($this->isPrivateIp($ip)) {
            return config('geo.default_country');
        }

        $reader = $this->getReader();

        if ($reader === null) {
            return config('geo.default_country');
        }

        try {
            $record = $reader->country($ip);

            return $record->country->name ?? config('geo.default_country');
        } catch (AddressNotFoundException) {
            Log::warning('GeoIP: address not found in database.', ['ip' => $ip]);

            return config('geo.default_country');
        } catch (\Exception $e) {
            Log::warning('GeoIP: lookup failed.', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return config('geo.default_country');
        }
    }

    /**
     * Get or create the MaxMind Reader instance.
     */
    private function getReader(): ?Reader
    {
        if (config('geo.cache_reader') && self::$reader !== null) {
            return self::$reader;
        }

        $dbPath = config('geo.database_path');

        if (! $dbPath || ! file_exists($dbPath)) {
            Log::error('GeoIP: database file not found.', ['path' => $dbPath]);

            return null;
        }

        try {
            $reader = new Reader($dbPath);

            if (config('geo.cache_reader')) {
                self::$reader = $reader;
            }

            return $reader;
        } catch (\Exception $e) {
            Log::error('GeoIP: failed to open database.', [
                'path' => $dbPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if an IP address is private or loopback.
     *
     * Handles IPv4 and IPv6 private ranges.
     */
    private function isPrivateIp(string $ip): bool
    {
        $ip = trim($ip);

        if ($ip === '' || $ip === '0.0.0.0') {
            return true;
        }

        // IPv6 loopback and unspecified
        if ($ip === '::1' || $ip === '::') {
            return true;
        }

        // Use PHP's filter_var for IPv4 private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            // Public IP — not private
            return false;
        }

        // If filter_var rejected it as private/reserved, check if it's at least a valid IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        // Not a valid IP at all
        return true;
    }

    /**
     * Reset the cached reader (useful for testing).
     */
    public static function resetReader(): void
    {
        self::$reader = null;
    }
}
