<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSucursalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || (!$admin->esSuperadmin() && !$admin->esAdminSucursal())) {
            return redirect()->route('admin.login')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
