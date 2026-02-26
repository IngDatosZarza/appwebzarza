<?php
/**
 * Test de Registro Completo
 * Prueba el flujo completo: API Lazarza -> BD Local
 * 
 * Pasos:
 * 1. Crear cliente en API Lazarza
 * 2. Sincronizar datos en clientes_api_lazarza
 * 3. Crear credenciales en clientes_credenciales
 */

session_start();

// ============================================
// CONFIGURACIÓN
// ============================================

define('BASE_URL', 'https://opercompruebausa.oppen.io/genericapi/fidelizacion');
define('BEARER_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiT08iLCJwZXJtaXNzaW9ucyI6W10sImlhdCI6MTc2MjQ2NTk5NCwiZXhwIjo0MjgyNDY1OTk0fQ.NkN5FSDfrwzbgNrt_xYEmijMPnlM3ABaHNmeA6mqZuc');

// Configuración PostgreSQL
define('DB_HOST', 'localhost');
define('DB_PORT', 5432);
define('DB_NAME', 'postgres');
define('DB_USER', 'appwebuser');
define('DB_PASS', 'appwebpass');
define('DB_SCHEMA', 'appwebzarza');

// ============================================
// CONEXIÓN A LA BASE DE DATOS
// ============================================

$pdo = null;

function conectar_db() {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Establecer schema
        $pdo->exec("SET search_path TO " . DB_SCHEMA . ", public");
        
        return $pdo;
    } catch (Exception $e) {
        return ['error' => 'Error de conexión: ' . $e->getMessage()];
    }
}

// ============================================
// FUNCIONES DE API
// ============================================

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
        return ['error' => 'cURL error: ' . $err, 'status' => 0];
    }

    $decoded = json_decode($resp, true);
    return [
        'status' => $httpCode,
        'raw' => $resp,
        'json' => $decoded,
    ];
}

// ============================================
// FUNCIONES DE SINCRONIZACIÓN
// ============================================

/**
 * Registra un cliente en la API y sincroniza en BD
 */
