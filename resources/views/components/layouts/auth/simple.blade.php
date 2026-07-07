<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white">
    @livewire('partials.navbar')
    <div class="bg-background flex flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="my-8 flex w-full max-w-md flex-col gap-2">
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewire('partials.footer')
    @fluxScripts
</body>

</html>
