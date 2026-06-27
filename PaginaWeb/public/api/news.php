<?php
/**
 * API: Obtener noticias y eventos
 * Lee de JSON
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once 'storage.php';

function _cacheBust($path) {
    $abs = __DIR__ . '/../' . $path;
    $v = file_exists($abs) ? filemtime($abs) : time();
    return $path . '?v=' . $v;
}

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
}

usort($results, function($a, $b) {
    $da = isset($a['created_at']) ? $a['created_at'] : '';
    $db = isset($b['created_at']) ? $b['created_at'] : '';
    return strcmp($db, $da);
});

$results = array_slice($results, 0, $limit);

foreach ($results as &$item) {
    if (!empty($item['imagen'])) {
        $item['imagen'] = _cacheBust($item['imagen']);
    }
    if (!empty($item['imagenes']) && is_array($item['imagenes'])) {
        foreach ($item['imagenes'] as &$img) {
            $img = _cacheBust($img);
        }
        unset($img);
    }
}
unset($item);

echo json_encode(array(
    'success' => true,
    'data' => $results,
    'total' => count($results)
));