function registrar_cliente_completo($datos, $password, $canal = 'WEB', $usuario_id = null) {
    global $pdo;
    
    $resultado = [
        'api' => null,
        'bd' => null,
        'credenciales' => null,
        'auditoria' => null,
        'exito' => false,
        'mensaje' => '',
    ];
    
    try {
        // PASO 1: Crear en API (solo con campos que acepta la API)
        echo "📝 Paso 1: Creando cliente en API Lazarza...\n";
        
        // ⚠️ Solo enviamos los campos que la API de Lazarza acepta
        $body = [
            'PersonCustomer' => true,
            'PersonName' => $datos['PersonName'] ?? '',
            'PersonLastName' => $datos['PersonLastName'] ?? '',
            'PersonLastName2' => $datos['PersonLastName2'] ?? '',
            'TaxRegNr' => $datos['TaxRegNr'] ?? '',
            'EmailClubLazarza' => $datos['EmailClubLazarza'] ?? '',
            'Phone' => $datos['Phone'] ?? ''
        ];
        
        // Campos opcionales de la API (excluyendo PersonGender porque no existe en API)
        if (!empty($datos['PersonBirthDate'])) {
            $body['PersonBirthDate'] = $datos['PersonBirthDate'];
        }
        
        echo "   📤 Datos enviados a API (sin contraseña): " . json_encode($body, JSON_UNESCAPED_UNICODE) . "\n";
        echo "   📍 Canal de Registro: $canal\n";
        if ($usuario_id) {
            echo "   👤 Registrado por Usuario ID: $usuario_id\n";
        }
        
        $api_result = api_request('POST', 'Customer', [], $body);

        // Intento de auto-reintento si la API devuelve "Field X not found"
        if (($api_result['status'] >= 400) && isset($api_result['json']['error'])) {
            if (preg_match('/Field\s+(\w+)\s+not\s+found/i', $api_result['json']['error'], $m)) {
                $field = $m[1];
                if (isset($body[$field])) {
                    unset($body[$field]);
                    echo "   🔁 Auto-reintento: quitando campo no soportado por API: $field\n";
                    $api_result = api_request('POST', 'Customer', [], $body);
                }
            }
        }
        $resultado['api'] = $api_result;
        
        if ($api_result['status'] !== 201 && $api_result['status'] !== 200) {
            // Mostrar detalle del error si lo hay
            $api_msg = $api_result['json']['message'] ?? $api_result['json']['error'] ?? 'Error desconocido';
            $resultado['mensaje'] = "❌ Error en API: " . $api_msg;
            return $resultado;
        }
        
        // Extraer código del cliente
        $customer_code = null;
        if (isset($api_result['json']['data']['Code'])) {
            $customer_code = $api_result['json']['data']['Code'];
        } elseif (isset($api_result['json']['Code'])) {
            $customer_code = $api_result['json']['Code'];
        }
        
        if (!$customer_code) {
            $resultado['mensaje'] = "❌ No se obtuvo código del cliente de la API";
            return $resultado;
        }
        
        echo "✅ Cliente creado en API con código: $customer_code\n";
        
        echo "\n📝 Paso 2: Sincronizando en base de datos local...\n";
        echo "   📥 Se guardan TODOS los datos incluyendo ubicación:\n";
        
        if (!$pdo) {
            $pdo = conectar_db();
            if (is_array($pdo)) {
                $resultado['mensaje'] = "❌ Error de conexión a BD";
                return $resultado;
            }
        }
        
        // Preparar datos para insertar en BD (TODOS los campos)
        $insert_sql = "INSERT INTO clientes_api_lazarza (
            code, person_customer, person_name, person_last_name, person_last_name2,
            person_birth_date, tax_reg_nr, person_gender, email_club_lazarza,
            phone, province, city_name, district_name, closed, 
            registration_channel, registered_by_user_id, registration_ip, registration_user_agent,
            last_sync_at
        ) VALUES (
            :code, :person_customer, :person_name, :person_last_name, :person_last_name2,
            :person_birth_date, :tax_reg_nr, :person_gender, :email_club_lazarza,
            :phone, :province, :city_name, :district_name, :closed,
            :registration_channel, :registered_by_user_id, :registration_ip, :registration_user_agent,
            NOW()
        ) RETURNING id";
        
        $stmt = $pdo->prepare($insert_sql);
        
        $stmt->execute([
            ':code' => $customer_code,
            ':person_customer' => true,
            ':person_name' => $datos['PersonName'] ?? '',
            ':person_last_name' => $datos['PersonLastName'] ?? '',
            ':person_last_name2' => $datos['PersonLastName2'] ?? '',
            ':person_birth_date' => !empty($datos['PersonBirthDate']) ? $datos['PersonBirthDate'] : null,
            ':tax_reg_nr' => $datos['TaxRegNr'] ?? null,
            ':person_gender' => $datos['PersonGender'] ?? null,
            ':email_club_lazarza' => $datos['EmailClubLazarza'] ?? '',
            ':phone' => $datos['Phone'] ?? null,
            ':province' => $datos['Province'] ?? null,
            ':city_name' => $datos['CityName'] ?? null,
            ':district_name' => $datos['DistrictName'] ?? null,
            ':closed' => false,
            ':registration_channel' => $canal,
            ':registered_by_user_id' => $usuario_id,
            ':registration_ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            ':registration_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        ]);
        
        $cliente_id = $stmt->fetchColumn();
        echo "✅ Cliente sincronizado en BD local con ID: $cliente_id\n";
        
        // PASO 3: Crear credenciales
        echo "📝 Paso 3: Creando credenciales de acceso...\n";
        
        // Hash con bcrypt
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $cred_sql = "INSERT INTO clientes_credenciales (
            cliente_id, password_hash, password_algorithm, activo, password_changed_at
        ) VALUES (
            :cliente_id, :password_hash, :password_algorithm, :activo, NOW()
        )";
        
        $cred_stmt = $pdo->prepare($cred_sql);
        $cred_stmt->execute([
            ':cliente_id' => $cliente_id,
            ':password_hash' => $password_hash,
            ':password_algorithm' => 'bcrypt',
            ':activo' => true,
        ]);
        
        echo "✅ Credenciales creadas exitosamente\n";
        
        // PASO 4: Registrar en auditoría
        echo "📝 Paso 4: Registrando en auditoría...\n";
        
        $audit_sql = "INSERT INTO auditoria_registros (
            cliente_id, evento_tipo, evento_descripcion, canal, usuario_id,
            ip_address, user_agent, dispositivo_tipo
        ) VALUES (
            :cliente_id, :evento_tipo, :evento_descripcion, :canal, :usuario_id,
            :ip_address, :user_agent, :dispositivo_tipo
        )";
        
        // Detectar tipo de dispositivo
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $dispositivo_tipo = 'DESKTOP';
        if (stripos($user_agent, 'mobile') !== false) {
            $dispositivo_tipo = 'MOBILE';
        } elseif (stripos($user_agent, 'tablet') !== false) {
            $dispositivo_tipo = 'TABLET';
        }
        
        $audit_stmt = $pdo->prepare($audit_sql);
        $audit_stmt->execute([
            ':cliente_id' => $cliente_id,
            ':evento_tipo' => 'REGISTRO',
            ':evento_descripcion' => "Nuevo cliente registrado en canal: $canal",
            ':canal' => $canal,
            ':usuario_id' => $usuario_id,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            ':user_agent' => $user_agent,
            ':dispositivo_tipo' => $dispositivo_tipo,
        ]);
        
        echo "✅ Evento registrado en auditoría\n";
        $resultado['exito'] = true;
        $resultado['mensaje'] = "✅ Registro completado exitosamente";
        $resultado['bd'] = [
            'cliente_id' => $cliente_id,
            'customer_code' => $customer_code,
        ];
        
        return $resultado;
        
    } catch (Exception $e) {
        $resultado['mensaje'] = "❌ Error: " . $e->getMessage();
        echo "❌ Error: " . $e->getMessage() . "\n";
        return $resultado;
    }
}

