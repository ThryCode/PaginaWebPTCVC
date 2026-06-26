---
description: "Verificaci&oacute;n pre-despliegue: rutas absolutas, .htaccess, datos protegidos, sin console.log, sin funciones inseguras, errores PHP off."
---

## Lista de verificaci&oacute;n pre-despliegue

### 1. Rutas absolutas
Buscar rutas relativas incorrectas en archivos:
- `src="uploads/` o `src="../uploads` en PHP/HTML
- `href="css/` o `href="../css` en PHP/HTML
- `'api/` en JS (debe ser `'/api/`)
- `url(uploads/` o `url(../` en CSS
Usar `Select-String` para buscar patrones prohibidos.

### 2. Seguridad
- Verificar `error_reporting(0)` en `public/api/config.php`
- Verificar `.htaccess` en `public/data/` tiene `Require all denied`
- Buscar funciones inseguras: `exec(`, `system(`, `eval(`, `assert(`, `passthru(` en archivos PHP
- Buscar `console.log(` en archivos JS

### 3. Headers de seguridad
Verificar en `public/.htaccess` o archivos PHP:
- `X-Frame-Options`
- `X-Content-Type-Options`
- `Referrer-Policy`
- `Permissions-Policy`

### 4. Archivos a excluir del despliegue
Verificar que NO se subir&aacute;n:
- `.git/`, `tests/`, `tools/`, `phpunit.phar`, `composer.lock`, `PaginaWeb.zip`

### 5. Resumen final
```
## Deploy Check Report
- Rutas absolutas: {PASS/FAIL — n violaciones}
- Funciones inseguras: {PASS/FAIL}
- console.log: {PASS/FAIL}
- error_reporting(0): {PASS/FAIL}
- .htaccess data protegido: {PASS/FAIL}
- Headers seguridad: {PASS/FAIL — n faltantes}
- Archivos a excluir: {PASS/FAIL}

### Acciones requeridas antes del deploy
{lista}
```
