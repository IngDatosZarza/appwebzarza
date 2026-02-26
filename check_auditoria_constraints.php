<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
$pdo->exec('SET search_path TO appweb, public');
$stmt = $pdo->query("SELECT constraint_name, check_clause FROM information_schema.check_constraints WHERE constraint_schema = 'appweb' AND constraint_name LIKE '%auditoria%'");
echo "Restricciones de la tabla auditoria:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['constraint_name']}: {$row['check_clause']}\n";
}
?>