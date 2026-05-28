<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test de Geolocalización</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #b51a8a;
            margin-bottom: 20px;
        }
        .step {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #ccc;
            background: #f9f9f9;
        }
        .step.success {
            border-left-color: #4CAF50;
            background: #f1f8f4;
        }
        .step.error {
            border-left-color: #f44336;
            background: #fef1f0;
        }
        .step.warning {
            border-left-color: #ff9800;
            background: #fff8f0;
        }
        .step.info {
            border-left-color: #2196F3;
            background: #f0f7ff;
        }
        button {
            background: linear-gradient(135deg, #b51a8a 0%, #d63a9e 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        button:hover {
            background: linear-gradient(135deg, #9e1577 0%, #c0348b 100%);
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        .log {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin: 5px 0;
        }
        .icon {
            display: inline-block;
            margin-right: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Test de Sistema de Geolocalización</h1>
        <p>Este test verificará cada paso del proceso de captura de ubicación.</p>

        <button onclick="runFullTest()">🚀 Ejecutar Test Completo</button>
        <button onclick="testAPI()">📡 Test API Directo</button>
        <button onclick="clearLogs()">🗑️ Limpiar</button>

        <div id="results"></div>
    </div>

    <script>
        const results = document.getElementById('results');

        function log(message, type = 'info') {
            const step = document.createElement('div');
            step.className = `step ${type}`;
            
            let icon = 'ℹ️';
            if (type === 'success') icon = '✅';
            if (type === 'error') icon = '❌';
            if (type === 'warning') icon = '⚠️';
            
            step.innerHTML = `<span class="icon">${icon}</span><span class="log">${message}</span>`;
            results.appendChild(step);
            results.scrollTop = results.scrollHeight;
        }

        function clearLogs() {
            results.innerHTML = '';
        }

        async function runFullTest() {
            clearLogs();
            log('=== INICIANDO TEST COMPLETO ===', 'info');
            
            // Test 1: Verificar soporte de geolocalización
            log('Test 1: Verificando soporte de geolocalización...', 'info');
            if ('geolocation' in navigator) {
                log('✓ Geolocalización está soportada por el navegador', 'success');
            } else {
                log('✗ Geolocalización NO está soportada', 'error');
                return;
            }

            // Test 2: Verificar CSRF Token
            log('Test 2: Verificando CSRF Token...', 'info');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                log(`✓ CSRF Token encontrado: ${csrfToken.substring(0, 20)}...`, 'success');
            } else {
                log('✗ CSRF Token NO encontrado', 'error');
            }

            // Test 3: Obtener ubicación
            log('Test 3: Obteniendo ubicación GPS...', 'info');
            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => resolve(pos),
                        (err) => reject(err),
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                });

                const coords = {
                    latitud: position.coords.latitude,
                    longitud: position.coords.longitude,
                    precision: position.coords.accuracy
                };

                log(`✓ Ubicación obtenida: Lat ${coords.latitud.toFixed(6)}, Lon ${coords.longitud.toFixed(6)}`, 'success');
                log(`  Precisión: ${coords.precision.toFixed(2)} metros`, 'info');

                // Test 4: Reverse Geocoding
                log('Test 4: Obteniendo información de ciudad...', 'info');
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${coords.latitud}&lon=${coords.longitud}&addressdetails=1&accept-language=es`;
                    const response = await fetch(url, {
                        headers: { 'User-Agent': 'LaZarzaContigoApp/1.0' }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        const geoInfo = {
                            ciudad: data.address.city || data.address.town || data.address.village || data.address.municipality || 'Desconocida',
                            estado: data.address.state || 'Desconocido',
                            pais: data.address.country || 'México'
                        };

                        log(`✓ Ciudad: ${geoInfo.ciudad}, ${geoInfo.estado}, ${geoInfo.pais}`, 'success');

                        // Test 5: Enviar a API
                        log('Test 5: Enviando datos a la API...', 'info');
                        const fullData = { ...coords, ...geoInfo };
                        
                        log(`Datos a enviar: ${JSON.stringify(fullData, null, 2)}`, 'info');

                        const apiResponse = await fetch('/api/v1/location', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify(fullData)
                        });

                        log(`Status HTTP: ${apiResponse.status} ${apiResponse.statusText}`, 'info');

                        const result = await apiResponse.json();

                        if (apiResponse.ok && result.success) {
                            log('✓ ¡UBICACIÓN GUARDADA EXITOSAMENTE EN LA BASE DE DATOS!', 'success');
                            log(`ID del registro: ${result.data.id}`, 'success');
                            log(`Dispositivo detectado: ${result.data.dispositivo}`, 'info');
                            log(`Primera visita: ${result.data.es_primera_visita ? 'Sí' : 'No'}`, 'info');
                            log('Respuesta completa:', 'info');
                            log(`<pre>${JSON.stringify(result, null, 2)}</pre>`, 'success');
                        } else {
                            log('✗ Error al guardar ubicación', 'error');
                            log(`Respuesta: ${JSON.stringify(result, null, 2)}`, 'error');
                        }

                    } else {
                        log(`⚠️ Error en geocoding (Status: ${response.status}), continuando sin ciudad...`, 'warning');
                        
                        // Intentar guardar sin geocoding
                        const apiResponse = await fetch('/api/v1/location', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({ ...coords, pais: 'México' })
                        });

                        const result = await apiResponse.json();
                        if (apiResponse.ok && result.success) {
                            log('✓ Ubicación guardada (sin información de ciudad)', 'success');
                        }
                    }

                } catch (geocodingError) {
                    log(`⚠️ Error en geocoding: ${geocodingError.message}`, 'warning');
                }

            } catch (error) {
                log(`✗ Error obteniendo ubicación: ${error.message}`, 'error');
                log(`Código de error: ${error.code}`, 'error');
                
                if (error.code === 1) {
                    log('PERMISO DENEGADO: El usuario rechazó el acceso a la ubicación', 'error');
                } else if (error.code === 2) {
                    log('POSICIÓN NO DISPONIBLE: No se pudo determinar la ubicación', 'error');
                } else if (error.code === 3) {
                    log('TIMEOUT: La solicitud de ubicación tardó demasiado', 'error');
                }
            }

            log('=== TEST COMPLETO FINALIZADO ===', 'info');
        }

        async function testAPI() {
            clearLogs();
            log('=== TEST DIRECTO DE API ===', 'info');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            // Datos de prueba
            const testData = {
                latitud: 20.6736,
                longitud: -103.3440,
                precision: 10.5,
                ciudad: 'Guadalajara',
                estado: 'Jalisco',
                pais: 'México',
                codigo_postal: '44100'
            };

            log(`Enviando datos de prueba a /api/v1/location...`, 'info');
            log(`<pre>${JSON.stringify(testData, null, 2)}</pre>`, 'info');

            try {
                const response = await fetch('/api/v1/location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify(testData)
                });

                log(`Status: ${response.status} ${response.statusText}`, 'info');

                const result = await response.json();

                if (response.ok && result.success) {
                    log('✓ API FUNCIONANDO CORRECTAMENTE', 'success');
                    log(`<pre>${JSON.stringify(result, null, 2)}</pre>`, 'success');
                } else {
                    log('✗ Error en la API', 'error');
                    log(`<pre>${JSON.stringify(result, null, 2)}</pre>`, 'error');
                }

            } catch (error) {
                log(`✗ Error de red: ${error.message}`, 'error');
            }
        }

        // Auto-ejecutar test al cargar (opcional)
        // setTimeout(() => runFullTest(), 1000);
    </script>
</body>
</html>
