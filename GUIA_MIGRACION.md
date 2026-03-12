# 📦 GUÍA DE MIGRACIÓN - La Zarza Contigo a Servidor Local

## 🎯 Objetivo
Migrar el sistema La Zarza Contigo desde tu ambiente de desarrollo (XAMPP) a un servidor de prueba local.

---

## 📋 CHECKLIST PRE-MIGRACIÓN

### ✅ Lo Que Necesitas Preparar:

1. **Respaldo de Base de Datos** ✅
2. **Archivos del Proyecto** ✅
3. **Configuración del Servidor** ✅
4. **Variables de Entorno** ✅
5. **Dependencias de PHP** ✅

---

## 🗄️ PASO 1: RESPALDAR LA BASE DE DATOS

### Opción A: Respaldo Completo con pg_dump (Recomendado)

```bash
# Crear carpeta de respaldos
mkdir respaldos
cd respaldos

# Exportar SOLO el esquema appweb
pg_dump -h localhost -p 5432 -U appwebuser -d postgres -n appweb --schema-only > schema_appweb.sql

# Exportar los DATOS del esquema appweb
pg_dump -h localhost -p 5432 -U appwebuser -d postgres -n appweb --data-only > datos_appweb.sql

# O crear un respaldo completo (esquema + datos)
pg_dump -h localhost -p 5432 -U appwebuser -d postgres -n appweb > respaldo_completo_appweb.sql
```

**Contraseña cuando la pida:** `appwebpass`

### Opción B: Script PHP de Respaldo (Más Fácil)

```php
# Ejecuta este comando:
php crear_respaldo_bd.php
```

Voy a crear este script para ti...

---

## 📁 PASO 2: PREPARAR ARCHIVOS DEL PROYECTO

### A. Crear Paquete del Proyecto

```bash
# Desde la raíz del proyecto
cd C:\xampp\htdocs\appwebzarza

# Comprimir proyecto (sin node_modules, vendor, etc.)
# Opción 1: Con 7zip (si lo tienes instalado)
7z a -xr!node_modules -xr!vendor -xr!.git -xr!storage\logs La Zarza Contigo_v1.0.zip .

# Opción 2: Manualmente
# Copia la carpeta completa EXCEPTO:
#  - node_modules/
#  - vendor/ (se reinstalará)
#  - .git/ (opcional)
#  - storage/logs/* (logs viejos)
#  - bootstrap/cache/* (cache)
```

### B. Lista de Archivos Críticos

✅ **Incluir:**
- `app/` - Controladores, Modelos, Middlewares
- `resources/views/` - Vistas Blade
- `routes/` - Rutas web
- `config/` - Configuraciones
- `database/migrations/` - Migraciones
- `public/` - Assets públicos (CSS, JS, imágenes)
- `storage/app/` - Archivos de la aplicación
- `.env` - Configuración (necesitarás modificarlo)
- `composer.json` - Dependencias PHP
- `package.json` - Dependencias JS (si las usas)
- `artisan` - CLI de Laravel
- Archivos `.php` auxiliares (check_*.php, fix_*.php)

❌ **NO Incluir:**
- `node_modules/` - 🚫 Muy pesado, se reinstala
- `vendor/` - 🚫 Se reinstala con composer
- `.git/` - 🚫 Opcional (historial git)
- `storage/logs/*.log` - 🚫 Logs viejos
- `bootstrap/cache/*` - 🚫 Cache se regenera

---

## 🖥️ PASO 3: CONFIGURAR SERVIDOR DE PRUEBA

### Requisitos del Servidor:

```
✅ PHP 8.1 o superior
✅ PostgreSQL 12 o superior
✅ Extensiones PHP necesarias:
   - pdo_pgsql
   - mbstring
   - openssl
   - tokenizer
   - xml
   - ctype
   - json
   - fileinfo
   - gd (para QR codes)
✅ Composer
✅ Nginx o Apache
```

### A. Instalar en Ubuntu/Debian:

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP 8.2 y extensiones
sudo apt install -y php8.2 php8.2-fpm php8.2-pgsql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl

# Instalar PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Nginx
sudo apt install -y nginx
```

### B. Instalar en Windows (Servidor Local):

```
1. Instalar XAMPP (o similar) con PHP 8.2
2. Instalar PostgreSQL desde: https://www.postgresql.org/download/windows/
3. Instalar Composer desde: https://getcomposer.org/download/
4. Configurar PHP extensions en php.ini:
   extension=pdo_pgsql
   extension=pgsql
