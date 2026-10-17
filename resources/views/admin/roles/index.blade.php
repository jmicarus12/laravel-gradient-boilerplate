<x-ui::layouts.admin title="Roles" :breadcrumbs="['Home' => route('admin.dashboard'), 'Roles' => null]">
    <x-ui::card title="Roles" flush>
        <x-slot:actions>
            @can('create', Spatie\Permission\Models\Role::class)
                <x-ui::button href="{{ route('admin.roles.create') }}" icon="plus" size="sm">New role</x-ui::button>
            @endcan
        </x-slot:actions>

        <x-ui::table :headers="['Role', 'Permissions', 'Admins', '']">
            @foreach ($roles as $role)
                @php($locked = $service->isLocked($role))
                <tr>
                    <td class="fw-semibold">
                        {{ $role->name }}
                        @if ($locked)<i class="ph ph-lock-simple text-muted ms-1" data-bs-toggle="tooltip" title="Built in — can't be changed"></i>@endif
                    </td>
                    <td>
                        @if ($locked)
                            <x-ui::badge variant="danger">All permissions</x-ui::badge>
                        @else
                            {{ $role->permissions_count }} / {{ count(App\Enums\Permission::cases()) }}
                        @endif
                    </td>
                    <td>{{ $role->users_count }}</td>
                    <td class="text-end text-nowrap">
                        @unless ($locked)
                            @can('update', $role)
                                <x-ui::button href="{{ route('admin.roles.edit', $role) }}" size="sm" outline icon="pencil-simple">Edit</x-ui::button>
                            @endcan
                            @can('delete', $role)
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Delete the {{ e($role->name) }} role?')">
                                    @csrf @method('DELETE')
                                    <x-ui::button size="sm" outline variant="danger" icon="trash">Delete</x-ui::button>
                                </form>
                            @endcan
                        @endunless
                    </td>
                </tr>
            @endforeach
        </x-ui::table>
    </x-ui::card>
</x-ui::layouts.admin>
