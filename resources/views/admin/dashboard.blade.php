<x-ui::layouts.admin title="Dashboard" :breadcrumbs="['Home' => route('admin.dashboard'), 'Dashboard' => null]">
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <x-ui::stat-card label="Users" :value="number_format($stats['users'])" icon="users" note="New this month" :note-value="$stats['usersThisMonth']" />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-ui::stat-card label="Admins" :value="$stats['admins']" icon="user-gear" variant="success" note="Active" :note-value="$stats['activeAdmins']" />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-ui::stat-card label="Roles" :value="$stats['roles']" icon="shield-check" variant="warning" note="Permissions" :note-value="$stats['permissions']" />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-ui::stat-card label="Admins online" :value="$stats['adminsOnline']" icon="broadcast" variant="danger" note="Last" note-value="{{ config('areas.areas.admin.session.lifetime') }} min" />
        </div>
    </div>

    <x-ui::card title="Recent admin logins" flush>
        <x-ui::table :headers="['Admin', 'When', 'From']">
            @forelse ($recentLogins as $admin)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $admin->name }}</div>
                        <small class="text-muted">{{ $admin->email }}</small>
                    </td>
                    <td class="text-muted">{{ $admin->last_login_at->diffForHumans() }}</td>
                    <td class="text-muted">{{ $admin->last_login_ip }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No logins yet.</td></tr>
            @endforelse
        </x-ui::table>
    </x-ui::card>
</x-ui::layouts.admin>
