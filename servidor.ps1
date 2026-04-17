# Script robusto para iniciar el servidor Laravel
# Verifica configuración y inicia el servidor

param(
    [string]$Host = "172.16.1.44",
    [int]$Port = 8000
)

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   SERVIDOR LARAVEL - SISTEMA PUNTOS FIDELIDAD" -ForegroundColor White
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# 1. Verificar ubicación del proyecto
$ProjectPath = "\\172.16.1.44\htdocs\appwebzarza"
Write-Host "[1/5] Verificando proyecto..." -ForegroundColor Yellow

if (Test-Path $ProjectPath) {
    Write-Host "      ✓ Proyecto encontrado: $ProjectPath" -ForegroundColor Green
    Set-Location $ProjectPath
} else {
    Write-Host "      ✗ Error: No se encuentra el proyecto" -ForegroundColor Red
    exit 1
}

# 2. Verificar PHP
Write-Host "[2/5] Verificando PHP..." -ForegroundColor Yellow
$phpVersion = & php -v 2>&1 | Select-Object -First 1
if ($LASTEXITCODE -eq 0) {
    Write-Host "      ✓ $phpVersion" -ForegroundColor Green
} else {
    Write-Host "      ✗ Error: PHP no encontrado" -ForegroundColor Red
    exit 1
}

# 3. Verificar si el puerto está en uso
Write-Host "[3/5] Verificando puerto $Port..." -ForegroundColor Yellow
$portInUse = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue
if ($portInUse) {
    Write-Host "      ⚠ Advertencia: El puerto $Port ya está en uso" -ForegroundColor Yellow
    Write-Host "      Proceso usando el puerto:" -ForegroundColor Gray
    Get-Process -Id $portInUse.OwningProcess | Select-Object Id, ProcessName, CPU | Format-Table
    
    $continue = Read-Host "¿Deseas continuar de todas formas? (s/n)"
    if ($continue -ne "s") {
        Write-Host "      Operación cancelada" -ForegroundColor Yellow
        exit 0
    }
} else {
    Write-Host "      ✓ Puerto $Port disponible" -ForegroundColor Green
}

# 4. Limpiar caché
Write-Host "[4/5] Limpiando caché de Laravel..." -ForegroundColor Yellow
php artisan cache:clear *>&1 | Out-Null
php artisan config:clear *>&1 | Out-Null
php artisan route:clear *>&1 | Out-Null
php artisan view:clear *>&1 | Out-Null
Write-Host "      ✓ Caché limpiada" -ForegroundColor Green

# 5. Verificar base de datos
Write-Host "[5/5] Verificando conexión a base de datos..." -ForegroundColor Yellow
$dbTest = php -r "
require 'vendor/autoload.php';
`$app = require 'bootstrap/app.php';
`$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    DB::connection()->getPdo();
    echo 'OK';
} catch (Exception `$e) {
    echo 'ERROR: ' . `$e->getMessage();
}
" 2>&1

if ($dbTest -eq "OK") {
    Write-Host "      ✓ Conexión a base de datos exitosa" -ForegroundColor Green
} else {
    Write-Host "      ⚠ Advertencia: $dbTest" -ForegroundColor Yellow
}

# Iniciar servidor
Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   SERVIDOR INICIADO" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "  🌐 URL: " -NoNewline
Write-Host "http://$Host`:$Port" -ForegroundColor Cyan
Write-Host ""
Write-Host "  📁 Directorio: " -NoNewline
Write-Host "$ProjectPath" -ForegroundColor Gray
Write-Host ""
Write-Host "  ⌨️  Presiona Ctrl+C para detener el servidor" -ForegroundColor Yellow
Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Ejecutar servidor
php artisan serve --host=$Host --port=$Port
