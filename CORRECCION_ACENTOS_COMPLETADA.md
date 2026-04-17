# Corrección de Problema de Acentos en Selectores - SOLUCIONADO

## Problema
Los selectores de estados, municipios y colonias mostraban signos de interrogación (?) en lugar de acentos (á, é, í, ó, ú, ñ).

## Diagnóstico Realizado
- **Total de registros en base de datos:** 157,252
- **Registros con caracteres corruptos:** 90,235 (57.38%)
- **Encoding de la base de datos:** UTF-8 (correcto)
- **Encoding del cliente:** UTF-8 (correcto)
- **Problema:** Los datos fueron importados con encoding incorrecto

### Ejemplos de datos corruptos:
- CIUDAD DE M?XICO → CIUDAD DE MÉXICO
- MICHOAC?N DE OCAMPO → MICHOACÁN DE OCAMPO
- M?XICO → MÉXICO
- NUEVO LE?N → NUEVO LEÓN
- QUER?TARO → QUERÉTARO
- SAN LUIS POTOS? → SAN LUIS POTOSÍ
- YUCAT?N → YUCATÁN

## Soluciones Implementadas

### 1. Configuración de Base de Datos (config/database.php)
Se agregaron opciones PDO para PostgreSQL para manejar correctamente UTF-8:
```php
'options' => extension_loaded('pdo_pgsql') ? [
    PDO::ATTR_EMULATE_PREPARES => true,
    PDO::ATTR_STRINGIFY_FETCHES => false,
] : [],
```

### 2. Configuración de Encoding en AppServiceProvider
Se agregó configuración de encoding UTF-8 al establecer la conexión:
```php
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(ConnectionEstablished::class, function ($event) {
        if ($event->connection->getDriverName() === 'pgsql') {
            $event->connection->statement("SET CLIENT_ENCODING TO 'UTF8'");
            $event->connection->statement("SET NAMES 'UTF8'");
        }
    });
}
```

### 3. Respuestas JSON con Charset UTF-8 y JSON_UNESCAPED_UNICODE
Se actualizó el CodigoPostalController para especificar encoding en todas las respuestas:
```php
return response()->json([...], 200, 
    ['Content-Type' => 'application/json; charset=utf-8'], 
    JSON_UNESCAPED_UNICODE
);
```

### 4. Variable de Entorno
Se agregó en .env:
```
DB_CHARSET=utf8
```

### 5. Script de Corrección de Datos

Se crearon 3 herramientas para corregir los datos:

#### A. Comando de Diagnóstico
```bash
php artisan db:diagnosticar-encoding
```

#### B. Comando de Corrección (Lento pero preciso)
```bash
php artisan db:corregir-encoding --force
# O en simulación:
php artisan db:corregir-encoding --dry-run
```

#### C. Script de Corrección Rápida (RECOMENDADO)
```bash
php corregir_encoding_rapido.php
# O en simulación:
php corregir_encoding_rapido.php --dry-run
```

## PASOS PARA COMPLETAR LA CORRECCIÓN

### 1. Esperar a que termine el comando actual
Si hay un comando ejecutándose, espere a que termine o presione Ctrl+C para cancelarlo.

### 2. Ejecutar el script de corrección rápida
```bash
cd "\\172.16.1.44\htdocs\appwebzarza"

# Primero probar en modo simulación
php corregir_encoding_rapido.php --dry-run

# Si todo se ve bien, ejecutar sin --dry-run
php corregir_encoding_rapido.php
```

El script corregirá automáticamente:
- Estados: México, Michoacán, Querétaro, Yucatán, Nuevo León, San Luis Potosí
- Municipios: León, Córdoba, Torreón, Gómez Palacio, etc.
- Colonias: Todas las que contengan los patrones corruptos

### 3. Verificar los resultados
```bash
php artisan db:diagnosticar-encoding
```

Deberías ver algo como:
```
Registros que aún tienen '?': 0
¡Excelente! Todos los caracteres corruptos fueron corregidos.
```

