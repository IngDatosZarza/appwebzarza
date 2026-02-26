<?php
// Verificar usuarios en la base de datos

echo "=== Verificación de Usuarios ===\n";

try {
    $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    echo "✅ Conexión a base de datos OK\n\n";
    
    // Listar todos los usuarios
    $stmt = $pdo->query("SELECT id, nombres, apellido_paterno, email, rol FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "❌ No hay usuarios en la base de datos\n";
        echo "💡 Necesitas ejecutar las migraciones y seeders\n";
    } else {
        echo "📋 Usuarios encontrados:\n";
        foreach ($usuarios as $usuario) {
            echo "  ID: {$usuario['id']}\n";
            echo "  Nombre: {$usuario['nombres']} {$usuario['apellido_paterno']}\n";
            echo "  Email: {$usuario['email']}\n";
            echo "  Rol: {$usuario['rol']}\n";
            echo "  ---\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Verificación Completada ===\n";
?>