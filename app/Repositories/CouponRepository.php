<?php

namespace App\Repositories;

use App\Models\Cupon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Repositorio para la entidad Cupón
 * Maneja el acceso a datos de cupones
 */
class CouponRepository
{
    /**
     * Buscar cupón por ID
     * 
     * @param int $id
     * @return Cupon|null
     */
    public function find(int $id): ?Cupon
    {
        return Cupon::find($id);
    }

    /**
     * Obtener todos los cupones activos
     * 
     * @return Collection
     */
    public function getAllActive(): Collection
    {
        return Cupon::where('activo', true)
            ->whereDate('fecha_inicio', '<=', DB::raw('CURRENT_DATE'))
            ->whereDate('fecha_fin', '>=', DB::raw('CURRENT_DATE'))
            ->orderBy('puntos_requeridos', 'ASC')
            ->get();
    }

    /**
     * Obtener todos los cupones (activos e inactivos)
     * 
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Cupon::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtener cupones con estadísticas de asignaciones
     * 
     * @return Collection
     */
    public function getAllWithStats(): Collection
    {
        return Cupon::leftJoin('cupones_asignados', 'cupones.id', '=', 'cupones_asignados.cupon_id')
            ->select('cupones.*')
            ->selectRaw('COUNT(cupones_asignados.id) as total_asignados')
            ->selectRaw("SUM(CASE WHEN cupones_asignados.estado = 'usado' THEN 1 ELSE 0 END) as total_usados")
            ->groupBy('cupones.id')
            ->orderBy('cupones.created_at', 'DESC')
            ->get();
    }

    /**
     * Crear un nuevo cupón
     * 
     * @param array $data
     * @return Cupon
     */
    public function create(array $data): Cupon
    {
        return Cupon::create($data);
    }

    /**
     * Actualizar un cupón
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $cupon = Cupon::find($id);
        if (!$cupon) {
            return false;
        }
        return $cupon->update($data);
    }

    /**
     * Eliminar un cupón (soft delete)
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $cupon = Cupon::find($id);
        if (!$cupon) {
            return false;
        }
        return $cupon->delete();
    }

    /**
     * Buscar cupón por código
     * 
     * @param string $codigo
     * @return Cupon|null
     */
    public function findByCode(string $codigo): ?Cupon
    {
        return Cupon::where('codigo', $codigo)->first();
    }
}
