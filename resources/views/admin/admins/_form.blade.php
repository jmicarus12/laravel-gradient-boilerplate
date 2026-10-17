{{-- Shared by create and edit. $admin, $roles (assignable) --}}
@php
    $isSelf = $admin->exists && $admin->is(auth()->user());
    $held = $admin->exists ? $admin->getRoleNames()->all() : [];
    $selected = old('roles', $held);
    $assignable = $roles->pluck('name')->all();
    $locked = array_diff($held, $assignable); // roles this admin has that you may not change
@endphp

<div class="row">
    <div class="col-md-6">
        <x-ui::input name="name" label="Name" :value="$admin->name" required />
    </div>
    <div class="col-md-6">
        <x-ui::input name="email" type="email" label="Email" :value="$admin->email" required />
    </div>

    @unless ($admin->exists)
        <div class="col-md-6">
            <x-ui::input name="password" type="password" label="Password" autocomplete="new-password" required />
        </div>
        <div class="col-md-6">
            <x-ui::input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" required />
        </div>
    @endunless
</div>

@if ($isSelf)
    <input type="hidden" name="active" value="1">
@else
    <x-ui::checkbox name="active" label="Active" :checked="$admin->active" help="Deactivated admins can't log in." />
@endif

<h6 class="mt-2 mb-2">Roles</h6>
@if ($isSelf)
    <p class="text-muted small">You can't change your own roles.</p>
    @foreach ($held as $role)
        <input type="hidden" name="roles[]" value="{{ $role }}">
        <x-ui::badge>{{ $role }}</x-ui::badge>
    @endforeach
@else
    <div class="row">
        @foreach ($roles as $role)
            <div class="col-md-6">
                <x-ui::checkbox name="roles[]" :value="$role->name" :id="'role-'.$role->id" :label="$role->name"
                    :checked="in_array($role->name, $selected)"
                    :help="$role->name === App\Models\Admin::SUPER_ADMIN ? 'Full access to everything' : trans_choice('{0} No permissions|{1} 1 permission|[2,*] :count permissions', $role->permissions->count())" />
            </div>
        @endforeach
        @foreach ($locked as $role)
            {{-- Kept as is: you may not add or remove this role. --}}
            <input type="hidden" name="roles[]" value="{{ $role }}">
            <div class="col-md-6">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" checked disabled>
                    <label class="form-check-label">{{ $role }} <small class="d-block text-muted">Only a Super Admin can change this</small></label>
                </div>
            </div>
        @endforeach
    </div>
    @if ($roles->isEmpty() && $locked === [])
        <p class="text-muted small">No roles you can assign yet.</p>
    @endif
@endif
