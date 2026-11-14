<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Logged-in users who open a guest-only page (login, register) go to their own dashboard.
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()->homeUrl());

        $middleware->alias([
            'shop.subscription' => \App\Http\Middleware\EnsureShopSubscriptionIsActive::class,
            'admin.role' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return $response;
            }

            $status = $response->getStatusCode();

            // Expired CSRF token: send the user back with a friendly message instead of a dead end.
            if ($status === 419) {
                return back()->withErrors(['status' => 'Your session expired. Please try again.']);
            }

            // Branded error pages. 500/503 stay as Laravel's debug page while debugging.
            $branded = in_array($status, [403, 404], true)
                || (in_array($status, [500, 503], true) && ! config('app.debug'));

            if ($branded) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
