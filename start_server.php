<?php
/**
 * Servidor Laravel alternativo que evita problemas de cache
 */

// Configurar directorio de trabajo
chdir(__DIR__);

// Cargar autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Crear aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

// Configurar para desarrollo
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 Servidor Laravel iniciando en http://localhost:8000\n";
echo "📝 Usando método alternativo para evitar problemas de cache\n";
echo "⏹️ Presiona Ctrl+C para detener\n\n";

// Configurar servidor PHP interno
$host = 'localhost';
$port = 8000;
$publicPath = __DIR__ . '/public';

// Cambiar al directorio public
chdir($publicPath);

// Ejecutar servidor
$command = "php -S {$host}:{$port} -t {$publicPath} {$publicPath}/index.php";
echo "Ejecutando: $command\n\n";

// Ejecutar el servidor
passthru($command);