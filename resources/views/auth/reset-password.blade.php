<x-ui::layouts.guest title="Reset password">
    <div class="text-center mb-4">
        <h4 class="mb-1">Choose a new password</h4>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui::input name="email" type="email" label="Email" :value="$email" autocomplete="email" required />
        <x-ui::input name="password" type="password" label="New password" autocomplete="new-password" required autofocus />
        <x-ui::input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />
        <div class="d-grid">
            <x-ui::button icon="key">Reset password</x-ui::button>
        </div>
    </form>
</x-ui::layouts.guest>
