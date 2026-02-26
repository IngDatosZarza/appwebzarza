<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
$pdo->exec('SET search_path TO appweb, public');

echo "Columnas de la tabla usuarios:\n";
$result = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'usuarios' AND table_schema = 'appweb'");
while ($row = $result->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}

echo "\nDatos actuales:\n";
$users = $pdo->query("SELECT id, nombres, apellido_paterno, email, rol FROM usuarios");
while ($user = $users->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$user['id']}, Nombre: {$user['nombres']} {$user['apellido_paterno']}, Email: {$user['email']}, Rol: {$user['rol']}\n";
}
?>