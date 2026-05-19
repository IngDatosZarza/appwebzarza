<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSucursalController extends Controller
{
    public function misClientes(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $query = Usuario::clientes()
            ->where('registrado_por_administrador_id', $admin->id);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $clientes = $query->orderBy('created_at', 'desc')->paginate(20);

        $totalRegistrados = Usuario::clientes()
            ->where('registrado_por_administrador_id', $admin->id)
            ->count();

        return view('admin.sucursal.clientes', compact('clientes', 'admin', 'totalRegistrados'));
    }
}
