<?php
/**
 * API pública de categorías
 * GET /api/categories.php
 */
require_once 'storage.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

$categorias = Storage::read('categorias');

echo json_encode(array(
    'success' => true,
    'data' => $categorias,
    'total' => count($categorias)
));