```

---

## 🔧 PASO 4: RESTAURAR EN SERVIDOR NUEVO

### A. Copiar Archivos del Proyecto

```bash
# En el servidor nuevo
cd /var/www/  # (Linux)
# o
cd C:\inetpub\wwwroot\  # (Windows IIS)
# o
cd C:\xampp\htdocs\  # (Windows XAMPP)

# Extraer el proyecto
unzip La Zarza Contigo_v1.0.zip -d La Zarza Contigo
cd La Zarza Contigo

# Dar permisos (Linux)
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### B. Instalar Dependencias

```bash
# Instalar dependencias de PHP
composer install --no-dev --optimize-autoloader

# Si usas npm/node (opcional)
npm install
npm run build
```

### C. Configurar Variables de Entorno

```bash
# Copiar el archivo .env de ejemplo
cp .env.example .env

# Editar .env con los datos del nuevo servidor
nano .env  # (Linux)
# o
notepad .env  # (Windows)
```

**Configuración del `.env`:**

```env
APP_NAME="La Zarza Contigo"
APP_ENV=local
APP_KEY=base64:TU_KEY_AQUI  # Se genera con: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000  # o la URL de tu servidor

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Base de Datos PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=appwebuser
DB_PASSWORD=appwebpass
DB_SCHEMA=appweb

# Sesiones
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### D. Generar Application Key

```bash
php artisan key:generate
```

---

## 🗄️ PASO 5: RESTAURAR BASE DE DATOS

### A. Crear Usuario y Esquema en PostgreSQL

```bash
# Conectar a PostgreSQL
sudo -u postgres psql

# O en Windows:
psql -U postgres
```

```sql
-- Crear usuario
CREATE USER appwebuser WITH PASSWORD 'appwebpass';

-- Crear esquema
CREATE SCHEMA appweb;

-- Dar permisos
GRANT ALL PRIVILEGES ON SCHEMA appweb TO appwebuser;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA appweb TO appwebuser;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA appweb TO appwebuser;

-- Permitir crear tablas
ALTER SCHEMA appweb OWNER TO appwebuser;

-- Salir
\q
```

### B. Restaurar el Respaldo

```bash
# Restaurar esquema y datos
psql -h localhost -U appwebuser -d postgres -f respaldo_completo_appweb.sql

# O por separado:
psql -h localhost -U appwebuser -d postgres -f schema_appweb.sql
psql -h localhost -U appwebuser -d postgres -f datos_appweb.sql
```

### C. Verificar Restauración

```bash
psql -h localhost -U appwebuser -d postgres

