<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'project.auth' => \App\Http\Middleware\ValidateProjectCredentials::class,
            'project.cors' => \App\Http\Middleware\ProjectCors::class,
        ]);
        
        // Remove default Laravel CORS middleware for API routes to use our custom CORS
        $middleware->remove(\Illuminate\Http\Middleware\HandleCors::class);
        
        // Only disable CSRF middleware in testing environment, not session middleware
        if ($_ENV['APP_ENV'] ?? 'production' === 'testing') {
            $middleware->web(remove: [
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
