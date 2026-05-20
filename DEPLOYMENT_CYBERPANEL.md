# 🚀 Deployment Laravel en CyberPanel (AlmaLinux 9)

## 📌 Información del Servidor

**Panel:** CyberPanel  
**Servidor Web:** OpenLiteSpeed  
**OS:** AlmaLinux 9  
**Dominio:** contigo.lazarza.com.mx  
**Método:** Git deployment  
**Base de datos:** Externa/Remota  

---

## ✅ VENTAJAS DE CYBERPANEL

CyberPanel ya incluye:
- ✅ OpenLiteSpeed (servidor web ultrarrápido)
- ✅ PHP 8.x con extensiones
- ✅ MySQL/MariaDB
- ✅ Git
- ✅ Composer
- ✅ Node.js & NPM
- ✅ SSL automático con Let's Encrypt (1-click)
- ✅ Gestión de dominios desde el panel

---

## 📋 PASO 1: Configurar Sitio en CyberPanel (15 min)

### 1.1 Acceder a CyberPanel
```
URL: https://tu-ip-vps:8090
Usuario: admin
Contraseña: [tu contraseña de CyberPanel]
```

### 1.2 Crear el Sitio Web

1. **Main Menu** → **Website** → **Create Website**
2. Llenar el formulario:
   - **Select Package:** Default
   - **Select Owner:** admin (o el que prefieras)
   - **Domain Name:** `contigo.lazarza.com.mx`
   - **Email:** `admin@lazarza.com.mx`
   - **Select PHP:** PHP 8.2 (o superior)
   - **dkimCheck:** ☑ (opcional)
   - **Open_Basedir:** ☑ Activar
   - **SSL:** Activar después de configurar el DNS
3. Click **Create Website**

### 1.3 Configurar DNS

Si el dominio está en tu proveedor DNS:
1. Agregar registro A:
   ```
   contigo.lazarza.com.mx → IP-del-VPS
   ```
2. Esperar propagación (5-30 minutos)

Si usas Cloudflare u otro proxy, temporalmente desactiva el proxy (nube gris) para configurar SSL.

### 1.4 Instalar SSL (Opcional pero recomendado)

1. **Main Menu** → **SSL** → **Manage SSL**
2. Seleccionar: `contigo.lazarza.com.mx`
3. Click **Issue SSL**
4. Esperar 1-2 minutos
5. ✅ Verificar que aparece "SSL issued successfully"

---

## 📋 PASO 2: Conectar por SSH y Clonar Proyecto (20 min)

### 2.1 Conectar al Servidor
```bash
ssh root@ip-del-vps
# O si tienes usuario no-root:
ssh usuario@ip-del-vps
```

### 2.2 Ubicar Directorio del Sitio

En CyberPanel, los sitios se crean en:
```bash
/home/contigo.lazarza.com.mx/public_html/
```

### 2.3 Limpiar Directorio y Clonar Proyecto

```bash
# Ir al directorio del dominio
cd /home/contigo.lazarza.com.mx

# Hacer backup del public_html original (opcional)
mv public_html public_html_backup

# Clonar el repositorio
git clone https://github.com/IngDatosZarza/appwebzarza.git public_html

# Entrar al directorio
cd public_html
```

**IMPORTANTE:** Laravel necesita que el `public` sea la raíz web, lo configuraremos en el siguiente paso.

---

## 📋 PASO 3: Configurar Laravel (20 min)

### 3.1 Instalar Dependencias

```bash
cd /home/contigo.lazarza.com.mx/public_html

# Instalar dependencias PHP
composer install --optimize-autoloader --no-dev

# Instalar dependencias Node.js
npm install
npm run build
```

### 3.2 Configurar Variables de Entorno

```bash
# Copiar template de producción
cp .env.production.example .env

# Editar con nano
nano .env
```

**Actualizar estas variables:**
```env
APP_URL=https://contigo.lazarza.com.mx

DB_HOST=tu-servidor-mysql-remoto.com
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario_db
DB_PASSWORD=contraseña_segura

MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@lazarza.com.mx
MAIL_PASSWORD=tu-password-email
MAIL_ENCRYPTION=ssl

OPPEN_API_URL=tu-url-api
OPPEN_API_KEY=tu-api-key
OPPEN_API_SECRET=tu-api-secret
```

Guardar: `Ctrl+X`, `Y`, `Enter`

### 3.3 Ejecutar Comandos de Laravel

```bash
# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate --force

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3.4 Configurar Permisos

```bash
cd /home/contigo.lazarza.com.mx/public_html

