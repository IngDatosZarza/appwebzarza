<?php
// Limpiar opcache de PHP
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache limpiado\n";
} else {
    echo "ℹ OPcache no está habilitado\n";
}

// Limpiar APCu si está disponible
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✓ APCu cache limpiado\n";
}

echo "\n✅ Cachés de PHP limpiados exitosamente\n";
echo "Por favor, recarga la página del perfil en tu navegador.\n";
