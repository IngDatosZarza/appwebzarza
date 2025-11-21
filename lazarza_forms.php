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
        'TaxRegSAT' => $_POST['TaxRegSAT'] ?? '',
        'Address' => $_POST['Address'] ?? '',
        'Province' => $_POST['Province'] ?? '',
        'City' => $_POST['City'] ?? '',
        'CityName' => $_POST['CityName'] ?? '',
        'DistrictName' => $_POST['DistrictName'] ?? '',
        'ZipCode' => $_POST['ZipCode'] ?? '',
        'EmailClubLazarza' => $_POST['EmailClubLazarza'] ?? '',
        'Phone' => $_POST['Phone'] ?? '',
        'PersonID' => $_POST['PersonID'] ?? '',
        'PersonBirthDate' => $_POST['PersonBirthDate'] ?? '',
        'ClubLazarzaPromoEmails' => isset($_POST['ClubLazarzaPromoEmails']),
        'ClubLazarzaPromoWhatsApp' => isset($_POST['ClubLazarzaPromoWhatsApp']),
    ];
}

/**
 * Obtiene los datos del cliente para pre-llenar el formulario
 */
function get_customer_data($code) {
    $endpoint = 'Customer/' . urlencode($code);
    $result = api_request('GET', $endpoint);
    
    if (isset($result['json']) && is_array($result['json'])) {
        // Si es un array, retornar el primer cliente
        if (isset($result['json'][0])) {
            return $result['json'][0];
        }
        // Si es un objeto directo
        return $result['json'];
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

    // Try to decode JSON, otherwise return raw
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

// Recuperar CustomerCode de sesión si existe
$current_customer_code = $_SESSION['current_customer_code'] ?? null;

// Handle actions from forms
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'search_rfc':
            $tax = trim($_POST['TaxRegNr'] ?? '');
            $result = api_request('GET', 'Customer', ['TaxRegNr' => $tax]);
            $_POST = [];
            break;
        case 'search_email':
            $email = trim($_POST['EmailClubLazarza'] ?? '');
            $result = api_request('GET', 'Customer', ['EmailClubLazarza' => $email]);
            $_POST = [];
            break;
        case 'search_phone':
            $phone = trim($_POST['Phone'] ?? '');
            $result = api_request('GET', 'Customer', ['Phone' => $phone]);
            $_POST = [];
            break;
        case 'search_customer':
            // Buscar cliente por código para cargar sus datos en el formulario de actualización
            $code = trim($_POST['CustomerCode'] ?? '');
            if ($code === '') {
                $result = ['error' => 'CustomerCode es requerido'];
            } else {
                $customer_data = get_customer_data($code);
                if ($customer_data) {
                    // Guardar CustomerCode en sesión
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
            // Usar CustomerCode de sesión si no viene en POST
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
            // Limpiar búsqueda de cliente
            unset($_SESSION['current_customer_code']);
            $customer_data = null;
            $search_mode = false;
            $current_customer_code = null;
            $_POST = [];
            break;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Lazarza - Formularios simplificados</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; max-width: 900px; margin: 20px auto; }
        form { border: 1px solid #ddd; padding: 12px; margin-bottom: 16px; }
        label { display:block; margin:6px 0 2px; }
        input[type=text], input[type=email], input[type=date] { width:100%; padding:6px; }
        .row { display:flex; gap:10px; }
        .col { flex:1; }
        pre { background:#f6f6f6; padding:10px; overflow:auto; }
    </style>
</head>
<body>
    <h1>Lazarza - Formularios simplificados</h1>

    <form method="post">
        <h3>Buscar Cliente por RFC</h3>
        <label>TaxRegNr</label>
        <input type="text" name="TaxRegNr" required>
        <input type="hidden" name="action" value="search_rfc">
        <p><button type="submit">Buscar</button></p>
    </form>

    <form method="post">
        <h3>Buscar Cliente por Email</h3>
        <label>EmailClubLazarza</label>
        <input type="email" name="EmailClubLazarza" required>
        <input type="hidden" name="action" value="search_email">
        <p><button type="submit">Buscar</button></p>
    </form>

    <form method="post">
        <h3>Buscar Cliente por Teléfono</h3>
        <label>Phone</label>
        <input type="text" name="Phone" required>
        <input type="hidden" name="action" value="search_phone">
        <p><button type="submit">Buscar</button></p>
    </form>

    <form method="post">
        <h3>Crear Cliente (POST)</h3>
        <div class="row">
            <div class="col">
                <label>Nombre</label>
                <input type="text" name="PersonName">
                <label>Apellido paterno</label>
                <input type="text" name="PersonLastName">
                <label>Apellido materno</label>
                <input type="text" name="PersonLastName2">
                <label>TaxRegNr(RFC)</label>
                <input type="text" name="TaxRegNr">
                <label>TaxRegSAT(Regimen Fiscal)</label>
                <input type="text" name="TaxRegSAT">
            </div>
            <div class="col">
                <label>EmailClubLazarza</label>
                <input type="email" name="EmailClubLazarza">
                <label>Phone</label>
                <input type="text" name="Phone">
                <label>Address</label>
                <input type="text" name="Address">
                <label>ZipCode(Código Postal)</label>
                <input type="text" name="ZipCode">
                <label>City(Ciudad)</label>
                <input type="text" name="City">
                <label>CityName(Municipio)</label>
                <input type="text" name="CityName">
                <label>Province(Estado)</label>
                <input type="text" name="Province">
                <label>PersonBirthDate</label>
                <input type="date" name="PersonBirthDate">
            </div>
        </div>
        <label>PersonID(DNI)</label>
        <input type="text" name="PersonID">
        <label><input type="checkbox" name="ClubLazarzaPromoEmails"> ClubLazarzaPromoEmails</label>
        <label><input type="checkbox" name="ClubLazarzaPromoWhatsApp"> ClubLazarzaPromoWhatsApp</label>
        <input type="hidden" name="action" value="create">
        <p><button type="submit">Crear Cliente</button></p>
    </form>

    <form method="post">
        <h3>Actualizar Cliente</h3>
        <h4>Paso 1: Buscar Cliente</h4>
        <label>CustomerCode (por ejemplo: VL008103)</label>
        <input type="text" name="CustomerCode" required value="<?php echo htmlspecialchars($current_customer_code ?? ''); ?>">
        <input type="hidden" name="action" value="search_customer">
        <p><button type="submit">Buscar Cliente</button></p>
        <?php if ($current_customer_code): ?>
            <p style="color: green;">✅ Cliente cargado: <strong><?php echo htmlspecialchars($current_customer_code); ?></strong></p>
        <?php endif; ?>
    </form>

    <?php if ($search_mode && $customer_data): ?>
    <form method="post">
        <h3>Actualizar Cliente</h3>
        <h4>Paso 2: Editar Datos</h4>
        <p style="background: #fffbea; padding: 10px; border-left: 4px solid #ffc107;">
            Editando cliente: <strong><?php echo htmlspecialchars($customer_data['CustomerCode'] ?? $current_customer_code); ?></strong>
        </p>
        <input type="hidden" name="CustomerCode" value="<?php echo htmlspecialchars($current_customer_code); ?>">
        <div class="row">
            <div class="col">
                <label>Nombre</label>
                <input type="text" name="PersonName" value="<?php echo htmlspecialchars($customer_data['PersonName'] ?? ''); ?>">
                <label>Apellido paterno</label>
                <input type="text" name="PersonLastName" value="<?php echo htmlspecialchars($customer_data['PersonLastName'] ?? ''); ?>">
                <label>Apellido materno</label>
                <input type="text" name="PersonLastName2" value="<?php echo htmlspecialchars($customer_data['PersonLastName2'] ?? ''); ?>">
                <label>TaxRegNr(RFC)</label>
                <input type="text" name="TaxRegNr" value="<?php echo htmlspecialchars($customer_data['TaxRegNr'] ?? ''); ?>">
                <label>TaxRegSAT(Regimen Fiscal)</label>
                <input type="text" name="TaxRegSAT" value="<?php echo htmlspecialchars($customer_data['TaxRegSAT'] ?? ''); ?>">
            </div>
            <div class="col">
                <label>EmailClubLazarza</label>
                <input type="email" name="EmailClubLazarza" value="<?php echo htmlspecialchars($customer_data['EmailClubLazarza'] ?? ''); ?>">
                <label>Phone</label>
                <input type="text" name="Phone" value="<?php echo htmlspecialchars($customer_data['Phone'] ?? ''); ?>">
                <label>Address</label>
                <input type="text" name="Address" value="<?php echo htmlspecialchars($customer_data['Address'] ?? ''); ?>">
                <label>ZipCode(Código Postal)</label>
                <input type="text" name="ZipCode" value="<?php echo htmlspecialchars($customer_data['ZipCode'] ?? ''); ?>">
                <label>City(Ciudad)</label>
                <input type="text" name="City" value="<?php echo htmlspecialchars($customer_data['City'] ?? ''); ?>">
                <label>CityName(Municipio)</label>
                <input type="text" name="CityName" value="<?php echo htmlspecialchars($customer_data['CityName'] ?? ''); ?>">
                <label>Province(Estado)</label>
                <input type="text" name="Province" value="<?php echo htmlspecialchars($customer_data['Province'] ?? ''); ?>">
                <label>PersonBirthDate</label>
                <input type="date" name="PersonBirthDate" value="<?php echo htmlspecialchars($customer_data['PersonBirthDate'] ?? ''); ?>">
            </div>
        </div>
        <label>PersonID(DNI)</label>
        <input type="text" name="PersonID" value="<?php echo htmlspecialchars($customer_data['PersonID'] ?? ''); ?>">
        <label><input type="checkbox" name="ClubLazarzaPromoEmails" <?php echo isset($customer_data['ClubLazarzaPromoEmails']) && $customer_data['ClubLazarzaPromoEmails'] ? 'checked' : ''; ?>> ClubLazarzaPromoEmails</label>
        <label><input type="checkbox" name="ClubLazarzaPromoWhatsApp" <?php echo isset($customer_data['ClubLazarzaPromoWhatsApp']) && $customer_data['ClubLazarzaPromoWhatsApp'] ? 'checked' : ''; ?>> ClubLazarzaPromoWhatsApp</label>
        <input type="hidden" name="action" value="update">
        <p>
            <button type="submit">Guardar Cambios</button>
            <button type="submit" formaction="" onclick="document.getElementById('clear_form').value = 'clear_customer'; document.getElementById('clear_form').submit(); return false;">Cancelar Búsqueda</button>
        </p>
    </form>
    <?php endif; ?>

    <h2>Resultado</h2>
    <?php if ($result !== null): ?>
        <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    <?php else: ?>
        <p>No hay respuesta aún. Realiza una petición usando los formularios arriba.</p>
    <?php endif; ?>

    <!-- Formulario oculto para limpiar búsqueda -->
    <form method="post" id="clear_form" style="display: none;">
        <input type="hidden" name="action" id="clear_form_action" value="">
    </form>

    <hr>
    <p><strong>Nota:</strong> Base URL y Bearer Token están configurados como constantes fijas en el archivo PHP.</p>
</body>
</html>
