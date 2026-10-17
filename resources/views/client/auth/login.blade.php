<x-ui::layouts.guest title="Client view">
    <div class="text-center mb-4">
        <h4 class="mb-1">Client view</h4>
        <p class="text-muted mb-0">Enter the view code from your administrator</p>
    </div>

    <form method="POST" action="{{ route('client.login.store') }}">
        @csrf
        <x-ui::input name="view_code" label="View code" autocomplete="off" required autofocus />
        <div class="d-grid">
            <x-ui::button icon="eye">View</x-ui::button>
        </div>
    </form>
</x-ui::layouts.guest>
