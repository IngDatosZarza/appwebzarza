# Diagnóstico Rápido de Puertos

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  DIAGNÓSTICO DE PUERTOS Y PROCESOS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar puertos
Write-Host "PUERTOS EN USO:" -ForegroundColor Yellow
Write-Host ""

$ports = @{
    "8000" = "Laravel"
    "5432" = "PostgreSQL"
    "5173" = "Vite"
    "5174" = "Vite (alternativo)"
    "80" = "Apache/XAMPP"
    "3306" = "MySQL/XAMPP"
}

foreach ($port in $ports.Keys | Sort-Object) {
    $service = $ports[$port]
    $result = netstat -ano | Select-String ":$port\s.*LISTENING"
    
    if ($result) {
        $pid = ($result -split '\s+' | Where-Object { $_ -ne '' })[-1]
        $process = Get-Process -Id $pid -ErrorAction SilentlyContinue
        if ($process) {
            Write-Host "✅ Puerto $port ($service)" -ForegroundColor Green -NoNewline
            Write-Host " - Proceso: $($process.ProcessName) (PID: $pid)" -ForegroundColor White
        } else {
            Write-Host "✅ Puerto $port ($service)" -ForegroundColor Green -NoNewline
            Write-Host " - PID: $pid" -ForegroundColor White
        }
    } else {
        Write-Host "❌ Puerto $port ($service) - NO está en uso" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  PROCESOS RELEVANTES" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$processes = Get-Process php,postgres,httpd,node -ErrorAction SilentlyContinue

if ($processes) {
    $processes | Select-Object Id, ProcessName, CPU, @{Name='MemoryMB';Expression={[math]::Round($_.WorkingSet64/1MB,2)}} | Format-Table -AutoSize
} else {
    Write-Host "No se encontraron procesos PHP, PostgreSQL, Apache o Node" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Diagnostico completado" -ForegroundColor Green
