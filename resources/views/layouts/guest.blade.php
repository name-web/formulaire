<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --yellow: #ffb703;
                --yellow-light: #ffc72c;
                --navy: #071426;
                --navy-2: #0b1b31;
                --blue: #122c4d;
                --blue-light: #193b65;
            }

            body {
                font-family: 'DM Sans', sans-serif;
                background: linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);
                min-height: 100vh;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 overflow-hidden sm:rounded-lg"
                 style="background: linear-gradient(145deg, rgba(18,44,77,0.88), rgba(7,20,38,0.94)); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(20px); box-shadow: 0 35px 80px rgba(0,0,0,0.35);">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
