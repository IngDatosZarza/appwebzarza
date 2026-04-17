@extends('layouts.app')

@section('title', 'Gestión de Transacciones')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                    <i class="fas fa-exchange-alt mr-3"></i>
                    Transacciones
                </h1>
                <p class="text-gray-600 mt-2">Gestiona y supervisa todas las transacciones del sistema</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.transactions.export', request()->query()) }}" 
                   class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-download mr-2"></i>
                    Exportar CSV
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Transacciones</p>
                    <p class="text-3xl font-bold">{{ number_format($estadisticas['total_transacciones']) }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-list-alt text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Generado</p>
                    <p class="text-3xl font-bold">{{ number_format($estadisticas['puntos_generados']) }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-plus-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Utilizado</p>
                    <p class="text-3xl font-bold">{{ number_format($estadisticas['puntos_utilizados']) }}</p>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-minus-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Saldo Neto</p>
                    <p class="text-3xl font-bold">{{ number_format($estadisticas['saldo_neto']) }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-balance-scale text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipos de Transacciones -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">🛒 Compras</h3>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ number_format($estadisticas['total_compras']) }}
                </span>
            </div>
            <p class="text-gray-600 text-sm">Generados por compras de clientes</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">🎟️ Canjes</h3>
                <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ number_format($estadisticas['total_canjes']) }}
                </span>
            </div>
            <p class="text-gray-600 text-sm">Utilizados en canjes de cupones</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">⚖️ Ajustes</h3>
                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ number_format($estadisticas['total_ajustes']) }}
                </span>
            </div>
            <p class="text-gray-600 text-sm">Ajustes manuales del sistema</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-filter text-purple-500 mr-2"></i>
            Filtros de Búsqueda
        </h2>
        
        <form method="GET" action="{{ route('admin.transactions') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tipo de Transacción -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Transacción</label>
                    <select name="tipo" id="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Todos los tipos</option>
                        <option value="compra" {{ $filtros['tipo'] === 'compra' ? 'selected' : '' }}>🛒 Compra</option>
                        <option value="canje" {{ $filtros['tipo'] === 'canje' ? 'selected' : '' }}>🎫 Canje</option>
                        <option value="ajuste" {{ $filtros['tipo'] === 'ajuste' ? 'selected' : '' }}>⚖️ Ajuste</option>
                    </select>
                </div>

                <!-- Usuario -->
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                    <input type="text" name="usuario" id="usuario" value="{{ $filtros['usuario'] }}" 
                           placeholder="Nombre, apellido o email..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Fecha Desde -->
                <div>
                    <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="{{ $filtros['fecha_desde'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex justify-between">
                <div class="flex space-x-3">
                    <button type="submit" class="bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-search mr-2"></i>
                        Buscar
                    </button>
                    <a href="{{ route('admin.transactions') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Limpiar
                    </a>
                </div>
                
                <div class="text-sm text-gray-600 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Mostrando {{ count($transacciones) }} de {{ number_format($paginacion['total_records']) }} registros
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Transacciones -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-table text-purple-500 mr-2"></i>
                Lista de Transacciones
            </h2>
        </div>

        @if(count($transacciones) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado Por</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transacciones as $transaccion)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $transaccion['id'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-400 to-blue-400 flex items-center justify-center">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($transaccion['usuario_nombre'], 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $transaccion['usuario_nombre'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $transaccion['usuario_email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($transaccion['tipo'] === 'compra') bg-blue-100 text-blue-800
                                        @elseif($transaccion['tipo'] === 'canje') bg-orange-100 text-orange-800
                                        @elseif($transaccion['tipo'] === 'ajuste') bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $transaccion['tipo_descripcion'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($transaccion['tipo_movimiento'] === 'positivo')
                                            <span class="text-green-600 font-semibold">+{{ number_format($transaccion['puntos']) }}</span>
                                            <i class="fas fa-arrow-up text-green-500 ml-2"></i>
                                        @elseif($transaccion['tipo_movimiento'] === 'negativo')
                                            <span class="text-red-600 font-semibold">{{ number_format($transaccion['puntos']) }}</span>
                                            <i class="fas fa-arrow-down text-red-500 ml-2"></i>
                                        @else
                                            <span class="text-gray-600 font-semibold">{{ number_format($transaccion['puntos']) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $transaccion['descripcion'] }}">
                                        {{ $transaccion['descripcion'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($transaccion['registrado_por_nombre'])
                                        <div>
                                            <div class="font-medium">{{ $transaccion['registrado_por_nombre'] }}</div>
                                            <div class="text-xs">{{ $transaccion['registrado_por_email'] }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Sistema</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>
                                        <div class="font-medium">{{ date('d/m/Y', strtotime($transaccion['created_at'])) }}</div>
                                        <div class="text-xs">{{ date('H:i:s', strtotime($transaccion['created_at'])) }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($paginacion['total_pages'] > 1)
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if($paginacion['has_prev'])
                            <a href="{{ route('admin.transactions', array_merge(request()->query(), ['page' => $paginacion['current_page'] - 1])) }}" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Anterior
                            </a>
                        @endif
                        @if($paginacion['has_next'])
                            <a href="{{ route('admin.transactions', array_merge(request()->query(), ['page' => $paginacion['current_page'] + 1])) }}" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Siguiente
                            </a>
                        @endif
                    </div>
                    
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">{{ (($paginacion['current_page'] - 1) * $paginacion['per_page']) + 1 }}</span>
                                a
                                <span class="font-medium">{{ min($paginacion['current_page'] * $paginacion['per_page'], $paginacion['total_records']) }}</span>
                                de
                                <span class="font-medium">{{ number_format($paginacion['total_records']) }}</span>
                                resultados
                            </p>
                        </div>
                        
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                @if($paginacion['has_prev'])
                                    <a href="{{ route('admin.transactions', array_merge(request()->query(), ['page' => $paginacion['current_page'] - 1])) }}" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @endif
                                
                                @php
                                    $start = max(1, $paginacion['current_page'] - 2);
                                    $end = min($paginacion['total_pages'], $paginacion['current_page'] + 2);
                                @endphp
                                
                                @for($i = $start; $i <= $end; $i++)
                                    @if($i == $paginacion['current_page'])
                                        <span class="relative inline-flex items-center px-4 py-2 border border-purple-500 bg-purple-50 text-sm font-medium text-purple-600">
                                            {{ $i }}
                                        </span>
                                    @else
                                        <a href="{{ route('admin.transactions', array_merge(request()->query(), ['page' => $i])) }}" 
                                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            {{ $i }}
                                        </a>
                                    @endif
                                @endfor
                                
                                @if($paginacion['has_next'])
                                    <a href="{{ route('admin.transactions', array_merge(request()->query(), ['page' => $paginacion['current_page'] + 1])) }}" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay transacciones</h3>
                <p class="text-gray-600">No se encontraron transacciones con los filtros aplicados.</p>
                <a href="{{ route('admin.transactions') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-purple-600 bg-purple-100 hover:bg-purple-200">
                    <i class="fas fa-refresh mr-2"></i>
                    Ver todas las transacciones
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    // Auto-envío del formulario cuando cambian los filtros
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const selects = form.querySelectorAll('select');
        const dateInputs = form.querySelectorAll('input[type="date"]');
        
        [...selects, ...dateInputs].forEach(input => {
            input.addEventListener('change', function() {
                // Pequeño delay para mejor UX
                setTimeout(() => {
                    form.submit();
                }, 100);
            });
        });

        // Limpiar filtros con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = '{{ route("admin.transactions") }}';
            }
        });
    });
</script>
@endsection