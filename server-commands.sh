#!/bin/bash

###############################################################################
# Comandos Útiles para Administración del Servidor
# Sistema de Puntos Zarza - AlmaLinux 9
###############################################################################

echo "============================================="
echo "Comandos Útiles - appwebzarza"
echo "============================================="
echo ""

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_section() {
    echo -e "${GREEN}$1${NC}"
}

print_command() {
    echo -e "${YELLOW}$1${NC}"
}

# Verificación del sistema
print_section "=== 1. VERIFICAR ESTADO DE SERVICIOS ==="
print_command "sudo systemctl status nginx"
print_command "sudo systemctl status httpd"
print_command "sudo systemctl status php-fpm"
print_command "sudo systemctl status mysql"
echo ""

# Logs
print_section "=== 2. VER LOGS EN TIEMPO REAL ==="
print_command "# Laravel logs:"
print_command "tail -f /var/www/appwebzarza/storage/logs/laravel.log"
echo ""
print_command "# Nginx logs:"
print_command "tail -f /var/log/nginx/contigo.lazarza.com.mx-error.log"
print_command "tail -f /var/log/nginx/contigo.lazarza.com.mx-access.log"
echo ""
print_command "# Apache logs:"
print_command "tail -f /var/log/httpd/contigo.lazarza.com.mx-error.log"
print_command "tail -f /var/log/httpd/contigo.lazarza.com.mx-access.log"
echo ""
print_command "# PHP-FPM logs:"
print_command "tail -f /var/log/php-fpm/www-error.log"
echo ""

# Reiniciar servicios
print_section "=== 3. REINICIAR SERVICIOS ==="
print_command "# Reiniciar Nginx:"
print_command "sudo systemctl restart nginx"
echo ""
print_command "# Reiniciar Apache:"
print_command "sudo systemctl restart httpd"
echo ""
print_command "# Reiniciar PHP-FPM:"
print_command "sudo systemctl restart php-fpm"
echo ""
print_command "# Recargar configuración (sin downtime):"
print_command "sudo systemctl reload nginx"
print_command "sudo systemctl reload php-fpm"
echo ""

# Limpiar cachés de Laravel
print_section "=== 4. LIMPIAR CACHÉS DE LARAVEL ==="
print_command "cd /var/www/appwebzarza"
print_command "php artisan cache:clear"
print_command "php artisan config:clear"
print_command "php artisan route:clear"
print_command "php artisan view:clear"
print_command "php artisan event:clear"
echo ""

# Optimizar Laravel
print_section "=== 5. OPTIMIZAR LARAVEL ==="
print_command "cd /var/www/appwebzarza"
print_command "php artisan config:cache"
print_command "php artisan route:cache"
print_command "php artisan view:cache"
print_command "php artisan event:cache"
print_command "composer dump-autoload -o"
echo ""

# Verificar permisos
print_section "=== 6. ARREGLAR PERMISOS ==="
print_command "cd /var/www/appwebzarza"
print_command "sudo chown -R \$USER:nginx ."
print_command "sudo chmod -R 775 storage bootstrap/cache"
print_command "find storage -type d -exec chmod 775 {} \;"
print_command "find storage -type f -exec chmod 664 {} \;"
echo ""

# Base de datos
print_section "=== 7. BASE DE DATOS ==="
print_command "# Ejecutar migraciones:"
print_command "php artisan migrate --force"
echo ""
print_command "# Rollback última migración:"
print_command "php artisan migrate:rollback"
echo ""
print_command "# Ver estado de migraciones:"
print_command "php artisan migrate:status"
echo ""
print_command "# Probar conexión a BD:"
print_command "php artisan tinker"
print_command ">>> DB::connection()->getPdo();"
print_command ">>> DB::table('users')->count();"
echo ""

# Monitoreo
print_section "=== 8. MONITOREO DEL SISTEMA ==="
print_command "# Ver uso de CPU y memoria:"
print_command "htop"
echo ""
print_command "# Ver procesos PHP:"
print_command "ps aux | grep php"
echo ""
print_command "# Ver conexiones activas:"
print_command "ss -tulpn | grep :80"
print_command "ss -tulpn | grep :443"
echo ""
print_command "# Ver uso de disco:"
print_command "df -h"
echo ""
print_command "# Ver archivos grandes en storage:"
print_command "du -sh /var/www/appwebzarza/storage/*"
echo ""

