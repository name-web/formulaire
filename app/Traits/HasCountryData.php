<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

trait HasCountryData
{
    private const API_BASE = 'https://countriesnow.space/api/v0.1';

    private const CACHE_KEY = 'countriesnow_all';

    private const CACHE_TTL = 86400;

    private const NAME_OVERRIDES = [
        'Ivory Coast' => "Côte d'Ivoire",
        'Democratic Republic of the Congo' => 'République démocratique du Congo',
        'Republic of the Congo' => 'République du Congo',
        'Burkina' => 'Burkina Faso',
        'United States' => 'États-Unis',
        'United Kingdom' => 'Royaume-Uni',
        'South Korea' => 'Corée du Sud',
        'North Korea' => 'Corée du Nord',
        'Czech Republic' => 'Tchéquie',
        'Timor-Leste' => 'Timor oriental',
        'Palestine' => 'Palestine',
        'Morocco' => 'Maroc',
        'Algeria' => 'Algérie',
        'Tunisia' => 'Tunisie',
        'Senegal' => 'Sénégal',
        'Guinea' => 'Guinée',
        'Benin' => 'Bénin',
    ];

    private function fetchAllCountryData(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $response = Http::timeout(30)
                ->withOptions($this->sslOptions())
                ->get(self::API_BASE.'/countries');

            if (! $response->successful()) {
                return [];
            }

            $body = $response->json();

            if (! isset($body['data']) || ! is_array($body['data'])) {
                return [];
            }

            return array_map(function (array $item) {
                if (isset(self::NAME_OVERRIDES[$item['country']])) {
                    $item['country'] = self::NAME_OVERRIDES[$item['country']];
                }

                return $item;
            }, $body['data']);
        });
    }

    private function sslOptions(): array
    {
        $caBundle = $this->findCaBundle();

        if ($caBundle) {
            return ['verify' => $caBundle];
        }

        return ['verify' => false];
    }

    private function findCaBundle(): ?string
    {
        $candidates = [
            ini_get('openssl.cafile'),
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/cert.pem',
            'C:\\Program Files\\Git\\mingw64\\etc\\ssl\\certs\\ca-bundle.crt',
            'C:\\Program Files\\Common Files\\SSL\\cacert.pem',
        ];

        foreach ($candidates as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
