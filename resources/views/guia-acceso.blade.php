<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guía de Acceso - La Zarza Contigo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full mb-4">
                <i class="fas fa-info-circle text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Guía de Acceso - La Zarza Contigo</h1>
            <p class="text-lg text-gray-600">Cómo acceder al módulo de tickets y otras funcionalidades</p>
        </div>

        <!-- Estado Actual -->
        <div class="bg-green-50 border-l-4 border-green-400 p-6 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-green-800">✅ Sistema Funcionando Correctamente</h3>
                    <p class="mt-2 text-sm text-green-700">
                        El módulo de tickets y todas las funcionalidades están operativas. 
                        Solo necesitas autenticarte para acceder a las rutas protegidas.
                    </p>
                </div>
            </div>
        </div>

        <!-- Pasos para Acceder -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-list-ol text-blue-600 mr-2"></i>
                Pasos para Acceder al Sistema
            </h2>
            
            <div class="space-y-6">
                <!-- Paso 1 -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Ir a la página de Login</h3>
                        <p class="text-gray-600 mb-2">Accede a la página de inicio de sesión:</p>
                        <a href="/login" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Ir a Login
                        </a>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Iniciar Sesión</h3>
                        <p class="text-gray-600 mb-3">Usa estas credenciales de prueba:</p>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email:</label>
                                    <code class="bg-white px-2 py-1 rounded text-sm">cliente@test.com</code>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Password:</label>
                                    <code class="bg-white px-2 py-1 rounded text-sm">password</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paso 3 -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">3</div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Acceder al Módulo de Tickets</h3>
                        <p class="text-gray-600 mb-3">Después del login exitoso, podrás acceder a:</p>
                        <div class="space-y-2">
                            <a href="/tickets" 
                               class="block px-4 py-2 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 transition-colors">
                                <i class="fas fa-receipt mr-2"></i>
                                Mis Tickets
                            </a>
                            <a href="/tickets/create" 
                               class="block px-4 py-2 bg-purple-100 text-purple-800 rounded-lg hover:bg-purple-200 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Registrar Ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Funcionalidades Disponibles -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Funcionalidades Disponibles
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">🎫 Módulo de Tickets</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>• Registrar tickets con número único</li>
                        <li>• Ganar 100 puntos por ticket</li>
                        <li>• Ver historial de tickets</li>
                        <li>• Detalles completos de cada ticket</li>
                        <li>• Estadísticas personalizadas</li>
                    </ul>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">🎁 Otras Funcionalidades</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>• Sistema de cupones</li>
                        <li>• Historial de compras</li>
                        <li>• Panel de administración</li>
                        <li>• Gestión de puntos</li>
                        <li>• Perfil de usuario</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Test de Enlaces -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-link text-blue-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-blue-800">🔗 Enlaces de Prueba</h3>
                    <p class="mt-2 text-sm text-blue-700 mb-4">
                        Usa estos enlaces para probar el sistema después de iniciar sesión:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <a href="/" class="block px-3 py-2 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200 transition-colors">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                        <a href="/tickets" class="block px-3 py-2 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200 transition-colors">
                            <i class="fas fa-receipt mr-2"></i>Mis Tickets
                        </a>
                        <a href="/tickets/create" class="block px-3 py-2 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Registrar Ticket
                        </a>
                        <a href="/cupones" class="block px-3 py-2 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200 transition-colors">
                            <i class="fas fa-gift mr-2"></i>Cupones
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="text-center">
            <div class="space-x-4">
                <a href="/login" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Iniciar Sesión
                </a>
                <a href="/" 
                   class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                    <i class="fas fa-home mr-2"></i>
                    Ir al Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>