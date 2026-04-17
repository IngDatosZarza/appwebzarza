<?php
// Iniciar sesión para mantener datos de cliente
session_start();

// Configuración fija
define('BASE_URL', 'https://opercompruebausa.oppen.io/genericapi/fidelizaciontest');
define('BEARER_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiU0lTSURQVTAxIiwicGVybWlzc2lvbnMiOltdLCJpYXQiOjE3NzU2Njk5NzUsImV4cCI6MTAxNzc1NjY5OTc0fQ.acyeX-5XD-WuKAH1OsNROLqB5rx7Ol0U4iSjp-pnqXI');

/**
 * Construye el body JSON para create/update
 */
function build_customer_body(): array {
    $sex  = trim($_POST['PersonSex'] ?? '');
    $body = [
        'PersonCustomer'   => true,
        'PersonName'       => trim($_POST['PersonName']       ?? ''),
        'PersonLastName'   => trim($_POST['PersonLastName']   ?? ''),
        'PersonLastName2'  => trim($_POST['PersonLastName2']  ?? ''),
        'PersonBirthDate'  => trim($_POST['PersonBirthDate']  ?? ''),
        'PersonSex'        => $sex !== '' ? (int)$sex : null,
        'TaxRegNr'         => trim($_POST['TaxRegNr']         ?? ''),
        'EmailClubLazarza' => trim($_POST['EmailClubLazarza'] ?? ''),
        'Phone'            => trim($_POST['Phone']            ?? ''),
        'Province'         => trim($_POST['Province']         ?? ''),
        'CityName'         => trim($_POST['CityName']         ?? ''),
        'DistrictName'     => trim($_POST['DistrictName']     ?? ''),
        'TaxRegSAT'                => '612',
        'Address'                  => 'Calle',
        'ClubLazarzaPromoEmails'   => isset($_POST['ClubLazarzaPromoEmails']),
        'ClubLazarzaPromoWhatsApp' => isset($_POST['ClubLazarzaPromoWhatsApp']),
    ];
    return $body;
}

/**
 * Obtiene datos de un cliente por código
 */
function get_customer_data(string $code): ?array {
    $result = api_request('GET', 'Customer/' . urlencode($code));
    if (!isset($result['json']) || !is_array($result['json'])) return null;
    $data = $result['json']['data'] ?? $result['json'];
    if (!is_array($data) || empty($data)) return null;
    $customer = isset($data[0]) ? $data[0] : $data;
    if (empty($customer['Code'])) return null;
    if (isset($customer['Closed']) && $customer['Closed'] == 1) return null;
    return $customer;
}

/**
 * Busca clientes por un parámetro de la API
 */
function search_customers(string $param, string $value): array {
    $result = api_request('GET', 'Customer', [$param => $value]);
    if (!isset($result['json']) || !is_array($result['json'])) {
        return ['error' => "Sin resultados: {$param}={$value}", 'status' => 404];
    }
    $data = $result['json']['data'] ?? $result['json'];
    if (!is_array($data)) {
        return ['error' => "Sin resultados: {$param}={$value}", 'status' => 404];
    }
    if (isset($data['Code'])) $data = [$data];
    $data = array_values(array_filter($data, fn($c) =>
        !empty($c['Code']) && (!isset($c['Closed']) || $c['Closed'] != 1)
    ));
    if (empty($data)) {
        return ['error' => "No hay cliente activo con {$param}={$value}", 'status' => 404];
    }
    $result['json'] = $data;
    return $result;
}

function api_request(string $method, string $endpoint, array $query = [], $body = null): array {
    $url = rtrim(BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    if (!empty($query)) $url .= '?' . http_build_query($query);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $headers  = ['Authorization: Bearer ' . BEARER_TOKEN];
    $sentJson = null;

    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null) {
            $sentJson = json_encode($body, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $sentJson);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($sentJson);
        }
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $resp   = curl_exec($ch);
    $err    = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) return ['error' => 'cURL: ' . $err];
    return [
        'status'     => $status,
        'raw'        => $resp,
        'json'       => json_decode($resp, true),
        '_method'    => $method,
        '_url'       => $url,
        '_sent_body' => $sentJson,
    ];
}

