<?php
/**
 * API: Obtener noticias y eventos
 * Lee de JSON
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once 'storage.php';

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$items = Storage::read('noticias');

$results = array();
foreach ($items as $item) {
    if (!isset($item['publicada']) || !$item['publicada']) {
        continue;
    }
    if ($tipo && isset($item['tipo']) && $item['tipo'] !== $tipo) {
        continue;
    }
    if ($q) {
        $titulo = isset($item['titulo']) ? $item['titulo'] : '';
        $resumen = isset($item['resumen']) ? $item['resumen'] : '';
        $contenido = isset($item['contenido']) ? $item['contenido'] : '';
        $searchStr = strtolower($titulo . ' ' . $resumen . ' ' . $contenido);
        if (strpos($searchStr, strtolower($q)) === false) {
            continue;
        }
    }
    $results[] = $item;
    if (count($results) >= $limit) {
        break;
    }
}

usort($results, function($a, $b) {
    $da = isset($a['created_at']) ? $a['created_at'] : '';
    $db = isset($b['created_at']) ? $b['created_at'] : '';
    return strcmp($db, $da);
});

echo json_encode(array(
    'success' => true,
    'data' => $results,
    'total' => count($results)
));
