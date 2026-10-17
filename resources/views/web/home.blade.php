<x-ui::layouts.user title="Home">
    <div class="row">
        <div class="col-lg-8">
            @auth
                <x-ui::card title="Welcome back, {{ auth()->user()->name }}">
                    <p class="mb-0">You're signed in as <strong>{{ auth()->user()->email }}</strong>. Your content goes here.</p>
                </x-ui::card>
            @else
                <x-ui::card title="Welcome">
                    <p class="mb-3">Create an account or log in to get started.</p>
                    <div class="d-flex gap-2">
                        <x-ui::button href="{{ route('register') }}" icon="user-plus">Register</x-ui::button>
                        <x-ui::button href="{{ route('login') }}" outline icon="sign-in">Log in</x-ui::button>
                    </div>
                </x-ui::card>
            @endauth
        </div>
        <div class="col-lg-4">
            <x-ui::card title="Your account">
                @auth
                    <p class="text-muted">Member since {{ auth()->user()->created_at->format('M j, Y') }}</p>
                    <x-ui::button href="{{ route('profile.edit') }}" size="sm" outline icon="user-circle">Profile settings</x-ui::button>
                @else
                    <p class="text-muted mb-0">Log in to see your account.</p>
                @endauth
            </x-ui::card>
        </div>
    </div>
</x-ui::layouts.user>
