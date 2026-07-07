<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- Dynamic Title and Meta Description --}}
    <title>{{ $title ?? _('Ready to Wear') }}</title>
    <meta name="description"
        content="{{ $description ?? 'Discover Ready to Wear - a premium cosmetics brand offering natural skincare, exfoliating body scrubs, and nourishing lip gloss. Reveal glowing, healthy skin with our handcrafted beauty essentials.' }}">

    {{-- Open Graph for Social Sharing --}}
    <meta property="og:title" content="{{ $title ?? 'Ready to Wear' }}">
    <meta property="og:description"
        content="{{ $description ?? 'Discover Ready to Wear - a premium cosmetics brand offering natural skincare, exfoliating body scrubs, and nourishing lip gloss. Reveal glowing, healthy skin with our handcrafted beauty essentials.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/logo/1.png') }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Ready to Wear' }}">
    <meta name="twitter:description"
        content="{{ $description ?? 'Discover Ready to Wear - a premium cosmetics brand offering natural skincare, exfoliating body scrubs, and nourishing lip gloss. Reveal glowing, healthy skin with our handcrafted beauty essentials.' }}">
    <meta name="twitter:image" content="{{ asset('images/logo/1.png') }}">

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ url()->current() }}" />

    {{-- Favicon & Icons --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon.png') }}">

    {{-- Appearance Settings --}}
    @fluxAppearance
</head>

<body class="template-color-1">
    <div>
        {{ $slot }}
    </div>
    @fluxScripts
    @stack('scripts')
</body>

</html>
