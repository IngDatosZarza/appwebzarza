# Script de Corrección de Acentos
# Ejecutar este script para corregir los caracteres corruptos en la base de datos

Write-Host "=== Corrección de Acentos en Base de Datos ===" -ForegroundColor Cyan
Write-Host ""

# Verificar si estamos en el directorio correcto
if (!(Test-Path "artisan")) {
    Write-Host "ERROR: Este script debe ejecutarse desde el directorio raíz del proyecto Laravel" -ForegroundColor Red
    exit 1
}

Write-Host "1. Diagnosticando base de datos..." -ForegroundColor Yellow
php artisan db:diagnosticar-encoding

Write-Host ""
Write-Host "2. ¿Desea ejecutar la corrección? (S/N)" -ForegroundColor Yellow
$respuesta = Read-Host

if ($respuesta -eq "S" -or $respuesta -eq "s" -or $respuesta -eq "SI" -or $respuesta -eq "si") {
    Write-Host ""
    Write-Host "3. Ejecutando corrección..." -ForegroundColor Green
    Write-Host "   Esto puede tomar algunos minutos..." -ForegroundColor Gray
    Write-Host ""
    
    php corregir_encoding_rapido.php
    
    Write-Host ""
    Write-Host "4. Verificando resultados..." -ForegroundColor Yellow
    php artisan db:diagnosticar-encoding
    
    Write-Host ""
    Write-Host "5. Limpiando caché..." -ForegroundColor Yellow
    php artisan config:clear
    php artisan cache:clear
    
    Write-Host ""
    Write-Host "=== CORRECCIÓN COMPLETADA ===" -ForegroundColor Green
    Write-Host ""
    Write-Host "Ahora puedes probar el formulario de registro:" -ForegroundColor Cyan
    Write-Host "http://localhost:8000/register" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "Corrección cancelada" -ForegroundColor Yellow
    Write-Host ""
}
