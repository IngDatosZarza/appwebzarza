<?php

namespace App\Console\Commands;

use App\Models\PromocionOppen;
use App\Services\OppenApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SincronizarPromocionesOppen extends Command
{
    protected $signature = 'oppen:sync-promociones';
    protected $description = 'Sincroniza las promociones desde la API de Oppen hacia la BD local';

    public function handle(): int
    {
        $this->info('Iniciando sincronización de promociones desde Oppen...');

        try {
            $api = app(OppenApiService::class);
            $promociones = $api->obtenerPromociones();

            if (empty($promociones)) {
                $this->warn('No se obtuvieron promociones de la API. Intentando obtener una por una...');
                $promociones = $this->obtenerPromocionesIndividualmente($api);
            }

            if (empty($promociones)) {
                $this->warn('No se encontraron promociones en la API de Oppen.');
                return self::SUCCESS;
            }

            $codigosRecibidos = [];
            $creadas = 0;
            $actualizadas = 0;

            foreach ($promociones as $data) {
                if (!isset($data['Code'])) {
                    continue;
                }

                $promo = PromocionOppen::sincronizarDesdeOppen($data);
                $codigosRecibidos[] = $data['Code'];

                if ($promo->wasRecentlyCreated) {
                    $creadas++;
                    $this->line("  + Creada: {$data['Name']} ({$data['Code']})");
                } else {
                    $actualizadas++;
                    $this->line("  ~ Actualizada: {$data['Name']} ({$data['Code']})");
                }
            }

            // Marcar como inactivas las que ya no vienen de la API
            $desactivadas = PromocionOppen::whereNotIn('oppen_code', $codigosRecibidos)
                ->where('activo', true)
                ->update(['activo' => false, 'ultima_sincronizacion' => now()]);

            $this->info("Sincronización completada:");
            $this->info("  Creadas: {$creadas}");
            $this->info("  Actualizadas: {$actualizadas}");
            $this->info("  Desactivadas: {$desactivadas}");

            Log::info('Sincronización de promociones Oppen completada', [
                'creadas' => $creadas,
                'actualizadas' => $actualizadas,
                'desactivadas' => $desactivadas,
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error en la sincronización: ' . $e->getMessage());
            Log::error('Error sincronizando promociones Oppen', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Intenta obtener promociones una por una (fallback si el listado no funciona).
     * Prueba códigos del 00001 al 00050.
     */
    private function obtenerPromocionesIndividualmente(OppenApiService $api): array
    {
        $promociones = [];

        for ($i = 1; $i <= 50; $i++) {
            $code = str_pad($i, 5, '0', STR_PAD_LEFT);
            $promo = $api->obtenerPromocion($code);

            if ($promo && isset($promo['Code'])) {
                $promociones[] = $promo;
                $this->line("  Encontrada promo individual: {$promo['Name']} ({$code})");
            }
        }

        return $promociones;
    }
}
