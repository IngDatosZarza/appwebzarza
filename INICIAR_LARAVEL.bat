@echo off
REM Script de inicio para Laravel en rutas de red

echo ========================================
echo  INICIANDO LARAVEL - Workaround UNC
echo ========================================
echo.

REM 1. Asegurar que bootstrap/cache existe y tiene archivos
echo [1/4] Verificando bootstrap/cache...
if not exist "bootstrap\cache" mkdir "bootstrap\cache"
if not exist "bootstrap\cache\.gitkeep" echo. > "bootstrap\cache\.gitkeep"

REM 2. Crear archivos de caché si no existen
echo [2/4] Creando archivos de cache...
php -r "file_put_contents('bootstrap/cache/packages.php', '<?php return [];');"
php -r "file_put_contents('bootstrap/cache/services.php', '<?php return [];');"

REM 3. Crear directorios de storage necesarios
echo [3/4] Verificando directorios de storage...
if not exist "storage\framework\cache" mkdir "storage\framework\cache"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\logs" mkdir "storage\logs"

REM 4. Iniciar servidor
echo [4/4] Iniciando servidor Laravel...
echo.
echo Servidor disponible en: http://localhost:8000
echo Presiona Ctrl+C para detener
echo.
php artisan serve --host=localhost --port=8000

pause
