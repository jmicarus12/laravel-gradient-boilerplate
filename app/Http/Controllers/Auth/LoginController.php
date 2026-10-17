<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate('web');

        // New session ID after login, so an ID captured before login is useless (session fixation).
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /** GET /logout — typed in the address bar. Asks first; the button POSTs to destroy(). */
    public function confirm(Request $request): RedirectResponse|View
    {
        if (! $account = Auth::guard('web')->user()) {
            return redirect()->route('home');
        }

        return view('auth.logout', [
            'account' => $account,
            'action' => route('logout'),
            'cancel' => route('home'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Throw the session away and issue a fresh CSRF token.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
