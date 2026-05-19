@extends('layouts.admin')

@section('title', 'Registrar Cliente')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-user-plus text-indigo-600 mr-2"></i>
                    Registrar Nuevo Cliente
                </h1>
                <p class="text-gray-500 mt-1">
                    @php $admin = Auth::guard('admin')->user(); @endphp
                    @if($admin->esAdminSucursal() && $admin->sucursal)
                        Registrando desde: <strong>{{ $admin->sucursal->nombre }}</strong>
                    @else
                        Panel de Superadministrador
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Messages -->
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                <div>
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

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <form method="POST" action="{{ route('admin.clientes.registrar.store') }}" class="space-y-8" id="clientForm">
            @csrf

            <!-- Información Personal -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-user text-indigo-500 mr-2"></i> Información Personal
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">Nombre(s) *</label>
                        <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" required maxlength="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('nombres') border-red-500 @enderror">
                        @error('nombres')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno *</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}" required maxlength="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('apellido_paterno') border-red-500 @enderror">
                        @error('apellido_paterno')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno *</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}" required maxlength="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('apellido_materno') border-red-500 @enderror">
                        @error('apellido_materno')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required
                            max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('fecha_nacimiento') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Debe ser mayor de 18 años</p>
                        @error('fecha_nacimiento')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="rfc" class="block text-sm font-medium text-gray-700 mb-1">RFC (auto-calculado)</label>
                        <input type="text" name="rfc" id="rfc" value="{{ old('rfc') }}" maxlength="13"
                            pattern="[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}" placeholder="XAXX010101000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent uppercase bg-gray-50 @error('rfc') border-red-500 @enderror">
                        @error('rfc')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                        <input type="tel" name="telefono" id="telefono" value="{{ old('telefono') }}" required
                            pattern="\+52[0-9]{10}" placeholder="+525512345678"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('telefono') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Formato: +52 seguido de 10 dígitos</p>
                        @error('telefono')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Contacto -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-envelope text-indigo-500 mr-2"></i> Correo Electrónico
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="150"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Correo *</label>
                        <input type="email" name="email_confirmation" id="email_confirmation" value="{{ old('email_confirmation') }}" required maxlength="150"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Dirección -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i> Dirección
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                        <select name="estado" id="estado" required onchange="cargarMunicipios(this.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('estado') border-red-500 @enderror">
                            <option value="">Selecciona un estado</option>
                        </select>
                        @error('estado')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="municipio" class="block text-sm font-medium text-gray-700 mb-1">Municipio *</label>
                        <select name="municipio" id="municipio" required disabled
                            onchange="cargarColonias(document.getElementById('estado').value, this.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('municipio') border-red-500 @enderror">
                            <option value="">Selecciona un municipio</option>
                        </select>
                        @error('municipio')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="codigo_postal_id" class="block text-sm font-medium text-gray-700 mb-1">Colonia y CP *</label>
                        <select name="codigo_postal_id" id="codigo_postal_id" required disabled onchange="actualizarCamposColonia()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('codigo_postal_id') border-red-500 @enderror">
                            <option value="">Selecciona una colonia</option>
                        </select>
                        @error('codigo_postal_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="colonia" class="block text-sm font-medium text-gray-700 mb-1">Colonia (auto)</label>
                        <input type="text" name="colonia" id="colonia" value="{{ old('colonia') }}" readonly required
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label for="calle" class="block text-sm font-medium text-gray-700 mb-1">Calle</label>
                        <input type="text" name="calle" id="calle" value="{{ old('calle') }}" placeholder="Opcional" maxlength="200"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                        <input type="text" name="numero" id="numero" value="{{ old('numero') }}" placeholder="Opcional" maxlength="20"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Marketing -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-bullhorn text-indigo-500 mr-2"></i> Preferencias de Marketing
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="promo_email" value="1" {{ old('promo_email') ? 'checked' : '' }}
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700"><i class="fas fa-envelope text-indigo-500 mr-1"></i> Recibir promociones por email</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="promo_whatsapp" value="1" {{ old('promo_whatsapp') ? 'checked' : '' }}
                            class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-3 text-sm text-gray-700"><i class="fab fa-whatsapp text-green-500 mr-1"></i> Recibir promociones por WhatsApp</span>
                    </label>
                </div>
            </div>

            <!-- Campaña (opcional) -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-chart-line text-indigo-500 mr-2"></i> Campaña (Opcional)
                </h3>
                <div>
                    <label for="campana_id" class="block text-sm font-medium text-gray-700 mb-1">ID de Campaña Marketing</label>
                    <input type="text" name="campana_id" id="campana_id" value="{{ old('campana_id') }}" maxlength="100"
                        placeholder="Ej: IG-2026-SPRING, FB-PROMO-01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Si proviene de una campaña de redes sociales</p>
                </div>
            </div>

            <!-- Contraseña -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center border-b pb-3">
                    <i class="fas fa-lock text-indigo-500 mr-2"></i> Contraseña del Cliente
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña * (mín. 8 caracteres)</label>
                        <input type="password" name="password" id="password" required minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror">
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between pt-6 border-t">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-white font-medium transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
                    <i class="fas fa-user-plus mr-2"></i> Registrar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

    function calcRFC(n, a1, a2, bd) {
        if (!n || !a1 || !bd) return '';
        const skip = ['DE','LA','LAS','MC','VON','DEL','LOS','Y','MAC'];
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
        if (rfc) document.getElementById('rfc').value = rfc;
    }

    function formatearTelefono(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 0 && !value.startsWith('52')) value = '52' + value;
        if (value.length > 12) value = value.substring(0, 12);
        input.value = value.length > 2 ? '+' + value : (value ? '+' + value : '');
    }

    document.getElementById('rfc')?.addEventListener('input', function(e) { e.target.value = e.target.value.toUpperCase(); });

    document.getElementById('clientForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const emailConf = document.getElementById('email_confirmation').value;
        if (email !== emailConf) { e.preventDefault(); alert('Los correos electrónicos no coinciden'); return; }
        const pass = document.getElementById('password').value;
        const passConf = document.getElementById('password_confirmation').value;
        if (pass !== passConf) { e.preventDefault(); alert('Las contraseñas no coinciden'); }
    });

    document.addEventListener('DOMContentLoaded', function() {
        cargarEstados();
        ['nombres', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', actualizarRFC);
        });
        document.getElementById('telefono')?.addEventListener('input', function() { formatearTelefono(this); });
    });
</script>
@endpush
