<x-ui::layouts.admin title="Admins" :breadcrumbs="['Home' => route('admin.dashboard'), 'Admins' => null]">
    <x-ui::card title="Admin accounts" flush>
        <x-slot:actions>
            @can('create', App\Models\Admin::class)
                <x-ui::button href="{{ route('admin.admins.create') }}" icon="plus" size="sm">New admin</x-ui::button>
            @endcan
        </x-slot:actions>

        <form method="GET" class="row g-2 align-items-end px-4 pt-3 pb-1">
            <div class="col-md-6">
                <x-ui::input name="search" label="Search" :value="$search" placeholder="Name or email" class="mb-0" />
            </div>
            <div class="col-md-3">
                <x-ui::select name="status" label="Status" :value="$status" :options="['all' => 'All', 'active' => 'Active', 'deactivated' => 'Deactivated', 'deleted' => 'Deleted']" />
            </div>
            <div class="col-md-3 mb-3 d-flex gap-2">
                <x-ui::button type="submit" icon="magnifying-glass" outline>Filter</x-ui::button>
                @if ($search !== '' || $status !== 'all')
                    <x-ui::button href="{{ route('admin.admins.index') }}" variant="secondary" outline>Clear</x-ui::button>
                @endif
            </div>
        </form>

        <x-ui::table :headers="['Admin', 'Roles', 'Status', 'Last login', '']">
            @forelse ($admins as $admin)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $admin->name }} @if ($admin->is(auth()->user()))<small class="text-muted">(you)</small>@endif</div>
                        <small class="text-muted">{{ $admin->email }}</small>
                    </td>
                    <td>
                        @forelse ($admin->roles as $role)
                            <x-ui::badge :variant="$role->name === App\Models\Admin::SUPER_ADMIN ? 'danger' : 'primary'">{{ $role->name }}</x-ui::badge>
                        @empty
                            <span class="text-muted small">No role</span>
                        @endforelse
                    </td>
                    <td>
                        @if ($admin->trashed())
                            <x-ui::badge variant="secondary">Deleted</x-ui::badge>
                        @elseif ($admin->active)
                            <x-ui::badge variant="success">Active</x-ui::badge>
                        @else
                            <x-ui::badge variant="warning">Deactivated</x-ui::badge>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td class="text-end text-nowrap">
                        @if ($admin->trashed())
                            @can('restore', $admin)
                                <form method="POST" action="{{ route('admin.admins.restore', $admin) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <x-ui::button size="sm" outline icon="arrow-counter-clockwise">Restore</x-ui::button>
                                </form>
                            @endcan
                        @else
                            @can('update', $admin)
                                <x-ui::button href="{{ route('admin.admins.edit', $admin) }}" size="sm" outline icon="pencil-simple">Edit</x-ui::button>
                            @endcan
                            @unless ($admin->is(auth()->user()))
                                @can('deactivate', $admin)
                                    <form method="POST" action="{{ route($admin->active ? 'admin.admins.deactivate' : 'admin.admins.reactivate', $admin) }}" class="d-inline"
                                          @if ($admin->active) onsubmit="return confirm('Deactivate {{ e($admin->name) }}? They will be logged out.')" @endif>
                                        @csrf @method('PATCH')
                                        <x-ui::button size="sm" outline :variant="$admin->active ? 'warning' : 'success'" :icon="$admin->active ? 'prohibit' : 'check-circle'">
                                            {{ $admin->active ? 'Deactivate' : 'Reactivate' }}
                                        </x-ui::button>
                                    </form>
                                @endcan
                            @endunless
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No admins found.</td></tr>
            @endforelse
        </x-ui::table>

        <x-ui::pagination :paginator="$admins" class="px-4 pt-3" />
    </x-ui::card>
</x-ui::layouts.admin>
