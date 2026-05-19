@extends('layouts.app')

@section('title', 'Registrar Cliente - Admin')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                        Registrar Nuevo Cliente
                    </h1>
                    <p class="text-gray-600 mt-2">Crea un nuevo cliente en el sistema <span class="font-mercurius">La Zarza Contigo</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Mensaje de error CSRF -->
        @if(session('error') && str_contains(session('error'), '419'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                    <div>
                        <h4 class="text-red-800 font-medium">Sesión Expirada</h4>
                        <p class="text-red-700 text-sm mt-1">La página ha expirado. Por favor, recarga la página e intenta nuevamente.</p>
                        <button onclick="location.reload()" class="mt-2 text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                            <i class="fas fa-refresh mr-1"></i> Recargar Página
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Mensajes de éxito -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Mensajes de error -->
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <h4 class="text-red-800 font-medium mb-2">Por favor corrige los siguientes errores:</h4>
                        <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.clients.store') }}" class="space-y-8" id="clientForm">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrf_token">
            
            <!-- Información Personal -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-user text-blue-500 mr-3"></i>
                    Información Personal
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre(s) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="nombres" 
                               id="nombres" 
                               value="{{ old('nombres') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nombres') border-red-500 @enderror"
                               required
                               maxlength="100">
                        @error('nombres')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-2">
                            Apellido Paterno <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="apellido_paterno" 
                               id="apellido_paterno" 
                               value="{{ old('apellido_paterno') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('apellido_paterno') border-red-500 @enderror"
                               required
                               maxlength="100">
                        @error('apellido_paterno')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-2">
                            Apellido Materno <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="apellido_materno" 
                               id="apellido_materno" 
                               value="{{ old('apellido_materno') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('apellido_materno') border-red-500 @enderror"
                               required
                               maxlength="100">
                        @error('apellido_materno')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Nacimiento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="fecha_nacimiento" 
                               id="fecha_nacimiento" 
                               value="{{ old('fecha_nacimiento') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('fecha_nacimiento') border-red-500 @enderror"
                               required
                               max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                        <p class="text-xs text-gray-500 mt-1">Debe ser mayor de 18 años</p>
                        @error('fecha_nacimiento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rfc" class="block text-sm font-medium text-gray-700 mb-2">
                            RFC <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="rfc" 
                               id="rfc" 
                               value="{{ old('rfc') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase @error('rfc') border-red-500 @enderror"
                               required
                               maxlength="13"
                               pattern="[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}"
                               placeholder="XAXX010101000">
                        <p class="text-xs text-gray-500 mt-1">13 caracteres</p>
                        @error('rfc')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                            Teléfono <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" 
                               name="telefono" 
                               id="telefono" 
                               value="{{ old('telefono') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('telefono') border-red-500 @enderror"
                               required
                               pattern="\+52[0-9]{10}"
                               placeholder="+525512345678">
                        <p class="text-xs text-gray-500 mt-1">Formato: +52 seguido de 10 dígitos</p>
                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-envelope text-blue-500 mr-3"></i>
                    Información de Contacto
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                               required
                               maxlength="150">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               name="email_confirmation" 
                               id="email_confirmation" 
                               value="{{ old('email_confirmation') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email_confirmation') border-red-500 @enderror"
                               required
                               maxlength="150">
                        @error('email_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Dirección -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-map-marker-alt text-blue-500 mr-3"></i>
                    Dirección
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <select 
                               name="estado" 
                               id="estado" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('estado') border-red-500 @enderror"
                               required
                               onchange="cargarMunicipios(this.value)">
                            <option value="">Selecciona un estado</option>
                        </select>
                        @error('estado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="municipio" class="block text-sm font-medium text-gray-700 mb-2">
                            Municipio <span class="text-red-500">*</span>
                        </label>
                        <select 
                               name="municipio" 
                               id="municipio" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('municipio') border-red-500 @enderror"
                               required
                               disabled
                               onchange="cargarColonias(document.getElementById('estado').value, this.value)">
                            <option value="">Selecciona un municipio</option>
                        </select>
                        @error('municipio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="codigo_postal_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Colonia y CP <span class="text-red-500">*</span>
                        </label>
                        <select 
                               name="codigo_postal_id" 
                               id="codigo_postal_id" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('codigo_postal_id') border-red-500 @enderror"
                               required
                               disabled
                               onchange="actualizarCamposColonia()">
                            <option value="">Selecciona una colonia</option>
                        </select>
                        @error('codigo_postal_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="colonia" class="block text-sm font-medium text-gray-700 mb-2">
                            Colonia (auto) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="colonia" 
                               id="colonia" 
                               value="{{ old('colonia') }}"
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('colonia') border-red-500 @enderror"
                               readonly
                               required>
                        @error('colonia')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="calle" class="block text-sm font-medium text-gray-700 mb-2">
                            Calle
                        </label>
                        <input type="text" 
                               name="calle" 
                               id="calle" 
                               value="{{ old('calle') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('calle') border-red-500 @enderror"
                               placeholder="Opcional"
                               maxlength="200">
                        @error('calle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                            Número
                        </label>
                        <input type="text" 
                               name="numero" 
                               id="numero" 
                               value="{{ old('numero') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('numero') border-red-500 @enderror"
                               placeholder="Opcional"
                               maxlength="20">
                        @error('numero')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Preferencias de Marketing (Opcional) -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-bullhorn text-blue-500 mr-3"></i>
                    Preferencias de Marketing
                </h3>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mr-3 mt-1"></i>
                        <p class="text-sm text-blue-800">
                            Estas preferencias se sincronizarán con el sistema Oppen y permiten al cliente recibir promociones y ofertas especiales.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="promo_email" 
                               id="promo_email" 
                               value="1"
                               {{ old('promo_email') ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <label for="promo_email" class="ml-3 text-sm font-medium text-gray-700">
                            <i class="fas fa-envelope text-blue-500 mr-2"></i>
                            Acepta recibir promociones por correo electrónico
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="promo_whatsapp" 
                               id="promo_whatsapp" 
                               value="1"
                               {{ old('promo_whatsapp') ? 'checked' : '' }}
                               class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-2 focus:ring-green-500">
                        <label for="promo_whatsapp" class="ml-3 text-sm font-medium text-gray-700">
                            <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                            Acepta recibir promociones por WhatsApp
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tracking de Registro (Opcional) -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-chart-line text-blue-500 mr-3"></i>
                    Información de Campaña (Opcional)
                </h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="campana_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fab fa-instagram text-pink-500 mr-2"></i>
                            ID de Campaña Marketing
                        </label>
                        <input type="text" 
                               name="campana_id" 
                               id="campana_id" 
                               value="{{ old('campana_id') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ej: IG-2026-SPRING, FB-PROMO-01"
                               maxlength="100">
                        <p class="text-xs text-gray-500 mt-1">Si este registro proviene de una campaña de Instagram, Facebook u otra red social, ingresa el identificador de la campaña</p>
                    </div>
                </div>
            </div>

            <!-- Contraseña -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-lock text-blue-500 mr-3"></i>
                    Contraseña
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                               required
                               minlength="8">
                        <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password_confirmation') border-red-500 @enderror"
                               required
                               minlength="8">
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex items-center justify-between pt-6 border-t">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
                
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>
                    Registrar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Funciones para carga dinámica de estados, municipios y colonias (API de Oppen)
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
                // Limpiar colonias
                document.getElementById('codigo_postal_id').innerHTML = '<option value="">Selecciona una colonia</option>';
                document.getElementById('codigo_postal_id').disabled = true;
                document.getElementById('colonia').value = '';
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

    // Auto-calcular RFC (réplica de calcRFC de lazarza_forms_advanced.php)
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
        r += String(d.getFullYear()).slice(-2) + String(d.getMonth()+1).padStart(2,'0') + String(d.getDate()).padStart(2,'0') + 'XX0';
        const bad = ['BUEI','BUEY','CACA','COGE','CULO','FETO','GUEY','JOTO','MEAR','MEON','PUTA','PUTO','RATA'];
        return bad.includes(r.slice(0,4)) ? r[0]+'X'+r.slice(2) : r;
    }

    function actualizarRFC() {
        const n  = document.getElementById('nombres')?.value || '';
        const a1 = document.getElementById('apellido_paterno')?.value || '';
        const a2 = document.getElementById('apellido_materno')?.value || '';
        const bd = document.getElementById('fecha_nacimiento')?.value || '';
        const rfc = calcRFC(n, a1, a2, bd);
        const rfcInput = document.getElementById('rfc');
        if (rfcInput && rfc) rfcInput.value = rfc;
    }

    // Formatear teléfono en tiempo real
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

    // Formatear RFC a mayúsculas
    document.getElementById('rfc').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Validar coincidencia de emails
    document.getElementById('clientForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const emailConfirmation = document.getElementById('email_confirmation').value;
        
        if (email !== emailConfirmation) {
            e.preventDefault();
            alert('Los correos electrónicos no coinciden');
        }
        
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;
        
        if (password !== passwordConfirmation) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
        }
    });

    // Cargar estados al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        cargarEstados();

        // Vincular auto-cálculo de RFC con los campos del formulario
        const campos = ['nombres', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento'];
        campos.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', actualizarRFC);
        });

        // Formatear teléfono automáticamente
        const telefonoInput = document.getElementById('telefono');
        if (telefonoInput) {
            telefonoInput.addEventListener('input', function() {
                formatearTelefono(this);
            });
        }
    });

    // Auto-refresh del token CSRF cada 10 minutos
    setInterval(function() {
        fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('csrf_token').value = data.token;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
        })
        .catch(error => console.log('Error al actualizar token:', error));
    }, 600000); // 10 minutos
</script>
@endsection
