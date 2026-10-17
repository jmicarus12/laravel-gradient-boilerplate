{{-- Admin layout: sidebar + header. Usage: <x-ui::layouts.admin title="Users" :breadcrumbs="[...]"> --}}
@props(['title' => null, 'breadcrumbs' => []])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('theme::partials.head', ['title' => $title])
    @stack('styles')
</head>
<body data-pc-sidebar-caption="true" data-pc-direction="ltr">
    @include('theme::partials.sidebar', ['menu' => $theme->menu(), 'pinned' => $theme->pinnedMenu(), 'homeUrl' => $theme->url('home') ?? url('/')])

    <header class="pc-header">
        <div class="m-header">
            @include('theme::partials.brand', ['href' => $theme->url('home') ?? url('/')])
        </div>
        <div class="header-wrapper">
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-hide"><i class="ph ph-list"></i></a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse"><i class="ph ph-list"></i></a>
                    </li>
                </ul>
            </div>
            <div class="ms-auto">
                <ul class="list-unstyled">
                    @include('theme::partials.theme-toggle')
                    @include('theme::partials.user-menu')
                </ul>
            </div>
        </div>
    </header>

    <div class="pc-container">
        <div class="pc-content">
            @if ($title)
                <x-ui::page-header :title="$title" :breadcrumbs="$breadcrumbs" />
            @endif
            <x-ui::flash />
            @includeIf($theme->area().'.notices')
            {{ $slot }}
        </div>
    </div>

    @include('theme::partials.footer')
    @stack('scripts')
</body>
</html>
