# 🚀 DEPLOYMENT EN 3 PASOS - Hostinger VPS

## 📌 Información Rápida

**Servidor:** AlmaLinux 9 con **CyberPanel**  
**Servidor Web:** OpenLiteSpeed (incluido en CyberPanel)  
**Dominio:** contigo.lazarza.com.mx  
**Método:** Git deployment  
**Base de datos:** Externa/Remota  
**Tiempo estimado:** 60 minutos (primera vez) / 2 minutos (actualizaciones)

## ⚠️ IMPORTANTE: TIENES CYBERPANEL

Este servidor usa **CyberPanel con OpenLiteSpeed**. Para una guía completa adaptada específicamente a CyberPanel, consulta:

👉 **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** - Guía específica para CyberPanel

La guía a continuación es una versión simplificada. Si encuentras problemas, usa la guía de CyberPanel.

---

## ✅ PASO 1: Preparar Credenciales (10 min)

Antes de conectarte al servidor, ten a mano:

### 🔐 Credenciales SSH
```
Host: [IP del VPS de Hostinger]
Usuario: [tu-usuario-ssh]
Contraseña/Clave: [tu-password o ruta a clave privada]
```

### 🗄️ Credenciales Base de Datos Remota
```
DB_HOST=tu-servidor-mysql-remoto.com
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario_db
DB_PASSWORD=contraseña_segura
```

### 📧 Credenciales SMTP (Hostinger)
```
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@lazarza.com.mx
MAIL_PASSWORD=tu-password-email
```

### 🔑 API Keys Oppen
```
OPPEN_API_URL=https://api.oppen.com.mx/v1
OPPEN_API_KEY=tu-api-key
OPPEN_API_SECRET=tu-api-secret
```

---

## ✅ PASO 2: Ejecutar Deployment (45 min)

### 🖥️ Conectar al Servidor
```bash
ssh root@ip-del-vps
# O si tienes usuario no-root:
ssh usuario@ip-del-vps
```

### 📥 Crear Sitio en CyberPanel (PRIMERO)

**IMPORTANTE:** Con CyberPanel, primero debes crear el sitio desde el panel web:

1. Abrir CyberPanel: `https://ip-del-vps:8090`
2. **Main Menu** → **Website** → **Create Website**
3. Configurar:
   - Domain: `contigo.lazarza.com.mx`
   - PHP: 8.2+
   - SSL: Activar después
4. Click **Create Website**

### 📥 Clonar el Proyecto

```bash
# Ir al directorio del dominio (creado por CyberPanel)
cd /home/contigo.lazarza.com.mx

# Backup del directorio original
mv public_html public_html_backup

# Clonar el repositorio
git clone https://github.com/IngDatosZarza/appwebzarza.git public_html
cd public_html
```

### ⚙️ Configurar Variables de Entorno
```bash
cp .env.production.example .env
nano .env
```

**Actualizar estos valores en el archivo .env:**
- Credenciales de base de datos (DB_*)
- Credenciales de correo (MAIL_*)
- API Keys de Oppen (OPPEN_*)
- APP_URL=http://contigo.lazarza.com.mx
# CyberPanel usa el usuario 'nobody' para OpenLiteSpeed
cd /home/contigo.lazarza.com.mx/public_html
chown -R nobody:nobody storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 🌐 Configurar OpenLiteSpeed (CyberPanel)

1. **CyberPanel** → **Website** → **List Websites**
2. Click **Manage** junto a `contigo.lazarza.com.mx`
3. Click **vHost Conf**
4. Buscar la línea `docRoot` y cambiar a:
   ```apache
   docRoot   /home/contigo.lazarza.com.mx/public_html/public
   ```
   **IMPORTANTE:** Agregar `/public` al final
5. Buscar la sección `rewrite` y asegurar que esté así:
   ```apache
   rewrite  {
     enable                1
     autoLoadHtaccess      1
   }
   ```
6. Click **Save**
7. Reiniciar OpenLiteSpeed:
   ```bash
   systemctl restart lsws
   **Si usas Nginx:**
```bash
sudo nano /etc/nginx/conf.d/contigo.lazarza.com.mx.conf
```
Copiar el contenido del archivo `nginx.conf` del proyecto.

```bash
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

**Si usas Apache:**
```bash
sudo nano /etc/httpd/conf.d/contigo.lazarza.com.mx.conf
```
Copiar el contenido del archivo `apache.conf` del proyecto.

```bash
sudo apachectl configtest
sudo systemctl restart httpd
```

### 🛡️ Configurar Firewall (CyberPanel ya configura automáticamente)
```bash
# Verificar que los puertos estén abiertos
firewall-cmd --list-services
# Debe mostrar http, https y ssh

# Si no están, agregarlos:
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload
```

### ⏰ Configurar Cron desde CyberPanel

1. **CyberPanel** → **Cron** → **Create Cron Job**
2. Configurar:
   - Website: `contigo.lazarza.com.mx`
   - Minutes: `*`
   - Hours: `*`
   - Day: `*`
   - Month: `*`
   - Day of week: `*`
   - Command:
   ```bash
   cd /home/contigo.lazarza.com.mx/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```
