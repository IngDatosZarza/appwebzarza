# 📍 Sistema de Captura de Ubicación Geográfica

## Descripción General

Este sistema captura automáticamente la ubicación geográfica del usuario cuando visita la página de inicio y la almacena en la base de datos. La implementación utiliza la **Geolocation API** del navegador y un servicio de **Reverse Geocoding** gratuito para obtener información detallada de la ubicación.

## ✨ Características

- ✅ Captura automática de coordenadas GPS (latitud y longitud)
- ✅ Obtención de información de ciudad, estado y país mediante Reverse Geocoding
- ✅ Almacenamiento de ubicación para usuarios autenticados
- ✅ Almacenamiento temporal en sesión para usuarios no autenticados
- ✅ Solicitud de permisos no intrusiva
- ✅ Una sola solicitud por sesión del navegador
- ✅ Compatible con todos los navegadores modernos
- ✅ Manejo de errores y privacidad del usuario

## 📋 Componentes Implementados

### 1. Migración de Base de Datos
**Archivo:** `database/migrations/2024_12_01_000001_add_location_fields_to_usuarios_table.php`

Agrega los siguientes campos a la tabla `usuarios`:
- `latitud` (decimal 10,7): Coordenada de latitud
- `longitud` (decimal 10,7): Coordenada de longitud
- `ciudad` (string 100): Nombre de la ciudad
- `estado` (string 100): Nombre del estado/provincia
- `pais` (string 100): Nombre del país
- `ubicacion_capturada_at` (timestamp): Fecha y hora de captura

### 2. Modelo Usuario Actualizado
**Archivo:** `app/Models/Usuario.php`

Se agregaron los campos de ubicación al array `$fillable` y se configuró el cast para `ubicacion_capturada_at`.

### 3. Controlador de Ubicación
**Archivo:** `app/Http/Controllers/Api/LocationController.php`

**Endpoints disponibles:**
- `POST /api/v1/location` - Guardar ubicación del usuario (público)
- `GET /api/v1/location` - Obtener ubicación guardada (requiere autenticación)

### 4. Rutas API
**Archivo:** `routes/api.php`

Se agregaron las rutas para el LocationController en las secciones de rutas públicas y protegidas.

### 5. JavaScript de Geolocalización
**Archivo:** `resources/js/geolocation.js`

Clase completa `GeolocationService` con métodos para:
- Verificar soporte del navegador
- Solicitar permisos de ubicación
- Obtener coordenadas GPS
- Realizar Reverse Geocoding
- Guardar datos en el servidor

### 6. Script Integrado en Layout
**Archivo:** `resources/views/layouts/app.blade.php`

Script inline que se ejecuta automáticamente en todas las páginas que usen el layout `app.blade.php`.

## 🚀 Instalación y Configuración

### Paso 1: Ejecutar la Migración

```bash
php artisan migrate
```

Esto creará los nuevos campos en la tabla `usuarios`.

### Paso 2: Limpiar Caché (Opcional pero recomendado)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Paso 3: Verificar que el Script esté Cargando

Abre tu navegador y visita cualquier página de tu aplicación que use el layout `app.blade.php`. Abre la consola del navegador (F12) y deberías ver mensajes como:

```
✅ Ubicación guardada exitosamente
```

## 📱 Flujo de Funcionamiento

### Para Usuarios No Autenticados:

1. El usuario visita la página de inicio
2. Después de 2 segundos, el navegador solicita permiso para acceder a la ubicación
3. Si el usuario acepta:
   - Se obtienen las coordenadas GPS
   - Se realiza Reverse Geocoding para obtener ciudad, estado y país
   - Los datos se guardan en la **sesión** del usuario
   - Estos datos pueden usarse posteriormente en el registro

4. Si el usuario rechaza: No se hace nada, la experiencia continúa normalmente

### Para Usuarios Autenticados:

1. El usuario visita cualquier página de la aplicación
2. Después de 2 segundos, el navegador solicita permiso para acceder a la ubicación
3. Si el usuario acepta:
   - Se obtienen las coordenadas GPS
   - Se realiza Reverse Geocoding
   - Los datos se guardan directamente en la **base de datos** en el perfil del usuario
   - Se actualiza el campo `ubicacion_capturada_at`

## 🔒 Consideraciones de Privacidad

