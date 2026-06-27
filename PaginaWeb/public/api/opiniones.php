<?php
require_once 'storage.php';

function _cacheBust($path) {
    $abs = __DIR__ . '/../' . $path;
    $v = file_exists($abs) ? filemtime($abs) : time();
    return $path . '?v=' . $v;
}

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
