<x-ui::layouts.user title="Profile settings" :breadcrumbs="['Home' => route('home'), 'Profile settings' => null]">
    <div class="row">
        <div class="col-lg-6">
            <x-ui::card title="Profile information">
                <p class="text-muted">Your name and email address.</p>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <x-ui::input name="name" label="Name" :value="$user->name" autocomplete="name" required />
                    <x-ui::input name="email" type="email" label="Email" :value="$user->email" autocomplete="email" required />
                    <x-ui::button icon="floppy-disk">Save</x-ui::button>
                </form>
            </x-ui::card>
        </div>

        <div class="col-lg-6">
            <x-ui::card title="Change password">
                <p class="text-muted">Use a long password you don't use anywhere else.</p>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')
                    <x-ui::input name="current_password" type="password" label="Current password" autocomplete="current-password" required />
                    <x-ui::input name="password" type="password" label="New password" autocomplete="new-password" required />
                    <x-ui::input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />
                    <x-ui::button icon="key">Update password</x-ui::button>
                </form>
            </x-ui::card>
        </div>

        <div class="col-lg-6">
            <x-ui::card title="Delete account" class="border-danger-subtle">
                <p class="text-muted">This permanently deletes your account. Enter your password to confirm.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Delete your account permanently?')">
                    @csrf
                    @method('DELETE')
                    <x-ui::input name="delete_password" type="password" label="Password" bag="deleteAccount" autocomplete="current-password" required />
                    <x-ui::button variant="danger" icon="trash">Delete account</x-ui::button>
                </form>
            </x-ui::card>
        </div>
    </div>
</x-ui::layouts.user>
