<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" href="/images/hotel-icon.webp" type="image/webp">
        <link rel="icon" href="/images/hotel-icon.png" type="image/png">
        <link rel="apple-touch-icon" href="/images/hotel-icon.png">

        {{-- Preload LCP hero image --}}
        <link rel="preload" as="image" type="image/webp" href="/images/hero-main.webp" fetchpriority="high">

        {{-- Bunny Fonts (non-blocking via @fonts directive) --}}
        @fonts

        {{-- Google Fonts — loaded async to avoid render-blocking --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap">
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

        @unless (app()->environment('testing'))
            @viteReactRefresh
            @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @endunless
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
            <meta name="description" content="Hotel Bugambilias Estelí — Reserva habitaciones, suites y servicios exclusivos en el corazón de Nicaragua. WiFi, estacionamiento y desayuno incluido.">
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
