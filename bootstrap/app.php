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
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            $status = $response->getStatusCode();

            // En producción, mostrar páginas de error personalizadas para errores HTTP comunes
            if (!app()->hasDebugModeEnabled() && in_array($status, [403, 404, 405, 419, 429, 500, 503])) {
                if (view()->exists("errors.{$status}")) {
                    return response()->view("errors.{$status}", [], $status);
                }
            }

            return $response;
        });
    })->create();
