@echo off
echo ===============================================
echo    INICIANDO SERVIDOR LARAVEL
echo ===============================================
echo.
echo Servidor: http://172.16.1.44:8000
echo Directorio: \\172.16.1.44\htdocs\appwebzarza
echo.
echo Presiona Ctrl+C para detener el servidor
echo ===============================================
echo.

cd /d \\172.16.1.44\htdocs\appwebzarza

echo Limpiando cache...
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1

echo.
echo Iniciando servidor Laravel...
echo.

php artisan serve --host=172.16.1.44 --port=8000

pause
