# AGENTS.md ΓÇö PCTVC Website

## Project Overview
Sitio web del Parque Cient├¡fico Tecnol├│gico de Villa Clara. CMS propio con almacenamiento en JSON (sin base de datos). Panel admin para gestionar todo el contenido del sitio.

## Tech Stack
| Tecnolog├¡a | Versi├│n |
|-----------|---------|
| PHP | 7.3.11+ |
| Apache | 2.4.6 |
| MariaDB | No usado (JSON) |
| PHPMailer | ^7.1 (formulario de contacto) |
| JS | Vanilla (sin framework) |
| CSS | Plano (sin preprocesador) |
| NodeJS | No usado |

## Project Structure
```
PaginaWeb/
Γö£ΓöÇΓöÇ public/                    # Document root
Γöé   Γö£ΓöÇΓöÇ admin/                # Panel de administraci├│n
Γöé   Γöé   Γö£ΓöÇΓöÇ css/admin.css
Γöé   Γöé   Γö£ΓöÇΓöÇ js/admin.js
Γöé   Γöé   Γö£ΓöÇΓöÇ includes/sidebar.php
Γöé   Γöé   Γö£ΓöÇΓöÇ login.php
Γöé   Γöé   ΓööΓöÇΓöÇ *.php             # CRUDs del admin
Γöé   Γö£ΓöÇΓöÇ api/                  # Endpoints AJAX (REST interno)
Γöé   Γöé   Γö£ΓöÇΓöÇ config.php
Γöé   Γöé   Γö£ΓöÇΓöÇ storage.php
Γöé   Γöé   Γö£ΓöÇΓöÇ auth.php
Γöé   Γöé   Γö£ΓöÇΓöÇ contact.php
Γöé   Γöé   ΓööΓöÇΓöÇ *.php
Γöé   Γö£ΓöÇΓöÇ assets/               # Im├ígenes est├íticas (logo, colaboradores, junta)
Γöé   Γö£ΓöÇΓöÇ css/style.css
Γöé   Γö£ΓöÇΓöÇ data/                 # Almacenamiento JSON (NO subir a git con datos)
Γöé   Γö£ΓöÇΓöÇ includes/             # header.php, footer.php
Γöé   Γö£ΓöÇΓöÇ js/main.js            # Frontend JS
Γöé   Γö£ΓöÇΓöÇ uploads/              # Im├ígenes subidas por usuarios
Γöé   ΓööΓöÇΓöÇ *.php                 # P├íginas p├║blicas
Γö£ΓöÇΓöÇ data/                     # Data inicial (setup)
Γö£ΓöÇΓöÇ lib/                      # PHPMailer
Γö£ΓöÇΓöÇ tests/                    # PHPUnit tests
Γö£ΓöÇΓöÇ iniciar.ps1              # Script de arranque local
Γö£ΓöÇΓöÇ setup.php                # Instalaci├│n inicial
Γö£ΓöÇΓöÇ composer.json
ΓööΓöÇΓöÇ AGENTS.md
```

## Commands

### Local Development
```powershell
# Iniciar servidor local (puerto fijo 8000)
.\iniciar.ps1

# O iniciar manualmente
php -d upload_max_filesize=5M -d post_max_size=8M -S localhost:8000 -t public

# Encontrar puerto activo si cambi├│ (VPN, etc.)
$port = (Get-NetTCPConnection -State Listen | Where-Object { $_.OwningProcess -in (Get-Process php).Id } | Select-Object -First 1).LocalPort
Write-Host "http://localhost:$port"
```

### Testing
```bash
cd PaginaWeb
php vendor/bin/phpunit tests/
```

## Path Convention ΓÇö CR├ìTICO
TODAS las rutas de recursos usan prefijo absoluto `/`:
- `<img src="/uploads/archivo.jpg">`
- `<link href="/css/style.css">`
- `<script src="/js/main.js">`
- `<link href="/admin/css/admin.css">`
- `<script src="/admin/js/admin.js">`
- API calls en JS: `'/api/news.php'`
- CSS `url()`: `url('/assets/img/sliders/slider-01.jpg')`

