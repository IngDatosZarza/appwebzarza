@echo off
echo ======================================
echo LOGS DEL REGISTRO
echo ======================================
echo.

if exist storage\logs\laravel.log (
    type storage\logs\laravel.log
    echo.
    echo ======================================
    echo Fin de logs
    echo ======================================
) else (
    echo No hay archivo de log
)

pause
