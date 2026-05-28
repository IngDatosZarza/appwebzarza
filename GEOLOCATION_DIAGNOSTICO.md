# 🔍 INSTRUCCIONES PARA DIAGNÓSTICO DE GEOLOCALIZACIÓN

## ✅ Sistema Instalado
- Tabla: `ubicaciones_usuarios` ✓
- Modelo: `UbicacionUsuario` ✓
- API: `/api/v1/location` ✓
- JavaScript: Integrado en layout ✓

## 🧪 PRUEBAS DISPONIBLES

### 1. Test Completo desde el Navegador (RECOMENDADO)

**URL:** `https://tu-dominio.com/test-geolocation`

Este test interactivo te mostrará:
- ✅ Si el navegador soporta geolocalización
- ✅ Si el CSRF token está disponible
- ✅ Las coordenadas GPS capturadas
- ✅ El resultado del reverse geocoding
- ✅ La respuesta de la API
- ✅ Si se guardó en la base de datos

**Pasos:**
1. Abre `https://tu-dominio.com/test-geolocation` en tu móvil
2. Haz clic en "🚀 Ejecutar Test Completo"
3. Permite el acceso a la ubicación
4. Lee los resultados paso a paso
5. Busca errores en color rojo ❌

### 2. Test de Inserción Directa (desde servidor)

```bash
php artisan location:test-insert
```

Este comando:
- ✅ Inserta una ubicación de prueba en PostgreSQL
- ✅ Verifica que el modelo funciona
- ✅ Muestra estadísticas de la tabla
- ✅ No requiere navegador ni JavaScript

### 3. Test de API Directo (desde el navegador)

En `/test-geolocation`, haz clic en "📡 Test API Directo"

Esto envía datos de prueba directamente a la API sin usar GPS.

## 📋 CHECKLIST DE DIAGNÓSTICO

### Paso 1: ¿El servidor funciona?
```bash
php artisan location:test-insert
```
- ✅ Si funciona → El modelo y la BD están OK
- ❌ Si falla → Problema en modelo/BD/conexión

### Paso 2: ¿La API responde?
Abre: `https://tu-dominio.com/test-geolocation`
Haz clic en "📡 Test API Directo"

- ✅ Si devuelve success: true → API funciona
- ❌ Si devuelve error → Problema en controlador/rutas

### Paso 3: ¿El JavaScript funciona?
Abre: `https://tu-dominio.com/test-geolocation`
Haz clic en "🚀 Ejecutar Test Completo"

- ✅ Test 1: Geolocalización soportada
- ✅ Test 2: CSRF Token encontrado
- ✅ Test 3: Ubicación GPS obtenida
- ✅ Test 4: Geocoding exitoso
- ✅ Test 5: API guardó datos

## 🐛 PROBLEMAS COMUNES

### Problema 1: "CSRF Token NO encontrado"
**Causa:** El meta tag de CSRF no está en la página
**Solución:** Verificar que el layout tenga:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Problema 2: "Error 419 - CSRF token mismatch"
**Causa:** Token expirado o sesión inválida
**Solución:**
- Refrescar la página
- Limpiar cookies del navegador
- Verificar que la sesión funcione

### Problema 3: "Error 500 - Internal Server Error"
**Causa:** Error en el controlador o base de datos
**Solución:**
- Revisar logs: `storage/logs/laravel.log`
- Ejecutar: `php artisan location:test-insert` para ver el error exacto

### Problema 4: GPS obtiene ubicación pero no se guarda
**Causa:** La API no responde o hay error en el fetch
**Solución:**
- Abrir consola del navegador (F12)
- Buscar errores en la pestaña "Console"
- Verificar respuesta en pestaña "Network" → filtrar por "location"

### Problema 5: "Session ID null"
**Causa:** Las sesiones no funcionan
**Solución:**
```bash
php artisan session:table  # Si usa DB sessions
php artisan migrate
php artisan config:cache
```

## 📱 INFORMACIÓN REQUERIDA DEL USUARIO

Por favor proporciona:

1. **Resultado del Test Completo:**
   - Abre `/test-geolocation` en tu móvil
   - Ejecuta el test completo
   - Copia y pega TODO el resultado (o toma screenshot)

2. **Consola del navegador:**
   - Abre la página principal
   - Abre DevTools (inspeccionar en Chrome móvil)
   - Ve a la pestaña "Console"
   - Busca mensajes en rojo (errores)
   - Copia los errores

3. **Network Inspector:**
   - En DevTools, pestaña "Network"
   - Filtra por "location"
   - Busca la petición POST a `/api/v1/location`
   - Ver: Status code, Response, Headers

4. **Resultado del comando:**
   ```bash
   php artisan location:test-insert
   ```

5. **Logs del servidor:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

## 🎯 SIGUIENTE PASO

**Ejecuta esto primero:**
```bash
php artisan location:test-insert
```

Si funciona → El problema está en el frontend (JavaScript/CSRF)
Si falla → El problema está en el backend (modelo/BD/permisos)

Luego abre:
```
https://tu-dominio.com/test-geolocation
```

Y envíame los resultados completos.
