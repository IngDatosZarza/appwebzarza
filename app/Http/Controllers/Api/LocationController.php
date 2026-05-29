<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UbicacionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Guardar la ubicación del usuario (para fines de marketing)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'precision' => 'nullable|numeric|min:0',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'evento' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de ubicación inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Detectar información del dispositivo
            $userAgent = $request->header('User-Agent', '');
            $dispositivo = $this->detectarDispositivo($userAgent);

            // Verificar si es la primera visita de esta sesión
            $sessionId = session()->getId();
            $esPrimeraVisita = !UbicacionUsuario::where('session_id', $sessionId)->exists();

            // Crear registro de ubicación
            $ubicacion = UbicacionUsuario::create([
                'usuario_id' => Auth::id(), // Null si no está autenticado
                'latitud' => (float) $request->latitud,
                'longitud' => (float) $request->longitud,
                'precision' => $request->precision ? (float) $request->precision : null,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'pais' => $request->pais ?? 'México',
                'codigo_postal' => $request->codigo_postal,
                'dispositivo' => $dispositivo,
                'navegador' => $this->detectarNavegador($userAgent),
                'sistema_operativo' => $this->detectarSO($userAgent),
                'user_agent' => $userAgent,
                'ip_address' => $request->ip(),
                'pagina_origen' => $request->header('Referer') ?? $request->input('pagina_origen'),
                'evento' => $request->evento ?? 'navegacion',
                'session_id' => $sessionId,
                'es_primera_visita' => (bool) $esPrimeraVisita,
                'metadata' => $request->metadata,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación registrada exitosamente',
                'data' => [
                    'id' => $ubicacion->id,
                    'latitud' => $ubicacion->latitud,
                    'longitud' => $ubicacion->longitud,
                    'ciudad' => $ubicacion->ciudad,
                    'estado' => $ubicacion->estado,
                    'pais' => $ubicacion->pais,
                    'dispositivo' => $ubicacion->dispositivo,
                    'es_primera_visita' => $ubicacion->es_primera_visita,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la ubicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener las ubicaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $ubicaciones = UbicacionUsuario::deUsuario(Auth::id())
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $ubicaciones
        ]);
    }

    /**
     * Obtener la última ubicación del usuario
     */
    public function show()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $ubicacion = UbicacionUsuario::ultimaDeUsuario(Auth::id());

        if ($ubicacion) {
            return response()->json([
                'success' => true,
                'data' => $ubicacion
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró información de ubicación'
        ], 404);
    }

    /**
     * Obtener estadísticas de ubicaciones (solo admin)
     */
    public function stats()
    {
        if (!Auth::check() || Auth::user()->rol !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $estadisticas = UbicacionUsuario::estadisticas();

        // Top ciudades
        $topCiudades = UbicacionUsuario::query()
            ->select('ciudad', 'estado', \DB::raw('COUNT(*) as total'))
            ->whereNotNull('ciudad')
            ->groupBy('ciudad', 'estado')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Distribución por dispositivo
        $porDispositivo = UbicacionUsuario::query()
            ->select('dispositivo', \DB::raw('COUNT(*) as total'))
            ->whereNotNull('dispositivo')
            ->groupBy('dispositivo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'estadisticas' => $estadisticas,
                'top_ciudades' => $topCiudades,
                'por_dispositivo' => $porDispositivo,
            ]
        ]);
    }

    /**
     * Detectar tipo de dispositivo desde User Agent
     */
    private function detectarDispositivo($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i', $userAgent)) {
            if (preg_match('/(ipad|tablet)/i', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }

    /**
     * Detectar navegador desde User Agent
     */
    private function detectarNavegador($userAgent)
    {
        if (preg_match('/MSIE|Trident/i', $userAgent)) return 'Internet Explorer';
        if (preg_match('/Edge/i', $userAgent)) return 'Microsoft Edge';
        if (preg_match('/Chrome/i', $userAgent)) return 'Google Chrome';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Firefox/i', $userAgent)) return 'Mozilla Firefox';
        if (preg_match('/Opera|OPR/i', $userAgent)) return 'Opera';
        
        return 'Desconocido';
    }

    /**
     * Detectar sistema operativo desde User Agent
     */
    private function detectarSO($userAgent)
    {
        if (preg_match('/windows nt 10/i', $userAgent)) return 'Windows 10/11';
        if (preg_match('/windows nt 6.3/i', $userAgent)) return 'Windows 8.1';
        if (preg_match('/windows nt 6.2/i', $userAgent)) return 'Windows 8';
        if (preg_match('/windows nt 6.1/i', $userAgent)) return 'Windows 7';
        if (preg_match('/windows/i', $userAgent)) return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'Mac OS';
        if (preg_match('/linux/i', $userAgent)) return 'Linux';
        if (preg_match('/android/i', $userAgent)) return 'Android';
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) return 'iOS';
        
        return 'Desconocido';
    }
}
