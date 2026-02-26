<?php
// Servidor simple para mostrar información del sistema
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Puntos de Fidelidad</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #4f46e5; 
            text-align: center; 
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .status-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #4f46e5;
        }
        .status-card h3 {
            margin: 0 0 10px 0;
            color: #1e293b;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .users-table th, .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .users-table th {
            background: #4f46e5;
            color: white;
            font-weight: 600;
        }
        .users-table tr:hover {
            background: #f1f5f9;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .badge-admin { background: #fee2e2; color: #dc2626; }
        .badge-cliente { background: #dbeafe; color: #2563eb; }
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .feature-item {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #22c55e;
        }
        .api-section {
            background: #fefce8;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid #eab308;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Sistema de Puntos de Fidelidad</h1>
        
        <?php
        try {
            $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            // Obtener estadísticas
            $usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
            $compras = $pdo->query('SELECT COUNT(*) FROM compras')->fetchColumn();
            $cupones = $pdo->query('SELECT COUNT(*) FROM cupones')->fetchColumn();
            $sucursales = $pdo->query('SELECT COUNT(*) FROM sucursales')->fetchColumn();
            $puntos_total = $pdo->query('SELECT SUM(saldo) FROM puntos')->fetchColumn() ?: 0;
            
            echo '<div class="status-grid">';
            echo '<div class="status-card"><h3>👥 Usuarios Registrados</h3><div style="font-size: 2em; font-weight: bold; color: #4f46e5;">' . $usuarios . '</div></div>';
            echo '<div class="status-card"><h3>🛒 Compras Realizadas</h3><div style="font-size: 2em; font-weight: bold; color: #059669;">' . $compras . '</div></div>';
            echo '<div class="status-card"><h3>🎫 Cupones Disponibles</h3><div style="font-size: 2em; font-weight: bold; color: #dc2626;">' . $cupones . '</div></div>';
            echo '<div class="status-card"><h3>🏪 Sucursales Activas</h3><div style="font-size: 2em; font-weight: bold; color: #7c2d12;">' . $sucursales . '</div></div>';
            echo '<div class="status-card"><h3>💰 Puntos Totales</h3><div style="font-size: 2em; font-weight: bold; color: #b45309;">' . number_format($puntos_total) . '</div></div>';
            echo '</div>';
            
            // Tabla de usuarios
            echo '<h2>👥 Usuarios del Sistema</h2>';
            echo '<table class="users-table">';
            echo '<thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Puntos</th><th>Fecha Registro</th></tr></thead>';
            echo '<tbody>';
            
            $stmt = $pdo->query("SELECT u.nombres || ' ' || u.apellido_paterno as nombre, u.email, u.rol, COALESCE(p.saldo, 0) as puntos, u.created_at::date as fecha FROM usuarios u LEFT JOIN puntos p ON u.id = p.usuario_id ORDER BY u.rol, u.nombres");
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $badge_class = $row['rol'] == 'admin' ? 'badge-admin' : 'badge-cliente';
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td><span class="badge ' . $badge_class . '">' . ucfirst($row['rol']) . '</span></td>';
                echo '<td><strong>' . number_format($row['puntos']) . '</strong> pts</td>';
                echo '<td>' . $row['fecha'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            
            // Características del sistema
            echo '<h2>🚀 Características Implementadas</h2>';
            echo '<div class="feature-list">';
            echo '<div class="feature-item">✅ <strong>Base de Datos PostgreSQL</strong><br>Schema configurado con 11 tablas relacionales</div>';
            echo '<div class="feature-item">✅ <strong>Sistema de Usuarios</strong><br>Roles de cliente y administrador</div>';
            echo '<div class="feature-item">✅ <strong>Gestión de Puntos</strong><br>Acumulación automática por compras</div>';
            echo '<div class="feature-item">✅ <strong>Sistema de Cupones</strong><br>Canje por puntos con códigos QR</div>';
            echo '<div class="feature-item">✅ <strong>Multi-sucursales</strong><br>Gestión de múltiples ubicaciones</div>';
            echo '<div class="feature-item">✅ <strong>Auditoría Completa</strong><br>Trazabilidad de todas las operaciones</div>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div style="background: #fecaca; color: #dc2626; padding: 20px; border-radius: 8px; margin: 20px 0;">';
            echo '<h3>❌ Error de Conexión</h3>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        ?>
        
        <div class="api-section">
            <h2>🔗 API REST Completamente Funcional</h2>
            <p><strong>✅ Estado:</strong> API implementada y lista para usar</p>
            <p><strong>🌐 URL Base:</strong> <code>http://localhost:8080/api</code></p>
            
            <h3>🔓 Endpoints Públicos</h3>
            <ul>
                <li><code>GET /api/health</code> - Estado de la API</li>
                <li><code>POST /api/v1/auth/register</code> - Registrar usuario</li>
                <li><code>POST /api/v1/auth/login</code> - Iniciar sesión</li>
                <li><code>GET /api/v1/coupons</code> - Cupones disponibles</li>
                <li><code>GET /api/v1/stats/purchases</code> - Estadísticas públicas</li>
            </ul>
            
            <h3>🔒 Endpoints Autenticados</h3>
            <ul>
                <li><code>GET /api/v1/auth/profile</code> - Perfil del usuario</li>
                <li><code>PUT /api/v1/auth/profile</code> - Actualizar perfil</li>
                <li><code>POST /api/v1/auth/logout</code> - Cerrar sesión</li>
                <li><code>POST /api/v1/purchases</code> - Registrar compra</li>
                <li><code>GET /api/v1/purchases</code> - Historial de compras</li>
                <li><code>POST /api/v1/coupons/redeem</code> - Canjear cupón</li>
                <li><code>GET /api/v1/user/{id}/coupons</code> - Cupones del usuario</li>
            </ul>
            
            <h3>👑 Endpoints Administrativos</h3>
            <ul>
                <li><code>GET /api/v1/admin/users</code> - Gestión de usuarios</li>
                <li><code>POST /api/v1/admin/coupons</code> - Crear cupones</li>
                <li><code>GET /api/v1/admin/reports/dashboard</code> - Panel administrativo</li>
                <li><code>GET /api/v1/admin/reports/sales</code> - Reportes de ventas</li>
            </ul>
            
            <div style="margin-top: 20px; padding: 15px; background: #ecfdf5; border-radius: 8px; border-left: 4px solid #10b981;">
                <p><strong>🧪 Probar API:</strong></p>
                <p><a href="http://localhost:8080/api/health" target="_blank">Probar Health Check</a></p>
                <p><strong>Autenticación:</strong> Usar Bearer Token en headers</p>
                <p><strong>Documentación:</strong> Swagger/OpenAPI próximamente</p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Sistema de Puntos de Fidelidad</strong> - Desarrollado con Laravel & PostgreSQL</p>
            <p>📊 Base de datos: <strong>appweb</strong> | 🌐 Puerto: <strong>8080</strong> | 📅 <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>