Las rutas en JSON se almacenan LIMPIAS (sin `/` ni `../`), el prefijo se a├▒ade al renderizar:
- JSON guarda: `uploads/opinion_123.jpg`
- HTML renderiza: `src="/<?php echo $path; ?>"`

NUNCA usar:
- `src="uploads/..."` (relativo, se rompe desde subdirectorios)
- `src="../uploads/..."` (relativo, se rompe si cambia profundidad)
- `'api/news.php'` en JS (relativo, se rompe desde admin)

Links de navegaci├│n entre p├íginas S├ì pueden ser relativos:
- `href="quienes-somos.php"` (correcto para navegaci├│n entre p├íginas)

## Coding Style
- `htmlspecialchars()` en TODA salida de datos del usuario o din├ímicos
- CSRF token en todos los formularios admin (ya implementado v├¡a `csrfField()`)
- Las rutas en JSON se almacenan SIN prefijo (`uploads/img.jpg`), el `/` se a├▒ade al renderizar
- No usar frameworks PHP ni JS
- No usar base de datos SQL ΓÇö almacenamiento JSON plano
- No usar `console.log` en producci├│n
- `error_reporting(0)` en producci├│n, `E_ALL` en desarrollo
- C├│digo sin comentarios (autodocumentado con nombres descriptivos)

## Formulario de Contacto
- Usa PHPMailer (`lib/`) para enviar correos
- En local: muestra mensaje de ├⌐xito sin enviar realmente (config.php detecta localhost)
- En producci├│n: requiere configuraci├│n SMTP
- Anti-spam implementado via honeypot + timestamp
- L├¡mite: 5 env├¡os por hora por IP

## InfinityFree Deployment
InfinityFree usa `htdocs/` como document root.

### Opci├│n A ΓÇö Renombrar carpeta
1. Renombrar `public/` ΓåÆ `htdocs/`
2. Subir el contenido de `PaginaWeb/` al FTP (todo lo que est├í dentro, NO la carpeta `PaginaWeb/` en s├¡)
3. `htdocs/` debe quedar en la ra├¡z del sitio FTP

### Opci├│n B ΓÇö Mover contenido
1. Copiar TODO el contenido de `public/` a `htdocs/`
2. Copiar `lib/`, `vendor/`, `data/` al mismo nivel que `htdocs/`
3. Asegurar que `PaginaWeb/` NO se suba como carpeta extra

### Archivos que NO subir
- `.git/`
- `tests/`, `tools/`
- `phpunit.phar`, `composer.lock`
- `.phpunit.result.cache`

### Ventaja: rutas absolutas
Tras la migraci├│n a InfinityFree TODAS las rutas siguen funcionando porque empiezan con `/`:
- `/uploads/...` ΓåÆ `htdocs/uploads/...`
- `/css/style.css` ΓåÆ `htdocs/css/style.css`
- `/admin/css/admin.css` ΓåÆ `htdocs/admin/css/admin.css`
- `/api/...` ΓåÆ `htdocs/api/...`

### Cache busting autom├ítico
InfinityFree usa CloudFlare CDN que cachea CSS, JS e IM├üGENES indefinidamente (TTL 30 d├¡as desde `.htaccess` ra├¡z). Si un archivo cambia pero CloudFlare sirve la versi├│n vieja, hay dos mecanismos de cache busting:

#### Para CSS/JS est├íticos (header.php/footer.php)
- **NUNCA** usar links sin versi├│n: `<link href="css/style.css">`
- **SIEMPRE** usar `filemtime()`: `<link href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">`
- Esto aplica a: style.css, main.js, admin.css, admin.js

#### Para im├ígenes cargadas din├ímicamente (v├¡a API o PHP server-side)
CloudFlare cachea la URL completa incluyendo la ruta. Si una imagen se subi├│, se cache├│ un 404/error, y luego se reemplaz├│, CloudFlare sigue sirviendo el error hasta purgar manualmente (30 d├¡as de TTL).

