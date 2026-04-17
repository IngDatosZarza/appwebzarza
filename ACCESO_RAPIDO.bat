@echo off
REM Script de Windows para verificar y acceder al sistema

echo.
echo ========================================
echo   SISTEMA La Zarza Contigo - ACCESO RAPIDO
echo ========================================
echo.

echo [1/4] Verificando servidor Laravel en puerto 8000...
netstat -an | find ":8000" | find "LISTENING" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo      ✓ Servidor esta corriendo en puerto 8000
) else (
    echo      ✗ Servidor NO esta corriendo
    echo.
    echo ¿Deseas iniciar el servidor Laravel ahora? (S/N)
    set /p INICIAR=
    if /i "%INICIAR%"=="S" (
        echo.
        echo Iniciando servidor...
        start "Servidor Laravel" cmd /k "cd /d \\172.16.1.44\htdocs\appwebzarza && php artisan serve --host=172.16.1.44 --port=8000"
        timeout /t 3 /nobreak >nul
        echo      ✓ Servidor iniciado en http://172.16.1.44:8000
    )
)
echo.

echo [2/4] Verificando configuracion de base de datos...
php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); try { DB::connection()->getPdo(); echo '     ✓ Conexion a base de datos OK'; } catch (Exception $e) { echo '     ✗ Error: ' . $e->getMessage(); }" 2>nul
echo.

echo [3/4] Sistema listo para usar
echo.

echo ========================================
echo   INSTRUCCIONES DE ACCESO
echo ========================================
echo.
echo 1. Abre tu navegador
echo 2. Ve a: http://172.16.1.44:8000
echo.
echo CREDENCIALES ADMINISTRADOR:
echo    Email: admin@test.com
echo    Password: password
echo.
echo CREDENCIALES CLIENTE:
echo    Email: cliente@test.com
echo    Password: password
echo.
echo Rutas principales:
echo    - Login: http://172.16.1.44:8000/login
echo    - Admin: http://172.16.1.44:8000/admin/dashboard
echo    - Cliente: http://172.16.1.44:8000/compras
echo.
echo ========================================
echo.

pause
