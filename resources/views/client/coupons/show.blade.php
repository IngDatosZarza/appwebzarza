@extends('layouts.app')

@section('title', 'Cupón La Zarza Contigo')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Mensaje de celebración para canje nuevo -->
    @if(session('nuevo_canje'))
    <div class="mb-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg p-6 text-white text-center animate-bounce">
        <div class="text-4xl mb-3">🎉 ¡FELICIDADES! 🎉</div>
        <div class="text-xl font-bold mb-2">¡Has canjeado tu cupón exitosamente!</div>
        <div class="text-lg">Aquí tienes tu código QR listo para usar</div>
    </div>
    @endif

    <!-- Botón de regreso -->
    <div class="mb-6">
        <a href="{{ route('coupons.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a mis cupones
        </a>
    </div>

    <!-- Card del Cupón -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header del cupón con gradiente -->
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-8 text-center">
                <div class="mb-4">
                    <i class="fas fa-ticket-alt text-5xl mb-3"></i>
                    <h1 class="text-3xl font-bold mb-2">{{ $cupon['nombre'] }}</h1>
                    <p class="text-purple-100 text-lg">{{ $cupon['descripcion'] }}</p>
                </div>

            </div>

            <!-- Contenido del cupón -->
            <div class="p-8">
                <!-- Estado del cupón -->
                <div class="text-center mb-8">
                    @if($cupon['estado'] === 'asignado' || $cupon['estado'] === 'disponible')
                        <div class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium bg-green-100 text-green-800 mb-4">
                            <i class="fas fa-check-circle mr-2"></i>
                            Cupón Disponible para Usar
                        </div>
                    @elseif($cupon['estado'] === 'usado')
                        <div class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium bg-gray-100 text-gray-800 mb-4">
                            <i class="fas fa-check mr-2"></i>
                            Cupón Ya Utilizado
                        </div>
                    @else
                        <div class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium bg-red-100 text-red-800 mb-4">
                            <i class="fas fa-times-circle mr-2"></i>
                            Cupón {{ ucfirst($cupon['estado']) }}
                        </div>
                    @endif
                </div>

                <!-- Código del Cupón (destacado) -->
                <div class="text-center mb-6">
                    <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl p-6 border-2 border-purple-300 shadow-md">
                        <div class="text-sm text-purple-600 mb-2 font-medium">
                            <i class="fas fa-tag mr-1"></i>
                            CÓDIGO DEL CUPÓN
                        </div>
                        <div class="font-mono text-3xl font-bold text-purple-800 mb-1 tracking-wider">
                            {{ $cupon['codigo'] ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-purple-500 mt-2">
                            Presenta este código en cualquier sucursal
                        </div>
                    </div>
                </div>

                <!-- Código QR -->
                <div class="text-center mb-8">
                    <div class="bg-gray-50 rounded-lg p-8 mb-4">
                        <!-- QR Code Real -->
                        <div class="bg-white rounded-lg p-4 mb-4 inline-block shadow-lg border-2 border-purple-200">
                            <img id="qr-image" 
                                 src="{{ route('qr.coupon', $cupon['codigo_qr']) }}" 
                                 alt="Código QR del cupón" 
                                 class="mx-auto cursor-pointer hover:scale-105 transition-transform"
                                 style="max-width: 300px; height: auto;"
                                 onclick="showQrFullscreen()">
                        </div>
                        
                        <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
                            <div class="text-sm text-blue-700 mb-1">Código QR de validación:</div>
                            <div class="font-mono text-lg font-bold text-blue-800">{{ $cupon['codigo_qr'] }}</div>
                            <div class="text-xs text-blue-600 mt-1">Este código único se usa para validar el cupón</div>
                        </div>
                        
                        <!-- Botones de acción para el QR -->
                        <div class="flex justify-center space-x-4 mt-4">
                            <button onclick="showQrFullscreen()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-expand mr-2"></i>
                                Ver QR Grande
                            </button>
                            <button onclick="copyQrCode()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-copy mr-2"></i>
                                Copiar Código QR
                            </button>
                        </div>
                    </div>
                    
                    @if($cupon['estado'] === 'asignado' || $cupon['estado'] === 'disponible')
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Presenta el código <strong>{{ $cupon['codigo'] }}</strong> o el código QR en cualquier sucursal
                        </p>
                    @else
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-check mr-1"></i>
                            Este cupón fue utilizado el {{ date('d/m/Y H:i', strtotime($cupon['fecha_uso'] ?? $cupon['created_at'])) }}
                        </p>
                    @endif
                </div>

                <!-- Información adicional -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información del cupón
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Fecha de canje:</span>
                            <div class="font-medium text-gray-900">{{ date('d/m/Y H:i', strtotime($cupon['created_at'])) }}</div>
                        </div>

                        @if($cupon['estado'] === 'usado' && isset($cupon['fecha_uso']))
                        <div>
                            <span class="text-gray-500">Fecha de uso:</span>
                            <div class="font-medium text-gray-900">{{ date('d/m/Y H:i', strtotime($cupon['fecha_uso'])) }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Términos y condiciones -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2">
                        <i class="fas fa-file-contract mr-1"></i>
                        Términos y condiciones
                    </h4>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Este cupón es válido únicamente en sucursales <span class="font-mercurius">La Zarza Contigo</span></li>
                        <li>• El cupón debe ser presentado antes de realizar el pago</li>
                        <li>• No es acumulable con otras promociones</li>
                        <li>• El cupón es personal e intransferible</li>
                        <li>• Una vez utilizado, el cupón no puede ser reutilizado</li>
                    </ul>
                </div>

                <!-- Botones de acción -->
                <div class="mt-8 flex justify-center space-x-4">
                    @if($cupon['estado'] === 'disponible')
                        <button onclick="window.print()" class="zarza-bg hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir Cupón
                        </button>
                        <button onclick="compartirCupon()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                            <i class="fas fa-share-alt mr-2"></i>
                            Compartir
                        </button>
                    @endif
                    
                    <a href="{{ route('coupons.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                        <i class="fas fa-list mr-2"></i>
                        Ver Todos mis Cupones
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de contacto -->
    <div class="max-w-2xl mx-auto mt-8">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 text-center">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                <i class="fas fa-store mr-2"></i>
                ¿Necesitas ayuda?
            </h3>
            <p class="text-gray-600 mb-4">Si tienes problemas para usar este cupón, contacta con cualquier sucursal La Zarza Contigo</p>
            <div class="flex justify-center space-x-6 text-sm text-gray-700">
                <div class="flex items-center">
                    <i class="fas fa-phone text-blue-500 mr-2"></i>
                    <span>📞 (555) 123-4567</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-envelope text-purple-500 mr-2"></i>
                    <span>✉️ ayuda@La Zarza Contigo.com</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.zarza-bg { background-color: #b51a8a; }

@media print {
    body * {
        visibility: hidden;
    }
    .print-area, .print-area * {
        visibility: visible;
    }
    .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>

<script>
function compartirCupon() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $cupon["nombre"] }}',
            text: 'He canjeado un cupón La Zarza Contigo: {{ $cupon["descripcion"] }}',
            url: window.location.href
        });
    } else {
        // Fallback: copiar al portapapeles
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Enlace copiado al portapapeles');
        });
    }
}

