<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white font-sans text-black antialiased">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col justify-between overflow-hidden bg-black p-10 text-white lg:flex">
                <img src="https://picsum.photos/seed/rtw-auth/1200/1600" alt="" aria-hidden="true" class="absolute inset-0 size-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-black/10"></div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center" wire:navigate>
                    <img src="{{ asset('images/logo-white.png') }}" alt="Ready To Wear by Amena" class="h-10 w-auto object-contain">
                </a>

                <div class="relative z-20">
                    <p class="rtw-label mb-3 text-white/70">Ready To Wear by Amena</p>
                    <p class="font-serif text-2xl leading-snug">Precise tailoring, considered fabrics &mdash; a wardrobe built to last beyond the season.</p>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <img src="{{ asset('images/logo-black.png') }}" alt="Ready To Wear by Amena" class="h-10 w-auto object-contain">
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
