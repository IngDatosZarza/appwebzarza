<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Exception;
use PDO;

class CouponsController
{
    private function getConnection()
    {
        return new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    private function getCurrentUser()
    {
        if (!Session::get('user_authenticated', false)) {
            return null;
        }
        
        return (object) [
            'id' => Session::get('user_id'),  
            'email' => Session::get('user_email'),
            'nombres' => Session::get('user_nombres'),
            'apellido_paterno' => Session::get('user_apellido_paterno'),
            'rol' => Session::get('user_rol')
        ];
    }

    // ========== ADMINISTRACIÓN DE CUPONES ==========

    /**
     * Lista de cupones para administradores
     */
    public function index()
    {
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Obtener todos los cupones con estadísticas
            $stmt = $pdo->query("
                SELECT 
                    c.*,
                    COUNT(ca.id) as total_asignados,
                    COUNT(CASE WHEN ca.estado = 'pendiente' THEN 1 END) as disponibles,
                    COUNT(CASE WHEN ca.estado = 'redimido' THEN 1 END) as usados,
                    CASE 
                        WHEN c.fecha_fin < CURRENT_DATE THEN 'vencido'
                        WHEN c.fecha_inicio > CURRENT_DATE THEN 'futuro'
                        ELSE 'vigente'
                    END as estado_vigencia
                FROM cupones c
                LEFT JOIN cupones_asignados ca ON c.id = ca.cupon_id
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ");
            
            $cupones = $stmt->fetchAll();

            // Obtener lista de clientes para asignación
            $stmt = $pdo->query("
                SELECT 
                    u.id,
                    u.nombres || ' ' || u.apellido_paterno || COALESCE(' ' || u.apellido_materno, '') as nombre_completo,
                    u.email,
                    COALESCE(p.saldo, 0) as puntos
                FROM usuarios u
                LEFT JOIN puntos p ON u.id = p.usuario_id
                WHERE u.rol = 'cliente'
                ORDER BY u.nombres, u.apellido_paterno
            ");
            
            $clientes = $stmt->fetchAll();

            return view('admin.coupons.index', compact('cupones', 'clientes'));
            
        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar cupones: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario para crear cupón
     */
    public function create()
    {
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        return view('admin.coupons.create');
    }

    /**
     * Guardar nuevo cupón
     */
    public function store(Request $request)
    {
        // Verificar autenticación primero
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        // Validaciones de Laravel
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'puntos_requeridos' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ], [
            'nombre.required' => 'El nombre del cupón es obligatorio',
            'descripcion.required' => 'La descripción es obligatoria',
            'puntos_requeridos.required' => 'Los puntos requeridos son obligatorios',
            'puntos_requeridos.min' => 'Los puntos requeridos deben ser al menos 1',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Obtener datos validados
            $nombre = trim($request->input('nombre'));
            $descripcion = trim($request->input('descripcion'));
            $puntos_requeridos = (int) $request->input('puntos_requeridos');
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_fin = $request->input('fecha_fin');
            $activo = $request->has('activo');

            $pdo->beginTransaction();

            // Insertar cupón
            $stmt = $pdo->prepare("
                INSERT INTO cupones (nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $nombre,
                $descripcion, 
                $puntos_requeridos,
                $fecha_inicio,
                $fecha_fin,
                $activo,
                $user->id
            ]);

            $cupon_id = $pdo->lastInsertId();

            // Registrar en auditoría
            $stmt = $pdo->prepare("
                INSERT INTO auditoria (tabla, registro_id, accion, usuario_id, cambios, fecha)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                'cupones',
                $cupon_id,
                'create',
                $user->id,
                json_encode([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'puntos_requeridos' => $puntos_requeridos,
                    'activo' => $activo
                ])
            ]);

            $pdo->commit();

            return redirect()->route('admin.coupons.index')->with('success', 'Cupón "' . $nombre . '" creado exitosamente');

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return back()->with('error', 'Error al crear cupón: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mostrar formulario para editar cupón
     */
    public function edit($id)
    {
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            $stmt = $pdo->prepare("SELECT * FROM cupones WHERE id = ?");
            $stmt->execute([$id]);
            $cupon = $stmt->fetch();

            if (!$cupon) {
                return redirect()->route('admin.coupons.index')->with('error', 'Cupón no encontrado');
            }

            return view('admin.coupons.edit', compact('cupon'));

        } catch (Exception $e) {
            return redirect()->route('admin.coupons.index')->with('error', 'Error al cargar cupón: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar cupón
     */
    public function update(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Obtener datos actuales para auditoría
            $stmt = $pdo->prepare("SELECT * FROM cupones WHERE id = ?");
            $stmt->execute([$id]);
            $cupon_actual = $stmt->fetch();

            if (!$cupon_actual) {
                return redirect()->route('admin.coupons.index')->with('error', 'Cupón no encontrado');
            }

            // Validar datos
            $nombre = trim($request->input('nombre'));
            $descripcion = trim($request->input('descripcion'));
            $puntos_requeridos = (int) $request->input('puntos_requeridos');
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_fin = $request->input('fecha_fin');
            $activo = $request->has('activo');

            if (empty($nombre) || empty($descripcion) || $puntos_requeridos <= 0) {
                return back()->with('error', 'Todos los campos son obligatorios')->withInput();
            }

            if ($fecha_fin <= $fecha_inicio) {
                return back()->with('error', 'La fecha de fin debe ser posterior a la fecha de inicio')->withInput();
            }

            $pdo->beginTransaction();

            // Actualizar cupón
            $stmt = $pdo->prepare("
                UPDATE cupones 
                SET nombre = ?, descripcion = ?, puntos_requeridos = ?, fecha_inicio = ?, fecha_fin = ?, activo = ?, actualizado_por = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $nombre,
                $descripcion,
                $puntos_requeridos,
                $fecha_inicio,
                $fecha_fin,
                $activo,
                $user->id,
                $id
            ]);

            // Registrar en auditoría
            $stmt = $pdo->prepare("
                INSERT INTO auditoria (tabla, registro_id, accion, usuario_id, cambios, fecha)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $cambios = [
                'anterior' => $cupon_actual,
                'nuevo' => [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'puntos_requeridos' => $puntos_requeridos,
                    'activo' => $activo
                ]
            ];
            
            $stmt->execute([
                'cupones',
                $id,
                'update',
                $user->id,
                json_encode($cambios)
            ]);

            $pdo->commit();

            return redirect()->route('admin.coupons.index')->with('success', 'Cupón actualizado exitosamente');

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return back()->with('error', 'Error al actualizar cupón: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Eliminar cupón
     */
    public function destroy($id)
    {
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Obtener datos actuales para auditoría
            $stmt = $pdo->prepare("SELECT * FROM cupones WHERE id = ?");
            $stmt->execute([$id]);
            $cupon = $stmt->fetch();

            if (!$cupon) {
                return back()->with('error', 'Cupón no encontrado');
            }

            // Verificar si el cupón tiene asignaciones
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cupones_asignados WHERE cupon_id = ?");
            $stmt->execute([$id]);
            $asignaciones = $stmt->fetch();

            if ($asignaciones['total'] > 0) {
                return back()->with('error', 'No se puede eliminar un cupón que ya tiene asignaciones');
            }

            $pdo->beginTransaction();

            // Eliminar cupón
            $stmt = $pdo->prepare("DELETE FROM cupones WHERE id = ?");
            $stmt->execute([$id]);

            // Registrar en auditoría
            $stmt = $pdo->prepare("
                INSERT INTO auditoria (tabla, registro_id, accion, usuario_id, cambios, fecha)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                'cupones',
                $id,
                'delete',
                $user->id,
                json_encode(['cupon_eliminado' => $cupon])
            ]);

            $pdo->commit();

            return back()->with('success', 'Cupón eliminado exitosamente');

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return back()->with('error', 'Error al eliminar cupón: ' . $e->getMessage());
        }
    }

    /**
     * Asignar cupón a usuario
     */
    public function assign(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            $usuario_id = (int) $request->input('usuario_id');
            if (!$usuario_id) {
                return back()->with('error', 'Debe seleccionar un usuario');
            }

            // Verificar que el cupón existe y está activo
            $stmt = $pdo->prepare("SELECT * FROM cupones WHERE id = ? AND activo = true");
            $stmt->execute([$id]);
            $cupon = $stmt->fetch();

            if (!$cupon) {
                return back()->with('error', 'Cupón no encontrado o inactivo');
            }

            // Verificar que el usuario existe
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado');
            }

            // Verificar que el usuario tiene suficientes puntos
            $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            $puntos = $stmt->fetch();

            if (!$puntos || $puntos['saldo'] < $cupon['puntos_requeridos']) {
                return back()->with('error', 'El usuario no tiene puntos suficientes');
            }

            $pdo->beginTransaction();

            // Generar código QR único
            $codigo_qr = 'ZP' . strtoupper(bin2hex(random_bytes(6)));

            // Asignar cupón
            $stmt = $pdo->prepare("
                INSERT INTO cupones_asignados (usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at)
                VALUES (?, ?, 'pendiente', ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([$usuario_id, $id, $codigo_qr, $user->id]);

            // Descontar puntos
            $stmt = $pdo->prepare("UPDATE puntos SET saldo = saldo - ? WHERE usuario_id = ?");
            $stmt->execute([$cupon['puntos_requeridos'], $usuario_id]);

            // Registrar transacción de puntos
            $stmt = $pdo->prepare("
                INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, registrado_por, created_at)
                VALUES (?, 'canje', ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $usuario_id,
                $cupon['puntos_requeridos'],
                'Canje por cupón: ' . $cupon['nombre'],
                $user->id
            ]);

            $pdo->commit();

            return back()->with('success', 'Cupón asignado exitosamente');

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return back()->with('error', 'Error al asignar cupón: ' . $e->getMessage());
        }
    }

    /**
     * Obtener asignaciones de un cupón específico
     */
    public function getAssignments($id)
    {
        $user = $this->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Obtener asignaciones del cupón
            $stmt = $pdo->prepare("
                SELECT 
                    ca.*,
                    u.nombres || ' ' || u.apellido_paterno || COALESCE(' ' || u.apellido_materno, '') as nombre_cliente,
                    u.email,
                    s.nombre as sucursal_redencion,
                    r.fecha_redencion
                FROM cupones_asignados ca
                INNER JOIN usuarios u ON ca.usuario_id = u.id
                LEFT JOIN redenciones r ON ca.id = r.cupon_asignado_id
                LEFT JOIN sucursales s ON r.sucursal_id = s.id
                WHERE ca.cupon_id = ?
                ORDER BY ca.created_at DESC
            ");
            $stmt->execute([$id]);
            $asignaciones = $stmt->fetchAll();

            return response()->json(['assignments' => $asignaciones]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========== ÁREA DE CLIENTES ==========

    /**
     * Lista de cupones disponibles para clientes
     */
    public function myCoupons()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect('/login')->with('error', 'Debes iniciar sesión');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Obtener puntos actuales del usuario
            $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
            $stmt->execute([$user->id]);
            $puntos = $stmt->fetch();
            $saldo_puntos = $puntos ? $puntos['saldo'] : 0;

            // Obtener cupones disponibles con verificación de si ya fueron canjeados
            $stmt = $pdo->prepare("
                SELECT 
                    c.*,
                    CASE 
                        WHEN ca.id IS NOT NULL THEN true
                        ELSE false
                    END as ya_canjeado
                FROM cupones c
                LEFT JOIN cupones_asignados ca ON c.id = ca.cupon_id AND ca.usuario_id = ?
                WHERE c.activo = true 
                AND c.fecha_inicio <= CURRENT_DATE 
                AND c.fecha_fin >= CURRENT_DATE
                ORDER BY c.puntos_requeridos ASC
            ");
            $stmt->execute([$user->id]);
            $cupones_disponibles = $stmt->fetchAll();

            // Obtener cupones del usuario
            $stmt = $pdo->prepare("
                SELECT 
                    ca.*,
                    c.nombre,
                    c.codigo,
                    c.descripcion,
                    c.puntos_requeridos,
                    CASE 
                        WHEN ca.estado = 'asignado' THEN 'disponible'
                        WHEN ca.estado = 'usado' THEN 'usado'
                        WHEN ca.estado = 'bloqueado' THEN 'bloqueado'
                        WHEN ca.estado = 'vencido' THEN 'vencido'
                        ELSE 'otro'
                    END as estado_display
                FROM cupones_asignados ca
                INNER JOIN cupones c ON ca.cupon_id = c.id
                WHERE ca.usuario_id = ?
                ORDER BY ca.created_at DESC
            ");
            $stmt->execute([$user->id]);
            $mis_cupones = $stmt->fetchAll();

            return view('client.coupons.index', compact('cupones_disponibles', 'mis_cupones', 'saldo_puntos'));

        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar cupones: ' . $e->getMessage());
        }
    }

    /**
     * Canjear cupón por puntos
     */
    public function redeem($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Debes iniciar sesión'], 401);
            }
            return redirect('/login')->with('error', 'Debes iniciar sesión');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Verificar que el cupón existe y está disponible
            $stmt = $pdo->prepare("
                SELECT * FROM cupones 
                WHERE id = ? AND activo = true 
                AND fecha_inicio <= CURRENT_DATE 
                AND fecha_fin >= CURRENT_DATE
            ");
            $stmt->execute([$id]);
            $cupon = $stmt->fetch();

            if (!$cupon) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cupón no disponible'], 400);
                }
                return back()->with('error', 'Cupón no disponible');
            }

            // Verificar que el usuario tiene suficientes puntos
            $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
            $stmt->execute([$user->id]);
            $puntos = $stmt->fetch();

            if (!$puntos || $puntos['saldo'] < $cupon['puntos_requeridos']) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'No tienes puntos suficientes para este cupón'], 400);
                }
                return back()->with('error', 'No tienes puntos suficientes para este cupón');
            }

            // Verificar que no ha canjeado este cupón antes
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cupones_asignados WHERE usuario_id = ? AND cupon_id = ?");
            $stmt->execute([$user->id, $id]);
            $ya_canjeado = $stmt->fetch();

            if ($ya_canjeado['total'] > 0) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Ya has canjeado este cupón anteriormente'], 400);
                }
                return back()->with('error', 'Ya has canjeado este cupón anteriormente');
            }

            $pdo->beginTransaction();

            // Generar código QR único basado en el código del cupón
            // Formato: CODIGOCUPON-XXXXX (ej: BANDERILLAS20-A3F9B)
            $codigo_qr = $cupon['codigo'] . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));

