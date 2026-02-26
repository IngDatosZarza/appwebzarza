<?php

echo "=== PRUEBA DE APIs DE CÓDIGOS POSTALES ===\n\n";

$baseUrl = 'http://localhost:8000/api/codigos-postales';

// Test 1: Obtener estados
echo "1. Probando GET /estados\n";
$ch = curl_init("$baseUrl/estados");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Estados obtenidos: " . count($data['data']) . "\n";
        echo "   Primeros 5: " . implode(", ", array_slice($data['data'], 0, 5)) . "\n";
    }
} else {
    echo "   ❌ Error\n";
}

echo "\n";

// Test 2: Obtener municipios de un estado
echo "2. Probando GET /municipios?estado=JALISCO\n";
$ch = curl_init("$baseUrl/municipios?estado=" . urlencode("JALISCO"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Municipios obtenidos: " . count($data['data']) . "\n";
        echo "   Primeros 5: " . implode(", ", array_slice($data['data'], 0, 5)) . "\n";
    }
} else {
    echo "   ❌ Error\n";
}

echo "\n";

// Test 3: Obtener colonias
echo "3. Probando GET /colonias?estado=JALISCO&municipio=GUADALAJARA\n";
$ch = curl_init("$baseUrl/colonias?estado=" . urlencode("JALISCO") . "&municipio=" . urlencode("GUADALAJARA"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status: $httpCode\n";
if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Colonias obtenidas: " . count($data['data']) . "\n";
        if (count($data['data']) > 0) {
            echo "   Primera colonia: " . $data['data'][0]['colonia'] . " (CP: " . $data['data'][0]['codigo_postal'] . ")\n";
        }
    }
} else {
    echo "   ❌ Error\n";
}

echo "\n";
echo "=== FIN DE PRUEBAS ===\n";
