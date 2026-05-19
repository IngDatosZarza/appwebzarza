<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AdminClientesController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::clientes()
            ->with(['registradoPorAdministrador.sucursal', 'sucursalRegistro']);

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_registro_id', $request->sucursal_id);
        }

        if ($request->filled('administrador_id')) {
            $query->where('registrado_por_administrador_id', $request->administrador_id);
        }

        if ($request->filled('origen')) {
            $query->where('origen_registro', $request->origen);
        }

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

        $clientes = $query->orderBy('created_at', 'desc')->paginate(25);

        $sucursales = Sucursal::orderBy('nombre')->get();
        $administradores = Administrador::adminsSucursal()
            ->activos()
            ->with('sucursal')
            ->orderBy('nombres')
            ->get();

        $totalClientes = Usuario::clientes()->count();
        $totalAutoregistro = Usuario::clientes()->where('origen_registro', 'autoregistro')->count();
        $totalPorAdmin = Usuario::clientes()->whereNotNull('registrado_por_administrador_id')->count();

        return view('admin.clientes.index', compact(
            'clientes', 'sucursales', 'administradores',
            'totalClientes', 'totalAutoregistro', 'totalPorAdmin'
        ));
    }
}
