@extends('layouts.admin')

@section('title', 'Crear Cupón - Admin')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center">
            <a href="{{ route('admin.coupons.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-plus-circle text-purple-600 mr-3"></i>
                    Crear Nuevo Cupón
                </h1>
                <p class="text-gray-600 mt-2">Configura un nuevo cupón para el sistema <span class="font-mercurius">La Zarza Contigo</span></p>
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

        <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-6" id="couponForm">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrf_token">
            
            <!-- Información Básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Información Básica
                    </h3>
                </div>

                <div class="md:col-span-2">
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-gray-400 mr-2"></i>
                        Nombre del Cupón *
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Ej: Descuento 20% en productos seleccionados"
                           required>
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left text-gray-400 mr-2"></i>
                        Descripción *
                    </label>
                    <textarea id="descripcion" 
                              name="descripcion" 
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="Describe los términos y condiciones del cupón..."
                              required>{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center pt-8">
                    <input type="checkbox" 
                           id="activo" 
                           name="activo" 
                           {{ old('activo', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="activo" class="ml-2 block text-sm text-gray-700">
                        <i class="fas fa-eye text-gray-400 mr-1"></i>
                        Cupón activo (visible para los usuarios)
                    </label>
                </div>
            </div>

            <!-- Vigencia -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                    Período de Vigencia
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check text-gray-400 mr-2"></i>
                            Fecha de Inicio *
                        </label>
                        <input type="date" 
                               id="fecha_inicio" 
                               name="fecha_inicio" 
                               value="{{ old('fecha_inicio', date('Y-m-d')) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               required>
                        @error('fecha_inicio')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-times text-gray-400 mr-2"></i>
                            Fecha de Fin *
                        </label>
                        <input type="date" 
                               id="fecha_fin" 
                               name="fecha_fin" 
                               value="{{ old('fecha_fin') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               required>
                        @error('fecha_fin')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Importante:</strong> Los cupones solo estarán disponibles para canje durante el período de vigencia establecido.
                    </p>
                </div>
            </div>

            <!-- Botones -->
            <div class="border-t pt-6 flex justify-end space-x-4">
                <a href="{{ route('admin.coupons.index') }}" 
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
                <button type="submit" 
                        class="zarza-bg hover:bg-purple-700 text-white px-8 py-3 rounded-lg transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Crear Cupón
                </button>
            </div>
        </form>
    </div>

    <!-- Información Adicional -->
    <div class="mt-8 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
            Consejos para crear cupones efectivos
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
            <div class="flex items-start">
                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                <span>Usa nombres descriptivos y atractivos para tus cupones</span>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                <span>Establece fechas de vigencia claras y realistas</span>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                <span>Detalla claramente el beneficio que ofrece el cupón</span>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                <span>Incluye términos y condiciones detallados</span>
            </div>
        </div>
    </div>
</div>

<style>
.zarza-bg { background-color: #b51a8a; }
</style>

<script>
// Validar fechas
document.getElementById('fecha_inicio').addEventListener('change', function() {
    const fechaInicio = new Date(this.value);
    const fechaFinInput = document.getElementById('fecha_fin');
    
    // Establecer fecha mínima para fecha fin
    fechaFinInput.min = this.value;
    
    // Si fecha fin es anterior, resetearla
    if (fechaFinInput.value && new Date(fechaFinInput.value) <= fechaInicio) {
        fechaFinInput.value = '';
    }
});

// Renovar token CSRF automáticamente cada 30 minutos
function renewCSRFToken() {
    fetch('/admin/cupones/crear', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extraer el nuevo token CSRF
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newToken = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (newToken) {
            // Actualizar todos los tokens CSRF en la página
            const csrfInputs = document.querySelectorAll('input[name="_token"]');
            csrfInputs.forEach(input => {
                input.value = newToken;
            });
            
            // Actualizar meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', newToken);
            }
            
            console.log('Token CSRF renovado');
        }
    })
    .catch(error => {
        console.log('Error renovando token CSRF:', error);
    });
}

// Renovar token cada 30 minutos
setInterval(renewCSRFToken, 30 * 60 * 1000);

// Manejar envío del formulario con validación de errores
document.getElementById('couponForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Deshabilitar botón para evitar doble envío
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando Cupón...';
    
    // Re-habilitar después de 5 segundos por si hay error
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Crear Cupón';
    }, 5000);
});

// Mostrar alerta si el usuario intenta salir con cambios sin guardar
let formChanged = false;
document.querySelectorAll('#couponForm input, #couponForm textarea, #couponForm select').forEach(element => {
    element.addEventListener('change', () => {
        formChanged = true;
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que deseas salir?';
    }
});
</script>
@endsection