# 🚀 MIGRACIÓN RÁPIDA - La Zarza Contigo

## 📝 RESUMEN EJECUTIVO

Esta es la guía rápida para migrar La Zarza Contigo a un servidor de prueba local.

---

## ⚡ PASOS RÁPIDOS (5 minutos)

### 1️⃣ EN EL SERVIDOR ACTUAL (Windows/XAMPP)

```powershell
# Crear respaldo de BD
php crear_respaldo_bd.php

# Verificar sistema
php verificar_migracion.php

# Comprimir proyecto (sin node_modules, vendor)
# Usar 7zip o WinRAR para comprimir la carpeta
# Excluir: node_modules/, vendor/, .git/, storage/logs/
```

### 2️⃣ EN EL SERVIDOR NUEVO (Linux)

```bash
# Instalar dependencias
sudo apt update
sudo apt install -y php8.2 php8.2-pgsql php8.2-mbstring php8.2-xml \
    php8.2-gd postgresql nginx composer

# Extraer proyecto
cd /var/www/
sudo unzip La Zarza Contigo.zip -d La Zarza Contigo
cd La Zarza Contigo

# Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# Configurar .env
cp .env.example .env
nano .env  # Editar DB_HOST, DB_USERNAME, DB_PASSWORD

# Generar key
php artisan key:generate

# Permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3️⃣ RESTAURAR BASE DE DATOS

```bash
# Conectar a PostgreSQL
sudo -u postgres psql

# Crear usuario y esquema
CREATE USER appwebuser WITH PASSWORD 'appwebpass';
CREATE SCHEMA appweb;
GRANT ALL PRIVILEGES ON SCHEMA appweb TO appwebuser;
ALTER SCHEMA appweb OWNER TO appwebuser;
\q

# Restaurar respaldo
psql -h localhost -U appwebuser -d postgres -f La Zarza Contigo_backup.sql

# Verificar
psql -h localhost -U appwebuser -d postgres
SET search_path TO appweb, public;
\dt
SELECT COUNT(*) FROM usuarios;
\q
```

### 4️⃣ PROBAR

```bash
# Ejecutar verificación
php verificar_migracion.php

# Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000

# Abrir navegador: http://localhost:8000
# Login: cliente@test.com / password
```

---

## 📂 ARCHIVOS IMPORTANTES

| Archivo | Descripción |
|---------|-------------|
| `GUIA_MIGRACION.md` | Guía completa paso a paso |
| `CHECKLIST_MIGRACION.md` | Lista de verificación |
| `crear_respaldo_bd.php` | Script para respaldar BD |
| `verificar_migracion.php` | Script de verificación |
| `migracion_menu.ps1` | Menú interactivo (Windows) |
| `migracion_menu.sh` | Menú interactivo (Linux) |

---

## 🎯 COMANDOS ÚTILES

### Windows (PowerShell)
```powershell
# Ejecutar menú interactivo
.\migracion_menu.ps1

# O manualmente:
php crear_respaldo_bd.php
php verificar_migracion.php
php artisan serve
```

### Linux (Bash)
```bash
# Ejecutar menú interactivo
chmod +x migracion_menu.sh
./migracion_menu.sh

# O manualmente:
php crear_respaldo_bd.php
php verificar_migracion.php
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## ✅ VERIFICACIÓN RÁPIDA

Después de migrar, verifica que:

- [ ] Login funciona (cliente@test.com)
- [ ] Cupones se muestran
- [ ] Botón "Canjear Cupón" funciona
- [ ] Popup con QR aparece
- [ ] Admin puede validar cupones

---

## 🔧 CONFIGURACIÓN NGINX (Producción)

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/La Zarza Contigo/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/La Zarza Contigo /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🆘 PROBLEMAS COMUNES

### Error: "Connection refused"
```bash
# Verificar PostgreSQL
sudo systemctl status postgresql
sudo systemctl start postgresql
```

### Error: "Permission denied"
```bash
# Dar permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Error: "500 Internal Server Error"
```bash
# Ver logs
tail -f storage/logs/laravel.log
```

### QR codes no se generan
```bash
# Instalar GD
sudo apt install php8.2-gd
sudo systemctl restart php8.2-fpm
```

---

## 📞 SOPORTE

**Archivos de respaldo:** `/respaldos/`  
**Logs del sistema:** `storage/logs/laravel.log`  
**Configuración:** `.env`

---

## 🎉 ¡LISTO!

Tu sistema La Zarza Contigo está migrado y funcionando.

**URLs:**
- Frontend: http://localhost:8000
- Admin: http://localhost:8000/admin/points

**Usuarios:**
- Cliente: cliente@test.com / password
- Admin: admin@test.com / password

---

**Última actualización:** 2025-10-15  
**Versión:** La Zarza Contigo v2.1
