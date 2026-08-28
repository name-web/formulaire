<?php

namespace App\Http\Controllers;

use App\Traits\HasCountryData;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    use HasCountryData;

    /**
     * GET /api/countries
     */
    public function index(): JsonResponse
    {
        $data = $this->fetchAllCountryData();

        $countries = collect($data)
            ->map(fn (array $item) => [
                'name' => $item['country'],
                'code' => $item['iso2'],
            ])
            ->sortBy('name')
            ->values()
            ->all();

        return response()->json($countries);
    }

    /**
     * GET /api/countries/{iso2}/cities
     */
    public function cities(string $iso2): JsonResponse
    {
        $iso2 = strtoupper($iso2);
        $data = $this->fetchAllCountryData();

        $country = collect($data)->first(
            fn (array $item) => strtoupper($item['iso2']) === $iso2
        );

        if (! $country) {
            return response()->json([
                'error' => true,
                'message' => "Pays non trouvé pour le code « {$iso2} ».",
            ], 404);
        }

        $cities = collect($country['cities'] ?? [])
            ->sort()
            ->values()
            ->all();

        return response()->json([
            'country' => [
                'name' => $country['country'],
                'code' => $country['iso2'],
            ],
            'cities' => $cities,
        ]);
    }
}
