# 🐘 Implementación de Geolocalización en PostgreSQL

## 📋 Configuración Actual

Tu aplicación ya está configurada para usar **PostgreSQL**:

```env
DB_CONNECTION=pgsql
DB_HOST=data-warehouse.cn1hqjnw6sbe.us-east-2.rds.amazonaws.com
DB_PORT=5432
DB_DATABASE=productivo
DB_USERNAME=appwebuser
DB_SCHEMA=appweb
```

## 🚀 Instalación - Método 1: Laravel Migrations (Recomendado)

### Paso 1: Ejecutar la Migración

```bash
php artisan migrate
```

La migración agregará automáticamente:
- ✅ 6 columnas nuevas a la tabla `usuarios`
- ✅ 3 índices optimizados para búsquedas
- ✅ Validaciones de tipo de dato

### Paso 2: Verificar la Migración

```bash
php artisan migrate:status
```

Deberías ver:
```
✓ 2024_12_01_000001_add_location_fields_to_usuarios_table
```

### Paso 3: Verificar en la Base de Datos

Conéctate a PostgreSQL y ejecuta:

```sql
\d appweb.usuarios
```

O desde Laravel Tinker:

```bash
php artisan tinker
```

```php
DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'appweb' AND table_name = 'usuarios' AND column_name IN ('latitud', 'longitud', 'ciudad', 'estado', 'pais', 'ubicacion_capturada_at')");
```

## 🛠️ Instalación - Método 2: SQL Directo

Si prefieres ejecutar el SQL directamente en PostgreSQL:

### Opción A: Usando psql

```bash
psql -h data-warehouse.cn1hqjnw6sbe.us-east-2.rds.amazonaws.com \
     -U appwebuser \
     -d productivo \
     -f database/sql/add_location_fields_postgres.sql
```

### Opción B: Desde pgAdmin o DBeaver

1. Abre el archivo `database/sql/add_location_fields_postgres.sql`
2. Copia el contenido
3. Pégalo en tu cliente SQL
4. Ejecuta el script

## 📊 Estructura de Datos Creada

### Columnas Agregadas

| Columna | Tipo | Nullable | Default | Descripción |
|---------|------|----------|---------|-------------|
| `latitud` | NUMERIC(10,7) | Sí | NULL | Coordenada de latitud (-90 a 90) |
| `longitud` | NUMERIC(10,7) | Sí | NULL | Coordenada de longitud (-180 a 180) |
| `ciudad` | VARCHAR(100) | Sí | NULL | Nombre de la ciudad |
| `estado` | VARCHAR(100) | Sí | NULL | Nombre del estado/provincia |
| `pais` | VARCHAR(100) | Sí | 'México' | Nombre del país |
| `ubicacion_capturada_at` | TIMESTAMP | Sí | NULL | Fecha/hora de captura |

### Índices Creados

```sql
-- Búsquedas por ciudad y estado
CREATE INDEX idx_usuarios_ubicacion 
ON appweb.usuarios (ciudad, estado) 
WHERE ciudad IS NOT NULL;

-- Búsquedas por país
CREATE INDEX idx_usuarios_pais 
ON appweb.usuarios (pais) 
WHERE pais IS NOT NULL;

-- Ordenamiento por fecha de captura
CREATE INDEX idx_usuarios_ubicacion_fecha 
ON appweb.usuarios (ubicacion_capturada_at DESC) 
WHERE ubicacion_capturada_at IS NOT NULL;
```

## 🔍 Consultas Útiles en PostgreSQL

### Ver Usuarios con Ubicación

```sql
SELECT 
    id,
    CONCAT(nombres, ' ', apellido_paterno) as nombre_completo,
    email,
    ciudad,
    estado,
    pais,
    ROUND(latitud::numeric, 4) as latitud,
    ROUND(longitud::numeric, 4) as longitud,
    TO_CHAR(ubicacion_capturada_at, 'DD/MM/YYYY HH24:MI') as fecha_captura
FROM appweb.usuarios
WHERE latitud IS NOT NULL
ORDER BY ubicacion_capturada_at DESC
LIMIT 10;
```

### Estadísticas por Ciudad

```sql
SELECT 
    ciudad,
    estado,
    COUNT(*) as total_usuarios,
    ROUND(AVG(latitud::numeric), 4) as latitud_promedio,
    ROUND(AVG(longitud::numeric), 4) as longitud_promedio
FROM appweb.usuarios
WHERE ciudad IS NOT NULL
GROUP BY ciudad, estado
ORDER BY total_usuarios DESC
LIMIT 20;
```

### Estadísticas por Estado

