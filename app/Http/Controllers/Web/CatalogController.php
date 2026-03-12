<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class CatalogController extends Controller
{
    /**
     * Mostrar el catálogo de productos
     */
    public function index()
    {
        return view('catalog.index');
    }
}
