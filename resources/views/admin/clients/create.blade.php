@extends('layouts.app')

@section('title', 'Registrar Cliente - Admin')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.points') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                        Registrar Nuevo Cliente
                    </h1>
                    <p class="text-gray-600 mt-2">Crea un nuevo cliente en el sistema La Zarza Contigo</p>
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
                        <input type="text" 
                               name="estado" 
                               id="estado" 
                               value="{{ old('estado') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('estado') border-red-500 @enderror"
                               required>
                        @error('estado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="municipio" class="block text-sm font-medium text-gray-700 mb-2">
                            Municipio <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="municipio" 
                               id="municipio" 
                               value="{{ old('municipio') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('municipio') border-red-500 @enderror"
                               required>
                        @error('municipio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="codigo_postal_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Código Postal <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="codigo_postal_id" 
                               id="codigo_postal_id" 
                               value="{{ old('codigo_postal_id') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('codigo_postal_id') border-red-500 @enderror"
                               required
                               placeholder="1">
                        <p class="text-xs text-gray-500 mt-1">ID del código postal en la BD</p>
                        @error('codigo_postal_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="colonia" class="block text-sm font-medium text-gray-700 mb-2">
                            Colonia <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="colonia" 
                               id="colonia" 
                               value="{{ old('colonia') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('colonia') border-red-500 @enderror"
                               required>
                        @error('colonia')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="calle" class="block text-sm font-medium text-gray-700 mb-2">
                            Calle <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="calle" 
                               id="calle" 
                               value="{{ old('calle') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('calle') border-red-500 @enderror"
                               required
                               maxlength="200">
                        @error('calle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                            Número <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="numero" 
                               id="numero" 
                               value="{{ old('numero') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('numero') border-red-500 @enderror"
                               required
                               maxlength="20">
                        @error('numero')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                <a href="{{ route('admin.points') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
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
