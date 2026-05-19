<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting por IP + email
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning('Rate limit alcanzado en login admin', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos de inicio de sesión. Intenta de nuevo en {$seconds} segundos.",
            ]);
        }

        $admin = Administrador::where('email', $request->email)->first();

        // Verificar existencia y contraseña
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            RateLimiter::hit($throttleKey, 900); // 15 min decay

            if ($admin) {
                $admin->incrementarIntentosFallidos();
            }

            Log::info('Intento fallido de login admin', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no son correctas.',
            ]);
        }

        // Verificar si la cuenta está bloqueada
        if ($admin->estaBloqueado()) {
            $minutos = $admin->bloqueado_hasta->diffInMinutes(now());
            Log::warning('Intento de login a cuenta bloqueada', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => "Tu cuenta está temporalmente bloqueada. Intenta de nuevo en {$minutos} minutos.",
            ]);
        }

        // Verificar si está activo
        if (!$admin->activo) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta ha sido desactivada. Contacta al superadministrador.',
            ]);
        }

        // Autenticación exitosa
        RateLimiter::clear($throttleKey);
        $admin->registrarAcceso();

        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        Log::info('Login exitoso de administrador', [
            'admin_id' => $admin->id,
            'email' => $admin->email,
            'rol' => $admin->rol,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', '¡Bienvenido al Panel de Administración, ' . $admin->nombres . '!');
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        Log::info('Logout de administrador', [
            'admin_id' => $admin?->id,
            'email' => $admin?->email,
        ]);

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}
