<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Http\Requests\Admin\PasswordRequest;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin accounts. Who may call each action is decided on the route (->can(...) → AdminPolicy);
 * extra rules (no self-deletion, no privilege escalation) live in AdminService.
 */
class AdminController extends Controller
{
    public function __construct(protected AdminService $admins) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search'));

        $admins = Admin::query()
            ->with('roles')
            ->when($status === 'active', fn ($q) => $q->where('active', true))
            ->when($status === 'deactivated', fn ($q) => $q->where('active', false))
            ->when($status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.admins.index', compact('admins', 'status', 'search'));
    }

    public function create(Request $request): View
    {
        return view('admin.admins.create', [
            'admin' => new Admin(['active' => true]),
            'roles' => $this->admins->assignableRoles($request->user()),
        ]);
    }

    public function store(AdminRequest $request): RedirectResponse
    {
        $admin = $this->admins->create($request->user(), $request->account(), $request->roles());

        return redirect()->route('admin.admins.index')->with('success', __('Admin :name created.', ['name' => $admin->name]));
    }

    public function edit(Request $request, Admin $admin): View
    {
        return view('admin.admins.edit', [
            'admin' => $admin,
            'roles' => $this->admins->assignableRoles($request->user()),
            'sessions' => $this->admins->activeSessionCount($admin),
        ]);
    }

    public function update(AdminRequest $request, Admin $admin): RedirectResponse
    {
        $this->admins->update($request->user(), $admin, $request->account(), $request->roles());

        return redirect()->route('admin.admins.edit', $admin)->with('success', __('Admin updated.'));
    }

    public function updatePassword(PasswordRequest $request, Admin $admin): RedirectResponse
    {
        $this->admins->changePassword($request->user(), $admin, $request->validated('password'));

        return redirect()->route('admin.admins.edit', $admin)->with('success', __('Password changed.'));
    }

    public function deactivate(Request $request, Admin $admin): RedirectResponse
    {
        $this->admins->setActive($request->user(), $admin, false);

        return back()->with('success', __(':name has been deactivated and logged out.', ['name' => $admin->name]));
    }

    public function reactivate(Request $request, Admin $admin): RedirectResponse
    {
        $this->admins->setActive($request->user(), $admin, true);

        return back()->with('success', __(':name has been reactivated.', ['name' => $admin->name]));
    }

    public function clearSessions(Request $request, Admin $admin): RedirectResponse
    {
        $count = $this->admins->clearSessions($request->user(), $admin);

        return back()->with('success', trans_choice('{0} :name had no active sessions.|{1} :name was logged out of 1 session.|[2,*] :name was logged out of :count sessions.', $count, ['name' => $admin->name]));
    }

    public function destroy(Request $request, Admin $admin): RedirectResponse
    {
        $this->admins->delete($request->user(), $admin);

        return redirect()->route('admin.admins.index')->with('success', __(':name has been deleted. You can restore them from "Deleted".', ['name' => $admin->name]));
    }

    public function restore(Request $request, Admin $admin): RedirectResponse
    {
        $this->admins->restore($request->user(), $admin);

        return redirect()->route('admin.admins.index')->with('success', __(':name has been restored.', ['name' => $admin->name]));
    }
}
