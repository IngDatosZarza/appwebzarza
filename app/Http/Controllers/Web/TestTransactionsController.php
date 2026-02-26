<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PDO;

class TestTransactionsController extends Controller
{
    public function showTransactions(Request $request)
    {
        // Verificar si el usuario es administrador
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Datos de prueba estáticos
        $transacciones = [
            [
                'id' => 1,
                'usuario_nombre' => 'Juan Pérez',
                'usuario_email' => 'juan@test.com',
                'tipo' => 'compra',
                'tipo_descripcion' => '🛒 Compra',
                'tipo_movimiento' => 'positivo',
                'puntos' => 100,
                'descripcion' => 'Compra en sucursal',
                'registrado_por_nombre' => 'Sistema',
                'registrado_por_email' => 'sistema@test.com',
                'created_at' => now()->format('Y-m-d H:i:s')
            ]
        ];

        $estadisticas = [
            'total_transacciones' => 1,
            'puntos_generados' => 100,
            'puntos_utilizados' => 0,
            'saldo_neto' => 100,
            'total_compras' => 1,
            'total_canjes' => 0,
            'total_ajustes' => 0
        ];

        $filtros = [
            'tipo' => '',
            'usuario' => '',
            'fecha_desde' => '',
            'fecha_hasta' => ''
        ];

        $paginacion = [
            'current_page' => 1,
            'total_pages' => 1,
            'total_records' => 1,
            'per_page' => 50,
            'has_prev' => false,
            'has_next' => false
        ];

        return view('admin.transactions.test', compact('transacciones', 'estadisticas', 'filtros', 'paginacion'));
    }
}