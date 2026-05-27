# Proyecto Laravel - Sistema de Puntos de Fidelidad

## Descripción del Proyecto
Sistema de gestión de puntos de fidelidad para clientes con las siguientes funcionalidades:
- Gestión de usuarios (clientes y administradores)
- Sistema de direcciones múltiples por usuario
- Gestión de puntos y transacciones
- Sistema de cupones y canjes
- Gestión de sucursales
- Sistema de auditoría completo
- **Sistema de geolocalización GPS** para captura automática de ubicación de usuarios

## Estado del Proyecto
- [x] ✅ Clarificar Requisitos del Proyecto
- [x] ✅ Crear estructura inicial del proyecto
- [x] ✅ Scaffolding del Proyecto Laravel
- [x] ✅ Personalizar el Proyecto (Crear Migraciones y Modelos)
- [x] ✅ Instalar Extensiones Requeridas (No requeridas)
- [x] ✅ Compilar el Proyecto
- [x] ✅ Sistema de Geolocalización GPS
- [ ] Crear y Ejecutar Tareas
- [ ] Lanzar el Proyecto
- [ ] Documentación Completa

## Base de Datos
**Motor:** PostgreSQL en AWS RDS  
**Schema:** appweb  
**Ubicación:** data-warehouse.cn1hqjnw6sbe.us-east-2.rds.amazonaws.com

El proyecto incluye las siguientes entidades principales:
- USUARIOS (con roles cliente/admin + campos de geolocalización GPS)
- DIRECCIONES
- PUNTOS
- SUCURSALES
- COMPRAS
- TRANSACCIONES_PUNTOS
- CUPONES
- CUPONES_ASIGNADOS
- REDENCIONES
- AUDITORIA

### Sistema de Tracking de Ubicación para Marketing
Tabla separada `ubicaciones_usuarios` para almacenar ubicaciones con fines de marketing:

**Características:**
- Historial completo de ubicaciones por usuario
- Tracking de visitantes anónimos (sin usuario_id)
- Información de dispositivo, navegador y sistema operativo
- Contexto: evento, página origen, sesión
- Identificación de primeras visitas
- Metadata JSON para datos adicionales

**Campos principales:**
- Ubicación: `latitud`, `longitud`, `ciudad`, `estado`, `pais`, `codigo_postal`
- Dispositivo: `dispositivo`, `navegador`, `sistema_operativo`, `user_agent`, `ip_address`
- Contexto: `evento`, `pagina_origen`, `session_id`, `es_primera_visita`
- Relación: `usuario_id` (nullable para anónimos)

**API Endpoints:**
- `POST /api/v1/location` - Guardar ubicación (público)
- `GET /api/v1/location` - Última ubicación del usuario (autenticado)
- `GET /api/v1/locations` - Historial de ubicaciones (autenticado)
- `GET /api/v1/admin/reports/locations` - Estadísticas (solo admin)

**Modelo:** `App\Models\UbicacionUsuario`

**Documentación:**
- Guía marketing: `GEOLOCATION_MARKETING.md`
- Guía rápida: `GEOLOCATION_QUICKSTART.md`
- PostgreSQL: `GEOLOCATION_POSTGRES.md`

## Tecnologías
- Laravel (PHP Framework)
- PostgreSQL en AWS RDS
- Geolocation API (navegador)
- Nominatim/OpenStreetMap (Reverse Geocoding)
- Git para control de versiones