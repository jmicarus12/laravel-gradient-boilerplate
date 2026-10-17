<x-ui::layouts.guest title="Register">
    <div class="text-center mb-4">
        <h4 class="mb-1">Create your account</h4>
        <p class="text-muted mb-0">
            Already registered? <a href="{{ route('login') }}" class="link-primary">Log in</a>
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <x-ui::input name="name" label="Name" autocomplete="name" required autofocus />
        <x-ui::input name="email" type="email" label="Email" autocomplete="email" required />
        <x-ui::input name="password" type="password" label="Password" autocomplete="new-password" required help="At least 8 characters." />
        <x-ui::input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" required />
        <div class="d-grid">
            <x-ui::button icon="user-plus">Create account</x-ui::button>
        </div>
    </form>
</x-ui::layouts.guest>
