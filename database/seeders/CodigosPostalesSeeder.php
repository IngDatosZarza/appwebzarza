<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CodigoPostal;
use Illuminate\Support\Facades\DB;

class CodigosPostalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder importa los códigos postales de México desde el archivo
     * proporcionado por Correos de México:
     * https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx
     * 
     * Formato del archivo (separado por |):
     * d_codigo|d_asenta|d_tipo_asenta|D_mnpio|d_estado|d_ciudad|d_CP|...
     * 01000|San Ángel|Colonia|Álvaro Obregón|Ciudad de México|...
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando importación de códigos postales...');
        
        // Buscar el archivo en storage/app
        $file = storage_path('app/codigos_postales.txt');
        
        if (!file_exists($file)) {
            $this->command->error('❌ Archivo no encontrado en: ' . $file);
            $this->command->warn('');
            $this->command->warn('Instrucciones:');
            $this->command->warn('1. Descarga el archivo desde: https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx');
            $this->command->warn('2. Guárdalo como: storage/app/codigos_postales.txt');
            $this->command->warn('3. Ejecuta nuevamente: php artisan db:seed --class=CodigosPostalesSeeder');
            return;
        }
        
        $this->command->info('📁 Archivo encontrado. Limpiando tabla...');
        DB::table('codigos_postales')->truncate();
        
        $this->command->info('📖 Leyendo archivo...');
        $handle = fopen($file, 'r');
        $batch = [];
        $count = 0;
        $lineNumber = 0;
        
        // Leer primera línea (puede ser encabezado)
        $firstLine = fgets($handle);
        $lineNumber++;
        
        // Si la primera línea no es un CP válido, es encabezado - saltarla
        if (!preg_match('/^\d{5}\|/', $firstLine)) {
            $this->command->info('⏭️  Saltando línea de encabezado');
        } else {
            // Es un registro válido, procesarlo
            rewind($handle);
        }
        
        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $data = explode('|', trim($line));
            
            // Formato: d_codigo|d_asenta|d_tipo_asenta|D_mnpio|d_estado|d_ciudad|...
            // Índices: 0=CP, 1=Colonia, 2=Tipo, 3=Municipio, 4=Estado, 5=Ciudad, 13=Zona
            
            // Validar que tenga al menos los campos mínimos requeridos
            if (count($data) >= 5 && preg_match('/^\d{5}$/', $data[0])) {
                $batch[] = [
                    'codigo_postal' => $data[0],                                    // d_codigo
                    'colonia' => mb_strtoupper(trim($data[1] ?? '')),              // d_asenta
                    'tipo_asentamiento' => mb_strtoupper(trim($data[2] ?? '')),    // d_tipo_asenta
                    'municipio' => mb_strtoupper(trim($data[3] ?? '')),            // D_mnpio
                    'estado' => mb_strtoupper(trim($data[4] ?? '')),               // d_estado
                    'ciudad' => !empty($data[5]) ? mb_strtoupper(trim($data[5])) : null, // d_ciudad
                    'zona' => !empty($data[13]) ? mb_strtoupper(trim($data[13])) : null, // d_zona
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $count++;
                
                // Insertar en lotes de 1000 para mejor performance
                if (count($batch) >= 1000) {
                    DB::table('codigos_postales')->insert($batch);
                    $batch = [];
                    $this->command->info("✅ Insertados $count registros... (línea $lineNumber)");
                }
            } else {
                // Línea inválida - registrar en log pero continuar
                if ($count < 10) { // Solo mostrar los primeros 10 errores
                    $this->command->warn("⚠️  Línea $lineNumber ignorada (formato inválido)");
                }
            }
        }
        
        // Insertar el último lote
        if (!empty($batch)) {
            DB::table('codigos_postales')->insert($batch);
            $this->command->info("✅ Insertados últimos " . count($batch) . " registros");
        }
        
        fclose($handle);
        
        $this->command->info('');
        $this->command->info("🎉 ¡Importación completada!");
        $this->command->info("📊 Total de códigos postales importados: " . number_format($count));
        
        // Mostrar estadísticas
        $estados = DB::table('codigos_postales')->distinct('estado')->count('estado');
        $municipios = DB::table('codigos_postales')->distinct('municipio')->count('municipio');
        
        $this->command->info("🗺️  Estados: $estados");
        $this->command->info("🏘️  Municipios: $municipios");
        $this->command->info('');
    }
}
