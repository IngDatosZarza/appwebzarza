@extends('layouts.admin')

@section('title', 'Validar Cupones QR - Admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-qrcode text-purple-600 mr-2"></i>
                        Validador de Cupones QR
                    </h1>
                    <p class="text-gray-600">Escanea o ingresa códigos QR para validar cupones de clientes</p>
                </div>
                <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Formulario de Validación -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-search text-blue-600 mr-2"></i>
                    Validar Cupón
                </h2>
                
                <form id="validation-form" class="space-y-4">
                    <div>
                        <label for="codigo_qr" class="block text-sm font-medium text-gray-700 mb-2">
                            Código QR del Cupón o Código del Cupón
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="codigo_qr" 
                                name="codigo_qr" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Ej: BANDERILLAS20-A3F9B o BANDERILLAS20"
                                required
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-qrcode text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Ingresa el código QR completo (BANDERILLAS20-A3F9B) o solo el código del cupón (BANDERILLAS20)
                        </p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button 
                            type="submit" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors"
                        >
                            <i class="fas fa-search mr-2"></i>
                            Validar Cupón
                        </button>
                        <button 
                            type="button" 
                            onclick="startQrScanner()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition-colors"
                        >
                            <i class="fas fa-camera mr-2"></i>
                            Escanear
                        </button>
                    </div>
                </form>

                <!-- Scanner de Cámara -->
                <div id="qr-scanner" class="hidden mt-6">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Escáner de Cámara</h3>
                    <div class="bg-gray-100 rounded-lg p-4 text-center">
                        <div id="scanner-placeholder" class="py-12">
                            <i class="fas fa-camera text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-600">Escáner de QR no disponible</p>
                            <p class="text-sm text-gray-500">Por ahora, ingresa el código manualmente</p>
                        </div>
                    </div>
                    <button 
                        onclick="stopQrScanner()" 
                        class="mt-3 w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Cerrar Escáner
                    </button>
                </div>
            </div>

            <!-- Resultado de Validación -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-clipboard-check text-green-600 mr-2"></i>
                    Resultado de Validación
                </h2>
                
                <div id="validation-result" class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <i class="fas fa-info-circle text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">Ingresa un código QR para validar</p>
                        <p class="text-sm text-gray-500 mt-2">Los resultados aparecerán aquí</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial Reciente -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-history text-orange-600 mr-2"></i>
                Validaciones Recientes
            </h2>
            
            <div id="recent-validations" class="space-y-3">
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <p class="text-gray-600">No hay validaciones recientes</p>
                    <p class="text-sm text-gray-500">Las validaciones aparecerán aquí</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let recentValidations = [];

document.getElementById('validation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const codigoQr = document.getElementById('codigo_qr').value.trim();
    if (!codigoQr) {
        showError('Por favor ingresa un código QR');
        return;
    }
    
    validateCoupon(codigoQr);
});

function validateCoupon(codigoQr) {
    // Mostrar loading
    showLoading();
    
    fetch('/admin/cupones/validar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            codigo_qr: codigoQr
        })
    })
    .then(response => response.json())
    .then(data => {
        showValidationResult(data, codigoQr);
        addToRecentValidations(codigoQr, data);
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error al validar cupón. Intenta nuevamente.');
    });
}

