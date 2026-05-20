# Sistema de Puntos de Fidelidad - Laravel

## Descripción
Sistema completo de gestión de puntos de fidelidad desarrollado en Laravel que permite administrar clientes, sucursales, compras, cupones y un sistema completo de auditoría.

## Características Principales

### 👥 Gestión de Usuarios
- Sistema de autenticación con roles (Cliente/Administrador)
- Perfil completo con datos personales
- Gestión de múltiples direcciones por usuario
- Dirección principal configurable

### 🏪 Gestión de Sucursales
- Registro de sucursales con códigos únicos
- Información completa de contacto y ubicación
- Trazabilidad de modificaciones

### 💰 Sistema de Puntos
- Acumulación automática de puntos por compras
- Historial completo de transacciones
- Saldo en tiempo real por usuario
- Tipos de transacción: compra, canje, ajuste

### 🎫 Sistema de Cupones
- Creación de cupones con vigencia
- Asignación automática por puntos requeridos
- Códigos QR únicos para redención
- Estados: pendiente, redimido, vencido

### 📊 Sistema de Auditoría
- Registro completo de cambios en el sistema
- Trazabilidad de quién, qué y cuándo
- Almacenamiento en formato JSON para análisis

## Estructura de Base de Datos

### Tablas Principales
- **usuarios**: Gestión de usuarios y roles
- **direcciones**: Direcciones múltiples por usuario
- **puntos**: Saldo actual de puntos por usuario
- **sucursales**: Información de sucursales
- **compras**: Registro de compras y puntos generados
- **transacciones_puntos**: Historial de movimientos de puntos
- **cupones**: Definición de cupones disponibles
- **cupones_asignados**: Cupones asignados a usuarios
- **redenciones**: Registro de cupones redimidos
- **auditoria**: Trazabilidad completa del sistema

## Instalación

### Desarrollo Local

### Requisitos
- PHP 8.2 o superior
- Composer
- MySQL/MariaDB
- Servidor web (Apache/Nginx)

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd appwebzarza
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
   Editar el archivo `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=appwebuser
DB_PASSWORD=appwebpass
DB_SCHEMA=appweb
```

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

**Nota**: Este proyecto está configurado para PostgreSQL y usa el schema `appweb`. Las migraciones se han ejecutado exitosamente.

6. **Datos de ejemplo incluidos**
   El sistema incluye datos de ejemplo pre-configurados:
   - **Admin**: `admin@puntosfidelidad.com` / `password123`
   - **Cliente**: `cliente@example.com` / `password123` (150 puntos)
   - 3 sucursales operativas
   - 3 cupones disponibles para canje
   - 1 compra de ejemplo registrada

## Modelos y Relaciones

### Usuario
- Tiene múltiples direcciones
- Tiene un saldo de puntos
- Realiza compras
- Recibe cupones asignados
- Genera transacciones de puntos

### Puntos
- Pertenece a un usuario (relación 1:1)
- Se actualiza con cada transacción
- Métodos para agregar/descontar puntos

### Compra
- Pertenece a un usuario y sucursal
- Genera puntos automáticamente
- Crea transacción de puntos asociada

### Cupón
- Puede ser asignado a múltiples usuarios
- Tiene fechas de vigencia
- Requiere puntos específicos para canje

### Sistema de Auditoría
- Registra todas las operaciones CRUD
- Almacena el estado anterior y nuevo
- Identifica al usuario que realizó el cambio

## Desarrollo

### Comandos Útiles
```bash
# Crear migración
php artisan make:migration create_table_name

# Crear modelo
php artisan make:model ModelName

# Crear controlador
php artisan make:controller ControllerName

# Ejecutar tests
php artisan test

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Estructura de Archivos
```
app/
├── Models/              # Modelos Eloquent
│   ├── Usuario.php
│   ├── Direccion.php
│   ├── Puntos.php
│   └── ...
├── Http/Controllers/    # Controladores
└── Services/           # Lógica de negocio

database/
├── migrations/         # Migraciones de BD
├── seeders/           # Datos de prueba
└── queries.sql        # Consultas SQL útiles
```

### Consultas SQL Útiles
El archivo `database/queries.sql` contiene consultas predefinidas para:
- Verificar estructura de tablas
- Consultar saldos de puntos por usuario
- Historial de compras y transacciones
- Reportes de ventas por sucursal
- Sistema de auditoría
- Cupones disponibles por usuario

## 🚀 Deployment a Producción

### Deployment a VPS con CyberPanel (Hostinger - AlmaLinux 9)

⭐ **TU CASO:** Servidor con CyberPanel + OpenLiteSpeed

Para desplegar esta aplicación en un VPS con CyberPanel:

📋 **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** ⭐ - **Guía específica para CyberPanel**  
📝 **[START_HERE.md](START_HERE.md)** - Guía rápida simplificada (~60 min)

#### Para servidores tradicionales (sin CyberPanel)

📋 **[DEPLOYMENT.md](DEPLOYMENT.md)** - Guía completa con Nginx/Apache  
📝 **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Checklist detallado

#### Archivos de Configuración Incluidos

**Para CyberPanel:**
- **deploy-cyberpanel.sh** ⭐ - Script automatizado para OpenLiteSpeed
- **.env.production.example** - Variables de entorno

**Para servidores tradicionales:**
- **deploy.sh** - Script para Nginx/Apache
- **nginx.conf** - Configuración Nginx
- **apache.conf** - Configuración Apache  
- **server-commands.sh** - Comandos útiles

#### Deployment Rápido con CyberPanel

```bash
# 1. Primera vez: Seguir DEPLOYMENT_CYBERPANEL.md (~60 min)

# 2. Actualizaciones futuras (1-2 min):
ssh root@ip-del-servidor
bash ~/deploy-appwebzarza.sh
```

#### Características del Deployment con CyberPanel

✅ Interface web para gestión  
✅ OpenLiteSpeed (más rápido que Apache/Nginx)  
✅ SSL Let's Encrypt con 1-click  
✅ HTTP/3 soportado nativamente  
✅ Modo mantenimiento automático  
✅ Pull desde Git automático  
✅ Instalación de dependencias  
✅ Migraciones automáticas  
✅ Optimización de caché  
✅ Verificación de salud  

#### Requisitos del Servidor

- AlmaLinux 9 (o similar RHEL-based)
- **CyberPanel instalado** (incluye todo lo siguiente):
  - OpenLiteSpeed
  - PHP 8.2+
  - Composer
  - Git
  - Node.js & NPM
- MySQL/MariaDB (remoto o local)

## Contribución
1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia
Este proyecto está bajo la Licencia MIT.

## Soporte
Para soporte técnico o consultas sobre el sistema de puntos de fidelidad.

## Changelog
### v1.0.0 (2024-10-07)
- ✅ Estructura inicial del proyecto
- ✅ Migraciones de base de datos completas
- ✅ Modelos Eloquent con relaciones
- ✅ Sistema de auditoría implementado
- ✅ Documentación completa

---
Desarrollado con ❤️ usando Laravel Framework

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
