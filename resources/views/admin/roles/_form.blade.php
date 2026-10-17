{{-- Shared by create and edit. $role, $grantable (permission names you may grant) --}}
@php
    $held = $role->exists ? $role->permissions->pluck('name')->all() : [];
    $selected = old('permissions', $held);
@endphp

<x-ui::input name="name" label="Role name" :value="$role->name" required placeholder="e.g. HR, Accounting, Support" />

<h6 class="mt-2 mb-1">Permissions</h6>
<p class="text-muted small">Tick what admins with this role may do.</p>

<div class="row">
    @foreach (App\Enums\Permission::grouped() as $group => $permissions)
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="border rounded p-3 h-100">
                <div class="fw-semibold mb-2">{{ $group }}</div>
                @foreach ($permissions as $permission)
                    @php($canGrant = in_array($permission->value, $grantable))
                    @if ($canGrant)
                        <x-ui::checkbox name="permissions[]" :value="$permission->value" :id="'perm-'.$permission->value"
                            :label="$permission->label()" :checked="in_array($permission->value, $selected)" class="mb-2" />
                    @else
                        {{-- You don't have this permission yourself, so you can't change it. --}}
                        @if (in_array($permission->value, $held))
                            <input type="hidden" name="permissions[]" value="{{ $permission->value }}">
                        @endif
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" disabled @checked(in_array($permission->value, $held))>
                            <label class="form-check-label text-muted">{{ $permission->label() }} <i class="ph ph-lock-simple" title="You don't have this permission"></i></label>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
