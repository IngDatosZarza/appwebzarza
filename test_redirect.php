<?php

echo "=== TEST DE REDIRECCIÓN ===\n\n";

// Simular registro y capturar redirección
$url = 'http://localhost:8000/register';

// Primero obtener el CSRF token desde el formulario GET
echo "1. Obteniendo formulario de registro para CSRF token...\n";
$ch = curl_init('http://localhost:8000/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookie.txt');
$formHtml = curl_exec($ch);
curl_close($ch);

// Extraer token CSRF
preg_match('/name="_token"\s+value="([^"]+)"/', $formHtml, $matches);
$csrfToken = $matches[1] ?? '';

if ($csrfToken) {
    echo "   ✅ Token CSRF obtenido: " . substr($csrfToken, 0, 20) . "...\n\n";
} else {
    echo "   ❌ No se pudo obtener token CSRF\n\n";
}

// Datos del formulario
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    $stmt = $pdo->query("SELECT id, colonia, codigo_postal, estado, municipio FROM codigos_postales WHERE estado = 'JALISCO' AND municipio = 'GUADALAJARA' LIMIT 1");
    $cp = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $timestamp = time();
    $postData = [
        '_token' => $csrfToken,
        'nombres' => 'María',
        'apellido_paterno' => 'González',
        'apellido_materno' => 'Martínez',
        'email' => "maria{$timestamp}@test.com",
        'email_confirmation' => "maria{$timestamp}@test.com",
        'telefono' => '+52' . rand(1000000000, 9999999999),
        'fecha_nacimiento' => '1995-06-15',
        'rfc' => 'GOMM950615ABC',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'estado' => $cp['estado'],
        'municipio' => $cp['municipio'],
        'codigo_postal_id' => $cp['id'],
        'colonia' => $cp['colonia'],
        'calle' => 'Av. Revolución',
        'numero' => '456',
    ];
    
    echo "2. Enviando petición POST con CSRF token...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // NO seguir redirecciones automáticamente
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookie.txt');
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Activar modo verbose
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    echo "   HTTP Status: {$info['http_code']}\n";
    echo "   Redirect URL: " . ($info['redirect_url'] ?: 'ninguna') . "\n\n";
    
    // Separar headers y body
    list($headers, $body) = explode("\r\n\r\n", $response, 2);
    
    echo "3. Headers de respuesta:\n";
    foreach (explode("\n", $headers) as $header) {
        $header = trim($header);
        if ($header && (
            stripos($header, 'Location') !== false ||
            stripos($header, 'Set-Cookie') !== false ||
            stripos($header, 'HTTP/') === 0
        )) {
            echo "   $header\n";
        }
    }
    
    echo "\n4. Verificando usuario en BD...\n";
    $stmt = $pdo->prepare("SELECT id, email, nombres FROM usuarios WHERE email = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$postData['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "   ✅ Usuario creado: ID {$user['id']} - {$user['nombres']} ({$user['email']})\n";
    } else {
        echo "   ❌ Usuario NO creado\n";
    }
    
    echo "\n5. Análisis del problema:\n";
    if ($info['http_code'] === 200) {
        echo "   ❌ El servidor devuelve 200 en vez de 302 (redirect)\n";
        echo "   ⚠️  Esto significa que algo está interfiriendo con redirect()\n";
        echo "   Posibles causas:\n";
        echo "   - Output buffer issues\n";
        echo "   - Headers ya enviados\n";
        echo "   - Middleware que captura la respuesta\n";
        
        // Buscar errores en el body
        if (preg_match('/<div[^>]*class="[^"]*alert[^"]*danger[^"]*"[^>]*>(.*?)<\/div>/is', $body, $matches)) {
            echo "\n   Error encontrado en página:\n";
            echo "   " . strip_tags($matches[1]) . "\n";
        }
    } elseif ($info['http_code'] === 302) {
        echo "   ✅ Redirección funcionando correctamente\n";
    } elseif ($info['http_code'] === 419) {
        echo "   ❌ Error 419 - CSRF Token Mismatch\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
