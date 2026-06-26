---
description: "Inicializa o reinicia los datos del proyecto: ejecuta setup.php web o setup-admin.php CLI seg&uacute;n corresponda."
---

## Setup del proyecto

### Opci&oacute;n A — Web (para InfinityFree / producci&oacute;n)
- Navegar a `http://localhost:8000/setup.php`
- Sigue las instrucciones en pantalla

### Opci&oacute;n B — CLI (admin auth)
- Ejecutar:
```powershell
php tools/setup-admin.php
```
- Genera el `admin_auth.json` inicial con PAC
- Sigue las instrucciones del script

### Verificaci&oacute;n post-setup
- Confirmar que `public/data/` contiene los JSON iniciales:
  - `admin_auth.json`, `config.json`, `sliders.json`, `noticias.json`, etc.
- Verificar que `public/uploads/` tiene los subdirectorios:
  - `eventos/`, `flyers/`, `galeria/`, `noticias/`, `opiniones/`, `proyectos/`, `sliders/`
- Probar login en `http://localhost:8000/admin/login.php`

### Reinicio completo (solo desarrollo)
Si se necesita resetear:
1. Borrar `public/data/*.json`
2. Ejecutar `php setup.php` o el CLI
