# 📦 ZarzaPoints - Checklist de Migración

## ✅ PASOS COMPLETADOS

### ANTES DE MIGRAR (En Servidor Actual)
- [ ] Ejecutar respaldo de base de datos: `php crear_respaldo_bd.php`
- [ ] Verificar que el archivo de respaldo se creó en carpeta `/respaldos/`
- [ ] Comprimir proyecto (sin node_modules, vendor, .git)
- [ ] Copiar archivo `.env` para referencia
- [ ] Anotar versión de PHP: ___________
- [ ] Anotar versión de PostgreSQL: ___________

### TRANSFERIR ARCHIVOS
- [ ] Subir archivo ZIP del proyecto al servidor nuevo
- [ ] Subir archivo SQL de respaldo al servidor nuevo
- [ ] Descomprimir proyecto en ubicación correcta
- [ ] Verificar que todos los archivos se copiaron

### CONFIGURAR SERVIDOR NUEVO
- [ ] Instalar PHP 8.1+ con extensiones requeridas
- [ ] Instalar PostgreSQL
- [ ] Instalar Composer
- [ ] Instalar Nginx/Apache
- [ ] Configurar firewall (si aplica)

### BASE DE DATOS
- [ ] Crear usuario PostgreSQL: `appwebuser`
- [ ] Crear esquema: `appweb`
- [ ] Asignar permisos al usuario
- [ ] Restaurar respaldo SQL
- [ ] Verificar que las tablas se crearon
- [ ] Verificar que los datos se importaron
- [ ] Probar conexión con: `psql -h localhost -U appwebuser -d postgres`

### CONFIGURAR PROYECTO
- [ ] Ejecutar: `composer install --no-dev --optimize-autoloader`
- [ ] Copiar `.env.example` a `.env`
- [ ] Editar `.env` con credenciales del servidor nuevo
- [ ] Generar application key: `php artisan key:generate`
- [ ] Dar permisos: `chmod -R 775 storage bootstrap/cache`
- [ ] Dar ownership: `chown -R www-data:www-data storage bootstrap/cache`

### SERVIDOR WEB
- [ ] Configurar virtual host (Nginx o Apache)
- [ ] Apuntar DocumentRoot a `/public`
- [ ] Habilitar rewrite module (Apache)
- [ ] Reiniciar servidor web
- [ ] Probar acceso: `curl http://localhost`

### VERIFICACIÓN
- [ ] Ejecutar: `php verificar_migracion.php`
- [ ] Todos los checks pasaron ✅
- [ ] Abrir navegador: `http://localhost:8000`
- [ ] Login funciona (cliente@test.com)
- [ ] Login admin funciona (admin@test.com)
- [ ] Dashboard carga correctamente
- [ ] Cupones se muestran
- [ ] Botón "Canjear Cupón" funciona
- [ ] Popup de QR aparece
- [ ] QR code se genera
- [ ] Admin puede validar cupones
- [ ] Registro de tickets funciona

### OPTIMIZACIÓN (Opcional)
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] Configurar backup automático
- [ ] Configurar monitoreo de logs
- [ ] Configurar SSL (si es público)

### SEGURIDAD
- [ ] Cambiar contraseñas de prueba
- [ ] Configurar firewall
- [ ] Restringir acceso a PostgreSQL
- [ ] Configurar `.env` con APP_DEBUG=false (si es producción)
- [ ] Revisar permisos de archivos

---

## 🚨 PROBLEMAS COMUNES

### Error: "SQLSTATE[08006] Could not connect to server"
**Solución:**
```bash
# Verificar que PostgreSQL esté corriendo
sudo systemctl status postgresql
sudo systemctl start postgresql

# Verificar credenciales en .env
cat .env | grep DB_
```

### Error: "Permission denied" en storage/
**Solución:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Error: "500 Internal Server Error"
**Solución:**
```bash
# Ver logs
tail -f storage/logs/laravel.log

# Limpiar cache
php artisan cache:clear
php artisan config:clear
```

### QR codes no se generan
**Solución:**
```bash
# Instalar extensión GD
sudo apt install php8.2-gd
sudo systemctl restart php8.2-fpm
```

---

## 📞 COMANDOS ÚTILES

```bash
# Ver estado de PostgreSQL
sudo systemctl status postgresql

# Conectar a BD
psql -h localhost -U appwebuser -d postgres

# Ver tablas
\dt

# Contar usuarios
SELECT COUNT(*) FROM appweb.usuarios;

# Reiniciar servicios
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart postgresql

# Ver logs en tiempo real
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
tail -f /var/log/postgresql/postgresql-14-main.log
```

---

## ✅ CRITERIOS DE ÉXITO

La migración es exitosa cuando:

1. ✅ Base de datos restaurada con todas las tablas
2. ✅ Login funciona para cliente y admin
3. ✅ Cupones se muestran correctamente
4. ✅ Canje de cupones genera QR
5. ✅ Popup aparece con código y QR
6. ✅ Admin puede validar cupones
7. ✅ Registro de tickets funciona
8. ✅ No hay errores en logs
9. ✅ Permisos correctos en storage/
10. ✅ Todas las extensiones PHP instaladas

---

## 📝 NOTAS

**Fecha de migración:** _________________

**Servidor origen:** _________________

**Servidor destino:** _________________

**Responsable:** _________________

**Observaciones:**
_________________________________________________
_________________________________________________
_________________________________________________

---

**Última actualización:** 2025-10-15
