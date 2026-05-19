<?php

namespace App\Http\Controllers\Web;

use App\Models\Cupon;
use App\Models\CuponAsignado;
use App\Models\Usuario;
use App\Models\Auditoria;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Exception;

class CouponsController
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ========== ADMINISTRACIÓN DE CUPONES ==========

    /**
     * Lista de cupones para administradores
     */
    public function index()
    {
        $user = $this->authService->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            // Obtener todos los cupones con estadísticas usando Eloquent
            $cupones = Cupon::leftJoin('cupones_asignados', 'cupones.id', '=', 'cupones_asignados.cupon_id')
                ->select('cupones.*')
                ->selectRaw('COUNT(cupones_asignados.id) as total_asignados')
                ->selectRaw("COUNT(CASE WHEN cupones_asignados.estado = 'pendiente' THEN 1 END) as disponibles")
                ->selectRaw("COUNT(CASE WHEN cupones_asignados.estado = 'redimido' THEN 1 END) as usados")
                ->selectRaw("
                    CASE 
                        WHEN cupones.fecha_fin < CURRENT_DATE THEN 'vencido'
                        WHEN cupones.fecha_inicio > CURRENT_DATE THEN 'futuro'
                        ELSE 'vigente'
                    END as estado_vigencia
                ")
                ->groupBy('cupones.id')
                ->orderBy('cupones.created_at', 'DESC')
                ->get()
                ->toArray();

            // Obtener lista de clientes para asignación
            $clientes = Usuario::select('usuarios.id', 'usuarios.email')
                ->selectRaw("usuarios.nombres || ' ' || usuarios.apellido_paterno || COALESCE(' ' || usuarios.apellido_materno, '') as nombre_completo")
                ->where('usuarios.rol', 'cliente')
                ->orderBy('usuarios.nombres')
                ->orderBy('usuarios.apellido_paterno')
                ->get()
                ->toArray();

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
        $user = $this->authService->getCurrentUser();
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
        $user = $this->authService->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        // Validaciones de Laravel
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ], [
            'nombre.required' => 'El nombre del cupón es obligatorio',
            'descripcion.required' => 'La descripción es obligatoria',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        try {
            // Obtener datos validados
            $nombre = trim($request->input('nombre'));
            $descripcion = trim($request->input('descripcion'));
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_fin = $request->input('fecha_fin');
            $activo = (bool) $request->has('activo');

            DB::beginTransaction();

            // Crear cupón usando Eloquent
            $cupon = Cupon::create([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'puntos_requeridos' => 0,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'activo' => $activo,
                'actualizado_por' => $user->id
            ]);

            // Registrar en auditoría
            Auditoria::create([
                'tabla' => 'cupones',
                'registro_id' => $cupon->id,
                'accion' => 'create',
                'usuario_id' => $user->id,
                'cambios' => json_encode([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'activo' => $activo
                ])
            ]);

            DB::commit();

            return redirect()->route('admin.coupons.index')->with('success', 'Cupón "' . $nombre . '" creado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear cupón: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mostrar formulario para editar cupón
     */
    public function edit($id)
    {
        $user = $this->authService->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            $cupon = Cupon::find($id);

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
        $user = $this->authService->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            // Obtener cupón actual
            $cupon = Cupon::find($id);

            if (!$cupon) {
                return redirect()->route('admin.coupons.index')->with('error', 'Cupón no encontrado');
            }

            // Guardar datos actuales para auditoría
            $cupon_actual = $cupon->toArray();

            // Validar datos
            $nombre = trim($request->input('nombre'));
            $descripcion = trim($request->input('descripcion'));
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_fin = $request->input('fecha_fin');
            $activo = (bool) $request->has('activo');

            if (empty($nombre) || empty($descripcion)) {
                return back()->with('error', 'Todos los campos son obligatorios')->withInput();
            }

            if ($fecha_fin <= $fecha_inicio) {
                return back()->with('error', 'La fecha de fin debe ser posterior a la fecha de inicio')->withInput();
            }

            DB::beginTransaction();

            // Actualizar cupón
            $cupon->update([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'activo' => $activo,
                'actualizado_por' => $user->id
            ]);

            // Registrar en auditoría
            Auditoria::create([
                'tabla' => 'cupones',
                'registro_id' => $id,
                'accion' => 'update',
                'usuario_id' => $user->id,
                'cambios' => json_encode([
                    'anterior' => $cupon_actual,
                    'nuevo' => [
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'activo' => $activo
                    ]
                ])
            ]);

            DB::commit();

            return redirect()->route('admin.coupons.index')->with('success', 'Cupón actualizado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar cupón: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Eliminar cupón
     */
    public function destroy($id)
    {
        $user = $this->authService->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            // Obtener cupón
            $cupon = Cupon::find($id);

            if (!$cupon) {
                return back()->with('error', 'Cupón no encontrado');
            }

            // Verificar si el cupón tiene asignaciones
            $total_asignaciones = CuponAsignado::where('cupon_id', $id)->count();

            if ($total_asignaciones > 0) {
                return back()->with('error', 'No se puede eliminar un cupón que ya tiene asignaciones');
            }

            DB::beginTransaction();

            // Guardar datos antes de eliminar
            $cupon_data = $cupon->toArray();

            // Eliminar cupón
            $cupon->delete();

            // Registrar en auditoría
            Auditoria::create([
                'tabla' => 'cupones',
                'registro_id' => $id,
                'accion' => 'delete',
                'usuario_id' => $user->id,
                'cambios' => json_encode(['cupon_eliminado' => $cupon_data])
            ]);

            DB::commit();

            return back()->with('success', 'Cupón eliminado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar cupón: ' . $e->getMessage());
        }
    }

    /**
     * Asignar cupón a usuario
     */
    public function assign(Request $request, $id)
    {
        $user = $this->authService->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return redirect('/login')->with('error', 'Acceso denegado');
        }

        try {
            $usuario_id = (int) $request->input('usuario_id');
            if (!$usuario_id) {
                return back()->with('error', 'Debe seleccionar un usuario');
            }

            // Verificar que el cupón existe y está activo
            $cupon = Cupon::where('id', $id)
                ->whereRaw('"activo" = true')
                ->first();

            if (!$cupon) {
                return back()->with('error', 'Cupón no encontrado o inactivo');
            }

            // Verificar que el usuario existe
            $usuario = Usuario::find($usuario_id);

            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado');
            }

            // Verificar que el usuario tiene suficientes puntos
            // (sistema de puntos deshabilitado: la asignación es libre)

            DB::beginTransaction();

            // Generar código QR único
            $codigo_qr = 'ZP' . strtoupper(bin2hex(random_bytes(6)));

            // Asignar cupón
            CuponAsignado::create([
                'usuario_id' => $usuario_id,
                'cupon_id' => $id,
                'estado' => 'pendiente',
                'codigo_qr' => $codigo_qr,
                'asignado_por' => $user->id
            ]);

            DB::commit();

            return back()->with('success', 'Cupón asignado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al asignar cupón: ' . $e->getMessage());
        }
    }

    /**
     * Obtener asignaciones de un cupón específico
     */
    public function getAssignments($id)
    {
        $user = $this->authService->getCurrentUser();
        if (!$user || $user->rol !== 'admin') {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        try {
            // Obtener asignaciones del cupón con datos relacionados
            $asignaciones = CuponAsignado::select(
                    'cupones_asignados.*',
                    DB::raw("usuarios.nombres || ' ' || usuarios.apellido_paterno || COALESCE(' ' || usuarios.apellido_materno, '') as nombre_cliente"),
                    'usuarios.email',
                    'sucursales.nombre as sucursal_redencion',
                    'redenciones.fecha_redencion'
                )
                ->join('usuarios', 'cupones_asignados.usuario_id', '=', 'usuarios.id')
                ->leftJoin('redenciones', 'cupones_asignados.id', '=', 'redenciones.cupon_asignado_id')
                ->leftJoin('sucursales', 'redenciones.sucursal_id', '=', 'sucursales.id')
                ->where('cupones_asignados.cupon_id', $id)
                ->orderBy('cupones_asignados.created_at', 'DESC')
                ->get()
                ->toArray();

            return response()->json(['assignments' => $asignaciones]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========== ÁREA DE CLIENTES ==========

    /**
     * Mostrar promociones vigentes de Oppen al cliente.
     */
    public function myCoupons()
    {
        try {
            $promociones = \App\Models\PromocionOppen::activasHoy()
                ->orderBy('nombre', 'ASC')
                ->get();

            return view('client.coupons.index', compact('promociones'));

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al cargar promociones', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error al cargar promociones: ' . $e->getMessage());
        }
    }

    /**
     * Canjear cupón por puntos
     */
    public function redeem($id)
    {
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Debes iniciar sesión'], 401);
            }
            return redirect('/login')->with('error', 'Debes iniciar sesión');
        }

        try {
            // Verificar que el cupón existe y está disponible
            $cupon = Cupon::where('id', $id)
                ->whereRaw('"activo" = true')
                ->whereDate('fecha_inicio', '<=', DB::raw('CURRENT_DATE'))
                ->whereDate('fecha_fin', '>=', DB::raw('CURRENT_DATE'))
                ->first();

            if (!$cupon) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cupón no disponible'], 400);
                }
                return back()->with('error', 'Cupón no disponible');
            }

            // Verificar que no ha canjeado este cupón antes
            $ya_canjeado = CuponAsignado::where('usuario_id', $user->id)
                ->where('cupon_id', $id)
                ->count();

            if ($ya_canjeado > 0) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Ya has canjeado este cupón anteriormente'], 400);
                }
                return back()->with('error', 'Ya has canjeado este cupón anteriormente');
            }

            DB::beginTransaction();

            // Generar código QR único basado en el código del cupón
            // Formato: CODIGOCUPON-XXXXX (ej: BANDERILLAS20-A3F9B)
            $codigo_qr = $cupon->codigo . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));

            // Asignar cupón con estado 'asignado'
            $asignacion = CuponAsignado::create([
                'usuario_id' => $user->id,
                'cupon_id' => $id,
                'estado' => 'asignado',
                'codigo_qr' => $codigo_qr,
                'asignado_por' => $user->id
            ]);

            DB::commit();

            // Si es petición AJAX, devolver JSON
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '¡Cupón canjeado exitosamente!',
                    'cupon' => [
                        'id' => $cupon->id,
                        'nombre' => $cupon->nombre,
                        'codigo' => $cupon->codigo,
                        'descripcion' => $cupon->descripcion,
                        'codigo_qr' => $codigo_qr
                    ]
                ]);
            }

            // Redirigir a la vista del cupón canjeado con el ID de asignación
            if ($asignacion) {
                return redirect()->route('coupons.show', $asignacion->id)
                    ->with('success', '¡Felicidades! Has canjeado tu cupón exitosamente. Aquí tienes tu código QR:')
                    ->with('nuevo_canje', true);
            }

            return back()->with('success', 'Cupón canjeado exitosamente. Código: ' . $codigo_qr);

        } catch (Exception $e) {
            DB::rollBack();
            
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
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return redirect('/login')->with('error', 'Debes iniciar sesión');
        }

        try {
            // Obtener cupón asignado con sus datos relacionados
            $cupon = CuponAsignado::select(
                    'cupones_asignados.*',
                    'cupones.nombre',
                    'cupones.codigo',
                    'cupones.descripcion',
                    'cupones.puntos_requeridos',
                    'cupones.fecha_inicio',
                    'cupones.fecha_fin'
                )
                ->join('cupones', 'cupones_asignados.cupon_id', '=', 'cupones.id')
                ->where('cupones_asignados.id', $id)
                ->where('cupones_asignados.usuario_id', $user->id)
                ->first();

            if (!$cupon) {
                return redirect()->route('coupons.index')->with('error', 'Cupón no encontrado');
            }

            // Convertir a array para mantener compatibilidad con la vista
            $cupon = $cupon->toArray();

            return view('client.coupons.show', compact('cupon'));

        } catch (Exception $e) {
            return redirect()->route('coupons.index')->with('error', 'Error al cargar cupón: ' . $e->getMessage());
        }
    }
}
