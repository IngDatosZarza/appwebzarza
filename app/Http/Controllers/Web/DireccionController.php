<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Direccion;
use App\Models\CodigoPostal;
use App\Models\Usuario;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DireccionController extends Controller
{
    /**
     * Obtener dirección principal del usuario actual
     */
    public function getDireccionPrincipal()
    {
        try {
            $userId = Session::get('user_id');
            
            $direccion = Direccion::where('usuario_id', $userId)
                ->whereRaw('principal = true')
                ->first();
            
            return response()->json([
                'success' => true,
                'data' => $direccion
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dirección',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar o crear dirección principal
     */
    public function updateDireccionPrincipal(Request $request)
    {
        if (!Session::get('user_authenticated', false)) {
            return back()->with('error', 'Debes iniciar sesión para actualizar tu dirección.');
        }

        // Validación
        $request->validate([
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:50',
            'codigo_postal_id' => 'required|exists:codigos_postales,id',
            'referencias' => 'nullable|string|max:500',
            'tipo' => 'required|in:casa,trabajo,otro',
        ]);

        try {
            $userId = Session::get('user_id');
            
            DB::beginTransaction();
            
            // Obtener datos del código postal
            $codigoPostal = CodigoPostal::findOrFail($request->codigo_postal_id);
            
            // Buscar dirección principal existente
            $direccion = Direccion::where('usuario_id', $userId)
                ->whereRaw('principal = true')
                ->first();
            
            $datosDir = [
                'usuario_id' => $userId,
                'calle' => $request->calle,
                'numero' => $request->numero,
                'codigo_postal_id' => $request->codigo_postal_id,
                'codigo_postal' => $codigoPostal->codigo_postal,
                'estado' => $codigoPostal->estado,
                'municipio' => $codigoPostal->municipio,
                'colonia' => $codigoPostal->colonia,
                'referencias' => $request->referencias,
                'tipo' => $request->tipo,
                'principal' => true,
                'actualizado_por' => $userId
            ];
            
            if ($direccion) {
                // Actualizar dirección existente
                $direccion->update($datosDir);
                $accion = 'update';
                $mensaje = 'Dirección actualizada exitosamente';
            } else {
                // Crear nueva dirección
                $direccion = Direccion::create($datosDir);
                $accion = 'create';
                $mensaje = 'Dirección creada exitosamente';
            }
            
            // Registrar en auditoría
            Auditoria::create([
                'tabla' => 'direcciones',
                'registro_id' => $direccion->id,
                'accion' => $accion,
                'usuario_id' => $userId,
                'cambios' => json_encode([
                    'direccion_completa' => $direccion->direccion_completa,
                    'tipo' => $request->tipo
                ])
            ]);
            
            DB::commit();
            
            return back()->with('success', "✅ $mensaje");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar dirección', [
                'user_id' => Session::get('user_id'),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', '❌ Error al actualizar dirección. Por favor, inténtalo más tarde.')->withInput();
        }
    }
}
