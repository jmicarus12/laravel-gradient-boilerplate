<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(protected RoleService $roles) {}

    public function index(): View
    {
        $roles = Role::where('guard_name', Permission::GUARD)
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', ['roles' => $roles, 'service' => $this->roles]);
    }

    public function create(Request $request): View
    {
        return view('admin.roles.create', [
            'role' => new Role,
            'grantable' => $this->roles->grantablePermissions($request->user()),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = $this->roles->create($request->user(), $request->validated('name'), $request->permissions());

        return redirect()->route('admin.roles.index')->with('success', __('Role :name created.', ['name' => $role->name]));
    }

    public function edit(Request $request, Role $role): View|RedirectResponse
    {
        if ($this->roles->isLocked($role)) {
            return redirect()->route('admin.roles.index')->with('error', __('The :role role is built in and can\'t be changed.', ['role' => $role->name]));
        }

        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'grantable' => $this->roles->grantablePermissions($request->user()),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($request->user(), $role, $request->validated('name'), $request->permissions());

        return redirect()->route('admin.roles.index')->with('success', __('Role :name updated.', ['name' => $role->name]));
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $name = $role->name;
        $this->roles->delete($request->user(), $role);

        return redirect()->route('admin.roles.index')->with('success', __('Role :name deleted.', ['name' => $name]));
    }
}
