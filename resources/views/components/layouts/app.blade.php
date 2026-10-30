@props([
    'title' => null,
    'description' => 'H & T Integrated Farms Limited: integrated livestock, poultry, egg and oil palm production in Ogun State, Nigeria. Request a quote or become a partner.',
    'ogImage' => null,
    'noindex' => false,
])
@php
    $fullTitle = $title ? $title.' · H & T Integrated Farms' : 'H & T Integrated Farms Limited — Livestock, Poultry and Palm Production';
    $analytics = config('site.analytics');
    $ogImageUrl = url($ogImage ?? Vite::asset('resources/images/farm-1600.webp'));
@endphp
<!DOCTYPE html>
<html lang="en-NG" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="H & T Integrated Farms Limited">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0E3B21">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    @livewireStyles
    @stack('head')

    @if (in_array($analytics['provider'], ['plausible', 'ga4'], true) && $analytics['id'] !== '')
        <script id="ht-analytics-config" type="application/json">@json(['provider' => $analytics['provider'], 'id' => $analytics['id'], 'src' => $analytics['src']])</script>
    @endif
</head>
<body class="min-h-screen bg-surface">
    <a href="#main" class="sr-only z-50 rounded-ui bg-g700 px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:text-white">Skip to content</a>

    <x-site.header />

    <main id="main" tabindex="-1" class="focus:outline-none">
        @if (session('status'))
            <div class="border-b border-g500/30 bg-g100" role="status">
                <div class="container-site flex items-center gap-3 py-3.5 text-sm font-medium text-g800">
                    <x-ph name="info" class="size-5 shrink-0 text-g700" />
                    {{ session('status') }}
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>

    <x-site.footer />
    <x-site.cookie-consent />

    @livewireScriptConfig
    @stack('scripts')
</body>
</html>
