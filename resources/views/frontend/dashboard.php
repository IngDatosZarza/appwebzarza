<?php 
// Incluir helpers necesarios
$userHelperPath = realpath(__DIR__ . '/../../../app/Helpers/user_helper.php');
if ($userHelperPath && file_exists($userHelperPath)) {
    require_once $userHelperPath;
}

$dbHelperPath = realpath(__DIR__ . '/../../../app/Helpers/database_helper.php');
if ($dbHelperPath && file_exists($dbHelperPath)) {
    require_once $dbHelperPath;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Puntos de Fidelidad</title>
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
                        <a href="/transactions" class="text-white hover:text-yellow-300 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-history mr-1"></i> Transacciones
                        </a>
                        <a href="/purchase" class="text-white hover:text-yellow-300 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-plus mr-1"></i> Registrar Compra
                        </a>
                        <a href="/coupons" class="text-white hover:text-yellow-300 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-gift mr-1"></i> Cupones
                        </a>
                        <a href="/notifications" class="text-white hover:text-yellow-300 px-3 py-2 rounded-md text-sm font-medium relative">
                            <i class="fas fa-bell mr-1"></i> Notificaciones
                            <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" style="display: none;"></span>
                        </a>
                        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
                            <a href="/admin/points" class="text-yellow-300 hover:text-yellow-100 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-cog mr-1"></i> Admin
                            </a>
                        <?php endif; ?>
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
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-white hover:text-yellow-300 focus:outline-none">
                                    <i class="fas fa-user-circle text-xl mr-1"></i>
                                    <span class="hidden md:block"><?= htmlspecialchars($user->nombre) ?></span>
                                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <a href="/perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i> Mi Perfil
                                    </a>
                                    <a href="/mis-cupones" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-ticket-alt mr-2"></i> Mis Cupones
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                    </a>
                                </div>
                            </div>
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
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-chart-line text-blue-500 mr-3"></i>
                        Dashboard
                    </h1>
                    <p class="text-gray-600 mt-2">Bienvenido al sistema de puntos de fidelidad</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Última actualización</div>
                    <div class="text-lg font-semibold text-gray-900"><?= date('d/m/Y H:i') ?></div>
                </div>
            </div>
        </div>

        <?php $user = getCurrentUser(); ?>
        <?php if ($user): ?>
            <!-- User Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Mis Puntos</p>
                            <p class="text-3xl font-bold"><?= number_format($user->puntos) ?></p>
                        </div>
                        <div class="text-blue-200">
                            <i class="fas fa-coins text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Nivel</p>
                            <p class="text-2xl font-bold">
                                <?php 
                                if($user->puntos < 100) echo 'Bronce';
                                elseif($user->puntos < 500) echo 'Plata'; 
                                elseif($user->puntos < 1000) echo 'Oro';
                                else echo 'Platino';
                                ?>
                            </p>
                        </div>
                        <div class="text-green-200">
                            <i class="fas fa-trophy text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-sm p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Mis Cupones</p>
                            <p class="text-3xl font-bold">
                                <?php
                                echo getUserCouponsCount($user->id);
                                ?>
                            </p>
                        </div>
                        <div class="text-purple-200">
                            <i class="fas fa-ticket-alt text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-sm p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Compras</p>
                            <p class="text-3xl font-bold">
                                <?php
                                echo getUserPurchasesCount($user->id);
                                ?>
                            </p>
                        </div>
                        <div class="text-orange-200">
                            <i class="fas fa-shopping-cart text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Back Message -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 mb-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        ¡Hola, <?= htmlspecialchars(explode(' ', $user->nombre)[0]) ?>! 👋
                    </h1>
                    <p class="text-lg text-gray-600">
                        Tienes <strong><?= number_format($user->puntos) ?> puntos</strong> disponibles para canjear
                    </p>
                </div>
            </div>
        <?php else: ?>
            <!-- Welcome Section -->
            <div class="text-center py-12 mb-8">
                <div class="max-w-3xl mx-auto">
                    <i class="fas fa-star text-6xl text-yellow-400 mb-6"></i>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        ¡Bienvenido a FidelityPoints!
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        Únete a nuestro programa de fidelidad y comienza a ganar puntos con cada compra
                    </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Compra y Gana</h3>
                        <p class="text-gray-600">Gana 1 punto por cada peso gastado en nuestras sucursales</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-gift text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Canjea Cupones</h3>
                        <p class="text-gray-600">Usa tus puntos para obtener descuentos y ofertas especiales</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Sube de Nivel</h3>
                        <p class="text-gray-600">Alcanza diferentes niveles y desbloquea beneficios exclusivos</p>
                    </div>
                </div>
                
                    <div class="space-x-4">
                        <a href="/register" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-user-plus mr-2"></i>
                            Registrarse Gratis
                        </a>
                        <a href="/login" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Iniciar Sesión
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($user): ?>
            <!-- Recent Notifications Widget -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8" id="notifications-widget">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-bell text-indigo-500 mr-2"></i>
                        Notificaciones Recientes
                    </h3>
                    <a href="/notifications" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                        Ver todas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div id="recent-notifications" class="space-y-3">
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Cargando notificaciones...
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions for logged in users -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="/purchase" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 card-hover">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                                <i class="fas fa-plus text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Registrar Compra</h3>
                            <p class="text-sm text-gray-600">Gana puntos por tus compras</p>
                        </div>
                    </div>
                </a>
                
                <a href="/transactions" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 card-hover">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                                <i class="fas fa-history text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Ver Historial</h3>
                            <p class="text-sm text-gray-600">Revisa todas tus transacciones</p>
                        </div>
                    </div>
                </a>
                
                <a href="/coupons" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 card-hover">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors duration-200">
                                <i class="fas fa-gift text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Canjear Cupones</h3>
                            <p class="text-sm text-gray-600">Usa tus puntos por premios</p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- System Stats -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6 text-center">
                <i class="fas fa-chart-bar text-indigo-500 mr-2"></i>
                Estadísticas del Sistema
            </h2>
            
            <?php
            $stats = getSystemStats();
            $usuarios = $stats['usuarios'];
            $compras = $stats['compras'];
            $cupones = $stats['cupones'];
            $sucursales = $stats['sucursales'];
            $puntos_total = $stats['puntos_total'];
            ?>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600"><?= number_format($usuarios) ?></div>
                    <div class="text-sm text-gray-600 mt-1">Usuarios Registrados</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600"><?= number_format($compras) ?></div>
                    <div class="text-sm text-gray-600 mt-1">Compras Realizadas</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600"><?= number_format($cupones) ?></div>
                    <div class="text-sm text-gray-600 mt-1">Cupones Disponibles</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600"><?= number_format($sucursales) ?></div>
                    <div class="text-sm text-gray-600 mt-1">Sucursales Activas</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600"><?= number_format($puntos_total) ?></div>
                    <div class="text-sm text-gray-600 mt-1">Puntos en Circulación</div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-users text-blue-500 mr-2"></i>
                Usuarios del Sistema
            </h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $users = getAllUsers();
                        
                        foreach ($users as $row) {
                            $badge_class = $row['rol'] == 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                            echo '<tr>';
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($row['nombre']) . '</td>';
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . htmlspecialchars($row['email']) . '</td>';
                            echo '<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $badge_class . '">' . ucfirst($row['rol']) . '</span></td>';
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-purple-600">' . number_format($row['puntos']) . ' pts</td>';
                            echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . $row['fecha'] . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- API Information -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-code text-green-500 mr-2"></i>
                API REST Disponible
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Endpoints Principales</h3>
                    <ul class="space-y-1 text-sm text-gray-600">
                        <li><code class="bg-gray-100 px-2 py-1 rounded">GET /api/health</code> - Estado API</li>
                        <li><code class="bg-gray-100 px-2 py-1 rounded">POST /api/v1/auth/login</code> - Autenticación</li>
                        <li><code class="bg-gray-100 px-2 py-1 rounded">POST /api/v1/purchases</code> - Registrar compras</li>
                        <li><code class="bg-gray-100 px-2 py-1 rounded">POST /api/v1/coupons/redeem</code> - Canjear cupones</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Enlaces de Prueba</h3>
                    <div class="space-y-2">
                        <a href="/api/health" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Probar API Health
                        </a>
                    </div>
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

    <!-- JavaScript para notificaciones -->
    <script>
        // Cargar notificaciones recientes al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($user): ?>
                loadRecentNotifications();
                // Actualizar cada 30 segundos
                setInterval(loadRecentNotifications, 30000);
            <?php endif; ?>
        });

        async function loadRecentNotifications() {
            try {
                const response = await fetch('/notifications/api');
                const data = await response.json();
                
                if (data.success) {
                    displayNotifications(data.notifications, data.unread_count);
                    updateNotificationBadge(data.unread_count);
                }
            } catch (error) {
                console.error('Error cargando notificaciones:', error);
            }
        }

        function displayNotifications(notifications, unreadCount) {
            const container = document.getElementById('recent-notifications');
            
            if (notifications.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-bell-slash mr-2"></i>
                        No tienes notificaciones
                    </div>
                `;
                return;
            }

            const recentNotifications = notifications.slice(0, 3); // Solo mostrar 3 más recientes
            
            container.innerHTML = recentNotifications.map(notification => {
                const iconClass = getNotificationIcon(notification.tipo);
                const bgClass = getNotificationBgClass(notification.tipo);
                const timeAgo = formatTimeAgo(notification.created_at);
                
                return `
                    <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 ${!notification.leida ? 'bg-indigo-50' : ''}">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center ${bgClass}">
                                <i class="${iconClass} text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                ${notification.titulo}
                            </p>
                            <p class="text-xs text-gray-600 truncate">
                                ${notification.mensaje}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${timeAgo}
                            </p>
                        </div>
                        ${!notification.leida ? '<div class="w-2 h-2 bg-indigo-500 rounded-full"></div>' : ''}
                    </div>
                `;
            }).join('');
        }

        function updateNotificationBadge(count) {
            const badge = document.getElementById('notification-badge');
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }

        function getNotificationIcon(type) {
            const icons = {
                'welcome': 'fas fa-hand-wave',
                'purchase': 'fas fa-coins',
                'coupon': 'fas fa-gift',
                'promotion': 'fas fa-bullhorn',
                'system': 'fas fa-cog'
            };
            return icons[type] || 'fas fa-bell';
        }

        function getNotificationBgClass(type) {
            const classes = {
                'welcome': 'bg-blue-100 text-blue-600',
                'purchase': 'bg-green-100 text-green-600',
                'coupon': 'bg-purple-100 text-purple-600',
                'promotion': 'bg-orange-100 text-orange-600',
                'system': 'bg-gray-100 text-gray-600'
            };
            return classes[type] || 'bg-indigo-100 text-indigo-600';
        }

        function formatTimeAgo(datetime) {
            const now = new Date();
            const time = new Date(datetime);
            const diff = Math.floor((now - time) / 1000);
            
            if (diff < 60) return 'Hace un momento';
            if (diff < 3600) return `Hace ${Math.floor(diff/60)} min`;
            if (diff < 86400) return `Hace ${Math.floor(diff/3600)} h`;
            if (diff < 2592000) return `Hace ${Math.floor(diff/86400)} días`;
            
            return time.toLocaleDateString('es-ES');
        }
    </script>
</body>
</html>