# CyberPanel usa el usuario 'nobody' para OpenLiteSpeed
chown -R nobody:nobody storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Asegurar permisos correctos
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
```

---

## 📋 PASO 4: Configurar OpenLiteSpeed para Laravel (10 min)

### Opción A: Desde CyberPanel (Recomendado)

1. **Main Menu** → **Website** → **List Websites**
2. Click en **Manage** junto a `contigo.lazarza.com.mx`
3. Click en **vHost Conf**
4. Buscar la sección `docRoot` y modificar:

```apache
docRoot                   /home/contigo.lazarza.com.mx/public_html/public
```

**IMPORTANTE:** Agregar `/public` al final para que apunte a la carpeta pública de Laravel.

5. Buscar o agregar estas directivas de rewrite:

```apache
rewrite  {
  enable                1
  autoLoadHtaccess      1
  rules                 <<<END_rules
  # Laravel Rewrite Rules
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^ index.php [L]
  END_rules
}
```

6. Click **Save**
7. Reiniciar OpenLiteSpeed:
```bash
systemctl restart lsws
```

### Opción B: Editar Manualmente (Avanzado)

```bash
# Editar configuración del vHost
nano /usr/local/lsws/conf/vhosts/contigo.lazarza.com.mx/vhost.conf
```

Modificar `docRoot` para agregar `/public`:
```apache
docRoot                   $VH_ROOT/public_html/public
```

Reiniciar:
```bash
systemctl restart lsws
```

---

## 📋 PASO 5: Configurar Cron Jobs desde CyberPanel (5 min)

1. **Main Menu** → **Cron** → **Create Cron Job**
2. Llenar:
   - **Select Website:** contigo.lazarza.com.mx
   - **Minutes:** *
   - **Hours:** *
   - **Day (month):** *
   - **Month:** *
   - **Day (week):** *
   - **Command to run:**
   ```bash
   cd /home/contigo.lazarza.com.mx/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```
3. Click **Create Cron Job**

---

## 📋 PASO 6: Script de Deployment Automatizado (5 min)

### 6.1 Crear Script Personalizado para CyberPanel

```bash
nano ~/deploy-appwebzarza.sh
```

**Contenido del script:**

```bash
#!/bin/bash

# Script de Deployment para CyberPanel
APP_DIR="/home/contigo.lazarza.com.mx/public_html"
WEB_USER="nobody"
LOG_FILE="$APP_DIR/storage/logs/deployment.log"

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

cd "$APP_DIR" || exit 1

log "==================================================="
log "Deployment en CyberPanel - appwebzarza"
log "==================================================="

# Modo mantenimiento
log "Activando modo mantenimiento..."
php artisan down

# Git pull
log "Obteniendo cambios de Git..."
git pull origin master

# Dependencias
log "Instalando dependencias..."
composer install --optimize-autoloader --no-dev --no-interaction
npm install
npm run build

# Migraciones
log "Ejecutando migraciones..."
php artisan migrate --force

# Limpiar y reconstruir caché
log "Optimizando..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Permisos
log "Ajustando permisos..."
chown -R nobody:nobody "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# Reiniciar OpenLiteSpeed
log "Reiniciando OpenLiteSpeed..."
systemctl reload lsws

# Desactivar mantenimiento
log "Desactivando modo mantenimiento..."
php artisan up

log "==================================================="
log "Deployment completado"
log "==================================================="

exit 0
```

### 6.2 Dar Permisos de Ejecución

```bash
chmod +x ~/deploy-appwebzarza.sh
```

---

## ✅ PASO 7: Verificación (5 min)

### 7.1 Probar el Sitio

```bash
# Probar con curl
curl -I https://contigo.lazarza.com.mx
```

Debe devolver `HTTP/2 200` o `HTTP/2 302`

### 7.2 Abrir en Navegador

Abrir: `https://contigo.lazarza.com.mx`

**Verificar:**
- ✅ Sitio carga correctamente
- ✅ SSL funciona (candado verde)
- ✅ Registro funciona
- ✅ Login funciona

### 7.3 Verificar Base de Datos

```bash
cd /home/contigo.lazarza.com.mx/public_html
php artisan tinker
```

En tinker:
```php
DB::connection()->getPdo();
DB::table('usuarios')->count();
exit
```

### 7.4 Ver Logs

```bash
# Logs de Laravel
tail -50 /home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log

# Logs de OpenLiteSpeed
tail -50 /usr/local/lsws/logs/error.log
```

---

## 🔄 ACTUALIZACIONES FUTURAS (2 min)

```bash
ssh root@ip-del-vps
bash ~/deploy-appwebzarza.sh
```

