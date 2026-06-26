---
description: "Verifica sintaxis PHP (php -l) en todos los archivos .php modificados o en todo el proyecto."
---

## Diagn&oacute;stico
1. Obtener archivos PHP modificados con `git diff --name-only -- '*.php'` y `git diff --cached --name-only -- '*.php'`
2. Si hay archivos, ejecutar `php -l` en cada uno

## Verificaci&oacute;n global (opcional si no hay cambios)
Recorrer archivos PHP del proyecto:
- `public/` — `Get-ChildItem -Recurse -Filter "*.php" -Path "public" | ForEach-Object { php -l $_.FullName }`
- `admin/` — `Get-ChildItem -Recurse -Filter "*.php" -Path "public/admin" | ForEach-Object { php -l $_.FullName }`
- `api/` — `Get-ChildItem -Recurse -Filter "*.php" -Path "public/api" | ForEach-Object { php -l $_.FullName }`

## Reporte
- Listar errores de sintaxis con archivo y n&uacute;mero de l&iacute;nea
- Si todo OK: mostrar "PHP lint: All files passed"
