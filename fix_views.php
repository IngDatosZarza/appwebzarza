<?php
/**
 * Script para reparar el sistema de vistas de Laravel
 */

echo "🔧 REPARANDO SISTEMA DE VISTAS\n";
echo "==============================\n\n";

try {
    // 1. Verificar y crear directorios
    echo "1. Verificando directorios de vistas...\n";
    
    $directories = [
        'resources/views',
        'storage/framework/views',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/logs'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "   ✅ Creado: $dir\n";
            } else {
                echo "   ❌ Error creando: $dir\n";
            }
        } else {
            echo "   ✅ Existe: $dir\n";
        }
    }
    
    // 2. Verificar permisos de escritura
    echo "\n2. Verificando permisos de escritura...\n";
    
    $writableDirs = [
        'storage/framework/views',
        'storage/framework/cache',
        'storage/logs'
    ];
    
    foreach ($writableDirs as $dir) {
        if (is_writable($dir)) {
            echo "   ✅ Escribible: $dir\n";
        } else {
            if (chmod($dir, 0755)) {
                echo "   ✅ Permisos corregidos: $dir\n";
            } else {
                echo "   ⚠️ No se pudo corregir: $dir\n";
            }
        }
    }
    
    // 3. Limpiar cache de vistas compiladas
    echo "\n3. Limpiando cache de vistas...\n";
    
    $viewCache = 'storage/framework/views';
    $cacheFiles = glob($viewCache . '/*.php');
    
    $removedCount = 0;
    foreach ($cacheFiles as $file) {
        if (unlink($file)) {
            $removedCount++;
        }
    }
    
    echo "   ✅ $removedCount archivos de cache eliminados\n";
    
    // 4. Actualizar cache de configuración con la vista
    echo "\n4. Actualizando cache de configuración...\n";
    
    $configCache = 'bootstrap/cache/config.php';
    
    if (file_exists($configCache)) {
        // Leer configuración actual
        $config = include $configCache;
        
        // Agregar configuración de vista
        $config['view'] = [
            'paths' => [
                realpath('resources/views')
            ],
            'compiled' => realpath('storage/framework/views')
        ];
        
        // Guardar configuración actualizada
        $configContent = '<?php' . "\n\nreturn " . var_export($config, true) . ';';
        file_put_contents($configCache, $configContent);
        
        echo "   ✅ Cache de configuración actualizado\n";
    } else {
        echo "   ⚠️ Cache de configuración no encontrado\n";
    }
    
    // 5. Crear vista de prueba básica si no existe
    echo "\n5. Verificando vistas básicas...\n";
    
    $basicViews = [
        'resources/views/welcome.blade.php' => '<!DOCTYPE html>
<html>
<head>
    <title>Laravel App</title>
</head>
<body>
    <h1>¡Laravel está funcionando!</h1>
    <p>Sistema de puntos de fidelidad con QR</p>
</body>
</html>',
        
        'resources/views/layouts/app.blade.php' => '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield(\'title\', \'Sistema de Puntos\')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        @yield(\'content\')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>'
    ];
    
    foreach ($basicViews as $viewPath => $content) {
        $dir = dirname($viewPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if (!file_exists($viewPath)) {
            file_put_contents($viewPath, $content);
            echo "   ✅ Creada: $viewPath\n";
        } else {
            echo "   ✅ Existe: $viewPath\n";
        }
    }
    
    // 6. Verificar configuración final
    echo "\n6. Verificación final...\n";
    
    $checks = [
        'Config view.php existe' => file_exists('config/view.php'),
        'Directorio views existe' => is_dir('resources/views'),
        'Directorio compiled existe' => is_dir('storage/framework/views'),
        'Directorio compiled es escribible' => is_writable('storage/framework/views'),
        'Vista welcome existe' => file_exists('resources/views/welcome.blade.php')
    ];
    
    $allGood = true;
    foreach ($checks as $check => $result) {
        $status = $result ? '✅' : '❌';
        echo "   $status $check\n";
        if (!$result) $allGood = false;
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    
    if ($allGood) {
        echo "🎉 ¡SISTEMA DE VISTAS REPARADO EXITOSAMENTE!\n";
        echo "\n📋 Próximos pasos:\n";
        echo "   1. Reiniciar el servidor Laravel\n";
        echo "   2. Probar http://localhost:8000\n";
        echo "\n✨ El error de ViewServiceProvider debería estar resuelto.\n";
    } else {
        echo "⚠️ Reparación parcial completada.\n";
        echo "   Algunos problemas persisten, pero debería funcionar mejor.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR DURANTE LA REPARACIÓN:\n";
    echo "   " . $e->getMessage() . "\n";
}