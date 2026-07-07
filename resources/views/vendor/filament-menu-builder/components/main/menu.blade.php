<ul>
    @foreach ($menuItems as $menuItem)
        @include('filament-menu-builder::components.main.menu-item', ['item' => $menuItem])
    @endforeach
</ul>
