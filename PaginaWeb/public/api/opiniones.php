<?php
require_once 'storage.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://pctvc.cu');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

$opiniones = Storage::read('opiniones');

usort($opiniones, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

echo json_encode(array(
    'success' => true,
    'data' => $opiniones
));
