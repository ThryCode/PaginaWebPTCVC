<?php
/**
 * API pública de galería
 * GET /api/gallery.php
 */
require_once 'storage.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$galeria = Storage::read('galeria');
usort($galeria, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

$total = count($galeria);
$galeria = array_slice($galeria, $offset, $limit);

echo json_encode(array(
    'success' => true,
    'data' => $galeria,
    'total' => $total
));
