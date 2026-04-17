<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodigoPostal;
use Illuminate\Http\Request;

class CodigoPostalController extends Controller
{
    /**
     * Obtener lista de todos los estados
     */
    public function getEstados()
    {
        try {
            $estados = CodigoPostal::getEstados();
            
            return response()->json([
                'success' => true,
                'data' => $estados
            ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estados',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obtener municipios de un estado
     */
    public function getMunicipios(Request $request)
    {
        $request->validate([
            'estado' => 'required|string'
        ]);

        try {
            $municipios = CodigoPostal::getMunicipiosPorEstado($request->estado);
            
            return response()->json([
                'success' => true,
                'data' => $municipios
            ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener municipios',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obtener colonias de un municipio
     */
    public function getColonias(Request $request)
    {
        $request->validate([
            'estado' => 'required|string',
            'municipio' => 'required|string'
        ]);

        try {
            $colonias = CodigoPostal::getColoniasPorMunicipio(
                $request->estado,
                $request->municipio
            );
            
            return response()->json([
                'success' => true,
                'data' => $colonias
            ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener colonias',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Buscar por código postal
     */
    public function buscarPorCP(Request $request)
    {
        $request->validate([
            'cp' => 'required|string|size:5'
        ]);

        try {
            $resultados = CodigoPostal::porCodigoPostal($request->cp)->get();
            
            if ($resultados->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron resultados para este código postal'
                ], 404, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
            }
            
            return response()->json([
                'success' => true,
                'data' => $resultados
            ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar código postal',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }
    }
}
