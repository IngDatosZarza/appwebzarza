# ========================================
# ZarzaPoints - Scripts de Migración
# PowerShell para Windows
# ========================================

Write-Host "🎯 ZARZAPOINTS - UTILIDADES DE MIGRACIÓN" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

function Show-Menu {
    Write-Host "Selecciona una opción:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "1. 📦 Crear respaldo de base de datos" -ForegroundColor Green
    Write-Host "2. 🔍 Verificar estado del sistema" -ForegroundColor Green
    Write-Host "3. 📁 Empaquetar proyecto para migración" -ForegroundColor Green
    Write-Host "4. 🧪 Probar conexión a BD" -ForegroundColor Green
    Write-Host "5. 🔑 Ver usuarios de prueba" -ForegroundColor Green
    Write-Host "6. 📊 Ver resumen de datos" -ForegroundColor Green
    Write-Host "7. 🌐 Iniciar servidor Laravel" -ForegroundColor Green
    Write-Host "8. 🧹 Limpiar cache de Laravel" -ForegroundColor Green
    Write-Host "0. ❌ Salir" -ForegroundColor Red
    Write-Host ""
}

function Backup-Database {
    Write-Host "`n📦 Creando respaldo de base de datos..." -ForegroundColor Cyan
    php crear_respaldo_bd.php
    Write-Host "`n✅ Respaldo completado. Revisa la carpeta 'respaldos/'" -ForegroundColor Green
    Read-Host "`nPresiona Enter para continuar"
}

function Verify-System {
    Write-Host "`n🔍 Verificando sistema..." -ForegroundColor Cyan
    php verificar_migracion.php
    Read-Host "`nPresiona Enter para continuar"
}

function Package-Project {
    Write-Host "`n📁 Empaquetando proyecto..." -ForegroundColor Cyan
    
    $timestamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
    $zipName = "zarzapoints_$timestamp.zip"
    
    Write-Host "Creando archivo: $zipName" -ForegroundColor Yellow
    
    # Crear carpeta temporal
    $tempDir = "temp_migration"
    if (Test-Path $tempDir) {
        Remove-Item $tempDir -Recurse -Force
    }
    New-Item -ItemType Directory -Path $tempDir | Out-Null
    
    # Copiar archivos esenciales
    Write-Host "Copiando archivos..." -ForegroundColor Yellow
    
    $folders = @("app", "config", "database", "public", "resources", "routes", "storage")
    foreach ($folder in $folders) {
        if (Test-Path $folder) {
            Copy-Item -Path $folder -Destination "$tempDir\$folder" -Recurse -Force
            Write-Host "  ✓ $folder" -ForegroundColor Green
        }
    }
    
    # Copiar archivos raíz importantes
    $files = @(".env", "artisan", "composer.json", "composer.lock", "package.json")
    foreach ($file in $files) {
        if (Test-Path $file) {
            Copy-Item -Path $file -Destination "$tempDir\$file" -Force
            Write-Host "  ✓ $file" -ForegroundColor Green
        }
    }
    
    # Copiar scripts auxiliares
    Get-ChildItem -Filter "*.php" | Where-Object { $_.Name -like "check_*" -or $_.Name -like "fix_*" -or $_.Name -like "crear_*" -or $_.Name -like "verificar_*" } | ForEach-Object {
        Copy-Item -Path $_.FullName -Destination $tempDir -Force
    }
    
    # Copiar documentación
    Get-ChildItem -Filter "*.md" | ForEach-Object {
        Copy-Item -Path $_.FullName -Destination $tempDir -Force
    }
    
    Write-Host "`nCreando archivo ZIP..." -ForegroundColor Yellow
    Compress-Archive -Path "$tempDir\*" -DestinationPath $zipName -Force
    
    Remove-Item $tempDir -Recurse -Force
    
    $size = (Get-Item $zipName).Length / 1MB
    Write-Host "`n✅ Paquete creado exitosamente!" -ForegroundColor Green
    Write-Host "   Archivo: $zipName" -ForegroundColor Cyan
    Write-Host "   Tamaño: $([math]::Round($size, 2)) MB" -ForegroundColor Cyan
    
    Read-Host "`nPresiona Enter para continuar"
}

