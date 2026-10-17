<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ClientLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Client;

/**
 * Client login. Same flow as the user LoginController, but on the "client" guard —
 * and because the client area has its own session (config/areas.php), logging in
 * or out here never touches a user session in the same browser.
 */
class LoginController extends Controller
{
    public function create(): View
    {
        return view('client.auth.login');
    }

    public function store(ClientLoginRequest $request): RedirectResponse
    {
        $request->authenticate('client');

        $request->session()->regenerate();

        $request->user('client')->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        return redirect()->intended(route('client.dashboard'));
    }

    /** GET /client/logout — typed in the address bar. Asks first; the button POSTs to destroy(). */
    public function confirm(Request $request): RedirectResponse|View
    {
        if (! $account = Auth::guard('client')->user()) {
            return redirect()->route('client.login');
        }

        return view('auth.logout', [
            'account' => $account,
            'action' => route('client.logout'),
            'cancel' => route('client.dashboard'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('client')->logout();

        // Only the client session is thrown away — it is a different session from the user's.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
}
