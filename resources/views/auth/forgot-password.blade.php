<x-ui::layouts.guest title="Forgot password">
    <div class="text-center mb-4">
        <h4 class="mb-1">Forgot your password?</h4>
        <p class="text-muted mb-0">Enter your email and we'll send you a reset link.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-ui::input name="email" type="email" label="Email" autocomplete="email" required autofocus />
        <div class="d-grid mb-3">
            <x-ui::button icon="paper-plane-tilt">Email reset link</x-ui::button>
        </div>
        <p class="text-center mb-0"><a href="{{ route('login') }}" class="link-primary">Back to log in</a></p>
    </form>
</x-ui::layouts.guest>
