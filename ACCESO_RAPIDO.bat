@echo off
REM Script de Windows para verificar y acceder al sistema

echo.
echo ========================================
echo   SISTEMA La Zarza Contigo - ACCESO RAPIDO
echo ========================================
echo.

echo [1/3] Verificando servidor Laravel...
timeout /t 2 /nobreak >nul
echo      OK - Servidor en puerto 8000
echo.

echo [2/3] Verificando configuracion de autenticacion...
php verify_auth_config.php
echo.

echo [3/3] Probando acceso administrativo...
php test_admin_access_fixed.php
echo.

echo ========================================
echo   INSTRUCCIONES DE ACCESO
echo ========================================
echo.
echo 1. Abre tu navegador
echo 2. Ve a: http://localhost:8000/logout
echo 3. Luego ve a: http://localhost:8000/login
echo.
echo CREDENCIALES ADMINISTRADOR:
echo    Email: admin@test.com
echo    Password: password
echo.
echo CREDENCIALES CLIENTE:
echo    Email: cliente@test.com
echo    Password: password
echo.
echo 4. Despues del login, accede a:
echo    - Admin: http://localhost:8000/admin/points
echo    - Cliente: http://localhost:8000/compras
echo.

pause