// ============================================
// PROCESAR FORMULARIO
// ============================================

$test_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_registro'])) {
    echo "<pre style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    
    $datos = [
        'PersonName' => $_POST['test_name'] ?? 'Juan Carlos',
        'PersonLastName' => $_POST['test_lastname'] ?? 'Pérez',
        'PersonLastName2' => $_POST['test_lastname2'] ?? 'García',
        'PersonBirthDate' => $_POST['test_birthdate'] ?? '1990-01-15',
        'TaxRegNr' => $_POST['test_rfc'] ?? 'PEGJ900115XXX',
        'PersonGender' => $_POST['test_gender'] ?? 'M',
        'EmailClubLazarza' => $_POST['test_email'] ?? 'prueba' . time() . '@test.com',
        'Phone' => $_POST['test_phone'] ?? '5551234567',
        'Province' => $_POST['test_province'] ?? 'Ciudad de México',
        'CityName' => $_POST['test_city'] ?? 'Mexico',
        'DistrictName' => $_POST['test_district'] ?? 'Benito Juárez',
    ];
    
    $password = $_POST['test_password'] ?? 'Password123!';
    $canal = $_POST['test_canal'] ?? 'WEB';
    $usuario_id = !empty($_POST['test_usuario_id']) ? (int)$_POST['test_usuario_id'] : null;
    
    echo "🚀 INICIANDO TEST DE REGISTRO COMPLETO\n";
    echo "=====================================\n\n";
    
    $test_result = registrar_cliente_completo($datos, $password, $canal, $usuario_id);
    
    echo "\n=====================================\n";
    echo "📊 RESULTADO FINAL\n";
    echo "=====================================\n";
    
    if ($test_result['exito']) {
        echo "✅ " . $test_result['mensaje'] . "\n";
        echo "\n📋 Datos sincronizados:\n";
        echo "  - Cliente ID: " . $test_result['bd']['cliente_id'] . "\n";
        echo "  - Customer Code: " . $test_result['bd']['customer_code'] . "\n";
        echo "  - Email: " . $datos['EmailClubLazarza'] . "\n";
    } else {
        echo "❌ " . $test_result['mensaje'] . "\n";
        if ($test_result['api']) {
            echo "\n🔴 Respuesta de API:\n";
            echo json_encode($test_result['api'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    
    echo "</pre>\n";
}

// ============================================
// HTML
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Registro Completo - Lazarza</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .zarza-gradient { background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%); }
        .form-input:focus { border-color: #b51a8a; box-shadow: 0 0 0 3px rgba(181, 26, 138, 0.1); }
        .zarza-text { color: #b51a8a; }
        .zarza-bg { background-color: #b51a8a; }
        .zarza-bg-hover:hover { background-color: #71398d; }
    </style>
</head>
<body class="zarza-gradient min-h-screen py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="flex items-center gap-4 mb-8">
                <img src="/logoZarza.webp" alt="ZarzaPoints" class="h-16 w-auto">
                <div>
                    <h1 class="text-3xl font-bold zarza-text">Test de Registro Completo</h1>
                    <p class="text-gray-600">API → Base de Datos → Credenciales</p>
                </div>
            </div>

            <form method="POST" class="space-y-6">
                <!-- Sección: Canal de Registro -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 rounded-lg border border-purple-200">
                    <h3 class="text-lg font-bold zarza-text mb-4"><i class="fas fa-route mr-2"></i> Canal de Registro</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Canal -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store mr-2 zarza-text"></i> Canal
                            </label>
                            <select name="test_canal" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" required>
                                <option value="WEB">🌐 Página Web</option>
                                <option value="POS">🛒 Punto de Venta (POS)</option>
                                <option value="PHONE">☎️ Telefónico</option>
                                <option value="MOBILE_APP">📱 Aplicación Móvil</option>
                                <option value="MANUAL">✏️ Registro Manual</option>
                                <option value="API">⚙️ API Tercero</option>
                            </select>
                        </div>

                        <!-- Usuario que Registra -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user-tie mr-2 zarza-text"></i> ID Usuario Registrador (Opcional)
                            </label>
                            <input type="number" name="test_usuario_id" placeholder="Ej: 1, 5, 100" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                            <p class="text-xs text-gray-500 mt-1">Admin/Vendedor que realiza el registro</p>
                        </div>

                        <!-- Info de Red -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-globe mr-2 zarza-text"></i> Tu IP
                            </label>
                            <input type="text" value="<?php echo $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'; ?>" readonly
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                            <p class="text-xs text-gray-500 mt-1">Se registra automáticamente</p>
                        </div>
                    </div>
                </div>

                <!-- Sección: Datos del Cliente -->
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 zarza-text"></i> Nombre
                        </label>
                        <input type="text" name="test_name" value="Juan Carlos" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" required>
                    </div>

                    <!-- Apellido Paterno -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 zarza-text"></i> Apellido Paterno
                        </label>
                        <input type="text" name="test_lastname" value="Pérez" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" required>
                    </div>

                    <!-- Apellido Materno -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 zarza-text"></i> Apellido Materno
                        </label>
                        <input type="text" name="test_lastname2" value="García" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 zarza-text"></i> Email
                        </label>
                        <input type="email" name="test_email" placeholder="prueba@test.com" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" required>
                        <p class="text-xs text-gray-500 mt-1">Se agregará un timestamp automáticamente si está vacío</p>
                    </div>

                    <!-- RFC -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-2 zarza-text"></i> RFC
                        </label>
                        <input type="text" name="test_rfc" value="PEGJ900115XXX" maxlength="13"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" required>
                    </div>

                    <!-- Género -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-venus-mars mr-2 zarza-text"></i> Género
                        </label>
                        <select name="test_gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro/Prefiero no especificar</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Se almacena en BD local, no se envía a la API</p>
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-birthday-cake mr-2 zarza-text"></i> Fecha de Nacimiento
                        </label>
                        <input type="date" name="test_birthdate" value="1990-01-15"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-phone mr-2 zarza-text"></i> Teléfono
                        </label>
                        <input type="tel" name="test_phone" value="5551234567"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                    </div>

                    <!-- Estado/Provincia -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-pin mr-2 zarza-text"></i> Estado/Provincia
                        </label>
                        <input type="text" name="test_province" value="Ciudad de México"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                    </div>

                    <!-- Ciudad -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-city mr-2 zarza-text"></i> Ciudad
                        </label>
                        <input type="text" name="test_city" value="Mexico"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                    </div>

                    <!-- Distrito -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-home mr-2 zarza-text"></i> Distrito/Municipio
                        </label>
                        <input type="text" name="test_district" value="Benito Juárez"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 zarza-text"></i> Contraseña
                        </label>
                        <input type="password" name="test_password" value="Password123!"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" required>
                    </div>
                </div>

                <!-- Botón -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" name="test_registro" value="1" 
                            class="flex-1 zarza-bg text-white font-bold py-3 rounded-lg hover:opacity-90 transition-all">
                        <i class="fas fa-rocket mr-2"></i> Ejecutar Test de Registro
                    </button>
                    <a href="/" class="flex items-center justify-center px-6 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>
            </form>

            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-bold text-blue-900 mb-3"><i class="fas fa-info-circle mr-2"></i> Sistema de Auditoría Completo</h3>
                <div class="space-y-2 text-sm text-blue-800">
                    <p><strong>📊 Canales soportados:</strong> WEB, POS (Punto de Venta), PHONE (Telefónico), MOBILE_APP, MANUAL, API</p>
                    <p><strong>👤 Rastreo de Usuario:</strong> Registra quién hizo el registro (ID de vendedor/admin)</p>
                    <p><strong>🌐 Datos Técnicos:</strong> IP, User-Agent, Tipo de Dispositivo (MOBILE, DESKTOP, TABLET)</p>
                    <p><strong>📝 Tablas involucradas:</strong></p>
                    <ul class="ml-4 mt-2 space-y-1">
                        <li>• <code>clientes_api_lazarza</code> - Datos del cliente + canal de registro</li>
                        <li>• <code>clientes_credenciales</code> - Credenciales hasheadas</li>
                        <li>• <code>auditoria_registros</code> - Log completo de eventos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
