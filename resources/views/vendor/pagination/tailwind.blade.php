@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="rtw-btn-secondary pointer-events-none opacity-40">{!! __('pagination.previous') !!}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rtw-btn-secondary">{!! __('pagination.previous') !!}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rtw-btn-secondary">{!! __('pagination.next') !!}</a>
            @else
                <span class="rtw-btn-secondary pointer-events-none opacity-40">{!! __('pagination.next') !!}</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-xs tracking-wide text-neutral-500 uppercase">
                {!! __('Showing') !!}
                @if ($paginator->firstItem())
                    <span class="font-medium text-black">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-medium text-black">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                {!! __('of') !!}
                <span class="font-medium text-black">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>

            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="rtw-focus flex size-9 items-center justify-center border border-neutral-200 text-neutral-300">
                        <svg class="size-4" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12.5 15.5 7.5 10l5-5.5" /></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="rtw-focus flex size-9 items-center justify-center border border-neutral-200 text-black hover:border-black">
                        <svg class="size-4" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12.5 15.5 7.5 10l5-5.5" /></svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="flex size-9 items-center justify-center text-sm text-neutral-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="flex size-9 items-center justify-center border border-black bg-black text-sm font-medium text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="rtw-focus flex size-9 items-center justify-center border border-neutral-200 text-sm text-black hover:border-black">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="rtw-focus flex size-9 items-center justify-center border border-neutral-200 text-black hover:border-black">
                        <svg class="size-4" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5 12.5 10l-5 5.5" /></svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="rtw-focus flex size-9 items-center justify-center border border-neutral-200 text-neutral-300">
                        <svg class="size-4" fill="none" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5 12.5 10l-5 5.5" /></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
