<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Cupon;
use App\Models\CuponAsignado;
use App\Models\Compra;
use App\Models\TransaccionPuntos;
use App\Models\Puntos;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal
     */
    public function index()
    {
        // Verificar autenticación usando sesiones de Laravel
        if (!Session::get('user_authenticated', false)) {
            return view('dashboard-simple', ['isAuthenticated' => false]); // Vista pública
        }
        
        // Si es admin, redirigir al panel de administración
        if (Session::get('user_rol') === 'admin') {
            return redirect('/admin/points');
        }
        
        // Dashboard para clientes autenticados
        return $this->clientDashboard();
    }
    
    /**
     * Dashboard específico para clientes
     */
    private function clientDashboard()
    {
        try {
            // Obtener datos básicos de la sesión
            $userData = [
                'id' => Session::get('user_id'),
                'nombre' => Session::get('user_nombre'),
                'email' => Session::get('user_email'),
                'puntos' => Session::get('user_puntos', 0)
            ];
            
            // Datos por defecto si no podemos conectar a BD
            $comprasData = ['total_compras' => 0, 'total_gastado' => 0];
            $cuponesDisponibles = 0;
            $misCupones = 0;
            $transaccionesRecientes = [];
            
            try {
                $userId = Session::get('user_id');
                
                // Actualizar puntos actuales desde BD usando Eloquent
                $puntos = Puntos::where('usuario_id', $userId)->first();
                $userData['puntos'] = $puntos ? $puntos->saldo : 0;
                
                // Obtener compras del usuario
                $comprasData = Compra::where('usuario_id', $userId)
                    ->selectRaw('COUNT(*) as total_compras, COALESCE(SUM(monto), 0) as total_gastado')
                    ->first()
                    ->toArray();
                
                // Obtener cupones disponibles
                $cuponesDisponibles = Cupon::where('activo', true)
                    ->whereDate('fecha_inicio', '<=', now())
                    ->whereDate('fecha_fin', '>=', now())
                    ->count();
                
                // Obtener cupones del usuario
                $misCupones = CuponAsignado::join('cupones', 'cupones_asignados.cupon_id', '=', 'cupones.id')
                    ->where('cupones_asignados.usuario_id', $userId)
                    ->where('cupones_asignados.estado', 'asignado')
                    ->count();
                
                // Obtener transacciones recientes
                $transaccionesRecientes = TransaccionPuntos::where('usuario_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['tipo', 'puntos', 'descripcion', 'created_at'])
                    ->toArray();
                
            } catch (\Exception $dbError) {
                // Si hay error de BD, usar valores por defecto
                error_log("Error BD en dashboard: " . $dbError->getMessage());
            }
            
            return view('dashboard-simple', [
                'userData' => $userData,
                'comprasData' => $comprasData,
                'cuponesDisponibles' => $cuponesDisponibles,
                'misCupones' => $misCupones,
                'transaccionesRecientes' => $transaccionesRecientes,
                'isAuthenticated' => true
            ]);
            
        } catch (\Exception $e) {
            // Fallback básico
            return response()->view('dashboard', [
                'error' => 'Error al cargar dashboard: ' . $e->getMessage(),
                'isAuthenticated' => false
            ], 500);
        }
    }

    /**
     * Mostrar cupones disponibles
     */
    public function coupons()
    {
        return view('coupons.index');
    }

    /**
     * Canjear un cupón
     */
    public function redeemCoupon(Request $request)
    {
        $request->validate([
            'cupon_id' => 'required|exists:cupones,id',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para canjear cupones');
        }

        DB::beginTransaction();

        try {
            $usuario = Auth::user();
            $cupon = Cupon::findOrFail($request->cupon_id);

            // Validaciones
            if (!$cupon->activo) {
                return back()->with('error', 'El cupón no está activo');
            }

            if ($cupon->fecha_vencimiento < now()) {
                return back()->with('error', 'El cupón ha vencido');
            }

            if ($cupon->cantidad_disponible <= 0) {
                return back()->with('error', 'No hay cupones disponibles');
            }

            $puntosUsuario = $usuario->puntos ? $usuario->puntos->saldo : 0;
            
            if ($puntosUsuario < $cupon->puntos_requeridos) {
                return back()->with('error', 'Puntos insuficientes para canjear este cupón');
            }

            // Verificar si ya canjeó este cupón
            $yaCanjeado = CuponAsignado::where('usuario_id', $usuario->id)
                                     ->where('cupon_id', $cupon->id)
                                     ->exists();

            if ($yaCanjeado && !$cupon->multiple_uso) {
                return back()->with('error', 'Ya has canjeado este cupón anteriormente');
            }

            // Descontar puntos
            $usuario->puntos->update([
                'saldo' => $usuario->puntos->saldo - $cupon->puntos_requeridos,
                'puntos_utilizados' => $usuario->puntos->puntos_utilizados + $cupon->puntos_requeridos,
                'ultima_actualizacion' => now(),
            ]);

            // Asignar cupón al usuario
            $cuponAsignado = CuponAsignado::create([
                'usuario_id' => $usuario->id,
                'cupon_id' => $cupon->id,
                'codigo_qr' => 'QR_' . strtoupper(uniqid()),
                'estado' => 'asignado',
                'fecha_asignacion' => now(),
                'fecha_vencimiento' => $cupon->fecha_vencimiento,
            ]);

            // Reducir cantidad disponible
            $cupon->decrement('cantidad_disponible');

            // Registrar en auditoría
            DB::table('auditoria')->insert([
                'tabla' => 'cupones_asignados',
                'registro_id' => $cuponAsignado->id,
                'accion' => 'create',
                'usuario_id' => $usuario->id,
                'cambios' => json_encode([
                    'cupon_asignado' => $cuponAsignado->toArray(),
                    'direccion_ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]),
                'fecha' => now(),
            ]);

            DB::commit();

            return back()->with('success', '¡Cupón canjeado exitosamente! Revisa tu código QR en "Mis Cupones"');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error al canjear cupón: ' . $e->getMessage());
        }
    }

    /**
     * Asignar cupón con gamificación (puede estar bloqueado si no tiene puntos suficientes)
     */
    public function assignCouponWithGamification(Request $request)
    {
        $request->validate([
            'cupon_id' => 'required|exists:cupones,id',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para obtener cupones');
        }

        DB::beginTransaction();

        try {
            $usuario = Auth::user();
            $cupon = Cupon::findOrFail($request->cupon_id);

            // Validaciones básicas
            if (!$cupon->activo) {
                return back()->with('error', 'El cupón no está activo');
            }

            if ($cupon->fecha_vencimiento < now()) {
                return back()->with('error', 'El cupón ha vencido');
            }

            if ($cupon->cantidad_disponible <= 0) {
                return back()->with('error', 'No hay cupones disponibles');
            }

            // Verificar si ya tiene este cupón asignado
            $yaAsignado = CuponAsignado::where('usuario_id', $usuario->id)
                                     ->where('cupon_id', $cupon->id)
                                     ->exists();

            if ($yaAsignado) {
                return back()->with('error', 'Ya tienes este cupón en tu colección');
            }

            $puntosUsuario = $usuario->puntos ? $usuario->puntos->saldo : 0;
            $tienePuntosSuficientes = $puntosUsuario >= $cupon->puntos_requeridos;
            
            // Determinar el estado inicial del cupón
            $estadoInicial = $tienePuntosSuficientes ? 'pendiente' : 'bloqueado';
            
            // Asignar cupón al usuario (sin descontar puntos aún)
            $cuponAsignado = CuponAsignado::create([
                'usuario_id' => $usuario->id,
                'cupon_id' => $cupon->id,
                'codigo_qr' => 'QR_' . strtoupper(uniqid()),
                'estado' => $estadoInicial,
                'asignado_por' => 1, // ID del sistema/admin
            ]);

            // Solo reducir cantidad disponible si se puede usar inmediatamente
            if ($tienePuntosSuficientes) {
                $cupon->decrement('cantidad_disponible');
            }

            // Registrar en auditoría
            DB::table('auditoria')->insert([
                'tabla' => 'cupones_asignados',
                'registro_id' => $cuponAsignado->id,
                'accion' => 'create',
                'usuario_id' => $usuario->id,
                'cambios' => json_encode([
                    'cupon_asignado' => $cuponAsignado->toArray(),
                    'estado_inicial' => $estadoInicial,
                    'puntos_usuario' => $puntosUsuario,
                    'puntos_requeridos' => $cupon->puntos_requeridos,
                    'direccion_ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]),
                'fecha' => now(),
            ]);

            DB::commit();

            if ($tienePuntosSuficientes) {
                return back()->with('success', '¡Cupón añadido a tu colección! Ya puedes usarlo cuando quieras.');
            } else {
                $puntosNecesarios = $cupon->puntos_requeridos - $puntosUsuario;
                return back()->with('info', "¡Cupón añadido a tu colección! 🎯 Necesitas $puntosNecesarios puntos más para desbloquearlo. ¡Sigue comprando para alcanzar tu meta!");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error al asignar cupón: ' . $e->getMessage());
        }
    }

    /**
     * Verificar y desbloquear cupones cuando el usuario alcance los puntos necesarios
     * Este método se debe llamar después de cada transacción que agregue puntos
     */
    public function checkAndUnlockCoupons($userId = null)
    {
        try {
            // Si no se proporciona userId, usar el usuario autenticado
            if (!$userId) {
                $usuario = Auth::user();
                if (!$usuario) return 0;
                $userId = $usuario->id;
            }
            
            // Obtener los puntos del usuario directamente de la base de datos
            $stmt = DB::select("SELECT saldo FROM puntos WHERE usuario_id = ?", [$userId]);
            $puntosUsuario = $stmt ? $stmt[0]->saldo : 0;
            
            // Buscar cupones bloqueados que ahora se pueden desbloquear
            $cuponesBloqueados = DB::select("
                SELECT ca.*, c.puntos_requeridos, c.nombre
                FROM cupones_asignados ca
                JOIN cupones c ON ca.cupon_id = c.id
                WHERE ca.usuario_id = ? 
                AND ca.estado = 'bloqueado'
                AND c.puntos_requeridos <= ?
                AND c.activo = true
                AND c.fecha_vencimiento > NOW()
            ", [$userId, $puntosUsuario]);

            $desbloqueados = 0;
            foreach ($cuponesBloqueados as $cuponBloqueado) {
                // Desbloquear el cupón
                DB::table('cupones_asignados')
                    ->where('id', $cuponBloqueado->id)
                    ->update([
                        'estado' => 'pendiente',
                        'updated_at' => now()
                    ]);

                // Reducir cantidad disponible del cupón original
                DB::table('cupones')
                    ->where('id', $cuponBloqueado->cupon_id)
                    ->decrement('cantidad_disponible');

                // Registrar en auditoría
                DB::table('auditoria')->insert([
                    'tabla' => 'cupones_asignados',
                    'registro_id' => $cuponBloqueado->id,
                    'accion' => 'update',
                    'usuario_id' => $userId,
                    'cambios' => json_encode([
                        'estado_anterior' => 'bloqueado',
                        'estado_nuevo' => 'pendiente',
                        'puntos_usuario' => $puntosUsuario,
                        'puntos_requeridos' => $cuponBloqueado->puntos_requeridos,
                        'auto_desbloqueado' => true
                    ]),
                    'fecha' => now(),
                ]);

                $desbloqueados++;
            }

            return $desbloqueados;

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Endpoint público para verificar y desbloquear cupones del usuario autenticado
     */
    public function unlockMyBlockedCoupons()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión');
        }

        $desbloqueados = $this->checkAndUnlockCoupons(Auth::id());
        
        if ($desbloqueados > 0) {
            return back()->with('success', "¡Genial! Se desbloquearon $desbloqueados cupones. Ya puedes usarlos 🎉");
        } else {
            return back()->with('info', 'No tienes cupones listos para desbloquear en este momento. ¡Sigue ganando puntos! 💪');
        }
    }

    /**
     * Mostrar cupones del usuario
     */
    public function myCoupons()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $cupones = Auth::user()->cuponesAsignados()
                             ->with('cupon')
                             ->orderBy('fecha_asignacion', 'desc')
                             ->get();

        return view('coupons.my', compact('cupones'));
    }

    /**
     * Mostrar compras del usuario
     */
    public function purchases()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver tus compras.');
        }

    /** @var Usuario $usuario */
    $usuario = Auth::user();

        $comprasQuery = $usuario->compras()->orderByDesc('fecha_compra');

        $compras = (clone $comprasQuery)
            ->with('sucursal')
            ->paginate(10);

        $stats = [
            'total_compras' => (clone $comprasQuery)->count(),
            'total_monto' => (clone $comprasQuery)->sum('monto'),
            'total_puntos' => (clone $comprasQuery)->sum('puntos_generados'),
        ];

        return view('purchases.index', compact('compras', 'stats'));
    }

    /**
     * Mostrar historial de puntos
     */
    public function pointsHistory()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

    /** @var Usuario $usuario */
    $usuario = Auth::user();

        $usuario->loadMissing('puntos');

    $transacciones = $usuario->transaccionesPuntos()
                 ->with('registradoPor')
                 ->orderByDesc('created_at')
                 ->paginate(15);

        $saldoActual = optional($usuario->puntos)->saldo ?? 0;

        return view('points.history', compact('transacciones', 'saldoActual'));
    }

    /**
     * Mostrar perfil del usuario
     */
    public function profile()
    {
        if (!Session::get('user_authenticated', false)) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder a tu perfil.');
        }

        try {
            $userId = Session::get('user_id');
            
            // Obtener datos del usuario usando Eloquent con relaciones
            $usuario = Usuario::with('puntos')->find($userId);
            
            if (!$usuario) {
                return redirect()->route('login')->with('error', 'Usuario no encontrado.');
            }
            
            // Preparar datos para la vista
            $userData = $usuario->toArray();
            $userData['puntos_saldo'] = $usuario->puntos ? $usuario->puntos->saldo : 0;
            
            return view('profile.show', ['user' => $userData]);
            
        } catch (\Exception $e) {
            Log::error('Error al cargar perfil de usuario', [
                'user_id' => Session::get('user_id'),
                'exception' => $e->getMessage(),
            ]);
            
            return redirect()->route('dashboard')->with('error', 'Error al cargar el perfil. Inténtalo más tarde.');
        }
    }

    /**
     * Actualizar perfil del usuario
     */
    public function updateProfile(Request $request)
    {
        if (!Session::get('user_authenticated', false)) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para actualizar tu perfil.');
        }

        // Validación de datos
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'email' => 'required|email|max:255',
            'password' => 'nullable|min:6|confirmed',
        ]);

        try {
            $userId = Session::get('user_id');
            
            // Verificar si el email ya existe (para otro usuario)
            $emailExists = Usuario::where('email', $request->email)
                ->where('id', '!=', $userId)
                ->exists();
                
            if ($emailExists) {
                return back()->withErrors(['email' => 'Este email ya está en uso por otro usuario.'])->withInput();
            }
            
            DB::beginTransaction();
            
            // Obtener usuario
            $usuario = Usuario::findOrFail($userId);
            
            // Preparar datos para actualizar
            $updateData = [
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento
            ];
            
            // Si se proporcionó una nueva contraseña, incluirla
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
            
            // Actualizar usuario
            $usuario->update($updateData);
            
            // Registrar en auditoría
            Auditoria::create([
                'tabla' => 'usuarios',
                'registro_id' => $userId,
                'accion' => 'update',
                'usuario_id' => $userId,
                'cambios' => json_encode([
                    'campo_actualizado' => 'perfil_usuario',
                    'datos_modificados' => array_keys($updateData)
                ])
            ]);
            
            DB::commit();
            
            // Actualizar sesión si cambió el email o nombre
            Session::put('user_email', $request->email);
            Session::put('user_nombre', $request->nombres);
            Session::put('user_apellido', $request->apellido_paterno);
            
            return redirect()->route('profile.show')->with('success', '✅ Perfil actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar perfil de usuario', [
                'user_id' => Session::get('user_id'),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', '❌ Error al actualizar perfil. Por favor, inténtalo más tarde.')->withInput();
        }
    }

    /**
     * Mostrar todas las transacciones de puntos (Solo para administradores)
     */
    public function showTransactions(Request $request)
    {
        // Verificar si el usuario es administrador
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        try {
            // Datos estáticos por ahora para probar
            $transacciones = [
                [
                    'id' => 1,
                    'usuario_nombre' => 'Juan Pérez',
                    'usuario_email' => 'juan@test.com',
                    'tipo' => 'compra',
                    'tipo_descripcion' => '🛒 Compra',
                    'tipo_movimiento' => 'positivo',
                    'puntos' => 100,
                    'descripcion' => 'Compra en sucursal',
                    'registrado_por_nombre' => 'Sistema',
                    'registrado_por_email' => 'sistema@test.com',
                    'created_at' => '2025-10-10 10:00:00'
                ]
            ];

            $estadisticas = [
                'total_transacciones' => 1,
                'puntos_generados' => 100,
                'puntos_utilizados' => 0,
                'saldo_neto' => 100,
                'total_compras' => 1,
                'total_canjes' => 0,
                'total_ajustes' => 0
            ];

            $filtros = [
                'tipo' => $request->get('tipo', ''),
                'usuario' => $request->get('usuario', ''),
                'fecha_desde' => $request->get('fecha_desde', ''),
                'fecha_hasta' => $request->get('fecha_hasta', ''),
            ];

            $paginacion = [
                'current_page' => 1,
                'total_pages' => 1,
                'total_records' => 1,
                'per_page' => 50,
                'has_prev' => false,
                'has_next' => false
            ];

            return view('admin.transactions.index', compact('transacciones', 'estadisticas', 'filtros', 'paginacion'));

        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('error', 'Error al cargar las transacciones: ' . $e->getMessage());
        }
    }

    /**
     * Exportar transacciones a CSV (Solo para administradores)
     */
    public function exportTransactions(Request $request)
    {
        // Verificar si el usuario es administrador
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para exportar transacciones');
        }

        try {
            // Aplicar los mismos filtros que en la vista
            $filtros = [
                'tipo' => $request->get('tipo', ''),
                'usuario' => $request->get('usuario', ''),
                'fecha_desde' => $request->get('fecha_desde', ''),
                'fecha_hasta' => $request->get('fecha_hasta', ''),
            ];

            // Construir query usando Query Builder
            $query = TransaccionPuntos::select(
                    'transacciones_puntos.id',
                    DB::raw("usuarios.nombres || ' ' || usuarios.apellido_paterno as usuario"),
                    'usuarios.email',
                    'transacciones_puntos.tipo',
                    'transacciones_puntos.puntos',
                    'transacciones_puntos.descripcion',
                    DB::raw("ur.nombres || ' ' || ur.apellido_paterno as registrado_por"),
                    DB::raw('transacciones_puntos.created_at::date as fecha'),
                    DB::raw('transacciones_puntos.created_at::time as hora')
                )
                ->leftJoin('usuarios', 'transacciones_puntos.usuario_id', '=', 'usuarios.id')
                ->leftJoin('usuarios as ur', 'transacciones_puntos.registrado_por', '=', 'ur.id');

            if (!empty($filtros['tipo'])) {
                $query->where('transacciones_puntos.tipo', $filtros['tipo']);
            }

            if (!empty($filtros['usuario'])) {
                $searchPattern = '%' . $filtros['usuario'] . '%';
                $query->where(function($q) use ($searchPattern) {
                    $q->where('usuarios.nombres', 'ILIKE', $searchPattern)
                      ->orWhere('usuarios.apellido_paterno', 'ILIKE', $searchPattern)
                      ->orWhere('usuarios.email', 'ILIKE', $searchPattern);
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->where('transacciones_puntos.created_at', '>=', $filtros['fecha_desde'] . ' 00:00:00');
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->where('transacciones_puntos.created_at', '<=', $filtros['fecha_hasta'] . ' 23:59:59');
            }

            $transacciones = $query->orderBy('transacciones_puntos.created_at', 'desc')->get()->toArray();

            // Configurar headers para descarga CSV
            $filename = 'transacciones_puntos_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            // Abrir output stream
            $output = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers del CSV
            fputcsv($output, [
                'ID',
                'Usuario',
                'Email',
                'Tipo',
                'Puntos',
                'Descripción',
                'Registrado Por',
                'Fecha',
                'Hora'
            ]);

            // Datos
            foreach ($transacciones as $transaccion) {
                fputcsv($output, [
                    $transaccion['id'],
                    $transaccion['usuario'],
                    $transaccion['email'],
                    ucfirst($transaccion['tipo']),
                    $transaccion['puntos'],
                    $transaccion['descripcion'],
                    $transaccion['registrado_por'] ?? 'Sistema',
                    $transaccion['fecha'],
                    $transaccion['hora']
                ]);
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            Log::error('Error al exportar transacciones', [
                'user_id' => Session::get('user_id'),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.transactions')->with('error', 'Error al exportar transacciones: ' . $e->getMessage());
        }
    }
}