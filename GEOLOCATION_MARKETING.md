# 📊 Sistema de Tracking de Ubicación para Marketing

## 🎯 Concepto

Este sistema almacena las ubicaciones de los usuarios en una **tabla separada** específicamente diseñada para análisis de marketing. A diferencia de guardar la ubicación en el perfil del usuario, este enfoque permite:

- ✅ **Historial completo** de ubicaciones a lo largo del tiempo
- ✅ **Tracking de visitantes anónimos** (no autenticados)
- ✅ **Análisis de comportamiento** por dispositivo, navegador y SO
- ✅ **Segmentación geográfica** precisa para campañas
- ✅ **Datos de contexto** (página origen, evento, sesión)
- ✅ **Primeras visitas vs. visitas recurrentes**
- ✅ **Separación clara** entre datos personales y datos de análisis

## 📋 Estructura de la Base de Datos

### Tabla: `ubicaciones_usuarios`

```sql
CREATE TABLE appweb.ubicaciones_usuarios (
    id BIGSERIAL PRIMARY KEY,
    
    -- Usuario (NULL para anónimos)
    usuario_id BIGINT,
    
    -- Ubicación GPS
    latitud NUMERIC(10, 7),
    longitud NUMERIC(10, 7),
    precision NUMERIC(10, 2),
    ciudad VARCHAR(100),
    estado VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'México',
    codigo_postal VARCHAR(10),
    
    -- Datos del dispositivo
    dispositivo VARCHAR(50), -- mobile, tablet, desktop
    navegador VARCHAR(100),
    sistema_operativo VARCHAR(100),
    user_agent VARCHAR(500),
    ip_address INET,
    
    -- Contexto
    pagina_origen VARCHAR(255),
    evento VARCHAR(100), -- registro, compra, navegacion, etc.
    session_id VARCHAR(255),
    es_primera_visita BOOLEAN,
    metadata JSONB,
    
    -- Timestamps
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🚀 Instalación

### Opción 1: Migración de Laravel (Recomendado)

```bash
php artisan migrate
```

### Opción 2: SQL Directo

```bash
psql -h data-warehouse.cn1hqjnw6sbe.us-east-2.rds.amazonaws.com \
     -U appwebuser \
     -d productivo \
     -f database/sql/add_location_fields_postgres.sql
```

## 📡 API Endpoints

### 1. Guardar Ubicación (Público)

```http
POST /api/v1/location
```

**Body:**
```json
{
  "latitud": 20.6736,
  "longitud": -103.3440,
  "precision": 10.5,
  "ciudad": "Guadalajara",
  "estado": "Jalisco",
  "pais": "México",
  "codigo_postal": "44100",
  "evento": "navegacion",
  "metadata": {
    "referrer": "google",
    "campaign": "verano2024"
  }
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Ubicación registrada exitosamente",
  "data": {
    "id": 123,
    "latitud": "20.6736000",
    "longitud": "-103.3440000",
    "ciudad": "Guadalajara",
    "estado": "Jalisco",
    "pais": "México",
    "dispositivo": "mobile",
    "es_primera_visita": true
  }
}
```

### 2. Obtener Última Ubicación (Autenticado)

```http
GET /api/v1/location
Authorization: Bearer {token}
```

### 3. Obtener Historial de Ubicaciones (Autenticado)

```http
GET /api/v1/locations?per_page=20
Authorization: Bearer {token}
```

### 4. Estadísticas de Ubicación (Solo Admin)

```http
GET /api/v1/admin/reports/locations
Authorization: Bearer {token}
```

## 💻 Uso desde Laravel

### Crear Registro de Ubicación

```php
use App\Models\UbicacionUsuario;

$ubicacion = UbicacionUsuario::create([
    'usuario_id' => auth()->id(), // NULL para anónimos
    'latitud' => 20.6736,
    'longitud' => -103.3440,
    'ciudad' => 'Guadalajara',
    'estado' => 'Jalisco',
    'pais' => 'México',
    'dispositivo' => 'mobile',
    'evento' => 'compra',
]);
```

### Consultar Ubicaciones

```php
// Última ubicación de un usuario
$ultimaUbicacion = UbicacionUsuario::ultimaDeUsuario($userId);

