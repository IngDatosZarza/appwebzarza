<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Sistema de Puntos</title>
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

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="notificationsApp()">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
                <a href="/" class="hover:text-indigo-600">Inicio</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900">Notificaciones</span>
            </div>
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Notificaciones</h1>
                    <p class="mt-2 text-gray-600">
                        <?= $unreadCount ?> notificaciones sin leer
                    </p>
                </div>
                
                <?php if ($unreadCount > 0): ?>
                    <button 
                        @click="markAllAsRead()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        <i class="fas fa-check-double mr-2"></i>
                        Marcar Todas como Leídas
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="space-y-4">
            <?php if (empty($notifications)): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <i class="fas fa-bell-slash text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes notificaciones</h3>
                    <p class="text-gray-600">Cuando realices compras o canjees cupones, aparecerán aquí</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 <?= !$notification['leida'] ? 'ring-2 ring-indigo-100' : '' ?>"
                         data-notification-id="<?= $notification['id'] ?>">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-4 flex-1">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0">
                                        <?php
                                        $iconClass = '';
                                        $bgClass = '';
                                        
                                        switch ($notification['tipo']) {
                                            case 'welcome':
                                                $iconClass = 'fas fa-hand-wave';
                                                $bgClass = 'bg-blue-100 text-blue-600';
                                                break;
                                            case 'purchase':
                                                $iconClass = 'fas fa-coins';
                                                $bgClass = 'bg-green-100 text-green-600';
                                                break;
                                            case 'coupon':
                                                $iconClass = 'fas fa-gift';
                                                $bgClass = 'bg-purple-100 text-purple-600';
                                                break;
                                            case 'promotion':
                                                $iconClass = 'fas fa-bullhorn';
                                                $bgClass = 'bg-orange-100 text-orange-600';
                                                break;
                                            case 'system':
                                                $iconClass = 'fas fa-cog';
                                                $bgClass = 'bg-gray-100 text-gray-600';
                                                break;
                                            default:
                                                $iconClass = 'fas fa-bell';
                                                $bgClass = 'bg-indigo-100 text-indigo-600';
                                        }
                                        ?>
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center <?= $bgClass ?>">
                                            <i class="<?= $iconClass ?> text-xl"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                <?= htmlspecialchars($notification['titulo']) ?>
                                            </h3>
                                            <?php if (!$notification['leida']): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    Nueva
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="text-gray-600 mb-2">
                                            <?= htmlspecialchars($notification['mensaje']) ?>
                                        </p>
                                        
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span>
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php
                                                $time = time() - strtotime($notification['created_at']);
                                                if ($time < 60) echo 'Hace un momento';
                                                elseif ($time < 3600) echo 'Hace ' . floor($time/60) . ' minutos';
                                                elseif ($time < 86400) echo 'Hace ' . floor($time/3600) . ' horas';
                                                elseif ($time < 2592000) echo 'Hace ' . floor($time/86400) . ' días';
                                                else echo date('d/m/Y', strtotime($notification['created_at']));
                                                ?>
                                            </span>
                                            <span class="capitalize">
                                                <i class="fas fa-tag mr-1"></i>
                                                <?php
                                                $labels = [
                                                    'welcome' => 'Bienvenida',
                                                    'purchase' => 'Compra',
                                                    'coupon' => 'Cupón',
                                                    'promotion' => 'Promoción',
                                                    'system' => 'Sistema'
                                                ];
                                                echo $labels[$notification['tipo']] ?? $notification['tipo'];
                                                ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Additional data -->
                                        <?php if ($notification['datos']): ?>
                                            <?php $data = json_decode($notification['datos'], true); ?>
                                            <?php if ($data['type'] === 'purchase' && isset($data['amount'])): ?>
                                                <div class="mt-3 p-3 bg-green-50 rounded-lg">
                                                    <div class="flex items-center justify-between text-sm">
                                                        <span class="text-green-700">Compra realizada:</span>
                                                        <span class="font-semibold text-green-800">$<?= number_format($data['amount'], 2) ?></span>
                                                    </div>
                                                    <div class="flex items-center justify-between text-sm">
                                                        <span class="text-green-700">Puntos ganados:</span>
                                                        <span class="font-semibold text-green-800"><?= $data['points'] ?> pts</span>
                                                    </div>
                                                </div>
                                            <?php elseif ($data['type'] === 'coupon_redeemed' && isset($data['points_used'])): ?>
                                                <div class="mt-3 p-3 bg-purple-50 rounded-lg">
                                                    <div class="flex items-center justify-between text-sm">
                                                        <span class="text-purple-700">Puntos utilizados:</span>
                                                        <span class="font-semibold text-purple-800"><?= $data['points_used'] ?> pts</span>
                                                    </div>
                                                    <?php if (isset($data['qr_code'])): ?>
                                                        <div class="mt-2 text-xs text-purple-600">
                                                            Código QR: <?= htmlspecialchars($data['qr_code']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex-shrink-0 ml-4">
                                    <?php if (!$notification['leida']): ?>
                                        <button 
                                            @click="markAsRead(<?= $notification['id'] ?>)"
                                            class="text-indigo-600 hover:text-indigo-800 transition-colors duration-200"
                                            title="Marcar como leída">
                                            <i class="fas fa-check text-lg"></i>
                                        </button>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-green-500 text-lg" title="Leída"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="/" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                            <i class="fas fa-home text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Dashboard</h3>
                        <p class="text-sm text-gray-600">Volver al inicio</p>
                    </div>
                </div>
            </a>
            
            <a href="/transactions" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                            <i class="fas fa-history text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Transacciones</h3>
                        <p class="text-sm text-gray-600">Ver historial</p>
                    </div>
                </div>
            </a>
            
            <a href="/coupons" class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors duration-200">
                            <i class="fas fa-gift text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Cupones</h3>
                        <p class="text-sm text-gray-600">Canjear premios</p>
                    </div>
                </div>
            </a>
        </div>
    </main>

    <script>
        function notificationsApp() {
            return {
                async markAsRead(notificationId) {
                    try {
                        const response = await fetch('/notifications/mark-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                notification_id: notificationId
                            })
                        });
                        
                        if (response.ok) {
                            // Actualizar visualmente la notificación
                            const element = document.querySelector(`[data-notification-id="${notificationId}"]`);
                            if (element) {
                                element.classList.remove('ring-2', 'ring-indigo-100');
                                const badge = element.querySelector('.bg-indigo-100');
                                if (badge) badge.remove();
                                
                                const button = element.querySelector('button');
                                if (button) {
                                    button.outerHTML = '<i class="fas fa-check-circle text-green-500 text-lg" title="Leída"></i>';
                                }
                            }
                            
                            // Actualizar contador
                            this.updateUnreadCount();
                        }
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }
                },
                
                async markAllAsRead() {
                    if (!confirm('¿Marcar todas las notificaciones como leídas?')) return;
                    
                    try {
                        const response = await fetch('/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        });
                        
                        if (response.ok) {
                            location.reload();
                        }
                    } catch (error) {
                        console.error('Error marking all notifications as read:', error);
                    }
                },
                
                updateUnreadCount() {
                    // Esta función se podría usar para actualizar el contador sin recargar
                    // la página completa
                }
            }
        }
    </script>
</body>
</html>