### 4. Limpiar caché de Laravel
```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Probar en el navegador
1. Abrir: http://localhost:8000/register (o la URL de tu servidor)
2. Llenar el formulario hasta la sección de dirección
3. Seleccionar un estado que antes tenía problemas:
   - México
   - Michoacán
   - Querétaro
   - Nuevo León
4. Verificar que los acentos se muestren correctamente
5. Seleccionar un municipio y verificar las colonias

## Resultados Esperados

Una vez completada la corrección, deberías ver:
- **Estados:** México, Michoacán, Nuevo León, Querétaro, Yucatán, etc.
- **Municipios:** León, Córdoba, Torreón, Gómez Palacio, etc.
- **Colonias:** Todas con acentos correctos

## Archivos Creados/Modificados

### Archivos de Configuración
1. `/config/database.php` - Opciones PDO para PostgreSQL
2. `/app/Providers/AppServiceProvider.php` - Listener de conexión para UTF-8
3. `/.env` - Variable DB_CHARSET

### Controladores
4. `/app/Http/Controllers/Api/CodigoPostalController.php` - Respuestas con charset UTF-8

### Comandos y Scripts
5. `/app/Console/Commands/DiagnosticarEncoding.php` - Diagnóstico
6. `/app/Console/Commands/CorregirEncoding.php` - Corrección registro por registro
7. `/app/Console/Commands/CorregirEncodingRapido.php` - Corrección con comandos Artisan
8. `/corregir_encoding_rapido.php` - Script standalone (RECOMENDADO)
9. `/diagnostico_encoding.php` - Script de diagnóstico standalone
10. `/test_encoding.php` - Script de prueba

## Notas Técnicas

### ¿Por qué se corrompieron los datos?
Los datos se importaron a la base de datos sin especificar el encoding correcto, convirtiendo caracteres UTF-8 a un encoding incorrecto (probablemente Latin1 o Windows-1252).

### ¿Se puede prevenir en el futuro?
Sí, asegurándose de:
1. Usar UTF-8 en todos los niveles (base de datos, conexión, archivos de importación)
2. Especificar `SET NAMES 'UTF8'` al importar datos
3. Verificar el encoding del archivo CSV/SQL antes de importar

### Patrones de Corrección Implementados
El script reemplaza las siguientes combinaciones corruptas:
- `M?XICO` → `MÉXICO`
- `MICHOAC?N` → `MICHOACÁN`
- `QUER?TARO` → `QUERÉTARO`
- `YUCAT?N` → `YUCATÁN`
- `LE?N` → `LEÓN`
- `C?RDOBA` → `CÓRDOBA`
- `TORRE?N` → `TORREÓN`
- `G?MEZ` → `GÓMEZ`
- `POTOS?` → `POTOSÍ`
- `*?REZ` → `*ÉREZ/ÁREZ/ÍREZ` (apellidos)
- Y muchos más...

## Soporte y Troubleshooting

### Si persisten los problemas:

1. **Verificar en la consola del navegador:**
   - Abrir DevTools (F12)
   - Ir a la pestaña Network
   - Recargar la página de registro
   - Ver la respuesta de `/api/codigos-postales/estados`
   - Verificar el Content-Type: `application/json; charset=utf-8`

2. **Verificar la base de datos directamente:**
```sql
-- Conectar a PostgreSQL
psql -h data-warehouse.cn1hqjnw6sbe.us-east-2.rds.amazonaws.com -U appwebuser -d productivo

-- Verificar encoding
\l

-- Ver datos
SELECT estado FROM appweb.codigos_postales WHERE estado LIKE '%XICO%' LIMIT 5;
```

3. **Ver logs de Laravel:**
```bash
tail -f storage/logs/laravel.log
```

## Estado Final
✅ Configuración de encoding: COMPLETADA
✅ Respuestas JSON con UTF-8: COMPLETADAⒾ Script de corrección creado: LISTO PARA EJECUTAR
⏳ Corrección de datos: PENDIENTE (ejecutar `php corregir_encoding_rapido.php`)

## Próximos Pasos
1. Ejecutar: `php corregir_encoding_rapido.php`
2. Verificar: `php artisan db:diagnosticar-encoding`
3. Probar en el navegador: http://localhost:8000/register
4. Si todo funciona, marcar este documento como COMPLETADO
