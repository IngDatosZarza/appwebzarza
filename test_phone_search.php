<?php
// Script de depuración para búsqueda por teléfono
define('BASE_URL', 'https://opercompruebausa.oppen.io/genericapi/fidelizacion');
define('BEARER_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiT08iLCJwZXJtaXNzaW9ucyI6W10sImlhdCI6MTc2MjQ2NTk5NCwiZXhwIjo0MjgyNDY1OTk0fQ.NkN5FSDfrwzbgNrt_xYEmijMPnlM3ABaHNmeA6mqZuc');

function api_request(string $method, string $endpoint, array $query = [], $body = null) {
    $base = BASE_URL;
    $token = BEARER_TOKEN;

    $url = rtrim($base, '/') . '/' . ltrim($endpoint, '/');
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $headers = [];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null) {
            $json = json_encode($body, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($json);
        }
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return ['error' => 'cURL error: ' . $err];
    }

    $decoded = json_decode($resp, true);
    return [
        'status' => $httpCode,
        'raw' => $resp,
        'json' => $decoded,
    ];
}

// Probar búsqueda por teléfono
$phone = '1122334455';
echo "<h1>Prueba de Búsqueda por Teléfono: $phone</h1>";
echo "<hr>";

$result = api_request('GET', 'Customer', ['Phone' => $phone]);

echo "<h2>URL consultada:</h2>";
echo "<pre>" . BASE_URL . "/Customer?Phone=" . urlencode($phone) . "</pre>";

echo "<h2>Código HTTP:</h2>";
echo "<pre>{$result['status']}</pre>";

echo "<h2>Respuesta Raw:</h2>";
echo "<pre>" . htmlspecialchars($result['raw']) . "</pre>";

echo "<h2>JSON Decodificado:</h2>";
echo "<pre>" . print_r($result['json'], true) . "</pre>";

echo "<h2>Análisis:</h2>";
echo "<ul>";
echo "<li><strong>¿Tiene 'json'?</strong> " . (isset($result['json']) ? 'SÍ' : 'NO') . "</li>";
echo "<li><strong>¿Es array?</strong> " . (is_array($result['json']) ? 'SÍ' : 'NO') . "</li>";
echo "<li><strong>¿Está vacío?</strong> " . (empty($result['json']) ? 'SÍ' : 'NO') . "</li>";

if (isset($result['json']) && is_array($result['json'])) {
    echo "<li><strong>Tipo de estructura:</strong> ";
    if (isset($result['json'][0])) {
        echo "Array de clientes (múltiples)";
        echo "<ul>";
        foreach ($result['json'] as $index => $customer) {
            echo "<li>Cliente $index: ";
            if (isset($customer['CustomerCode'])) {
                echo "Código: {$customer['CustomerCode']}";
            } else {
                echo "Sin CustomerCode";
            }
            echo "</li>";
        }
        echo "</ul>";
    } elseif (isset($result['json']['CustomerCode'])) {
        echo "Cliente único - Código: {$result['json']['CustomerCode']}";
    } else {
        echo "Estructura desconocida";
        echo "<pre>" . print_r(array_keys($result['json']), true) . "</pre>";
    }
    echo "</li>";
}
echo "</ul>";

// Probar diferentes formatos de teléfono
echo "<hr>";
echo "<h2>Pruebas con diferentes formatos:</h2>";

$formats = [
    '1122334455',
    '+521122334455',
    '+52 1122334455',
    '52 1122334455',
    '(11) 2233-4455',
];

foreach ($formats as $format) {
    $test_result = api_request('GET', 'Customer', ['Phone' => $format]);
    echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0;'>";
    echo "<strong>Formato: $format</strong><br>";
    echo "Status: {$test_result['status']}<br>";
    echo "Encontrado: " . (isset($test_result['json']) && !empty($test_result['json']) && 
          ((isset($test_result['json'][0]['CustomerCode']) && !empty($test_result['json'][0]['CustomerCode'])) ||
           (isset($test_result['json']['CustomerCode']) && !empty($test_result['json']['CustomerCode']))) ? 'SÍ' : 'NO');
    echo "</div>";
}
?>
