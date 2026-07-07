@props(['active'])

@php
    $classes = $active
        ? 'font-medium text-gold py-3 md:py-6'
        : 'p-2 flex items-center font-medium text-neutral-200 hover:text-gold py-3 md:py-6 rounded-lg focus:outline-hidden focus:text-gold text-nowrap';
@endphp

<a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
