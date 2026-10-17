<?php

namespace App\Http\Middleware;

use App\Areas\AreaManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts a group of routes in an area: ->middleware('area:admin').
 *
 * Registered in bootstrap/app.php to run before StartSession, so the
 * session is opened with the area's own cookie and table.
 */
class UseArea
{
    public function __construct(protected AreaManager $areas) {}

    public function handle(Request $request, Closure $next, string $area): Response
    {
        $this->areas->use($area);

        return $next($request);
    }
}