- ✅ El sistema **respeta** la decisión del usuario si rechaza compartir ubicación
- ✅ Solo solicita ubicación **una vez por sesión del navegador**
- ✅ La ubicación se solicita de forma **no intrusiva** (2 segundos después de cargar)
- ✅ El usuario puede **revocar permisos** en cualquier momento desde su navegador
- ✅ Los datos se usan exclusivamente para mejorar la experiencia del usuario

## 🛠️ Personalización

### Cambiar el Tiempo de Espera

En `app.blade.php`, línea del `setTimeout`:

```javascript
setTimeout(() => {
    service.capture()...
}, 2000); // Cambiar 2000 a los milisegundos deseados
```

### Desactivar la Captura Automática

Si prefieres solicitar la ubicación manualmente, comenta el código en `app.blade.php`:

```javascript
// document.addEventListener('DOMContentLoaded', function() {
//     const locationRequested = sessionStorage.getItem('locationRequested');
//     ...
// });
```

Y luego puedes llamar manualmente:

```javascript
const service = new GeolocationService();
service.capture();
```

### Usar la Ubicación en el Proceso de Registro

En tu controlador de registro, puedes usar la ubicación guardada en sesión:

```php
$tempLocation = session('temp_location');

if ($tempLocation) {
    $usuario->latitud = $tempLocation['latitud'];
    $usuario->longitud = $tempLocation['longitud'];
    $usuario->ciudad = $tempLocation['ciudad'];
    $usuario->estado = $tempLocation['estado'];
    $usuario->pais = $tempLocation['pais'];
    $usuario->ubicacion_capturada_at = $tempLocation['ubicacion_capturada_at'];
    
    // Limpiar la sesión
    session()->forget('temp_location');
}
```

## 📊 Consultar Ubicaciones Guardadas

### Desde PHP/Laravel:

```php
// Obtener usuarios con ubicación
$usuariosConUbicacion = Usuario::whereNotNull('latitud')
    ->whereNotNull('longitud')
    ->get();

// Buscar usuarios en una ciudad específica
$usuariosEnCiudad = Usuario::where('ciudad', 'LIKE', '%Guadalajara%')->get();

// Usuarios que compartieron ubicación en los últimos 7 días
$usuariosRecientes = Usuario::where('ubicacion_capturada_at', '>=', now()->subDays(7))->get();
```

### Desde JavaScript (Usuario Autenticado):

```javascript
fetch('/api/v1/location', {
    method: 'GET',
    headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + token // Si usas Sanctum
    }
})
.then(response => response.json())
.then(data => {
    console.log('Ubicación del usuario:', data);
});
```

## 🌐 Servicio de Reverse Geocoding

Este sistema utiliza la API gratuita de **Nominatim** (OpenStreetMap) para obtener información de ubicación.

**Límites de uso:**
- Máximo 1 solicitud por segundo
- Para uso gratuito y no comercial extensivo

**Alternativas si necesitas mayor capacidad:**
- Google Maps Geocoding API (requiere API key)
- Mapbox Geocoding API (requiere API key)
- OpenCage Geocoding API

Para cambiar el servicio, modifica el método `reverseGeocode` en el script de geolocalización.

## 🐛 Solución de Problemas

### El navegador no solicita permisos

**Causa:** La geolocalización requiere HTTPS (excepto en localhost)

**Solución:** Asegúrate de que tu sitio use HTTPS en producción.

### La ubicación no se guarda

**Verificar:**
1. Que la migración se haya ejecutado correctamente
2. Que el token CSRF esté presente en el meta tag
3. Que la ruta `/api/v1/location` esté registrada
4. Revisar la consola del navegador en busca de errores

### Reverse Geocoding no funciona

**Causa:** Límite de tasa de Nominatim excedido

**Solución:** Implementar un sistema de caché o cambiar a un servicio alternativo.

## 📝 Notas Adicionales

- La ubicación se solicita automáticamente solo **una vez por sesión**
- Los datos se almacenan de forma segura en la base de datos
- El sistema es compatible con dispositivos móviles
- La precisión depende del dispositivo y la disponibilidad de GPS

## 🎯 Casos de Uso

1. **Mostrar sucursales cercanas** al usuario
2. **Personalizar promociones** según la ubicación
3. **Análisis geográfico** de la base de clientes
4. **Validación de región** para servicios disponibles
5. **Recomendaciones de productos** según la zona

---

**¿Preguntas o problemas?** Revisa la consola del navegador (F12) para mensajes de debug.