3. Click **Create Cron Job**

### 📜 Configurar Script de Deployment (CyberPanel)
```bash
cp /home/contigo.lazarza.com.mx/public_html/deploy-cyberpanel.sh ~/deploy-appwebzarza.sh
chmod� Configurar SSL (1-Click en CyberPanel)

1. **CyberPanel** → **SSL** → **Manage SSL**
2. Seleccionar: `contigo.lazarza.com.mx`
3. Click **Issue SSL**
4. Esperar 1-2 minutos
5. ✅ Debe aparecer "SSL issued successfully"

### 🔍 Verificar que el Sitio Funciona
```bash
curl -I https://contigo.lazarza.com.mx
```
Debe devolver `HTTP/2 200` o `HTTP/2 302`

### 🌐 Abrir en Navegador
Abrir en tu navegador: `httpso Funciona
```bash
curlhome/contigo.lazarza.com.mx/public_html
php artisan tinker
```
En el prompt de tinker:
```php
DB::connection()->getPdo();
DB::table('usuarios')->count();
exit
```

### 📝 Ver Logs (si hay errores)
```bash
# Laravel logs
tail -50 /home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log

# OpenLiteSpeed logs
tail -50 /usr/local/lsws/logs/error.log

# Logs del dominio
tail -50 /home/contigo.lazarza.com.mx/logs/*
```bash
cd /var/www/appwebzarza
php artisan tinker
```
En el prompt de tinker:
```php
DB::connection()->getPdo();
DB::table('usuarios')->count();
exit
```

### 📝 Ver Logs (si hay errores)
```bash
tail -50 /var/www/appwebzarza/storage/logs/laravel.log
tail -50 /var/log/nginx/contigo.lazarza.com.mx-error.log
```

---

## 🔄 ACTUALIZACIONES FUTURAS (2 min)

Después de hacer cambios en el código y subirlos al repositorio Git:

```bash
ssh usuario@ip-del-vps
bash ~/deploy-appwebzarza.sh
```
# CyberPanel usa nobody:nobody
chown -R nobody:nobody /home/contigo.lazarza.com.mx/public_html
chmod -R 775 /home/contigo.lazarza.com.mx/public_html/storage
chmod -R 775 /home/contigo.lazarza.com.mx/public_html/bootstrap/cache

# Verificar que docRoot apunta a /public
# CyberPanel → Website → Manage → vHost Conf
1. ✅ Activa modo mantenimiento
2. ✅ Descarga cambios de Git
3. ✅ Actualiza dependencias
4. ✅ Ejecuta migraciones
5. ✅home/contigo.lazarza.com.mx/public_html
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reiniciar OpenLiteSpeed
systemctl restart lsws

# O liSSL no funciona
1. **CyberPanel** → **SSL** → **Manage SSL**
2. Seleccionar dominio
3. Click **Issue SSL**
4. Si falla, verificar DNS apunta correctamente

---

## 📚 DOCUMENTACIÓN COMPLETA

- **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** ⭐ **GUÍA ESPECÍFICA PARA CYBERPANEL**
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Guía para servidores tradicionales
- **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Checklist paso a paso
- **[server-commands.sh](server-commands.sh)** - Comandos para servidores tradicionales

---

## 🔐 SSL/HTTPS con CyberPanel (1-Click)

CyberPanel tiene SSL integrado con Let's Encrypt:

1. **CyberPanel** → **SSL** → **Manage SSL**
2. Seleccionar: `contigo.lazarza.com.mx`
3. Click **Issue SSL**
4. ✅ Listo en 1-2 minutos

El certificado se renueva
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

### ❌ Comandos útiles no funcionan
```bash
bash /var/www/appwebzarza/server-commands.sh
```
Este comando mostrará una lista de todos los comandos disponibles.

---

## 📚 DOCUMENTACIÓN COMPLETA

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Guía detallada con todas las opciones
- **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Checklist paso a paso
- **[server-commands.sh](server-commands.sh)** - Referencia de comandos útiles

---

## 🔐 SSL/HTTPS (Opcional - Recomendado)

Después de verificar que todo funciona en HTTP, instalar certificado SSL:

```bash
sudo dnf install -y certbot python3-certbot-nginx
sudo certbot --nginx -d contigo.lazarza.com.mx
```

El certificado se renovará automáticamente.

---

## ✅ CHECKLIST FINAL

- [ ] Sitio carga en http://contigo.lazarza.com.mx
- [ ] Registro de usuarios funciona
- [ ] Login funciona  
- [ ] Base de datos conecta correctamente
- [ ] Correos se envían (verificar con registro)
- [ ] Logs no muestran errores críticos
- [ ] Script de deployment configurado
- [ ] Cron job configurado para tareas programadas
- [ ] (Opcional) SSL/HTTPS configurado

---

## 📞 SOPORTE

Si encuentras problemas:

1. Consulta [DEPLOYMENT.md](DEPLOYMENT.md) para soluciones detalladas
2. Revisa los logs: `tail -f storage/logs/laravel.log`
3. Ejecuta `bash server-commands.sh` para ver comandos de troubleshooting

---

**¡Listo! Tu aplicación Laravel está en producción. 🎉**
