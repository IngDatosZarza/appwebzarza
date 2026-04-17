# Script para iniciar servidor Laravel usando SSH
# Requiere OpenSSH Client instalado en Windows

param(
    [string]$ServerIP = "172.16.1.44",
    [int]$Port = 8000,
    [string]$Username = "Administrator"
)

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   INICIO REMOTO VIA SSH - SERVIDOR LARAVEL" -ForegroundColor White
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Verificar si SSH está disponible
$ssh = Get-Command ssh -ErrorAction SilentlyContinue

if (-not $ssh) {
    Write-Host "✗ SSH no está instalado" -ForegroundColor Red
    Write-Host ""
    Write-Host "Para instalar OpenSSH Client en Windows:" -ForegroundColor Yellow
    Write-Host "1. Abrir 'Configuración' > 'Aplicaciones' > 'Características opcionales'" -ForegroundColor Gray
    Write-Host "2. Hacer clic en 'Agregar una característica'" -ForegroundColor Gray
    Write-Host "3. Buscar 'Cliente OpenSSH' e instalar" -ForegroundColor Gray
    Write-Host ""
    Write-Host "O ejecutar en PowerShell como Administrador:" -ForegroundColor Yellow
    Write-Host "Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0" -ForegroundColor Cyan
    Write-Host ""
    pause
    exit 1
}

Write-Host "Conectando a $ServerIP como $Username..." -ForegroundColor Yellow
Write-Host ""

# Comando para ejecutar en el servidor remoto
$remoteCommand = @"
cd C:\xampp\htdocs\appwebzarza && php artisan serve --host=$ServerIP --port=$Port
"@

# Ejecutar comando SSH
ssh $Username@$ServerIP $remoteCommand

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Comando ejecutado" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "⚠ Verifica que:" -ForegroundColor Yellow
    Write-Host "  1. SSH Server esté habilitado en $ServerIP" -ForegroundColor Gray
    Write-Host "  2. Las credenciales sean correctas" -ForegroundColor Gray
    Write-Host "  3. El firewall permita conexiones SSH (puerto 22)" -ForegroundColor Gray
    Write-Host ""
}

pause
