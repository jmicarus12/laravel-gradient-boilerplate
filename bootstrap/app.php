<?php

use App\Areas\AreaManager;
use App\Exceptions\ActionNotAllowed;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\UseArea;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Every area with a "routes" folder in config/areas.php (e.g. admin → routes/admin)
            // gets its URL prefix, "{area}." route names and the area middleware.
            //   routes/admin/public/*.php  no login needed (the login page)
            //   routes/admin/*.php         logged-in, active admins only
            foreach (config('areas.areas') as $name => $area) {
                if (! isset($area['routes'])) {
                    continue;
                }

                $folder = base_path($area['routes']);

                Route::middleware(['web', "area:{$name}"])
                    ->prefix($area['prefix'])
                    ->name("{$name}.")
                    ->group(function () use ($folder, $area) {
                        foreach (glob("{$folder}/public/*.php") as $file) {
                            require $file;
                        }

                        Route::middleware(["auth:{$area['guard']}", 'active'])->group(function () use ($folder) {
                            foreach (glob("{$folder}/*.php") as $file) {
                                require $file;
                            }
                        });
                    });
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'area' => UseArea::class,
            'active' => EnsureAccountIsActive::class,
        ]);

        // The area decides the session cookie and table, so it must run before the session starts.
        $middleware->prependToPriorityList(before: StartSession::class, prepend: UseArea::class);

        // "auth" sends guests to the current area's login; "guest" sends members to its home.
        $middleware->redirectGuestsTo(fn () => app(AreaManager::class)->loginUrl());
        $middleware->redirectUsersTo(fn () => app(AreaManager::class)->homeUrl());
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Business rules ("you can't delete yourself") → back to the form with a message.
        // They're expected refusals, not bugs, so keep them out of the error log.
        $exceptions->dontReport(ActionNotAllowed::class);
        $exceptions->render(function (ActionNotAllowed $e, Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 403)
                : back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
