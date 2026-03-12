# 🎯 Sistema de Registro Mejorado - La Zarza Contigo

## 📋 Resumen de Cambios

Se ha implementado un sistema completo de registro con los siguientes cambios:

### ✅ Campos del Formulario (Todos Obligatorios)

1. **Nombre**
2. **Apellido Paterno**
3. **Apellido Materno**
4. **Correo Electrónico** (con confirmación)
5. **Número de Teléfono** (+52 + 10 dígitos)
6. **Fecha de Nacimiento** (validación 18+)
7. **RFC** (13 caracteres)
8. **Contraseña** (mínimo 8 caracteres)
9. **Estado** (dropdown dinámico)
10. **Municipio** (dropdown dinámico)
11. **Colonia** (dropdown dinámico con CP)
12. **Calle**
13. **Número**

### 🔐 Validaciones Implementadas

- ✅ **Email único** - Verificación en BD y confirmación
- ✅ **Teléfono único** - Formato: +52XXXXXXXXXX
- ✅ **RFC único** - Formato válido de 13 caracteres
- ✅ **Mayoría de edad** - 18 años mínimo
- ✅ **Dropdowns dinámicos** - Estado → Municipio → Colonia
- ✅ **Verificación de email** - Sistema de tokens

### 🔗 Integración con Oppen

El sistema verifica si el usuario ya existe en Oppen por:
- Email
- Teléfono
- RFC

**Lógica:**
- Si NO existe → Crear nuevo usuario
- Si SÍ existe y tiene club_zarza → Error "Ya registrado"
- Si SÍ existe pero sin club_zarza → Actualizar flag a TRUE

---

## 🚀 Instalación y Configuración

### Paso 1: Ejecutar Migraciones

```bash
# Opción A: Fresh install (borra datos)
php artisan migrate:fresh

# Opción B: Solo nuevas migraciones (mantiene datos)
php artisan migrate
```

### Paso 2: Importar Códigos Postales

#### 2.1. Descargar archivo oficial
Visita: https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx

#### 2.2. Guardar en storage
Guarda el archivo descargado como:
```
storage/app/codigos_postales.txt
```

#### 2.3. Ejecutar seeder
```bash
php artisan db:seed --class=CodigosPostalesSeeder
```

**Nota:** Este proceso puede tardar varios minutos (importa +100k registros)

### Paso 3: Configurar Email (Opcional)

Edita el archivo `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@La Zarza Contigo.com
MAIL_FROM_NAME="La Zarza Contigo"
```

**Sin configuración de email:**
- Los registros funcionarán normalmente
- Los tokens se registrarán en logs: `storage/logs/laravel.log`
- Los usuarios NO podrán verificar su email (opcional por ahora)

---

## 📁 Archivos Creados/Modificados

### Migraciones
- ✅ `2024_01_01_000001_create_usuarios_table.php` (modificada)
- ✅ `2024_01_01_000002_create_codigos_postales_table.php` (nueva)
- ✅ `2024_01_01_000003_create_direcciones_table.php` (modificada)
- ✅ `2024_01_01_000009_create_email_verifications_table.php` (nueva)

### Modelos
- ✅ `app/Models/Usuario.php` (modificado)
- ✅ `app/Models/Direccion.php` (modificado)
- ✅ `app/Models/CodigoPostal.php` (nuevo)

### Controladores
- ✅ `app/Http/Controllers/Web/AuthController.php` (modificado)
- ✅ `app/Http/Controllers/Api/CodigoPostalController.php` (nuevo)

### Vistas
- ✅ `resources/views/auth/register.blade.php` (modificado)

### Rutas
- ✅ `routes/api.php` (modificada - endpoints de códigos postales)

### Seeders
- ✅ `database/seeders/CodigosPostalesSeeder.php` (nuevo)

### Documentación
- ✅ `GUIA_IMPLEMENTACION_REGISTRO.md` (nueva)
- ✅ `README_REGISTRO.md` (este archivo)

---

## 🧪 Pruebas

### Probar el formulario de registro

