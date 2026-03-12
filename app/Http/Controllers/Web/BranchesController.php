<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class BranchesController extends Controller
{
    /**
     * Mostrar listado de sucursales
     */
    public function index()
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        
        return view('branches.index', [
            'sucursales' => $sucursales
        ]);
    }
}
