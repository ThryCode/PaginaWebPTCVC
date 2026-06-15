<?php
/**
 * Configuración general
 * Sin base de datos - todo en archivos JSON
 */

if (version_compare(PHP_VERSION, '7.3.11', '<')) {
    http_response_code(500);
    die(json_encode(array(
        'success' => false,
        'message' => 'Este proyecto requiere PHP 7.3.11 o superior. Versión actual: ' . PHP_VERSION
    )));
}

define('ENV', 'production');

// Directorio de datos
define('DATA_DIR', __DIR__ . '/../data');

// Seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_FORM_SUBMISSIONS', 5);
define('FORM_SUBMISSION_WINDOW', 3600);

// Email
define('CONTACT_EMAIL', 'info@pctvc.cu');
define('FROM_EMAIL', 'noreply@pctvc.cu');
define('FROM_NAME', 'PCT Villa Clara');

// Timezone
date_default_timezone_set('America/Havana');

// CSRF Token functions
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}
