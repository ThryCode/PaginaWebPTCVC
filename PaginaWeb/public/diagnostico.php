<?php
header('Content-Type: text/plain; charset=utf-8');

$configLoaded = false;
$configFile = __DIR__ . '/api/config.php';
if (file_exists($configFile)) {
    try {
        require_once $configFile;
        $configLoaded = true;
    } catch (Throwable $e) {
        echo "[AVISO] config.php cargado con error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== DIAGNÓSTICO DE CORREO ===\n\n";

echo "--- Versión PHP ---\n";
echo phpversion() . "\n\n";

echo "--- Constantes ---\n";
echo "ENV: " . (defined('ENV') ? ENV : 'NO DEFINIDO') . "\n";
echo "FROM_EMAIL: " . (defined('FROM_EMAIL') ? FROM_EMAIL : 'NO DEFINIDO') . "\n";
echo "SMTP_HOST: " . (defined('SMTP_HOST') ? SMTP_HOST : 'NO DEFINIDO') . "\n";
echo "SMTP_PORT: " . (defined('SMTP_PORT') ? SMTP_PORT : 'NO DEFINIDO') . "\n";
echo "SMTP_USER: " . (defined('SMTP_USER') ? SMTP_USER : 'NO DEFINIDO') . "\n";
echo "SMTP_PASS: " . (defined('SMTP_PASS') ? (strlen(SMTP_PASS) . ' chars') : 'NO DEFINIDO') . "\n";
echo "\n";

echo "--- Autoloader ---\n";
$autoloadPaths = array(
    __DIR__ . '/api/../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
);
foreach ($autoloadPaths as $p) {
    $real = realpath($p);
    echo "  " . $p . " → " . ($real ? $real : 'NO EXISTE') . "\n";
}
echo "\n";

echo "--- PHPMailer ---\n";
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    echo "Autoloader cargado correctamente\n";
}
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "Clase PHPMailer: DISPONIBLE\n";
} else {
    echo "Clase PHPMailer: NO DISPONIBLE\n";
}
echo "\n";

echo "--- DATA_DIR ---\n";
if (defined('DATA_DIR')) {
    echo "DATA_DIR: " . DATA_DIR . "\n";
    echo "Real: " . realpath(DATA_DIR) . "\n";
    echo "Existe: " . (is_dir(DATA_DIR) ? 'SI' : 'NO') . "\n";
} else {
    echo "DATA_DIR no definido (config.php no cargado)\n";
}
echo "\n";

echo "--- Usuarios ---\n";
if (defined('DATA_DIR')) {
    $archivo = DATA_DIR . '/usuarios.json';
    echo "Archivo: " . $archivo . "\n";
    echo "Existe: " . (file_exists($archivo) ? 'SI' : 'NO') . "\n";
    echo "Legible: " . (is_readable($archivo) ? 'SI' : 'NO') . "\n";
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $datos = json_decode($contenido, true);
        echo "JSON valido: " . ($datos !== null ? 'SI' : 'NO') . "\n";
        echo "Total usuarios: " . (is_array($datos) ? count($datos) : 'NO ARRAY') . "\n";
        if (is_array($datos)) {
            foreach ($datos as $i => $u) {
                echo "  " . ($i+1) . ". " . ($u['nombre'] ?? '?') . " <" . ($u['email'] ?? '?') . ">\n";
            }
        }
    }
}
echo "\n";

echo "--- Prueba SMTP ---\n";
$host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
$port = defined('SMTP_PORT') ? SMTP_PORT : 587;
echo "Conectando a " . $host . ":" . $port . "...\n";
$start = microtime(true);
$errno = 0; $errstr = '';
$fp = @fsockopen($host, $port, $errno, $errstr, 10);
$elapsed = round(microtime(true) - $start, 2);
if ($fp) {
    echo "CONEXION EXITOSA (" . $elapsed . "s)\n";
    fclose($fp);
} else {
    echo "FALLO (" . $elapsed . "s): " . $errstr . " (errno=" . $errno . ")\n";
}
echo "\n";

echo "--- Extensión openssl ---\n";
echo "openssl: " . (extension_loaded('openssl') ? 'SI' : 'NO') . "\n";
echo "--- Extensión sockets ---\n";
echo "sockets: " . (extension_loaded('sockets') ? 'SI' : 'NO') . "\n";
echo "\n";

echo "=== FIN DIAGNÓSTICO ===\n";
