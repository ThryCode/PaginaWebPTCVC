<?php
/**
 * API pública de eventos
 * GET /api/events.php
 */
require_once 'storage.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$allNoticias = Storage::read('noticias');
$eventos = array();

foreach ($allNoticias as $n) {
    if (isset($n['tipo']) && $n['tipo'] === 'evento' && !empty($n['publicada'])) {
        if ($q) {
            $titulo = isset($n['titulo']) ? $n['titulo'] : '';
            $resumen = isset($n['resumen']) ? $n['resumen'] : '';
            $contenido = isset($n['contenido']) ? $n['contenido'] : '';
            $searchStr = strtolower($titulo . ' ' . $resumen . ' ' . $contenido);
            if (strpos($searchStr, strtolower($q)) === false) {
                continue;
            }
        }
        $eventos[] = $n;
    }
}

usort($eventos, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

$total = count($eventos);
$eventos = array_slice($eventos, $offset, $limit);

echo json_encode(array(
    'success' => true,
    'data' => $eventos,
    'total' => $total
));
