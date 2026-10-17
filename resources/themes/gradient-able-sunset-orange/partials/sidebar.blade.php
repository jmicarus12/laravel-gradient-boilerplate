<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            @include('theme::partials.brand', ['href' => $homeUrl ?? url('/')])
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                @include('theme::partials.menu-items', ['items' => $menu])
            </ul>
        </div>
        @if (! empty($pinned))
            <div class="pc-sidebar-pinned">
                <ul class="pc-navbar">
                    @include('theme::partials.menu-items', ['items' => $pinned])
                </ul>
            </div>
        @endif
    </div>
</nav>
