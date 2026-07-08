<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $metaTitle = isset($title) ? "{$title} | Ready-To-Wear by Amena" : 'Ready-To-Wear by Amena';
    $metaDescription =
        $description ??
        'Ready-To-Wear by Amena is a monochrome womenswear label built on precise tailoring, considered fabrics and editorial minimalism.';
@endphp

{{-- Dynamic Title and Meta Description --}}
<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">

{{-- Open Graph for Social Sharing --}}
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:image" content="{{ asset('images/logo-black.png') }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ asset('images/logo-black.png') }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ url()->current() }}" />

{{-- Favicon & Icons --}}
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon/favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('images/favicon/site.webmanifest') }}">

{{-- Editorial serif + clean sans, served via Bunny Fonts (privacy-friendly Google Fonts proxy) --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|instrument-sans:400,500,600"
    rel="stylesheet" />

{{-- Styles & Scripts --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Appearance Settings --}}
@fluxAppearance
