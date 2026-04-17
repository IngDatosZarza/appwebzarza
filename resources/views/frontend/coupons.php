<?php 
// Incluir helper de usuario
$userHelperPath = realpath(__DIR__ . '/../../../app/Helpers/user_helper.php');
if ($userHelperPath && file_exists($userHelperPath)) {
    require_once $userHelperPath;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupones - Sistema de Puntos de Fidelidad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-white text-xl font-bold">
                        <i class="fas fa-star text-yellow-300 mr-2"></i>
                        FidelityPoints
                    </h1>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="/" class="text-white hover:text-yellow-300 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-1"></i> Inicio
                        </a>
                        <a href="/cupones" class="text-yellow-300 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-ticket-alt mr-1"></i> Cupones
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php $user = getCurrentUser(); ?>
                    <?php if ($user): ?>
                        <div class="flex items-center space-x-3">
                            <div class="text-white text-sm">
                                <i class="fas fa-coins text-yellow-300 mr-1"></i>
                                <span class="font-semibold"><?= number_format($user->puntos) ?> pts</span>
                            </div>
                            <div class="text-white text-sm">
                                <i class="fas fa-user-circle mr-1"></i>
                                <span><?= htmlspecialchars($user->nombre) ?></span>
                            </div>
                            <a href="/logout" class="text-white hover:text-yellow-300 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-out-alt mr-1"></i> Salir
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex space-x-2">
                            <a href="/login" class="text-white hover:text-yellow-300 px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-in-alt mr-1"></i> Iniciar Sesión
                            </a>
                            <a href="/register" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-user-plus mr-1"></i> Registrarse
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-ticket-alt text-purple-500 mr-3"></i>
                        Cupones Disponibles
                    </h1>
                    <p class="text-gray-600 mt-2">Descubre increíbles descuentos y ofertas especiales</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Cupones activos</div>
                    <div class="text-2xl font-bold text-purple-600">
                        <i class="fas fa-gift mr-1"></i>
                        <?php
                        $cupones_activos = $pdo->query("SELECT COUNT(*) FROM cupones WHERE activo = true AND fecha_vencimiento >= CURRENT_DATE")->fetchColumn() ?: 0;
                        echo $cupones_activos;
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Coupons -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">
                <i class="fas fa-shopping-bag text-purple-500 mr-2"></i>
                Cupones para Canjear
            </h2>
            
            <?php
            $stmt = $pdo->query("
                SELECT * FROM cupones 
                WHERE activo = true 
                AND fecha_vencimiento >= CURRENT_DATE 
                AND cantidad_disponible > 0 
                ORDER BY puntos_requeridos ASC
            ");
            $cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if (count($cupones) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($cupones as $cupon): ?>
                        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 card-hover">
                            <!-- Coupon Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($cupon['nombre']) ?></h3>
                                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($cupon['descripcion']) ?></p>
                                </div>
                                <?php if ($cupon['tipo_descuento'] === 'porcentaje'): ?>
                                    <div class="bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                        <?= $cupon['valor_descuento'] ?>% OFF
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gradient-to-r from-green-500 to-blue-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                        $<?= number_format($cupon['valor_descuento'], 2) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Coupon Details -->
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Puntos requeridos:</span>
                                    <span class="font-semibold text-purple-600">
                                        <i class="fas fa-coins mr-1"></i>
                                        <?= number_format($cupon['puntos_requeridos']) ?>
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Disponibles:</span>
                                    <span class="font-semibold text-gray-900"><?= $cupon['cantidad_disponible'] ?></span>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Válido hasta:</span>
                                    <span class="font-semibold text-gray-900"><?= date('d/m/Y', strtotime($cupon['fecha_vencimiento'])) ?></span>
                                </div>
                            </div>
                            
                            <!-- Action Button -->
                            <?php $user = getCurrentUser(); ?>
                            <?php if ($user): ?>
                                <?php
                                $puedeCanjar = $user->puntos >= $cupon['puntos_requeridos'];
                                // Verificar si ya canjeó este cupón
                                $yaCanjeado = false;
                                try {
                                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM cupones_asignados WHERE usuario_id = ? AND cupon_id = ? AND estado = 'asignado'");
                                    $checkStmt->execute([$user->id, $cupon['id']]);
                                    $yaCanjeado = $checkStmt->fetchColumn() > 0;
                                } catch (Exception $e) {}
                                ?>
                                
                                <?php if ($yaCanjeado && !$cupon['multiple_uso']): ?>
                                    <button disabled class="w-full bg-gray-100 text-gray-500 px-4 py-2 rounded-lg font-medium cursor-not-allowed">
                                        <i class="fas fa-check mr-2"></i>
                                        Ya Canjeado
                                    </button>
                                <?php elseif ($puedeCanjar): ?>
                                    <form action="/canjear-cupon" method="POST">
                                        <input type="hidden" name="cupon_id" value="<?= $cupon['id'] ?>">
                                        <button type="submit" class="w-full bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                            <i class="fas fa-gift mr-2"></i>
                                            Canjear Cupón
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="w-full text-center">
                                        <button disabled class="w-full bg-gray-100 text-gray-500 px-4 py-2 rounded-lg font-medium cursor-not-allowed mb-2">
                                            <i class="fas fa-lock mr-2"></i>
                                            Puntos Insuficientes
                                        </button>
                                        <p class="text-xs text-red-600">
                                            Te faltan <?= number_format($cupon['puntos_requeridos'] - $user->puntos) ?> puntos
                                        </p>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/login" class="block w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white text-center px-4 py-2 rounded-lg font-medium transition-colors">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Iniciar Sesión para Canjear
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-ticket-alt text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay cupones disponibles</h3>
                    <p class="text-gray-600">¡Vuelve pronto para ver nuevas ofertas!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- How It Works -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg shadow-sm p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 text-center">
                <i class="fas fa-question-circle text-blue-500 mr-2"></i>
                ¿Cómo Funciona el Canje?
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-blue-600 font-bold">1</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Acumula Puntos</h3>
                    <p class="text-sm text-gray-600">Gana puntos con cada compra en nuestras sucursales</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-purple-600 font-bold">2</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Elige tu Cupón</h3>
                    <p class="text-sm text-gray-600">Selecciona el cupón que más te convenga</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-green-600 font-bold">3</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Canjea</h3>
                    <p class="text-sm text-gray-600">Usa tus puntos para obtener el cupón</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-orange-600 font-bold">4</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Disfruta</h3>
                    <p class="text-sm text-gray-600">Presenta tu código QR en cualquier sucursal</p>
                </div>
            </div>
        </div>

        <!-- Coupon Stats -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie text-indigo-500 mr-2"></i>
                Estadísticas de Cupones
            </h2>
            
            <?php
            $stats_cupones = $pdo->query("
                SELECT 
                    COUNT(*) as total_cupones,
                    COUNT(CASE WHEN activo = true THEN 1 END) as cupones_activos,
                    SUM(cantidad_disponible) as total_disponibles,
                    COUNT(CASE WHEN fecha_vencimiento < CURRENT_DATE THEN 1 END) as cupones_vencidos
                FROM cupones
            ")->fetch(PDO::FETCH_ASSOC);
            
            $cupones_canjeados = $pdo->query("SELECT COUNT(*) FROM cupones_asignados")->fetchColumn() ?: 0;
            ?>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?= number_format($stats_cupones['total_cupones']) ?></div>
                    <div class="text-xs text-gray-600">Total Cupones</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?= number_format($stats_cupones['cupones_activos']) ?></div>
                    <div class="text-xs text-gray-600">Activos</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600"><?= number_format($stats_cupones['total_disponibles']) ?></div>
                    <div class="text-xs text-gray-600">Disponibles</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600"><?= number_format($cupones_canjeados) ?></div>
                    <div class="text-xs text-gray-600">Canjeados</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600"><?= number_format($stats_cupones['cupones_vencidos']) ?></div>
                    <div class="text-xs text-gray-600">Vencidos</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-star text-yellow-400 mr-2"></i>
                    FidelityPoints
                </h3>
                <p class="text-gray-300">Sistema de puntos de fidelidad para recompensar a nuestros clientes más valiosos.</p>
                <p class="text-gray-400 mt-4">&copy; <?= date('Y') ?> FidelityPoints. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>