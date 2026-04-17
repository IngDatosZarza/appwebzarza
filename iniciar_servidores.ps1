# Script para iniciar los servidores de desarrollo

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  INICIANDO SERVIDORES DE DESARROLLO" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Cambiar al directorio correcto
Set-Location "Z:\appwebzarza"

# Verificar PostgreSQL
Write-Host "[1/4] Verificando PostgreSQL..." -ForegroundColor Yellow
$pgProcess = netstat -ano | Select-String ":5432.*LISTENING"
if ($pgProcess) {
    Write-Host "✅ PostgreSQL está corriendo" -ForegroundColor Green
} else {
    Write-Host "❌ PostgreSQL NO está corriendo" -ForegroundColor Red
    Write-Host "   Por favor, inicia PostgreSQL antes de continuar" -ForegroundColor Red
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host ""

# Limpiar puertos ocupados
Write-Host "[2/4] Limpiando puertos..." -ForegroundColor Yellow
$port8000 = netstat -ano | Select-String ":8000.*LISTENING"
if ($port8000) {
    $pid = ($port8000 -split '\s+')[-1]
    Write-Host "   Deteniendo proceso en puerto 8000 (PID: $pid)" -ForegroundColor Yellow
    Stop-Process -Id $pid -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 1
}
Write-Host "✅ Puertos liberados" -ForegroundColor Green

Write-Host ""

# Limpiar caché de Laravel
Write-Host "[3/4] Limpiando caché de Laravel..." -ForegroundColor Yellow
php artisan config:clear | Out-Null
php artisan cache:clear | Out-Null
Write-Host "✅ Caché limpiado" -ForegroundColor Green

Write-Host ""

# Iniciar servidores
Write-Host "[4/4] Iniciando servidores..." -ForegroundColor Yellow
Write-Host ""

Write-Host "🚀 Iniciando Laravel en http://localhost:8000" -ForegroundColor Cyan  
Write-Host "🚀 Iniciando Vite en http://localhost:5173" -ForegroundColor Cyan
Write-Host ""

# Abrir dos ventanas de PowerShell
Start-Process powershell -ArgumentList @(
    '-NoExit'
    '-Command'
    "Set-Location Z:\appwebzarza; Write-Host '=== SERVIDOR LARAVEL ===' -ForegroundColor Green; php artisan serve --host=127.0.0.1 --port=8000"
)

Start-Sleep -Seconds 2

Start-Process powershell -ArgumentList @(
    '-NoExit'
    '-Command'
    "Set-Location Z:\appwebzarza; Write-Host '=== SERVIDOR VITE ===' -ForegroundColor Blue; npm run dev"
)

Start-Sleep -Seconds 3

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  SERVIDORES INICIADOS" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "📌 Laravel: http://localhost:8000" -ForegroundColor Cyan
Write-Host "📌 Vite:    http://localhost:5173" -ForegroundColor Cyan
Write-Host ""
Write-Host "Para iniciar sesión:" -ForegroundColor Yellow
Write-Host "   Admin: admin@test.com / password" -ForegroundColor White
Write-Host "   Cliente: cliente@test.com / password" -ForegroundColor White
Write-Host ""

Read-Host "Presiona Enter para salir"