```sql
SELECT 
    estado,
    COUNT(*) as total_usuarios,
    COUNT(DISTINCT ciudad) as ciudades_distintas
FROM appweb.usuarios
WHERE estado IS NOT NULL
GROUP BY estado
ORDER BY total_usuarios DESC;
```

### Usuarios Recientes con Ubicación

```sql
SELECT 
    COUNT(*) FILTER (WHERE ubicacion_capturada_at >= NOW() - INTERVAL '24 hours') as ultimas_24h,
    COUNT(*) FILTER (WHERE ubicacion_capturada_at >= NOW() - INTERVAL '7 days') as ultimos_7d,
    COUNT(*) FILTER (WHERE ubicacion_capturada_at >= NOW() - INTERVAL '30 days') as ultimos_30d,
    COUNT(*) as total_con_ubicacion
FROM appweb.usuarios
WHERE latitud IS NOT NULL;
```

### Mapa de Calor (Top 10 Ciudades)

```sql
SELECT 
    ciudad,
    estado,
    COUNT(*) as usuarios,
    ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) as porcentaje
FROM appweb.usuarios
WHERE ciudad IS NOT NULL
GROUP BY ciudad, estado
ORDER BY usuarios DESC
LIMIT 10;
```

## 📈 Uso desde Laravel/Eloquent

### Obtener Usuarios con Ubicación

```php
use App\Models\Usuario;

// Usuarios con ubicación registrada
$usuariosConUbicacion = Usuario::whereNotNull('latitud')
    ->whereNotNull('longitud')
    ->orderByDesc('ubicacion_capturada_at')
    ->get();

// Usuarios de una ciudad específica
$usuariosEnGuadalajara = Usuario::where('ciudad', 'ILIKE', '%Guadalajara%')->get();

// Usuarios de un estado específico
$usuariosEnJalisco = Usuario::where('estado', 'ILIKE', '%Jalisco%')->get();

// Contar usuarios por ciudad (usando Query Builder)
$estadisticasCiudades = DB::table('usuarios')
    ->select('ciudad', 'estado', DB::raw('COUNT(*) as total'))
    ->whereNotNull('ciudad')
    ->groupBy('ciudad', 'estado')
    ->orderByDesc('total')
    ->get();
```

### Guardar Ubicación

```php
$usuario = Usuario::find($userId);
$usuario->update([
    'latitud' => -20.6736,
    'longitud' => -103.3440,
    'ciudad' => 'Guadalajara',
    'estado' => 'Jalisco',
    'pais' => 'México',
    'ubicacion_capturada_at' => now()
]);
```

### Scope para Usuarios con Ubicación

Agrega esto al modelo `Usuario.php`:

```php
/**
 * Scope para usuarios con ubicación registrada
 */
public function scopeConUbicacion($query)
{
    return $query->whereNotNull('latitud')
                 ->whereNotNull('longitud');
}

/**
 * Scope para usuarios de una ciudad
 */
public function scopeDeCiudad($query, $ciudad)
{
    return $query->where('ciudad', 'ILIKE', "%{$ciudad}%");
}

/**
 * Scope para usuarios de un estado
 */
public function scopeDeEstado($query, $estado)
{
    return $query->where('estado', 'ILIKE', "%{$estado}%");
}
```

Uso:

```php
// Usuarios con ubicación
$usuarios = Usuario::conUbicacion()->get();

// Usuarios de Guadalajara
$usuarios = Usuario::deCiudad('Guadalajara')->get();

// Usuarios de Jalisco
$usuarios = Usuario::deEstado('Jalisco')->get();

// Combinar scopes
$usuarios = Usuario::conUbicacion()
    ->deEstado('Jalisco')
    ->orderByDesc('ubicacion_capturada_at')
    ->get();
```

## 🌍 Funcionalidades Geoespaciales Avanzadas (Opcional)

Si necesitas calcular distancias entre ubicaciones, puedes usar la extensión **PostGIS**:

### Instalación de PostGIS

```sql
-- Requiere permisos de superusuario
CREATE EXTENSION IF NOT EXISTS postgis;
```

### Agregar Columna Geoespacial

```sql
-- Agregar columna GEOGRAPHY
ALTER TABLE appweb.usuarios 
ADD COLUMN ubicacion_geo GEOGRAPHY(POINT, 4326);

-- Crear índice espacial
CREATE INDEX idx_usuarios_ubicacion_geo 
ON appweb.usuarios USING GIST (ubicacion_geo);

-- Actualizar ubicaciones existentes
UPDATE appweb.usuarios
SET ubicacion_geo = ST_SetSRID(ST_MakePoint(longitud, latitud), 4326)::geography
WHERE latitud IS NOT NULL AND longitud IS NOT NULL;
```

