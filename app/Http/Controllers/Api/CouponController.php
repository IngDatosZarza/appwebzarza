<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\CuponAsignado;
use App\Models\Usuario;
use App\Models\Redencione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    /**
     * Obtener cupones disponibles
     */
    public function index(Request $request)
    {
        try {
            $query = Cupon::whereRaw('"activo" = true');

            // Filtros opcionales
            if ($request->has('puntos_requeridos_max')) {
                $query->where('puntos_requeridos', '<=', $request->puntos_requeridos_max);
            }

            if ($request->has('vigente')) {
                $query->where('fecha_vencimiento', '>=', now());
            }

            $cupones = $query->orderBy('puntos_requeridos', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $cupones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cupones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Canjear cupón por puntos
     */
    public function redeem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:usuarios,id',
            'cupon_id' => 'required|exists:cupones,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $usuario = Usuario::with('puntos')->findOrFail($request->usuario_id);
            $cupon = Cupon::findOrFail($request->cupon_id);

            // Validaciones
            if (!$cupon->activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón no está activo'
                ], 400);
            }

            if ($cupon->fecha_vencimiento < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón ha vencido'
                ], 400);
            }

            if ($cupon->cantidad_disponible <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay cupones disponibles'
                ], 400);
            }

            $puntosUsuario = $usuario->puntos ? $usuario->puntos->saldo : 0;
            
            if ($puntosUsuario < $cupon->puntos_requeridos) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puntos insuficientes',
                    'data' => [
                        'puntos_requeridos' => $cupon->puntos_requeridos,
                        'puntos_disponibles' => $puntosUsuario,
                        'puntos_faltantes' => $cupon->puntos_requeridos - $puntosUsuario
                    ]
                ], 400);
            }

            // Verificar si ya canjeó este cupón
            $yaCanjeado = CuponAsignado::where('usuario_id', $usuario->id)
                                     ->where('cupon_id', $cupon->id)
                                     ->exists();

            if ($yaCanjeado && !$cupon->multiple_uso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has canjeado este cupón anteriormente'
                ], 400);
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
            ]);

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

            return response()->json([
                'success' => true,
                'message' => 'Cupón canjeado exitosamente',
                'data' => [
                    'cupon_asignado' => $cuponAsignado->fresh()->load('cupon'),
                    'puntos_restantes' => $usuario->puntos->fresh()->saldo,
                    'puntos_utilizados' => $cupon->puntos_requeridos
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al canjear cupón',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cupones del usuario
     */
    public function userCoupons($userId)
    {
        try {
            $cupones = CuponAsignado::with('cupon')
                                  ->where('usuario_id', $userId)
                                  ->orderBy('created_at', 'desc')
                                  ->get();

            return response()->json([
                'success' => true,
                'data' => $cupones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cupones del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Usar cupón (marcar como utilizado)
     */
    public function useCoupon(Request $request, $cuponAsignadoId)
    {
        $validator = Validator::make($request->all(), [
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cuponAsignado = CuponAsignado::with(['cupon', 'usuario'])
                                        ->findOrFail($cuponAsignadoId);

            // Validaciones
            if ($cuponAsignado->estado !== 'asignado') {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón ya ha sido utilizado o está vencido'
                ], 400);
            }

            if ($cuponAsignado->fecha_vencimiento < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón ha vencido'
                ], 400);
            }

            // Marcar como utilizado
            $cuponAsignado->update([
                'estado' => 'utilizado',
                'fecha_uso' => now(),
            ]);

            // Registrar redención
            Redencione::create([
                'cupon_asignado_id' => $cuponAsignado->id,
                'sucursal_id' => $request->sucursal_id,
                'fecha_redencion' => now(),
                'valor_redencion' => $cuponAsignado->cupon->valor_descuento,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cupón utilizado exitosamente',
                'data' => $cuponAsignado->fresh()->load(['cupon', 'redenciones.sucursal'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al utilizar cupón',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo cupón (solo admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo_descuento' => 'required|in:porcentaje,monto_fijo',
            'valor_descuento' => 'required|numeric|min:0',
            'puntos_requeridos' => 'required|integer|min:1',
            'cantidad_disponible' => 'required|integer|min:1',
            'fecha_vencimiento' => 'required|date|after:today',
            'multiple_uso' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cupon = Cupon::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'tipo_descuento' => $request->tipo_descuento,
                'valor_descuento' => $request->valor_descuento,
                'puntos_requeridos' => $request->puntos_requeridos,
                'cantidad_disponible' => $request->cantidad_disponible,
                'cantidad_total' => $request->cantidad_disponible,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'multiple_uso' => $request->multiple_uso ?? false,
                'activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cupón creado exitosamente',
                'data' => $cupon
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cupón',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}