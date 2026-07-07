<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => $title ?? null, 'description' => $description ?? null])
</head>

<body class="min-h-screen bg-white font-sans text-black antialiased">
    <x-storefront.announcement-bar />

    <livewire:storefront.header />
    <livewire:cart.drawer />

    <main>
        {{ $slot }}
    </main>

    <x-storefront.footer />

    @fluxScripts
</body>

</html>
