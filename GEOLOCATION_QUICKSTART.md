# 🚀 Guía Rápida - Sistema de Tracking de Ubicación para Marketing

## ⚡ Instalación en 3 Pasos

### 1️⃣ Ejecutar la Migración

```bash
php artisan migrate
```

### 2️⃣ Verificar la Instalación

```bash
php artisan tinker
```

```php
\App\Models\UbicacionUsuario::count();
```

### 3️⃣ Probar en el Navegador

Abre tu aplicación y permite el acceso a la ubicación cuando se solicite.

---

## 📊 ¿Qué se instaló?

### Nueva Tabla: `ubicaciones_usuarios`

**Propósito:** Almacenar ubicaciones de usuarios para análisis de marketing

**Características:**
- ✅ Historial completo de ubicaciones
- ✅ Tracking de visitantes anónimos
- ✅ Información de dispositivo y navegador
- ✅ Contexto (evento, página origen, sesión)
- ✅ Primeras visitas vs. recurrentes

### Campos principales:
- `latitud`, `longitud` (NUMERIC) - Coordenadas GPS
- `ciudad`, `estado`, `pais` (VARCHAR) - Ubicación
- `dispositivo`, `navegador`, `sistema_operativo` - Info del dispositivo
- `evento` - Tipo de acción (navegacion, compra, registro)
- `es_primera_visita` (BOOLEAN) - Primera visita de la sesión
- `usuario_id` (BIGINT, nullable) - Vinculación con usuario

---

## 🔍 Consultas Rápidas

### Desde PostgreSQL:

```sql
-- Ver ubicaciones recientes
SELECT id, usuario_id, ciudad, estado, dispositivo, evento, created_at
FROM appweb.ubicaciones_usuarios
ORDER BY created_at DESC
LIMIT 10;

-- Contar ubicaciones por ciudad
SELECT ciudad, estado, COUNT(*) as total
FROM appweb.ubicaciones_usuarios
WHERE ciudad IS NOT NULL
GROUP BY ciudad, estado
ORDER BY total DESC;
```

### Desde Laravel Tinker:

```bash
php artisan tinker
```

```php
use App\Models\UbicacionUsuario;

// Ver últimas ubicaciones
UbicacionUsuario::latest()->take(10)->get();

// Ubicaciones de una ciudad específica
UbicacionUsuario::deCiudad('Guadalajara')->get();

// Estadísticas
UbicacionUsuario::estadisticas();

// Top ciudades
DB::table('ubicaciones_usuarios')
    ->select('ciudad', 'estado', DB::raw('COUNT(*) as total'))
    ->whereNotNull('ciudad')
    ->groupBy('ciudad', 'estado')
    ->orderByDesc('total')
    ->get();
```

---

## 🎯 API Endpoints

### Guardar Ubicación (POST)
```
POST /api/v1/location
```

**Body:**
```json
{
  "latitud": 20.6736,
  "longitud": -103.3440,
  "ciudad": "Guadalajara",
  "estado": "Jalisco",
  "pais": "México"
}
```

### Obtener Ubicación (GET)
```
GET /api/v1/location
```
*Requiere autenticación con Sanctum*

---

## 🛠️ Alternativa: SQL Directo

Si prefieres ejecutar el SQL directamente en PostgreSQL:

```bash
psql -h data-warehouse.cn1hqjnw6sbe.us-east-2.rds.amazonaws.com \
     -U appwebuser \
     -d productivo \
     -f database/sql/add_location_fields_postgres.sql
```

---

## 🐛 Solución de Problemas

### La migración no funciona

```bash
# Limpiar caché de configuración
php artisan config:clear
php artisan cache:clear

# Intentar de nuevo
php artisan migrate
```

### Verificar conexión a PostgreSQL

```bash
php artisan tinker
```

```php
DB::connection()->getPdo();
echo "Conectado!";
```

### Ver logs de migración

```bash
php artisan migrate:status
```

---

## 📚 Documentación Completa

- **PostgreSQL completo:** `GEOLOCATION_POSTGRES.md`
- **Documentación general:** `GEOLOCATION_SETUP.md`
- **Script SQL:** `database/sql/add_location_fields_postgres.sql`

---

## ✅ Checklist

- [ ] ✅ Ejecutar `php artisan migrate`
- [ ] ✅ Ejecutar `php artisan geolocation:verify`
- [ ] ✅ Abrir la aplicación en el navegador
- [ ] ✅ Permitir acceso a la ubicación
- [ ] ✅ Verificar que los datos se guarden
- [ ] ✅ Revisar con `php artisan tinker`

---

## 🎉 ¡Listo!

El sistema está configurado y funcionando automáticamente en todas las páginas que usen el layout `app.blade.php`.

**Configuración:**
- Base de datos: PostgreSQL en AWS RDS
- Schema: `appweb`
- Tabla: `usuarios`
- Driver: `pgsql`

---

**¿Preguntas?** Revisa la documentación completa en `GEOLOCATION_POSTGRES.md`
