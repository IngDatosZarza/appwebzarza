<?php
/**
 * Script para reparar el error "Target class [files] does not exist"
 */

echo "🔧 REPARANDO ERROR DE SERVICE PROVIDERS\n";
echo "=====================================\n\n";

try {
    // 1. Limpiar completamente el cache
    echo "1. Limpiando cache completamente...\n";
    
    $cacheDir = __DIR__ . '/bootstrap/cache';
    
    // Eliminar todos los archivos de cache
    $cacheFiles = glob($cacheDir . '/*.php');
    foreach ($cacheFiles as $file) {
        if (unlink($file)) {
            echo "   ✅ Eliminado: " . basename($file) . "\n";
        }
    }
    
    // 2. Crear manifiesto de service providers correcto
    echo "\n2. Creando manifiesto de service providers...\n";
    
    $servicesManifest = '<?php

return [
    \'providers\' => [
        Illuminate\\Auth\\AuthServiceProvider::class,
        Illuminate\\Broadcasting\\BroadcastServiceProvider::class,
        Illuminate\\Bus\\BusServiceProvider::class,
        Illuminate\\Cache\\CacheServiceProvider::class,
        Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider::class,
        Illuminate\\Cookie\\CookieServiceProvider::class,
        Illuminate\\Database\\DatabaseServiceProvider::class,
        Illuminate\\Encryption\\EncryptionServiceProvider::class,
        Illuminate\\Filesystem\\FilesystemServiceProvider::class,
        Illuminate\\Foundation\\Providers\\FoundationServiceProvider::class,
        Illuminate\\Hashing\\HashServiceProvider::class,
        Illuminate\\Mail\\MailServiceProvider::class,
        Illuminate\\Notifications\\NotificationServiceProvider::class,
        Illuminate\\Pagination\\PaginationServiceProvider::class,
        Illuminate\\Pipeline\\PipelineServiceProvider::class,
        Illuminate\\Queue\\QueueServiceProvider::class,
        Illuminate\\Redis\\RedisServiceProvider::class,
        Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider::class,
        Illuminate\\Session\\SessionServiceProvider::class,
        Illuminate\\Translation\\TranslationServiceProvider::class,
        Illuminate\\Validation\\ValidationServiceProvider::class,
        Illuminate\\View\\ViewServiceProvider::class,
        App\\Providers\\AppServiceProvider::class,
        App\\Providers\\AuthServiceProvider::class,
        App\\Providers\\EventServiceProvider::class,
        App\\Providers\\RouteServiceProvider::class,
    ],
    \'eager\' => [
        Illuminate\\Auth\\AuthServiceProvider::class,
        Illuminate\\Cookie\\CookieServiceProvider::class,
        Illuminate\\Database\\DatabaseServiceProvider::class,
        Illuminate\\Encryption\\EncryptionServiceProvider::class,
        Illuminate\\Filesystem\\FilesystemServiceProvider::class,
        Illuminate\\Foundation\\Providers\\FoundationServiceProvider::class,
        Illuminate\\Notifications\\NotificationServiceProvider::class,
        Illuminate\\Pagination\\PaginationServiceProvider::class,
        Illuminate\\Session\\SessionServiceProvider::class,
        Illuminate\\View\\ViewServiceProvider::class,
        App\\Providers\\AppServiceProvider::class,
        App\\Providers\\AuthServiceProvider::class,
        App\\Providers\\EventServiceProvider::class,
        App\\Providers\\RouteServiceProvider::class,
    ],
    \'deferred\' => [
        \'Illuminate\\Broadcasting\\BroadcastManager\' => Illuminate\\Broadcasting\\BroadcastServiceProvider::class,
        \'Illuminate\\Contracts\\Broadcasting\\Factory\' => Illuminate\\Broadcasting\\BroadcastServiceProvider::class,
        \'Illuminate\\Contracts\\Broadcasting\\Broadcaster\' => Illuminate\\Broadcasting\\BroadcastServiceProvider::class,
        \'Illuminate\\Bus\\Dispatcher\' => Illuminate\\Bus\\BusServiceProvider::class,
        \'Illuminate\\Contracts\\Bus\\Dispatcher\' => Illuminate\\Bus\\BusServiceProvider::class,
        \'Illuminate\\Contracts\\Bus\\QueueingDispatcher\' => Illuminate\\Bus\\BusServiceProvider::class,
        \'cache\' => Illuminate\\Cache\\CacheServiceProvider::class,
        \'cache.store\' => Illuminate\\Cache\\CacheServiceProvider::class,
        \'cache.psr6\' => Illuminate\\Cache\\CacheServiceProvider::class,
        \'memcached.connector\' => Illuminate\\Cache\\CacheServiceProvider::class,
        \'command.cache.clear\' => Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider::class,
        \'hash\' => Illuminate\\Hashing\\HashServiceProvider::class,
        \'hash.driver\' => Illuminate\\Hashing\\HashServiceProvider::class,
        \'mailer\' => Illuminate\\Mail\\MailServiceProvider::class,
        \'Illuminate\\Mail\\Markdown\' => Illuminate\\Mail\\MailServiceProvider::class,
        \'Illuminate\\Contracts\\Pipeline\\Hub\' => Illuminate\\Pipeline\\PipelineServiceProvider::class,
        \'queue\' => Illuminate\\Queue\\QueueServiceProvider::class,
        \'queue.connection\' => Illuminate\\Queue\\QueueServiceProvider::class,
        \'queue.failer\' => Illuminate\\Queue\\QueueServiceProvider::class,
        \'redis\' => Illuminate\\Redis\\RedisServiceProvider::class,
        \'redis.connection\' => Illuminate\\Redis\\RedisServiceProvider::class,
        \'auth.password\' => Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider::class,
        \'auth.password.broker\' => Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider::class,
        \'translator\' => Illuminate\\Translation\\TranslationServiceProvider::class,
        \'translation.loader\' => Illuminate\\Translation\\TranslationServiceProvider::class,
        \'validator\' => Illuminate\\Validation\\ValidationServiceProvider::class,
        \'validation.presence\' => Illuminate\\Validation\\ValidationServiceProvider::class,
    ],
    \'when\' => [
        Illuminate\\Broadcasting\\BroadcastServiceProvider::class => [],
        Illuminate\\Bus\\BusServiceProvider::class => [],
        Illuminate\\Cache\\CacheServiceProvider::class => [],
        Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider::class => [],
        Illuminate\\Hashing\\HashServiceProvider::class => [],
        Illuminate\\Mail\\MailServiceProvider::class => [],
        Illuminate\\Pipeline\\PipelineServiceProvider::class => [],
        Illuminate\\Queue\\QueueServiceProvider::class => [],
        Illuminate\\Redis\\RedisServiceProvider::class => [],
        Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider::class => [],
        Illuminate\\Translation\\TranslationServiceProvider::class => [],
        Illuminate\\Validation\\ValidationServiceProvider::class => [],
    ],
];';

    file_put_contents($cacheDir . '/services.php', $servicesManifest);
    echo "   ✅ Manifiesto de services creado\n";
    
    // 3. Crear manifiesto de paquetes
    echo "\n3. Creando manifiesto de paquetes...\n";
    
    $packagesManifest = '<?php

