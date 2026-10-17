{{-- Guest layout: centered card for login/register. Usage: <x-ui::layouts.guest title="Log in"> --}}
@props(['title' => null])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('theme::partials.head', ['title' => $title])
    @stack('styles')
</head>
<body>
    <div class="auth-main v1">
        <div class="auth-wrapper">
            <div class="auth-form">
                <div class="auth-stack">
                    <div class="text-center mb-3">
                        @include('theme::partials.brand')
                    </div>
                    <div class="card my-3">
                        <div class="card-body p-4">
                            <x-ui::flash />
                            {{ $slot }}
                        </div>
                    </div>
                    <div class="text-center">
                        <ul class="list-unstyled d-inline-flex mb-0">
                            @include('theme::partials.theme-toggle')
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
