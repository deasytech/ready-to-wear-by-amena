<li class="{{ $item->wrapper_class }}"
    class="{{ request()->url() === $item->link ? 'active' : '' }} {{ !$item->children->isEmpty() ? 'menu-item-has-children' : '' }}">
    <a href="{{ $item->link }}" target="{{ $item->target }}" class="{{ $item->link_class }}">
        {{ $item->name }}
        @if (!$item->children->isEmpty())
            <i class="lastudioicon-down-arrow"></i>
        @endif
    </a>

    @if (!$item->children->isEmpty())
        <ul class="sub-menu">
            @foreach ($item->children as $child)
                @include('filament-menu-builder::components.main.menu-sub-item', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>
