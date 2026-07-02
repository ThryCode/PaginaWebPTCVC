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

$allNoticias = Storage::read('noticias');

$eventos = array();
foreach ($allNoticias as $n) {
    if (isset($n['tipo']) && $n['tipo'] === 'evento' && !empty($n['publicada'])) {
        $eventos[] = $n;
    }
}

if (isset($_GET['calendar']) && $_GET['calendar'] == '1') {
    $year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
    $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));

    $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
    $prefix = $year . '-' . $monthStr . '-';

    $dates = array();
    foreach ($eventos as $e) {
        $fe = isset($e['fecha_evento']) ? $e['fecha_evento'] : '';
        if (!$fe || strlen($fe) < 10) continue;
        if (substr($fe, 0, 7) !== $year . '-' . $monthStr) continue;

        $day = intval(substr($fe, 8, 2));
        if ($day < 1 || $day > 31) continue;

        if (!isset($dates[$day])) {
            $dates[$day] = array();
        }
        $dates[$day][] = array(
            'id' => $e['id'],
            'titulo' => $e['titulo'],
            'ubicacion' => isset($e['ubicacion']) ? $e['ubicacion'] : '',
            'fecha_evento' => $fe
        );
    }

    echo json_encode(array(
        'success' => true,
        'dates' => $dates
    ));
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$filtered = array();
foreach ($eventos as $n) {
    if ($q) {
        $titulo = isset($n['titulo']) ? $n['titulo'] : '';
        $resumen = isset($n['resumen']) ? $n['resumen'] : '';
        $contenido = isset($n['contenido']) ? $n['contenido'] : '';
        $searchStr = strtolower($titulo . ' ' . $resumen . ' ' . $contenido);
        if (strpos($searchStr, strtolower($q)) === false) {
            continue;
        }
    }
    $filtered[] = $n;
}

usort($filtered, function($a, $b) {
    $da = isset($a['destacada']) ? intval($a['destacada']) : 0;
    $db = isset($b['destacada']) ? intval($b['destacada']) : 0;
    if ($db !== $da) return $db - $da;
    return strcmp($b['created_at'], $a['created_at']);
});

$total = count($filtered);
$filtered = array_slice($filtered, $offset, $limit);

foreach ($filtered as &$item) {
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
    'data' => $filtered,
    'total' => $total
));
