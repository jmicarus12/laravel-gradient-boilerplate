<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

/**
 * Activity Log: who did what in the admin panel (written by AdminService,
 * RoleService and RecordAdminAuthActivity).
 */
class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $event = (string) $request->query('event', '');

        $activities = Activity::query()
            ->inLog('admin')
            ->with('causer')
            ->when($event !== '', fn ($q) => $q->where('event', $event))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('description', 'like', "%{$search}%")
                ->orWhereHasMorph('causer', [Admin::class], fn ($q) => $q
                    ->withTrashed()
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $events = Activity::query()->inLog('admin')->distinct()->orderBy('event')->pluck('event')->filter()->values();

        return view('admin.activity.index', compact('activities', 'events', 'search', 'event'));
    }
}