---

## 🛠️ COMANDOS ÚTILES PARA CYBERPANEL

### Reiniciar OpenLiteSpeed
```bash
systemctl restart lsws    # Reinicio completo
systemctl reload lsws     # Recargar configuración
systemctl status lsws     # Ver estado
```

### Ver Logs
```bash
# Laravel
tail -f /home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log

# OpenLiteSpeed Error Log
tail -f /usr/local/lsws/logs/error.log

# OpenLiteSpeed Access Log
tail -f /home/contigo.lazarza.com.mx/logs/contigo.lazarza.com.mx.access_log
```

### Gestión de PHP
```bash
# Ver versión de PHP
php -v

# Cambiar versión de PHP desde CyberPanel:
# Main Menu → Website → List PHP → Cambiar versión
```

### Permisos
```bash
# Usuario de OpenLiteSpeed en CyberPanel
chown -R nobody:nobody /home/contigo.lazarza.com.mx/public_html
chmod -R 775 /home/contigo.lazarza.com.mx/public_html/storage
```

---

## 🆘 TROUBLESHOOTING ESPECÍFICO DE CYBERPANEL

### Error 500

```bash
# Verificar logs de Laravel
tail -50 /home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log

# Verificar logs de OpenLiteSpeed
tail -50 /usr/local/lsws/logs/error.log

# Verificar permisos
ls -la /home/contigo.lazarza.com.mx/public_html/storage
```

### Error 403 - Forbidden

```bash
# Arreglar permisos
cd /home/contigo.lazarza.com.mx/public_html
chown -R nobody:nobody storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Verificar que docRoot apunta a /public
# CyberPanel → Website → Manage → vHost Conf
```

### Página en blanco

```bash
# Verificar que docRoot tiene /public al final
# Debe ser: /home/contigo.lazarza.com.mx/public_html/public

# Reiniciar OpenLiteSpeed
systemctl restart lsws
```

### SSL no funciona

1. Ir a CyberPanel: **SSL** → **Manage SSL**
2. Seleccionar dominio
3. Click **Issue SSL**
4. Si falla, verificar que el DNS apunta correctamente

### Cambios no se reflejan

```bash
# Limpiar caché de Laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Limpiar caché de OpenLiteSpeed desde CyberPanel
# Main Menu → Dashboard → Flush All
```

---

## 📊 OPTIMIZACIONES ADICIONALES

### 1. Caché de OPcache

CyberPanel ya tiene OPcache activado, pero puedes verificar:

```bash
php -i | grep opcache
```

### 2. Compresión Brotli/Gzip

OpenLiteSpeed automáticamente comprime respuestas. Verificar en:
- CyberPanel → Website → Manage → vHost Conf
- Buscar `enableBrCompress` (debe estar en 1)

### 3. HTTP/3

OpenLiteSpeed soporta HTTP/3 por defecto. Verificar en:
```bash
# Debe mostrar QUIC/HTTP3
curl -I https://contigo.lazarza.com.mx --http3
```

### 4. Cache del Navegador

Agregar en `.htaccess` del directorio `public`:

```apache
<IfModule mod_headers.c>
    <FilesMatch "\.(jpg|jpeg|png|gif|svg|css|js|woff|woff2|ttf|eot)$">
        Header set Cache-Control "max-age=31536000, public"
    </FilesMatch>
</IfModule>
```

---

## 📋 CHECKLIST FINAL

- [ ] Sitio creado en CyberPanel
- [ ] DNS configurado y propagado
- [ ] SSL instalado y funcionando
- [ ] Proyecto clonado en `/home/dominio/public_html/`
- [ ] Dependencias instaladas (Composer + NPM)
- [ ] .env configurado
- [ ] Migraciones ejecutadas
- [ ] Permisos configurados (nobody:nobody)
- [ ] docRoot apunta a `/public`
- [ ] Rewrite rules configurados
- [ ] Cron job configurado
- [ ] Script de deployment funcionando
- [ ] Sitio accesible y sin errores

---

## 🎯 VENTAJAS DE USAR CYBERPANEL

✅ **Velocidad:** OpenLiteSpeed es más rápido que Nginx/Apache  
✅ **Fácil:** Interface gráfica para todo  
✅ **SSL 1-click:** Let's Encrypt integrado  
✅ **HTTP/3:** Soporte nativo  
✅ **Bajo consumo:** Menos recursos que Apache  
✅ **Sin complicaciones:** Ya viene todo configurado  

---

**¡Tu aplicación Laravel está lista en CyberPanel! 🎉**
