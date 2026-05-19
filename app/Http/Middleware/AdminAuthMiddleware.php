<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Debes iniciar sesión para acceder al panel de administración.');
        }

        $admin = Auth::guard('admin')->user();

        if (!$admin->activo) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacta al superadministrador.');
        }

        return $next($request);
    }
}
