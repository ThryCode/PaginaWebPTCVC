<?php
require_once 'storage.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

$galeria = Storage::read('galeria');
usort($galeria, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

$groups = array();
foreach ($galeria as $item) {
    $t = !empty($item['titulo']) ? $item['titulo'] : 'Sin título';
    if (!isset($groups[$t])) {
        $groups[$t] = array('titulo' => $t, 'imagenes' => array());
    }
    $groups[$t]['imagenes'][] = $item['imagen'];
}

$groups = array_values($groups);
$groups = array_slice($groups, 0, $limit);

echo json_encode(array(
    'success' => true,
    'data' => $groups,
    'total' => count($groups)
));
