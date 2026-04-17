# Script para iniciar el servidor Laravel en el servidor remoto
# Ejecuta el servidor en 172.16.1.44:8000

Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   INICIANDO SERVIDOR LARAVEL" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "Servidor: http://172.16.1.44:8000" -ForegroundColor Yellow
Write-Host "Directorio: \\172.16.1.44\htdocs\appwebzarza" -ForegroundColor Gray
Write-Host ""
Write-Host "Presiona Ctrl+C para detener el servidor" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Cambiar al directorio del proyecto
Set-Location "\\172.16.1.44\htdocs\appwebzarza"

# Limpiar caché antes de iniciar
Write-Host "Limpiando caché..." -ForegroundColor Cyan
php artisan cache:clear 2>&1 | Out-Null
php artisan config:clear 2>&1 | Out-Null
php artisan route:clear 2>&1 | Out-Null

Write-Host "✓ Caché limpiada" -ForegroundColor Green
Write-Host ""

# Iniciar servidor
Write-Host "Iniciando servidor Laravel..." -ForegroundColor Cyan
php artisan serve --host=172.16.1.44 --port=8000
