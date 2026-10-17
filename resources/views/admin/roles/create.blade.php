<x-ui::layouts.admin title="New role" :breadcrumbs="['Home' => route('admin.dashboard'), 'Roles' => route('admin.roles.index'), 'New' => null]">
    <x-ui::card title="Role">
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            @include('admin.roles._form')
            <div class="d-flex gap-2">
                <x-ui::button icon="floppy-disk">Create role</x-ui::button>
                <x-ui::button href="{{ route('admin.roles.index') }}" variant="secondary" outline>Cancel</x-ui::button>
            </div>
        </form>
    </x-ui::card>
</x-ui::layouts.admin>
