#!/bin/bash

# ========================================
# La Zarza Contigo - Scripts de Migración
# Bash para Linux/Unix
# ========================================

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

clear

echo -e "${CYAN}🎯 La Zarza Contigo - UTILIDADES DE MIGRACIÓN${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

show_menu() {
    echo -e "${YELLOW}Selecciona una opción:${NC}"
    echo ""
    echo -e "${GREEN}1.${NC} 📦 Crear respaldo de base de datos"
    echo -e "${GREEN}2.${NC} 🔍 Verificar estado del sistema"
    echo -e "${GREEN}3.${NC} 📁 Empaquetar proyecto para migración"
    echo -e "${GREEN}4.${NC} 🧪 Probar conexión a BD"
    echo -e "${GREEN}5.${NC} 🔑 Ver usuarios de prueba"
    echo -e "${GREEN}6.${NC} 📊 Ver resumen de datos"
    echo -e "${GREEN}7.${NC} 🌐 Iniciar servidor Laravel"
    echo -e "${GREEN}8.${NC} 🧹 Limpiar cache de Laravel"
    echo -e "${GREEN}9.${NC} 🔧 Configurar permisos (Linux)"
    echo -e "${RED}0.${NC} ❌ Salir"
    echo ""
}

backup_database() {
    echo ""
    echo -e "${CYAN}📦 Creando respaldo de base de datos...${NC}"
    php crear_respaldo_bd.php
    echo ""
    echo -e "${GREEN}✅ Respaldo completado. Revisa la carpeta 'respaldos/'${NC}"
    read -p "Presiona Enter para continuar..."
}

verify_system() {
    echo ""
    echo -e "${CYAN}🔍 Verificando sistema...${NC}"
    php verificar_migracion.php
    read -p "Presiona Enter para continuar..."
}

package_project() {
    echo ""
    echo -e "${CYAN}📁 Empaquetando proyecto...${NC}"
    
    timestamp=$(date +"%Y-%m-%d_%H%M%S")
    zipname="La Zarza Contigo_${timestamp}.tar.gz"
    
    echo -e "${YELLOW}Creando archivo: $zipname${NC}"
    
    # Crear archivo tar excluyendo directorios innecesarios
    tar -czf "$zipname" \
        --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='.git' \
        --exclude='storage/logs/*' \
        --exclude='bootstrap/cache/*' \
        --exclude='*.tar.gz' \
        --exclude='*.zip' \
        --exclude='respaldos' \
        .
    
    size=$(du -h "$zipname" | cut -f1)
    
    echo ""
    echo -e "${GREEN}✅ Paquete creado exitosamente!${NC}"
    echo -e "${CYAN}   Archivo: $zipname${NC}"
    echo -e "${CYAN}   Tamaño: $size${NC}"
    
    read -p "Presiona Enter para continuar..."
}

test_database() {
    echo ""
    echo -e "${CYAN}🧪 Probando conexión a base de datos...${NC}"
    php check_client_status.php
    read -p "Presiona Enter para continuar..."
}

show_test_users() {
    clear
    echo -e "${CYAN}🔑 Usuarios de Prueba${NC}"
    echo -e "${CYAN}==========================================${NC}"
    echo ""
    
    echo -e "${YELLOW}Cliente:${NC}"
    echo "  Email: cliente@test.com"
    echo "  Pass:  password"
    echo "  URL:   http://localhost:8000/login"
    echo ""
    
    echo -e "${YELLOW}Admin:${NC}"
    echo "  Email: admin@test.com"
    echo "  Pass:  password"
    echo "  URL:   http://localhost:8000/admin/points"
    echo ""
    
    read -p "Presiona Enter para continuar..."
}

