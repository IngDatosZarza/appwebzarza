<?php

echo "=== APLICANDO PARCHE TEMPORAL PARA RUTAS DE RED ===\n\n";

// Archivo a parchear
$file = 'Z:/appwebzarza/vendor/laravel/framework/src/Illuminate/Foundation/ProviderRepository.php';

if (!file_exists($file)) {
    echo "✗ Archivo no encontrado: $file\n";
    exit(1);
}

// Leer contenido
$content = file_get_contents($file);

// Verificar si ya está parcheado
if (strpos($content, 'PATCHED FOR UNC') !== false) {
    echo "⚠ El archivo ya está parcheado\n";
    exit(0);
}

// Buscar la línea problemática (espaciado exacto del archivo original)
$original = "        if (! is_writable(\$dirname = dirname(\$this->manifestPath))) {
            throw new Exception(\"The {\$dirname} directory must be present and writable.\");
        }";

// Nuevo código (intenta escribir directamente)
$patched = "// PATCHED FOR UNC/Network drives - is_writable() fails on Windows network paths
        \$dirname = dirname(\$this->manifestPath);
        if (! is_writable(\$dirname)) {
            // Try to write anyway - is_writable() is unreliable on Windows UNC paths
            try {
                \$testFile = \$dirname . '/.__test_write__';
                if (@file_put_contents(\$testFile, 'test') === false) {
                    throw new Exception(\"The {\$dirname} directory must be present and writable.\");
                }
                @unlink(\$testFile);
            } catch (Exception \$e) {
                throw new Exception(\"The {\$dirname} directory must be present and writable.\");
            }
        }";

// Aplicar parche
$newContent = str_replace($original, $patched, $content);

if ($newContent === $content) {
    echo "✗ No se pudo encontrar el código a parchear\n";
    echo "Puede que Laravel haya cambiado en esta versión\n";
    exit(1);
}

// Crear respaldo
$backup = $file . '.backup_' . date('Y-m-d_His');
if (copy($file, $backup)) {
    echo "✓ Respaldo creado: $backup\n";
} else {
    echo "✗ No se pudo crear respaldo\n";
    exit(1);
}

// Escribir archivo parcheado
if (file_put_contents($file, $newContent) !== false) {
    echo "✓ Parche aplicado exitosamente\n";
    echo "\n=== PARCHE COMPLETADO ===\n";
    echo "El archivo original se respaldó en:\n";
    echo "$backup\n";
    echo "\nPara revertir el parche:\n";
    echo "copy \"$backup\" \"$file\"\n";
} else {
    echo "✗ Error al escribir el archivo parcheado\n";
    exit(1);
}
