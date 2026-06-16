Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Mi Empresa - Servidor Local" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Sitio:  http://localhost:8000" -ForegroundColor Green
Write-Host "  Admin:  http://localhost:8000/admin/login.php" -ForegroundColor Green
Write-Host ""
Write-Host "  Presiona Ctrl+C para detener." -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

& "E:\Hector\php\php.exe" -d upload_max_filesize=5M -d post_max_size=8M -S localhost:8000 -t "$PSScriptRoot\public"

Read-Host "Presiona Enter para cerrar"
