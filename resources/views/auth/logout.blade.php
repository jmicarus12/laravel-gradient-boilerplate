{{-- Shown when someone types /logout (or /admin/logout) in the address bar. --}}
{{-- Logging out itself is a POST, so other sites can't log people out with a link or <img>. --}}
<x-ui::layouts.guest title="Log out">
    <div class="text-center mb-4">
        <h4 class="mb-1">Log out?</h4>
        <p class="text-muted mb-0">You're signed in as <strong>{{ $account->email }}</strong>.</p>
    </div>

    <form method="POST" action="{{ $action }}" class="d-grid mb-3">
        @csrf
        <x-ui::button icon="sign-out" autofocus>Log out</x-ui::button>
    </form>

    <p class="text-center mb-0"><a href="{{ $cancel }}" class="link-primary">Cancel</a></p>
</x-ui::layouts.guest>
