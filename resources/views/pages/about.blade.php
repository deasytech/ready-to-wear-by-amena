@php
    $sections = \App\Models\About::active()->orderBy('sort_order')->get();
    $main = $sections->firstWhere('section_name', 'main_about');
@endphp

<x-layouts.storefront :title="'About'" :description="$main?->title ? strip_tags($main->content) : null">
    <section class="relative flex h-[50vh] min-h-[360px] items-end overflow-hidden bg-neutral-900 text-white">
        <img src="https://images.pexels.com/photos/4614250/pexels-photo-4614250.jpeg?auto=compress&cs=tinysrgb&w=1920&h=900&fit=crop"
            alt="" aria-hidden="true" class="absolute inset-0 size-full object-cover opacity-70">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
        <div class="rtw-container relative pb-12">
            <p class="rtw-label mb-3 text-white/80">About</p>
            <h1 class="font-serif text-4xl lg:text-5xl">Ready-To-Wear by Amena</h1>
        </div>
    </section>

    <div class="rtw-container py-16 lg:py-24">
        <div class="mx-auto max-w-2xl space-y-16">
            @forelse ($sections as $section)
                <section id="{{ str_replace('_', '-', $section->section_name) }}" class="scroll-mt-24">
                    @if ($section->title)
                        <h2 class="font-serif text-3xl">{{ $section->title }}</h2>
                    @endif
                    @if ($section->image_url)
                        <img src="{{ $section->image_url }}" alt="{{ $section->title }}"
                            class="my-8 aspect-[4/3] w-full object-cover">
                    @endif
                    <div class="prose prose-neutral mt-6 text-sm leading-relaxed text-neutral-600">
                        {!! $section->content !!}
                    </div>
                </section>
            @empty
                <p class="text-center text-sm text-neutral-500">Our story is coming soon.</p>
            @endforelse
        </div>
    </div>
</x-layouts.storefront>