**Soluci├│n:** a├▒adir `?v=` con `filemtime()` a cada ruta de imagen en el momento de servirla:

```php
function _cacheBust($path) {
    $abs = __DIR__ . '/../' . $path;
    $v = file_exists($abs) ? filemtime($abs) : time();
    return $path . '?v=' . $v;
}
```

Esto aplica a:
- `api/gallery.php` → c/images en `$item['imagen']`
- `api/opiniones.php` → c/imagen en cada opini├│n
- `api/news.php` y `api/events.php` → c/images en `$item['imagen']` e `$item['imagenes'][]`
- `index.php` → c/slider (server-side)
- `flyers.php` → c/flyer (server-side)
- `noticia.php` → c/images en detalle de noticia (server-side)

El `?v=` cambia autom├íticamente cuando el archivo se sube/edit├│ → CloudFlare trata cada valor como URL distinta → sirve la versi├│n fresca.

#### Diagn├│stico de carga de im├ígenes
Si una imagen no se ve en InfinityFree pero s├¡ en localhost:
1. Acceder a `https://pctvc.cu/diagnostico.php` → Secci├│n 8
2. Verificar que HTTP devuelva 200 (no 404/403/500)
3. Si HTTP 200 pero no se ve en navegador → CloudFlare cache├│ respuesta anterior. Soluci├│n: forzar recarga (Ctrl+F5) o esperar que el `?v=` nuevo invalide la cach├®.
4. Si HTTP 0 o 500 → el `.htaccess` de `uploads/` puede tener sintaxis incompatible (usa `Require all granted` que es Apache 2.4; LiteSpeed de InfinityFree puede fallar).

#### ╚íNo requiere purgar manualmente!
Si el `?v=` est├í presente con `filemtime()`, cualquier cambio en el archivo cambia el `?v=` → CloudFlare lo ve como URL nueva → sirve el contenido fresco autom├íticamente. Solo en el caso extremo de que una URL sin `?v=` se haya cacheado con error, hay dos opciones:
- A├▒adir el `?v=` (lo implementado arriba)
- Activar "Development Mode" en CloudFlare (dura 3 horas) desde el panel de InfinityFree

## ETECSA Hosting
Servidor: Apache 2.4.6 + PHP 7.3.11+ sobre UNIX/Linux.

### Modo seguro PHP
Funciones deshabilitadas (no usar): `exec`, `system`, `passthru`, `shell_exec`, `popen`, `proc_open`, `eval`, `assert`, `create_function`, `include` con path remoto.

### ModSecurity + SELinux
- URLs "sucias" pueden ser bloqueadas por ModSecurity
- Si un formulario o acci├│n es bloqueada sin raz├│n aparente, revisar el patr├│n de la URL
- SELinux en modo enforcing: permisos de archivos deben ser correctos

### Email (PHPMailer)
- Solo SMTP relay desde localhost del servidor
- From debe ser cuenta bajo dominio ETECSA (enet.cu, nauta.cu)
- Si se usa otro from, configurar registro SPF y notificar a hosting@enet.cu
- El relay localhost no requiere autenticaci├│n SMTP, solo specify `Host = 'localhost'`

### HTTPS y SSL
- Certificado autofirmado disponible solo para directorios de admin
- Para HTTPS completo: obtener certificado SSL de entidad certificadora internacional
- Subir certificado por FTP al directorio `pki/` (fuera de `htdocs/`)
- Redirecci├│n 301 de HTTP a HTTPS obligatoria
- Protocolo TLS v1.2/v1.3 solamente (no SSLv3, no TLSv1.0/1.1)
- HSTS habilitado con `max-age` m├¡nimo 10886400 (18 semanas)
- Cookie Secure habilitada en todas las cookies
- Cipher suites permitidas:
  - `ECDHE-RSA-AES256-GCM-SHA384`
  - `DHE-RSA-AES256-GCM-SHA384`
  - `ECDHE-RSA-AES128-GCM-SHA256`
  - `DHE-RSA-AES128-GCM-SHA256`
