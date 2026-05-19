<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperadminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !$admin->esSuperadmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No tienes permisos de superadministrador para acceder a esta sección.');
        }

        return $next($request);
    }
}
