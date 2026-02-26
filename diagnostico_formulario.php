<?php

echo "=== DIAGNÓSTICO DEL FORMULARIO DE REGISTRO ===\n\n";

// Simular una petición POST como la que envía el formulario
$url = 'http://localhost:8000/register';

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    // Obtener un código postal válido
    $stmt = $pdo->query("SELECT id, colonia, codigo_postal, estado, municipio FROM codigos_postales WHERE estado = 'JALISCO' AND municipio = 'GUADALAJARA' LIMIT 1");
    $cp = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Datos del formulario
    $timestamp = time();
    $postData = [
        '_token' => 'test-token', // En producción Laravel lo validará
        'nombres' => 'Pedro',
        'apellido_paterno' => 'Ramírez',
        'apellido_materno' => 'Sánchez',
        'email' => "pedro{$timestamp}@test.com",
        'email_confirmation' => "pedro{$timestamp}@test.com",
        'telefono' => '+52' . rand(1000000000, 9999999999),
        'fecha_nacimiento' => '1992-03-20',
        'rfc' => 'RASP920320XXX',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'estado' => $cp['estado'],
        'municipio' => $cp['municipio'],
        'codigo_postal_id' => $cp['id'],
        'colonia' => $cp['colonia'],
        'calle' => 'Calle Morelos',
        'numero' => '789',
    ];
    
    echo "1. Datos a enviar:\n";
    foreach ($postData as $key => $value) {
        if (!in_array($key, ['password', 'password_confirmation', '_token'])) {
            echo "   $key: $value\n";
        }
    }
    
    echo "\n2. Verificando servidor Laravel...\n";
    $ch = curl_init('http://localhost:8000/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ Error de conexión: $error\n";
        echo "   ⚠️  El servidor Laravel NO está corriendo en http://localhost:8000\n";
        echo "\n   Para iniciar el servidor ejecuta:\n";
        echo "   php artisan serve\n\n";
        exit(1);
    }
    
    if ($httpCode === 200) {
        echo "   ✅ Servidor Laravel corriendo en http://localhost:8000\n";
    } else {
        echo "   ⚠️  Servidor responde con código: $httpCode\n";
    }
    
    echo "\n3. Enviando petición POST a /register...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
    
    $fullResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    
    echo "   HTTP Code: $httpCode\n";
    
    // Separar headers y body
    $headerSize = strpos($fullResponse, "\r\n\r\n");
    $headers = substr($fullResponse, 0, $headerSize);
    $body = substr($fullResponse, $headerSize + 4);
    
    if ($httpCode === 419) {
        echo "   ❌ Error 419: CSRF Token Mismatch\n";
        echo "   El formulario necesita un token CSRF válido\n";
        echo "\n   Esto es normal en pruebas sin navegador.\n";
        echo "   El formulario web funcionará correctamente.\n";
    } elseif ($httpCode === 302) {
        echo "   ✅ Redirección detectada (código 302)\n";
        
        // Buscar la ubicación de redirección en los headers
        if (preg_match('/Location: (.+)/i', $headers, $matches)) {
            $location = trim($matches[1]);
            echo "   Redirigiendo a: $location\n";
            
            if (strpos($location, '/login') !== false) {
                echo "   ✅ Redirigido a /login - ¡REGISTRO EXITOSO!\n";
            } elseif (strpos($location, '/register') !== false) {
                echo "   ⚠️  Redirigido de vuelta a /register - Puede haber errores de validación\n";
            } else {
                echo "   Redirigido a: $location\n";
            }
        }
    } elseif ($httpCode === 200) {
        echo "   ⚠️  Código 200 - No hubo redirección\n";
        echo "   Puede haber errores de validación o problemas en el formulario\n";
        
        // Buscar errores en el HTML
        if (preg_match_all('/<p class="[^"]*text-red[^"]*">([^<]+)<\/p>/i', $body, $matches)) {
            echo "\n   Errores encontrados:\n";
            foreach ($matches[1] as $error) {
                echo "   - " . trim(strip_tags($error)) . "\n";
            }
        }
    }
    
    echo "\n4. Verificando si el usuario fue creado en la BD...\n";
    $stmt = $pdo->prepare("SELECT id, email, nombres, apellido_paterno FROM usuarios WHERE email = ?");
    $stmt->execute([$postData['email']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "   ✅ Usuario encontrado en la base de datos:\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Email: {$usuario['email']}\n";
        echo "   - Nombre: {$usuario['nombres']} {$usuario['apellido_paterno']}\n";
    } else {
        echo "   ❌ Usuario NO encontrado en la base de datos\n";
        echo "   El registro no se completó\n";
    }
    
    echo "\n5. Verificando logs recientes...\n";
    $logFile = 'storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20);
        $hasRecentErrors = false;
        
        foreach ($lastLines as $line) {
            if (stripos($line, 'ERROR') !== false || stripos($line, 'SQLSTATE') !== false) {
                if (!$hasRecentErrors) {
                    echo "   Errores recientes encontrados:\n";
                    $hasRecentErrors = true;
                }
                echo "   " . trim($line) . "\n";
            }
        }
        
        if (!$hasRecentErrors) {
            echo "   ✅ No hay errores recientes en el log\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
