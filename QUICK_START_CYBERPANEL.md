# 🎯 RESUMEN RÁPIDO - CyberPanel

## Para Hostinger VPS con CyberPanel + AlmaLinux 9

### ✅ VENTAJAS
- ✅ Ya tienes todo instalado (OpenLiteSpeed, PHP, MySQL, Git, Composer, Node.js)
- ✅ Interface web para administrar todo
- ✅ SSL con 1-click (Let's Encrypt integrado)
- ✅ OpenLiteSpeed = Servidor web ultrarrápido
- ✅ HTTP/3 soportado nativamente

---

## 🚀 3 PASOS PRINCIPALES

### 1️⃣ CREAR SITIO EN CYBERPANEL (10 min)
```
1. Abrir: https://ip-vps:8090
2. Website → Create Website
3. Domain: contigo.lazarza.com.mx
4. PHP: 8.2+
5. Crear
```

### 2️⃣ CLONAR Y CONFIGURAR (30 min)
```bash
ssh root@ip-vps

# Limpiar y clonar
cd /home/contigo.lazarza.com.mx
mv public_html public_html_backup
git clone https://github.com/IngDatosZarza/appwebzarza.git public_html
cd public_html

# Instalar dependencias
composer install --no-dev
npm install && npm run build

# Configurar .env
cp .env.production.example .env
nano .env  # Actualizar credenciales

# Setup Laravel
php artisan key:generate
php artisan migrate --force
php artisan config:cache

# Permisos
chown -R nobody:nobody storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 3️⃣ CONFIGURAR OPENLITESPEED (10 min)
```
1. CyberPanel → Website → List Websites
2. Manage → vHost Conf
3. Cambiar docRoot a:
   docRoot /home/contigo.lazarza.com.mx/public_html/public
4. Guardar
5. Terminal: systemctl restart lsws
```

---

## 📋 CHECKLIST ULTRA RÁPIDO

- [ ] Sitio creado en CyberPanel
- [ ] Proyecto clonado en /home/dominio/public_html/
- [ ] Dependencias instaladas
- [ ] .env configurado con credenciales
- [ ] key:generate + migrate + cache
- [ ] Permisos: nobody:nobody
- [ ] docRoot apunta a /public
- [ ] OpenLiteSpeed reiniciado
- [ ] SSL instalado (opcional): SSL → Manage SSL → Issue SSL
- [ ] Sitio funciona en navegador ✅

---

## 🔄 ACTUALIZACIONES (2 min)

```bash
# Copiar script una vez
cp /home/contigo.lazarza.com.mx/public_html/deploy-cyberpanel.sh ~/deploy-appwebzarza.sh
chmod +x ~/deploy-appwebzarza.sh

# Para actualizar:
ssh root@ip-vps
bash ~/deploy-appwebzarza.sh
```

---

## 🆘 TROUBLESHOOTING

**Error 500:**
```bash
tail -50 /home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log
```

**Error 403:**
```bash
chown -R nobody:nobody /home/contigo.lazarza.com.mx/public_html
chmod -R 775 storage bootstrap/cache
```

**Sitio no carga:**
- Verificar docRoot tiene `/public` al final
- Reiniciar: `systemctl restart lsws`

**SSL 1-click:**
```
CyberPanel → SSL → Manage SSL → Issue SSL
```

---

## 📚 DOCUMENTACIÓN COMPLETA

👉 **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)**

---

## 🔗 ACCESOS RÁPIDOS

- **CyberPanel:** https://ip-vps:8090
- **Sitio:** https://contigo.lazarza.com.mx
- **Logs Laravel:** `/home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log`
- **Logs OpenLiteSpeed:** `/usr/local/lsws/logs/error.log`

---

**Tiempo total:** ~50-60 minutos primera vez | 2 minutos actualizaciones 🚀
