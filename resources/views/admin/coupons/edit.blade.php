@extends('layouts.app')

@section('title', 'Editar Cupón - Admin')

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
                    <i class="fas fa-edit text-blue-600 mr-3"></i>
                    Editar Cupón
                </h1>
                <p class="text-gray-600 mt-2">Modifica la configuración del cupón</p>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <form method="POST" action="{{ route('admin.coupons.update', $cupon['id']) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
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
                           value="{{ old('nombre', $cupon['nombre']) }}"
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
                              required>{{ old('descripcion', $cupon['descripcion']) }}</textarea>
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
                           {{ old('activo', $cupon['activo']) ? 'checked' : '' }}
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
                               value="{{ old('fecha_inicio', $cupon['fecha_inicio']) }}"
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
                               value="{{ old('fecha_fin', $cupon['fecha_fin']) }}"
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

                <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                    <p class="text-sm text-yellow-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Atención:</strong> Cambiar las fechas de vigencia puede afectar a los cupones ya asignados a los usuarios.
                    </p>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-purple-500 mr-2"></i>
                    Estadísticas del Cupón
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-ticket-alt text-2xl text-blue-600 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-600">Total Asignados</p>
                                <p class="text-2xl font-bold text-blue-800">{{ $cupon['created_at'] ? '0' : '0' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-2xl text-green-600 mr-3"></i>
                            <div>
                                <p class="text-sm text-green-600">Cupones Usados</p>
                                <p class="text-2xl font-bold text-green-800">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-2xl text-yellow-600 mr-3"></i>
                            <div>
                                <p class="text-sm text-yellow-600">Disponibles</p>
                                <p class="text-2xl font-bold text-yellow-800">0</p>
                            </div>
                        </div>
                    </div>
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
                    Actualizar Cupón
                </button>
            </div>
        </form>
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
</script>
@endsection