<x-ui::layouts.admin title="New admin" :breadcrumbs="['Home' => route('admin.dashboard'), 'Admins' => route('admin.admins.index'), 'New' => null]">
    <div class="row">
        <div class="col-xl-8">
            <x-ui::card title="Admin details">
                <form method="POST" action="{{ route('admin.admins.store') }}">
                    @csrf
                    @include('admin.admins._form')
                    <div class="d-flex gap-2 mt-2">
                        <x-ui::button icon="floppy-disk">Create admin</x-ui::button>
                        <x-ui::button href="{{ route('admin.admins.index') }}" variant="secondary" outline>Cancel</x-ui::button>
                    </div>
                </form>
            </x-ui::card>
        </div>
    </div>
</x-ui::layouts.admin>