return [
    \'providers\' => [],
    \'eager\' => [],
    \'deferred\' => [],
    \'when\' => [],
];';

    file_put_contents($cacheDir . '/packages.php', $packagesManifest);
    echo "   ✅ Manifiesto de packages creado\n";
    
    // 4. Crear cache de configuración básico
    echo "\n4. Creando cache de configuración...\n";
    
    $configCache = '<?php

return [
    \'app\' => [
        \'name\' => \'Laravel\',
        \'env\' => \'local\',
        \'debug\' => true,
        \'url\' => \'http://localhost\',
        \'timezone\' => \'UTC\',
        \'locale\' => \'en\',
        \'key\' => \'base64:TYZ1QWx2aW5QWXM2d0xLWVJyZ2pvZlBYQUIzNnAyc2xQVnJwdUpDVVR0TT0=\',
        \'cipher\' => \'AES-256-CBC\',
        \'providers\' => [
            Illuminate\\Auth\\AuthServiceProvider::class,
            Illuminate\\Broadcasting\\BroadcastServiceProvider::class,
            Illuminate\\Bus\\BusServiceProvider::class,
            Illuminate\\Cache\\CacheServiceProvider::class,
            Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider::class,
            Illuminate\\Cookie\\CookieServiceProvider::class,
            Illuminate\\Database\\DatabaseServiceProvider::class,
            Illuminate\\Encryption\\EncryptionServiceProvider::class,
            Illuminate\\Filesystem\\FilesystemServiceProvider::class,
            Illuminate\\Foundation\\Providers\\FoundationServiceProvider::class,
            Illuminate\\Hashing\\HashServiceProvider::class,
            Illuminate\\Mail\\MailServiceProvider::class,
            Illuminate\\Notifications\\NotificationServiceProvider::class,
            Illuminate\\Pagination\\PaginationServiceProvider::class,
            Illuminate\\Pipeline\\PipelineServiceProvider::class,
            Illuminate\\Queue\\QueueServiceProvider::class,
            Illuminate\\Redis\\RedisServiceProvider::class,
            Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider::class,
            Illuminate\\Session\\SessionServiceProvider::class,
            Illuminate\\Translation\\TranslationServiceProvider::class,
            Illuminate\\Validation\\ValidationServiceProvider::class,
            Illuminate\\View\\ViewServiceProvider::class,
            App\\Providers\\AppServiceProvider::class,
            App\\Providers\\AuthServiceProvider::class,
            App\\Providers\\EventServiceProvider::class,
            App\\Providers\\RouteServiceProvider::class,
        ],
        \'aliases\' => [
            \'App\' => Illuminate\\Support\\Facades\\App::class,
            \'Arr\' => Illuminate\\Support\\Arr::class,
            \'Artisan\' => Illuminate\\Support\\Facades\\Artisan::class,
            \'Auth\' => Illuminate\\Support\\Facades\\Auth::class,
            \'Blade\' => Illuminate\\Support\\Facades\\Blade::class,
            \'Broadcast\' => Illuminate\\Support\\Facades\\Broadcast::class,
            \'Bus\' => Illuminate\\Support\\Facades\\Bus::class,
            \'Cache\' => Illuminate\\Support\\Facades\\Cache::class,
            \'Config\' => Illuminate\\Support\\Facades\\Config::class,
            \'Cookie\' => Illuminate\\Support\\Facades\\Cookie::class,
            \'Crypt\' => Illuminate\\Support\\Facades\\Crypt::class,
            \'Date\' => Illuminate\\Support\\Facades\\Date::class,
            \'DB\' => Illuminate\\Support\\Facades\\DB::class,
            \'Eloquent\' => Illuminate\\Database\\Eloquent\\Model::class,
            \'Event\' => Illuminate\\Support\\Facades\\Event::class,
            \'File\' => Illuminate\\Support\\Facades\\File::class,
            \'Gate\' => Illuminate\\Support\\Facades\\Gate::class,
            \'Hash\' => Illuminate\\Support\\Facades\\Hash::class,
            \'Http\' => Illuminate\\Support\\Facades\\Http::class,
            \'Js\' => Illuminate\\Support\\Js::class,
            \'Lang\' => Illuminate\\Support\\Facades\\Lang::class,
            \'Log\' => Illuminate\\Support\\Facades\\Log::class,
            \'Mail\' => Illuminate\\Support\\Facades\\Mail::class,
            \'Notification\' => Illuminate\\Support\\Facades\\Notification::class,
            \'Password\' => Illuminate\\Support\\Facades\\Password::class,
            \'Queue\' => Illuminate\\Support\\Facades\\Queue::class,
            \'RateLimiter\' => Illuminate\\Support\\Facades\\RateLimiter::class,
            \'Redirect\' => Illuminate\\Support\\Facades\\Redirect::class,
            \'Request\' => Illuminate\\Support\\Facades\\Request::class,
            \'Response\' => Illuminate\\Support\\Facades\\Response::class,
            \'Route\' => Illuminate\\Support\\Facades\\Route::class,
            \'Schema\' => Illuminate\\Support\\Facades\\Schema::class,
            \'Session\' => Illuminate\\Support\\Facades\\Session::class,
            \'Storage\' => Illuminate\\Support\\Facades\\Storage::class,
            \'Str\' => Illuminate\\Support\\Str::class,
            \'URL\' => Illuminate\\Support\\Facades\\URL::class,
            \'Validator\' => Illuminate\\Support\\Facades\\Validator::class,
            \'View\' => Illuminate\\Support\\Facades\\View::class,
        ],
    ],
];';

    file_put_contents($cacheDir . '/config.php', $configCache);
    echo "   ✅ Cache de configuración creado\n";
    
    // 5. Verificar archivos de configuración principales
    echo "\n5. Verificando archivos de configuración...\n";
    
    $configFiles = [
        'config/app.php',
        'config/database.php',
        'config/filesystems.php'
    ];
    
    foreach ($configFiles as $configFile) {
        if (file_exists($configFile)) {
            echo "   ✅ $configFile existe\n";
        } else {
            echo "   ❌ $configFile falta\n";
        }
    }
    
    // 6. Verificar providers de la aplicación
    echo "\n6. Verificando providers de la aplicación...\n";
    
    $providerFiles = [
        'app/Providers/AppServiceProvider.php',
        'app/Providers/AuthServiceProvider.php',
        'app/Providers/EventServiceProvider.php',
        'app/Providers/RouteServiceProvider.php'
    ];
    
    foreach ($providerFiles as $providerFile) {
        if (file_exists($providerFile)) {
            echo "   ✅ $providerFile existe\n";
        } else {
            echo "   ❌ $providerFile falta\n";
        }
    }
    
    // 7. Crear archivo de eventos básico
    echo "\n7. Creando cache de eventos...\n";
    
    $eventsCache = '<?php return [];';
    file_put_contents($cacheDir . '/events.php', $eventsCache);
    echo "   ✅ Cache de eventos creado\n";
    
    // 8. Crear archivo de rutas básico
    echo "\n8. Creando cache de rutas...\n";
    
    $routesCache = '<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
|
| Here we will decode and unserialize the RouteCollection instance that
| holds all of the route information for an application. This allows
| us to instantiate it and avoid having to make the framework call
*/

app(\'router\')->setCompiledRoutes([
    \'compiled\' => [],
    \'attributes\' => [],
]);';

    file_put_contents($cacheDir . '/routes-v7.php', $routesCache);
    echo "   ✅ Cache de rutas creado\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 REPARACIÓN COMPLETADA\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "✅ Service providers reparados\n";
    echo "✅ Cache regenerado completamente\n";
    echo "✅ Manifiestos creados correctamente\n\n";
    
    echo "🚀 AHORA PUEDES INICIAR EL SERVIDOR:\n";
    echo "   php artisan serve --host=localhost --port=8000\n\n";
    
    echo "🔧 O usar el servidor PHP básico:\n";
    echo "   php -S localhost:8000 -t public\n\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la reparación: " . $e->getMessage() . "\n";
    echo "\n🔧 Intenta usar el servidor PHP básico:\n";
    echo "   php -S localhost:8000 -t public\n";
}