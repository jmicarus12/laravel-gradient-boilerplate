<x-ui::layouts.guest title="Admin login">
    <div class="text-center mb-4">
        <h4 class="mb-1">Admin panel</h4>
        <p class="text-muted mb-0">Sign in with your administrator account</p>
    </div>

    {{-- No "remember me" for admins: the 30-minute admin session timeout should always apply. --}}
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <x-ui::input name="email" type="email" label="Email" autocomplete="username" required autofocus />
        <x-ui::input name="password" type="password" label="Password" autocomplete="current-password" required />
        <div class="d-grid">
            <x-ui::button icon="shield-check">Log in</x-ui::button>
        </div>
    </form>
</x-ui::layouts.guest>
