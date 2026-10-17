<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $sessionTable = config('areas.areas.admin.session.table');
        $recent = now()->subMinutes(config('areas.areas.admin.session.lifetime'))->getTimestamp();

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'usersThisMonth' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'admins' => Admin::count(),
                'activeAdmins' => Admin::active()->count(),
                'roles' => Role::where('guard_name', 'admin')->count(),
                'permissions' => count(Permission::cases()),
                'adminsOnline' => DB::table($sessionTable)->whereNotNull('user_id')->where('last_activity', '>=', $recent)->distinct()->count('user_id'),
            ],
            'recentLogins' => Admin::whereNotNull('last_login_at')->latest('last_login_at')->limit(5)->get(),
        ]);
    }
}
