<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Compra - Sistema de Puntos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2">
                        <i class="fas fa-star text-indigo-600 text-xl"></i>
                        <span class="font-bold text-xl text-gray-900">Sistema de Puntos</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-1 text-gray-600">
                        <i class="fas fa-user text-sm"></i>
                        <span class="text-sm"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                    </div>
                    
                    <a href="/logout" class="text-red-600 hover:text-red-800 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
                <a href="/" class="hover:text-indigo-600">Inicio</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="/transactions" class="hover:text-indigo-600">Transacciones</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900">Registrar Compra</span>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900">Registrar Compra</h1>
            <p class="mt-2 text-gray-600">Registra tu compra para ganar puntos de fidelidad</p>
        </div>

        <!-- Error Messages -->
        <?php if (isset($_SESSION['errors'])): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <h3 class="text-sm font-medium text-red-800">Se encontraron errores:</h3>
                </div>
                <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Purchase Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Datos de la Compra</h2>
                    </div>
                    
                    <form action="/purchase/process" method="POST" class="p-6 space-y-6" x-data="{ amount: 0 }">
                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Monto de la Compra *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                <input 
                                    type="number" 
                                    id="amount" 
                                    name="amount" 
                                    step="0.01" 
                                    min="0.01"
                                    x-model="amount"
                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                    placeholder="0.00"
                                    required>
                            </div>
                            <p class="mt-1 text-xs text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Ganarás <span x-text="Math.floor(amount || 0)"></span> puntos por esta compra
                            </p>
                        </div>

                        <!-- Branch -->
                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Sucursal *
                            </label>
                            <select 
                                id="branch_id" 
                                name="branch_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                required>
                                <option value="">Selecciona una sucursal</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= $branch['id'] ?>">
                                        <?= htmlspecialchars($branch['nombre']) ?> (<?= htmlspecialchars($branch['codigo']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción (opcional)
                            </label>
                            <input 
                                type="text" 
                                id="description" 
                                name="description"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                placeholder="Ej: Compra de productos varios">
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button 
                                type="submit"
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200 font-medium">
                                <i class="fas fa-plus mr-2"></i>
                                Registrar Compra
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Sidebar -->
            <div class="space-y-6">
                <!-- Points Info -->
                <div class="bg-indigo-50 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-coins text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-indigo-900">¿Cómo Funciona?</h3>
                        </div>
                    </div>
                    
                    <div class="space-y-3 text-sm text-indigo-800">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-indigo-600 mt-0.5"></i>
                            <span>Ganas <strong>1 punto por cada peso</strong> gastado</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-indigo-600 mt-0.5"></i>
                            <span>Los puntos se acumulan automáticamente</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-indigo-600 mt-0.5"></i>
                            <span>Puedes canjear por cupones y descuentos</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                    
                    <div class="space-y-3">
                        <a href="/transactions" 
                           class="flex items-center space-x-3 text-gray-600 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-history w-5"></i>
                            <span>Ver Historial</span>
                        </a>
                        
                        <a href="/coupons" 
                           class="flex items-center space-x-3 text-gray-600 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-gift w-5"></i>
                            <span>Ver Cupones</span>
                        </a>
                        
                        <a href="/" 
                           class="flex items-center space-x-3 text-gray-600 hover:text-indigo-600 transition-colors duration-200">
                            <i class="fas fa-home w-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-yellow-50 rounded-lg p-6">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-lightbulb text-yellow-600 mr-2"></i>
                        <h3 class="text-sm font-semibold text-yellow-900">Consejos</h3>
                    </div>
                    
                    <ul class="text-xs text-yellow-800 space-y-1">
                        <li>• Registra todas tus compras para maximizar puntos</li>
                        <li>• Los puntos nunca caducan</li>
                        <li>• Revisa cupones disponibles regularmente</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</body>
</html>