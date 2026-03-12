@echo off
chcp 65001 > nul
color 0B
title La Zarza Contigo - Utilidades de Migración

:MENU
cls
echo.
echo ═══════════════════════════════════════════════════════
echo     🎯 La Zarza Contigo - UTILIDADES DE MIGRACIÓN
echo ═══════════════════════════════════════════════════════
echo.
echo  Selecciona una opción:
echo.
echo  [1] 📦 Crear respaldo de base de datos
echo  [2] 🔍 Verificar estado del sistema
echo  [3] 🧪 Probar conexión a BD
echo  [4] 🔑 Ver usuarios de prueba
echo  [5] 📊 Ver resumen de datos
echo  [6] 🌐 Iniciar servidor Laravel
echo  [7] 🧹 Limpiar cache de Laravel
echo  [8] 📖 Ver guías de migración
echo  [9] 💻 Menú interactivo PowerShell
echo  [0] ❌ Salir
echo.
echo ═══════════════════════════════════════════════════════
echo.

set /p opcion="Elige una opción (0-9): "

if "%opcion%"=="1" goto BACKUP
if "%opcion%"=="2" goto VERIFY
if "%opcion%"=="3" goto TEST_DB
if "%opcion%"=="4" goto USERS
if "%opcion%"=="5" goto SUMMARY
if "%opcion%"=="6" goto SERVER
if "%opcion%"=="7" goto CACHE
if "%opcion%"=="8" goto DOCS
if "%opcion%"=="9" goto POWERSHELL
if "%opcion%"=="0" goto EXIT

echo.
echo ❌ Opción no válida
timeout /t 2 > nul
goto MENU

:BACKUP
cls
echo.
echo 📦 CREANDO RESPALDO DE BASE DE DATOS
echo ═══════════════════════════════════════════════════════
echo.
php crear_respaldo_bd.php
echo.
echo ═══════════════════════════════════════════════════════
echo ✅ Respaldo completado. Revisa la carpeta 'respaldos/'
echo.
pause
goto MENU

:VERIFY
cls
echo.
echo 🔍 VERIFICANDO ESTADO DEL SISTEMA
echo ═══════════════════════════════════════════════════════
echo.
php verificar_migracion.php
echo.
echo ═══════════════════════════════════════════════════════
pause
goto MENU

:TEST_DB
cls
echo.
echo 🧪 PROBANDO CONEXIÓN A BASE DE DATOS
echo ═══════════════════════════════════════════════════════
echo.
php check_client_status.php
echo.
echo ═══════════════════════════════════════════════════════
pause
goto MENU

:USERS
cls
echo.
echo 🔑 USUARIOS DE PRUEBA
echo ═══════════════════════════════════════════════════════
echo.
echo  Cliente:
echo    Email: cliente@test.com
echo    Pass:  password
echo    URL:   http://localhost:8000/login
echo.
echo  Admin:
echo    Email: admin@test.com
echo    Pass:  password
echo    URL:   http://localhost:8000/admin/points
echo.
echo ═══════════════════════════════════════════════════════
pause
goto MENU

:SUMMARY
cls
echo.
echo 📊 RESUMEN DE DATOS
echo ═══════════════════════════════════════════════════════
echo.
php -r "try { $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass'); $pdo->exec('SET search_path TO appweb, public'); $tables = ['usuarios', 'sucursales', 'compras', 'cupones', 'cupones_asignados', 'transacciones_puntos', 'puntos']; foreach ($tables as $table) { $stmt = $pdo->query(\"SELECT COUNT(*) FROM $table\"); $count = $stmt->fetchColumn(); echo str_pad($table, 25) . ': ' . $count . \" registros\n\"; } echo \"\n✅ Base de datos conectada correctamente\n\"; } catch (Exception $e) { echo \"❌ Error: \" . $e->getMessage() . \"\n\"; }"
echo.
echo ═══════════════════════════════════════════════════════
pause
goto MENU

:SERVER
cls
echo.
echo 🌐 INICIANDO SERVIDOR LARAVEL
echo ═══════════════════════════════════════════════════════
echo.
echo  Accede a: http://localhost:8000
echo  Presiona Ctrl+C para detener el servidor
echo.
echo ═══════════════════════════════════════════════════════
echo.
php artisan serve --host=localhost --port=8000
pause
goto MENU

:CACHE
cls
echo.
echo 🧹 LIMPIANDO CACHE DE LARAVEL
echo ═══════════════════════════════════════════════════════
echo.
echo Limpiando cache...
php artisan cache:clear
echo   ✓ Cache limpiado
echo.
php artisan config:clear
echo   ✓ Config limpiado
echo.
php artisan route:clear
echo   ✓ Routes limpiado
echo.
php artisan view:clear
echo   ✓ Views limpiado
echo.
echo ═══════════════════════════════════════════════════════
echo ✅ Cache de Laravel limpiado completamente
echo.
pause
goto MENU

:DOCS
cls
echo.
echo 📖 GUÍAS DE MIGRACIÓN DISPONIBLES
echo ═══════════════════════════════════════════════════════
echo.
echo  [1] MIGRACION_RAPIDA.md - Guía rápida (5 minutos)
echo  [2] GUIA_MIGRACION.md - Guía completa detallada
echo  [3] CHECKLIST_MIGRACION.md - Lista de verificación
echo  [4] Volver al menú principal
echo.
echo ═══════════════════════════════════════════════════════
echo.

set /p doc="Elige qué ver (1-4): "

if "%doc%"=="1" (
    cls
    type MIGRACION_RAPIDA.md
    echo.
    pause
    goto DOCS
)
if "%doc%"=="2" (
    cls
    type GUIA_MIGRACION.md | more
    echo.
    pause
    goto DOCS
)
if "%doc%"=="3" (
    cls
    type CHECKLIST_MIGRACION.md | more
    echo.
    pause
    goto DOCS
)
if "%doc%"=="4" goto MENU

echo ❌ Opción no válida
timeout /t 2 > nul
goto DOCS

:POWERSHELL
cls
echo.
echo 💻 ABRIENDO MENÚ INTERACTIVO POWERSHELL
echo ═══════════════════════════════════════════════════════
echo.
echo Iniciando PowerShell...
echo.
powershell -ExecutionPolicy Bypass -File migracion_menu.ps1
goto MENU

:EXIT
cls
echo.
echo ═══════════════════════════════════════════════════════
echo     👋 ¡Hasta luego!
echo ═══════════════════════════════════════════════════════
echo.
timeout /t 2 > nul
exit
