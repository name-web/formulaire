<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MaxMind GeoLite2 Country Database
    |--------------------------------------------------------------------------
    |
    | Path to the GeoLite2-Country.mmdb file. Download from:
    | https://dev.maxmind.com/geoip/geolite2-free-geolocation-data
    |
    | You must create a free MaxMind account and accept the license:
    | https://www.maxmind.com/en/geolite2/eula
    |
    */

    'database_path' => env('GEOIP_DB_PATH', database_path('geoip/GeoLite2-Country.mmdb')),

    /*
    |--------------------------------------------------------------------------
    | Default Country
    |--------------------------------------------------------------------------
    |
    | The country value used when geolocation fails or the IP is private.
    | Set to null to store no country on failure.
    |
    */

    'default_country' => null,

    /*
    |--------------------------------------------------------------------------
    | Reader Cache
    |--------------------------------------------------------------------------
    |
    | Whether to cache the MaxMind Reader instance in memory.
    | Recommended for production to avoid re-opening the .mmdb file on each request.
    |
    */

    'cache_reader' => true,

];
