---
description: "Inicia el servidor local de desarrollo en puerto 8000 usando iniciar.ps1 o comando PHP directo."
---

## Iniciar servidor local
Ejecutar:
```powershell
.\iniciar.ps1
```
O directamente:
```powershell
php -d upload_max_filesize=5M -d post_max_size=8M -S localhost:8000 -t public
```

## Verificar
- Mostrar URL: `http://localhost:8000`
- Confirmar que el servidor inici&oacute; sin errores
- Si el puerto 8000 est&aacute; ocupado, usar `Get-NetTCPConnection -State Listen` para encontrar puerto activo
