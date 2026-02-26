<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Transacciones - Sistema de Puntos</title>
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

    <!-- Header -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Historial de Transacciones</h1>
                    <p class="mt-1 text-gray-600">Revisa todas tus transacciones de puntos</p>
                </div>
                
                <div class="flex space-x-3">
                    <a href="/purchase" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Registrar Compra
                    </a>
                    <a href="/coupons" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        <i class="fas fa-gift mr-2"></i>
                        Ver Cupones
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Balance Card -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-medium opacity-90">Saldo Actual</h2>
                    <p class="text-4xl font-bold mt-1"><?= number_format($currentBalance ?? 0) ?></p>
                    <p class="text-sm opacity-75 mt-1">puntos disponibles</p>
                </div>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Historial de Movimientos</h3>
            </div>
            
            <?php if (empty($transactions)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-exchange-alt text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay transacciones</h3>
                    <p class="text-gray-600 mb-4">Aún no tienes movimientos en tu cuenta</p>
                    <a href="/purchase" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Registrar Primera Compra
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Puntos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Registrado por
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($transactions as $transaction): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($transaction['tipo'] === 'compra'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-plus mr-1"></i>
                                                Compra
                                            </span>
                                        <?php elseif ($transaction['tipo'] === 'canje'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-minus mr-1"></i>
                                                Canje
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-adjust mr-1"></i>
                                                <?= ucfirst($transaction['tipo']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= htmlspecialchars($transaction['descripcion']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($transaction['tipo'] === 'compra'): ?>
                                            <span class="text-green-600">+<?= number_format($transaction['puntos']) ?></span>
                                        <?php elseif ($transaction['tipo'] === 'canje'): ?>
                                            <span class="text-red-600">-<?= number_format($transaction['puntos']) ?></span>
                                        <?php else: ?>
                                            <span class="text-blue-600"><?= number_format($transaction['puntos']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($transaction['registrado_por_nombre']): ?>
                                            <?= htmlspecialchars($transaction['registrado_por_nombre'] . ' ' . $transaction['registrado_por_apellido']) ?>
                                        <?php else: ?>
                                            Sistema
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination info -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <p class="text-sm text-gray-700">
                        Mostrando las últimas 50 transacciones
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="/purchase" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                            <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Registrar Compra</h3>
                        <p class="text-sm text-gray-600">Gana puntos por tus compras</p>
                    </div>
                </div>
            </a>
            
            <a href="/coupons" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors duration-200">
                            <i class="fas fa-gift text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Ver Cupones</h3>
                        <p class="text-sm text-gray-600">Canjea tus puntos por premios</p>
                    </div>
                </div>
            </a>
            
            <a href="/" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition-colors duration-200">
                            <i class="fas fa-home text-gray-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Dashboard</h3>
                        <p class="text-sm text-gray-600">Volver al inicio</p>
                    </div>
                </div>
            </a>
        </div>
    </main>
</body>
</html>