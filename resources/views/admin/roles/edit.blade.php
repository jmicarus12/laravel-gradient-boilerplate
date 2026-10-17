<x-ui::layouts.admin :title="'Edit role: '.$role->name" :breadcrumbs="['Home' => route('admin.dashboard'), 'Roles' => route('admin.roles.index'), $role->name => null]">
    <x-ui::card title="Role">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf @method('PUT')
            @include('admin.roles._form')
            <div class="d-flex gap-2">
                <x-ui::button icon="floppy-disk">Save changes</x-ui::button>
                <x-ui::button href="{{ route('admin.roles.index') }}" variant="secondary" outline>Back</x-ui::button>
            </div>
        </form>
    </x-ui::card>
</x-ui::layouts.admin>
