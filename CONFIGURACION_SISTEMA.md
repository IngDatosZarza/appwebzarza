# 🔐 CONFIGURACIÓN DEL SISTEMA - ZarzaPoints

## 📋 INFORMACIÓN GENERAL

**Nombre del Sistema:** ZarzaPoints  
**Versión:** 2.1  
**Framework:** Laravel 12.33.0  
**PHP:** 8.2.12  
**Base de Datos:** PostgreSQL 17.5  
**Fecha:** 2025-10-15

---

## 🗄️ CONFIGURACIÓN DE BASE DE DATOS

### Conexión PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=appwebuser
DB_PASSWORD=appwebpass
DB_SCHEMA=appweb
```

### Tablas del Sistema (13)

1. **usuarios** - Usuarios del sistema (clientes y admin)
2. **direcciones** - Direcciones de envío de clientes
3. **puntos** - Saldo de puntos por usuario
4. **sucursales** - Sucursales de la empresa
5. **compras** - Registro de compras/tickets
6. **transacciones_puntos** - Historial de movimientos de puntos
7. **cupones** - Catálogo de cupones disponibles
8. **cupones_asignados** - Cupones canjeados por usuarios
9. **redenciones** - Validaciones de cupones por admin
10. **auditoria** - Log de acciones del sistema
11. **notificaciones** - Notificaciones para usuarios
12. **sessions** - Sesiones activas
13. **migrations** - Control de migraciones

---

## 👥 USUARIOS DE PRUEBA

### Cliente
```
Email: cliente@test.com
Password: password
Rol: cliente
Puntos: 500
```

**Acceso:** http://localhost:8000/login

**Funcionalidades:**
- Ver cupones disponibles
- Canjear cupones con puntos
- Ver cupones canjeados
- Registrar tickets de compra
- Ver historial de puntos

### Administrador
```
Email: admin@test.com
Password: password
Rol: admin
```

**Acceso:** http://localhost:8000/admin/points

**Funcionalidades:**
- Ver dashboard de estadísticas
- Validar cupones de clientes
- Gestionar sucursales
- Ver auditoría del sistema
- Administrar cupones

---

## 🏪 SUCURSALES

1. **Sucursal Centro**
   - Dirección: Av. Principal 123, Centro, Xalapa, Veracruz, México
   - Teléfono: 228-123-4567

2. **Sucursal Norte**
   - Dirección: Calle Norte 456, Col. Norte, Xalapa, Veracruz, México
   - Teléfono: 228-234-5678

3. **Sucursal Sur**
   - Dirección: Av. Sur 789, Col. Sur, Xalapa, Veracruz, México
   - Teléfono: 228-345-6789

---

## 🎫 SISTEMA DE CUPONES

### Mecánica

1. Cliente acumula **100 puntos por cada ticket** registrado
2. Cliente puede **canjear cupones** con sus puntos acumulados
3. Al canjear, se genera un **código legible** (ej: BANDERILLAS20)
4. Se genera un **código QR único** (ej: BANDERILLAS20-A3F9B)
5. Cliente muestra el código al vendedor
6. Vendedor **valida el cupón** en el sistema admin
7. Cupón se marca como **usado** y no puede volver a canjearse

### Estados de Cupones

- **asignado** - Cupón canjeado pero no usado
- **usado** - Cupón validado por admin
- **vencido** - Cupón expirado
- **bloqueado** - Cupón bloqueado por admin

### Cupones Disponibles (10)

| Código | Nombre | Puntos | Descripción |
|--------|--------|--------|-------------|
| B2028 | Banderillas 20% | 100 | 20% de descuento en Banderillas |
| D1045 | Descuento 10% Compras | 50 | 10% de descuento en compras |
| PG80 | Producto Gratis | 200 | Producto gratis en tu compra |
| EGP71 | Envío Gratis Premium | 150 | Envío gratis en compras >$500 |
| D1570 | Descuento 15% Especial | 120 | 15% en productos seleccionados |
| CP34 | Cupón de Prueba - 50 pts | 50 | Cupón de prueba del sistema |
| CP44 | Cupón de Prueba Gamificación | 75 | Para pruebas de gamificación |
| D10C11 | Descuento 10% Compras | 50 | 10% en todas las compras |
| ... | ... | ... | ... |

---

## 🎨 COLORES DEL SISTEMA

### Paleta Principal
```css
--zarza-pink: #b51a8a
--zarza-purple: #71398d
--zarza-gradient: linear-gradient(135deg, #b51a8a 0%, #71398d 100%)
```

### Uso
- **Botones primarios:** Gradiente rosa-morado
- **Links:** Rosa (#b51a8a)
- **Hover:** Morado (#71398d)
- **Iconos:** Rosa con opacidad

---

## 📁 ESTRUCTURA DE DIRECTORIOS

```
appwebzarza/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Web/
│   │   │       ├── CouponsController.php
│   │   │       ├── CouponValidationController.php
│   │   │       └── PointsController.php
│   │   └── Middleware/
│   │       ├── CustomAuth.php
│   │       └── IsAdmin.php
│   ├── Models/
│   │   ├── Usuario.php
│   │   ├── Cupon.php
│   │   ├── CuponAsignado.php
│   │   ├── Compra.php
│   │   └── Punto.php
│   └── Helpers/
│       └── csrf_helper.php
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── client/
│       │   ├── dashboard.blade.php
│       │   └── coupons/
│       │       └── index.blade.php
│       └── admin/
│           ├── dashboard.blade.php
│           └── coupons/
│               └── validate.blade.php
├── routes/
│   └── web.php
├── database/
│   └── migrations/
├── public/
│   ├── logoZarza.webp
│   └── index.php
├── storage/
│   ├── app/
│   └── logs/
└── .env
```

---

## 🔧 VARIABLES DE ENTORNO (.env)

```env
APP_NAME="ZarzaPoints"
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=appwebuser
DB_PASSWORD=appwebpass
DB_SCHEMA=appweb

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## 🚀 COMANDOS IMPORTANTES

### Desarrollo
```bash
# Iniciar servidor
php artisan serve --host=localhost --port=8000

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas
php artisan route:list

# Ver comandos Artisan
php artisan list
```

### Base de Datos
```bash
# Conectar a PostgreSQL
psql -h localhost -U appwebuser -d postgres

# Dentro de psql:
SET search_path TO appweb, public;
\dt                              # Ver tablas
\d usuarios                      # Ver estructura de tabla
SELECT * FROM cupones LIMIT 5;   # Consultar datos
\q                               # Salir
```

### Migración
```bash
# Crear respaldo
php crear_respaldo_bd.php

# Verificar sistema
php verificar_migracion.php

# Menú interactivo (Windows)
MIGRACION.bat

# Menú interactivo (Linux)
chmod +x migracion_menu.sh
./migracion_menu.sh
```

---

## 📊 ESTADÍSTICAS ACTUALES

- **Usuarios totales:** 12 (2 de prueba + 10 clientes)
- **Cupones disponibles:** 10
- **Cupones canjeados:** 5
- **Compras registradas:** 5
- **Puntos en circulación:** ~4500
- **Sucursales activas:** 3
- **Transacciones:** 10

---

## 🔒 SEGURIDAD

### Autenticación
- Sistema de autenticación personalizado
- Sesiones en BD (tabla `sessions`)
- Middleware `CustomAuth` para rutas protegidas
- Middleware `IsAdmin` para rutas de administrador
- Protección CSRF en formularios
- Passwords hasheados con bcrypt

### Permisos
- Clientes solo acceden a `/cupones`, `/profile`
- Admins acceden a `/admin/*`
- Validación de roles en middleware
- Auditoría de acciones en tabla `auditoria`

---

## 📝 LOGS Y MONITOREO

### Ubicación de Logs
```
storage/logs/laravel.log
```

### Ver logs en tiempo real
```bash
# Linux/Mac
tail -f storage/logs/laravel.log

# Windows (PowerShell)
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

---

## 🆘 CONTACTOS Y SOPORTE

**Desarrollador:** GitHub Copilot  
**Proyecto:** ZarzaPoints - Sistema de Fidelidad  
**Repositorio:** Local (c:\xampp\htdocs\appwebzarza)

### Archivos de Ayuda
- `GUIA_MIGRACION.md` - Guía completa de migración
- `MIGRACION_RAPIDA.md` - Guía rápida
- `CHECKLIST_MIGRACION.md` - Checklist
- `MIGRACION.bat` - Utilidades Windows
- `migracion_menu.sh` - Utilidades Linux

---

## 📅 CHANGELOG

### v2.1 (2025-10-15)
- ✅ Sistema de cupones con códigos legibles
- ✅ Generación de QR codes únicos
- ✅ Popup de canje con confetti
- ✅ Validación de cupones por admin
- ✅ Deshabilitar cupones ya canjeados
- ✅ Scripts de migración completos
- ✅ Menús interactivos para Windows/Linux

### v2.0 (2025-10-14)
- ✅ Sistema de tickets (100 puntos)
- ✅ Autenticación personalizada
- ✅ Dashboard cliente y admin
- ✅ Sistema de cupones básico

---

**Última actualización:** 2025-10-15 21:15