// Historial de ubicaciones
$ubicaciones = UbicacionUsuario::deUsuario($userId)
    ->orderByDesc('created_at')
    ->get();

// Ubicaciones de una ciudad
$enGuadalajara = UbicacionUsuario::deCiudad('Guadalajara')->get();

// Ubicaciones anónimas
$anonimas = UbicacionUsuario::anonimas()->get();

// Por evento
$compras = UbicacionUsuario::porEvento('compra')->get();

// Rango de fechas
$ubicaciones = UbicacionUsuario::entreFechas(
    now()->subDays(7),
    now()
)->get();

// Primeras visitas
$primerasVisitas = UbicacionUsuario::primerasVisitas()->get();
```

### Relaciones en Usuario

```php
// Obtener todas las ubicaciones de un usuario
$usuario = Usuario::find($id);
$ubicaciones = $usuario->ubicaciones;

// Última ubicación
$ultimaUbicacion = $usuario->ultimaUbicacion;
```

## 📊 Consultas SQL para Marketing

### 1. Top 10 Ciudades

```sql
SELECT 
    ciudad,
    estado,
    COUNT(*) as total_registros,
    COUNT(DISTINCT usuario_id) as usuarios_unicos,
    COUNT(*) FILTER (WHERE es_primera_visita) as primeras_visitas
FROM appweb.ubicaciones_usuarios
WHERE ciudad IS NOT NULL
GROUP BY ciudad, estado
ORDER BY total_registros DESC
LIMIT 10;
```

### 2. Análisis por Dispositivo

```sql
SELECT 
    dispositivo,
    COUNT(*) as total,
    COUNT(DISTINCT usuario_id) as usuarios_unicos,
    ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) as porcentaje
FROM appweb.ubicaciones_usuarios
GROUP BY dispositivo;
```

### 3. Tendencia Temporal (7 días)

```sql
SELECT 
    DATE(created_at) as fecha,
    COUNT(*) as total_ubicaciones,
    COUNT(DISTINCT usuario_id) as usuarios_unicos,
    COUNT(*) FILTER (WHERE es_primera_visita) as primeras_visitas,
    COUNT(*) FILTER (WHERE usuario_id IS NULL) as visitas_anonimas
FROM appweb.ubicaciones_usuarios
WHERE created_at >= NOW() - INTERVAL '7 days'
GROUP BY DATE(created_at)
ORDER BY fecha DESC;
```

### 4. Análisis de Eventos

```sql
SELECT 
    evento,
    COUNT(*) as total,
    COUNT(DISTINCT session_id) as sesiones_unicas
FROM appweb.ubicaciones_usuarios
WHERE evento IS NOT NULL
GROUP BY evento
ORDER BY total DESC;
```

### 5. Mapa de Calor por Estado

```sql
SELECT 
    estado,
    COUNT(*) as registros,
    ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) as porcentaje
FROM appweb.ubicaciones_usuarios
WHERE estado IS NOT NULL
GROUP BY estado
ORDER BY registros DESC;
```

## 📈 Casos de Uso de Marketing

### 1. Segmentación Geográfica

```php
// Usuarios en una región específica
$usuariosJalisco = UbicacionUsuario::deEstado('Jalisco')
    ->with('usuario')
    ->whereNotNull('usuario_id')
    ->distinct('usuario_id')
    ->get();

// Crear campaña para usuarios de esa región
```

### 2. Análisis de Primeras Visitas

```php
// Ubicaciones de primeras visitas del último mes
$primerasVisitas = UbicacionUsuario::primerasVisitas()
    ->entreFechas(now()->subMonth(), now())
    ->get();

