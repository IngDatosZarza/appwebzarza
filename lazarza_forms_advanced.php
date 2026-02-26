<?php
// Iniciar sesión para mantener datos de cliente
session_start();

// Configuración fija
define('BASE_URL', 'https://opercompruebausa.oppen.io/genericapi/fidelizacion');
define('BEARER_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiT08iLCJwZXJtaXNzaW9ucyI6W10sImlhdCI6MTc2MjQ2NTk5NCwiZXhwIjo0MjgyNDY1OTk0fQ.NkN5FSDfrwzbgNrt_xYEmijMPnlM3ABaHNmeA6mqZuc');

/**
 * Extrae los datos del formulario para create/update
 */
function build_customer_body() {
    return [
        'PersonCustomer' => true,
        'PersonName' => $_POST['PersonName'] ?? '',
        'PersonLastName' => $_POST['PersonLastName'] ?? '',
        'PersonLastName2' => $_POST['PersonLastName2'] ?? '',
        'TaxRegNr' => $_POST['TaxRegNr'] ?? '',
        'EmailClubLazarza' => $_POST['EmailClubLazarza'] ?? '',
        'Phone' => $_POST['Phone'] ?? '',
        'Province' => $_POST['Province'] ?? '',
        'CityName' => $_POST['CityName'] ?? '',
        'DistrictName' => $_POST['DistrictName'] ?? ''
    ];
}

/**
 * Obtiene los datos del cliente para pre-llenar el formulario
 */
function get_customer_data($code) {
    $endpoint = 'Customer/' . urlencode($code);
    $result = api_request('GET', $endpoint);
    
    if (isset($result['json']) && is_array($result['json'])) {
        // La API devuelve la estructura: json -> data -> [array de clientes]
        $data = $result['json']['data'] ?? $result['json'];
        
        if (is_array($data) && !empty($data)) {
            // Si es un array de clientes
            if (isset($data[0])) {
                $customer = $data[0];
                // Filtrar clientes cerrados
                if (isset($customer['Code']) && !empty($customer['Code']) && (!isset($customer['Closed']) || $customer['Closed'] != 1)) {
                    return $customer;
                }
            } elseif (isset($data['Code']) && !empty($data['Code'])) {
                // Si es un solo cliente, verificar que no esté cerrado
                if (!isset($data['Closed']) || $data['Closed'] != 1) {
                    return $data;
                }
            }
        }
    }
    return null;
}

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

$result = null;
$customer_data = null;
$search_mode = false;
$current_customer_code = $_SESSION['current_customer_code'] ?? null;

