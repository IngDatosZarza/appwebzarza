# 📚 ÍNDICE DE DOCUMENTACIÓN - DEPLOYMENT

## ⚠️ IMPORTANTE: TIENES CYBERPANEL

Tu servidor usa **CyberPanel con OpenLiteSpeed**. Usa la guía específica:

👉 **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** - Guía completa para CyberPanel

## 🎯 ¿Por dónde empezar?

### Si es tu PRIMERA VEZ desplegando con CyberPanel:
1. 📤 **[GIT_PREPARE.md](GIT_PREPARE.md)** - Subir archivos al repositorio Git
2. 🎯 **[QUICK_START_CYBERPANEL.md](QUICK_START_CYBERPANEL.md)** - Resumen ultra rápido (50 min)
3. 📖 **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** - Guía completa y detallada

### Si prefieres una guía simplificada:
1. 📤 **[GIT_PREPARE.md](GIT_PREPARE.md)** - Subir archivos al repositorio Git
2. 🚀 **[START_HERE.md](START_HERE.md)** - Guía adaptada para CyberPanel (60 min)

### Si es tu PRIMERA VEZ sin CyberPanel (servidor tradicional):
1. 📤 **[GIT_PREPARE.md](GIT_PREPARE.md)** - Subir archivos al repositorio Git
2. 🚀 **[START_HERE.md](START_HERE.md)** - Guía rápida en 3 pasos (60 min)

### Si ya tienes el sitio desplegado y quieres ACTUALIZAR:
```bash
ssh usuario@ip-del-vps
bash ~/deploy-appwebzarza.sh
```
✅ Listo en 2 minutos.

---

## 📖 Documentación Completa

| Archivo | Propósito | Cuándo usarlo |
|---------|-----------|---------------|
| **[QUICK_START_CYBERPANEL.md](QUICK_START_CYBERPANEL.md)** 🎯 | Resumen ultra rápido | **Primeros pasos con CyberPanel** |
| **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** ⭐ | Guía completa CyberPanel | **Documentación detallada CyberPanel** |
| **[START_HERE.md](START_HERE.md)** | Guía simplificada | CyberPanel con pasos básicos |
| **[GIT_PREPARE.md](GIT_PREPARE.md)** | Preparar repositorio Git | Antes del deployment, subir archivos |
| **[DEPLOYMENT.md](DEPLOYMENT.md)** | Documentación tradicional | Servidores sin CyberPanel (Nginx/Apache) |
| **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** | Checklist tradicional | Servidores sin CyberPanel |
| **[server-commands.sh](server-commands.sh)** | Comandos tradicionales | Servidores sin CyberPanel |
| **[INDEX_DEPLOYMENT.md](INDEX_DEPLOYMENT.md)** | Este archivo | Orientación general |

---

## 🔧 Archivos de Configuración

| Archivo | Descripción | Uso |
|----------cyberpanel.sh** ⭐ | Script para CyberPanel/OpenLiteSpeed | **Para tu servidor (CyberPanel)** |
| **deploy.sh** | Script para servidores tradicionales | Para Nginx/Apache sin panel |
| **nginx.conf** | Configuración de Nginx | Solo si NO usas CyberPanel |
| **apache.conf** | Configuración de Apache | Solo si NO usas CyberPanel
| **apache.conf** | Configuración de Apache | Copiar a `/etc/httpd/conf.d/` |
| **.env.production.example** | Variables de entorno para producción | Copiar a `.env` y personalizar |

---

## 🗺️ Flujo de Trabajo Completo

