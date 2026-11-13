<?php

use App\Http\Middleware\AdminMiddleware;
// use App\Http\Middleware\IsTimerStop;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trimStrings(except: ['code', 'input']);

        //
        // Register the middleware with a short alias for use in routes
        $middleware->alias([
            'is.admin' => AdminMiddleware::class,
            // 'is.timer.stop' => IsTimerStop::class,
            'timer_stop' => \App\Http\Middleware\IsTimerStop::class,
            // ... any other aliases you have ...
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
