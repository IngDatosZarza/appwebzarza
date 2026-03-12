@extends('layouts.app')

@section('title', 'Cupones La Zarza Contigo')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header con Puntos -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">
                    <i class="fas fa-ticket-alt mr-3"></i>
                    Cupones La Zarza Contigo
                </h1>
                <p class="text-purple-100 text-lg">Canjea tus puntos por increíbles descuentos y ofertas</p>
            </div>
            <div class="text-center">
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                    <i class="fas fa-coins text-3xl mb-2"></i>
                    <div class="text-2xl font-bold">{{ number_format($saldo_puntos) }}</div>
                    <div class="text-sm text-purple-200">Puntos disponibles</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación de Tabs -->
    <div class="bg-white rounded-lg shadow-lg mb-8" x-data="{ activeTab: 'disponibles' }">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'disponibles'" 
                        :class="activeTab === 'disponibles' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-store mr-2"></i>
                    Cupones Disponibles
                </button>
                <button @click="activeTab = 'mis-cupones'" 
                        :class="activeTab === 'mis-cupones' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-wallet mr-2"></i>
                    Mis Cupones ({{ count($mis_cupones) }})
                </button>
            </nav>
        </div>

        <!-- Tab Content: Cupones Disponibles -->
        <div x-show="activeTab === 'disponibles'" class="p-6">
            @if(count($cupones_disponibles) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($cupones_disponibles as $cupon)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <!-- Header del cupón -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $cupon['nombre'] }}</h3>
                                        <p class="text-sm text-gray-600 line-clamp-3">{{ $cupon['descripcion'] }}</p>
                                    </div>
                                    <div class="ml-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-coins mr-1"></i>
                                            {{ number_format($cupon['puntos_requeridos']) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Fecha de vigencia -->
                                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-calendar mr-2"></i>
                                        <span>Válido hasta: {{ date('d/m/Y', strtotime($cupon['fecha_fin'])) }}</span>
                                    </div>
                                </div>

                                <!-- Botón de canje -->
                                <div class="mt-6">
                                    @if($cupon['ya_canjeado'])
                                        <!-- Cupón ya canjeado -->
                                        <button disabled class="w-full bg-orange-100 text-orange-700 border-2 border-orange-300 font-medium py-3 px-4 rounded-lg cursor-not-allowed">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Ya Canjeado
                                        </button>
                                        <p class="text-xs text-orange-600 mt-2 text-center">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Ya canjeaste este cupón anteriormente
                                        </p>
                                    @elseif($saldo_puntos >= $cupon['puntos_requeridos'])
                                        <!-- Puede canjear -->
                                        <button 
                                            onclick="canjearCupon({{ $cupon['id'] }}, '{{ $cupon['nombre'] }}', {{ $cupon['puntos_requeridos'] }})"
                                            class="w-full zarza-bg hover:bg-purple-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200">
                                            <i class="fas fa-exchange-alt mr-2"></i>
                                            Canjear Cupón
                                        </button>
                                    @else
                                        <!-- Puntos insuficientes -->
                                        <button disabled class="w-full bg-gray-300 text-gray-500 font-medium py-3 px-4 rounded-lg cursor-not-allowed">
                                            <i class="fas fa-lock mr-2"></i>
                                            Puntos insuficientes
                                        </button>
                                        <p class="text-xs text-red-600 mt-2 text-center">
                                            Te faltan {{ number_format($cupon['puntos_requeridos'] - $saldo_puntos) }} puntos
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-ticket-alt text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cupones disponibles</h3>
                    <p class="text-gray-500">En este momento no hay cupones activos para canjear</p>
                </div>
            @endif
        </div>

        <!-- Tab Content: Mis Cupones -->
        <div x-show="activeTab === 'mis-cupones'" class="p-6">
            @if(count($mis_cupones) > 0)
                <div class="space-y-4">
                    @foreach($mis_cupones as $cupon)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 mr-3">{{ $cupon['nombre'] }}</h3>
                                            @if($cupon['estado'] === 'disponible')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Disponible
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-times-circle mr-1"></i>
                                                    Usado
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mb-4">{{ $cupon['descripcion'] }}</p>
                                        
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Código QR:</span>
                                                <span class="font-mono font-bold text-purple-600">{{ $cupon['codigo_qr'] }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Canjeado:</span>
                                                <span class="text-gray-900">{{ date('d/m/Y', strtotime($cupon['created_at'])) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-6 text-center">
                                        <!-- Mini QR Code -->
                                        <div class="bg-white rounded-lg p-2 mb-3 shadow-sm">
                                            <img src="{{ route('qr.coupon', $cupon['codigo_qr']) }}" 
                                                 alt="Código QR" 
                                                 class="mx-auto"
                                                 style="width: 80px; height: 80px;">
                                        </div>
                                        
                                        <div class="bg-purple-100 rounded-lg p-2 mb-3">
                                            <div class="text-xs text-purple-600">{{ number_format($cupon['puntos_requeridos']) }} pts</div>
                                        </div>
                                        
                                        @if($cupon['estado'] === 'disponible')
                                            <a href="{{ route('coupons.show', $cupon['id']) }}" 
                                               class="inline-flex items-center text-purple-600 hover:text-purple-800 text-sm font-medium">
                                                <i class="fas fa-eye mr-1"></i>
                                                Ver Cupón
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-wallet text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes cupones</h3>
                    <p class="text-gray-500 mb-4">Aún no has canjeado ningún cupón con tus puntos</p>
                    <button @click="activeTab = 'disponibles'" class="zarza-bg hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-store mr-2"></i>
                        Ver Cupones Disponibles
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Información de Ayuda -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-question-circle text-blue-500 mr-2"></i>
            ¿Cómo funcionan los cupones?
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
            <div class="flex items-start">
                <div class="bg-blue-100 rounded-full p-2 mr-3 mt-1">
                    <i class="fas fa-coins text-blue-600"></i>
                </div>
                <div>
                    <div class="font-medium mb-1">1. Canjea tus puntos</div>
                    <div>Usa tus puntos La Zarza Contigo para obtener cupones de descuento</div>
                </div>
            </div>
            <div class="flex items-start">
                <div class="bg-green-100 rounded-full p-2 mr-3 mt-1">
                    <i class="fas fa-qrcode text-green-600"></i>
                </div>
                <div>
                    <div class="font-medium mb-1">2. Recibe tu código</div>
                    <div>Obtienes un código QR único para usar en cualquier sucursal</div>
                </div>
            </div>
            <div class="flex items-start">
                <div class="bg-purple-100 rounded-full p-2 mr-3 mt-1">
                    <i class="fas fa-store text-purple-600"></i>
                </div>
                <div>
                    <div class="font-medium mb-1">3. Usa en sucursal</div>
                    <div>Presenta tu cupón en cualquier sucursal para aplicar el descuento</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar el cupón canjeado -->
<div id="cuponModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto transform transition-all">
        <!-- Header del modal -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6 rounded-t-xl text-center">
            <div class="animate-bounce mb-3">
                <i class="fas fa-gift text-5xl"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">¡Felicidades!</h2>
            <p class="text-purple-100">Has canjeado tu cupón exitosamente</p>
        </div>

        <!-- Contenido del modal -->
        <div class="p-6" id="modalContent">
            <!-- El contenido se llenará dinámicamente -->
        </div>

        <!-- Footer del modal -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-between space-x-3">
            <button onclick="cerrarModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cerrar
            </button>
            <button onclick="imprimirCupon()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-print mr-2"></i>
                Imprimir
            </button>
        </div>
    </div>
</div>

<style>
.zarza-bg { background-color: #b51a8a; }
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes confetti-fall {
    to {
        transform: translateY(100vh) rotate(720deg);
    }
}

.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    z-index: 9999;
    pointer-events: none;
    animation: confetti-fall 3s linear forwards;
}
</style>

<script>
let currentSaldoPuntos = {{ $saldo_puntos }};

function canjearCupon(cuponId, cuponNombre, puntosRequeridos) {
    // Confirmar canje
    if (!confirm(`¿Estás seguro de que deseas canjear "${cuponNombre}"?\n\nSe descontarán ${puntosRequeridos.toLocaleString()} puntos de tu saldo.`)) {
        return;
    }

    // Mostrar loading
    mostrarLoading();

    // Hacer petición AJAX
    fetch(`/cupones/${cuponId}/canjear`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar saldo de puntos
            currentSaldoPuntos = data.nuevo_saldo;
            
            // Mostrar modal con el cupón
            mostrarCuponCanjeado(data.cupon);
            
            // Lanzar confetti
            lanzarConfetti();
            
            // Recargar la página después de cerrar el modal para actualizar la lista
            setTimeout(() => {
                // Solo si el usuario cierra el modal
            }, 1000);
        } else {
            alert(data.message || 'Error al canjear el cupón');
            cerrarModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud. Por favor intenta nuevamente.');
        cerrarModal();
    });
}

function mostrarLoading() {
    const modal = document.getElementById('cuponModal');
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-purple-600 mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Canjeando cupón...</p>
            <p class="text-gray-500 text-sm mt-2">Por favor espera</p>
        </div>
    `;
    
    modal.style.display = 'flex';
}

function mostrarCuponCanjeado(cupon) {
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
        <!-- Código del Cupón -->
        <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-lg p-4 mb-4 border-2 border-purple-300">
            <div class="text-center">
                <div class="text-xs text-purple-600 mb-1 font-medium">CÓDIGO DEL CUPÓN</div>
                <div class="font-mono text-2xl font-bold text-purple-800">${cupon.codigo}</div>
                <div class="text-xs text-purple-500 mt-1">Presenta este código en cualquier sucursal</div>
            </div>
        </div>

        <!-- Código QR -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4 text-center">
            <div class="text-sm text-gray-600 mb-3 font-medium">
                <i class="fas fa-qrcode mr-1"></i>
                Código QR para Validación
            </div>
            <div class="bg-white rounded-lg p-4 inline-block shadow-md border-2 border-purple-200">
                <img src="/qr/cupon/${cupon.codigo_qr}" 
                     alt="Código QR" 
                     class="mx-auto"
                     style="width: 200px; height: 200px;">
            </div>
            <div class="mt-3 bg-blue-50 rounded-lg p-2 border border-blue-200">
                <div class="text-xs text-blue-600 mb-1">Código QR:</div>
                <div class="font-mono text-sm font-bold text-blue-800">${cupon.codigo_qr}</div>
            </div>
        </div>

        <!-- Detalles del cupón -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-3">
                <i class="fas fa-ticket-alt text-purple-600 mr-2"></i>
                ${cupon.nombre}
            </h4>
            <p class="text-sm text-gray-600 mb-3">${cupon.descripcion}</p>
            
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded p-2">
                    <div class="text-gray-500 text-xs">Puntos utilizados</div>
                    <div class="font-bold text-purple-600">${cupon.puntos_requeridos.toLocaleString()}</div>
                </div>
                <div class="bg-gray-50 rounded p-2">
                    <div class="text-gray-500 text-xs">Nuevo saldo</div>
                    <div class="font-bold text-green-600">${currentSaldoPuntos.toLocaleString()}</div>
                </div>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="bg-blue-50 rounded-lg p-3 mt-4 border border-blue-200">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-2"></i>
                <div class="text-xs text-blue-700">
                    <p class="font-medium mb-1">¿Cómo usar tu cupón?</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Presenta el código <strong>${cupon.codigo}</strong> al vendedor</li>
                        <li>O muestra el código QR para que lo escanee</li>
                        <li>El descuento se aplicará automáticamente</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botón para copiar código -->
        <button onclick="copiarCodigo('${cupon.codigo}')" class="w-full mt-4 bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-copy mr-2"></i>
            Copiar Código del Cupón
        </button>
    `;
}

function cerrarModal() {
    const modal = document.getElementById('cuponModal');
    modal.style.display = 'none';
    
    // Recargar la página para actualizar los cupones y el saldo
    location.reload();
}

function imprimirCupon() {
    window.print();
}

function copiarCodigo(codigo) {
    navigator.clipboard.writeText(codigo).then(function() {
        // Mostrar mensaje temporal
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>¡Copiado!';
        btn.classList.remove('bg-green-500', 'hover:bg-green-600');
        btn.classList.add('bg-green-700');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-700');
            btn.classList.add('bg-green-500', 'hover:bg-green-600');
        }, 2000);
    }).catch(function() {
        alert('Código del cupón: ' + codigo);
    });
}

function lanzarConfetti() {
    const colors = ['#9333ea', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 0.5 + 's';
        confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
        
        document.body.appendChild(confetti);
        
        setTimeout(() => {
            confetti.remove();
        }, 5000);
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('cuponModal').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('cuponModal');
        if (modal.style.display === 'flex') {
            cerrarModal();
        }
    }
});
</script>
@endsection