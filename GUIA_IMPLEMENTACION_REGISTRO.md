# Guía de Implementación del Sistema de Registro Mejorado

## ✅ Cambios Implementados

### 1. Base de Datos

#### Tabla `usuarios` - Campos Nuevos/Modificados:
- ✅ `apellido_materno` - Ahora es **OBLIGATORIO**
- ✅ `email_verified_at` - Para verificación de correo
- ✅ `telefono` (VARCHAR 15, UNIQUE) - **OBLIGATORIO** con validación +52 + 10 dígitos
- ✅ `fecha_nacimiento` (DATE) - **OBLIGATORIO**, validación de mayoría de edad (18+)
- ✅ `rfc` (VARCHAR 13, UNIQUE) - **OBLIGATORIO**, validación de formato RFC
- ✅ `club_zarza` (BOOLEAN) - Flag para integración Oppen
- ✅ `oppen_customer_id` (VARCHAR, UNIQUE, NULLABLE) - ID del cliente en sistema Oppen

#### Nueva Tabla `codigos_postales`:
```sql
- id
- codigo_postal (VARCHAR 5, INDEX)
- estado (VARCHAR 100, INDEX)
- municipio (VARCHAR 100, INDEX)
- ciudad (VARCHAR 100, NULLABLE)
- colonia (VARCHAR 200)
- tipo_asentamiento (VARCHAR 50, NULLABLE)
- zona (VARCHAR 50, NULLABLE)
- created_at, updated_at
```

#### Tabla `direcciones` - Modificada:
- ✅ `codigo_postal_id` (FK a codigos_postales) - **OBLIGATORIO**
- ✅ `codigo_postal`, `estado`, `municipio`, `colonia` - Campos desnormalizados para consultas rápidas
- ❌ Eliminado: `ciudad` (reemplazado por `municipio`)

#### Nueva Tabla `email_verifications`:
```sql
- id
- usuario_id (FK)
- token (VARCHAR 64, UNIQUE)
- expires_at (TIMESTAMP)
- verified_at (TIMESTAMP, NULLABLE)
- created_at, updated_at
```

---

### 2. Formulario de Registro

#### Campos Obligatorios Implementados:
1. ✅ **Nombre** - Validación de caracteres
2. ✅ **Apellido Paterno** - Validación de caracteres
3. ✅ **Apellido Materno** - Validación de caracteres
4. ✅ **Correo Electrónico** - Validación de formato + unique
5. ✅ **Confirmación de Correo** - Debe coincidir con el correo
6. ✅ **Número de Teléfono** - Formato: +52 seguido de 10 dígitos
7. ✅ **Fecha de Nacimiento** - Validación de mayoría de edad (18+)
8. ✅ **RFC** - 13 caracteres alfanuméricos, validación de formato
9. ✅ **Estado** - Dropdown dinámico
10. ✅ **Municipio** - Dropdown dinámico (depende de Estado)
11. ✅ **Colonia** - Dropdown dinámico (depende de Municipio)
12. ✅ **CP** - Se asigna automáticamente según la colonia seleccionada
13. ✅ **Calle** - Campo de texto
14. ✅ **Número** - Campo de texto
15. ✅ **Contraseña** - Mínimo 8 caracteres
16. ✅ **Confirmar Contraseña** - Debe coincidir

---

### 3. Validaciones Implementadas

#### Frontend (JavaScript):
- ✅ Formateo automático de teléfono (+52XXXXXXXXXX)
- ✅ Formateo automático de RFC (mayúsculas, 13 caracteres)
- ✅ Validación de fecha máxima (mayoría de edad)
- ✅ Carga dinámica de dropdowns (Estado → Municipio → Colonia)
- ✅ Confirmación de email en tiempo real

#### Backend (Laravel):
- ✅ Email único en la base de datos
- ✅ Teléfono único con formato +52[0-9]{10}
- ✅ RFC único con formato [A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}
- ✅ Fecha de nacimiento: before:-18 years
- ✅ Confirmación de email
- ✅ Todos los campos de dirección obligatorios

---

### 4. Sistema de Verificación de Email

#### Flujo:
1. ✅ Usuario se registra
2. ✅ Sistema genera token único de verificación
3. ✅ Se envía email con link de verificación (implementado en logs por ahora)
4. ✅ Usuario NO puede iniciar sesión sin verificar email
5. ⏳ Usuario hace clic en link → email se marca como verificado
6. ⏳ Usuario ya puede iniciar sesión