```
┌─────────────────────────────────────────────────────┐
│ 1. PREPARAR CÓDIGO                                  │
│    └─> GIT_PREPARE.md                              │
│        - Subir archivos a GitHub                    │
│        - Verificar .env no está en repo            │
└─────────────────────────────────────────────────────┘
                        ↓
┌────CREAR SITIO EN CYBERPANEL (10 min)              │
│    └─> CyberPanel Web Interface                    │
│        - Website → Create Website                   │
│        - Domain: contigo.lazarza.com.mx            │
│        - PHP 8.2+                                   │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 3. CLONAR Y CONFIGURAR (30 min)                     │
│    └─> SSH al servidor                             │
│        - Clonar repositorio                         │
│        - Configurar .env                            │
│        - Instalar dependencias                      │
│        - Ejecutar migraciones                       │
│        - Configurar permisos (nobody:nobody)       │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 4. CONFIGURAR OPENLITESPEED (10 min)               │
│    └─> CyberPanel → vHost Conf                    │
│        - docRoot apunta a /public                   │
│        - Reiniciar OpenLiteSpeed                    │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 5. INSTALAR SSL (2 min)                             │
│    └─> CyberPanel → SSL → Issue SSL (1-click)     │
└─────────────────────────────────────────────────────┘
                        ↓
┌───────────────────- Quiero empezar YA
- **[QUICK_START_CYBERPANEL.md](QUICK_START_CYBERPANEL.md)** 🎯 - Resumen en 3 pasos
- Tiempo: 50 minutos
- Comandos listos para copiar y pegar

### 📊 Nivel Intermedio - Con algo de experiencia  
- **[START_HERE.md](START_HERE.md)** - Guía simplificada paso a paso
- Tiempo: 60 minutos
- Explicaciones básicas incluidas

### 🎯 Nivel Avanzado - Quiero entender todo
- **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** ⭐ - Documentación completa
- Configuraciones detalladas
- Troubleshooting avanzado
- Optimizaciones adicionales────────────────────────────┘
```

---

## 🎓 Niveles de Detalle

### 📱 Nivel Básico (Principiante)
- **[START_HERE.md](START_HERE.md)** - Instrucciones paso a paso sin complicaciones
- Tiempo: 60 minutos/home/dominio/public_html/storage/logs/laravel.log` | DEPLOYMENT_CYBERPANEL.md - Troubleshooting |
| Error 403 | Arreglar permisos: `chown -R nobody:nobody /home/dominio/public_html` | DEPLOYMENT_CYBERPANEL.md - Permisos |
| BD no conecta | Verificar .env y IP permitida | QUICK_START_CYBERPANEL.md |
| Cambios no aparecen | Limpiar caché + `systemctl restart lsws` | DEPLOYMENT_CYBERPANEL.md |
| SSL no funciona | CyberPanel → SSL → Issue SSL | QUICK_START_CYBERPANEL.md |
| docRoot incorrecto | Debe ser `/home/dominio/public_html/public` | DEPLOYMENT_CYBERPANEL.md
- Entiendes qué hace cada comando

### 🎯 Nivel Avanzado (Exp (CyberPanel)

```bash
# Ver logs de errores
tail -50 /home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log
tail -50 /usr/local/lsws/logs/error.log

# Arreglar permisos
cd /home/contigo.lazarza.com.mx/public_html
chown -R nobody:nobody storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Limpiar todo el caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reiniciar OpenLiteSpeed
systemctl restart lswsr todos los comandos | server-commands.sh |

### Comandos de Emergencia

```bash
# Ver todos los comandos disponibles
bash /var/www/appwebzarza/server-commands.sh

# Ver logs de errores
tail -50 /var/www/appwebzarza/storage/logs/laravel.log

# Arreglar permisos
sudo chown -R $USER:nginx /var/www/appwebzarza
sudo chmod -R 775 storage bootstrap/cache

# Limpiar todo el caché
cd /var/www/appwebzarza
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📋 CHECKLISTS RÁPIDAS

### ✅ Pre-Deployment (Antes de ir al servidor)

- [ ] Código subido a GitHub (GIT_PREPARE.md)
- [ ] .env NO está en el repositorio
- [ ] Credenciales SSH disponibles
- [ ] Credenciales de BD remota disponibles
- [ ] Credenciales SMTP disponibles
- [ ] API keys disponibles

### ✅ Durante Deployment (En el servidor)

- [ ] Proyecto clonado desde Git
- [ ] Dependencias instaladas (Composer + NPM)
- [ ] .env configurado con credenciales reales
- [ ] Migraciones ejecutada (CyberPanel)
```
appwebzarza/
├── 📄 QUICK_START_CYBERPANEL.md  ⭐ Empieza aquí (resumen)
├── 📄 DEPLOYMENT_CYBERPANEL.md   ⭐ Guía completa
├── 📄 START_HERE.md              ← Guía simplificada
├── 📄 GIT_PREPARE.md             ← Prepara Git primero
├── 📄 INDEX_DEPLOYMENT.md        ← Este archivo
├── 🔧 deploy-cyberpanel.sh       ⭐ Script para CyberPanel
├── 🔧 .env.production.example    ← Template .env
│
├── 📄 DEPLOYMENT.md              (Para servidores sin panel)
├── 📄 DEPLOYMENT_CHECKLIST.md    (Para servidores sin panel)
├── 🔧 deploy.sh                  (Para Nginx/Apache)
├── 🔧 nginx.conf                 (Para Nginx)
├── 🔧 apache.conf                (Para Apache)
├── 🔧 server-commands. (CyberPanel)

