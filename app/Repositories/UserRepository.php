<?php

namespace App\Repositories;

use App\Models\Usuario;
use Illuminate\Support\Collection;

/**
 * Repositorio para la entidad Usuario
 * Maneja el acceso a datos de usuarios
 */
class UserRepository
{
    /**
     * Buscar usuario por email
     * 
     * @param string $email
     * @return Usuario|null
     */
    public function findByEmail(string $email): ?Usuario
    {
        return Usuario::where('email', $email)->first();
    }

    /**
     * Buscar usuario por ID
     * 
     * @param int $id
     * @return Usuario|null
     */
    public function find(int $id): ?Usuario
    {
        return Usuario::find($id);
    }

    /**
     * Obtener todos los clientes
     * 
     * @return Collection
     */
    public function getAllClients(): Collection
    {
        return Usuario::where('rol', 'cliente')
            ->orderBy('nombres')
            ->get();
    }

    /**
     * Obtener todos los administradores
     * 
     * @return Collection
     */
    public function getAllAdmins(): Collection
    {
        return Usuario::where('rol', 'admin')
            ->orderBy('nombres')
            ->get();
    }

    /**
     * Obtener usuarios con más puntos
     * 
     * @param int $limit
     * @return Collection
     */
    public function getTopUsersByPoints(int $limit = 10): Collection
    {
        return Usuario::select('usuarios.*', 'puntos.saldo')
            ->join('puntos', 'usuarios.id', '=', 'puntos.usuario_id')
            ->where('usuarios.rol', 'cliente')
            ->orderBy('puntos.saldo', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Crear un nuevo usuario
     * 
     * @param array $data
     * @return Usuario
     */
    public function create(array $data): Usuario
    {
        return Usuario::create($data);
    }

    /**
     * Actualizar un usuario
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return false;
        }
        return $usuario->update($data);
    }

    /**
     * Eliminar un usuario (soft delete)
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return false;
        }
        return $usuario->delete();
    }

    /**
     * Contar usuarios por rol
     * 
     * @param string $rol
     * @return int
     */
    public function countByRole(string $rol): int
    {
        return Usuario::where('rol', $rol)->count();
    }
}
