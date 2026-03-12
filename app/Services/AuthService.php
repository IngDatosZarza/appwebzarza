<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

/**
 * Servicio centralizado de autenticación
 * Consolida el sistema dual (Laravel Auth + Sesiones manuales)
 */
class AuthService
{
    /**
     * Obtener el usuario autenticado actual
     * 
     * @return Usuario|null
     */
    public function getCurrentUser(): ?Usuario
    {
        // Primero intentar obtener desde Laravel Auth
        $user = Auth::user();
        
        // Si no hay usuario en Auth, verificar sesión manual (compatibilidad)
        if (!$user && Session::get('user_authenticated', false)) {
            $userId = Session::get('user_id');
            if ($userId) {
                $user = Usuario::find($userId);
            }
        }
        
        return $user;
    }

    /**
     * Realizar login del usuario
     * 
     * @param string $email
     * @param string $password
     * @return array ['success' => bool, 'user' => Usuario|null, 'message' => string]
     */
    public function login(string $email, string $password): array
    {
        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario || !Hash::check($password, $usuario->password)) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'
            ];
        }

        // Actualizar marca de tiempo de actividad
        $usuario->touch();

        // Regenerar la sesión para evitar fixation
        Session::regenerate();

        // Autenticar al usuario en el guard de Laravel
        Auth::login($usuario);

        // Sincronizar con sesión manual para compatibilidad
        $this->syncUserToSession($usuario);

        return [
            'success' => true,
            'user' => $usuario,
            'message' => '¡Bienvenido de vuelta, ' . $usuario->nombres . '!'
        ];
    }

    /**
     * Realizar logout del usuario
     * 
     * @return void
     */
    public function logout(): void
    {
        Auth::logout();
        Session::flush();
        Session::regenerate();
    }

    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return Auth::check() || Session::get('user_authenticated', false);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string $rol
     * @return bool
     */
    public function hasRole(string $rol): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->rol === $rol;
    }

    /**
     * Verificar si el usuario es administrador
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verificar si el usuario es cliente
     * 
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->hasRole('cliente');
    }

    /**
     * Sincronizar usuario a la sesión manual (compatibilidad)
     * 
     * @param Usuario $usuario
     * @return void
     */
    private function syncUserToSession(Usuario $usuario): void
    {
        Session::put('user_authenticated', true);
        Session::put('user_id', $usuario->id);
        Session::put('user_email', $usuario->email);
        Session::put('user_nombre', $usuario->nombres);
        Session::put('user_nombres', $usuario->nombres);
        Session::put('user_apellido', $usuario->apellido_paterno);
        Session::put('user_apellido_paterno', $usuario->apellido_paterno);
        Session::put('user_rol', $usuario->rol);

        // Obtener puntos del usuario
        $puntos = $usuario->puntos;
        $saldoPuntos = $puntos ? $puntos->saldo : 0;
        Session::put('user_puntos', $saldoPuntos);
    }

    /**
     * Registrar un nuevo usuario
     * 
     * @param array $data
     * @return array ['success' => bool, 'user' => Usuario|null, 'message' => string]
     */
    public function register(array $data): array
    {
        // Verificar si el email ya existe
        if (Usuario::where('email', $data['email'])->exists()) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'El email ya está registrado.'
            ];
        }

        try {
            // Registrar usuario tendría la lógica completa de creación
            // con direcciones, puntos iniciales, etc.
            // Por ahora retornamos estructura básica
            
            return [
                'success' => true,
                'user' => null,
                'message' => 'Usuario registrado exitosamente (implementar lógica completa).'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
            ];
        }
    }
}
