@extends('layouts.app')

@section('title', 'Registrar Ticket')

@push('styles')
<style>
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .input-error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .success-message {
        animation: slideIn 0.5s ease-out;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full mb-4">
                <i class="fas fa-ticket-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Registrar Ticket</h1>
            <p class="text-lg text-gray-600">Gana 100 puntos por cada ticket que registres</p>
        </div>

        <!-- Puntos Actuales -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">{{ number_format($saldoActual) }} puntos</h2>
                    <p class="text-green-100">Tu saldo actual</p>
                </div>
                <div class="text-4xl opacity-75">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Formulario Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover">
                    
                    @if($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Errores en el formulario:</h3>
                                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('tickets.store') }}" method="POST" id="ticketForm">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Número de Ticket -->
                            <div class="md:col-span-2">
                                <label for="numero_ticket" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-ticket-alt text-blue-500 mr-2"></i>
                                    Número de Ticket *
                                </label>
                                <input type="text" 
                                       id="numero_ticket" 
                                       name="numero_ticket" 
                                       value="{{ old('numero_ticket') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('numero_ticket') input-error @enderror"
                                       placeholder="Ej: ABC123456"
                                       required>
                                <div id="ticket-check-message" class="mt-2 text-sm"></div>
                            </div>

                            <!-- Monto -->
                            <div>
                                <label for="monto" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-dollar-sign text-green-500 mr-2"></i>
                                    Monto de la Compra *
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-gray-500">$</span>
                                    <input type="number" 
                                           id="monto" 
                                           name="monto" 
                                           value="{{ old('monto') }}"
                                           class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('monto') input-error @enderror"
                                           placeholder="0.00"
                                           min="0.01"
                                           step="0.01"
                                           required>
                                </div>
                            </div>

                            <!-- Sucursal -->
                            <div>
                                <label for="sucursal_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-store text-purple-500 mr-2"></i>
                                    Sucursal *
                                </label>
                                <select id="sucursal_id" 
                                        name="sucursal_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('sucursal_id') input-error @enderror"
                                        required>
                                    <option value="">Selecciona una sucursal</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre }} - {{ $sucursal->direccion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Método de Pago -->
                            <div>
                                <label for="metodo_pago" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-credit-card text-indigo-500 mr-2"></i>
                                    Método de Pago *
                                </label>
                                <select id="metodo_pago" 
                                        name="metodo_pago" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('metodo_pago') input-error @enderror"
                                        required>
                                    <option value="">Selecciona método</option>
                                    <option value="efectivo" {{ old('metodo_pago') == 'efectivo' ? 'selected' : '' }}>💵 Efectivo</option>
                                    <option value="tarjeta" {{ old('metodo_pago') == 'tarjeta' ? 'selected' : '' }}>💳 Tarjeta</option>
                                    <option value="transferencia" {{ old('metodo_pago') == 'transferencia' ? 'selected' : '' }}>🏦 Transferencia</option>
                                </select>
                            </div>

                            <!-- Fecha de Compra -->
                            <div>
                                <label for="fecha_compra" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-orange-500 mr-2"></i>
                                    Fecha de Compra
                                </label>
                                <input type="date" 
                                       id="fecha_compra" 
                                       name="fecha_compra" 
                                       value="{{ old('fecha_compra', date('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('fecha_compra') input-error @enderror">
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-edit text-gray-500 mr-2"></i>
                                    Descripción (Opcional)
                                </label>
                                <textarea id="descripcion" 
                                          name="descripcion" 
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('descripcion') input-error @enderror"
                                          placeholder="Describe tu compra (opcional)">{{ old('descripcion') }}</textarea>
                            </div>
                        </div>

                        <!-- Puntos a Ganar -->
                        <div class="mt-8 p-6 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border border-yellow-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-yellow-800 mb-1">
                                        <i class="fas fa-gift text-yellow-600 mr-2"></i>
                                        Puntos a Ganar
                                    </h3>
                                    <p class="text-yellow-700">Por registrar este ticket</p>
                                </div>
                                <div class="text-3xl font-bold text-yellow-600">
                                    +100 puntos
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex flex-col sm:flex-row gap-4 mt-8">
                            <button type="submit" 
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 px-6 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-semibold shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Registrar Ticket
                            </button>
                            <a href="{{ route('tickets.index') }}" 
                               class="flex-1 bg-gray-200 text-gray-700 py-4 px-6 rounded-lg hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-semibold text-center">
                                <i class="fas fa-list mr-2"></i>
                                Ver Mis Tickets
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar Informativo -->
            <div class="space-y-6">
                
                <!-- Información de Puntos -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">¿Cómo Funciona?</h3>
                        </div>
                    </div>
                    
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <span><strong>100 puntos fijos</strong> por cada ticket registrado</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <span>Los puntos se acreditan <strong>inmediatamente</strong></span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <span>Cada ticket solo puede registrarse <strong>una vez</strong></span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <span>Puedes canjear puntos por <strong>cupones y descuentos</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Últimos Tickets -->
                @if($ultimasCompras->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history text-gray-600 mr-2"></i>
                        Últimos Tickets
                    </h3>
                    
                    <div class="space-y-3">
                        @foreach($ultimasCompras as $compra)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    #{{ $compra->numero_ticket }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $compra->sucursal->nombre }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-green-600">
                                    +{{ $compra->puntos_generados }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    ${{ number_format($compra->monto, 2) }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Consejos -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-lightbulb text-purple-600 mr-2"></i>
                        <h3 class="text-sm font-semibold text-purple-900">Consejos</h3>
                    </div>
                    
                    <ul class="text-xs text-purple-800 space-y-2">
                        <li>• Registra tus tickets lo antes posible</li>
                        <li>• Verifica que el número sea correcto</li>
                        <li>• Los puntos nunca caducan</li>
                        <li>• Revisa cupones disponibles regularmente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticketInput = document.getElementById('numero_ticket');
    const messageDiv = document.getElementById('ticket-check-message');
    let checkTimeout;

    // Verificar número de ticket en tiempo real
    ticketInput.addEventListener('input', function() {
        clearTimeout(checkTimeout);
        const ticket = this.value.trim();
        
        if (ticket.length < 3) {
            messageDiv.innerHTML = '';
            return;
        }

        checkTimeout = setTimeout(() => {
            fetch(`{{ route('tickets.check') }}?numero_ticket=${encodeURIComponent(ticket)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        messageDiv.innerHTML = `<div class="text-red-600 flex items-center">
                            <i class="fas fa-times-circle mr-2"></i>
                            ${data.message}
                        </div>`;
                        ticketInput.classList.add('input-error');
                    } else {
                        messageDiv.innerHTML = `<div class="text-green-600 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            ${data.message}
                        </div>`;
                        ticketInput.classList.remove('input-error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }, 500);
    });
});
</script>
@endpush
@endsection