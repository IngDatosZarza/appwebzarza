# Checklist de Deployment - Hostinger VPS

## ✅ Antes de Conectarte al Servidor

- [ ] Tener credenciales SSH listas (IP, usuario, contraseña/clave)
- [ ] Tener credenciales de base de datos remota
- [ ] Tener credenciales de correo SMTP (Hostinger)
- [ ] Tener API Keys de Oppen
- [ ] Repositorio Git accesible desde el servidor

## ✅ Primera Vez en el Servidor

### 1. Verificar Componentes (5 min)
```bash
ssh usuario@ip-del-servidor
php -v          # Debe ser 8.2+
composer -v     # Debe estar instalado
git --version   # Debe estar instalado
nginx -v        # O httpd -v si es Apache
```

### 2. Clonar Proyecto (5 min)
```bash
cd /var/www
git clone https://github.com/IngDatosZarza/appwebzarza.git
cd appwebzarza
```

### 3. Configurar .env (10 min)
```bash
cp .env.production.example .env
nano .env
```
**Actualizar:**
- DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- MAIL_USERNAME, MAIL_PASSWORD
- OPPEN_API_KEY, OPPEN_API_SECRET
- APP_URL=http://contigo.lazarza.com.mx

### 4. Instalar Dependencias (15 min)
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 5. Setup de Laravel (5 min)
```bash
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Permisos (2 min)
```bash
sudo chown -R $USER:nginx /var/www/appwebzarza
sudo chmod -R 775 storage bootstrap/cache
```

### 7. Configurar Nginx/Apache (10 min)

**Para Nginx:**
```bash
sudo nano /etc/nginx/conf.d/contigo.lazarza.com.mx.conf
# Copiar contenido del archivo nginx.conf incluido
sudo nginx -t
sudo systemctl restart nginx
```

**Para Apache:**
```bash
sudo nano /etc/httpd/conf.d/contigo.lazarza.com.mx.conf
# Copiar contenido del archivo apache.conf incluido
sudo apachectl configtest
sudo systemctl restart httpd
```

### 8. SELinux y Firewall (5 min)
```bash
sudo setsebool -P httpd_can_network_connect 1
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/appwebzarza/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/appwebzarza/bootstrap/cache(/.*)?"
sudo restorecon -Rv /var/www/appwebzarza

sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 9. Configurar Script de Deployment (2 min)
```bash
cp deploy.sh ~/deploy-appwebzarza.sh
chmod +x ~/deploy-appwebzarza.sh
```

### 10. Configurar Cron (2 min)
```bash
crontab -e
# Agregar: * * * * * cd /var/www/appwebzarza && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Probar la Aplicación (5 min)
```bash
curl -I http://contigo.lazarza.com.mx
# Debe devolver HTTP 200 o 302

# Abrir en navegador y verificar:
# - Página de inicio carga
# - Registro funciona
# - Login funciona
# - Base de datos conecta
```

---

## ✅ Para Actualizaciones Futuras

**Deployment rápido (1-2 min):**
```bash
ssh usuario@ip-del-servidor
bash ~/deploy-appwebzarza.sh
```

---

## ⚠️ Troubleshooting Rápido

### Error 500
```bash
tail -50 /var/www/appwebzarza/storage/logs/laravel.log
```

### Error 403/Permisos
```bash
sudo chown -R $USER:nginx /var/www/appwebzarza
sudo chmod -R 775 storage bootstrap/cache
```

### Cambios no aparecen
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Base de datos no conecta
- Verificar IP del VPS está permitida en servidor de BD
- Verificar credenciales en .env
- Probar conexión: `php artisan tinker` → `DB::connection()->getPdo();`

---

## 📊 Tiempo Estimado Total

- **Primera instalación:** ~60 minutos
- **Actualizaciones posteriores:** ~2 minutos

---

## 📞 Comandos Útiles

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver estado de servicios
sudo systemctl status nginx
sudo systemctl status php-fpm

# Reiniciar servicios
sudo systemctl restart nginx
sudo systemctl restart php-fpm

# Ver procesos PHP
ps aux | grep php

# Ver conexiones a base de datos
php artisan tinker
>>> DB::connection()->getPdo();
```