$result = null;
$customer_data = null;
$search_mode = false;
$current_customer_code = $_SESSION['current_customer_code'] ?? null;

// Handle actions from forms
if (isset($_POST['action'])) {
    switch ($_POST['action']) {

        case 'search':
            $type  = $_POST['search_type'] ?? 'TaxRegNr';
            $value = trim($_POST['search_value'] ?? '');
            $result = $value === ''
                ? ['error' => 'Ingresa un valor para buscar']
                : search_customers($type, $value);
            $_POST = [];
            break;

        case 'search_customer':
            $code = trim($_POST['CustomerCode'] ?? '');
            if ($code === '') {
                $result = ['error' => 'Ingresa el código del cliente'];
            } else {
                $cd = get_customer_data($code);
                if ($cd) {
                    $_SESSION['current_customer_code'] = $code;
                    $current_customer_code = $code;
                    $customer_data = $cd;
                    $search_mode   = true;
                    $result = ['success' => "Cliente {$code} cargado correctamente"];
                } else {
                    $result = ['error' => "Cliente {$code} no encontrado o inactivo"];
                }
            }
            break;

        case 'create':
            $body   = build_customer_body();
            $result = api_request('POST', 'Customer', [], $body);
            $_POST  = [];
            break;

        case 'update':
            $code = trim($_POST['CustomerCode'] ?? $current_customer_code ?? '');
            if ($code === '') {
                $result = ['error' => 'CustomerCode es requerido'];
            } else {
                $body   = build_customer_body();
                $result = api_request('PUT', 'Customer/' . urlencode($code), [], $body);
                if (isset($result['status']) && $result['status'] >= 200 && $result['status'] < 300) {
                    $result['success']         = "Cliente {$code} actualizado correctamente";
                    $result['_update_success'] = true;
                    unset($_SESSION['current_customer_code']);
                    $current_customer_code = null;
                    $customer_data         = null;
                    $search_mode           = false;
                    $_POST = [];
                } else {
                    $search_mode = true;
                    $sex = trim($_POST['PersonSex'] ?? '');
                    $customer_data = [
                        'Code'             => $code,
                        'PersonName'       => $_POST['PersonName']       ?? '',
                        'PersonLastName'   => $_POST['PersonLastName']   ?? '',
                        'PersonLastName2'  => $_POST['PersonLastName2']  ?? '',
                        'PersonBirthDate'  => $_POST['PersonBirthDate']  ?? '',
                        'PersonSex'        => $sex !== '' ? (int)$sex : null,
                        'TaxRegNr'         => $_POST['TaxRegNr']         ?? '',
                        'EmailClubLazarza' => $_POST['EmailClubLazarza'] ?? '',
                        'Phone'            => $_POST['Phone']            ?? '',
                        'Province'         => $_POST['Province']         ?? '',
                        'CityName'         => $_POST['CityName']         ?? '',
                        'DistrictName'             => $_POST['DistrictName']             ?? '',
                        'ClubLazarzaPromoEmails'   => isset($_POST['ClubLazarzaPromoEmails']),
                        'ClubLazarzaPromoWhatsApp' => isset($_POST['ClubLazarzaPromoWhatsApp']),
                    ];
                }
            }
            break;

        case 'clear_customer':
            unset($_SESSION['current_customer_code']);
            $customer_data         = null;
            $search_mode           = false;
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
    <title>API Test – Customer · Lazarza</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .z-text { color:#b51a8a; }
        .z-bg   { background-color:#b51a8a; }
        .z-ring:focus { outline:none; box-shadow:0 0 0 3px rgba(181,26,138,.2); border-color:#b51a8a; }
        .tab-btn { padding:.45rem 1rem; border-radius:.5rem; font-size:.85rem; font-weight:500; cursor:pointer; transition:all .15s; }
        .tab-btn.on  { background:#b51a8a; color:#fff; }
        .tab-btn.off { background:#f3f4f6; color:#4b5563; }
        .tab-btn.off:hover { background:#e5e7eb; }
        .tab-pane { display:none; }
        .tab-pane.on { display:block; }
        .fl { display:block; font-size:.68rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.25rem; }
        .fi { width:100%; padding:.45rem .75rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.875rem; }
        .fi:focus { outline:none; box-shadow:0 0 0 3px rgba(181,26,138,.2); border-color:#b51a8a; }
        .cbox { background:#111827; color:#86efac; font-family:monospace; font-size:.7rem; padding:.75rem; border-radius:.5rem; overflow:auto; max-height:320px; white-space:pre-wrap; }
        .bok  { display:inline-block; padding:.1rem .45rem; border-radius:.3rem; font-size:.72rem; font-weight:700; background:#dcfce7; color:#15803d; border:1px solid #86efac; }
        .berr { display:inline-block; padding:.1rem .45rem; border-radius:.3rem; font-size:.72rem; font-weight:700; background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; }
        .binfo{ display:inline-block; padding:.1rem .45rem; border-radius:.3rem; font-size:.72rem; font-weight:700; background:#dbeafe; color:#1d4ed8; border:1px solid #93c5fd; }
        @keyframes fi { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:translateY(0)} }
        .fi-anim { animation:fi .2s ease; }
        .radio-opt { display:flex; align-items:center; gap:.4rem; cursor:pointer; font-size:.875rem; font-weight:500; color:#374151; }
        .radio-opt input { accent-color:#b51a8a; width:1rem; height:1rem; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- ── Header ───────────────────────────────────── -->
<header style="background:linear-gradient(135deg,#b51a8a,#71398d)" class="text-white px-6 py-3 flex items-center justify-between shadow-md">
    <div class="flex items-center gap-3">
        <img src="/logoZarza.webp" alt="Lazarza" class="h-9 w-auto">
        <div>
            <p class="font-bold text-base leading-none">API Test — Customer</p>
            <p class="text-[11px] text-pink-200 font-mono mt-0.5"><?php echo htmlspecialchars(BASE_URL); ?></p>
        </div>
    </div>
    <a href="/" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg transition-all">
        <i class="fas fa-arrow-left mr-1"></i>Volver
    </a>
</header>

<div class="max-w-7xl mx-auto p-4 grid grid-cols-1 lg:grid-cols-5 gap-4 mt-2">

    <!-- ══════════ FORMS (3/5) ══════════ -->
    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Tab nav -->
        <div class="flex gap-1.5 p-3 border-b border-gray-100 bg-gray-50">
            <button class="tab-btn <?php echo !$search_mode ? 'on' : 'off'; ?>" onclick="tabSwitch('search',this)">
                <i class="fas fa-search mr-1"></i>Buscar
            </button>
            <button class="tab-btn off" onclick="tabSwitch('create',this)">
                <i class="fas fa-user-plus mr-1"></i>Crear
            </button>
            <button class="tab-btn <?php echo $search_mode ? 'on' : 'off'; ?>" onclick="tabSwitch('update',this)">
                <i class="fas fa-edit mr-1"></i>Actualizar
            </button>
        </div>

        <!-- ─── Buscar ─── -->
        <div id="tab-search" class="tab-pane <?php echo !$search_mode ? 'on' : ''; ?> p-5 space-y-4">
            <p class="text-xs text-gray-400">Busca por RFC, Email o Teléfono. Los resultados aparecen en el panel derecho.</p>
            <form method="post" class="flex flex-wrap gap-2 items-end">
                <div style="min-width:140px">
                    <label class="fl">Tipo de búsqueda</label>
                    <select name="search_type" class="fi">
                        <option value="TaxRegNr">RFC (TaxRegNr)</option>
                        <option value="EmailClubLazarza">Email</option>
                        <option value="Phone">Teléfono</option>
                    </select>
                </div>
                <div style="flex:2;min-width:200px">
                    <label class="fl">Valor</label>
                    <input type="text" name="search_value" class="fi" placeholder="Ingresa el valor" autocomplete="off">
                </div>
                <input type="hidden" name="action" value="search">
                <button class="z-bg text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-all" style="align-self:flex-end">
                    <i class="fas fa-search mr-1"></i>Buscar
                </button>
            </form>

            <?php
            $showResults = $result !== null
                && isset($result['json']) && is_array($result['json'])
                && !isset($result['error']);
            if ($showResults):
                $customers = $result['json'];
                if (isset($customers['Code'])) $customers = [$customers];
                $customers = array_filter($customers, fn($c) => !empty($c['Code']));
            ?>
            <div class="space-y-2 fi-anim">
                <?php foreach ($customers as $c): ?>
                <div class="border border-gray-200 rounded-lg p-3 flex items-start justify-between gap-3 hover:border-pink-300 transition-all">
                    <div class="text-sm space-y-0.5 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">
                            <?php echo htmlspecialchars("{$c['PersonName']} {$c['PersonLastName']} {$c['PersonLastName2']}"); ?>
                        </p>
                        <p class="text-gray-400 text-xs">
                            <code class="bg-gray-100 px-1 rounded"><?php echo htmlspecialchars($c['Code']); ?></code>
                            &nbsp;·&nbsp;<?php echo htmlspecialchars($c['TaxRegNr'] ?? '–'); ?>
                            &nbsp;·&nbsp;<?php echo htmlspecialchars($c['EmailClubLazarza'] ?? '–'); ?>
                        </p>
                        <p class="text-gray-400 text-xs">
                            <?php echo htmlspecialchars($c['Phone'] ?? '–'); ?>
                            &nbsp;·&nbsp;PersonSex: <strong class="z-text"><?php
                                $sv = $c['PersonSex'] ?? null;
                                echo $sv === 0 || $sv === '0' ? '0 (Masculino)' : ($sv === 1 || $sv === '1' ? '1 (Femenino)' : 'null');
                            ?></strong>
                        </p>
                        <p class="text-gray-400 text-xs">
                            <i class="fas fa-envelope" style="margin-right:.2rem"></i>PromoEmails:
                            <strong class="<?php echo $c['ClubLazarzaPromoEmails'] ? 'text-green-600' : 'text-red-400'; ?>">
                                <?php echo $c['ClubLazarzaPromoEmails'] ? 'true' : 'false'; ?>
                            </strong>
                            &nbsp;·&nbsp;
                            <i class="fab fa-whatsapp" style="margin-right:.2rem"></i>PromoWhatsApp:
                            <strong class="<?php echo $c['ClubLazarzaPromoWhatsApp'] ? 'text-green-600' : 'text-red-400'; ?>">
                                <?php echo $c['ClubLazarzaPromoWhatsApp'] ? 'true' : 'false'; ?>
                            </strong>
                        </p>
                    </div>
                    <button onclick="copyCode('<?php echo htmlspecialchars($c['Code']); ?>')"
                            class="shrink-0 z-bg text-white text-xs px-2 py-1 rounded hover:opacity-80 transition-all">
                        <i class="fas fa-copy mr-1"></i><?php echo htmlspecialchars($c['Code']); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ─── Crear ─── -->
        <div id="tab-create" class="tab-pane p-5">
            <form method="post" id="createForm" onsubmit="return chkForm('create')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="fl">PersonName *</label>
                        <input name="PersonName" required class="fi" placeholder="Juan">
                    </div>
                    <div>
                        <label class="fl">PersonLastName *</label>
                        <input name="PersonLastName" required class="fi" placeholder="Pérez">
                    </div>
                    <div>
                        <label class="fl">PersonLastName2</label>
                        <input name="PersonLastName2" class="fi" placeholder="García">
                    </div>
                    <div>
                        <label class="fl">PersonBirthDate *</label>
                        <input type="date" name="PersonBirthDate" required class="fi">
                    </div>
                    <div class="md:col-span-2">
                        <label class="fl">TaxRegNr <span style="text-transform:none;color:#a855f7;font-size:.65rem">auto-calculado</span></label>
                        <input type="text" name="TaxRegNr" id="rfc-create" class="fi" style="background:#f9fafb" readonly maxlength="12" placeholder="Se calcula automáticamente">
                    </div>

                    <!-- PersonSex: radio buttons -->
                    <div class="md:col-span-2">
                        <label class="fl">PersonSex <span style="text-transform:none;color:#6b7280;font-size:.65rem">(0=Masculino, 1=Femenino)</span></label>
                        <div class="flex flex-wrap gap-5 mt-1">
                            <label class="radio-opt">
                                <input type="radio" name="PersonSex" value="0" onchange="sexPreview(this,'create')"> Masculino
                            </label>
                            <label class="radio-opt">
                                <input type="radio" name="PersonSex" value="1" onchange="sexPreview(this,'create')"> Femenino
                            </label>
                            <label class="radio-opt" style="color:#6b7280">
                                <input type="radio" name="PersonSex" value="" checked onchange="sexPreview(this,'create')"> No especificar
                            </label>
                        </div>
                        <p style="font-size:.68rem;color:#9ca3af;margin-top:.25rem">
                            Valor enviado al API → <code id="sp-create" style="background:#f3f4f6;padding:0 .25rem;border-radius:.2rem">null</code>
                        </p>
                    </div>

                    <div>
                        <label class="fl">Phone *</label>
                        <input type="tel" name="Phone" required class="fi" placeholder="+521234567890">
                    </div>
                    <div>
                        <label class="fl">EmailClubLazarza *</label>
                        <input type="email" name="EmailClubLazarza" id="email-create" required class="fi">
                    </div>
                    <div>
                        <label class="fl">Confirmar Email *</label>
                        <input type="email" name="EmailConfirm" id="email-confirm-create" required class="fi">
                    </div>
                    <div>
                        <label class="fl">Province (Estado) *</label>
                        <input name="Province" required class="fi" placeholder="Jalisco">
                    </div>
                    <div>
                        <label class="fl">CityName (Municipio) *</label>
                        <input name="CityName" required class="fi" placeholder="Guadalajara">
                    </div>
                    <div class="md:col-span-2">
                        <label class="fl">DistrictName (Colonia) *</label>
                        <input name="DistrictName" required class="fi" placeholder="Centro">
                    </div>

                    <!-- Preferencias de contacto -->
                    <div class="md:col-span-2">
                        <label class="fl">Preferencias de contacto</label>
                        <div class="flex flex-wrap gap-5 mt-1">
                            <label class="radio-opt">
                                <input type="checkbox" name="ClubLazarzaPromoEmails" value="1" checked
                                       style="accent-color:#b51a8a;width:1rem;height:1rem">
                                <i class="fas fa-envelope" style="color:#6b7280;font-size:.8rem"></i> PromoEmails
                            </label>
                            <label class="radio-opt">
                                <input type="checkbox" name="ClubLazarzaPromoWhatsApp" value="1" checked
                                       style="accent-color:#b51a8a;width:1rem;height:1rem">
                                <i class="fab fa-whatsapp" style="color:#25d366;font-size:.9rem"></i> PromoWhatsApp
                            </label>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="action" value="create">
                <button class="mt-4 w-full z-bg text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition-all">
                    <i class="fas fa-plus mr-2"></i>POST /Customer
                </button>
            </form>
        </div>

        <!-- ─── Actualizar ─── -->
        <div id="tab-update" class="tab-pane <?php echo $search_mode ? 'on' : ''; ?> p-5 space-y-4">

            <form method="post" class="flex gap-2 items-end">
                <div style="flex:1">
                    <label class="fl">Código de Cliente</label>
                    <input type="text" name="CustomerCode" class="fi" style="font-family:monospace"
                           placeholder="VL008103"
                           value="<?php echo htmlspecialchars($current_customer_code ?? ''); ?>">
                </div>
                <input type="hidden" name="action" value="search_customer">
                <button class="text-white px-4 py-2 rounded-lg text-sm font-medium transition-all" style="background:#2563eb;align-self:flex-end">
                    <i class="fas fa-search mr-1"></i>Cargar
                </button>
            </form>

            <?php if ($search_mode && $customer_data): ?>
            <form method="post" id="updateForm" onsubmit="return chkForm('update')" class="space-y-3" style="border-top:1px solid #f3f4f6;padding-top:1rem">

                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.5rem;padding:.5rem .75rem;font-size:.8rem;color:#92400e">
                    <i class="fas fa-edit mr-1"></i>Editando:
                    <code style="font-weight:700"><?php echo htmlspecialchars($customer_data['Code'] ?? $current_customer_code); ?></code>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="fl">PersonName *</label>
                        <input name="PersonName" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['PersonName'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="fl">PersonLastName *</label>
                        <input name="PersonLastName" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['PersonLastName'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="fl">PersonLastName2</label>
                        <input name="PersonLastName2" class="fi"
                               value="<?php echo htmlspecialchars($customer_data['PersonLastName2'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="fl">PersonBirthDate *</label>
                        <input type="date" name="PersonBirthDate" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['PersonBirthDate'] ?? ''); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label class="fl">TaxRegNr</label>
                        <input type="text" name="TaxRegNr" id="rfc-update" class="fi" style="background:#f9fafb" readonly maxlength="12"
                               value="<?php echo htmlspecialchars($customer_data['TaxRegNr'] ?? ''); ?>">
                    </div>

                    <!-- PersonSex: radio buttons -->
                    <div class="md:col-span-2">
                        <?php
                            $curSex = $customer_data['PersonSex'] ?? '';
                            $isMasc = ($curSex === 0 || $curSex === '0');
                            $isFem  = ($curSex === 1 || $curSex === '1');
                            $isNone = !$isMasc && !$isFem;
                            $sexPreviewVal = $isMasc ? '0' : ($isFem ? '1' : 'null');
                        ?>
                        <label class="fl">PersonSex <span style="text-transform:none;color:#6b7280;font-size:.65rem">(0=Masculino, 1=Femenino)</span></label>
                        <div class="flex flex-wrap gap-5 mt-1">
                            <label class="radio-opt">
                                <input type="radio" name="PersonSex" value="0"
                                       <?php echo $isMasc ? 'checked' : ''; ?>
                                       onchange="sexPreview(this,'update')"> Masculino
                            </label>
                            <label class="radio-opt">
                                <input type="radio" name="PersonSex" value="1"
                                       <?php echo $isFem ? 'checked' : ''; ?>
                                       onchange="sexPreview(this,'update')"> Femenino
                            </label>
                            <label class="radio-opt" style="color:#6b7280">
                                <input type="radio" name="PersonSex" value=""
                                       <?php echo $isNone ? 'checked' : ''; ?>
                                       onchange="sexPreview(this,'update')"> No especificar
                            </label>
                        </div>
                        <p style="font-size:.68rem;color:#9ca3af;margin-top:.25rem">
                            Valor enviado al API → <code id="sp-update" style="background:#f3f4f6;padding:0 .25rem;border-radius:.2rem"><?php echo $sexPreviewVal; ?></code>
                        </p>
                    </div>

                    <div>
                        <label class="fl">Phone *</label>
                        <input type="tel" name="Phone" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['Phone'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="fl">EmailClubLazarza *</label>
                        <input type="email" name="EmailClubLazarza" id="email-update" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['EmailClubLazarza'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="fl">Confirmar Email *</label>
                        <input type="email" name="EmailConfirm" id="email-confirm-update" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['EmailClubLazarza'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="fl">Province (Estado) *</label>
                        <input name="Province" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['Province'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="fl">CityName (Municipio) *</label>
                        <input name="CityName" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['CityName'] ?? ''); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label class="fl">DistrictName (Colonia) *</label>
                        <input name="DistrictName" required class="fi"
                               value="<?php echo htmlspecialchars($customer_data['DistrictName'] ?? ''); ?>">
                    </div>

                    <!-- Preferencias de contacto -->
                    <div class="md:col-span-2">
                        <label class="fl">Preferencias de contacto</label>
                        <div class="flex flex-wrap gap-5 mt-1">
                            <label class="radio-opt">
                                <input type="checkbox" name="ClubLazarzaPromoEmails" value="1"
                                       <?php echo !empty($customer_data['ClubLazarzaPromoEmails']) ? 'checked' : ''; ?>
                                       style="accent-color:#b51a8a;width:1rem;height:1rem">
                                <i class="fas fa-envelope" style="color:#6b7280;font-size:.8rem"></i> PromoEmails
                            </label>
                            <label class="radio-opt">
                                <input type="checkbox" name="ClubLazarzaPromoWhatsApp" value="1"
                                       <?php echo !empty($customer_data['ClubLazarzaPromoWhatsApp']) ? 'checked' : ''; ?>
                                       style="accent-color:#b51a8a;width:1rem;height:1rem">
                                <i class="fab fa-whatsapp" style="color:#25d366;font-size:.9rem"></i> PromoWhatsApp
                            </label>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="CustomerCode" value="<?php echo htmlspecialchars($current_customer_code); ?>">
                <input type="hidden" name="action" value="update">
                <div class="flex gap-2 pt-1">
                    <button type="submit" class="flex-1 z-bg text-white py-2.5 rounded-lg font-semibold hover:opacity-90 transition-all">
                        <i class="fas fa-save mr-2"></i>PUT /Customer/<?php echo htmlspecialchars($current_customer_code); ?>
                    </button>
                    <button type="button" onclick="document.getElementById('cancelForm').submit()"
                            title="Cancelar" style="background:#d1d5db;color:#374151;padding:.625rem .875rem;border-radius:.5rem;transition:all .15s"
                            onmouseover="this.style.background='#9ca3af'" onmouseout="this.style.background='#d1d5db'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </form>
            <form id="cancelForm" method="post" style="display:none">
                <input type="hidden" name="action" value="clear_customer">
            </form>
            <?php endif; ?>

        </div>

    </div><!-- /forms -->

    <!-- ══════════ DEBUG PANEL (2/5) ══════════ -->
    <div class="lg:col-span-2 space-y-3">

        <?php if ($result !== null): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 fi-anim">

            <!-- Status + endpoint -->
            <div class="flex items-center gap-2 mb-3 flex-wrap">
                <?php if (isset($result['status'])): ?>
                <span class="<?php echo ($result['status'] >= 200 && $result['status'] < 300) ? 'bok' : 'berr'; ?>">
                    HTTP <?php echo (int)$result['status']; ?>
                </span>
                <?php endif; ?>
                <?php if (isset($result['_method'], $result['_url'])): ?>
                <span class="binfo"><?php echo htmlspecialchars($result['_method']); ?></span>
                <span style="font-size:.7rem;color:#9ca3af;font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px">
                    <?php echo htmlspecialchars(str_replace(BASE_URL, '', $result['_url'])); ?>
                </span>
                <?php endif; ?>
            </div>

            <!-- Message -->
            <?php if (isset($result['success'])): ?>
            <p style="font-size:.82rem;color:#15803d;font-weight:600;margin-bottom:.75rem;display:flex;align-items:center;gap:.35rem">
                <i class="fas fa-check-circle" style="color:#22c55e"></i>
                <?php echo htmlspecialchars($result['success']); ?>
            </p>
            <?php elseif (isset($result['error'])): ?>
            <p style="font-size:.82rem;color:#b91c1c;font-weight:600;margin-bottom:.75rem;display:flex;align-items:center;gap:.35rem">
                <i class="fas fa-exclamation-circle" style="color:#ef4444"></i>
                <?php echo htmlspecialchars($result['error']); ?>
            </p>
            <?php endif; ?>

            <!-- Request body -->
            <?php if (!empty($result['_sent_body'])): ?>
            <div style="margin-bottom:.75rem">
                <p style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem">
                    <i class="fas fa-paper-plane" style="color:#60a5fa;margin-right:.25rem"></i>Request Body
                </p>
                <div class="cbox"><?php
                    $s = json_decode($result['_sent_body'], true);
                    echo htmlspecialchars(json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                ?></div>
            </div>
            <?php endif; ?>

            <!-- Response body -->
            <?php if (!empty($result['raw'])): ?>
            <div>
                <p style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem">
                    <i class="fas fa-reply" style="color:#c084fc;margin-right:.25rem"></i>Response Body
                </p>
                <div class="cbox"><?php
                    $r = json_decode($result['raw'], true);
                    echo htmlspecialchars($r !== null
                        ? json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : $result['raw']);
                ?></div>
            </div>
            <?php endif; ?>

        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center" style="color:#9ca3af">
            <i class="fas fa-satellite-dish" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
            <p style="font-size:.82rem">Realiza una acción para ver<br>la respuesta del API aquí</p>
        </div>
        <?php endif; ?>

        <!-- Endpoints reference -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem">Endpoints</p>
            <ul style="font-size:.72rem;font-family:monospace;color:#4b5563;line-height:1.7">
                <li><span style="color:#16a34a;font-weight:700">GET</span>  /Customer?TaxRegNr=</li>
                <li><span style="color:#16a34a;font-weight:700">GET</span>  /Customer?EmailClubLazarza=</li>
                <li><span style="color:#16a34a;font-weight:700">GET</span>  /Customer?Phone=</li>
                <li><span style="color:#2563eb;font-weight:700">POST</span> /Customer</li>
                <li><span style="color:#d97706;font-weight:700">PUT</span>  /Customer/{code}</li>
            </ul>
        </div>

    </div><!-- /debug panel -->

</div><!-- /grid -->

<div id="notif" style="display:none;position:fixed;top:1rem;right:1rem;background:#16a34a;color:#fff;font-size:.82rem;padding:.5rem 1rem;border-radius:.5rem;box-shadow:0 4px 12px rgba(0,0,0,.2);z-index:50"></div>

<script>
/* ── Tabs ─────────────────────────── */
function tabSwitch(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('on'));
    document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('on'); b.classList.add('off'); });
    document.getElementById('tab-' + name).classList.add('on');
    btn.classList.add('on'); btn.classList.remove('off');
}

/* ── PersonSex preview ────────────── */
function sexPreview(radio, prefix) {
    const el = document.getElementById('sp-' + prefix);
    if (el) el.textContent = radio.value !== '' ? radio.value : 'null';
}

/* ── Copy code notification ────────── */
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        const n = document.getElementById('notif');
        n.textContent = '✓ Copiado: ' + code;
        n.style.display = 'block';
        setTimeout(() => n.style.display = 'none', 2000);
    });
}

/* ── Form validation ──────────────── */
function chkForm(type) {
    const p   = type === 'create' ? 'create' : 'update';
    const em1 = document.getElementById('email-' + p)?.value ?? '';
    const em2 = document.getElementById('email-confirm-' + p)?.value ?? '';
    if (em1 !== em2) { alert('Los correos no coinciden'); return false; }
    return true;
}

/* ── RFC auto-calc ────────────────── */
function calcRFC(n, a1, a2, bd) {
    if (!n || !a1 || !bd) return '';
    const skip  = ['DE','LA','LAS','MC','VON','DEL','LOS','Y','MAC'];
    const clean = s => s.trim().toUpperCase().split(' ').filter(w => !skip.includes(w)).join(' ') || s.toUpperCase();
    const vowel = s => { for (let i=1;i<s.length;i++) if('AEIOU'.includes(s[i])) return s[i]; return 'X'; };
    a1 = clean(a1); a2 = clean(a2||''); n = n.trim().toUpperCase();
    let r = a1[0] + vowel(a1) + (a2 ? a2[0] : 'X');
    const pts = n.split(' ');
    r += (['JOSE','MARIA','MA','MA.','J','J.'].includes(pts[0]) && pts.length > 1) ? pts[1][0] : pts[0][0];
    const d = new Date(bd + 'T00:00:00');
    r += String(d.getFullYear()).slice(-2) + String(d.getMonth()+1).padStart(2,'0') + String(d.getDate()).padStart(2,'0') + 'XX';
    const bad = ['BUEI','BUEY','CACA','COGE','CULO','FETO','GUEY','JOTO','MEAR','MEON','PUTA','PUTO','RATA'];
    return bad.includes(r.slice(0,4)) ? r[0]+'X'+r.slice(2) : r;
}
function bindRFC(sel, rfcId) {
    const f = document.querySelector(sel); if (!f) return;
    const fn = f.querySelector('[name="PersonName"]');
    const l1 = f.querySelector('[name="PersonLastName"]');
    const l2 = f.querySelector('[name="PersonLastName2"]');
    const bd = f.querySelector('[name="PersonBirthDate"]');
    const rc = document.getElementById(rfcId);
    const upd = () => { const v = calcRFC(fn?.value, l1?.value, l2?.value, bd?.value); if (v && rc) { rc.value = v; rc.style.color='#b51a8a'; } };
    [fn, l1, l2, bd].forEach(el => el?.addEventListener('input', upd));
}
document.addEventListener('DOMContentLoaded', () => {
    bindRFC('#tab-create form', 'rfc-create');
    bindRFC('#updateForm', 'rfc-update');
});
</script>

</body>
</html>