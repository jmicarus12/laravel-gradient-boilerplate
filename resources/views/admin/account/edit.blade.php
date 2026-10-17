<x-ui::layouts.admin title="My account" :breadcrumbs="['Home' => route('admin.dashboard'), 'My account' => null]">
    <div class="row">
        <div class="col-lg-6">
            <x-ui::card title="Profile">
                <form method="POST" action="{{ route('admin.account.update') }}">
                    @csrf @method('PATCH')
                    <x-ui::input name="name" label="Name" :value="$admin->name" required />
                    <x-ui::input name="email" type="email" label="Email" :value="$admin->email" required />
                    <x-ui::button icon="floppy-disk">Save</x-ui::button>
                </form>
            </x-ui::card>

            <x-ui::card title="Your roles">
                @forelse ($admin->roles as $role)
                    <x-ui::badge :variant="$role->name === App\Models\Admin::SUPER_ADMIN ? 'danger' : 'primary'">{{ $role->name }}</x-ui::badge>
                @empty
                    <span class="text-muted">No roles yet — ask a Super Admin.</span>
                @endforelse
            </x-ui::card>
        </div>
        <div class="col-lg-6">
            <x-ui::card title="Change password">
                <form method="POST" action="{{ route('admin.account.password') }}">
                    @csrf @method('PUT')
                    <x-ui::input name="current_password" type="password" label="Current password" autocomplete="current-password" required />
                    <x-ui::input name="password" type="password" label="New password" autocomplete="new-password" required />
                    <x-ui::input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />
                    <x-ui::button icon="key">Change password</x-ui::button>
                </form>
            </x-ui::card>
        </div>
    </div>
</x-ui::layouts.admin>
