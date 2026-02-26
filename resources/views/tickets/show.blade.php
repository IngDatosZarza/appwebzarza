@extends('layouts.app')

@section('title', 'Detalle del Ticket')

@push('styles')
<style>
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .detail-item {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .status-badge {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div>
                <div class="flex items-center mb-2">
                    <a href="{{ route('tickets.index') }}" 
                       class="text-gray-500 hover:text-gray-700 mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-4xl font-bold text-gray-900">Ticket #{{ $ticket->numero_ticket }}</h1>
                </div>
                <p class="text-lg text-gray-600">Detalles completos del ticket registrado</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium status-badge">
                    <i class="fas fa-check-circle mr-2"></i>
                    Registrado
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Información Principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Resumen del Ticket -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fas fa-ticket-alt mr-2"></i>
                            Información del Ticket
                        </h2>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        <div class="detail-item">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Número de Ticket</label>
                                    <div class="text-lg font-semibold text-gray-900">#{{ $ticket->numero_ticket }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Monto</label>
                                    <div class="text-lg font-semibold text-green-600">${{ number_format($ticket->monto, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Puntos Generados</label>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-coins mr-1"></i>
                                            +{{ $ticket->puntos_generados }} puntos
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Método de Pago</label>
                                    <div class="flex items-center text-gray-900">
                                        @if($ticket->metodo_pago === 'efectivo')
                                            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                            Efectivo
                                        @elseif($ticket->metodo_pago === 'tarjeta')
                                            <i class="fas fa-credit-card text-blue-500 mr-2"></i>
                                            Tarjeta
                                        @elseif($ticket->metodo_pago === 'transferencia')
                                            <i class="fas fa-university text-purple-500 mr-2"></i>
                                            Transferencia
                                        @else
                                            <i class="fas fa-question-circle text-gray-500 mr-2"></i>
                                            No especificado
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Compra</label>
                                    <div class="text-gray-900">
                                        <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                        {{ $ticket->fecha_compra ? $ticket->fecha_compra->format('d/m/Y H:i') : $ticket->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Registro</label>
                                    <div class="text-gray-900">
                                        <i class="fas fa-clock text-gray-400 mr-2"></i>
                                        {{ $ticket->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($ticket->descripcion)
                        <div class="detail-item">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Descripción</label>
                            <p class="text-gray-900">{{ $ticket->descripcion }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Información de la Sucursal -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fas fa-store mr-2"></i>
                            Sucursal
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-purple-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $ticket->sucursal->nombre }}</h3>
                                <p class="text-gray-600 mb-2">{{ $ticket->sucursal->direccion }}</p>
                                @if($ticket->sucursal->telefono)
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-phone mr-1"></i>
                                        {{ $ticket->sucursal->telefono }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Resumen de Puntos -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white card-hover">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-gift text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">¡Puntos Ganados!</h3>
                        <div class="text-4xl font-bold mb-1">+{{ $ticket->puntos_generados }}</div>
                        <p class="text-green-100 text-sm">Con este ticket</p>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cog text-gray-600 mr-2"></i>
                        Acciones
                    </h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('tickets.index') }}" 
                           class="w-full flex items-center justify-center px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                            <i class="fas fa-list mr-2"></i>
                            Ver Todos los Tickets
                        </a>
                        
                        <a href="{{ route('tickets.create') }}" 
                           class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Registrar Nuevo Ticket
                        </a>
                        
                        <a href="{{ route('coupons.index') }}" 
                           class="w-full flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                            <i class="fas fa-gift mr-2"></i>
                            Ver Cupones Disponibles
                        </a>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        <h3 class="text-sm font-semibold text-yellow-900">Información</h3>
                    </div>
                    
                    <div class="text-xs text-yellow-800 space-y-2">
                        <p>• Este ticket ya fue registrado y los puntos acreditados</p>
                        <p>• Los puntos están disponibles para canje inmediatamente</p>
                        <p>• Puedes ver tu historial completo en tu perfil</p>
                    </div>
                </div>

                <!-- Compartir (Opcional) -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-share text-gray-600 mr-2"></i>
                        Compartir
                    </h3>
                    
                    <p class="text-gray-600 text-sm mb-4">
                        ¡Cuéntales a tus amigos sobre los puntos que ganas!
                    </p>
                    
                    <div class="flex space-x-2">
                        <button onclick="shareTicket()" 
                                class="flex-1 px-3 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors duration-200 text-sm">
                            <i class="fas fa-share-alt mr-1"></i>
                            Compartir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function shareTicket() {
    const ticketData = {
        title: 'ZarzaPoints - Ticket Registrado',
        text: `¡Acabo de ganar {{ $ticket->puntos_generados }} puntos con mi ticket #{{ $ticket->numero_ticket }}! 🎉`,
        url: window.location.href
    };

    if (navigator.share) {
        navigator.share(ticketData)
            .then(() => console.log('Compartido exitosamente'))
            .catch((error) => console.log('Error al compartir:', error));
    } else {
        // Fallback para navegadores que no soportan Web Share API
        const text = `${ticketData.text}\n${ticketData.url}`;
        navigator.clipboard.writeText(text).then(() => {
            alert('¡Información copiada al portapapeles!');
        });
    }
}
</script>
@endpush
@endsection