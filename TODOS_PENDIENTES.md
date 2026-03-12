# TODOs Pendientes del Sistema

## 📋 Estado: Documentación actualizada al 2024

### 🔴 Alta Prioridad (Críticos)

#### 1. Consolidar Sistema de Autenticación Dual ⚠️
**Ubicación:** Múltiples controladores  
**Problema:** Sistema dual confuso con Laravel Auth + Sesiones manuales  
**Archivos afectados:**
- `app/Http/Controllers/Web/CouponsController.php` (línea 19-32)
- `app/Http/Controllers/Web/TransactionController.php` (línea 21-28)
- `app/Http/Controllers/Web/DashboardController.php` (línea 28-52)

**Solución propuesta:**
- ✅ COMPLETADO: Crear `AuthService` centralizado
- ⏳ PENDIENTE: Refactorizar todos los controladores para usar `AuthService`
- ⏳ PENDIENTE: Eliminar métodos `getCurrentUser()` duplicados
- ⏳ PENDIENTE: Migrar completamente a Laravel Auth

**Estimación:** 4-6 horas

---

#### 2. Implementar Factory para Usuario
**Ubicación:** `database/factories/`  
**Problema:** Los tests requieren `Usuario::factory()` pero no existe el factory  
**Solución propuesta:**
- Crear `UserFactory.php` con definición completa
- Incluir relaciones (Puntos, Direccion)
- Agregar states (admin, cliente)

**Estimación:** 1-2 horas

---

### 🟡 Media Prioridad (Importantes)

#### 3. Completar Método register() en AuthService
**Ubicación:** `app/Services/AuthService.php` (línea 161-180)  
**Estado:** Stub/placeholder  
**Pendiente:**
- Implementar lógica completa de registro
- Crear usuario con direcciones
- Inicializar saldo de puntos
- Registrar en auditoría

**Estimación:** 2-3 horas

---

#### 4. Crear Tests de Integración
**Ubicación:** `tests/Feature/`  
**Pendiente:**
- Tests de flujo completo de autenticación
- Tests de canje de cupones end-to-end
- Tests de registro de compras
- Tests de generación de reportes

**Estimación:** 6-8 horas

---

#### 5. Implementar Validación con Form Requests
**Ubicación:** `app/Http/Requests/`  
**Problema:** Validación dispersa en controllers  
**Solución propuesta:**
- Crear `LoginRequest.php`
- Crear `RegisterRequest.php`
- Crear `CouponRedeemRequest.php`
- Crear `PurchaseCreateRequest.php`

**Estimación:** 3-4 horas

---

#### 6. Agregar Middleware de Roles
**Ubicación:** `app/Http/Middleware/`  
**Problema:** Verificación de roles manual en cada controlador  
**Solución propuesta:**
- Crear `CheckRole` middleware
- Crear `CheckAdmin` middleware
- Registrar en `app/Http/Kernel.php`
- Aplicar en rutas

**Estimación:** 2-3 horas

---

### 🟢 Baja Prioridad (Mejoras)

#### 7. Implementar Cache para Consultas Frecuentes
**Ubicación:** Services  
**Pendiente:**
- Cache de cupones disponibles
- Cache de estadísticas del sistema
- Cache de usuarios top por puntos

**Estimación:** 3-4 horas

---

#### 8. Crear Jobs para Procesos Largos
**Ubicación:** `app/Jobs/`  
**Pendiente:**
- `SendPurchaseNotificationJob`
- `GenerateMonthlyReportJob`
- `ExpireCouponsJob`

**Estimación:** 4-5 horas

---

#### 9. Implementar API Resources
**Ubicación:** `app/Http/Resources/`  
**Problema:** Respuestas JSON inconsistentes  
**Pendiente:**
- `UserResource.php`
- `CouponResource.php`
- `TransactionResource.php`
- `PurchaseResource.php`

**Estimación:** 3-4 horas

---

#### 10. Agregar Observadores de Modelos
**Ubicación:** `app/Observers/`  
**Pendiente:**
- `UserObserver` - Crear registro de Puntos al registrar usuario
- `PurchaseObserver` - Registrar automáticamente transacción
- `CouponObserver` - Auditoría de cambios

**Estimación:** 2-3 horas

---

#### 11. Completar Cobertura de Tests
**Estado actual:** ~30% (estimado)  
**Objetivo:** >80%  
**Pendiente:**
- Tests para todos los Services
- Tests para todos los Repositories
- Tests para CouponService
- Tests para NotificationController

**Estimación:** 10-12 horas

---

#### 12. Implementar Logging Estructurado
**Ubicación:** Todos los controladores y servicios  
**Pendiente:**
- Logs de autenticación
- Logs de canjes
- Logs de errores con contexto
- Configurar canales de log

**Estimación:** 2-3 horas

---

#### 13. Refactorizar Vistas a Componentes
**Ubicación:** `resources/views/`  
**Problema:** Código HTML repetido  
**Pendiente:**
- Crear componentes Blade reutilizables
- `<x-alert>`, `<x-card>`, `<x-button>`
- Consolidar estilos

**Estimación:** 6-8 horas

---

#### 14. Agregar Documentación de API
**Ubicación:** `docs/`  
**Pendiente:**
- Documentar endpoints
- Ejemplos de requests/responses
- Colección de Postman actualizada

**Estimación:** 4-5 horas

---

## 📊 Resumen por Prioridad

| Prioridad | Cantidad | Horas Estimadas |
|-----------|----------|-----------------|
| 🔴 Alta | 2 | 5-8 horas |
| 🟡 Media | 5 | 19-26 horas |
| 🟢 Baja | 7 | 34-44 horas |
| **TOTAL** | **14** | **58-78 horas** |

---

## 🎯 Plan de Acción Recomendado

### Sprint 1 (1 semana)
1. ✅ Consolidar sistema de autenticación
2. Implementar Factory para Usuario
3. Crear middleware de roles

### Sprint 2 (1 semana)
4. Completar AuthService.register()
5. Implementar Form Requests
6. Crear tests de integración básicos

### Sprint 3 (1 semana)
7. Agregar Observadores de Modelos
8. Implementar logging estructurado
9. Crear API Resources

### Sprint 4 (1 semana)
10. Implementar Cache
11. Crear Jobs para procesos largos
12. Aumentar cobertura de tests

---

## 📝 Notas Adicionales

### Dependencias
- **AuthService completo** → Requerido para refactorizar controladores
- **Factory Usuario** → Requerido para ejecutar tests
- **Form Requests** → Mejora validación antes de Services

### Riesgos Identificados
- ⚠️ Migración de autenticación podría romper sesiones activas
- ⚠️ Tests requieren fábrica funcional de Usuario
- ⚠️ Cambios en Services requieren actualizar controladores

### Criterios de Aceptación
- ✅ Todos los tests pasan
- ✅ Cobertura de código >80%
- ✅ Sin duplicación de lógica de autenticación
- ✅ Validación centralizada
- ✅ Documentación actualizada

---

**Última actualización:** 2024  
**Responsable:** Equipo de Desarrollo  
**Estado:** 🟡 En Progreso (Servicios y Repositorios completados)