// Handle actions from forms
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'search_rfc':
            $tax = trim($_POST['TaxRegNr'] ?? '');
            if (empty($tax)) {
                $result = ['error' => 'RFC es requerido para buscar'];
            } else {
                $api_result = api_request('GET', 'Customer', ['TaxRegNr' => $tax]);
                // La API devuelve: json -> data -> [array de clientes]
                if (isset($api_result['json']) && is_array($api_result['json'])) {
                    $data = $api_result['json']['data'] ?? $api_result['json'];
                    
                    // Filtrar clientes cerrados (Closed == 1)
                    if (is_array($data)) {
                        if (isset($data[0])) {
                            // Es un array de clientes, filtrar los cerrados
                            $data = array_values(array_filter($data, function($customer) {
                                return !isset($customer['Closed']) || $customer['Closed'] != 1;
                            }));
                        } elseif (isset($data['Closed']) && $data['Closed'] == 1) {
                            // Es un solo cliente y está cerrado
                            $data = [];
                        }
                    }
                    
                    $has_valid_customer = false;
                    if (is_array($data) && !empty($data)) {
                        if (isset($data[0])) {
                            $has_valid_customer = isset($data[0]['Code']) && !empty($data[0]['Code']);
                        } elseif (isset($data['Code'])) {
                            $has_valid_customer = !empty($data['Code']);
                        }
                    }
                    
                    if ($has_valid_customer) {
                        // Normalizar la estructura para el frontend
                        $api_result['json'] = $data;
                        $result = $api_result;
                    } else {
                        $result = ['error' => 'No se encontró ningún cliente activo con el RFC: ' . $tax, 'status' => 404];
                    }
                } else {
                    $result = ['error' => 'No se encontró ningún cliente con el RFC: ' . $tax, 'status' => 404];
                }
            }
            $_POST = [];
            break;
        case 'search_email':
            $email = trim($_POST['EmailClubLazarza'] ?? '');
            if (empty($email)) {
                $result = ['error' => 'Email es requerido para buscar'];
            } else {
                $api_result = api_request('GET', 'Customer', ['EmailClubLazarza' => $email]);
                // La API devuelve: json -> data -> [array de clientes]
                if (isset($api_result['json']) && is_array($api_result['json'])) {
                    $data = $api_result['json']['data'] ?? $api_result['json'];
                    
                    // Filtrar clientes cerrados (Closed == 1)
                    if (is_array($data)) {
                        if (isset($data[0])) {
                            // Es un array de clientes, filtrar los cerrados
                            $data = array_values(array_filter($data, function($customer) {
                                return !isset($customer['Closed']) || $customer['Closed'] != 1;
                            }));
                        } elseif (isset($data['Closed']) && $data['Closed'] == 1) {
                            // Es un solo cliente y está cerrado
                            $data = [];
                        }
                    }
                    
                    $has_valid_customer = false;
                    if (is_array($data) && !empty($data)) {
                        if (isset($data[0])) {
                            $has_valid_customer = isset($data[0]['Code']) && !empty($data[0]['Code']);
                        } elseif (isset($data['Code'])) {
                            $has_valid_customer = !empty($data['Code']);
                        }
                    }
                    
                    if ($has_valid_customer) {
                        // Normalizar la estructura para el frontend
                        $api_result['json'] = $data;
                        $result = $api_result;
                    } else {
                        $result = ['error' => 'No se encontró ningún cliente activo con el email: ' . $email, 'status' => 404];
                    }
                } else {
                    $result = ['error' => 'No se encontró ningún cliente con el email: ' . $email, 'status' => 404];
                }
            }
            $_POST = [];
            break;
        case 'search_phone':
            $phone = trim($_POST['Phone'] ?? '');
            if (empty($phone)) {
                $result = ['error' => 'Teléfono es requerido para buscar'];
            } else {
                $api_result = api_request('GET', 'Customer', ['Phone' => $phone]);
                // La API devuelve: json -> data -> [array de clientes]
                if (isset($api_result['json']) && is_array($api_result['json'])) {
                    $data = $api_result['json']['data'] ?? $api_result['json'];
                    
                    // Filtrar clientes cerrados (Closed == 1)
                    if (is_array($data)) {
                        if (isset($data[0])) {
                            // Es un array de clientes, filtrar los cerrados
                            $data = array_values(array_filter($data, function($customer) {
                                return !isset($customer['Closed']) || $customer['Closed'] != 1;
                            }));
                        } elseif (isset($data['Closed']) && $data['Closed'] == 1) {
                            // Es un solo cliente y está cerrado
                            $data = [];
                        }
                    }
                    
                    $has_valid_customer = false;
                    if (is_array($data) && !empty($data)) {
                        if (isset($data[0])) {
                            $has_valid_customer = isset($data[0]['Code']) && !empty($data[0]['Code']);
                        } elseif (isset($data['Code'])) {
                            $has_valid_customer = !empty($data['Code']);
                        }
                    }
                    
                    if ($has_valid_customer) {
                        // Normalizar la estructura para el frontend
                        $api_result['json'] = $data;
                        $result = $api_result;
                    } else {
                        $result = ['error' => 'No se encontró ningún cliente activo con el teléfono: ' . $phone, 'status' => 404];
                    }
                } else {
                    $result = ['error' => 'No se encontró ningún cliente con el teléfono: ' . $phone, 'status' => 404];
                }
            }
            $_POST = [];
            break;
        case 'search_customer':
            $code = trim($_POST['CustomerCode'] ?? '');
            if ($code === '') {
                $result = ['error' => 'CustomerCode es requerido'];
            } else {
                $customer_data = get_customer_data($code);
                if ($customer_data) {
                    $_SESSION['current_customer_code'] = $code;
                    $current_customer_code = $code;
                    $search_mode = true;
                    $result = ['success' => 'Cliente encontrado, puedes actualizar sus datos'];
                } else {
                    $result = ['error' => 'Cliente no encontrado'];
                }
            }
            break;
        case 'create':
            $body = build_customer_body();
            $result = api_request('POST', 'Customer', [], $body);
            $_POST = [];
            break;
        case 'update':
            $code = trim($_POST['CustomerCode'] ?? $current_customer_code ?? '');
            if ($code === '') {
                $result = ['error' => 'CustomerCode es requerido para actualizar'];
            } else {
                $body = build_customer_body();
                $endpoint = 'Customer/' . urlencode($code);
                $result = api_request('PUT', $endpoint, [], $body);
                if ($result['status'] >= 200 && $result['status'] < 300) {
                    $_POST = [];
                    $customer_data = null;
                    $search_mode = false;
                    unset($_SESSION['current_customer_code']);
                    $current_customer_code = null;
                }
            }
            break;
        case 'clear_customer':
            unset($_SESSION['current_customer_code']);
            $customer_data = null;
            $search_mode = false;
            $current_customer_code = null;
            $_POST = [];
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Pruebas - Lazarza</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        .zarza-gradient { background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%); }
        .form-input:focus { border-color: #b51a8a; box-shadow: 0 0 0 3px rgba(181, 26, 138, 0.1); }
        .zarza-text { color: #b51a8a; }
        .zarza-bg { background-color: #b51a8a; }
        .zarza-bg-hover:hover { background-color: #71398d; }
        .zarza-badge { background-color: #f3e8ff; color: #b51a8a; }
        .response-box { background: #f6f6f6; border-left: 4px solid #b51a8a; max-height: 400px; overflow-y: auto; }
        .tab-button { @apply px-4 py-2 rounded-lg transition-all; }
        .tab-button.active { @apply zarza-bg text-white; }
        .tab-button:not(.active) { @apply bg-gray-200 text-gray-700 hover:bg-gray-300; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    </style>
</head>
<body class="zarza-gradient min-h-screen py-12 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-xl p-8 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <img src="/logoZarza.webp" alt="ZarzaPoints" class="h-16 w-auto">
                    <div>
                        <h1 class="text-3xl font-bold zarza-text">Formulario de Pruebas</h1>
                        <p class="text-gray-600">Sistema de Gestión de Clientes - Lazarza</p>
                    </div>
                </div>
                <a href="/" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-all">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form Area -->
            <div class="lg:col-span-2">
                <!-- Tab Navigation -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex gap-2 flex-wrap mb-6">
                        <button class="tab-button active" onclick="showTab('search')">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                        <button class="tab-button" onclick="showTab('create')">
                            <i class="fas fa-user-plus mr-2"></i>Crear
                        </button>
                        <button class="tab-button" onclick="showTab('update')">
                            <i class="fas fa-edit mr-2"></i>Actualizar
                        </button>
                    </div>

                    <!-- Search Tab -->
                    <div id="search" class="tab-content active space-y-6">
                        <h3 class="text-xl font-semibold zarza-text mb-4">
                            <i class="fas fa-search mr-2"></i>Buscar Cliente
                        </h3>

                        <!-- Búsqueda por RFC -->
                        <form method="post" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-id-card mr-2"></i>RFC
                                    </label>
                                    <input type="text" name="TaxRegNr" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="ABCD123456XYZ" maxlength="13">
                                    <input type="hidden" name="action" value="search_rfc">
                                    <button type="submit" class="mt-2 w-full zarza-bg text-white py-2 rounded-lg hover:opacity-90 transition-all">
                                        <i class="fas fa-search mr-2"></i>Buscar por RFC
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Búsqueda por Email -->
                        <form method="post" class="space-y-4 border-t pt-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-envelope mr-2"></i>Email
                                    </label>
                                    <input type="email" name="EmailClubLazarza" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="cliente@email.com">
                                    <input type="hidden" name="action" value="search_email">
                                    <button type="submit" class="mt-2 w-full zarza-bg text-white py-2 rounded-lg hover:opacity-90 transition-all">
                                        <i class="fas fa-search mr-2"></i>Buscar por Email
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Búsqueda por Teléfono -->
                        <form method="post" class="space-y-4 border-t pt-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone mr-2"></i>Teléfono
                                    </label>
                                    <input type="tel" name="Phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="1234567890">
                                    <input type="hidden" name="action" value="search_phone">
                                    <button type="submit" class="mt-2 w-full zarza-bg text-white py-2 rounded-lg hover:opacity-90 transition-all">
                                        <i class="fas fa-search mr-2"></i>Buscar por Teléfono
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Create Tab -->
                    <div id="create" class="tab-content space-y-6">
                        <h3 class="text-xl font-semibold zarza-text mb-4">
                            <i class="fas fa-user-plus mr-2"></i>Crear Nuevo Cliente
                        </h3>

                        <form method="post" id="createForm" class="space-y-4" onsubmit="return validateForm('create')">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nombres -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombres <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="PersonName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Juan Carlos">
                                </div>

                                <!-- Apellido Paterno -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Apellido Paterno <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="PersonLastName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Pérez">
                                </div>

                                <!-- Apellido Materno -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Apellido Materno <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="PersonLastName2" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="García">
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Fecha de Nacimiento <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="PersonBirthDate" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                                </div>

                                <!-- RFC (Auto-calculado) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        RFC 
                                        <span class="text-xs text-purple-600 font-normal">
                                            <i class="fas fa-magic"></i> (Auto-calculado)
                                        </span>
                                    </label>
                                    <input type="text" name="TaxRegNr" id="rfc-create" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input bg-gray-50" placeholder="Se calculará automáticamente" maxlength="13" readonly>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle"></i> El RFC se genera automáticamente al completar nombre, apellidos y fecha de nacimiento
                                    </p>
                                </div>

                                <!-- Género -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Género <span class="text-red-500">*</span>
                                    </label>
                                    <select name="PersonGender" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                                        <option value="">Seleccione...</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Prefiero no especificar">Prefiero no especificar</option>
                                    </select>
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Teléfono <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="Phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="+52 1234567890">
                                </div>

                                <!-- Correo Electrónico -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Correo Electrónico <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="EmailClubLazarza" id="email-create" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="cliente@email.com">
                                </div>

                                <!-- Confirmar Correo Electrónico -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirmar Correo Electrónico <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="EmailConfirm" id="email-confirm-create" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="cliente@email.com">
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="Province" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Jalisco">
                                </div>

                                <!-- Municipio -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Municipio <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="CityName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Guadalajara">
                                </div>

                                <!-- Colonia -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Colonia <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="DistrictName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Centro">
                                </div>

                                <!-- Contraseña -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Contraseña <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="Password" id="password-create" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Mínimo 8 caracteres">
                                </div>

                                <!-- Confirmación de Contraseña -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirmar Contraseña <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="PasswordConfirm" id="password-confirm-create" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Mínimo 8 caracteres">
                                </div>
                            </div>

                            <input type="hidden" name="action" value="create">
                            <button type="submit" class="w-full zarza-bg text-white py-3 rounded-lg hover:opacity-90 transition-all font-semibold">
                                <i class="fas fa-save mr-2"></i>Crear Cliente
                            </button>
                        </form>
                    </div>

                    <!-- Update Tab -->
                    <div id="update" class="tab-content space-y-6">
                        <h3 class="text-xl font-semibold zarza-text mb-4">
                            <i class="fas fa-edit mr-2"></i>Actualizar Cliente
                        </h3>

                        <form method="post" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-barcode mr-2"></i>Código de Cliente
                                </label>
                                <input type="text" name="CustomerCode" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="VL008103" value="<?php echo htmlspecialchars($current_customer_code ?? ''); ?>">
                                <input type="hidden" name="action" value="search_customer">
                                <button type="submit" class="mt-2 w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-all">
                                    <i class="fas fa-search mr-2"></i>Buscar Cliente
                                </button>
                            </div>

                            <?php if ($current_customer_code): ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-green-700">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>Cliente cargado:</strong> <?php echo htmlspecialchars($current_customer_code); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </form>

                        <?php if ($search_mode && $customer_data): ?>
                            <form method="post" id="updateForm" class="space-y-4 border-t pt-4" onsubmit="return validateForm('update')">
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                                    <p class="text-amber-700">
                                        <i class="fas fa-edit mr-2"></i>
                                        <strong>Editando:</strong> <?php echo htmlspecialchars($customer_data['Code'] ?? $current_customer_code); ?>
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nombres -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Nombres <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="PersonName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['PersonName'] ?? ''); ?>">
                                    </div>

                                    <!-- Apellido Paterno -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Apellido Paterno <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="PersonLastName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['PersonLastName'] ?? ''); ?>">
                                    </div>

                                    <!-- Apellido Materno -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Apellido Materno <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="PersonLastName2" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['PersonLastName2'] ?? ''); ?>">
                                    </div>

                                    <!-- Fecha de Nacimiento -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Fecha de Nacimiento <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="PersonBirthDate" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['PersonBirthDate'] ?? ''); ?>">
                                    </div>

                                    <!-- RFC (Auto-calculado) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            RFC
                                            <span class="text-xs text-purple-600 font-normal">
                                                <i class="fas fa-magic"></i> (Auto-calculado)
                                            </span>
                                        </label>
                                        <input type="text" name="TaxRegNr" id="rfc-update" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input bg-gray-50" value="<?php echo htmlspecialchars($customer_data['TaxRegNr'] ?? ''); ?>" maxlength="13" readonly>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-info-circle"></i> El RFC se actualiza al modificar nombre, apellidos o fecha de nacimiento
                                        </p>
                                    </div>

                                    <!-- Género -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Género <span class="text-red-500">*</span>
                                        </label>
                                        <select name="PersonGender" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input">
                                            <option value="">Seleccione...</option>
                                            <option value="Femenino" <?php echo ($customer_data['PersonGender'] ?? '') === 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                                            <option value="Masculino" <?php echo ($customer_data['PersonGender'] ?? '') === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                            <option value="Prefiero no especificar" <?php echo ($customer_data['PersonGender'] ?? '') === 'Prefiero no especificar' ? 'selected' : ''; ?>>Prefiero no especificar</option>
                                        </select>
                                    </div>

                                    <!-- Teléfono -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Teléfono <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" name="Phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['Phone'] ?? ''); ?>">
                                    </div>

                                    <!-- Correo Electrónico -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Correo Electrónico <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="EmailClubLazarza" id="email-update" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['EmailClubLazarza'] ?? ''); ?>">
                                    </div>

                                    <!-- Confirmar Correo Electrónico -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Confirmar Correo Electrónico <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="EmailConfirm" id="email-confirm-update" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['EmailClubLazarza'] ?? ''); ?>">
                                    </div>

                                    <!-- Estado -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Estado <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="Province" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['Province'] ?? ''); ?>">
                                    </div>

                                    <!-- Municipio -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Municipio <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="CityName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['CityName'] ?? ''); ?>">
                                    </div>

                                    <!-- Colonia -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Colonia <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="DistrictName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" value="<?php echo htmlspecialchars($customer_data['DistrictName'] ?? ''); ?>">
                                    </div>

                                    <!-- Contraseña (Opcional en actualización) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Nueva Contraseña <span class="text-gray-500 text-xs">(dejar en blanco para mantener la actual)</span>
                                        </label>
                                        <input type="password" name="Password" id="password-update" minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Mínimo 8 caracteres">
                                    </div>

                                    <!-- Confirmación de Contraseña -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Confirmar Nueva Contraseña
                                        </label>
                                        <input type="password" name="PasswordConfirm" id="password-confirm-update" minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg form-input" placeholder="Mínimo 8 caracteres">
                                    </div>
                                </div>

                                <input type="hidden" name="CustomerCode" value="<?php echo htmlspecialchars($current_customer_code); ?>">
                                <input type="hidden" name="action" value="update">
                                <div class="flex gap-2">
                                    <button type="submit" class="flex-1 zarza-bg text-white py-3 rounded-lg hover:opacity-90 transition-all font-semibold">
                                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                                    </button>
                                    <form method="post" class="flex-1">
                                        <input type="hidden" name="action" value="clear_customer">
                                        <button type="submit" class="w-full bg-gray-400 text-white py-3 rounded-lg hover:bg-gray-500 transition-all font-semibold">
                                            <i class="fas fa-times mr-2"></i>Cancelar
                                        </button>
                                    </form>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Response Panel -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                    <h3 class="text-lg font-semibold zarza-text mb-4">
                        <i class="fas fa-server mr-2"></i>Respuesta API
                    </h3>
                    
                    <?php if ($result !== null): ?>
                        <?php
                        // Procesar resultado para mostrar de forma amigable
                        $isSearchResult = isset($result['json']) && is_array($result['json']) && !isset($result['error']);
                        $isSuccess = isset($result['success']) || (isset($result['status']) && $result['status'] >= 200 && $result['status'] < 300);
                        $isError = isset($result['error']) || (isset($result['status']) && $result['status'] >= 400);
                        
                        // Validar si hay datos reales en la búsqueda
                        if ($isSearchResult) {
                            $customers = $result['json'];
                            $has_valid_data = false;
                            
                            if (isset($customers[0])) {
                                $has_valid_data = isset($customers[0]['Code']) && !empty($customers[0]['Code']);
                            } elseif (isset($customers['Code'])) {
                                $has_valid_data = !empty($customers['Code']);
                            }
                            
                            if (!$has_valid_data) {
                                $isSearchResult = false;
                                $isError = true;
                                if (!isset($result['error'])) {
                                    $result['error'] = 'No se encontraron resultados para la búsqueda';
                                }
                            }
                        }
                        ?>
                        
                        <?php if ($isSearchResult && !empty($result['json'])): ?>
                            <!-- Resultado de Búsqueda Formateado -->
                            <div class="space-y-3">
                                <?php 
                                $customers = $result['json'];
                                $count = 0;
                                
                                // Si es un array de clientes
                                if (!isset($customers['Code']) && isset($customers[0])) {
                                    foreach ($customers as $index => $customer):
                                        if (!isset($customer['Code']) || empty($customer['Code'])) continue;
                                        $count++;
                                ?>
                                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-bold text-purple-900 flex items-center gap-2">
                                                <i class="fas fa-user-circle"></i>
                                                <?php echo htmlspecialchars($customer['PersonName'] ?? 'N/A'); ?>
                                                <?php echo htmlspecialchars($customer['PersonLastName'] ?? ''); ?>
                                                <?php echo htmlspecialchars($customer['PersonLastName2'] ?? ''); ?>
                                            </h4>
                                            <span class="text-xs bg-purple-600 text-white px-2 py-1 rounded">
                                                Cliente #<?php echo $count; ?>
                                            </span>
                                        </div>
                                        <div class="space-y-1 text-sm text-gray-700">
                                            <p><strong><i class="fas fa-barcode mr-1"></i>Código:</strong> <?php echo htmlspecialchars($customer['Code'] ?? 'N/A'); ?></p>
                                            <p><strong><i class="fas fa-id-card mr-1"></i>RFC:</strong> <?php echo htmlspecialchars($customer['TaxRegNr'] ?? 'N/A'); ?></p>
                                            <p><strong><i class="fas fa-envelope mr-1"></i>Email:</strong> <?php echo htmlspecialchars($customer['EmailClubLazarza'] ?? 'N/A'); ?></p>
                                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($customer['Phone'] ?? 'N/A'); ?></p>
                                            <?php if (!empty($customer['PersonGender'])): ?>
                                                <p><strong>Género:</strong> <?php echo htmlspecialchars($customer['PersonGender']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($customer['PersonBirthDate'])): ?>
                                                <p><strong>Fecha Nac.:</strong> <?php echo htmlspecialchars($customer['PersonBirthDate']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <button onclick="copyCode('<?php echo htmlspecialchars($customer['Code'] ?? ''); ?>')" 
                                                class="mt-2 w-full bg-purple-600 hover:bg-purple-700 text-white text-xs py-1 px-2 rounded transition-all">
                                            <i class="fas fa-copy mr-1"></i>Copiar Código
                                        </button>
                                    </div>
                                <?php 
                                    endforeach;
                                } else {
                                    // Si es un solo cliente
                                    $customer = $customers;
                                ?>
                                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-bold text-purple-900 flex items-center gap-2">
                                                <i class="fas fa-user-circle"></i>
                                                <?php echo htmlspecialchars($customer['PersonName'] ?? 'N/A'); ?>
                                                <?php echo htmlspecialchars($customer['PersonLastName'] ?? ''); ?>
                                                <?php echo htmlspecialchars($customer['PersonLastName2'] ?? ''); ?>
                                            </h4>
                                        </div>
                                        <div class="space-y-1 text-sm text-gray-700">
                                            <p><strong>Código:</strong> <?php echo htmlspecialchars($customer['Code'] ?? 'N/A'); ?></p>
                                            <p><strong>RFC:</strong> <?php echo htmlspecialchars($customer['TaxRegNr'] ?? 'N/A'); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['EmailClubLazarza'] ?? 'N/A'); ?></p>
                                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($customer['Phone'] ?? 'N/A'); ?></p>
                                            <?php if (!empty($customer['PersonGender'])): ?>
                                                <p><strong>Género:</strong> <?php echo htmlspecialchars($customer['PersonGender']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($customer['PersonBirthDate'])): ?>
                                                <p><strong>Fecha Nac.:</strong> <?php echo htmlspecialchars($customer['PersonBirthDate']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <button onclick="copyCode('<?php echo htmlspecialchars($customer['Code'] ?? ''); ?>')" 
                                                class="mt-2 w-full bg-purple-600 hover:bg-purple-700 text-white text-xs py-1 px-2 rounded transition-all">
                                            <i class="fas fa-copy mr-1"></i>Copiar Código
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <!-- Detalles Técnicos (colapsable) -->
                            <details class="mt-4">
                                <summary class="cursor-pointer text-xs text-gray-600 hover:text-gray-800 font-medium">
                                    <i class="fas fa-code mr-1"></i>Ver respuesta técnica completa
                                </summary>
                                <div class="response-box p-4 mt-2">
                                    <pre class="text-xs font-mono whitespace-pre-wrap"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                </div>
                            </details>
                            
                        <?php elseif (isset($result['success'])): ?>
                            <!-- Mensaje de Éxito -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <p class="text-green-700 flex items-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    <strong><?php echo htmlspecialchars($result['success']); ?></strong>
                                </p>
                            </div>
                            
                        <?php elseif (isset($result['error'])): ?>
                            <!-- Mensaje de Error -->
                            <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-red-800 font-bold text-base mb-2">
                                            Cliente No Encontrado
                                        </p>
                                        <p class="text-red-700 text-sm mb-2">
                                            <?php echo htmlspecialchars($result['error']); ?>
                                        </p>
                                        <p class="text-xs text-red-600 bg-red-100 p-2 rounded">
                                            <strong>Nota:</strong> El cliente no existe en el sistema o los datos ingresados no coinciden con ningún registro.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detalles técnicos para errores -->
                            <?php if (isset($result['status']) || isset($result['raw'])): ?>
                            <details class="mt-3">
                                <summary class="cursor-pointer text-xs text-gray-600 hover:text-gray-800 font-medium bg-gray-100 p-2 rounded">
                                    <i class="fas fa-bug mr-1"></i>Ver detalles técnicos del error
                                </summary>
                                <div class="response-box p-4 mt-2">
                                    <pre class="text-xs font-mono whitespace-pre-wrap"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                </div>
                            </details>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <!-- Respuesta Genérica -->
                            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-3">
                                <p class="text-yellow-800 text-sm font-semibold flex items-center gap-2">
                                    <i class="fas fa-question-circle text-lg"></i>
                                    Respuesta recibida pero sin datos de cliente
                                </p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    La API devolvió una respuesta pero no contiene información válida de cliente.
                                </p>
                            </div>
                            <div class="response-box p-4">
                                <pre class="text-xs font-mono whitespace-pre-wrap"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($result['status'])): ?>
                            <div class="mt-4 p-3 rounded-lg <?php echo ($result['status'] >= 200 && $result['status'] < 300) ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?>">
                                <p class="text-sm <?php echo ($result['status'] >= 200 && $result['status'] < 300) ? 'text-green-700' : 'text-red-700'; ?>">
                                    <strong>Estado HTTP:</strong> <?php echo htmlspecialchars($result['status']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                Realiza una búsqueda o acción para ver la respuesta aquí.
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Info Box -->
                    <div class="mt-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <h4 class="font-semibold text-purple-900 mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>Consejos
                        </h4>
                        <ul class="text-xs text-purple-800 space-y-1">
                            <li>✓ Usa el formulario de Búsqueda para localizar clientes</li>
                            <li>✓ Crea nuevos clientes con datos válidos</li>
                            <li>✓ Actualiza datos tras buscar el cliente</li>
                            <li>✓ Verifica las respuestas en este panel</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        /**
         * Copiar código de cliente al portapapeles
         */
        function copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                // Mostrar notificación temporal
                const notification = document.createElement('div');
                notification.textContent = '✓ Código copiado: ' + code;
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 2000);
            }).catch(err => {
                alert('Error al copiar: ' + err);
            });
        }

        /**
         * Validación de formularios
         */
        function validateForm(formType) {
            const prefix = formType === 'create' ? 'create' : 'update';
            const email = document.getElementById('email-' + prefix).value;
            const emailConfirm = document.getElementById('email-confirm-' + prefix).value;
            const password = document.getElementById('password-' + prefix).value;
            const passwordConfirm = document.getElementById('password-confirm-' + prefix).value;

            // Validar coincidencia de emails
            if (email !== emailConfirm) {
                alert('Los correos electrónicos no coinciden. Por favor verifica.');
                document.getElementById('email-confirm-' + prefix).focus();
                return false;
            }

            // Validar contraseñas (solo requerido en crear o si se proporciona en actualizar)
            if (formType === 'create' || password !== '') {
                if (password !== passwordConfirm) {
                    alert('Las contraseñas no coinciden. Por favor verifica.');
                    document.getElementById('password-confirm-' + prefix).focus();
                    return false;
                }

                if (password.length < 8) {
                    alert('La contraseña debe tener al menos 8 caracteres.');
                    document.getElementById('password-' + prefix).focus();
                    return false;
                }
            }

            return true;
        }

        /**
         * Función para calcular RFC automáticamente
         */
        function calculateRFC(nombre, apellidoPaterno, apellidoMaterno, fechaNacimiento) {
            if (!nombre || !apellidoPaterno || !fechaNacimiento) {
                return '';
            }

            // Limpiar y convertir a mayúsculas
            nombre = nombre.trim().toUpperCase();
            apellidoPaterno = apellidoPaterno.trim().toUpperCase();
            apellidoMaterno = (apellidoMaterno || '').trim().toUpperCase();

            // Palabras a ignorar en apellidos
            const palabrasIgnorar = ['DE', 'LA', 'LAS', 'MC', 'VON', 'DEL', 'LOS', 'Y', 'MAC'];
            
            // Función para limpiar apellido
            function limpiarApellido(apellido) {
                const partes = apellido.split(' ').filter(p => !palabrasIgnorar.includes(p));
                return partes.join(' ') || apellido;
            }

            apellidoPaterno = limpiarApellido(apellidoPaterno);
            apellidoMaterno = limpiarApellido(apellidoMaterno);

            // Función para obtener primera vocal interna
            function primeraVocalInterna(texto) {
                const vocales = 'AEIOU';
                for (let i = 1; i < texto.length; i++) {
                    if (vocales.includes(texto[i])) {
                        return texto[i];
                    }
                }
                return 'X';
            }

            // Construir las primeras 4 letras
            let rfc = '';
            
            // Primera letra y primera vocal interna del apellido paterno
            rfc += apellidoPaterno[0];
            rfc += primeraVocalInterna(apellidoPaterno);
            
            // Primera letra del apellido materno (o X si no existe)
            rfc += apellidoMaterno ? apellidoMaterno[0] : 'X';
            
            // Primera letra del nombre
            const nombrePartes = nombre.split(' ');
            const primerNombre = nombrePartes[0];
            // Ignorar nombres comunes
            if (['MARIA', 'JOSE', 'MA', 'MA.', 'J', 'J.'].includes(primerNombre) && nombrePartes.length > 1) {
                rfc += nombrePartes[1][0];
            } else {
                rfc += primerNombre[0];
            }

            // Agregar fecha de nacimiento (AAMMDD)
            const fecha = new Date(fechaNacimiento + 'T00:00:00');
            const year = fecha.getFullYear().toString().substr(-2);
            const month = String(fecha.getMonth() + 1).padStart(2, '0');
            const day = String(fecha.getDate()).padStart(2, '0');
            
            rfc += year + month + day;

            // Agregar homoclave genérica (XXX para ser asignada por el SAT)
            rfc += 'XXX';

            // Validar palabras inconvenientes
            const palabrasInconvenientes = [
                'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA', 'CAKO',
                'COGE', 'COJA', 'COJE', 'COJI', 'COJO', 'CULA', 'CULO', 'FETO',
                'GUEY', 'JOTO', 'KACA', 'KACO', 'KAGA', 'KAGO', 'KAKA', 'KOGE',
                'KOJO', 'KULO', 'MAME', 'MAMO', 'MEAR', 'MEAS', 'MEON', 'MION',
                'MOCO', 'MULA', 'PEDA', 'PEDO', 'PENE', 'PUTA', 'PUTO', 'QULO',
                'RATA', 'RUIN'
            ];

            if (palabrasInconvenientes.includes(rfc.substr(0, 4))) {
                rfc = rfc[0] + 'X' + rfc.substr(2);
            }

            return rfc;
        }

        /**
         * Configurar listeners para campos del formulario de CREAR
         */
        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.querySelector('#create form');
            if (createForm) {
                const nombreInput = createForm.querySelector('[name="PersonName"]');
                const apellidoPaternoInput = createForm.querySelector('[name="PersonLastName"]');
                const apellidoMaternoInput = createForm.querySelector('[name="PersonLastName2"]');
                const fechaNacimientoInput = createForm.querySelector('[name="PersonBirthDate"]');
                const rfcInput = document.getElementById('rfc-create');

                function actualizarRFC() {
                    const rfc = calculateRFC(
                        nombreInput.value,
                        apellidoPaternoInput.value,
                        apellidoMaternoInput.value,
                        fechaNacimientoInput.value
                    );
                    
                    if (rfc && rfcInput) {
                        rfcInput.value = rfc;
                        rfcInput.classList.add('bg-green-100', 'border-green-400');
                        setTimeout(() => {
                            rfcInput.classList.remove('bg-green-100', 'border-green-400');
                        }, 1500);
                    }
                }

                [nombreInput, apellidoPaternoInput, apellidoMaternoInput, fechaNacimientoInput].forEach(input => {
                    if (input) {
                        input.addEventListener('blur', actualizarRFC);
                        input.addEventListener('change', actualizarRFC);
                        input.addEventListener('input', function() {
                            // Calcular en tiempo real para fecha de nacimiento
                            if (this.name === 'PersonBirthDate') {
                                actualizarRFC();
                            }
                        });
                    }
                });
            }

            // Configurar listeners para el formulario de ACTUALIZAR
            // Observar cambios en el DOM para cuando se cargue el formulario de actualización
            const observer = new MutationObserver(function() {
                const updateFormContainer = document.querySelector('#update form[method="post"]:last-of-type');
                if (updateFormContainer && !updateFormContainer.dataset.rfcConfigured) {
                    updateFormContainer.dataset.rfcConfigured = 'true';
                    
                    const nombreInput = updateFormContainer.querySelector('[name="PersonName"]');
                    const apellidoPaternoInput = updateFormContainer.querySelector('[name="PersonLastName"]');
                    const apellidoMaternoInput = updateFormContainer.querySelector('[name="PersonLastName2"]');
                    const fechaNacimientoInput = updateFormContainer.querySelector('[name="PersonBirthDate"]');
                    const rfcInput = document.getElementById('rfc-update');

                    function actualizarRFCUpdate() {
                        const rfc = calculateRFC(
                            nombreInput.value,
                            apellidoPaternoInput.value,
                            apellidoMaternoInput.value,
                            fechaNacimientoInput.value
                        );
                        
                        if (rfc && rfcInput) {
                            rfcInput.value = rfc;
                            rfcInput.classList.add('bg-green-100', 'border-green-400');
                            setTimeout(() => {
                                rfcInput.classList.remove('bg-green-100', 'border-green-400');
                            }, 1500);
                        }
                    }

                    [nombreInput, apellidoPaternoInput, apellidoMaternoInput, fechaNacimientoInput].forEach(input => {
                        if (input) {
                            input.addEventListener('blur', actualizarRFCUpdate);
                            input.addEventListener('change', actualizarRFCUpdate);
                            input.addEventListener('input', function() {
                                // Calcular en tiempo real para fecha de nacimiento
                                if (this.name === 'PersonBirthDate') {
                                    actualizarRFCUpdate();
                                }
                            });
                        }
                    });
                }
            });
            
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
</body>
</html>