### Calcular Distancias

```sql
-- Usuarios dentro de 10km de una ubicación
SELECT 
    id,
    nombres,
    ciudad,
    ST_Distance(
        ubicacion_geo,
        ST_SetSRID(ST_MakePoint(-103.3440, 20.6736), 4326)::geography
    ) / 1000 as distancia_km
FROM appweb.usuarios
WHERE ST_DWithin(
    ubicacion_geo,
    ST_SetSRID(ST_MakePoint(-103.3440, 20.6736), 4326)::geography,
    10000  -- 10km en metros
)
ORDER BY distancia_km;
```

### Desde Laravel con PostGIS

```php
// Usuarios dentro de 10km de una coordenada
$latitud = 20.6736;
$longitud = -103.3440;
$radioKm = 10;

$usuarios = DB::select("
    SELECT 
        id,
        nombres,
        apellido_paterno,
        ciudad,
        ST_Distance(
            ubicacion_geo,
            ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography
        ) / 1000 as distancia_km
    FROM appweb.usuarios
    WHERE ST_DWithin(
        ubicacion_geo,
        ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
        ?
    )
    ORDER BY distancia_km
", [$longitud, $latitud, $longitud, $latitud, $radioKm * 1000]);
```

## 🔧 Mantenimiento y Optimización

### Analizar Rendimiento de Índices

```sql
-- Ver estadísticas de uso de índices
SELECT 
    schemaname,
    tablename,
    indexname,
    idx_scan as veces_usado,
    idx_tup_read as tuplas_leidas,
    idx_tup_fetch as tuplas_devueltas
FROM pg_stat_user_indexes
WHERE schemaname = 'appweb'
AND tablename = 'usuarios'
AND indexname LIKE '%ubicacion%';
```

### Actualizar Estadísticas

```sql
-- Actualizar estadísticas de la tabla para optimizar consultas
ANALYZE appweb.usuarios;
```

### Reindexar (si es necesario)

```sql
-- Reindexar todos los índices de ubicación
REINDEX INDEX CONCURRENTLY appweb.idx_usuarios_ubicacion;
REINDEX INDEX CONCURRENTLY appweb.idx_usuarios_pais;
REINDEX INDEX CONCURRENTLY appweb.idx_usuarios_ubicacion_fecha;
```

## 🐛 Solución de Problemas

### Error: Schema "appweb" no existe

```sql
-- Crear el schema si no existe
CREATE SCHEMA IF NOT EXISTS appweb;
```

### Error: Permisos insuficientes

```sql
-- Otorgar permisos al usuario
GRANT ALL PRIVILEGES ON SCHEMA appweb TO appwebuser;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA appweb TO appwebuser;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA appweb TO appwebuser;
```

### Verificar Conexión desde Laravel

```bash
php artisan tinker
```

```php
DB::connection()->getPdo();
echo "Conectado exitosamente a PostgreSQL!";
```

## 📊 Monitoreo

### Dashboard de Ubicaciones

```sql
-- Vista resumen para dashboard
SELECT 
    COUNT(*) as total_usuarios,
    COUNT(*) FILTER (WHERE latitud IS NOT NULL) as con_ubicacion,
    COUNT(*) FILTER (WHERE latitud IS NULL) as sin_ubicacion,
    ROUND(
        (COUNT(*) FILTER (WHERE latitud IS NOT NULL)::numeric / COUNT(*)::numeric * 100), 
        2
    ) as porcentaje_con_ubicacion,
    COUNT(DISTINCT ciudad) as ciudades_distintas,
    COUNT(DISTINCT estado) as estados_distintos,
    COUNT(*) FILTER (
        WHERE ubicacion_capturada_at >= NOW() - INTERVAL '24 hours'
    ) as ubicaciones_hoy
FROM appweb.usuarios;
```

## ✅ Checklist de Implementación

- [ ] Ejecutar migración de Laravel o script SQL
- [ ] Verificar que las columnas se crearon correctamente
- [ ] Verificar que los índices están activos
- [ ] Probar la captura de ubicación desde el navegador
- [ ] Verificar que los datos se guardan en PostgreSQL
- [ ] Ejecutar `ANALYZE` para actualizar estadísticas
- [ ] Configurar monitoring de ubicaciones
- [ ] (Opcional) Instalar PostGIS para funcionalidades geoespaciales

---

**Base de datos:** PostgreSQL en AWS RDS  
**Schema:** `appweb`  
**Tabla:** `usuarios`  
**Nuevas columnas:** 6 (latitud, longitud, ciudad, estado, pais, ubicacion_capturada_at)  
**Índices:** 3 (ubicacion, pais, ubicacion_fecha)
