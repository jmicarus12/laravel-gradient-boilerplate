<?php

namespace App\Http\Controllers\Client;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $sessionTable = config('areas.areas.client.session.table');
        $recent = now()->subMinutes(config('areas.areas.client.session.lifetime'))->getTimestamp();

        return view('client.dashboard');
    }
}
