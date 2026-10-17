<?php

namespace App\Http\Middleware;

use App\Areas\AreaManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out an account that was deactivated while logged in.
 * Applies to models with an "active" column (e.g. Admin); others pass through.
 */
class EnsureAccountIsActive
{
    public function __construct(protected AreaManager $areas) {}

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard($this->areas->guard());
        $account = $guard->user();

        if ($account && $account->getAttribute('active') === false) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect($this->areas->loginUrl())
                ->with('error', __('Your account has been deactivated.'));
        }

        return $next($request);
    }
}
