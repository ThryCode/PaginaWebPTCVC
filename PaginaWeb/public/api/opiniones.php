<?php
require_once 'storage.php';
require_once 'functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . SITE_URL);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

$opiniones = Storage::read('opiniones');

usort($opiniones, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

foreach ($opiniones as &$o) {
    if (!empty($o['imagen'])) {
        $o['imagen'] = _cacheBust($o['imagen']);
    }
}
unset($o);

echo json_encode(array(
    'success' => true,
    'data' => $opiniones
));
