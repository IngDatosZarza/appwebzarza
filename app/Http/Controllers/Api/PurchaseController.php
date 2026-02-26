<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\TransaccionPunto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * Registrar nueva compra y generar puntos
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:usuarios,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
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
            $usuario = Usuario::findOrFail($request->usuario_id);
            $sucursal = Sucursal::findOrFail($request->sucursal_id);

            // Calcular puntos (1 punto por cada peso gastado)
            $puntosGenerados = floor($request->monto);

            // Crear la compra
            $compra = Compra::create([
                'usuario_id' => $request->usuario_id,
                'sucursal_id' => $request->sucursal_id,
                'monto' => $request->monto,
                'puntos_generados' => $puntosGenerados,
                'descripcion' => $request->descripcion,
                'metodo_pago' => $request->metodo_pago,
                'fecha_compra' => now(),
            ]);

            // Actualizar puntos del usuario
            $puntos = $usuario->puntos()->firstOrCreate([
                'usuario_id' => $usuario->id
            ], [
                'saldo' => 0,
                'puntos_acumulados' => 0,
                'puntos_utilizados' => 0,
                'ultima_actualizacion' => now(),
            ]);

            $puntos->update([
                'saldo' => $puntos->saldo + $puntosGenerados,
                'puntos_acumulados' => $puntos->puntos_acumulados + $puntosGenerados,
                'ultima_actualizacion' => now(),
            ]);

            // Registrar transacción de puntos
            TransaccionPunto::create([
                'usuario_id' => $usuario->id,
                'compra_id' => $compra->id,
                'tipo' => 'ganancia',
                'puntos' => $puntosGenerados,
                'saldo_anterior' => $puntos->saldo - $puntosGenerados,
                'saldo_nuevo' => $puntos->saldo,
                'descripcion' => "Puntos por compra en {$sucursal->nombre}",
                'fecha_transaccion' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra registrada exitosamente',
                'data' => [
                    'compra' => $compra->fresh()->load(['usuario', 'sucursal']),
                    'puntos_generados' => $puntosGenerados,
                    'saldo_actual' => $puntos->fresh()->saldo,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de compras
     */
    public function index(Request $request)
    {
        try {
            $query = Compra::with(['usuario', 'sucursal'])
                          ->orderBy('fecha_compra', 'desc');

            // Filtros opcionales
            if ($request->has('usuario_id')) {
                $query->where('usuario_id', $request->usuario_id);
            }

            if ($request->has('sucursal_id')) {
                $query->where('sucursal_id', $request->sucursal_id);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_compra', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_compra', '<=', $request->fecha_hasta);
            }

            $compras = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $compras
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener compras',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de una compra específica
     */
    public function show($id)
    {
        try {
            $compra = Compra::with(['usuario', 'sucursal', 'transaccionPuntos'])
                           ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $compra
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtener estadísticas de compras
     */
    public function stats(Request $request)
    {
        try {
            $fechaDesde = $request->get('fecha_desde', now()->startOfMonth());
            $fechaHasta = $request->get('fecha_hasta', now()->endOfMonth());

            $stats = DB::table('compras')
                      ->whereBetween('fecha_compra', [$fechaDesde, $fechaHasta])
                      ->select([
                          DB::raw('COUNT(*) as total_compras'),
                          DB::raw('SUM(monto) as monto_total'),
                          DB::raw('SUM(puntos_generados) as puntos_total'),
                          DB::raw('AVG(monto) as monto_promedio'),
                          DB::raw('COUNT(DISTINCT usuario_id) as clientes_unicos'),
                      ])
                      ->first();

            // Top sucursales
            $topSucursales = DB::table('compras')
                              ->join('sucursales', 'compras.sucursal_id', '=', 'sucursales.id')
                              ->whereBetween('compras.fecha_compra', [$fechaDesde, $fechaHasta])
                              ->select([
                                  'sucursales.nombre',
                                  DB::raw('COUNT(*) as total_compras'),
                                  DB::raw('SUM(compras.monto) as monto_total')
                              ])
                              ->groupBy('sucursales.id', 'sucursales.nombre')
                              ->orderBy('monto_total', 'desc')
                              ->limit(5)
                              ->get();

            // Top clientes
            $topClientes = DB::table('compras')
                            ->join('usuarios', 'compras.usuario_id', '=', 'usuarios.id')
                            ->whereBetween('compras.fecha_compra', [$fechaDesde, $fechaHasta])
                            ->select([
                                DB::raw("CONCAT(usuarios.nombres, ' ', usuarios.apellido_paterno) as nombre"),
                                'usuarios.email',
                                DB::raw('COUNT(*) as total_compras'),
                                DB::raw('SUM(compras.monto) as monto_total'),
                                DB::raw('SUM(compras.puntos_generados) as puntos_generados')
                            ])
                            ->groupBy('usuarios.id', 'usuarios.nombres', 'usuarios.apellido_paterno', 'usuarios.email')
                            ->orderBy('monto_total', 'desc')
                            ->limit(10)
                            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => [
                        'desde' => $fechaDesde,
                        'hasta' => $fechaHasta
                    ],
                    'resumen' => $stats,
                    'top_sucursales' => $topSucursales,
                    'top_clientes' => $topClientes
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}