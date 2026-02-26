<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Cupón - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .validation-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin: 50px auto;
            max-width: 600px;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .admin-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .qr-scanner-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .manual-input-section {
            border: 2px dashed #bdc3c7;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .manual-input-section:hover {
            border-color: var(--secondary-color);
            background: #f8f9fa;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #ecf0f1;
            padding: 15px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--secondary-color), #5dade2);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }

        .validation-result {
            margin-top: 30px;
            padding: 25px;
            border-radius: 15px;
            display: none;
        }

        .validation-result.success {
            background: rgba(39, 174, 96, 0.1);
            border: 2px solid var(--success-color);
            color: var(--success-color);
        }

        .validation-result.error {
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid var(--danger-color);
            color: var(--danger-color);
        }

        .validation-result.warning {
            background: rgba(243, 156, 18, 0.1);
            border: 2px solid var(--warning-color);
            color: var(--warning-color);
        }

        .coupon-details {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .detail-value {
            color: #7f8c8d;
        }

        .scanner-placeholder {
            width: 200px;
            height: 200px;
            background: #ecf0f1;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #bdc3c7;
            font-size: 3rem;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        .spinner-border {
            color: var(--secondary-color);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="validation-container">
            <div class="admin-header">
                <h1><i class="fas fa-qrcode"></i> Validación de Cupones</h1>
                <p>Escaneé o ingrese el código QR para validar cupones</p>
            </div>

            <!-- Sección del Escáner QR -->
            <div class="qr-scanner-section">
                <h4><i class="fas fa-camera"></i> Escáner QR</h4>
                <div class="scanner-placeholder pulse-animation">
                    <i class="fas fa-qrcode"></i>
                </div>
                <p class="text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        Función de escáner en desarrollo.<br>
                        Use el campo manual mientras tanto.
                    </small>
                </p>
            </div>

            <!-- Sección de Entrada Manual -->
            <div class="manual-input-section">
                <h4><i class="fas fa-keyboard"></i> Entrada Manual</h4>
                <form id="validationForm" method="POST" action="/admin/validate-coupon">
                    <div class="mb-4">
                        <label for="qr_code" class="form-label">Código QR del Cupón</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="qr_code" 
                            name="qr_code" 
                            placeholder="Ingrese o escanee el código QR..."
                            required
                            autocomplete="off">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Validar Cupón
                    </button>
                </form>

                <div class="loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Validando...</span>
                    </div>
                    <p class="mt-2">Validando cupón...</p>
                </div>
            </div>

            <!-- Resultado de la Validación -->
            <div id="validationResult" class="validation-result">
                <!-- El resultado se mostrará aquí dinámicamente -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('validationForm');
            const loadingSpinner = document.querySelector('.loading-spinner');
            const validationResult = document.getElementById('validationResult');
            const qrCodeInput = document.getElementById('qr_code');

            // Manejar envío del formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const qrCode = qrCodeInput.value.trim();
                if (!qrCode) {
                    showResult('error', 'Por favor ingrese un código QR válido');
                    return;
                }

                // Mostrar loading
                loadingSpinner.style.display = 'block';
                validationResult.style.display = 'none';

                // Simular validación (en producción esto sería una llamada AJAX)
                setTimeout(() => {
                    validateCoupon(qrCode);
                }, 1500);
            });

            // Función para validar cupón
            function validateCoupon(qrCode) {
                // En producción esto sería una llamada AJAX real
                // Por ahora simulamos diferentes escenarios
                
                loadingSpinner.style.display = 'none';

                if (qrCode.includes('QR_')) {
                    // Cupón válido simulado
                    showResult('success', '¡Cupón Válido!', {
                        cliente: 'Juan Pérez',
                        cupon: 'Descuento 20% en productos',
                        codigo: qrCode,
                        estado: 'Activo',
                        fechaAsignacion: '2025-10-10',
                        valorDescuento: '20%'
                    });
                } else if (qrCode.toLowerCase().includes('usado')) {
                    // Cupón ya usado
                    showResult('warning', 'Cupón Ya Utilizado', {
                        cliente: 'María González',
                        cupon: 'Cupón de descuento',
                        codigo: qrCode,
                        estado: 'Usado',
                        fechaUso: '2025-10-09',
                        usuarioValidacion: 'Admin Usuario'
                    });
                } else {
                    // Cupón inválido
                    showResult('error', 'Código QR No Válido', {
                        mensaje: 'El código QR ingresado no corresponde a ningún cupón en el sistema.',
                        codigo: qrCode,
                        sugerencia: 'Verifique que el código esté completo y sea legible.'
                    });
                }
            }

            // Función para mostrar resultados
            function showResult(type, title, details = null) {
                validationResult.className = `validation-result ${type}`;
                
                let icon = '';
                switch(type) {
                    case 'success': icon = 'fas fa-check-circle'; break;
                    case 'warning': icon = 'fas fa-exclamation-triangle'; break;
                    case 'error': icon = 'fas fa-times-circle'; break;
                }

                let html = `
                    <div class="text-center mb-3">
                        <i class="${icon}" style="font-size: 3rem; margin-bottom: 15px;"></i>
                        <h4>${title}</h4>
                    </div>
                `;

                if (details) {
                    html += '<div class="coupon-details">';
                    
                    for (const [key, value] of Object.entries(details)) {
                        const label = formatLabel(key);
                        html += `
                            <div class="detail-row">
                                <span class="detail-label">${label}:</span>
                                <span class="detail-value">${value}</span>
                            </div>
                        `;
                    }
                    
                    html += '</div>';

                    // Agregar botón de acción si es un cupón válido
                    if (type === 'success') {
                        html += `
                            <div class="mt-3 text-center">
                                <button class="btn btn-success" onclick="markAsUsed('${details.codigo}')">
                                    <i class="fas fa-check"></i> Marcar como Usado
                                </button>
                            </div>
                        `;
                    }
                }

                validationResult.innerHTML = html;
                validationResult.style.display = 'block';

                // Scroll al resultado
                validationResult.scrollIntoView({ behavior: 'smooth' });
            }

            // Función para formatear labels
            function formatLabel(key) {
                const labels = {
                    cliente: 'Cliente',
                    cupon: 'Cupón',
                    codigo: 'Código QR',
                    estado: 'Estado',
                    fechaAsignacion: 'Fecha Asignación',
                    fechaUso: 'Fecha de Uso',
                    usuarioValidacion: 'Validado por',
                    valorDescuento: 'Valor Descuento',
                    mensaje: 'Mensaje',
                    sugerencia: 'Sugerencia'
                };
                return labels[key] || key.charAt(0).toUpperCase() + key.slice(1);
            }

            // Auto-focus en el campo de entrada
            qrCodeInput.focus();

            // Limpiar resultado al cambiar el código
            qrCodeInput.addEventListener('input', function() {
                if (validationResult.style.display === 'block') {
                    validationResult.style.display = 'none';
                }
            });
        });

        // Función para marcar cupón como usado
        function markAsUsed(qrCode) {
            if (confirm('¿Está seguro de marcar este cupón como usado?')) {
                // En producción esto sería una llamada AJAX
                alert('¡Cupón marcado como usado exitosamente!');
                
                // Limpiar formulario
                document.getElementById('qr_code').value = '';
                document.getElementById('validationResult').style.display = 'none';
                document.getElementById('qr_code').focus();
            }
        }

        // Función para manejar escáner QR (placeholder)
        function startQRScanner() {
            alert('Función de escáner QR en desarrollo.\nPor favor use la entrada manual.');
        }
    </script>
</body>
</html>