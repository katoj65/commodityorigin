<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // The upload-size check in ValidatePostSize fires before routing —
        // and therefore before any field-level `max:` validation rule runs
        // — so an oversized file (or set of files) would otherwise surface
        // as a raw, unstyled exception page instead of a normal flashed
        // form error.
        $exceptions->render(function (PostTooLargeException $e) {
            return back()->with('error', 'One or more files are too large. Images must be under 2 MB — please resize and try again.');
        });
    })->create();
