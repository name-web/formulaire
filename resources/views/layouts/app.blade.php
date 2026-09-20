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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --yellow: #FFB900;
                --yellow-light: #FFC72C;
                --yellow-dim: rgba(255,185,0,.10);
                --navy: #071426;
                --navy-2: #0b1b31;
                --blue: #122c4d;
                --blue-light: #193b65;
                --white: #FFFFFF;
                --text: #e6edf5;
                --muted: #91a1b5;
                --line: rgba(255,255,255,.08);
            }

            *, *::before, *::after { box-sizing: border-box; }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: var(--navy);
                min-height: 100vh;
                color: var(--white);
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: 'Montserrat', 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header style="background: rgba(7,20,38,0.9); border-bottom: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(12px);">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
