{{-- User layout: top bar, no sidebar. Usage: <x-ui::layouts.user title="Profile"> --}}
@props(['title' => null, 'breadcrumbs' => []])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('theme::partials.head', ['title' => $title])
    @stack('styles')
</head>
<body data-pc-direction="ltr">
    <header class="pc-header pc-header-top">
        <div class="m-header">
            @include('theme::partials.brand', ['href' => $theme->url('home') ?? url('/')])
        </div>
        <div class="header-wrapper">
            <div class="me-auto">
                <ul class="list-unstyled">
                    @foreach ($theme->menu() as $item)
                        @isset($item['url'])
                            <li class="pc-h-item">
                                <a href="{{ $item['url'] }}" @class(['pc-head-link', 'pc-head-text', 'fw-semibold' => $item['is_active']])>
                                    @isset($item['icon'])<i class="ph ph-{{ $item['icon'] }}"></i>@endisset
                                    <span class="d-none d-sm-inline">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endisset
                    @endforeach
                </ul>
            </div>
            <div class="ms-auto">
                <ul class="list-unstyled">
                    @include('theme::partials.theme-toggle')
                    @auth
                        @include('theme::partials.user-menu')
                    @else
                        @if ($login = $theme->url('login'))
                            <li class="pc-h-item">
                                <a href="{{ $login }}" class="pc-head-link pc-head-text"><i class="ph ph-sign-in"></i><span>Log in</span></a>
                            </li>
                        @endif
                        @if ($register = $theme->url('register'))
                            <li class="pc-h-item">
                                <a href="{{ $register }}" class="pc-head-link pc-head-text"><i class="ph ph-user-plus"></i><span class="d-none d-sm-inline">Register</span></a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>
        </div>
    </header>

    <div class="pc-container no-sidebar">
        <div class="pc-content">
            @if ($title)
                <x-ui::page-header :title="$title" :breadcrumbs="$breadcrumbs" />
            @endif
            <x-ui::flash />
            @includeIf($theme->area().'.notices')
            {{ $slot }}
        </div>
    </div>

    @include('theme::partials.footer', ['noSidebar' => true])
    @stack('scripts')
</body>
</html>
