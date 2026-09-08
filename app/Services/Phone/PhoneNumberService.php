<?php

namespace App\Services\Phone;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Countries;

class PhoneNumberService
{
    private PhoneNumberUtil $util;

    public function __construct()
    {
        $this->util = PhoneNumberUtil::getInstance();
    }

    /**
     * Regions (ISO 3166-1 alpha-2) supported by libphonenumber.
     *
     * @return list<string>
     */
    public function supportedRegions(): array
    {
        return $this->util->getSupportedRegions();
    }

    /**
     * Validate a phone number against the selected country and return it
     * normalized to E.164, or null when invalid.
     *
     * The number's country calling code must match the calling code of the
     * selected region (e.g. +44 matches GB/GG/JE/IM, +1 matches the whole
     * NANP), so numbers from a different country are rejected.
     */
    public function normalizeToE164(string $number, string $region): ?string
    {
        $region = strtoupper(trim($region));

        if (! in_array($region, $this->supportedRegions(), true)) {
            return null;
        }

        try {
            $parsed = $this->util->parse($number, $region);
        } catch (NumberParseException) {
            return null;
        }

        if (! $this->util->isValidNumber($parsed)) {
            return null;
        }

        if ($parsed->getCountryCode() !== $this->util->getCountryCodeForRegion($region)) {
            return null;
        }

        return $this->util->format($parsed, PhoneNumberFormat::E164);
    }

    /**
     * Selectable countries for the form, with French name and dial code.
     * Region codes come from libphonenumber, French names from CLDR
     * (symfony/intl) — nothing is hardcoded in the app.
     *
     * @return array<string, array{name: string, dial_code: int}>
     */
    public function countries(): array
    {
        $names = Countries::getNames('fr');

        $countries = [];
        foreach ($this->supportedRegions() as $region) {
            if (! isset($names[$region])) {
                continue;
            }

            $countries[$region] = [
                'name' => $names[$region],
                'dial_code' => $this->util->getCountryCodeForRegion($region),
            ];
        }

        uasort($countries, fn (array $a, array $b): int => strcmp(strtolower($a['name']), strtolower($b['name'])));

        return $countries;
    }
}
