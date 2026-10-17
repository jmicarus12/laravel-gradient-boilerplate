<x-ui::layouts.guest title="Log in">
    <div class="text-center mb-4">
        <h4 class="mb-1">Welcome back</h4>
        <p class="text-muted mb-0">
            No account yet? <a href="{{ route('register') }}" class="link-primary">Register</a>
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <x-ui::input name="email" type="email" label="Email" autocomplete="email" required autofocus />
        <x-ui::input name="password" type="password" label="Password" autocomplete="current-password" required />
        <div class="d-flex justify-content-between align-items-start">
            <x-ui::checkbox name="remember" label="Remember me" />
            <a href="{{ route('password.request') }}" class="link-primary small">Forgot password?</a>
        </div>
        <div class="d-grid">
            <x-ui::button icon="sign-in">Log in</x-ui::button>
        </div>
    </form>
</x-ui::layouts.guest>
