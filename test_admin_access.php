<?php
// Script para probar el acceso a transacciones con sesión de admin

session_start();

// Simular una sesión de admin
$_SESSION['user_authenticated'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['user_nombre'] = 'Admin';
$_SESSION['user_apellido'] = 'Sistema';
$_SESSION['user_email'] = 'admin@test.com';
$_SESSION['user_rol'] = 'admin';
$_SESSION['user_puntos'] = 0;

echo "=== SESIÓN DE ADMIN SIMULADA ===\n";
echo "Authenticated: " . ($_SESSION['user_authenticated'] ? 'Sí' : 'No') . "\n";
echo "Rol: " . $_SESSION['user_rol'] . "\n";
echo "Nombre: " . $_SESSION['user_nombre'] . "\n";
echo "Email: " . $_SESSION['user_email'] . "\n";

// Probar acceso con cURL usando la sesión
$cookieFile = tempnam(sys_get_temp_dir(), 'test_cookies');
$sessionName = 'sistema-puntos-fidelidad-session';
$sessionId = session_id();

echo "\n=== PROBANDO ACCESO A TRANSACCIONES ===\n";
echo "Session ID: " . $sessionId . "\n";
echo "Cookie File: " . $cookieFile . "\n";

// Crear cookie manualmente
file_put_contents($cookieFile, "localhost\tFALSE\t/\tFALSE\t0\t{$sessionName}\t{$sessionId}\n");

// Probar acceso
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/admin/transacciones',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_HEADER => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response (first 500 chars):\n";
echo substr($response, 0, 500) . "...\n";

// Limpiar
unlink($cookieFile);
session_destroy();
?>