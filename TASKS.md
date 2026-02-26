# 🎯 Guía de Tareas - Sistema de Puntos de Fidelidad

## 📋 Tareas Disponibles en VS Code

### 🚀 Tareas Principales

#### 1. `🚀 Laravel: Iniciar Servidor`
- **Descripción**: Inicia el servidor de desarrollo Laravel
- **Puerto**: 8000 (localhost:8000)
- **Uso**: Presiona `Ctrl+Shift+P` → "Tasks: Run Task" → Selecciona la tarea
- **Estado**: ⚠️ Requiere corrección de cache

#### 2. `🧹 Laravel: Limpiar Cache`
- **Descripción**: Limpia archivos de cache de Laravel
- **Uso**: Ejecutar antes de iniciar el servidor si hay problemas

#### 3. `🔄 Laravel: Reiniciar Proyecto`
- **Descripción**: Reinicia completamente el proyecto limpiando cache
- **Uso**: Para resolver problemas de configuración

### 📊 Tareas de Consulta

#### 4. `📊 Laravel: Ver Estado BD`
- **Descripción**: Muestra estadísticas de la base de datos
- **Información**: Usuarios, compras, cupones, sucursales

#### 5. `🔍 Sistema: Consultar Puntos Usuario`
- **Descripción**: Lista todos los usuarios con sus puntos actuales
- **Formato**: Nombre, email, rol, puntos

#### 6. `💰 Sistema: Ver Compras`
- **Descripción**: Historial completo de compras realizadas
- **Información**: Cliente, sucursal, monto, puntos generados

#### 7. `🎫 Sistema: Ver Cupones Disponibles`
- **Descripción**: Lista cupones con sus requisitos y vigencia
- **Estados**: Vigente, Vencido, Futuro

### 🧪 Tareas de Desarrollo

#### 8. `🧪 Laravel: Ejecutar Tests`
- **Descripción**: Ejecuta pruebas unitarias del proyecto
- **Comando**: `php artisan test`

#### 9. `📋 Laravel: Artisan Commands`
- **Descripción**: Lista todos los comandos Artisan disponibles
- **Uso**: Para consultar comandos Laravel

## 🌐 Servidor Alternativo

### Dashboard PHP Simple (Puerto 8080)
Mientras se resuelven los problemas de cache de Laravel, está disponible un dashboard alternativo:

```bash
# Ejecutar manualmente:
php -S localhost:8080 dashboard.php
```

**URL**: http://localhost:8080

**Características del Frontend**:
- ✅ Dashboard interactivo con estadísticas en tiempo real
- ✅ Página de cupones con diseño moderno
- ✅ Lista de usuarios y sistema de puntos
- ✅ Navegación responsiva con Tailwind CSS
- ✅ Router personalizado PHP
- ✅ Integración completa con base de datos
- ✅ Diseño profesional con animaciones

## 🛠️ Comandos Manuales Útiles

### Verificación de Estado
```bash
# Verificar conexión a BD
php -r "try { $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass'); echo 'Conexión OK'; } catch (Exception $e) { echo 'Error: ' . $e->getMessage(); }"

# Limpiar cache manualmente
php -r "file_put_contents('bootstrap/cache/packages.php', '<?php return [];'); file_put_contents('bootstrap/cache/services.php', '<?php return [];'); echo 'Cache limpiado';"
```

### Consultas de Base de Datos
```sql
-- Conectar a PostgreSQL y configurar schema
SET search_path TO appweb, public;

-- Ver usuarios con puntos
SELECT u.nombres || ' ' || u.apellido_paterno as nombre, 
       u.email, u.rol, COALESCE(p.saldo, 0) as puntos 
FROM usuarios u 
LEFT JOIN puntos p ON u.id = p.usuario_id 
ORDER BY u.rol, u.nombres;

-- Ver compras realizadas
SELECT u.nombres || ' ' || u.apellido_paterno as cliente,
       s.nombre as sucursal, c.monto, c.puntos_generados,
       c.created_at::date as fecha
FROM compras c
JOIN usuarios u ON c.usuario_id = u.id
JOIN sucursales s ON c.sucursal_id = s.id
ORDER BY c.created_at DESC;
```

## 🔧 Solución de Problemas

### Error: "bootstrap/cache directory must be present and writable"
1. Ejecutar: `🧹 Laravel: Limpiar Cache`
2. O manualmente: 
   ```bash
   Remove-Item -Recurse -Force bootstrap\cache\*
   New-Item -ItemType Directory bootstrap\cache -Force
   ```

### Error de Conexión a BD
1. Verificar que PostgreSQL esté ejecutándose
2. Confirmar credenciales en `.env`:
   - DB_HOST=localhost
   - DB_DATABASE=postgres
   - DB_USERNAME=appwebuser
   - DB_PASSWORD=appwebpass
   - DB_SCHEMA=appweb

### Servidor No Inicia
1. Usar servidor alternativo: `php -S localhost:8080 dashboard.php`
2. Verificar puerto disponible
3. Ejecutar `🔄 Laravel: Reiniciar Proyecto`

## 📈 Próximos Pasos

1. [x] ✅ **Resolver problemas de cache Laravel** - Cache configurado
2. [x] ✅ **Crear controladores API REST** - AuthController, PurchaseController, CouponController
3. [x] ✅ **Implementar autenticación** - Laravel Sanctum instalado y configurado
4. [x] ✅ **Desarrollar frontend con Blade** - Frontend web completado
5. [ ] 📝 **Agregar tests unitarios** - Pendiente
6. [x] ✅ **Documentar API completa** - API REST completamente documentada

## 🚀 Estado Actual del Proyecto

### ✅ **COMPLETADO**
- **Base de datos PostgreSQL** con 11 tablas
- **Modelos Eloquent** con relaciones completas
- **API REST completa** con 15+ endpoints
- **Autenticación JWT** con Laravel Sanctum
- **Dashboard funcional** en puerto 8080
- **Documentación de tareas** actualizada

### ✅ **RECIÉN COMPLETADO**
- **Frontend web completo** con diseño responsivo
- **Dashboard interactivo** con estadísticas en tiempo real
- **Página de cupones** con sistema de canje visual
- **Router personalizado** para manejar rutas web

### ⏳ **EN DESARROLLO**
- **Panel administrativo** avanzado
- **Tests unitarios** y de integración
- **Autenticación web** completa

### 📋 **PENDIENTE**
- **Optimización de performance**
- **Documentación Swagger/OpenAPI**
- **Deployment en producción**

---
💡 **Tip**: Usa el dashboard en puerto 8080 como interfaz principal mientras se desarrollan las funcionalidades avanzadas.