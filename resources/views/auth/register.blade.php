<?php 
// Incluir helpers CSRF
$helperPath = realpath(__DIR__ . '/../../../app/Helpers/csrf_helper.php');
if ($helperPath && file_exists($helperPath)) {
    require_once $helperPath;
} else {
    // Fallback: crear funciones básicas si no se encuentra el helper
    if (!function_exists('csrf_token')) {
        function csrf_token() {
            if (session_status() == PHP_SESSION_NONE) session_start();
            if (!isset($_SESSION['_token'])) {
                $_SESSION['_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['_token'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - ZarzaPoints</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <!-- Tailwind CSS CDN - Solo para desarrollo, cambiar a build para producción -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
        // Funciones para carga dinámica de estados, municipios y colonias
        async function cargarEstados() {
            try {
                const response = await fetch('/api/codigos-postales/estados');
                const data = await response.json();
                if (data.success) {
                    const select = document.getElementById('estado');
                    select.innerHTML = '<option value="">Selecciona un estado</option>';
                    data.data.forEach(estado => {
                        select.innerHTML += `<option value="${estado}">${estado}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error cargando estados:', error);
            }
        }

        async function cargarMunicipios(estado) {
            if (!estado) return;
            try {
                const response = await fetch(`/api/codigos-postales/municipios?estado=${encodeURIComponent(estado)}`);
                const data = await response.json();
                if (data.success) {
                    const select = document.getElementById('municipio');
                    select.innerHTML = '<option value="">Selecciona un municipio</option>';
                    select.disabled = false;
                    data.data.forEach(municipio => {
                        select.innerHTML += `<option value="${municipio}">${municipio}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error cargando municipios:', error);
            }
        }

        async function cargarColonias(estado, municipio) {
            if (!estado || !municipio) return;
            try {
                const response = await fetch(`/api/codigos-postales/colonias?estado=${encodeURIComponent(estado)}&municipio=${encodeURIComponent(municipio)}`);
                const data = await response.json();
                if (data.success) {
                    const select = document.getElementById('codigo_postal_id');
                    select.innerHTML = '<option value="">Selecciona una colonia</option>';
                    select.disabled = false;
                    data.data.forEach(item => {
                        select.innerHTML += `<option value="${item.id}" data-cp="${item.codigo_postal}" data-colonia="${item.colonia}">${item.colonia} (CP: ${item.codigo_postal})</option>`;
                    });
                }
            } catch (error) {
                console.error('Error cargando colonias:', error);
            }
        }

        function actualizarCamposColonia() {
            const select = document.getElementById('codigo_postal_id');
            const option = select.options[select.selectedIndex];
            if (option && option.value) {
                document.getElementById('colonia').value = option.dataset.colonia || '';
            }
        }

        // Validación de teléfono en tiempo real
        function formatearTelefono(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('52')) {
                value = '52' + value;
            }
            if (value.length > 12) {
                value = value.substring(0, 12);
            }
            if (value.length > 2) {
                input.value = '+' + value;
            } else {
                input.value = value ? '+' + value : '';
            }
        }

        // Validación de RFC en tiempo real
        function formatearRFC(input) {
            input.value = input.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
            if (input.value.length > 13) {
                input.value = input.value.substring(0, 13);
            }
        }

        // Cargar estados al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarEstados();
        });
    </script>
    
    <style>
        .zarza-gradient { background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%); }
        .zarza-gradient-reverse { background: linear-gradient(135deg, #71398d 0%, #b51a8a 100%); }
        .form-input:focus { border-color: #b51a8a; box-shadow: 0 0 0 3px rgba(181, 26, 138, 0.1); }
        .zarza-text { color: #b51a8a; }
        .zarza-bg { background-color: #b51a8a; }
        .zarza-bg-hover:hover { background-color: #71398d; }
        .zarza-border { border-color: #b51a8a; }
        .zarza-ring:focus { --tw-ring-color: #b51a8a; }
    </style>
</head>
<body class="zarza-gradient min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <div>
            <div class="text-center">
                <div class="mx-auto mb-6">
                    <img src="/logoZarza.webp" alt="ZarzaPoints" class="h-20 w-auto mx-auto mb-4 drop-shadow-lg">
                    
                </div>
                <h2 class="text-3xl font-extrabold text-white">
                    Crear Cuenta
                </h2>
                <p class="mt-2 text-sm text-pink-100">
                    Únete a ZarzaPoints y comienza a ganar puntos
                </p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-xl p-8" x-data="{ showPassword: false, showPasswordConfirmation: false }">
            <form method="POST" action="/register" class="space-y-6">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombres -->
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-gray-400 mr-2"></i>
                            Nombres *
                        </label>
                        <input 
                            id="nombres" 
                            name="nombres" 
                            type="text" 
                            autocomplete="given-name"
                            required 
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            placeholder="Juan"
                            value="<?= htmlspecialchars(old('nombres', '')) ?>"
                        >
                        <?php if (has_error('nombres')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('nombres')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Apellido Paterno -->
                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-gray-400 mr-2"></i>
                            Apellido Paterno *
                        </label>
                        <input 
                            id="apellido_paterno" 
                            name="apellido_paterno" 
                            type="text" 
                            autocomplete="family-name"
                            required 
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            placeholder="Pérez"
                            value="<?= htmlspecialchars(old('apellido_paterno', '')) ?>"
                        >
                        <?php if (has_error('apellido_paterno')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('apellido_paterno')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Apellido Materno -->
                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-gray-400 mr-2"></i>
                            Apellido Materno *
                        </label>
                        <input 
                            id="apellido_materno" 
                            name="apellido_materno" 
                            type="text" 
                            required
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            placeholder="García"
                            value="<?= htmlspecialchars(old('apellido_materno', '')) ?>"
                        >
                        <?php if (has_error('apellido_materno')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('apellido_materno')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope text-gray-400 mr-2"></i>
                            Correo Electrónico *
                        </label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email"
                            required 
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            placeholder="juan@email.com"
                            value="<?= htmlspecialchars(old('email', '')) ?>"
                        >
                        <?php if (has_error('email')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('email')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Confirmación de Email -->
                    <div>
                        <label for="email_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope-open text-gray-400 mr-2"></i>
                            Confirmar Correo *
                        </label>
                        <input 
                            id="email_confirmation" 
                            name="email_confirmation" 
                            type="email" 
                            autocomplete="email"
                            required 
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            placeholder="juan@email.com"
                            value="<?= htmlspecialchars(old('email_confirmation', '')) ?>"
                        >
                        <?php if (has_error('email_confirmation')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('email_confirmation')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone text-gray-400 mr-2"></i>
                            Teléfono *
                        </label>
                        <input 
                            id="telefono" 
                            name="telefono" 
                            type="tel" 
                            autocomplete="tel"
                            required
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            placeholder="+52 1234567890"
                            value="<?= htmlspecialchars(old('telefono', '')) ?>"
                            oninput="formatearTelefono(this)"
                            maxlength="13"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Formato: +52 seguido de 10 dígitos
                        </p>
                        <?php if (has_error('telefono')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('telefono')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- RFC -->
                    <div>
                        <label for="rfc" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card text-gray-400 mr-2"></i>
                            RFC
                        </label>
                        <input 
                            id="rfc" 
                            name="rfc" 
                            type="text" 
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            placeholder="ABCD123456XYZ"
                            value="<?= htmlspecialchars(old('rfc', '')) ?>"
                            oninput="formatearRFC(this)"
                            maxlength="13"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            13 caracteres alfanuméricos
                        </p>
                        <?php if (has_error('rfc')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('rfc')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar text-gray-400 mr-2"></i>
                            Fecha de Nacimiento *
                        </label>
                        <input 
                            id="fecha_nacimiento" 
                            name="fecha_nacimiento" 
                            type="date" 
                            autocomplete="bday"
                            required
                            max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                            class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            value="<?= htmlspecialchars(old('fecha_nacimiento', '')) ?>"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Debes ser mayor de 18 años
                        </p>
                        <?php if (has_error('fecha_nacimiento')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('fecha_nacimiento')) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sección de Dirección -->
                <div class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-map-marker-alt text-pink-600 mr-2"></i>
                        Dirección
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-map text-gray-400 mr-2"></i>
                                Estado *
                            </label>
                            <select 
                                id="estado" 
                                name="estado" 
                                required
                                onchange="cargarMunicipios(this.value)"
                                class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                            >
                                <option value="">Cargando estados...</option>
                            </select>
                            <?php if (has_error('estado')): ?>
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars(get_error('estado')) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Municipio -->
                        <div>
                            <label for="municipio" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-city text-gray-400 mr-2"></i>
                                Municipio *
                            </label>
                            <select 
                                id="municipio" 
                                name="municipio" 
                                required
                                disabled
                                onchange="cargarColonias(document.getElementById('estado').value, this.value)"
                                class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm disabled:bg-gray-100"
                            >
                                <option value="">Selecciona primero un estado</option>
                            </select>
                            <?php if (has_error('municipio')): ?>
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars(get_error('municipio')) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Colonia y CP -->
                        <div class="md:col-span-2">
                            <label for="codigo_postal_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-home text-gray-400 mr-2"></i>
                                Colonia *
                            </label>
                            <select 
                                id="codigo_postal_id" 
                                name="codigo_postal_id" 
                                required
                                disabled
                                onchange="actualizarCamposColonia()"
                                class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm disabled:bg-gray-100"
                            >
                                <option value="">Selecciona primero estado y municipio</option>
                            </select>
                            <input type="hidden" id="colonia" name="colonia">
                            <?php if (has_error('colonia')): ?>
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars(get_error('colonia')) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Calle -->
                        <div>
                            <label for="calle" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-road text-gray-400 mr-2"></i>
                                Calle *
                            </label>
                            <input 
                                id="calle" 
                                name="calle" 
                                type="text" 
                                required
                                class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                                placeholder="Av. Reforma"
                                value="<?= htmlspecialchars(old('calle', '')) ?>"
                            >
                            <?php if (has_error('calle')): ?>
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars(get_error('calle')) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Número -->
                        <div>
                            <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-hashtag text-gray-400 mr-2"></i>
                                Número *
                            </label>
                            <input 
                                id="numero" 
                                name="numero" 
                                type="text" 
                                required
                                class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                                placeholder="123"
                                value="<?= htmlspecialchars(old('numero', '')) ?>"
                            >
                            <?php if (has_error('numero')): ?>
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars(get_error('numero')) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-gray-400 mr-2"></i>
                            Contraseña *
                        </label>
                        <div class="relative">
                            <input 
                                id="password" 
                                name="password" 
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required 
                                class="form-input appearance-none relative block w-full px-3 py-3 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                                placeholder="Mínimo 8 caracteres"
                            >
                            <button 
                                type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                @click="showPassword = !showPassword"
                            >
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                        <?php if (has_error('password')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('password')) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-gray-400 mr-2"></i>
                            Confirmar Contraseña *
                        </label>
                        <div class="relative">
                            <input 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                autocomplete="new-password"
                                required 
                                class="form-input appearance-none relative block w-full px-3 py-3 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                                placeholder="Confirma tu contraseña"
                            >
                            <button 
                                type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                            >
                                <i :class="showPasswordConfirmation ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                        <?php if (has_error('password_confirmation')): ?>
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars(get_error('password_confirmation')) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-center">
                    <input 
                        id="terms" 
                        name="terms" 
                        type="checkbox" 
                        required
                        class="h-4 w-4 zarza-text zarza-ring border-gray-300 rounded"
                    >
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        Acepto los 
                        <a href="#" class="zarza-text hover:text-pink-600 font-medium">términos y condiciones</a> 
                        y la 
                        <a href="#" class="zarza-text hover:text-pink-600 font-medium">política de privacidad</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white zarza-bg zarza-bg-hover focus:outline-none focus:ring-2 focus:ring-offset-2 zarza-ring transition-colors duration-200"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus text-pink-200 group-hover:text-pink-100"></i>
                        </span>
                        Crear Cuenta
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿Ya tienes una cuenta?
                        <a href="/login" class="font-medium zarza-text hover:text-pink-600">
                            Inicia sesión aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Benefits -->
        <div class="bg-white bg-opacity-20 rounded-lg p-6 text-white">
            <h3 class="font-semibold mb-4 text-center">
                <i class="fas fa-gift mr-2"></i>
                ¿Por qué unirse a ZarzaPoints?
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="text-center">
                    <i class="fas fa-coins text-yellow-300 text-xl mb-2"></i>
                    <p><strong>Gana Puntos</strong><br>1 punto por cada peso gastado</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-ticket-alt text-green-300 text-xl mb-2"></i>
                    <p><strong>Cupones Exclusivos</strong><br>Descuentos especiales</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-trophy text-orange-300 text-xl mb-2"></i>
                    <p><strong>Niveles VIP</strong><br>Beneficios premium</p>
                </div>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="/" class="inline-flex items-center text-white hover:text-pink-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                <button @click="show = false" class="ml-4 text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <script>
        // DEBUG: Monitorear envío del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="/register"]');
            if (form) {
                console.log('✅ Formulario encontrado');
                
                form.addEventListener('submit', function(e) {
                    console.log('🚀 FORMULARIO ENVIÁNDOSE');
                    console.log('Action:', form.action);
                    console.log('Method:', form.method);
                    
                    const formData = new FormData(form);
                    const data = {};
                    for (let [key, value] of formData.entries()) {
                        if (key !== 'password' && key !== 'password_confirmation' && key !== '_token') {
                            data[key] = value;
                        }
                    }
                    console.log('Datos:', data);
                    console.log('✅ Formulario se enviará normalmente');
                    
                    // NO prevenir - dejar que se envíe
                });
                
                form.addEventListener('invalid', function(e) {
                    console.warn('⚠️ Campo inválido:', e.target.name, e.target.validationMessage);
                }, true);
            } else {
                console.error('❌ No se encontró el formulario');
            }
        });
    </script>
</body>
</html>