**Nota:** El envío real de emails requiere configurar un servicio SMTP (pendiente).

---

### 5. Integración con Sistema Oppen

#### Validaciones Implementadas:
1. ✅ **Verificación por Email** - Busca si existe en usuarios
2. ✅ **Verificación por Teléfono** - Busca si existe en usuarios
3. ✅ **Verificación por RFC** - Busca si existe en usuarios

#### Lógica de Registro:
```
SI cliente NO existe en Oppen:
    → Crear nuevo cliente
    → Flag club_zarza = TRUE
    → Crear cuenta en sistema

SI cliente SÍ existe en Oppen:
    SI ya tiene club_zarza = TRUE:
        → Mostrar error: "Ya estás registrado en Club Zarza"
    SI tiene club_zarza = FALSE:
        → Actualizar flag club_zarza = TRUE
        → Crear/actualizar contraseña
        → Mensaje: "Bienvenido a Club Zarza"
```

---

## 📋 Pasos Pendientes de Implementación

### 1. Cargar Datos de Códigos Postales

**Fuente oficial:** 
https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx

#### Opción A: Usando Seeder (Recomendado)

Crear archivo `database/seeders/CodigosPostalesSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CodigoPostal;
use Illuminate\Support\Facades\DB;

class CodigosPostalesSeeder extends Seeder
{
    public function run(): void
    {
        // Descargar archivo de Correos de México
        // Formato esperado: CP|Estado|Municipio|Colonia|TipoAsentamiento|Zona
        
        $file = storage_path('app/codigos_postales.txt');
        
        if (!file_exists($file)) {
            $this->command->error('Archivo no encontrado. Descárgalo de correosdemexico.gob.mx');
            return;
        }
        
        DB::table('codigos_postales')->truncate();
        
        $handle = fopen($file, 'r');
        $batch = [];
        $count = 0;
        
        while (($line = fgets($handle)) !== false) {
            $data = explode('|', trim($line));
            
            if (count($data) >= 5) {
                $batch[] = [
                    'codigo_postal' => $data[0],
                    'estado' => $data[1],
                    'municipio' => $data[2],
                    'colonia' => $data[3],
                    'tipo_asentamiento' => $data[4] ?? null,
                    'zona' => $data[5] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $count++;
                
                // Insertar en lotes de 1000 para mejor performance
                if (count($batch) >= 1000) {
                    DB::table('codigos_postales')->insert($batch);
                    $batch = [];
                    $this->command->info("Insertados $count registros...");
                }
            }
        }
        
        // Insertar el último lote
        if (!empty($batch)) {
            DB::table('codigos_postales')->insert($batch);
        }
        
        fclose($handle);
        
        $this->command->info("✅ Total de códigos postales importados: $count");
    }
}
```

**Ejecutar:**
```bash
php artisan db:seed --class=CodigosPostalesSeeder
```

#### Opción B: Importación directa desde CSV

1. Descarga el archivo de Correos de México
2. Guárdalo en `storage/app/codigos_postales.txt`
3. Ejecuta el seeder

---

### 2. Configurar Envío de Emails

**Archivo:** `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com  # O tu proveedor
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@La Zarza Contigo.com
MAIL_FROM_NAME="La Zarza Contigo"
```

**Crear Mailable:**
```bash
php artisan make:mail VerifyEmail
```

**Actualizar método en AuthController:**
```php
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

private function enviarEmailVerificacion($userId, $email, $nombre)
{
    $token = bin2hex(random_bytes(32));
    
    // Guardar token en BD
    DB::table('email_verifications')->insert([
        'usuario_id' => $userId,
        'token' => $token,
        'expires_at' => now()->addHours(24),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Enviar email
    Mail::to($email)->send(new VerifyEmail($nombre, $token, $userId));
}
```

---

### 3. Crear Ruta de Verificación

**Archivo:** `routes/web.php`

```php
Route::get('/verify-email/{userId}/{token}', [AuthController::class, 'verifyEmail'])
    ->name('verify.email');
```

