<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()->orderBy('name')->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    public function edit(Request $request, Client $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }
}