function showQrFullscreen() {
    const qrImage = document.getElementById('qr-image');
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-8 max-w-md text-center">
            <h3 class="text-xl font-bold mb-2 text-gray-900">{{ $cupon["nombre"] }}</h3>
            
            <!-- Código del cupón -->
            <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-lg p-4 mb-4 border-2 border-purple-300">
                <div class="text-xs text-purple-600 mb-1">CÓDIGO DEL CUPÓN</div>
                <div class="font-mono text-2xl font-bold text-purple-800">{{ $cupon['codigo'] ?? 'N/A' }}</div>
            </div>
            
            <!-- QR Code -->
            <img src="{{ route('qr.coupon', $cupon['codigo_qr']) }}" 
                 alt="Código QR" 
                 class="mx-auto mb-4"
                 style="width: 400px; height: 400px;">
            
            <!-- Código QR -->
            <div class="bg-blue-50 rounded-lg p-3 mb-4 border border-blue-200">
                <div class="text-xs text-blue-600 mb-1">Código QR de validación</div>
                <div class="font-mono text-sm font-bold text-blue-800">{{ $cupon['codigo_qr'] }}</div>
            </div>
            
            <p class="text-sm text-gray-600 mb-4">Presenta este código en cualquier sucursal La Zarza Contigo</p>
            <button onclick="this.closest('.fixed').remove()" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cerrar
            </button>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Cerrar con ESC
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modal.remove();
        }
    });
}

function copyQrCode() {
    const codigo = '{{ $cupon["codigo_qr"] }}';
    navigator.clipboard.writeText(codigo).then(function() {
        // Mostrar mensaje de éxito
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>¡Copiado!';
        btn.className = btn.className.replace('bg-green-600 hover:bg-green-700', 'bg-green-700');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.className = btn.className.replace('bg-green-700', 'bg-green-600 hover:bg-green-700');
        }, 2000);
    }).catch(function() {
        alert('Código del cupón: ' + codigo);
    });
}

// Animación de celebración si es un canje nuevo
const esNuevoCanje = {{ session('nuevo_canje') ? 'true' : 'false' }};

if (esNuevoCanje) {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            createConfetti();
        }, 500);
    });
}

function createConfetti() {
    const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
    
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.style.cssText = `
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: ${colors[Math.floor(Math.random() * colors.length)]};
            left: ${Math.random() * 100}%;
            top: -10px;
            z-index: 9999;
            pointer-events: none;
            animation: confetti-fall ${Math.random() * 3 + 2}s linear forwards;
        `;
        document.body.appendChild(confetti);
        
        setTimeout(() => {
            confetti.remove();
        }, 5000);
    }
}

// Agregar CSS para confetti
const confettiStyle = document.createElement('style');
confettiStyle.textContent = `
    @keyframes confetti-fall {
        to {
            transform: translateY(100vh) rotate(360deg);
        }
    }
`;
document.head.appendChild(confettiStyle);
</script>
@endsection