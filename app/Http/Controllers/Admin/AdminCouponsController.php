<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\CuponAsignado;
use App\Models\Usuario;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminCouponsController extends Controller
{
    /**
     * Obtener el admin autenticado del guard 'admin'.
     */
    private function admin()
    {
        return Auth::guard('admin')->user();
    }

    /**
     * Lista de cupones para administradores
     */
    public function index()
    {
        try {
            $cupones = Cupon::leftJoin('cupones_asignados', 'cupones.id', '=', 'cupones_asignados.cupon_id')
                ->select('cupones.*')
                ->selectRaw('COUNT(cupones_asignados.id) as total_asignados')
                ->selectRaw("COUNT(CASE WHEN cupones_asignados.estado = 'asignado' THEN 1 END) as disponibles")
                ->selectRaw("COUNT(CASE WHEN cupones_asignados.estado = 'usado' THEN 1 END) as usados")
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
        return view('admin.coupons.create');
    }

    /**
     * Guardar nuevo cupón
     */
    public function store(Request $request)
    {
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
            $nombre = trim($request->input('nombre'));
            $descripcion = trim($request->input('descripcion'));
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_fin = $request->input('fecha_fin');
            $activo = (bool) $request->has('activo');

            DB::beginTransaction();

            $cupon = Cupon::create([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'puntos_requeridos' => 0,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'activo' => $activo,
            ]);

            Auditoria::create([
                'tabla' => 'cupones',
                'registro_id' => $cupon->id,
                'accion' => 'create',
                'usuario_id' => $this->admin()->id,
                'cambios' => json_encode([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'activo' => $activo,
                    'admin_panel' => true,
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
        try {
            $cupon = Cupon::findOrFail($id);
            return view('admin.coupons.edit', compact('cupon'));
        } catch (Exception $e) {
            return redirect()->route('admin.coupons.index')->with('error', 'Cupón no encontrado');
        }
    }

    /**
     * Actualizar cupón
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        try {
            $cupon = Cupon::findOrFail($id);
            $cupon_actual = $cupon->toArray();

            $nombre = trim($request->input('nombre'));
            $descripcion = trim($request->input('descripcion'));
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_fin = $request->input('fecha_fin');
            $activo = (bool) $request->has('activo');

            DB::beginTransaction();

            $cupon->update([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'activo' => $activo,
            ]);

            Auditoria::create([
                'tabla' => 'cupones',
                'registro_id' => $id,
                'accion' => 'update',
                'usuario_id' => $this->admin()->id,
                'cambios' => json_encode([
                    'anterior' => $cupon_actual,
                    'nuevo' => ['nombre' => $nombre, 'descripcion' => $descripcion, 'activo' => $activo],
                    'admin_panel' => true,
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
        try {
            $cupon = Cupon::findOrFail($id);
            $total_asignaciones = CuponAsignado::where('cupon_id', $id)->count();

            if ($total_asignaciones > 0) {
                return back()->with('error', 'No se puede eliminar un cupón que ya tiene asignaciones');
            }

            DB::beginTransaction();

            $cupon_data = $cupon->toArray();
            $cupon->delete();

            Auditoria::create([
                'tabla' => 'cupones',
                'registro_id' => $id,
                'accion' => 'delete',
                'usuario_id' => $this->admin()->id,
                'cambios' => json_encode(['cupon_eliminado' => $cupon_data, 'admin_panel' => true])
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
        try {
            $usuario_id = (int) $request->input('usuario_id');
            if (!$usuario_id) {
                return back()->with('error', 'Debe seleccionar un usuario');
            }

            $cupon = Cupon::where('id', $id)->whereRaw('"activo" = true')->first();

            if (!$cupon) {
                return back()->with('error', 'Cupón no encontrado o inactivo');
            }

            $usuario = Usuario::findOrFail($usuario_id);

            DB::beginTransaction();

            $codigo_qr = 'ZP' . strtoupper(bin2hex(random_bytes(6)));

            CuponAsignado::create([
                'usuario_id' => $usuario_id,
                'cupon_id' => $id,
                'estado' => 'asignado',
                'codigo_qr' => $codigo_qr,
                'asignado_por' => $this->admin()->id
            ]);

            DB::commit();

            return back()->with('success', 'Cupón asignado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al asignar cupón: ' . $e->getMessage());
        }
    }

    /**
     * Obtener asignaciones de un cupón específico (JSON)
     */
    public function getAssignments($id)
    {
        try {
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

    /**
     * Mostrar formulario de validación de cupones (QR)
     */
    public function showValidationForm()
    {
        return view('admin.coupons.validate');
    }

    /**
     * Validar un código QR de cupón
     */
    public function validateCoupon(Request $request)
    {
        $codigo_input = $request->input('codigo_qr');

        if (empty($codigo_input)) {
            return response()->json(['error' => 'Código requerido'], 400);
        }

        try {
            $cupon = CuponAsignado::select(
                    'cupones_asignados.*',
                    'cupones.nombre as cupon_nombre',
                    'cupones.codigo as cupon_codigo',
                    'cupones.descripcion as cupon_descripcion',
                    'cupones.puntos_requeridos',
                    DB::raw("usuarios.nombres || ' ' || usuarios.apellido_paterno as cliente_nombre"),
                    'usuarios.email as cliente_email'
                )
                ->join('cupones', 'cupones_asignados.cupon_id', '=', 'cupones.id')
                ->join('usuarios', 'cupones_asignados.usuario_id', '=', 'usuarios.id')
                ->where(function ($q) use ($codigo_input) {
                    $q->where('cupones_asignados.codigo_qr', $codigo_input)
                      ->orWhere('cupones.codigo', $codigo_input);
                })
                ->orderBy('cupones_asignados.created_at', 'DESC')
                ->first();

            if (!$cupon) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Código no válido o no encontrado. Verifica el código e intenta nuevamente.'
                ]);
            }

            switch ($cupon->estado) {
                case 'asignado':
                    return response()->json([
                        'valid' => true,
                        'status' => 'available',
                        'message' => 'Cupón válido y disponible para usar',
                        'data' => [
                            'cupon_nombre' => $cupon->cupon_nombre,
                            'cupon_codigo' => $cupon->cupon_codigo,
                            'cupon_descripcion' => $cupon->cupon_descripcion,
                            'puntos_utilizados' => $cupon->puntos_requeridos,
                            'cliente_nombre' => $cupon->cliente_nombre,
                            'cliente_email' => $cupon->cliente_email,
                            'fecha_canje' => $cupon->created_at->format('d/m/Y H:i'),
                            'codigo_qr' => $cupon->codigo_qr,
                        ]
                    ]);

                case 'usado':
                    return response()->json([
                        'valid' => false,
                        'status' => 'used',
                        'message' => 'Este cupón ya fue utilizado',
                        'data' => [
                            'cupon_nombre' => $cupon->cupon_nombre,
                            'cupon_codigo' => $cupon->cupon_codigo,
                            'cliente_nombre' => $cupon->cliente_nombre,
                            'fecha_uso' => $cupon->fecha_uso ? date('d/m/Y H:i', strtotime($cupon->fecha_uso)) : 'No disponible',
                        ]
                    ]);

                case 'vencido':
                    return response()->json(['valid' => false, 'status' => 'expired', 'message' => 'Este cupón ha vencido']);

                case 'bloqueado':
                    return response()->json(['valid' => false, 'status' => 'blocked', 'message' => 'Este cupón ha sido bloqueado']);

                default:
                    return response()->json(['valid' => false, 'status' => 'invalid', 'message' => 'Estado de cupón no válido']);
            }

        } catch (Exception $e) {
            return response()->json(['error' => 'Error al validar cupón: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marcar cupón como usado
     */
    public function markAsUsed(Request $request)
    {
        $codigo_qr = $request->input('codigo_qr');

        if (empty($codigo_qr)) {
            return response()->json(['error' => 'Código QR requerido'], 400);
        }

        try {
            $cuponAsignado = CuponAsignado::where('codigo_qr', $codigo_qr)->first();

            if (!$cuponAsignado) {
                return response()->json(['error' => 'Cupón no encontrado'], 404);
            }

            if ($cuponAsignado->estado !== 'asignado') {
                return response()->json(['error' => 'El cupón no está disponible para usar'], 400);
            }

            DB::beginTransaction();

            DB::statement("
                UPDATE cupones_asignados 
                SET estado = 'usado', 
                    fecha_uso = NOW(), 
                    validado_por = ?,
                    updated_at = NOW()
                WHERE codigo_qr = ?
            ", [$this->admin()->id, $codigo_qr]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cupón marcado como utilizado exitosamente'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al marcar cupón: ' . $e->getMessage()], 500);
        }
    }
}
