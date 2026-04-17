# Script para iniciar servidor Laravel remotamente usando PSExec
# Requiere PsExec de Sysinternals instalado

param(
    [string]$ServerIP = "172.16.1.44",
    [int]$Port = 8000
)

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   INICIO REMOTO DE SERVIDOR LARAVEL" -ForegroundColor White
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Verificar si PsExec está disponible
$psexec = Get-Command psexec -ErrorAction SilentlyContinue
$psexec64 = Get-Command psexec64 -ErrorAction SilentlyContinue

if (-not $psexec -and -not $psexec64) {
    Write-Host "⚠ PsExec no está instalado" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Para usar esta opción, descarga PsExec de:" -ForegroundColor Gray
    Write-Host "https://learn.microsoft.com/sysinternals/downloads/psexec" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "O usa una de las siguientes alternativas:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "1. Conectarte por RDP al servidor $ServerIP" -ForegroundColor White
    Write-Host "   y ejecutar: cd C:\xampp\htdocs\appwebzarza && php artisan serve --host=$ServerIP --port=$Port" -ForegroundColor Gray
    Write-Host ""
    Write-Host "2. Usar el script .\iniciar_servidor_ssh.ps1 (requiere SSH habilitado)" -ForegroundColor White
    Write-Host ""
    Write-Host "3. Configurar una tarea programada en el servidor" -ForegroundColor White
    Write-Host ""
    pause
    exit 1
}

Write-Host "Iniciando servidor Laravel en $ServerIP`:$Port..." -ForegroundColor Yellow
Write-Host ""

# Intentar con PsExec
$psexecCmd = if ($psexec64) { "psexec64" } else { "psexec" }

& $psexecCmd "\\$ServerIP" -i -d cmd /c "cd /d C:\xampp\htdocs\appwebzarza && php artisan serve --host=$ServerIP --port=$Port"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Servidor iniciado exitosamente" -ForegroundColor Green
    Write-Host ""
    Write-Host "URL: http://$ServerIP`:$Port" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "✗ Error al iniciar el servidor" -ForegroundColor Red
    Write-Host ""
}

pause