function Test-DatabaseConnection {
    Write-Host "`n🧪 Probando conexión a base de datos..." -ForegroundColor Cyan
    php check_client_status.php
    Read-Host "`nPresiona Enter para continuar"
}

function Show-TestUsers {
    Write-Host "`n🔑 Usuarios de Prueba" -ForegroundColor Cyan
    Write-Host "==========================================`n" -ForegroundColor Cyan
    
    Write-Host "Cliente:" -ForegroundColor Yellow
    Write-Host "  Email: cliente@test.com" -ForegroundColor White
    Write-Host "  Pass:  password" -ForegroundColor White
    Write-Host "  URL:   http://localhost:8000/login`n" -ForegroundColor Gray
    
    Write-Host "Admin:" -ForegroundColor Yellow
    Write-Host "  Email: admin@test.com" -ForegroundColor White
    Write-Host "  Pass:  password" -ForegroundColor White
    Write-Host "  URL:   http://localhost:8000/admin/points`n" -ForegroundColor Gray
    
    Read-Host "Presiona Enter para continuar"
}

function Show-DataSummary {
    Write-Host "`n📊 Resumen de Datos" -ForegroundColor Cyan
    Write-Host "==========================================`n" -ForegroundColor Cyan
    
    php -r "
    try {
        `$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        `$pdo->exec('SET search_path TO appweb, public');
        
        `$tables = ['usuarios', 'sucursales', 'compras', 'cupones', 'cupones_asignados', 'transacciones_puntos', 'puntos'];
        
        foreach (`$tables as `$table) {
            `$stmt = `$pdo->query(\"SELECT COUNT(*) FROM `$table\");
            `$count = `$stmt->fetchColumn();
            echo str_pad(`$table, 25) . ': ' . `$count . \" registros\n\";
        }
        
        echo \"\\n✅ Base de datos conectada correctamente\\n\";
    } catch (Exception `$e) {
        echo \"❌ Error: \" . `$e->getMessage() . \"\\n\";
    }
    "
    
    Read-Host "`nPresiona Enter para continuar"
}

function Start-LaravelServer {
    Write-Host "`n🌐 Iniciando servidor Laravel..." -ForegroundColor Cyan
    Write-Host "Accede a: http://localhost:8000" -ForegroundColor Yellow
    Write-Host "Presiona Ctrl+C para detener el servidor`n" -ForegroundColor Gray
    
    php artisan serve --host=localhost --port=8000
}

function Clear-LaravelCache {
    Write-Host "`n🧹 Limpiando cache de Laravel..." -ForegroundColor Cyan
    
    php artisan cache:clear
    Write-Host "  ✓ Cache limpiado" -ForegroundColor Green
    
    php artisan config:clear
    Write-Host "  ✓ Config limpiado" -ForegroundColor Green
    
    php artisan route:clear
    Write-Host "  ✓ Routes limpiado" -ForegroundColor Green
    
    php artisan view:clear
    Write-Host "  ✓ Views limpiado" -ForegroundColor Green
    
    Write-Host "`n✅ Cache de Laravel limpiado completamente" -ForegroundColor Green
    Read-Host "`nPresiona Enter para continuar"
}

# Menú principal
do {
    Clear-Host
    Write-Host "🎯 ZARZAPOINTS - UTILIDADES DE MIGRACIÓN" -ForegroundColor Cyan
    Write-Host "========================================`n" -ForegroundColor Cyan
    
    Show-Menu
    
    $choice = Read-Host "Opción"
    
    switch ($choice) {
        '1' { Backup-Database }
        '2' { Verify-System }
        '3' { Package-Project }
        '4' { Test-DatabaseConnection }
        '5' { Show-TestUsers }
        '6' { Show-DataSummary }
        '7' { Start-LaravelServer }
        '8' { Clear-LaravelCache }
        '0' { 
            Write-Host "`n👋 ¡Hasta luego!" -ForegroundColor Cyan
            exit 
        }
        default { 
            Write-Host "`n❌ Opción inválida" -ForegroundColor Red
            Start-Sleep -Seconds 1
        }
    }
} while ($true)
