@props([
    'title',
    'description' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

<div class="mx-auto flex max-w-md flex-col items-center py-24 text-center">
    <h2 class="font-serif text-2xl text-black">{{ $title }}</h2>
    @if ($description)
        <p class="mt-3 text-sm text-neutral-500">{{ $description }}</p>
    @endif
    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" wire:navigate class="rtw-btn-primary mt-8">{{ $actionLabel }}</a>
    @endif
    {{ $slot ?? '' }}
</div>
