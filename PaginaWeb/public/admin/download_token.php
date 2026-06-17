<?php
require_once '../api/auth.php';
$auth = new Auth();
$auth->requireLogin();

$tokenFile = __DIR__ . '/../data/admin_token.json';
if (!file_exists($tokenFile)) {
    http_response_code(404);
    die('Token no encontrado');
}
$data = json_decode(file_get_contents($tokenFile), true);
$token = $data['token'] ?? '';

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="admin_token.txt"');
echo "Token de acceso al panel admin\n";
echo "==============================\n\n";
echo $token . "\n\n";
echo "URL: /admin/login.php?token=" . $token . "\n";
