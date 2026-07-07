<li class="{{ $item->wrapper_class }}">
    <a href="{{ $item->link }}" target="{{ $item->target }}"
        class="{{ request()->url() === $item->link ? 'active' : '' }} {{ $item->link_class }}">
        {{ $item->name }}
        @if (!$item->children->isEmpty())
            <i class="lastudioicon-down-arrow"></i>
        @endif
    </a>

    @if (!$item->children->isEmpty())
        <ul class="yena-dropdown">
            @foreach ($item->children as $child)
                @include('filament-menu-builder::components.main.menu-sub-item', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>