**Método en AuthController:**
```php
public function verifyEmail($userId, $token)
{
    $verification = DB::table('email_verifications')
        ->where('usuario_id', $userId)
        ->where('token', $token)
        ->where('expires_at', '>', now())
        ->whereNull('verified_at')
        ->first();
    
    if (!$verification) {
        return redirect('/login')->with('error', 'Link de verificación inválido o expirado.');
    }
    
    // Marcar email como verificado
    DB::table('usuarios')
        ->where('id', $userId)
        ->update(['email_verified_at' => now()]);
    
    DB::table('email_verifications')
        ->where('id', $verification->id)
        ->update(['verified_at' => now()]);
    
    return redirect('/login')->with('success', '¡Email verificado exitosamente! Ya puedes iniciar sesión.');
}
```

---

### 4. Validar Email Verificado en Login

**Actualizar método `login` en AuthController:**

```php
// Después de verificar credenciales
if (!$usuario['email_verified_at']) {
    return redirect()->route('login')->withErrors([
        'email' => 'Debes verificar tu correo electrónico antes de iniciar sesión.'
    ])->with('error', '❌ Email no verificado. Revisa tu bandeja de entrada.');
}
```

---

### 5. Integración Real con Sistema Oppen

**Pendiente:** Documentación de la API de Oppen

Actualizar método `verificarClienteEnOppen()` para consumir API real:

```php
private function verificarClienteEnOppen($email, $telefono, $rfc)
{
    // TODO: Implementar llamada a API de Oppen
    $response = Http::post('https://api-oppen.com/clientes/buscar', [
        'email' => $email,
        'telefono' => $telefono,
        'rfc' => $rfc,
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        return [
            'existe' => $data['exists'],
            'tiene_club_zarza' => $data['club_zarza'] ?? false,
            'oppen_id' => $data['customer_id'] ?? null,
        ];
    }
    
    return null;
}
```

---

## 🚀 Ejecución de Migraciones

**Importante:** Estas migraciones modifican campos existentes. Si ya tienes datos en producción, necesitarás un plan de migración.

### Para desarrollo (fresh start):
```bash
php artisan migrate:fresh --seed
```

### Para producción (con datos existentes):
```bash
# 1. Crear backup de la BD
php artisan db:backup  # O manualmente

# 2. Ejecutar migraciones
php artisan migrate

# 3. Si hay errores, necesitarás crear migraciones adicionales para:
#    - Rellenar campos obligatorios con datos por defecto
#    - Actualizar registros existentes
```

---

## 📝 Notas Importantes

1. **Códigos Postales**: La tabla puede tener más de 100,000 registros. Asegúrate de tener suficiente espacio en la BD.

2. **Validación de RFC**: El formato implementado es básico. Para validación completa (verificar dígito verificador), se necesita un algoritmo más complejo.

3. **Teléfonos**: Solo se valida formato mexicano (+52). Para números internacionales, ajustar validación.

4. **Email Verification**: Los tokens tienen validez de 24 horas. Ajustar según necesidades.

5. **Performance**: Los dropdowns dinámicos pueden ser lentos si hay muchos municipios/colonias. Considera implementar paginación o búsqueda.

---

## 🧪 Testing

Crear tests para validar:

```bash
php artisan make:test RegistroUsuarioTest
```

```php
public function test_registro_requiere_todos_los_campos()
{
    $response = $this->post('/register', []);
    
    $response->assertSessionHasErrors([
        'nombres', 'apellido_paterno', 'apellido_materno',
        'email', 'telefono', 'fecha_nacimiento', 'rfc',
        'estado', 'municipio', 'colonia', 'calle', 'numero'
    ]);
}

public function test_telefono_debe_tener_formato_correcto()
{
    $response = $this->post('/register', [
        'telefono' => '1234567890',  // Sin +52
        // ... otros campos
    ]);
    
    $response->assertSessionHasErrors('telefono');
}

public function test_usuario_debe_ser_mayor_de_edad()
{
    $response = $this->post('/register', [
        'fecha_nacimiento' => now()->subYears(17),  // Menor de edad
        // ... otros campos
    ]);
    
    $response->assertSessionHasErrors('fecha_nacimiento');
}
```

---

## 📞 Soporte

Para dudas o problemas con la implementación, revisar:
- Logs de Laravel: `storage/logs/laravel.log`
- Errores de migraciones: Verificar estructura de BD actual
- Errores de validación: Revisar mensajes en formulario
