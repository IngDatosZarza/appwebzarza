<?php

namespace App\Services;

use App\Models\Puntos;
use App\Models\TransaccionPuntos;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Servicio para manejar la lógica de puntos
 */
class PointsService
{
    /**
     * Obtener el saldo de puntos de un usuario
     * 
     * @param int $userId
     * @return int
     */
    public function getUserBalance(int $userId): int
    {
        $puntos = Puntos::where('usuario_id', $userId)->first();
        return $puntos ? $puntos->saldo : 0;
    }

    /**
     * Agregar puntos a un usuario
     * 
     * @param int $userId
     * @param int $puntos
     * @param string $descripcion
     * @param int $registradoPor
     * @return array ['success' => bool, 'nuevo_saldo' => int, 'message' => string]
     */
    public function addPoints(int $userId, int $puntos, string $descripcion, int $registradoPor): array
    {
        try {
            DB::beginTransaction();

            // Incrementar saldo
            Puntos::where('usuario_id', $userId)->increment('saldo', $puntos);

            // Registrar transacción
            TransaccionPuntos::create([
                'usuario_id' => $userId,
                'tipo' => 'compra',
                'puntos' => $puntos,
                'descripcion' => $descripcion,
                'registrado_por' => $registradoPor
            ]);

            DB::commit();

            $nuevoSaldo = $this->getUserBalance($userId);

            return [
                'success' => true,
                'nuevo_saldo' => $nuevoSaldo,
                'message' => "Se agregaron $puntos puntos exitosamente."
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'nuevo_saldo' => 0,
                'message' => 'Error al agregar puntos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Descontar puntos de un usuario
     * 
     * @param int $userId
     * @param int $puntos
     * @param string $descripcion
     * @param int $registradoPor
     * @return array ['success' => bool, 'nuevo_saldo' => int, 'message' => string]
     */
    public function deductPoints(int $userId, int $puntos, string $descripcion, int $registradoPor): array
    {
        try {
            // Verificar saldo suficiente
            $saldoActual = $this->getUserBalance($userId);
            
            if ($saldoActual < $puntos) {
                return [
                    'success' => false,
                    'nuevo_saldo' => $saldoActual,
                    'message' => 'Saldo insuficiente. Saldo actual: ' . $saldoActual . ' puntos.'
                ];
            }

            DB::beginTransaction();

            // Decrementar saldo
            Puntos::where('usuario_id', $userId)->decrement('saldo', $puntos);

            // Registrar transacción
            TransaccionPuntos::create([
                'usuario_id' => $userId,
                'tipo' => 'canje',
                'puntos' => $puntos,
                'descripcion' => $descripcion,
                'registrado_por' => $registradoPor
            ]);

            DB::commit();

            $nuevoSaldo = $this->getUserBalance($userId);

            return [
                'success' => true,
                'nuevo_saldo' => $nuevoSaldo,
                'message' => "Se descontaron $puntos puntos exitosamente."
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'nuevo_saldo' => 0,
                'message' => 'Error al descontar puntos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener historial de transacciones de un usuario
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getTransactionHistory(int $userId, int $limit = 50): array
    {
        return TransaccionPuntos::select(
                'transacciones_puntos.id',
                'transacciones_puntos.tipo',
                'transacciones_puntos.puntos',
                'transacciones_puntos.descripcion',
                'transacciones_puntos.created_at',
                'usuarios.nombres as registrado_por_nombre',
                'usuarios.apellido_paterno as registrado_por_apellido'
            )
            ->leftJoin('usuarios', 'transacciones_puntos.registrado_por', '=', 'usuarios.id')
            ->where('transacciones_puntos.usuario_id', $userId)
            ->orderBy('transacciones_puntos.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Calcular puntos generados por una compra
     * 
     * @param float $monto
     * @return int
     */
    public function calculatePointsFromPurchase(float $monto): int
    {
        // Regla de negocio: 1 punto por cada peso gastado
        return (int) floor($monto);
    }

    /**
     * Obtener estadísticas de puntos del sistema
     * 
     * @return array
     */
    public function getSystemStats(): array
    {
        return [
            'total_puntos_circulacion' => Puntos::sum('saldo') ?? 0,
            'total_usuarios_con_puntos' => Puntos::where('saldo', '>', 0)->count(),
            'promedio_puntos_usuario' => Puntos::avg('saldo') ?? 0,
            'transacciones_mes_actual' => TransaccionPuntos::where('created_at', '>=', DB::raw("DATE_TRUNC('month', CURRENT_DATE)"))->count()
        ];
    }
}
