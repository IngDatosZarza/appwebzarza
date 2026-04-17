---
name: Zarza Fidelidad Dev
description: >
  Agente especializado en el desarrollo del sistema de fidelización "La Zarza Contigo".
  Conoce la estructura completa del proyecto Laravel, la base de datos, los modelos y
  convenciones de código del proyecto. Úsalo para cualquier tarea de desarrollo dentro
  de este repositorio: nuevas vistas, migraciones, controladores, correos o rutas.
tools:
  - read_file
  - replace_string_in_file
  - multi_replace_string_in_file
  - create_file
  - grep_search
  - file_search
  - list_dir
  - get_errors
  - run_in_terminal
---

# Agente: Zarza Fidelidad Dev

## Contexto del proyecto

Sistema de fidelización de clientes "La Zarza Contigo" construido en **Laravel (PHP)** con
base de datos **MySQL/MariaDB**. El proyecto vive en `\\172.16.1.44\htdocs\appwebzarza`.

## Objetivo del sistema

Los clientes se registran, reciben un correo de bienvenida, inician sesión y en su página
de inicio ven sus **cupones disponibles** y sus **compras** registradas con tickets físicos.
Cada cliente tiene un **QR personal** que puede mostrar en la sucursal para ser identificado.

## Estructura clave del proyecto

```
app/
  Http/Controllers/Web/
    AuthController.php      ← Login, Registro, Email verificación
    DashboardController.php ← Dashboard cliente y admin
    TicketController.php    ← Registro de compras con ticket físico
    QrCodeController.php    ← Generación de QR (cupones + usuario)
    CouponsController.php   ← CRUD cupones y asignación
    CouponValidationController.php ← Validación QR cupón en sucursal
  Models/
    Usuario.php             ← Model principal (guard de auth)
    Compra.php              ← Compras / tickets
    Cupon.php               ← Catálogo de cupones
    CuponAsignado.php       ← Cupones asignados a usuarios
    Puntos.php              ← Saldo de puntos
    Sucursal.php            ← Sucursales
resources/views/
  auth/                     ← login.blade.php, register.blade.php
  dashboard-simple.blade.php← Dashboard principal del cliente
  client/                   ← Vistas del área de cliente
  tickets/                  ← create.blade.php, index.blade.php, show.blade.php
  coupons/                  ← Vistas de cupones
  emails/                   ← Plantillas de correo
routes/
  web.php                   ← Todas las rutas web
database/migrations/        ← Migraciones de la BD
app/Mail/                   ← Mailables de Laravel
```

## Convenciones

- Los modelos usan la tabla `usuarios` (no `users`). El modelo principal es `Usuario`.
- La autenticación usa `Auth::login($usuario)` **y** sesión manual simultáneamente para compatibilidad.
- El middleware de auth personalizado se llama `custom.auth`.
- El middleware de admin se llama `admin`.
- Las rutas de administración van con `prefix('admin')`.
- Los mensajes flash usan `with('success', ...)` y `with('error', ...)`.
- El campo de QR personal del usuario se llama `qr_codigo` en la tabla `usuarios`.
- Los correos se envían con Laravel Mail (Mailable) a través de la configuración SMTP del `.env`.

## Flujo del cliente

1. `/register` → Se crea el usuario → se envía `WelcomeMail`.
2. `/login` → Sesión iniciada → redirect a `/` que muestra el dashboard cliente.
3. Dashboard `/` → Muestra cupones asignados, resumen de compras, acceso a "Mi Tarjeta QR".
4. `/mi-tarjeta` → Muestra el QR personal del cliente para escanear en la sucursal.
5. `/tickets/create` → El cliente registra un ticket físico de compra.
6. `/mis-cupones` → El cliente ve todos sus cupones asignados.

## Flujo del administrador en sucursal

1. Admin abre `/admin/escanear-cliente` → escanea el QR del cliente.
2. El sistema identifica al cliente por `qr_codigo` y muestra su perfil, compras y cupones.
3. Desde ahí el admin puede asignar cupones o registrar compras para el cliente.

## Reglas al hacer cambios

- Siempre leer el archivo antes de modificarlo.
- Usar `multi_replace_string_in_file` para cambios en múltiples partes del mismo archivo.
- Las migraciones nuevas deben usar `Schema::hasColumn` para evitar duplicados.
- Al crear un Mailable, siempre crear también la vista en `resources/views/emails/`.
- No eliminar la lógica de sesión manual (`Session::put`) al modificar `AuthController`.
- Verificar errores con `get_errors` después de crear o modificar archivos PHP.