# Deployment
print_section "=== 9. DEPLOYMENT ==="
print_command "# Deployment automático:"
print_command "bash ~/deploy-appwebzarza.sh"
echo ""
print_command "# Deployment manual:"
print_command "cd /var/www/appwebzarza"
print_command "git pull origin master"
print_command "composer install --no-dev"
print_command "php artisan migrate --force"
print_command "php artisan config:cache"
print_command "sudo systemctl reload nginx"
echo ""

# Modo mantenimiento
print_section "=== 10. MODO MANTENIMIENTO ==="
print_command "# Activar:"
print_command "php artisan down --message='Mantenimiento programado' --retry=60"
echo ""
print_command "# Desactivar:"
print_command "php artisan up"
echo ""

# SELinux
print_section "=== 11. SELINUX ==="
print_command "# Ver estado de SELinux:"
print_command "sestatus"
echo ""
print_command "# Ver errores de SELinux:"
print_command "sudo ausearch -m AVC,USER_AVC -ts recent"
echo ""
print_command "# Aplicar contextos de SELinux:"
print_command "sudo restorecon -Rv /var/www/appwebzarza"
echo ""

# Firewall
print_section "=== 12. FIREWALL ==="
print_command "# Ver reglas activas:"
print_command "sudo firewall-cmd --list-all"
echo ""
print_command "# Abrir puerto HTTP/HTTPS:"
print_command "sudo firewall-cmd --permanent --add-service=http"
print_command "sudo firewall-cmd --permanent --add-service=https"
print_command "sudo firewall-cmd --reload"
echo ""

# SSL/HTTPS
print_section "=== 13. SSL/HTTPS ==="
print_command "# Instalar certificado SSL:"
print_command "sudo certbot --nginx -d contigo.lazarza.com.mx"
echo ""
print_command "# Renovar certificado:"
print_command "sudo certbot renew"
echo ""
print_command "# Verificar renovación automática:"
print_command "sudo systemctl status certbot-renew.timer"
echo ""

# Backup
print_section "=== 14. BACKUP ==="
print_command "# Backup de archivos:"
print_command "tar -czf ~/backup-appwebzarza-\$(date +%Y%m%d).tar.gz /var/www/appwebzarza"
echo ""
print_command "# Backup de base de datos (remota):"
print_command "php artisan backup:run"
echo ""

# Testing
print_section "=== 15. TESTING ==="
print_command "# Probar que el sitio responde:"
print_command "curl -I http://contigo.lazarza.com.mx"
echo ""
print_command "# Probar con verbose:"
print_command "curl -v http://contigo.lazarza.com.mx"
echo ""
print_command "# Ver headers:"
print_command "curl -I http://contigo.lazarza.com.mx | head -20"
echo ""

# Cron jobs
print_section "=== 16. CRON JOBS ==="
print_command "# Editar crontab:"
print_command "crontab -e"
echo ""
print_command "# Ver crontab actual:"
print_command "crontab -l"
echo ""
print_command "# Ver logs de cron:"
print_command "sudo tail -f /var/log/cron"
echo ""

# Queue workers
print_section "=== 17. QUEUE WORKERS ==="
print_command "# Ver trabajos en cola:"
print_command "php artisan queue:work --once"
echo ""
print_command "# Procesar cola en background:"
print_command "nohup php artisan queue:work &"
echo ""
print_command "# Ver procesos de cola:"
print_command "ps aux | grep 'queue:work'"
echo ""

# Git
print_section "=== 18. GIT ==="
print_command "# Ver estado:"
print_command "git status"
echo ""
print_command "# Ver commits recientes:"
print_command "git log --oneline -10"
echo ""
print_command "# Ver branch actual:"
print_command "git branch --show-current"
echo ""
print_command "# Descartar cambios locales:"
print_command "git reset --hard HEAD"
print_command "git clean -fd"
echo ""

# Troubleshooting rápido
print_section "=== 19. TROUBLESHOOTING RÁPIDO ==="
print_command "# Error 500 - Ver últimos 50 errores:"
print_command "tail -50 /var/www/appwebzarza/storage/logs/laravel.log"
echo ""
print_command "# Error 403 - Arreglar permisos:"
print_command "sudo chown -R \$USER:nginx /var/www/appwebzarza"
print_command "sudo chmod -R 775 storage bootstrap/cache"
echo ""
print_command "# Página en blanco - Verificar logs de PHP:"
print_command "tail -50 /var/log/php-fpm/www-error.log"
echo ""
print_command "# BD no conecta - Probar conexión:"
print_command "php artisan tinker"
print_command ">>> DB::connection()->getPdo();"
echo ""

echo "============================================="
echo "Para más información, consulta DEPLOYMENT.md"
echo "============================================="
