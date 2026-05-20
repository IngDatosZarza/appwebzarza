#!/bin/bash

###############################################################################
# Script de Deployment para Laravel en CyberPanel
# Sistema de Puntos Zarza - AlmaLinux 9 con OpenLiteSpeed
###############################################################################

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuración - ACTUALIZAR SEGÚN TU DOMINIO
DOMAIN="contigo.lazarza.com.mx"
APP_DIR="/home/${DOMAIN}/public_html"
WEB_USER="nobody"  # Usuario de OpenLiteSpeed en CyberPanel
LOG_FILE="$APP_DIR/storage/logs/deployment.log"

# Función para logging
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# Verificar que estamos en el directorio correcto
if [ ! -d "$APP_DIR" ]; then
    error "Directorio de aplicación no encontrado: $APP_DIR"
    exit 1
fi

cd "$APP_DIR" || exit 1

log "=================================================="
log "Deployment en CyberPanel - appwebzarza"
log "=================================================="

# 1. Activar modo mantenimiento
log "Activando modo mantenimiento..."
php artisan down --message="Actualizando sistema, volveremos pronto..." --retry=60

# 2. Pull del repositorio Git
log "Obteniendo últimos cambios desde Git..."
git fetch origin master
if ! git pull origin master; then
    error "Error al hacer pull del repositorio"
    php artisan up
    exit 1
fi

# 3. Instalar/Actualizar dependencias de Composer
log "Instalando dependencias de Composer..."
composer install --optimize-autoloader --no-dev --no-interaction

if [ $? -ne 0 ]; then
    error "Error al instalar dependencias de Composer"
    php artisan up
    exit 1
fi

# 4. Instalar/Actualizar dependencias de NPM y compilar assets
if [ -f "package.json" ]; then
    log "Instalando dependencias de NPM..."
    npm install
    
    log "Compilando assets para producción..."
    npm run build
    
    if [ $? -ne 0 ]; then
        warning "Error al compilar assets, continuando..."
    fi
fi

# 5. Ejecutar migraciones
log "Ejecutando migraciones de base de datos..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    error "Error al ejecutar migraciones"
    php artisan up
    exit 1
fi

# 6. Limpiar y reconstruir caché
log "Limpiando cachés antiguos..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

log "Optimizando aplicación para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Optimizar autoloader
log "Optimizando autoloader..."
composer dump-autoload --optimize

# 8. Ajustar permisos (CyberPanel usa nobody:nobody)
log "Ajustando permisos de archivos..."
chown -R $WEB_USER:$WEB_USER "$APP_DIR/storage"
chown -R $WEB_USER:$WEB_USER "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"

# Asegurar permisos correctos en subdirectorios
find "$APP_DIR/storage" -type d -exec chmod 775 {} \;
find "$APP_DIR/storage" -type f -exec chmod 664 {} \;
find "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;
find "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;

# 9. Reiniciar OpenLiteSpeed
log "Reiniciando OpenLiteSpeed..."
if systemctl is-active --quiet lsws; then
    systemctl reload lsws
    if [ $? -ne 0 ]; then
        warning "Reload falló, intentando restart completo..."
        systemctl restart lsws
    fi
else
    error "OpenLiteSpeed no está corriendo"
fi

# 10. Desactivar modo mantenimiento
log "Desactivando modo mantenimiento..."
php artisan up

# 11. Prueba rápida de la aplicación
log "Verificando que la aplicación responde..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -k https://${DOMAIN})

if [ "$HTTP_STATUS" = "200" ] || [ "$HTTP_STATUS" = "302" ]; then
    log "✓ Aplicación respondiendo correctamente (HTTP $HTTP_STATUS)"
else
    warning "⚠ La aplicación responde con código HTTP $HTTP_STATUS"
fi

# 12. Limpiar archivos temporales
log "Limpiando archivos temporales..."
find "$APP_DIR/storage/logs" -name "*.log" -mtime +30 -delete

log "=================================================="
log "Deployment completado exitosamente"
log "=================================================="

# Mostrar información del deployment
log ""
log "Información del deployment:"
log "- Dominio: ${DOMAIN}"
log "- Branch: $(git branch --show-current)"
log "- Commit: $(git log -1 --pretty=format:'%h - %s')"
log "- Fecha: $(date +'%Y-%m-%d %H:%M:%S')"
log "- Usuario: $USER"
log ""

log "Verificar sitio en: https://${DOMAIN}"
log ""

exit 0