// Identificar ciudades de mayor adquisición
```

### 3. Análisis de Dispositivos

```php
// Distribución por tipo de dispositivo
$porDispositivo = UbicacionUsuario::query()
    ->select('dispositivo', DB::raw('COUNT(*) as total'))
    ->groupBy('dispositivo')
    ->get();

// Optimizar experiencia móvil si es el dispositivo predominante
```

### 4. Tracking de Conversión

```php
// Ubicaciones asociadas a eventos de compra
$ubicacionesCompra = UbicacionUsuario::porEvento('compra')
    ->with('usuario')
    ->get();

// Analizar zonas con mayor conversión
```

### 5. Análisis de Sesiones Anónimas

```php
// Visitantes que no se han registrado
$visitantesAnonimos = UbicacionUsuario::anonimas()
    ->whereBetween('created_at', [now()->subDays(30), now()])
    ->get();

// Identificar oportunidades de captación
```

## 🔒 Privacidad y Cumplimiento

### GDPR / LFPDPPP

- ✅ Los usuarios anónimos no están vinculados a información personal
- ✅ Los datos se usan exclusivamente para análisis agregados
- ✅ Los usuarios pueden solicitar eliminación de sus ubicaciones
- ✅ La IP se almacena encriptada (tipo INET de PostgreSQL)
- ✅ Consentimiento implícito al aceptar términos de servicio

### Eliminar Datos de un Usuario

```php
// Borrar todas las ubicaciones de un usuario
UbicacionUsuario::where('usuario_id', $userId)->delete();

// O anonimizar (recomendado para análisis histórico)
UbicacionUsuario::where('usuario_id', $userId)
    ->update(['usuario_id' => null]);
```

## 🎨 Visualizaciones Sugeridas

### Dashboard de Marketing

1. **Mapa de calor**: Concentración de usuarios por ciudad
2. **Gráfico de líneas**: Tendencia de ubicaciones capturadas por día
3. **Gráfico circular**: Distribución por dispositivo
4. **Tabla top**: Top 10 ciudades con más actividad
5. **Métricas**: Total de ubicaciones, usuarios únicos, ciudades alcanzadas

### Herramientas de Visualización

- **Laravel Charts** para gráficos
- **Google Maps API** para mapas
- **Chart.js** para gráficos interactivos
- **Datatables** para tablas con filtros

## 🔧 Mantenimiento

### Limpieza de Datos Antiguos

```sql
-- Eliminar ubicaciones de más de 2 años
DELETE FROM appweb.ubicaciones_usuarios
WHERE created_at < NOW() - INTERVAL '2 years';

-- O archivar en tabla histórica
INSERT INTO appweb.ubicaciones_usuarios_historico
SELECT * FROM appweb.ubicaciones_usuarios
WHERE created_at < NOW() - INTERVAL '1 year';
```

### Optimización de Rendimiento

```sql
-- Actualizar estadísticas de la tabla
ANALYZE appweb.ubicaciones_usuarios;

-- Reindexar si es necesario
REINDEX TABLE CONCURRENTLY appweb.ubicaciones_usuarios;
```

## ✅ Ventajas de Tabla Separada

| Aspecto | Tabla Separada | En Tabla Usuarios |
|---------|----------------|-------------------|
| **Historial** | ✅ Múltiples ubicaciones | ❌ Solo la última |
| **Anónimos** | ✅ Sí (usuario_id NULL) | ❌ No |
| **Contexto** | ✅ Evento, dispositivo, página | ❌ Limitado |
| **Análisis temporal** | ✅ Fácil | ❌ Difícil |
| **GDPR** | ✅ Fácil separar | ❌ Mezclado con datos personales |
| **Rendimiento** | ✅ No afecta usuarios | ❌ Tabla usuarios más grande |
| **Marketing** | ✅ Diseñado para ello | ❌ No optimizado |

---

**Tabla:** `appweb.ubicaciones_usuarios`  
**Modelo:** `App\Models\UbicacionUsuario`  
**Controlador:** `App\Http\Controllers\Api\LocationController`  
**Rutas API:** `/api/v1/location`, `/api/v1/locations`
