<x-ui::layouts.guest title="Verify email">
    <div class="text-center mb-4">
        <h4 class="mb-1">Check your inbox</h4>
        <p class="text-muted mb-0">
            We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
            Click it to finish setting up your account.
        </p>
    </div>

    <form method="POST" action="{{ route('verification.send') }}" class="d-grid mb-3">
        @csrf
        <x-ui::button icon="paper-plane-tilt">Resend verification email</x-ui::button>
    </form>

    <div class="d-flex justify-content-between">
        <a href="{{ route('profile.edit') }}" class="link-primary small">Wrong email? Change it</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link btn-sm p-0">Log out</button>
        </form>
    </div>
</x-ui::layouts.guest>
