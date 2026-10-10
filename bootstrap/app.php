<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'login' => \App\Http\Middleware\Login::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Merged into every exception's log entry (file + Slack) so a
        // regression can be traced back to who/what triggered it without
        // reproducing it - there was no request/user context on any log
        // line before this.
        $exceptions->context(fn () => [
            'url' => request()?->fullUrl(),
            'user_id' => auth()->id(),
            'role' => auth()->user()?->role,
        ]);
    })->create();
