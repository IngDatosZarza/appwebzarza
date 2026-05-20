# Guía de Deployment - Laravel en Hostinger VPS (AlmaLinux 9)

## Pre-requisitos en el Servidor

### 1. Verificar instalación de componentes necesarios
```bash
# Conectar al servidor
ssh usuario@tu-ip-del-vps

# Verificar versiones instaladas
php -v
composer -v
git --version
nginx -v  # o httpd -v para Apache
mysql --version
```

### 2. Instalar componentes faltantes (si es necesario)

#### PHP 8.2+ y extensiones requeridas
```bash
sudo dnf install -y php php-cli php-fpm php-mysql php-mbstring php-xml php-bcmath php-curl php-zip php-gd php-intl php-soap php-pdo
```

#### Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

#### Git (si no está instalado)
```bash
sudo dnf install -y git
```

---

## Configuración del Proyecto

### 1. Clonar el repositorio
```bash
# Navegar al directorio de aplicaciones
cd /var/www  # o el directorio que uses para tus aplicaciones

# Clonar el repositorio
git clone https://github.com/IngDatosZarza/appwebzarza.git
cd appwebzarza

# Configurar permisos
sudo chown -R $USER:nginx /var/www/appwebzarza
# O si usas Apache:
# sudo chown -R $USER:apache /var/www/appwebzarza
```

### 2. Instalar dependencias
```bash
# Instalar dependencias de PHP
composer install --optimize-autoloader --no-dev

# Instalar dependencias de Node.js (si es necesario)
npm install
npm run build
```

### 3. Configurar variables de entorno
```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar configuración de producción
nano .env
```

**Configuración .env para producción:**
```env
APP_NAME="Sistema de Puntos Zarza"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=America/Mexico_City
APP_URL=http://contigo.lazarza.com.mx

# Base de datos remota
DB_CONNECTION=mysql
DB_HOST=tu-servidor-remoto-mysql
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario_db
DB_PASSWORD=contraseña_segura

# Configuración de correo
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=tu-email@lazarza.com.mx
MAIL_PASSWORD=tu-password-email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@lazarza.com.mx
MAIL_FROM_NAME="${APP_NAME}"

# Sesiones y cache
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=database

# Oppen API (tu servicio externo)
OPPEN_API_URL=tu-url-api
OPPEN_API_KEY=tu-api-key
```

### 4. Generar clave de aplicación y optimizar
```bash
# Generar APP_KEY
php artisan key:generate

# Ejecutar migraciones (contra base de datos remota)
php artisan migrate --force

# Ejecutar seeders si es necesario
# php artisan db:seed --force

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 5. Configurar permisos correctos
```bash
# Dar permisos a directorios de escritura
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:nginx storage bootstrap/cache
# O para Apache: sudo chown -R $USER:apache storage bootstrap/cache

# Asegurar que el grupo tiene permisos de escritura
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

---

## Configuración del Servidor Web

### Opción A: Nginx (Recomendado)

Crear archivo de configuración del sitio:
```bash
sudo nano /etc/nginx/conf.d/contigo.lazarza.com.mx.conf
```

**Contenido del archivo:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name contigo.lazarza.com.mx;
    root /var/www/appwebzarza/public;

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
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Proteger archivos sensibles
    location ~ /\.env {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }
}
```

**Activar y reiniciar Nginx:**
```bash
# Verificar configuración
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx

# Verificar PHP-FPM
sudo systemctl start php-fpm
sudo systemctl enable php-fpm
```

### Opción B: Apache

Crear archivo de configuración del sitio:
```bash
sudo nano /etc/httpd/conf.d/contigo.lazarza.com.mx.conf
```

**Contenido del archivo:**
```apache
<VirtualHost *:80>
    ServerName contigo.lazarza.com.mx
    ServerAdmin admin@lazarza.com.mx
    DocumentRoot /var/www/appwebzarza/public

    <Directory /var/www/appwebzarza/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Proteger archivos sensibles
    <Directory /var/www/appwebzarza>
        <FilesMatch "^\.env">
            Require all denied
        </FilesMatch>
        <DirectoryMatch "^\.git">
            Require all denied
        </DirectoryMatch>
    </Directory>

    ErrorLog /var/log/httpd/contigo.lazarza.com.mx-error.log
    CustomLog /var/log/httpd/contigo.lazarza.com.mx-access.log combined
</VirtualHost>
```

**Activar y reiniciar Apache:**
```bash
# Verificar configuración
sudo apachectl configtest

# Reiniciar Apache
sudo systemctl restart httpd
sudo systemctl enable httpd
```

---

## Configuración de Firewall (SELinux y Firewalld)

```bash
# Configurar SELinux para permitir que Nginx/Apache acceda a archivos
sudo setsebool -P httpd_can_network_connect 1
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/appwebzarza/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/appwebzarza/bootstrap/cache(/.*)?"
sudo restorecon -Rv /var/www/appwebzarza

# Configurar firewall
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

---

## Script de Deployment Automatizado

Crea el script `deploy.sh` en el servidor:

```bash
nano ~/deploy-appwebzarza.sh
```

Contenido en el archivo `deploy.sh` del repositorio.

---

## SSL/HTTPS con Let's Encrypt (Opcional pero Recomendado)

```bash
# Instalar Certbot
sudo dnf install -y certbot python3-certbot-nginx  # Para Nginx
# O para Apache: sudo dnf install -y certbot python3-certbot-apache

# Obtener certificado SSL
sudo certbot --nginx -d contigo.lazarza.com.mx  # Para Nginx
# O para Apache: sudo certbot --apache -d contigo.lazarza.com.mx

# Renovación automática
sudo systemctl enable certbot-renew.timer
sudo systemctl start certbot-renew.timer
```

---

## Configuración de Cron Jobs

Para ejecutar tareas programadas de Laravel:

```bash
# Editar crontab
crontab -e

# Agregar esta línea:
* * * * * cd /var/www/appwebzarza && php artisan schedule:run >> /dev/null 2>&1
```

---

## Verificación Final

1. **Verificar que el sitio funciona:**
   ```bash
   curl -I http://contigo.lazarza.com.mx
   ```

2. **Verificar logs:**
   ```bash
   # Laravel logs
   tail -f /var/www/appwebzarza/storage/logs/laravel.log
   
   # Nginx logs
   tail -f /var/log/nginx/error.log
   
   # Apache logs
   tail -f /var/log/httpd/contigo.lazarza.com.mx-error.log
   ```

3. **Verificar conexión a base de datos:**
   ```bash
   php artisan tinker
   # Ejecutar: DB::connection()->getPdo();
   ```

---

## Actualizaciones Futuras

Para actualizar la aplicación después de hacer cambios:

```bash
cd /var/www/appwebzarza
bash ~/deploy-appwebzarza.sh
```

---

## Troubleshooting

### Error 500 - Internal Server Error
```bash
# Verificar permisos
ls -la storage/
ls -la bootstrap/cache/

# Verificar logs
tail -50 storage/logs/laravel.log
```

### Error de permisos
```bash
sudo chown -R $USER:nginx /var/www/appwebzarza
sudo chmod -R 775 storage bootstrap/cache
```

### Cambios no se reflejan
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Base de datos no conecta
- Verificar que el servidor remoto permite conexiones desde la IP del VPS
- Verificar credenciales en `.env`
- Verificar firewall del servidor de base de datos

---

## Contacto y Soporte

Para problemas específicos, revisar:
- Logs de Laravel: `/var/www/appwebzarza/storage/logs/laravel.log`
- Logs del servidor web
- Estado de servicios: `sudo systemctl status nginx` o `sudo systemctl status httpd`
