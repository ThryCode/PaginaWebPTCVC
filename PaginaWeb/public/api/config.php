<?php

define('ENV', getenv('APP_ENV') ?: (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']) ? 'development' : 'production'));
if (ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
if (ENV === 'production') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 7200);

if (version_compare(PHP_VERSION, '7.3.11', '<')) {
    http_response_code(500);
    die(json_encode(array(
        'success' => false,
        'message' => 'Este proyecto requiere PHP 7.3.11 o superior. Versión actual: ' . PHP_VERSION
    )));
}

define('SITE_URL', 'https://pctvc.cu');

define('DATA_DIR', __DIR__ . '/../data');

define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_FORM_SUBMISSIONS', 5);
define('FORM_SUBMISSION_WINDOW', 3600);
define('EMERGENCY_PAC_HASH', '$2y$10$3qdtCS5jT2F.8tH/09FTeuUr8NDqbhNSBuJ0.f6EpwN7tXWToKUxC');

date_default_timezone_set('America/Havana');

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

if (ENV === 'production') {
    header('Strict-Transport-Security: max-age=10886400; includeSubDomains; preload');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

register_shutdown_function(function() {
    if (class_exists('Storage')) {
        Storage::clearCache();
    }
});
