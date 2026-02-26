<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== ESTRUCTURA TABLA DIRECCIONES ===\n\n";
    
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'direcciones' 
        ORDER BY ordinal_position
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf(
            "%-25s | %-20s | Nullable: %-3s\n",
            $row['column_name'],
            $row['data_type'],
            $row['is_nullable']
        );
    }
    
    echo "\n=== VERIFICAR CAMPOS NECESARIOS ===\n\n";
    
    $camposNecesarios = ['codigo_postal_id', 'municipio'];
    
    foreach ($camposNecesarios as $campo) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.columns 
            WHERE table_name = 'direcciones' AND column_name = ?
        ");
        $stmt->execute([$campo]);
        $existe = $stmt->fetchColumn() > 0;
        
        echo "$campo: " . ($existe ? "✅ EXISTE" : "❌ NO EXISTE") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
