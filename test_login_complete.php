<?php
// Script para hacer login como admin y probar acceso a transacciones

// Simular una petición POST de login
$loginData = [
    'email' => 'admin@test.com',
    'password' => 'admin123',
    '_token' => 'test'
];

// Usar cURL para hacer login
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($loginData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEJAR => 'login_test_cookies.txt',
    CURLOPT_COOKIEFILE => 'login_test_cookies.txt',
    CURLOPT_HEADER => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== LOGIN RESPONSE ===\n";
echo "HTTP Code: $loginHttpCode\n";
echo "Response length: " . strlen($loginResponse) . " chars\n";

// Extraer headers
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE) ?: strpos($loginResponse, "\r\n\r\n") + 4;
$headers = substr($loginResponse, 0, $headerSize);
$body = substr($loginResponse, $headerSize);

echo "Headers contain 'Location'? " . (strpos($headers, 'Location:') !== false ? 'Yes' : 'No') . "\n";

// Ahora probar acceso a transacciones
echo "\n=== TESTING ADMIN TRANSACTIONS ACCESS ===\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/admin/transacciones',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEFILE => 'login_test_cookies.txt',
    CURLOPT_HEADER => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$transResponse = curl_exec($ch);
$transHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $transHttpCode\n";
echo "Response contains 'transacciones'? " . (stripos($transResponse, 'transacciones') !== false ? 'Yes' : 'No') . "\n";
echo "Response contains 'login'? " . (stripos($transResponse, 'login') !== false ? 'Yes' : 'No') . "\n";

if ($transHttpCode === 302) {
    $headerLines = explode("\n", $transResponse);
    foreach ($headerLines as $line) {
        if (stripos($line, 'location:') === 0) {
            echo "Redirect to: " . trim(substr($line, 9)) . "\n";
            break;
        }
    }
}

// Mostrar primeros 500 caracteres de la respuesta
echo "First 500 chars:\n";
echo substr($transResponse, 0, 500) . "...\n";

// Limpiar archivo de cookies
if (file_exists('login_test_cookies.txt')) {
    unlink('login_test_cookies.txt');
}
?>