# Dentro de psql:
SET search_path TO appweb, public;
\dt  -- Ver tablas
SELECT COUNT(*) FROM usuarios;
SELECT COUNT(*) FROM cupones;
\q
```

---

## 🌐 PASO 6: CONFIGURAR SERVIDOR WEB

### Opción A: Nginx (Recomendado para Producción)

Crear archivo: `/etc/nginx/sites-available/La Zarza Contigo`

```nginx
server {
    listen 80;
    server_name localhost;  # o tu dominio
    root /var/www/La Zarza Contigo/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Activar sitio
sudo ln -s /etc/nginx/sites-available/La Zarza Contigo /etc/nginx/sites-enabled/

# Probar configuración
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

### Opción B: Apache (.htaccess ya incluido)

Crear archivo: `/etc/apache2/sites-available/La Zarza Contigo.conf`

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/La Zarza Contigo/public

    <Directory /var/www/La Zarza Contigo/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/La Zarza Contigo_error.log
    CustomLog ${APACHE_LOG_DIR}/La Zarza Contigo_access.log combined
</VirtualHost>
```

```bash
# Activar módulos necesarios
sudo a2enmod rewrite
sudo a2ensite La Zarza Contigo

# Reiniciar Apache
sudo systemctl restart apache2
```

### Opción C: PHP Built-in Server (Solo Desarrollo)

```bash
cd /var/www/La Zarza Contigo
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🧪 PASO 7: VERIFICAR INSTALACIÓN

### A. Tests Básicos

```bash
# Test de conexión a BD
php check_client_status.php

# Test del sistema de cupones
php test_coupon_codes.php

# Test de cupones deshabilitados
php test_cupones_deshabilitados.php
```

### B. Verificación Manual

1. **Abrir navegador:**
   ```
   http://localhost:8000
   # o
   http://TU_IP_SERVIDOR:8000
   ```

2. **Probar Login:**
   - Cliente: `cliente@test.com` / `password`
   - Admin: `admin@test.com` / `password`

3. **Verificar Funcionalidades:**
   - ✅ Dashboard carga correctamente
   - ✅ Login funciona
   - ✅ Cupones se muestran
   - ✅ Canje de cupones funciona
   - ✅ Popup de QR aparece
   - ✅ Admin puede validar cupones
   - ✅ Registro de tickets funciona

---

## 🔒 PASO 8: SEGURIDAD POST-MIGRACIÓN

### A. Cambiar Credenciales

```bash
# Ejecutar script para actualizar passwords
php fix_passwords.php
```

### B. Configurar Firewall (Linux)

```bash
# Permitir solo puertos necesarios
sudo ufw allow 22    # SSH
sudo ufw allow 80    # HTTP
sudo ufw allow 443   # HTTPS (si usas SSL)
sudo ufw enable
```

### C. Configurar PostgreSQL para Acceso Remoto (Opcional)

Editar: `/etc/postgresql/14/main/postgresql.conf`
```
listen_addresses = 'localhost'  # Solo local
# o
listen_addresses = '*'  # Todas las IPs (menos seguro)
```

Editar: `/etc/postgresql/14/main/pg_hba.conf`
```
# Solo conexiones locales
host    postgres    appwebuser    127.0.0.1/32    md5
```

---

## 📊 PASO 9: OPTIMIZACIÓN

### A. Cache de Laravel

```bash
# Optimizar carga
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar cache si hay cambios
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### B. Permisos Correctos (Linux)

```bash
# Storage y cache escribibles
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Resto del proyecto solo lectura
sudo chown -R www-data:www-data /var/www/La Zarza Contigo
sudo chmod -R 755 /var/www/La Zarza Contigo
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS COMUNES

### Problema 1: Error de Conexión a BD

```bash
# Verificar que PostgreSQL esté corriendo
sudo systemctl status postgresql

# Probar conexión manual
psql -h localhost -U appwebuser -d postgres

# Verificar configuración en .env
cat .env | grep DB_
```

### Problema 2: Error 500 Internal Server Error

```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Ver logs de Nginx
sudo tail -f /var/log/nginx/error.log

# Ver logs de PHP
sudo tail -f /var/log/php8.2-fpm.log
```

### Problema 3: Permisos Denegados

```bash
# Dar permisos a storage
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage

# Dar permisos a bootstrap/cache
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 bootstrap/cache
```

### Problema 4: QR Codes No Se Generan

```bash
# Verificar extensión GD
php -m | grep gd

# Instalar si falta
sudo apt install php8.2-gd
sudo systemctl restart php8.2-fpm
```

---

## 📝 CHECKLIST FINAL

Antes de dar por terminada la migración:

- [ ] Base de datos restaurada correctamente
- [ ] Archivos del proyecto copiados
- [ ] Dependencias instaladas (composer install)
- [ ] .env configurado correctamente
- [ ] Application key generada
- [ ] Servidor web configurado (Nginx/Apache)
- [ ] Permisos de archivos correctos
- [ ] Login funciona (cliente y admin)
- [ ] Cupones se muestran correctamente
- [ ] Popup de canje funciona
- [ ] QR codes se generan
- [ ] Validación de cupones funciona
- [ ] Registro de tickets funciona
- [ ] Logs no muestran errores

---

## 🎉 ¡MIGRACIÓN COMPLETADA!

Tu sistema La Zarza Contigo ahora está funcionando en el servidor de prueba.

**URLs de Acceso:**
- Frontend: `http://localhost:8000` (o tu IP/dominio)
- Login Cliente: `http://localhost:8000/login`
- Admin: `http://localhost:8000/admin/points`

**Credenciales de Prueba:**
- Cliente: `cliente@test.com` / `password`
- Admin: `admin@test.com` / `password`

---

## 📞 SOPORTE

Si encuentras algún problema:

1. Revisa los logs: `storage/logs/laravel.log`
2. Verifica la conexión a BD: `php check_client_status.php`
3. Revisa la configuración: `.env`
4. Verifica permisos: `ls -la storage/`

---

**Fecha:** 2025-10-15  
**Sistema:** La Zarza Contigo v2.1  
**Guía:** Migración a Servidor de Prueba