show_data_summary() {
    echo ""
    echo -e "${CYAN}📊 Resumen de Datos${NC}"
    echo -e "${CYAN}==========================================${NC}"
    echo ""
    
    php -r "
    try {
        \$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        \$pdo->exec('SET search_path TO appweb, public');
        
        \$tables = ['usuarios', 'sucursales', 'compras', 'cupones', 'cupones_asignados', 'transacciones_puntos', 'puntos'];
        
        foreach (\$tables as \$table) {
            \$stmt = \$pdo->query(\"SELECT COUNT(*) FROM \$table\");
            \$count = \$stmt->fetchColumn();
            echo str_pad(\$table, 25) . ': ' . \$count . \" registros\n\";
        }
        
        echo \"\n✅ Base de datos conectada correctamente\n\";
    } catch (Exception \$e) {
        echo \"❌ Error: \" . \$e->getMessage() . \"\n\";
    }
    "
    
    read -p "Presiona Enter para continuar..."
}

start_server() {
    echo ""
    echo -e "${CYAN}🌐 Iniciando servidor Laravel...${NC}"
    echo -e "${YELLOW}Accede a: http://localhost:8000${NC}"
    echo -e "Presiona Ctrl+C para detener el servidor"
    echo ""
    
    php artisan serve --host=0.0.0.0 --port=8000
}

clear_cache() {
    echo ""
    echo -e "${CYAN}🧹 Limpiando cache de Laravel...${NC}"
    
    php artisan cache:clear
    echo -e "${GREEN}  ✓ Cache limpiado${NC}"
    
    php artisan config:clear
    echo -e "${GREEN}  ✓ Config limpiado${NC}"
    
    php artisan route:clear
    echo -e "${GREEN}  ✓ Routes limpiado${NC}"
    
    php artisan view:clear
    echo -e "${GREEN}  ✓ Views limpiado${NC}"
    
    echo ""
    echo -e "${GREEN}✅ Cache de Laravel limpiado completamente${NC}"
    read -p "Presiona Enter para continuar..."
}

fix_permissions() {
    echo ""
    echo -e "${CYAN}🔧 Configurando permisos...${NC}"
    
    # Verificar si se ejecuta como root o con sudo
    if [ "$EUID" -ne 0 ]; then
        echo -e "${YELLOW}⚠️  Este script necesita permisos de superusuario${NC}"
        echo "   Ejecuta: sudo bash migracion_menu.sh"
        read -p "Presiona Enter para continuar..."
        return
    fi
    
    echo "Configurando permisos para storage y bootstrap/cache..."
    
    # Dar ownership a www-data (Nginx/Apache)
    chown -R www-data:www-data storage bootstrap/cache
    echo -e "${GREEN}  ✓ Ownership configurado (www-data)${NC}"
    
    # Dar permisos de escritura
    chmod -R 775 storage bootstrap/cache
    echo -e "${GREEN}  ✓ Permisos configurados (775)${NC}"
    
    # Configurar SELinux si está activo
    if command -v semanage &> /dev/null; then
        echo "Configurando SELinux..."
        semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/La Zarza Contigo/storage(/.*)?"
        semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/La Zarza Contigo/bootstrap/cache(/.*)?"
        restorecon -Rv storage bootstrap/cache
        echo -e "${GREEN}  ✓ SELinux configurado${NC}"
    fi
    
    echo ""
    echo -e "${GREEN}✅ Permisos configurados correctamente${NC}"
    read -p "Presiona Enter para continuar..."
}

# Menú principal
while true; do
    clear
    echo -e "${CYAN}🎯 La Zarza Contigo - UTILIDADES DE MIGRACIÓN${NC}"
    echo -e "${CYAN}========================================${NC}"
    echo ""
    
    show_menu
    
    read -p "Opción: " choice
    
    case $choice in
        1) backup_database ;;
        2) verify_system ;;
        3) package_project ;;
        4) test_database ;;
        5) show_test_users ;;
        6) show_data_summary ;;
        7) start_server ;;
        8) clear_cache ;;
        9) fix_permissions ;;
        0) 
            echo ""
            echo -e "${CYAN}👋 ¡Hasta luego!${NC}"
            exit 0
            ;;
        *)
            echo ""
            echo -e "${RED}❌ Opción inválida${NC}"
            sleep 1
            ;;
    esac
done
