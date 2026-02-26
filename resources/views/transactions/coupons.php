<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupones - Sistema de Puntos</title>
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
                    <div class="flex items-center space-x-2 bg-indigo-50 px-3 py-1 rounded-full">
                        <i class="fas fa-coins text-indigo-600"></i>
                        <span class="font-semibold text-indigo-800"><?= number_format($currentBalance ?? 0) ?> puntos</span>
                    </div>
                    
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
                <a href="/" class="hover:text-indigo-600">Inicio</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900">Cupones</span>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900">Cupones Disponibles</h1>
            <p class="mt-2 text-gray-600">Canjea tus puntos por increíbles premios y descuentos</p>
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

        <!-- Balance Card -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-medium opacity-90">Puntos Disponibles</h2>
                    <p class="text-4xl font-bold mt-1"><?= number_format($currentBalance ?? 0) ?></p>
                    <p class="text-sm opacity-75 mt-1">listos para canjear</p>
                </div>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-gift"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Available Coupons -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Cupones para Canjear</h2>
                    </div>
                    
                    <?php if (empty($coupons)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-gift text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cupones disponibles</h3>
                            <p class="text-gray-600">No hay cupones activos en este momento</p>
                        </div>
                    <?php else: ?>
                        <div class="p-6 space-y-4">
                            <?php foreach ($coupons as $coupon): ?>
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                                <?= htmlspecialchars($coupon['nombre']) ?>
                                            </h3>
                                            <p class="text-gray-600 mb-4">
                                                <?= htmlspecialchars($coupon['descripcion']) ?>
                                            </p>
                                            
                                            <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
                                                <span>
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    Válido hasta: <?= date('d/m/Y', strtotime($coupon['fecha_fin'])) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-2xl font-bold text-indigo-600">
                                                        <?= number_format($coupon['puntos_requeridos']) ?>
                                                    </span>
                                                    <span class="text-gray-600">puntos</span>
                                                </div>
                                                
                                                <?php if ($currentBalance >= $coupon['puntos_requeridos']): ?>
                                                    <form action="/coupons/redeem" method="POST" class="inline">
                                                        <input type="hidden" name="coupon_id" value="<?= $coupon['id'] ?>">
                                                        <button 
                                                            type="submit"
                                                            onclick="return confirm('¿Estás seguro de canjear este cupón?')"
                                                            class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 font-medium">
                                                            <i class="fas fa-exchange-alt mr-2"></i>
                                                            Canjear
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button 
                                                        disabled
                                                        class="bg-gray-300 text-gray-500 px-6 py-2 rounded-lg cursor-not-allowed font-medium">
                                                        <i class="fas fa-lock mr-2"></i>
                                                        Puntos Insuficientes
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- My Coupons Sidebar -->
            <div class="space-y-6">
                <!-- My Assigned Coupons -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Mis Cupones</h3>
                    </div>
                    
                    <?php if (empty($assignedCoupons)): ?>
                        <div class="p-6 text-center">
                            <i class="fas fa-ticket-alt text-3xl text-gray-300 mb-3"></i>
                            <p class="text-gray-600 text-sm">No tienes cupones canjeados</p>
                        </div>
                    <?php else: ?>
                        <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
                            <?php 
                            // Obtener información del cupón para cada uno asignado
                            foreach ($assignedCoupons as $assigned): 
                                $couponInfo = null;
                                foreach ($coupons as $c) {
                                    if ($c['id'] == $assigned['cupon_id']) {
                                        $couponInfo = $c;
                                        break;
                                    }
                                }
                                if (!$couponInfo) continue;
                            ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-1">
                                        <?= htmlspecialchars($couponInfo['nombre']) ?>
                                    </h4>
                                    
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="<?= $assigned['estado'] === 'pendiente' ? 'text-yellow-600' : ($assigned['estado'] === 'usado' ? 'text-green-600' : 'text-gray-600') ?>">
                                            <?= ucfirst($assigned['estado']) ?>
                                        </span>
                                        <span class="text-gray-500">
                                            <?= date('d/m/Y', strtotime($assigned['created_at'])) ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($assigned['estado'] === 'pendiente'): ?>
                                        <div class="mt-2 p-2 bg-gray-50 rounded text-center">
                                            <span class="text-xs font-mono text-gray-600">
                                                <?= htmlspecialchars($assigned['codigo_qr']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-indigo-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-indigo-900 mb-4">Acciones Rápidas</h3>
                    
                    <div class="space-y-3">
                        <a href="/purchase" 
                           class="flex items-center space-x-3 text-indigo-700 hover:text-indigo-900 transition-colors duration-200">
                            <i class="fas fa-plus w-5"></i>
                            <span>Registrar Compra</span>
                        </a>
                        
                        <a href="/transactions" 
                           class="flex items-center space-x-3 text-indigo-700 hover:text-indigo-900 transition-colors duration-200">
                            <i class="fas fa-history w-5"></i>
                            <span>Ver Historial</span>
                        </a>
                        
                        <a href="/" 
                           class="flex items-center space-x-3 text-indigo-700 hover:text-indigo-900 transition-colors duration-200">
                            <i class="fas fa-home w-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>

                <!-- Info -->
                <div class="bg-yellow-50 rounded-lg p-6">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        <h3 class="text-sm font-semibold text-yellow-900">Información</h3>
                    </div>
                    
                    <ul class="text-xs text-yellow-800 space-y-1">
                        <li>• Los cupones se activan inmediatamente</li>
                        <li>• Presenta el código QR en la sucursal</li>
                        <li>• Los cupones tienen fecha de vencimiento</li>
                        <li>• No se pueden devolver puntos canjeados</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</body>
</html>