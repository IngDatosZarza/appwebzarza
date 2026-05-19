<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromocionOppen;
use App\Services\OppenApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminPromosOppenController extends Controller
{
    /**
     * Vista principal: listado de promociones sincronizadas desde Oppen.
     */
    public function index()
    {
        $promociones = PromocionOppen::orderByDesc('ultima_sincronizacion')
            ->orderBy('nombre')
            ->get();

        $stats = [
            'total'    => $promociones->count(),
            'activas'  => $promociones->where('activo', true)->count(),
            'vigentes' => $promociones->filter(fn ($p) => $p->activo && $p->fecha_inicio <= now() && $p->fecha_fin >= now())->count(),
            'inactivas' => $promociones->where('activo', false)->count(),
            'ultima_sync' => $promociones->max('ultima_sincronizacion'),
        ];

        return view('admin.promos-oppen.index', compact('promociones', 'stats'));
    }

    /**
     * Detalle de una promoción (datos raw de la API).
     */
    public function show($id)
    {
        $promocion = PromocionOppen::findOrFail($id);

        return view('admin.promos-oppen.show', compact('promocion'));
    }

    /**
     * Forzar sincronización manual desde la API Oppen.
     */
    public function sync(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        try {
            $service = app(OppenApiService::class);
            $promos = $service->obtenerPromociones();

            if (empty($promos)) {
                return back()->with('warning', 'La API de Oppen no devolvió promociones. Verifica la conexión.');
            }

            $syncedCodes = [];
            $errores = [];

            foreach ($promos as $data) {
                try {
                    $code = $data['Code'] ?? null;
                    if (!$code) continue;

                    PromocionOppen::sincronizarDesdeOppen($data);
                    $syncedCodes[] = $code;
                } catch (\Exception $e) {
                    $errores[] = ($data['Code'] ?? '?') . ': ' . $e->getMessage();
                }
            }

            // Desactivar las que ya no están en la API
            if (!empty($syncedCodes)) {
                PromocionOppen::whereNotIn('oppen_code', $syncedCodes)
                    ->whereRaw('"activo" = true')
                    ->update(['activo' => \DB::raw('false'), 'updated_at' => now()]);
            }

            Log::info('Sincronización manual de promociones Oppen', [
                'admin_id' => $admin->id ?? null,
                'sincronizadas' => count($syncedCodes),
                'errores' => count($errores),
            ]);

            $msg = count($syncedCodes) . ' promociones sincronizadas correctamente.';
            if (!empty($errores)) {
                $msg .= ' ' . count($errores) . ' errores: ' . implode('; ', array_slice($errores, 0, 3));
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Error en sincronización manual de promociones', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error al sincronizar: ' . $e->getMessage());
        }
    }
}
