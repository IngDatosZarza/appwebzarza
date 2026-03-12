<?php

namespace App\Services;

use App\Models\Cupon;
use App\Models\CuponAsignado;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Servicio para manejar la lógica de cupones
 */
class CouponService
{
    protected $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Obtener cupones disponibles para un usuario
     * 
     * @param int $userId
     * @return array
     */
    public function getAvailableCoupons(int $userId): array
    {
        return Cupon::select(
                'cupones.*',
                DB::raw('CASE WHEN cupones_asignados.id IS NOT NULL THEN true ELSE false END as ya_canjeado')
            )
            ->leftJoin('cupones_asignados', function($join) use ($userId) {
                $join->on('cupones.id', '=', 'cupones_asignados.cupon_id')
                     ->where('cupones_asignados.usuario_id', '=', $userId);
            })
            ->where('cupones.activo', true)
            ->whereDate('cupones.fecha_inicio', '<=', DB::raw('CURRENT_DATE'))
            ->whereDate('cupones.fecha_fin', '>=', DB::raw('CURRENT_DATE'))
            ->orderBy('cupones.puntos_requeridos', 'ASC')
            ->get()
            ->toArray();
    }

    /**
     * Obtener cupones del usuario
     * 
     * @param int $userId
     * @return array
     */
    public function getUserCoupons(int $userId): array
    {
        return CuponAsignado::select(
                'cupones_asignados.*',
                'cupones.nombre',
                'cupones.codigo',
                'cupones.descripcion',
                'cupones.puntos_requeridos',
                DB::raw("
                    CASE 
                        WHEN cupones_asignados.estado = 'asignado' THEN 'disponible'
                        WHEN cupones_asignados.estado = 'usado' THEN 'usado'
                        WHEN cupones_asignados.estado = 'bloqueado' THEN 'bloqueado'
                        WHEN cupones_asignados.estado = 'vencido' THEN 'vencido'
                        ELSE 'otro'
                    END as estado_display
                ")
            )
            ->join('cupones', 'cupones_asignados.cupon_id', '=', 'cupones.id')
            ->where('cupones_asignados.usuario_id', $userId)
            ->orderBy('cupones_asignados.created_at', 'DESC')
            ->get()
            ->toArray();
    }

    /**
     * Verificar si un usuario puede canjear un cupón
     * 
     * @param int $userId
     * @param int $cuponId
     * @return array ['can_redeem' => bool, 'message' => string]
     */
    public function canRedeemCoupon(int $userId, int $cuponId): array
    {
        // Verificar que el cupón existe y está disponible
        $cupon = Cupon::where('id', $cuponId)
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', DB::raw('CURRENT_DATE'))
            ->whereDate('fecha_fin', '>=', DB::raw('CURRENT_DATE'))
            ->first();

        if (!$cupon) {
            return [
                'can_redeem' => false,
                'message' => 'Cupón no disponible'
            ];
        }

        // Verificar puntos suficientes
        $saldo = $this->pointsService->getUserBalance($userId);
        if ($saldo < $cupon->puntos_requeridos) {
            return [
                'can_redeem' => false,
                'message' => "No tienes puntos suficientes. Necesitas {$cupon->puntos_requeridos} puntos, tienes {$saldo}."
            ];
        }

        // Verificar que no ha canjeado este cupón antes
        $yaCanjeado = CuponAsignado::where('usuario_id', $userId)
            ->where('cupon_id', $cuponId)
            ->exists();

        if ($yaCanjeado) {
            return [
                'can_redeem' => false,
                'message' => 'Ya has canjeado este cupón anteriormente'
            ];
        }

        return [
            'can_redeem' => true,
            'message' => 'Puedes canjear este cupón'
        ];
    }

    /**
     * Canjear un cupón para un usuario
     * 
     * @param int $userId
     * @param int $cuponId
     * @return array ['success' => bool, 'cupon_asignado' => CuponAsignado|null, 'message' => string]
     */
    public function redeemCoupon(int $userId, int $cuponId): array
    {
        // Verificar si puede canjear
        $check = $this->canRedeemCoupon($userId, $cuponId);
        if (!$check['can_redeem']) {
            return [
                'success' => false,
                'cupon_asignado' => null,
                'message' => $check['message']
            ];
        }

        try {
            DB::beginTransaction();

            $cupon = Cupon::find($cuponId);

            // Generar código QR único
            $codigo_qr = $cupon->codigo . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));

            // Asignar cupón
            $asignacion = CuponAsignado::create([
                'usuario_id' => $userId,
                'cupon_id' => $cuponId,
                'estado' => 'asignado',
                'codigo_qr' => $codigo_qr,
                'asignado_por' => $userId
            ]);

            // Descontar puntos
            $resultado = $this->pointsService->deductPoints(
                $userId,
                $cupon->puntos_requeridos,
                'Canje por cupón: ' . $cupon->nombre,
                $userId
            );

            if (!$resultado['success']) {
                throw new Exception($resultado['message']);
            }

            DB::commit();

            return [
                'success' => true,
                'cupon_asignado' => $asignacion,
                'message' => "¡Cupón canjeado exitosamente! Código QR: {$codigo_qr}"
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'cupon_asignado' => null,
                'message' => 'Error al canjear cupón: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de cupones
     * 
     * @return array
     */
    public function getCouponStats(): array
    {
        return [
            'total_cupones_activos' => Cupon::where('activo', true)->count(),
            'total_cupones_asignados' => CuponAsignado::count(),
            'cupones_mes_actual' => CuponAsignado::where('created_at', '>=', DB::raw("DATE_TRUNC('month', CURRENT_DATE)"))->count(),
            'cupones_usados' => CuponAsignado::where('estado', 'usado')->count(),
            'cupones_pendientes' => CuponAsignado::where('estado', 'asignado')->count()
        ];
    }

    /**
     * Generar código QR único para un cupón
     * 
     * @param string $codigoCupon
     * @return string
     */
    public function generateUniqueQRCode(string $codigoCupon): string
    {
        return $codigoCupon . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));
    }
}
