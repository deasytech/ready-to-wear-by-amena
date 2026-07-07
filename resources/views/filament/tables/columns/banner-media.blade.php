@php
    $record = $getRecord();
@endphp

@if (!empty($record->video))
    <div class="flex flex-col items-start gap-1">
        <video src="{{ asset('storage/' . $record->video) }}" class="rounded"
            style="width: 80px; height: 56px; object-fit: cover; background: #000;" muted preload="metadata"
            title="{{ $record->title }}"></video>
        <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm11.5 7.134L10 8.268v7.464l5.5-3.732-.001.134z" />
            </svg>
            Video
        </span>
    </div>
@elseif (!empty($record->image))
    <img src="{{ asset('storage/' . $record->image) }}" alt="{{ $record->title }}" class="rounded"
        style="width: 80px; height: 56px; object-fit: cover;">
@else
    <span class="text-xs text-gray-400 italic">No media</span>
@endif
