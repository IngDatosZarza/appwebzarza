<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
$pdo->exec('SET search_path TO appweb, public');

echo "Columnas de la tabla puntos:\n";
$result = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'puntos' AND table_schema = 'appweb'");
while ($row = $result->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}
?>