```bash
# Deployment rápido
bash ~/deploy-appwebzarza.sh

# Ver logs Laravel
tail -f /home/contigo.lazarza.com.mx/public_html/storage/logs/laravel.log

# Ver logs OpenLiteSpeed
tail -f /usr/local/lsws/logs/error.log

# Limpiar caché
cd /home/contigo.lazarza.com.mx/public_html
php artisan cache:clear && php artisan config:clear

# Reiniciar OpenLiteSpeed
systemctl restart lsws

# Arreglar permisos
chown -R nobody:nobody storage bootstrap/cache
ch**CyberPanel:** https://ip-vps:8090
- **Sitio:** https://contigo.lazarza.com.mx
- **Repositorio:** https://github.com/IngDatosZarza/appwebzarza
- **Documentación Laravel:** https://laravel.com/docs
- **Documentación OpenLiteSpeed:** https://openlitespeed.org/kb/
├── 🔧 apache.conf                ← Config Apache
├── 🔧 server-commands.sh         ← Comandos útiles
├── 🔧 .env.production.example    ← Template .env
└── 📖 README.md                  ← Info del proyecto
```

### Comandos Más Usados

```bash
# Deployment rápido
bash ~/deploy-appwebzarza.sh

# Ver logs
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan cache:clear && php artisan config:clear

# Reiniciar servicios
sudo systemctl restart nginx && sudo systemctl restart php-fpm

# Ver comandos disponibles
bash server-commands.sh con CyberPanel:
1. 👉 Ir a **[GIT_PREPARE.md](GIT_PREPARE.md)**
2. 👉 Luego ir a **[QUICK_START_CYBERPANEL.md](QUICK_START_CYBERPANEL.md)** (ultra rápido)
3. 👉 O ir a **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** (completo)

### Si ya desplegaste:
- Para actualizaciones: `bash ~/deploy-appwebzarza.sh`
- Para troubleshooting: **[DEPLOYMENT_CYBERPANEL.md](DEPLOYMENT_CYBERPANEL.md)** - Sección Troubleshooting
- Para SSL: CyberPanel → SSL → Issue SSL (1-click)

### Para mejorar el deployment:
- ✅ Configurar SSL/HTTPS (1-click en CyberPanel)
- ✅ Configurar backups automáticos (CyberPanel → Backup)
- ✅ Configurar monitoring (CyberPanel incluye métricas)
- ⏰ Configurar queue workers si usas colas
- 🔄 Verificar cron jobs funcionan

---

**¿Listo para empezar? 🚀**

👉 **[GIT_PREPARE.md](GIT_PREPARE.md)** → **[QUICK_START_CYBERPANEL.md](QUICK_START_CYBERPANEL.md)** ⭐shooting
- Para comandos útiles: `bash server-commands.sh`

### Para mejorar el deployment:
- Configurar SSL/HTTPS (DEPLOYMENT.md - SSL)
- Configurar backups automáticos
- Configurar monitoring
- Configurar queue workers (si usas colas)

---

**¿Listo para empezar? 🚀**

👉 **[GIT_PREPARE.md](GIT_PREPARE.md)** → **[START_HERE.md](START_HERE.md)**
