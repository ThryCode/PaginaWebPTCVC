<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Diagn&oacute;stico PCTVC</title>
<style>
body { font-family: monospace; max-width: 1000px; margin: 20px auto; padding: 0 20px; background: #fafafa; color: #222; line-height:1.5; }
h1 { color: #004966; border-bottom: 3px solid #004966; padding-bottom: 8px; }
h2 { color: #004966; }
.pass { background:#dcfce7; color:#166534; padding:2px 10px; border-radius:12px; font-weight:bold; display:inline-block; font-size:12px; }
.fail { background:#fee2e2; color:#991b1b; padding:2px 10px; border-radius:12px; font-weight:bold; display:inline-block; font-size:12px; }
.warn { background:#fef3c7; color:#92400e; padding:2px 10px; border-radius:12px; font-weight:bold; display:inline-block; font-size:12px; }
.info { background:#e0f2fe; color:#075985; padding:2px 10px; border-radius:12px; font-weight:bold; display:inline-block; font-size:12px; }
pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow: auto; font-size: 13px; }
table { border-collapse: collapse; width: 100%; font-size: 14px; margin:8px 0; }
th, td { text-align: left; padding: 6px 10px; border-bottom: 1px solid #ddd; }
th { background: #004966; color: #fff; font-weight: 600; }
tr:hover { background: #f0f0f0; }
.section { margin: 20px 0; border: 1px solid #d0d0d0; padding: 16px 20px; border-radius: 8px; background: #fff; }
.section h2 { margin-top: 0; }
code { background: #eee; padding: 2px 5px; border-radius: 3px; font-size: 13px; }
.small { font-size: 12px; color: #666; }
#poppins-test { font-family: 'Poppins', 'Segoe UI', Tahoma, sans-serif; padding: 14px; border: 2px solid #ccc; border-radius: 6px; margin: 10px 0; font-size: 16px; }
.diag-row { display:flex; justify-content:space-between; align-items:center; padding:5px 8px; border-bottom:1px solid #eee; }
.diag-label { font-weight:600; }
.diag-value { font-family:monospace; }
.diag-summary { font-size:13px; margin-top:8px; padding:8px; border-radius:4px; }
#diag-table-body { border:1px solid #ddd; border-radius:6px; overflow:hidden; }
#diag-progress { padding:8px; color:#666; font-style:italic; }
.load-error { cursor:pointer; }
.error-accordion { background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:10px; margin:4px 0; font-size:13px; }
.error-accordion summary { cursor:pointer; color:#991b1b; font-weight:600; }
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>" id="main-css">
</head>
<body>

<h1>Diagn&oacute;stico de Despliegue &mdash; PCTVC</h1>
<p class="small">Generado: <?php echo date('Y-m-d H:i:s'); ?> | IP: <?php echo $_SERVER['SERVER_ADDR'] ?? 'N/A'; ?></p>

<div class="section">
<h2>1. Informaci&oacute;n del Servidor</h2>
<table>
<tr><th>Propiedad</th><th>Valor</th></tr>
<tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
<tr><td>Sistema Operativo</td><td><?php echo PHP_OS; ?></td></tr>
<tr><td>Servidor Web</td><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td></tr>
<tr><td>Document Root</td><td><code><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A'); ?></code></td></tr>
<tr><td>Directorio del script</td><td><code><?php echo htmlspecialchars(__DIR__); ?></code></td></tr>
<tr><td>HTTP Host</td><td><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A'); ?></td></tr>
<tr><td>Protocolo</td><td><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'HTTPS' : 'HTTP'; ?></td></tr>
</table>
</div>

<div class="section">
<h2>2. Archivos Cr&iacute;ticos</h2>
<table>
<tr><th>Archivo</th><th>Ruta absoluta</th><th>Estado</th></tr>
<?php
function checkFile($label, $path) {
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);
    $size = $exists ? filesize($path) : 0;
    if (!$exists) {
        $icon = '❌';
        $status = 'NO EXISTE';
    } elseif (!$readable) {
        $icon = '⚠️';
        $status = 'No legible';
    } else {
        $icon = '✅';
        $status = 'OK (' . number_format($size) . ' bytes)';
    }
    $cls = $exists && $readable ? 'pass' : 'fail';
    echo '<tr><td>' . htmlspecialchars($label) . '</td><td><code>' . htmlspecialchars($path) . '</code></td><td><span class="' . $cls . '">' . $icon . ' ' . $status . '</span></td></tr>';
}

$base = __DIR__;
checkFile('css/style.css', $base . '/css/style.css');
checkFile('js/main.js', $base . '/js/main.js');
checkFile('api/storage.php', $base . '/api/storage.php');
checkFile('api/config.php', $base . '/api/config.php');

$dataFiles = ['proyectos','noticias','servicios','sliders','opiniones','counters','mensajes','flyers','galeria','usuarios'];
foreach ($dataFiles as $f) {
    checkFile("data/$f.json", $base . "/data/$f.json");
}

checkFile('.htaccess (raíz)', $base . '/.htaccess');
checkFile('data/.htaccess', $base . '/data/.htaccess');
checkFile('uploads/.htaccess', $base . '/uploads/.htaccess');
?>
</table>
</div>

<div class="section">
<h2>3. Resoluci&oacute;n de DATA_DIR</h2>
<?php
require_once 'api/config.php';
echo '<p>DATA_DIR: <code>' . DATA_DIR . '</code></p>';
echo '<p>DATA_DIR existe: ' . (file_exists(DATA_DIR) ? '✅ S&iacute;' : '❌ No') . '</p>';
echo '<p>DATA_DIR escribible: ' . (is_writable(DATA_DIR) ? '✅ S&iacute;' : '❌ No') . '</p>';

echo '<h3>Contenido de cada JSON</h3>';
echo '<table><tr><th>Colecci&oacute;n</th><th>Existe</th><th>Tama&ntilde;o</th><th>Items</th><th>JSON v&aacute;lido</th></tr>';
foreach ($dataFiles as $col) {
    $file = DATA_DIR . '/' . $col . '.json';
    $exists = file_exists($file);
    $size = $exists ? filesize($file) : 0;
    $valid = false;
    $count = 0;
    if ($exists && $size > 0) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        $valid = is_array($data);
        $count = $valid ? count($data) : 0;
    }
    echo '<tr>';
    echo '<td>' . htmlspecialchars($col) . '</td>';
    echo '<td>' . ($exists ? '✅' : '❌') . '</td>';
    echo '<td>' . number_format($size) . ' bytes</td>';
    echo '<td>' . $count . '</td>';
    echo '<td>' . ($valid ? '✅' : ($exists ? '⚠️ Inv&aacute;lido' : '—')) . '</td>';
    echo '</tr>';
}
echo '</table>';
?>
</div>

<div class="section">
<h2>4. Conexi&oacute;n a Recursos Externos</h2>
<?php
function testUrl($label, $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'method' => 'GET', 'follow_location' => true, 'ignore_errors' => true]]);
    $start = microtime(true);
    $headers = @get_headers($url, 1, $ctx);
    $elapsed = round((microtime(true) - $start) * 1000);
    if ($headers && isset($headers[0])) {
        preg_match('/\d{3}/', $headers[0], $m);
        $code = $m[0] ?? '???';
        $ok = $code >= 200 && $code < 400;
        $icon = $ok ? '✅' : '❌';
        echo "<p>$icon $label &rarr; HTTP $code ({$elapsed}ms)</p>";
    } else {
        echo "<p>❌ $label &rarr; No se pudo conectar ({$elapsed}ms)</p>";
    }
}
if (ini_get('allow_url_fopen')) {
    echo '<p>✅ allow_url_fopen = On</p>';
    testUrl('Google Fonts API (Poppins 400,700)', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;700');
    testUrl('Google Fonts CDN (gstatic)', 'https://fonts.gstatic.com');
    $self = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/css/style.css';
    testUrl('style.css (autoprueba)', $self);
} else {
    echo '<p>⚠️ allow_url_fopen = Off</p>';
}
?>
</div>

<div class="section">
<h2>5. Configuraci&oacute;n PHP</h2>
<table>
<tr><th>Directiva</th><th>Valor</th></tr>
<?php
$checks = [
    'allow_url_fopen' => 'Permite fopen() de URLs',
    'display_errors' => 'Muestra errores en pantalla',
    'error_reporting' => 'Nivel de reporte de errores',
    'file_uploads' => 'Subida de archivos',
    'upload_max_filesize' => 'Tamaño máximo de subida',
    'post_max_size' => 'Tamaño máximo POST',
    'max_execution_time' => 'Tiempo máximo de ejecución (s)',
    'memory_limit' => 'Límite de memoria',
    'max_input_time' => 'Tiempo máximo de entrada (s)',
];
foreach ($checks as $key => $label) {
    $v = ini_get($key);
    echo "<tr><td>$key</td><td>" . htmlspecialchars($label) . "</td><td><code>" . htmlspecialchars(var_export($v, true)) . "</code></td></tr>";
}
?>
</table>
</div>

<div class="section">
<h2>6. Prueba Visual de Carga de CSS y Fuente</h2>

<p><strong>Indicador de carga de style.css:</strong></p>
<p id="css-test-loaded" style="display:none;color:#16a34a;font-weight:bold;">✅ style.css cargado</p>
<p id="css-test-failed" style="color:#dc2626;font-weight:bold;">⚠️ style.css NO cargado o su regla no se aplic&oacute;</p>
<style>
#css-test-loaded { display: block !important; }
#css-test-failed { display: none !important; }
</style>

<p><strong>Prueba de fuente Poppins:</strong></p>
<div id="poppins-test">
Este texto deber&iacute;a verse en <strong>Poppins</strong>.<br>
<span class="small">Si ves Times New Roman, Arial u otra, la fuente no carg&oacute;.</span>
</div>

<p><strong>Prueba de JavaScript:</strong></p>
<p id="js-test-status">Esperando JavaScript...</p>
</div>

<div style="position:absolute;left:-9999px;top:0;width:1px;height:1px;overflow:hidden;" id="diag-test-elements" aria-hidden="true">
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
    <div class="proy-tabs">
        <button class="proy-tab active" data-tab="0">Tab 1</button>
        <button class="proy-tab" data-tab="1">Tab 2</button>
        <div class="proy-panel active" data-panel="0">Panel 1</div>
        <div class="proy-panel" data-panel="1">Panel 2</div>
    </div>
    <div class="proy-grid">
        <div class="proy-card"><div class="card-icon"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/></svg></div></div>
        <div class="proy-card"><div class="card-icon"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg></div></div>
    </div>
    <div class="counter" id="diag-counter"><span class="counter-value" data-target="150">0</span></div>
</div>

<div class="section" id="diag-seccion">
<h2>7. Diagn&oacute;stico del Navegador <span class="info" id="diag-status-icon">⏳</span></h2>
<p class="small" id="diag-progress">Ejecutando pruebas... espera unos segundos.</p>

<div id="diag-table-body"></div>

<div id="diag-resumen"></div>
</div>

<div class="section">
<h2>8. Diagn&oacute;stico de Carga de Im&aacute;genes</h2>

<?php
$diagOk = 0; $diagWarn = 0; $diagFail = 0;
function diagImgIcon($ok) { global $diagOk, $diagFail; if ($ok) { $diagOk++; return '<span class="pass">✅</span>'; } else { $diagFail++; return '<span class="fail">❌</span>'; } }
function diagImgWarn() { global $diagWarn; $diagWarn++; return '<span class="warn">⚠️</span>'; }

$base = __DIR__;
$uploads = $base . '/uploads';
$slidersDir = $uploads . '/sliders';
$galeriaDir = $uploads . '/galeria';
$htaccessFile = $uploads . '/.htaccess';

function testHttp($url, $maxTime = 5) {
    $ctx = stream_context_create(['http' => ['timeout' => $maxTime, 'method' => 'GET', 'follow_location' => true, 'ignore_errors' => true]]);
    $start = microtime(true);
    $headers = @get_headers($url, 1, $ctx);
    $elapsed = round((microtime(true) - $start) * 1000);
    if ($headers && isset($headers[0])) {
        preg_match('/\d{3}/', $headers[0], $m);
        $code = $m[0] ?? '???';
        return ['code' => intval($code), 'ms' => $elapsed, 'headers' => $headers[0]];
    }
    return ['code' => 0, 'ms' => $elapsed, 'headers' => 'Sin respuesta'];
}

function permStr($path) {
    if (!file_exists($path)) return '—';
    return '0' . decoct(fileperms($path) & 0777);
}
?>

<h3>8.1 Directorios de uploads</h3>
<table>
<tr><th>Directorio</th><th>Existe</th><th>Permisos</th><th>Archivos</th></tr>
<?php
foreach (['uploads/', 'uploads/sliders/', 'uploads/galeria/'] as $rel) {
    $abs = $base . '/' . $rel;
    $exists = is_dir($abs);
    $perm = $exists ? permStr($abs) : '—';
    $count = $exists ? count(array_diff(scandir($abs), ['.', '..', 'index.html', 'index.php'])) : 0;
    echo '<tr><td><code>' . htmlspecialchars($rel) . '</code></td>'
        . '<td>' . diagImgIcon($exists) . '</td>'
        . '<td><code>' . htmlspecialchars($perm) . '</code></td>'
        . '<td>' . $count . ' archivos</td></tr>';
}
?>
</table>

<h3>8.2 Archivos de imagen en disco</h3>
<table>
<tr><th>Archivo</th><th>Ruta absoluta</th><th>Existe</th><th>Tama&ntilde;o</th><th>Permisos</th></tr>
<?php
$testFiles = [
    'slider' => ['label' => 'Slider (referencia)', 'path' => $slidersDir . '/slider-01.jpg'],
    'galeria_jpeg' => ['label' => 'Galería .jpeg', 'path' => $galeriaDir . '/galeria_1782398314_2718.jpeg'],
    'galeria_jpg' => ['label' => 'Galería .jpg', 'path' => $galeriaDir . '/galeria_1782399532_4797.jpg'],
];
foreach ($testFiles as $key => $f) {
    $exists = file_exists($f['path']);
    $size = $exists ? number_format(filesize($f['path'])) . ' bytes' : '—';
    $perm = $exists ? permStr($f['path']) : '—';
    echo '<tr><td>' . htmlspecialchars($f['label']) . '</td>'
        . '<td><code>' . htmlspecialchars($f['path']) . '</code></td>'
        . '<td>' . diagImgIcon($exists) . '</td>'
        . '<td>' . $size . '</td>'
        . '<td><code>' . $perm . '</code></td></tr>';
}
?>
</table>

<h3>8.3 Prueba HTTP directa (get_headers)</h3>
<?php
$hasFopen = ini_get('allow_url_fopen');
if ($hasFopen) {
    $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $proto . '://' . $host;

    $urlsToTest = [
        'Slider (referencia)' => $baseUrl . '/uploads/sliders/slider-01.jpg',
        'Galería .jpeg' => $baseUrl . '/uploads/galeria/galeria_1782398314_2718.jpeg',
        'Galería .jpg' => $baseUrl . '/uploads/galeria/galeria_1782399532_4797.jpg',
        '.htaccess de uploads/' => $baseUrl . '/uploads/.htaccess',
    ];

    echo '<table><tr><th>URL</th><th>HTTP</th><th>Tiempo</th></tr>';
    foreach ($urlsToTest as $label => $url) {
        $result = testHttp($url);
        $code = $result['code'];
        $ms = $result['ms'];
        if ($code >= 200 && $code < 400) {
            $icon = diagImgIcon(true);
        } elseif ($code >= 400 && $code < 500) {
            $icon = diagImgIcon(false);
        } elseif ($code === 0) {
            $icon = diagImgWarn();
        } else {
            $icon = diagImgIcon(false);
        }
        echo '<tr><td>' . htmlspecialchars($label) . '</td>'
            . '<td>' . $icon . ' HTTP ' . $code . '</td>'
            . '<td>' . $ms . ' ms</td></tr>';
    }
    echo '</table>';
    echo '<p class="small">Nota: si slider da 200 y galería da 404 → el archivo no existe en el servidor. '
        . 'Si slider da 200 y galería da 403 → permisos o .htaccess bloquean. '
        . 'Si ambos dan 403/500 → el .htaccess de uploads/ es el problema.</p>';
} else {
    echo '<p>' . diagImgWarn() . ' allow_url_fopen = Off. No se pueden hacer pruebas HTTP desde el servidor.</p>';
}
?>

<h3>8.4 Archivo .htaccess de uploads/</h3>
<table>
<tr><th>Propiedad</th><th>Valor</th></tr>
<?php
$htaccessExists = file_exists($htaccessFile);
echo '<tr><td>.htaccess existe</td><td>' . diagImgIcon($htaccessExists) . ' ' . ($htaccessExists ? 'Sí' : 'No') . '</td></tr>';
if ($htaccessExists) {
    echo '<tr><td>Permisos</td><td><code>' . permStr($htaccessFile) . '</code></td></tr>';
    echo '<tr><td>Legible</td><td>' . diagImgIcon(is_readable($htaccessFile)) . (is_readable($htaccessFile) ? ' Sí' : ' No') . '</td></tr>';
    $content = file_get_contents($htaccessFile);
    echo '<tr><td>Contenido</td><td><pre>' . htmlspecialchars($content) . '</pre></td></tr>';
    $hasRequireAll = strpos($content, 'Require all') !== false;
    echo '<tr><td>Usa sintaxis Require all</td><td>' . ($hasRequireAll ? diagImgWarn() . ' ⚠️ Apache 2.4 — puede fallar en LiteSpeed' : diagImgIcon(true) . ' Sintaxis compatible') . '</td></tr>';
}
?>
</table>

<h3>8.5 Conclusi&oacute;n</h3>
<?php
$galeriaOnDisk = file_exists($galeriaDir . '/galeria_1782398314_2718.jpeg');
$sliderOnDisk = file_exists($slidersDir . '/slider-01.jpg');
echo '<div class="diag-summary" style="background:#f0f0f0;border:1px solid #ccc;padding:12px;border-radius:6px;">';
echo '<p><strong>Resumen de diagn&oacute;stico:</strong></p>';
echo '<p>✅ ' . $diagOk . ' correctas | ⚠️ ' . $diagWarn . ' advertencias | ❌ ' . $diagFail . ' fallos</p>';

if ($galeriaOnDisk && $sliderOnDisk) {
    echo '<p>✅ Ambos tipos de archivos existen en disco.</p>';
} elseif (!$galeriaOnDisk && $sliderOnDisk) {
    echo '<p>❌ Los archivos de galería <strong>NO existen</strong> en el servidor. Sliders SÍ existen.</p>';
    echo '<p><strong>Causa probable:</strong> Las imágenes no se copiaron al FTP. Debes subir <code>uploads/galeria/</code> al servidor.</p>';
} elseif (!$sliderOnDisk) {
    echo '<p>❌ Tampoco existen los sliders. Posible error de deploy general.</p>';
}

if ($hasFopen) {
    $sliderHttp = testHttp($baseUrl . '/uploads/sliders/slider-01.jpg');
    $galeriaHttp = testHttp($baseUrl . '/uploads/galeria/galeria_1782398314_2718.jpeg');
    if ($sliderHttp['code'] === 200 && $galeriaHttp['code'] >= 400) {
        echo '<p>❌ HTTP: slider OK (200) pero galería falla (' . $galeriaHttp['code'] . '). ';
        if ($galeriaHttp['code'] === 404) echo 'El archivo no se encontró en la ruta solicitada.</p>';
        elseif ($galeriaHttp['code'] === 403) echo 'El archivo está bloqueado (permisos o .htaccess).</p>';
        elseif ($galeriaHttp['code'] === 500) echo 'Error interno del servidor (posible .htaccess incompatible).</p>';
        else echo 'Código HTTP ' . $galeriaHttp['code'] . '.</p>';
    } elseif ($sliderHttp['code'] >= 400 && $galeriaHttp['code'] >= 400) {
        echo '<p>❌ Ambos tipos fallan. Posible problema con el .htaccess de <code>uploads/</code> o permisos generales.</p>';
    } elseif ($sliderHttp['code'] === 200 && $galeriaHttp['code'] === 200) {
        echo '<p>✅ HTTP: ambas URLs responden 200. Las imágenes deberían ser accesibles.</p>';
        echo '<p>⚠️ Si aun así no se ven en la página, puede ser problema de rutas en el código o caché del navegador/CloudFlare.</p>';
    }
}

echo '</div>';
?>
</div>

<script>
(function() {
    'use strict';

    var errors = [];
    var loadedResources = {};

    window.addEventListener('error', function(e) {
        if (e.target && (e.target.tagName === 'LINK' || e.target.tagName === 'SCRIPT' || e.target.tagName === 'IMG')) {
            loadedResources[e.target.tagName + ':' + (e.target.src || e.target.href)] = false;
            return;
        }
        errors.push({ msg: e.message || String(e), url: e.filename, line: e.lineno, col: e.colno });
    }, true);

    window.addEventListener('unhandledrejection', function(e) {
        errors.push({ msg: 'Promise: ' + (e.reason && e.reason.message ? e.reason.message : String(e.reason)), url: '', line: 0, col: 0 });
    });

    var $ = function(id) { return document.getElementById(id); };
    var esc = function(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };

    var okIcon = '<span class="pass">✅</span>';
    var warnIcon = '<span class="warn">⚠️</span>';
    var badIcon = '<span class="fail">❌</span>';

    function diagRow(label, value, grade) {
        var icon = grade === 'ok' ? okIcon : (grade === 'warn' ? warnIcon : badIcon);
        return '<div class="diag-row"><span class="diag-label">' + icon + ' ' + esc(label) + '</span><span class="diag-value">' + value + '</span></div>';
    }

    var rows = [];
    function add(label, value, grade) {
        rows.push({ label: label, value: value, grade: grade });
    }

    var cssLink = document.querySelector('link[href*="style.css"]');

    // 1. CSS loading
    if (cssLink && cssLink.sheet) {
        var rules;
        try { rules = cssLink.sheet.cssRules || cssLink.sheet.rules; } catch(e) { rules = null; }
        if (rules && rules.length > 0) {
            add('style.css cargado', rules.length + ' reglas', 'ok');
        } else {
            add('style.css cargado', 'Sin reglas (posible CORS)', 'warn');
        }
    } else {
        add('style.css cargado', 'No disponible', 'fail');
    }

    // 2. Font
    var bodyFont = window.getComputedStyle(document.body).fontFamily;
    var hasPoppins = bodyFont.indexOf('Poppins') !== -1;
    add('Fuente body', esc(bodyFont), hasPoppins ? 'ok' : 'fail');

    // 3. Skip-link
    var skip = document.querySelector('.skip-link');
    if (skip) {
        var s = window.getComputedStyle(skip);
        var topVal = parseFloat(s.top);
        var isHidden = topVal < -50 || s.visibility === 'hidden' || s.display === 'none';
        add('Skip-link oculto', 'top:' + s.top + ' vis:' + s.visibility + ' disp:' + s.display, isHidden ? 'ok' : 'fail');
    } else {
        add('Skip-link', 'No encontrado', 'fail');
    }

    // 4. proy-grid
    var proyGrid = document.querySelector('.proy-grid');
    if (proyGrid) {
        var gs = window.getComputedStyle(proyGrid);
        var gap = gs.gap || '';
        var cols = gs.gridTemplateColumns || '';
        var display = gs.display || '';
        var isGrid = display.indexOf('grid') !== -1;
        add('proy-grid layout', 'display:' + display + ' gap:' + gap + ' cols:' + cols, isGrid && gap ? 'ok' : 'fail');
    } else {
        add('proy-grid', 'No encontrado', 'fail');
    }

    // 5. proy-tab / proy-panel
    var tab = document.querySelector('.proy-tab');
    var panel = document.querySelector('.proy-panel');
    if (tab && panel) {
        var tabStyle = window.getComputedStyle(tab);
        var panelStyle = window.getComputedStyle(panel);
        add('Pestañas CSS', 'tab font:' + tabStyle.fontWeight + ' panel display:' + panelStyle.display, 'ok');
    } else {
        add('Pestañas CSS', 'No encontrados', 'fail');
    }

    // 6. card-icon + SVG visibility
    var cardIcon = document.querySelector('.card-icon');
    if (cardIcon) {
        var ci = window.getComputedStyle(cardIcon);
        var svg = cardIcon.querySelector('svg');
        var svgVisible = false;
        if (svg) {
            var svgs = window.getComputedStyle(svg);
            svgVisible = svgs.display !== 'none' && svgs.visibility !== 'hidden';
        }
        add('.card-icon', 'bg:' + ci.background.substring(0,60) + ' svg:' + (svgVisible ? 'visible' : 'HIDDEN'), svgVisible ? 'ok' : 'fail');
    } else {
        add('.card-icon', 'No encontrado', 'fail');
    }

    // 7. Counter element
    var counter = document.querySelector('.counter, .counter-value');
    if (counter) {
        add('Contador', 'Elemento encontrado', 'ok');
    } else {
        add('Contador', 'No encontrado', 'fail');
    }

    // 8. @keyframes in CSS
    var hasKeyframes = false;
    if (cssLink && cssLink.sheet) {
        try {
            var rr = cssLink.sheet.cssRules || cssLink.sheet.rules;
            for (var i = 0; i < rr.length; i++) {
                if (rr[i].type === CSSRule.KEYFRAMES_RULE || rr[i].type === 7) { hasKeyframes = true; break; }
            }
        } catch(e) {}
    }
    add('@keyframes en CSS', hasKeyframes ? 'Presente' : 'No encontrado', hasKeyframes ? 'ok' : 'warn');

    // 9. Font faces loaded
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function() {
            var list = [];
            document.fonts.forEach(function(f) { list.push(f.family + '(' + f.status + ')'); });
            var poppinsReady = document.fonts.check('12px Poppins');
            add('Poppins cargada', poppinsReady ? list.filter(function(s){return s.indexOf('Poppins')!==-1;}).join(', ') : 'NO cargada', poppinsReady ? 'ok' : 'fail');
            renderDiag();
        });
    }

    // 10. Viewport
    add('Viewport', window.innerWidth + 'x' + window.innerHeight + ' @' + window.devicePixelRatio + 'x', 'ok');

    // Resource errors
    var netErrors = [];
    if (window.PerformanceObserver) {
        try {
            var po = new PerformanceObserver(function(list) {
                list.getEntries().forEach(function(entry) {
                    if (entry.entryType === 'resource' && entry.responseStatus && entry.responseStatus >= 400) {
                        netErrors.push(entry.name + ' HTTP ' + entry.responseStatus);
                    }
                });
            });
            po.observe({ entryTypes: ['resource'] });
            setTimeout(function() { try { po.disconnect(); } catch(e) {} }, 3000);
        } catch(e) {}
    }

    function renderDiag() {
        var body = $('diag-table-body');
        var html = '';
        rows.forEach(function(r) { html += diagRow(r.label, r.value, r.grade); });

        if (netErrors.length > 0) {
            html += diagRow('HTTP errors (4xx)', netErrors.length + ' recursos', 'fail');
            netErrors.forEach(function(e) { html += '<div style="padding:2px 8px 2px 40px;font-size:12px;color:#991b1b;">' + esc(e) + '</div>'; });
        }

        if (errors.length > 0) {
            html += diagRow('Errores JS', errors.length + ' error(es)', 'fail');
            errors.forEach(function(e) {
                html += '<div class="error-accordion"><details><summary>' + esc(e.msg && e.msg.length > 80 ? e.msg.substring(0,80)+'...' : e.msg) + '</summary>';
                html += esc(e.msg) + '<br>Archivo: ' + esc(e.url) + ':' + e.line + ':' + e.col + '</details></div>';
            });
        } else {
            html += diagRow('Errores JS', '0', 'ok');
        }

        body.innerHTML = html;

        var okN = rows.filter(function(r) { return r.grade === 'ok'; }).length;
        var warnN = rows.filter(function(r) { return r.grade === 'warn'; }).length;
        var failN = rows.filter(function(r) { return r.grade === 'fail'; }).length;
        if (netErrors.length > 0) failN++;
        if (errors.length > 0) failN++;

        var s = '<div class="diag-summary" style="';
        if (failN > 0) s += 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;';
        else if (warnN > 0) s += 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;';
        else s += 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;';
        s += '"><strong>Resumen:</strong> ' + okN + ' ✅ | ' + warnN + ' ⚠️ | ' + failN + ' ❌';
        if (failN > 0) s += '<br>❌ <strong>Revisa los detalles arriba.</strong>';
        else if (warnN === 0) s += '<br>✅ Todo correcto.';
        s += '</div>';
        $('diag-resumen').innerHTML = s;
        $('diag-status-icon').textContent = '✅';
        $('diag-status-icon').className = 'info';
        $('diag-progress').textContent = 'Pruebas completadas.';
    }

    setTimeout(function() {
        if (netErrors.length > 0) {
            netErrors.forEach(function(e) { rows.push({ label: 'HTTP error', value: esc(e), grade: 'fail' }); });
        }
        renderDiag();
    }, 500);

    var status = $('js-test-status');
    if (status) { status.innerHTML = '✅ JavaScript funcionando'; status.style.color = '#16a34a'; }
})();
</script>

</body>
</html>