1. Inicia el servidor:
```bash
php artisan serve
```

2. Visita: http://localhost:8000/register

3. Verifica que:
   - ✅ Todos los campos aparecen
   - ✅ Los dropdowns de Estado/Municipio/Colonia cargan correctamente
   - ✅ El teléfono se formatea automáticamente
   - ✅ El RFC se convierte a mayúsculas
   - ✅ La validación de mayoría de edad funciona
   - ✅ Los mensajes de error se muestran correctamente

### Probar las APIs

```bash
# Obtener estados
curl http://localhost:8000/api/codigos-postales/estados

# Obtener municipios de un estado
curl "http://localhost:8000/api/codigos-postales/municipios?estado=CIUDAD%20DE%20MEXICO"

# Obtener colonias de un municipio
curl "http://localhost:8000/api/codigos-postales/colonias?estado=CIUDAD%20DE%20MEXICO&municipio=CUAUHTEMOC"
```

---

## ⚠️ Problemas Comunes

### Error: "Table codigos_postales doesn't exist"
**Solución:** Ejecuta las migraciones
```bash
php artisan migrate
```

### Error: "Call to undefined method"
**Solución:** Limpia cache
```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

### Los dropdowns no cargan
**Solución:** Verifica que:
1. Las rutas API estén registradas
2. La tabla `codigos_postales` tenga datos
3. No haya errores en la consola del navegador (F12)

### El teléfono no acepta el formato
**Solución:** El formato correcto es:
- Ingresa: `1234567890`
- Se formatea automáticamente a: `+521234567890`

---

## 📊 Datos de Prueba

Para crear un usuario de prueba con todos los campos:

```php
// En tinker: php artisan tinker

use App\Models\Usuario;
use App\Models\CodigoPostal;
use App\Models\Direccion;

$cp = CodigoPostal::first();

$usuario = Usuario::create([
    'nombres' => 'Juan',
    'apellido_paterno' => 'Pérez',
    'apellido_materno' => 'García',
    'email' => 'juan.perez@example.com',
    'telefono' => '+525512345678',
    'fecha_nacimiento' => '1990-01-01',
    'rfc' => 'PEGJ900101ABC',
    'password' => bcrypt('password123'),
    'email_verified_at' => now(),
]);

Direccion::create([
    'usuario_id' => $usuario->id,
    'calle' => 'Reforma',
    'numero' => '123',
    'codigo_postal_id' => $cp->id,
    'codigo_postal' => $cp->codigo_postal,
    'estado' => $cp->estado,
    'municipio' => $cp->municipio,
    'colonia' => $cp->colonia,
    'principal' => true,
]);
```

---

## 📞 Soporte

### Logs
Revisa los logs en caso de errores:
```bash
tail -f storage/logs/laravel.log
```

### Verificar estructura de BD
```bash
php artisan db:show
php artisan db:table usuarios
php artisan db:table codigos_postales
```

### Documentación completa
Ver: `GUIA_IMPLEMENTACION_REGISTRO.md`

---

## 🎯 Próximos Pasos

1. ⏳ Implementar envío real de emails de verificación
2. ⏳ Crear vista para verificar email
3. ⏳ Integración real con API de Oppen
4. ⏳ Tests automatizados
5. ⏳ Optimizar performance de dropdowns (búsqueda incremental)

---

## ✅ Checklist de Implementación

- [x] Migraciones de BD
- [x] Modelos actualizados
- [x] Formulario de registro
- [x] Validaciones backend
- [x] Validaciones frontend
- [x] API de códigos postales
- [x] Dropdowns dinámicos
- [x] Sistema de verificación de email (base)
- [x] Integración con Oppen (base)
- [x] Seeder de códigos postales
- [ ] Envío de emails (requiere configuración SMTP)
- [ ] Ruta de verificación de email
- [ ] Integración real con Oppen (requiere documentación API)
- [ ] Tests automatizados

---

**Fecha de implementación:** <?= date('Y-m-d') ?>  
**Versión:** 1.0.0
