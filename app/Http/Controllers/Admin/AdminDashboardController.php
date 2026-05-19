<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->esSuperadmin()) {
            return $this->superadminDashboard();
        }

        return $this->sucursalDashboard($admin);
    }

    private function superadminDashboard()
    {
        $totalAdmins = Administrador::adminsSucursal()->activos()->count();
        $totalClientes = Usuario::clientes()->count();
        $clientesHoy = Usuario::clientes()->whereDate('created_at', today())->count();
        $clientesSemana = Usuario::clientes()->where('created_at', '>=', now()->startOfWeek())->count();
        $clientesMes = Usuario::clientes()->where('created_at', '>=', now()->startOfMonth())->count();

        $sucursales = Sucursal::withCount('clientesRegistrados')
            ->with(['administradores' => function ($q) {
                $q->adminsSucursal()->activos()->withCount('clientesRegistrados');
            }])
            ->orderBy('nombre')
            ->get();

        $ultimosClientes = Usuario::clientes()
            ->with(['registradoPorAdministrador.sucursal', 'sucursalRegistro'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalAdmins', 'totalClientes', 'clientesHoy', 'clientesSemana', 'clientesMes',
            'sucursales', 'ultimosClientes'
        ));
    }

    private function sucursalDashboard(Administrador $admin)
    {
        $misClientes = Usuario::clientes()
            ->where('registrado_por_administrador_id', $admin->id)
            ->count();

        $clientesHoy = Usuario::clientes()
            ->where('registrado_por_administrador_id', $admin->id)
            ->whereDate('created_at', today())
            ->count();

        $clientesSemana = Usuario::clientes()
            ->where('registrado_por_administrador_id', $admin->id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        $clientesMes = Usuario::clientes()
            ->where('registrado_por_administrador_id', $admin->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $ultimosClientes = Usuario::clientes()
            ->where('registrado_por_administrador_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.sucursal.dashboard', compact(
            'admin', 'misClientes', 'clientesHoy', 'clientesSemana', 'clientesMes', 'ultimosClientes'
        ));
    }
}