            // Asignar cupón con estado 'asignado'
            $stmt = $pdo->prepare("
                INSERT INTO cupones_asignados (usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at)
                VALUES (?, ?, 'asignado', ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([$user->id, $id, $codigo_qr, $user->id]);

            // Descontar puntos
            $stmt = $pdo->prepare("UPDATE puntos SET saldo = saldo - ? WHERE usuario_id = ?");
            $stmt->execute([$cupon['puntos_requeridos'], $user->id]);

            // Obtener nuevo saldo
            $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
            $stmt->execute([$user->id]);
            $nuevo_saldo = $stmt->fetch()['saldo'];

            // Registrar transacción de puntos
            $stmt = $pdo->prepare("
                INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, registrado_por, created_at)
                VALUES (?, 'canje', ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $user->id,
                $cupon['puntos_requeridos'],
                'Canje por cupón: ' . $cupon['nombre'],
                $user->id
            ]);

            $pdo->commit();

            // Si es petición AJAX, devolver JSON
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '¡Cupón canjeado exitosamente!',
                    'cupon' => [
                        'id' => $cupon['id'],
                        'nombre' => $cupon['nombre'],
                        'codigo' => $cupon['codigo'],
                        'descripcion' => $cupon['descripcion'],
                        'puntos_requeridos' => (int)$cupon['puntos_requeridos'],
                        'codigo_qr' => $codigo_qr
                    ],
                    'nuevo_saldo' => (int)$nuevo_saldo
                ]);
            }

            // Redirigir a la vista del cupón canjeado con el ID de asignación
            $stmt = $pdo->prepare("SELECT id FROM cupones_asignados WHERE codigo_qr = ?");
            $stmt->execute([$codigo_qr]);
            $asignacion = $stmt->fetch();
            
            if ($asignacion) {
                return redirect()->route('coupons.show', $asignacion['id'])
                    ->with('success', '¡Felicidades! Has canjeado tu cupón exitosamente. Aquí tienes tu código QR:')
                    ->with('nuevo_canje', true);
            }

            return back()->with('success', 'Cupón canjeado exitosamente. Código: ' . $codigo_qr);

        } catch (Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al canjear cupón: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al canjear cupón: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles del cupón
     */
    public function show($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect('/login')->with('error', 'Debes iniciar sesión');
        }

        try {
            $pdo = $this->getConnection();
            $pdo->exec('SET search_path TO appweb, public');

            // Obtener cupón asignado
            $stmt = $pdo->prepare("
                SELECT 
                    ca.*,
                    c.nombre,
                    c.codigo,
                    c.descripcion,
                    c.puntos_requeridos,
                    c.fecha_inicio,
                    c.fecha_fin
                FROM cupones_asignados ca
                INNER JOIN cupones c ON ca.cupon_id = c.id
                WHERE ca.id = ? AND ca.usuario_id = ?
            ");
            $stmt->execute([$id, $user->id]);
            $cupon = $stmt->fetch();

            if (!$cupon) {
                return redirect()->route('coupons.index')->with('error', 'Cupón no encontrado');
            }

            return view('client.coupons.show', compact('cupon'));

        } catch (Exception $e) {
            return redirect()->route('coupons.index')->with('error', 'Error al cargar cupón: ' . $e->getMessage());
        }
    }
}