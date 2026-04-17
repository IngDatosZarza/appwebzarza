# Diagnostico Rapido de Puertos

Write-Host "========================================"
Write-Host "  DIAGNOSTICO DE PUERTOS Y PROCESOS"
Write-Host "========================================"
Write-Host ""

# Verificar puertos
Write-Host "PUERTOS EN USO:"
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
            Write-Host "[OK] Puerto $port ($service) - Proceso: $($process.ProcessName) (PID: $pid)"
        } else {
            Write-Host "[OK] Puerto $port ($service) - PID: $pid"
        }
    } else {
        Write-Host "[NO] Puerto $port ($service) - NO esta en uso"
    }
}

Write-Host ""
Write-Host "========================================"
Write-Host "  PROCESOS RELEVANTES"
Write-Host "========================================"
Write-Host ""

$processes = Get-Process php,postgres,httpd,node -ErrorAction SilentlyContinue

if ($processes) {
    $processes | Select-Object Id, ProcessName, CPU | Format-Table -AutoSize
} else {
    Write-Host "No se encontraron procesos PHP, PostgreSQL, Apache o Node"
}

Write-Host ""
Write-Host "Diagnostico completado"
