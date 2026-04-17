# 🚀 Cómo Iniciar el Servidor Laravel

## Problema Identificado

Laravel no puede ejecutarse correctamente con `--host=localhost` desde rutas de red UNC debido a un bug de PHP con la función `is_writable()` en Windows.

## ✅ Solución Correcta

### Opción 1: Usar Script Automatizado (RECOMENDADO) ⭐

Ejecuta uno de los siguientes scripts:

**PowerShell:**
```powershell
.\servidor.ps1
```

**Batch/CMD:**
```bat
.\iniciar_servidor.bat
```

**O ejecuta directamente:**
```bat
.\ACCESO_RAPIDO.bat
```

Este script te preguntará si deseas iniciar el servidor si no está corriendo.

### Opción 2: Comando Directo

```bash
php artisan serve --host=172.16.1.44 --port=8000
```

**IMPORTANTE:** Usa `--host=172.16.1.44` en lugar de `--host=localhost`

## 🌐 Acceso a la Aplicación

Una vez iniciado el servidor:

**URL Principal:**
```
http://172.16.1.44:8000
```

**Rutas importantes:**
- Login: `http://172.16.1.44:8000/login`
- Registro: `http://172.16.1.44:8000/register`
- Dashboard Admin: `http://172.16.1.44:8000/admin/dashboard`
- Panel Cliente: `http://172.16.1.44:8000/compras`

## 🔑 Credenciales de Prueba

### Administrador
```
Email: admin@test.com
Password: password
```

### Cliente
```
Email: cliente@test.com
Password: password
```

## 🛠️ Comandos Útiles

### Verificar Estado del Servidor
```powershell
netstat -an | findstr ":8000"
```

### Limpiar Caché de Laravel
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Verificar Conexión a Base de Datos
```bash
php artisan db:show
```

### Ver Migraciones
```bash
php artisan migrate:status
```

## ⚙️ Configuración Actual

- **Servidor:** 172.16.1.44
- **Puerto:** 8000
- **Base de Datos:** PostgreSQL en AWS RDS
- **Schema:** appweb
- **PHP:** 8.2.12

## 📝 Notas Técnicas

### ¿Por qué no funciona con localhost?

PHP en Windows tiene un bug conocido donde `is_writable()` siempre devuelve `false` en:
- Rutas UNC (`\\172.16.1.44\...`)
- Unidades de red mapeadas (Z:, etc.)

Laravel usa `is_writable()` para verificar si puede escribir en `bootstrap/cache`, por lo que falla la inicialización.

### Soluciones Aplicadas

1. ✅ Parches aplicados a:
   - `vendor/laravel/framework/src/Illuminate/Foundation/PackageManifest.php`
   - `vendor/laravel/framework/src/Illuminate/Foundation/ProviderRepository.php`

2. ✅ Scripts de inicio configurados para usar IP específica

3. ✅ Conexión a Data Warehouse verificada y funcionando

## 🔍 Troubleshooting

### Error: "Address already in use"
```bash
# Ver qué proceso usa el puerto 8000
netstat -ano | findstr :8000

# Matar el proceso (reemplaza PID con el número de la columna de la derecha)
taskkill /PID <PID> /F
```

### Error: "Could not open input file: artisan"
```bash
# Asegúrate de estar en el directorio correcto
cd \\172.16.1.44\htdocs\appwebzarza
```

### Error: "Connection refused" de Base de Datos
```bash
# Verifica el archivo .env
cat .env | findstr DB_

# Prueba la conexión
php test_db_simple.php
```

## 📞 Soporte

Si tienes problemas, revisa los archivos de log:
- `storage/logs/laravel.log`
- Ejecuta `.\diagnostico_simple.ps1` para diagnóstico automático
