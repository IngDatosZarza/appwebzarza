<?php
// Script de depuración para búsqueda por RFC
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

// Ingresa un RFC para probar
$rfc = isset($_GET['rfc']) ? $_GET['rfc'] : 'XXXX000000XXX';

echo "<h1>Prueba de Búsqueda por RFC</h1>";
echo "<form method='get'>";
echo "RFC: <input type='text' name='rfc' value='" . htmlspecialchars($rfc) . "' maxlength='13'>";
echo " <button type='submit'>Buscar</button>";
echo "</form>";
echo "<hr>";

if ($rfc && $rfc !== 'XXXX000000XXX') {
    $result = api_request('GET', 'Customer', ['TaxRegNr' => $rfc]);

    echo "<h2>URL consultada:</h2>";
    echo "<pre>" . BASE_URL . "/Customer?TaxRegNr=" . urlencode($rfc) . "</pre>";

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
                    echo "Código: {$customer['CustomerCode']} - ";
                    echo "Nombre: " . ($customer['PersonName'] ?? 'N/A') . " ";
                    echo ($customer['PersonLastName'] ?? '') . " ";
                    echo ($customer['PersonLastName2'] ?? '');
                } else {
                    echo "Sin CustomerCode";
                }
                echo "</li>";
            }
            echo "</ul>";
        } elseif (isset($result['json']['CustomerCode'])) {
            echo "Cliente único<br>";
            echo "<strong>Código:</strong> {$result['json']['CustomerCode']}<br>";
            echo "<strong>Nombre:</strong> " . ($result['json']['PersonName'] ?? 'N/A') . " ";
            echo ($result['json']['PersonLastName'] ?? '') . " ";
            echo ($result['json']['PersonLastName2'] ?? '');
        } else {
            echo "Estructura desconocida - Keys: ";
            echo "<pre>" . print_r(array_keys($result['json']), true) . "</pre>";
        }
        echo "</li>";
    }
    echo "</ul>";

    echo "<h2>Validación:</h2>";
    $has_valid_customer = false;
    if (isset($result['json']) && is_array($result['json']) && !empty($result['json'])) {
        if (isset($result['json'][0])) {
            $has_valid_customer = isset($result['json'][0]['CustomerCode']) && !empty($result['json'][0]['CustomerCode']);
        } elseif (isset($result['json']['CustomerCode'])) {
            $has_valid_customer = !empty($result['json']['CustomerCode']);
        }
    }

    echo "<div style='padding: 20px; background: " . ($has_valid_customer ? '#d4edda' : '#f8d7da') . "; border: 2px solid " . ($has_valid_customer ? '#28a745' : '#dc3545') . ";'>";
    echo "<h3>" . ($has_valid_customer ? '✅ CLIENTE ENCONTRADO' : '❌ CLIENTE NO ENCONTRADO') . "</h3>";
    echo "</div>";
}
?>
