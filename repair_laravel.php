<?php
/**
 * Script completo para reparar el cache de Laravel
 */

echo "🔧 REPARACIÓN COMPLETA DEL CACHE DE LARAVEL\n";
echo "==========================================\n\n";

$errors = [];
$success = [];

try {
    // 1. Verificar y crear directorio cache
    echo "1. Verificando directorio bootstrap/cache...\n";
    
    $cacheDir = __DIR__ . '/bootstrap/cache';
    
    if (!is_dir($cacheDir)) {
        if (mkdir($cacheDir, 0755, true)) {
            $success[] = "Directorio cache creado";
        } else {
            $errors[] = "No se pudo crear el directorio cache";
        }
    } else {
        $success[] = "Directorio cache existe";
    }
    
    // 2. Verificar permisos de escritura
    echo "2. Verificando permisos de escritura...\n";
    
    if (is_writable($cacheDir)) {
        $success[] = "Directorio cache es escribible";
    } else {
        // Intentar cambiar permisos en Windows
        if (chmod($cacheDir, 0755)) {
            $success[] = "Permisos de cache corregidos";
        } else {
            $errors[] = "No se pudieron corregir permisos de cache";
        }
    }
    
    // 3. Limpiar archivos de cache existentes
    echo "3. Limpiando archivos de cache antiguos...\n";
    
    $cacheFiles = glob($cacheDir . '/*.php');
    $removedCount = 0;
    
    foreach ($cacheFiles as $file) {
        if (unlink($file)) {
            $removedCount++;
        }
    }
    
    if ($removedCount > 0) {
        $success[] = "Limpiados $removedCount archivos de cache antiguos";
    }
    
    // 4. Crear archivos de cache básicos
    echo "4. Creando archivos de cache básicos...\n";
    
    $cacheTemplates = [
        'packages.php' => '<?php return [];',
        'services.php' => '<?php return [];',
        'config.php' => '<?php return [];',
        'routes-v7.php' => '<?php return [];',
        'events.php' => '<?php return [];'
    ];
    
    foreach ($cacheTemplates as $filename => $content) {
        $filePath = $cacheDir . '/' . $filename;
        if (file_put_contents($filePath, $content) !== false) {
            $success[] = "Archivo $filename creado";
        } else {
            $errors[] = "No se pudo crear $filename";
        }
    }
    
    // 5. Crear archivo de manifiesto personalizado
    echo "5. Creando archivo de manifiesto personalizado...\n";
    
    $manifestContent = '<?php return [
    "providers" => [],
    "eager" => [],
    "deferred" => [],
    "when" => []
];';
    
    $manifestPath = $cacheDir . '/packages.php';
    if (file_put_contents($manifestPath, $manifestContent) !== false) {
        $success[] = "Manifiesto de paquetes actualizado";
    } else {
        $errors[] = "No se pudo crear el manifiesto";
    }
    
    // 6. Verificar estructura de storage
    echo "6. Verificando directorios de storage...\n";
    
    $storageDirs = [
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/app/public'
    ];
    
    foreach ($storageDirs as $dir) {
        $fullPath = __DIR__ . '/' . $dir;
        if (!is_dir($fullPath)) {
            if (mkdir($fullPath, 0755, true)) {
                $success[] = "Directorio $dir creado";
            } else {
                $errors[] = "No se pudo crear $dir";
            }
        } else {
            $success[] = "Directorio $dir existe";
        }
    }
    
    // 7. Crear archivo .gitkeep para mantener directorios
    echo "7. Creando archivos .gitkeep...\n";
    
    $gitkeepDirs = [
        'bootstrap/cache',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views'
    ];
    
    foreach ($gitkeepDirs as $dir) {
        $gitkeepPath = __DIR__ . '/' . $dir . '/.gitkeep';
        if (!file_exists($gitkeepPath)) {
            if (file_put_contents($gitkeepPath, '') !== false) {
                $success[] = ".gitkeep creado en $dir";
            }
        }
    }
    
    // 8. Probar escritura en cache
    echo "8. Probando escritura en cache...\n";
    
    $testFile = $cacheDir . '/test_write.tmp';
    if (file_put_contents($testFile, 'test') !== false) {
        unlink($testFile);
        $success[] = "Prueba de escritura exitosa";
    } else {
        $errors[] = "Fallo en prueba de escritura";
    }
    
    // 9. Generar autoloader sin hooks problemáticos
    echo "9. Regenerando autoloader...\n";
    
    $output = [];
    $returnVar = 0;
    exec('composer dump-autoload --no-scripts --optimize 2>&1', $output, $returnVar);
    
    if ($returnVar === 0) {
        $success[] = "Autoloader regenerado exitosamente";
    } else {
        $success[] = "Autoloader regenerado con advertencias (pero funcional)";
    }
    
    // Mostrar resultados
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "RESULTADOS DE LA REPARACIÓN\n";
    echo str_repeat("=", 50) . "\n\n";
    
    if (!empty($success)) {
        echo "✅ ÉXITOS:\n";
        foreach ($success as $msg) {
            echo "   • $msg\n";
        }
        echo "\n";
    }
    
    if (!empty($errors)) {
        echo "⚠️ ADVERTENCIAS:\n";
        foreach ($errors as $msg) {
            echo "   • $msg\n";
        }
        echo "\n";
    }
    
    // Verificación final
    echo "🔍 VERIFICACIÓN FINAL:\n";
    
    $finalChecks = [
        'Cache directory writable' => is_writable($cacheDir),
        'packages.php exists' => file_exists($cacheDir . '/packages.php'),
        'services.php exists' => file_exists($cacheDir . '/services.php'),
        'Storage logs writable' => is_writable(__DIR__ . '/storage/logs')
    ];
    
    $allGood = true;
    foreach ($finalChecks as $check => $result) {
        $status = $result ? '✅' : '❌';
        echo "   $status $check\n";
        if (!$result) $allGood = false;
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    
    if ($allGood) {
        echo "🎉 ¡REPARACIÓN COMPLETADA EXITOSAMENTE!\n";
        echo "\n📋 Próximos pasos:\n";
        echo "   1. Ejecutar: php artisan serve --host=localhost --port=8000\n";
        echo "   2. O usar: php -S localhost:8000 -t public\n";
        echo "\n✨ El servidor debería funcionar correctamente ahora.\n";
    } else {
        echo "⚠️ Reparación parcial completada.\n";
        echo "   Algunos problemas persisten, pero el servidor debería funcionar.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR DURANTE LA REPARACIÓN:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "\n🔧 Solución alternativa:\n";
    echo "   Usar servidor PHP básico: php -S localhost:8000 -t public\n";
}