function showValidationResult(data, codigoQr) {
    const resultDiv = document.getElementById('validation-result');
    
    if (data.valid === true && data.status === 'available') {
        // Cupón válido
        resultDiv.innerHTML = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-check-circle text-3xl text-green-600 mr-3"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800">Cupón Válido ✓</h3>
                        <p class="text-green-600">${data.message}</p>
                    </div>
                </div>
                
                <!-- Código del Cupón Destacado -->
                <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-lg p-4 mb-4 border-2 border-purple-300">
                    <div class="text-center">
                        <div class="text-xs text-purple-600 mb-1">CÓDIGO DEL CUPÓN</div>
                        <div class="font-mono text-2xl font-bold text-purple-800">${data.data.cupon_codigo}</div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Detalles del Cupón</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div><strong>Cupón:</strong> ${data.data.cupon_nombre}</div>
                        <div><strong>Cliente:</strong> ${data.data.cliente_nombre}</div>
                        <div><strong>Email:</strong> ${data.data.cliente_email}</div>
                        <div><strong>Fecha canje:</strong> ${data.data.fecha_canje}</div>
                        <div><strong>Código QR:</strong> <code class="bg-gray-100 px-2 py-1 rounded">${data.data.codigo_qr}</code></div>
                    </div>
                    <div class="mt-3">
                        <strong>Descripción:</strong> ${data.data.cupon_descripcion}
                    </div>
                </div>
                
                <button 
                    onclick="markAsUsed('${codigoQr}')" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition-colors"
                >
                    <i class="fas fa-check mr-2"></i>
                    Marcar como Usado
                </button>
            </div>
        `;
    } else if (data.valid === false) {
        // Cupón no válido
        let alertClass = 'red';
        let icon = 'fas fa-times-circle';
        
        if (data.status === 'used') {
            alertClass = 'yellow';
            icon = 'fas fa-exclamation-triangle';
        }
        
        resultDiv.innerHTML = `
            <div class="bg-${alertClass}-50 border border-${alertClass}-200 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <i class="${icon} text-3xl text-${alertClass}-600 mr-3"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-${alertClass}-800">Cupón No Válido</h3>
                        <p class="text-${alertClass}-600">${data.message}</p>
                    </div>
                </div>
                
                ${data.data ? `
                    ${data.data.cupon_codigo ? `
                        <div class="bg-white rounded-lg p-3 mb-3 text-center border-2 border-gray-300">
                            <div class="text-xs text-gray-600 mb-1">CÓDIGO DEL CUPÓN</div>
                            <div class="font-mono text-xl font-bold text-gray-800">${data.data.cupon_codigo}</div>
                        </div>
                    ` : ''}
                    <div class="bg-white rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Información del Cupón</h4>
                        <div class="text-sm space-y-1">
                            <div><strong>Cupón:</strong> ${data.data.cupon_nombre}</div>
                            <div><strong>Cliente:</strong> ${data.data.cliente_nombre}</div>
                            ${data.data.fecha_uso ? `<div><strong>Fecha de uso:</strong> ${data.data.fecha_uso}</div>` : ''}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    } else {
        // Error
        showError(data.error || 'Error desconocido');
    }
}

function markAsUsed(codigoQr) {
    if (!confirm('¿Estás seguro de marcar este cupón como usado? Esta acción no se puede deshacer.')) {
        return;
    }
    
    fetch('/admin/cupones/marcar-usado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            codigo_qr: codigoQr
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Cupón marcado como usado exitosamente');
            // Limpiar formulario
            document.getElementById('codigo_qr').value = '';
            document.getElementById('validation-result').innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <i class="fas fa-check-circle text-4xl text-green-600 mb-3"></i>
                    <h3 class="text-lg font-semibold text-green-800 mb-2">Cupón Procesado</h3>
                    <p class="text-green-600">El cupón ha sido marcado como usado correctamente</p>
                </div>
            `;
        } else {
            showError(data.error || 'Error al marcar cupón como usado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error al procesar la solicitud');
    });
}

function addToRecentValidations(codigoQr, result) {
    const validation = {
        codigo: codigoQr,
        timestamp: new Date().toLocaleString(),
        status: result.valid ? (result.status === 'available' ? 'valid' : 'invalid') : 'invalid',
        message: result.message
    };
    
    recentValidations.unshift(validation);
    if (recentValidations.length > 10) {
        recentValidations.pop();
    }
    
    updateRecentValidations();
}

function updateRecentValidations() {
    const container = document.getElementById('recent-validations');
    
    if (recentValidations.length === 0) {
        container.innerHTML = `
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-600">No hay validaciones recientes</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = recentValidations.map(validation => {
        const statusColor = validation.status === 'valid' ? 'green' : 'red';
        const statusIcon = validation.status === 'valid' ? 'check-circle' : 'times-circle';
        
        return `
            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-${statusIcon} text-${statusColor}-500"></i>
                    <div>
                        <div class="font-mono text-sm font-semibold">${validation.codigo}</div>
                        <div class="text-xs text-gray-500">${validation.timestamp}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 max-w-xs truncate">
                    ${validation.message}
                </div>
            </div>
        `;
    }).join('');
}

function showLoading() {
    document.getElementById('validation-result').innerHTML = `
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-3"></i>
            <p class="text-blue-800 font-medium">Validando cupón...</p>
            <p class="text-blue-600 text-sm">Por favor espera</p>
        </div>
    `;
}

function showError(message) {
    document.getElementById('validation-result').innerHTML = `
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <i class="fas fa-exclamation-triangle text-3xl text-red-600 mb-3"></i>
            <h3 class="text-lg font-semibold text-red-800 mb-2">Error</h3>
            <p class="text-red-600">${message}</p>
        </div>
    `;
}

function showSuccess(message) {
    // Crear notificación temporal
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50';
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function startQrScanner() {
    document.getElementById('qr-scanner').classList.remove('hidden');
    // Aquí se podría integrar una librería de escáner QR como QuaggaJS o ZXing
}

function stopQrScanner() {
    document.getElementById('qr-scanner').classList.add('hidden');
}

// Auto-focus en el campo de código QR
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('codigo_qr').focus();
});
</script>
@endsection