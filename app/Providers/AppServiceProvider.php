<?php

namespace App\Providers;

use App\Mail\Transport\MicrosoftGraphTransport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar encoding UTF-8 para PostgreSQL cuando se establece la conexión
        Event::listen(ConnectionEstablished::class, function ($event) {
            if ($event->connection->getDriverName() === 'pgsql') {
                $event->connection->statement("SET CLIENT_ENCODING TO 'UTF8'");
                $event->connection->statement("SET NAMES 'UTF8'");
            }
        });

        // Registrar el transport de Microsoft Graph API para envío de correo
        Mail::extend('microsoft-graph', function () {
            return new MicrosoftGraphTransport(
                clientId:     config('mail.mailers.microsoft-graph.client_id'),
                clientSecret: config('mail.mailers.microsoft-graph.client_secret'),
                tenantId:     config('mail.mailers.microsoft-graph.tenant_id'),
                fromEmail:    config('mail.mailers.microsoft-graph.from_email'),
            );
        });
    }
}
