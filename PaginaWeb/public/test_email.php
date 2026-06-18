<?php
/**
 * DIAGNÓSTICO DE EMAIL
 * Eliminar cuando el correo funcione correctamente
 */

// Cargar todo sin sesion para evitar "headers already sent"
error_reporting(E_ALL);
ini_set('display_errors', 1);

$autoloadPaths = array(
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/api/../vendor/autoload.php',
);
$autoloadLoaded = false;
foreach ($autoloadPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        $autoloadLoaded = true;
        break;
    }
}

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/storage.php';
require_once __DIR__ . '/api/mail.php';
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Test de Email</title>
<style>
    body { font-family: monospace; background: #111; color: #eee; padding: 20px; }
    h1 { color: #0f0; }
    .ok { color: #0f0; }
    .fail { color: #f00; }
    .section { background: #222; padding: 15px; margin: 10px 0; border-radius: 8px; }
    pre { white-space: pre-wrap; }
    .btn { padding: 10px 20px; background: #0a0; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
    .btn:hover { background: #0c0; }
    input { padding: 8px; width: 300px; border-radius: 4px; border: none; }
</style>
</head>
<body>
<h1>🔍 Diagnóstico de Email</h1>
<p><em>Elimina este archivo (public/test_email.php) cuando el correo funcione.</em></p>

<div class="section">
<h2>1. Autoloader</h2>
<?php echo $autoloadLoaded ? '<span class="ok">✓ Cargado</span>' : '<span class="fail">✗ NO CARGADO</span>'; ?>
<br>PHPMailer: <?php echo class_exists('PHPMailer\PHPMailer\PHPMailer') ? '<span class="ok">✓ DISPONIBLE</span>' : '<span class="fail">✗ NO DISPONIBLE</span>'; ?>
</div>

<div class="section">
<h2>2. Constantes SMTP</h2>
<?php
$checks = array(
    'FROM_EMAIL' => defined('FROM_EMAIL') ? FROM_EMAIL : 'NO DEFINIDO',
    'SMTP_HOST' => defined('SMTP_HOST') ? SMTP_HOST : 'NO DEFINIDO',
    'SMTP_PORT' => defined('SMTP_PORT') ? SMTP_PORT : 'NO DEFINIDO',
    'SMTP_USER' => defined('SMTP_USER') ? SMTP_USER : 'NO DEFINIDO',
    'SMTP_PASS' => defined('SMTP_PASS') ? (strlen(SMTP_PASS) . ' chars') : 'NO DEFINIDO',
    'SMTP_ENCRYPTION' => defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'NO DEFINIDO',
);
foreach ($checks as $k => $v): ?>
    <div><?php echo $k; ?>: <?php echo htmlspecialchars($v); ?></div>
<?php endforeach; ?>
</div>

<div class="section">
<h2>3. Usuarios</h2>
<?php
if (defined('DATA_DIR')) {
    echo "DATA_DIR: " . DATA_DIR . "<br>";
    echo "Real: " . (realpath(DATA_DIR) ?: 'NO RESUELVE') . "<br>";
    $archivo = rtrim(DATA_DIR, '/\\') . '/usuarios.json';
    echo "Archivo: $archivo<br>";
    echo "Existe: " . (file_exists($archivo) ? '<span class="ok">SI</span>' : '<span class="fail">NO</span>') . "<br>";
    if (file_exists($archivo)) {
        $cont = file_get_contents($archivo);
        $datos = json_decode($cont, true);
        echo "JSON valido: " . ($datos !== null ? '<span class="ok">SI</span>' : '<span class="fail">NO</span>') . "<br>";
        echo "Usuarios: " . (is_array($datos) ? count($datos) : 0) . "<br>";
        if (is_array($datos)) {
            foreach ($datos as $u) {
                echo "&nbsp;&nbsp;&bull; " . htmlspecialchars($u['nombre'] ?? '?') . " &lt;" . htmlspecialchars($u['email'] ?? '?') . "&gt;<br>";
            }
        }
    }
} else {
    echo '<span class="fail">DATA_DIR no definido</span>';
}
?>
</div>

<div class="section">
<h2>4. Conexión SMTP</h2>
<?php
$host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
$port = defined('SMTP_PORT') ? SMTP_PORT : 587;
echo "Conectando a $host:$port...<br>";
$start = microtime(true);
$errno = 0; $errstr = '';
$fp = @fsockopen($host, $port, $errno, $errstr, 10);
$elapsed = round(microtime(true) - $start, 2);
if ($fp) {
    echo '<span class="ok">✓ CONECTADO (' . $elapsed . 's)</span><br>';
    echo "Respuesta inicial:<br><pre>";
    $resp = fgets($fp, 512);
    echo htmlspecialchars($resp);
    fclose($fp);
    echo "</pre>";
} else {
    echo '<span class="fail">✗ FALLO (' . $elapsed . 's): ' . htmlspecialchars($errstr) . ' (errno=' . $errno . ')</span><br>';
}
echo "<br>openssl: " . (extension_loaded('openssl') ? '<span class="ok">SI</span>' : '<span class="fail">NO</span>');
?>
</div>

<div class="section">
<h2>5. Enviar correo de prueba</h2>
<form method="POST">
    <label>Email de prueba:</label><br>
    <input type="email" name="test_email" value="<?php echo htmlspecialchars($_POST['test_email'] ?? 'epmjob@gmail.com'); ?>" required>
    <button type="submit" class="btn" name="send_test">Enviar prueba</button>
</form>
<?php
if (isset($_POST['send_test'])) {
    $testTo = trim($_POST['test_email']);
    echo "<h3>Resultado:</h3>";
    try {
        $result = sendMail($testTo, 'Prueba desde PCTVC - ' . date('Y-m-d H:i:s'), "Este es un correo de prueba.\n\nSi recibes esto, el email funciona correctamente.\n\nSaludos.");
    } catch (Exception $e) {
        echo '<span class="fail">Excepción no capturada: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
    echo '<div style="background:#333;padding:15px;border-radius:6px;margin-top:10px;">';
    echo "Resultado: " . (!empty($result) ? '<span class="ok">ENVIADO</span>' : '<span class="fail">FALLO</span>') . "<br><br>";
    echo "<strong>Log de env&iacute;o:</strong><br>";
    $log = getMailLog();
    if (!empty($log)) {
        echo "<pre>";
        foreach ($log as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo "</pre>";
    }
    $errors = getMailErrors();
    if (!empty($errors)) {
        echo "<strong>Errores:</strong><br>";
        foreach ($errors as $e) {
            echo '<div class="fail">&bull; ' . htmlspecialchars($e) . "</div>";
        }
    }
    echo '</div>';
}
?>
</div>

<?php if (isset($_GET['send_test'])): ?>
<script>
    var testForm = document.querySelector('form');
    testForm.style.border = '2px solid red';
</script>
<?php endif; ?>
</body>
</html>
