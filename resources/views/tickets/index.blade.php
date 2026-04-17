@extends('layouts.app')

@section('title', 'Mis Tickets')

@push('styles')
<style>
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .ticket-status {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Mis Tickets</h1>
                <p class="text-lg text-gray-600">Historial completo de tus tickets registrados</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('tickets.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-semibold shadow-lg">
                    <i class="fas fa-plus mr-2"></i>
                    Registrar Ticket
                </a>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Tickets -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Tickets</p>
                        <p class="text-3xl font-bold">{{ number_format($estadisticas['total_tickets']) }}</p>
                    </div>
                    <div class="text-4xl opacity-75">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
            </div>

            <!-- Monto Total -->
            <div class="bg-gradient-to-r from-orange-500 to-red-600 rounded-xl shadow-lg p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Monto Total</p>
                        <p class="text-3xl font-bold">${{ number_format($estadisticas['monto_total'], 2) }}</p>
                    </div>
                    <div class="text-4xl opacity-75">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Tickets -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-list text-gray-600 mr-2"></i>
                    Historial de Tickets
                </h2>
            </div>

            @if($tickets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ticket
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sucursal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Monto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-ticket-alt text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                #{{ $ticket->numero_ticket }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $ticket->metodo_pago ? ucfirst($ticket->metodo_pago) : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ticket->sucursal->nombre }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($ticket->sucursal->direccion, 30) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        ${{ number_format($ticket->monto, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $ticket->fecha_compra ? $ticket->fecha_compra->format('d/m/Y') : $ticket->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $ticket->fecha_compra ? $ticket->fecha_compra->format('H:i') : $ticket->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 ticket-status">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Registrado
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('tickets.show', $ticket->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                        <i class="fas fa-eye mr-1"></i>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    {{ $tickets->links() }}
                </div>
            @else
                <!-- Estado Vacío -->
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <i class="fas fa-ticket-alt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes tickets registrados</h3>
                    <p class="text-gray-500 mb-6">¡Registra tu primer ticket y comienza a ganar puntos!</p>
                    <a href="{{ route('tickets.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-semibold shadow-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Registrar Mi Primer Ticket
                    </a>
                </div>
            @endif
        </div>

        <!-- Acciones Rápidas -->
        @if($tickets->count() > 0)
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="{{ route('tickets.create') }}" 
               class="group bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 card-hover">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                            <i class="fas fa-plus text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Registrar Otro Ticket</h3>
                        <p class="text-sm text-gray-600">Gana 100 puntos más</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('coupons.index') }}" 
               class="group bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 card-hover">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors duration-200">
                            <i class="fas fa-gift text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Ver Cupones</h3>
                        <p class="text-sm text-gray-600">Canjea tus puntos</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('points.history') }}" 
               class="group bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 card-hover">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                            <i class="fas fa-history text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Historial de Puntos</h3>
                        <p class="text-sm text-gray-600">Ver movimientos</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
</div>
@endsection