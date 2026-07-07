<li class="{{ $item->wrapper_class }}">
    <a href="{{ $item->link }}" target="{{ $item->target }}"
        class="{{ request()->url() === $item->link ? 'active' : '' }} {{ $item->link_class }}">
        {{ $item->name }}
    </a>

    @if ($item->children->isNotEmpty())
        <ul>
            @foreach ($item->children as $child)
                @include('filament-menu-builder::components.main.menu-sub-item', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>
