@php($isSelf = $admin->is(auth()->user()))
<x-ui::layouts.admin :title="'Edit '.$admin->name" :breadcrumbs="['Home' => route('admin.dashboard'), 'Admins' => route('admin.admins.index'), $admin->name => null]">
    <div class="row">
        <div class="col-xl-8">
            <x-ui::card title="Admin details">
                <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
                    @csrf @method('PUT')
                    @include('admin.admins._form')
                    <div class="d-flex gap-2 mt-2">
                        <x-ui::button icon="floppy-disk">Save changes</x-ui::button>
                        <x-ui::button href="{{ route('admin.admins.index') }}" variant="secondary" outline>Back</x-ui::button>
                    </div>
                </form>
            </x-ui::card>

            <x-ui::card title="Set a new password">
                <form method="POST" action="{{ route('admin.admins.password', $admin) }}">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6"><x-ui::input name="password" type="password" label="New password" autocomplete="new-password" required /></div>
                        <div class="col-md-6"><x-ui::input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required /></div>
                    </div>
                    <x-ui::button icon="key" variant="secondary">Change password</x-ui::button>
                </form>
            </x-ui::card>
        </div>

        <div class="col-xl-4">
            <x-ui::card title="Account">
                <dl class="mb-0 small">
                    <dt>Last login</dt>
                    <dd>{{ $admin->last_login_at?->format('M j, Y g:i A') ?? 'Never' }} @if ($admin->last_login_ip)<span class="text-muted">from {{ $admin->last_login_ip }}</span>@endif</dd>
                    <dt>Active sessions</dt>
                    <dd>{{ $sessions }}</dd>
                    <dt>Created</dt>
                    <dd class="mb-0">{{ $admin->created_at->format('M j, Y') }}</dd>
                </dl>
            </x-ui::card>

            @unless ($isSelf)
                <x-ui::card title="Danger zone" class="border-danger-subtle">
                    @can('clearSessions', $admin)
                        <form method="POST" action="{{ route('admin.admins.sessions.destroy', $admin) }}" class="mb-3">
                            @csrf @method('DELETE')
                            <p class="small text-muted mb-2">Logs {{ $admin->name }} out on every device.</p>
                            <x-ui::button size="sm" variant="warning" outline icon="sign-out">Log out everywhere</x-ui::button>
                        </form>
                    @endcan
                    @can('delete', $admin)
                        <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" onsubmit="return confirm('Delete {{ e($admin->name) }}? You can restore them later.')">
                            @csrf @method('DELETE')
                            <p class="small text-muted mb-2">Deleted admins can't log in. You can restore them from the "Deleted" filter.</p>
                            <x-ui::button size="sm" variant="danger" icon="trash">Delete admin</x-ui::button>
                        </form>
                    @endcan
                </x-ui::card>
            @endunless
        </div>
    </div>
</x-ui::layouts.admin>