- Habilitar Forward Secrecy, OCSP Stapling, deshabilitar renegociaci├│n TLS
- Content-Security-Policy pendiente de implementar

### Anti-spam
- Captcha o honeypot obligatorio en formularios p├║blicos
- Ya implementado: honeypot por timestamp en formulario de contacto

### NodeJS (si se requiere en el futuro)
- No se usa actualmente. Si se a├▒ade:
  - Una sola aplicaci├│n por sitio, en `htdocs/node/<app_dir>/`
  - Puerto TCP ├║nico asignado por ETECSA
  - ProxyPass /node/ apuntando a localhost:puerto
  - Informar fichero inicial de carga al soporte t├⌐cnico

### FTP
- FTP Seguro (FTPES) con TLS/SSL expl├¡cito
- Cliente recomendado: FileZilla con protocolo FTPES
- Solo accesible desde redes nacionales cubanas
- Para actualizaciones desde redes internacionales: usar HTTPS

### Contrase├▒as
- FTP y BD: 8-15 caracteres, may├║sculas, min├║sculas, n├║meros, s├¡mbolos
- Caducan a los 300 d├¡as
- Cambio v├¡a portal de utilidades del servicio
- Soporte: hosting@enet.cu (incluir nombre del sitio, contrase├▒a de contacto, datos del titular)

## Storage System
Almacenamiento plano en JSON (`public/data/`). Cada recurso es un array de objetos en un archivo JSON:
- `sliders.json` ΓÇö Sliders del carrusel de inicio
- `opiniones.json` ΓÇö Testimonios
- `galeria.json` ΓÇö Galer├¡a de im├ígenes
- `noticias.json` ΓÇö Noticias y eventos
- `flyers.json` ΓÇö Flyers promocionales
- `proyectos.json` ΓÇö Proyectos
- `servicios.json` ΓÇö Servicios y subservicios
- `contadores.json` ΓÇö Contadores de estad├¡sticas
- `mensajes.json` ΓÇö Mensajes del formulario de contacto
- `usuarios.json` ΓÇö Usuarios del panel admin

API de acceso v├¡a `Storage::read('nombre')` y `Storage::write('nombre', $data)` en `api/storage.php`.

NO es SQL ΓÇö no se pueden hacer JOINs, consultas complejas ni transacciones.

## Admin Panel
- URL: `/admin/login.php`
- Roles: `admin` (acceso total), `editor` (contenido limitado)
- Dashboard: `/admin/index.php`
- Gesti├│n de inicio (portada, sliders, opiniones): `/admin/inicio.php`
- Gesti├│n completa de contenido: `/admin/informacion.php`

## Boundaries
### Γ£à Always
- Usar rutas absolutas `/` para recursos (img, css, js, api)
- Escapar todo output din├ímico con `htmlspecialchars()`
- Validar CSRF token en acciones del admin
- Ejecutar `php vendor/bin/phpunit tests/` antes de commits significativos
- C├│digo autodocumentado sin comentarios irrelevantes

### ΓÜá∩╕Å Ask First
- **Hacer commit o push** - siempre preguntar antes de commitear y pushear
- Cambiar la estructura de almacenamiento (JSON ΓåÆ SQL)
- A├▒adir dependencias npm/composer nuevas
- Modificar el sistema de rutas (absolutas ΓåÆ relativas)
- Refactorizar archivos existentes sin necesidad real
- Mover `public/` a `htdocs/` en el repositorio

### ≡ƒÜ½ Never
- Usar `../` en rutas de recursos HTML/CSS/JS/API
- Almacenar contrase├▒as en texto plano
- Exponer errores PHP en producci├│n (`display_errors=Off`)
- Usar funciones PHP inseguras: `exec`, `system`, `eval`, `assert`, `passthru`
- Modificar `public/data/` en producci├│n sin respaldo
- Commitear secretos, credenciales reales, o datos del sitio
- Usar `console.log()` en c├│digo JS de producci├│n
