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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/login',
            '/register',
            '/admin/login',
        ]);
        
        // Registrar middleware personalizado
        $middleware->alias([
            'custom.auth' => \App\Http\Middleware\CustomAuth::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.auth' => \App\Http\Middleware\AdminAuthMiddleware::class,
            'superadmin' => \App\Http\Middleware\SuperadminMiddleware::class,
            'admin.sucursal' => \App\Http\Middleware\AdminSucursalMiddleware::class,
            'qa.access' => \App\Http\Middleware\QaAccessMiddleware::class,
        ]);

        // Aplicar QA Access globalmente en web (solo activo cuando QA_ACCESS_PASSWORD esté definido)
        $middleware->appendToGroup('web', \App\Http\Middleware\QaAccessMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
