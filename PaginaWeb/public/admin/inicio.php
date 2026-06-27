<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

// Counters managed via Storage::read('counters') / Storage::write('counters')

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'portada';
$message = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'created') $message = 'Creado correctamente.';
    elseif ($msg === 'updated') $message = 'Actualizado correctamente.';
    elseif ($msg === 'deleted') $message = 'Eliminado correctamente.';
    elseif ($msg === 'reordered') $message = 'Orden actualizado.';
    elseif ($msg === 'creado') $message = 'Creado correctamente.';
    elseif ($msg === 'editado') $message = 'Actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Eliminado correctamente.';
}

// === POST HANDLER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        // --- PORTADA ---
        if ($tab === 'portada') {
            if ($action === 'delete') {
                $deleteId = intval($_POST['id'] ?? 0);
                if ($deleteId > 0) {
                    $existing = Storage::findById('sliders', $deleteId);
                    if ($existing && isset($existing['imagen'])) {
                        $imgPath = '../' . ltrim($existing['imagen'], '/');
                        if (strpos($imgPath, '/../') === false && strpos($imgPath, '..\\') === false && file_exists($imgPath)) unlink($imgPath);
                    }
                    Storage::delete('sliders', $deleteId);
                    $message = 'Slider eliminado.';
                }
                $action = 'list';

            } elseif ($action === 'upload') {
                $sliderDir = '../uploads/sliders/';
                if (!is_dir($sliderDir)) mkdir($sliderDir, 0755, true);
                $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                $uploaded = 0;
                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['imagenes']['error'][$key] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($_FILES['imagenes']['name'][$key], PATHINFO_EXTENSION));
                    $mime = mime_content_type($tmpName);
                    if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMime) || $_FILES['imagenes']['size'][$key] > 5 * 1024 * 1024) continue;
                    $filename = 'slider_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $sliderDir . $filename)) {
                        $allSliders = Storage::read('sliders');
                        $maxOrden = 0;
                        foreach ($allSliders as $s) { if (($s['orden'] ?? 0) > $maxOrden) $maxOrden = $s['orden']; }
                        Storage::insert('sliders', array('imagen' => 'uploads/sliders/' . $filename, 'titulo' => trim($_POST['titulo'] ?? ''), 'orden' => $maxOrden + 1));
                        $uploaded++;
                    }
                }
                if ($uploaded > 0) $message = "$uploaded imagen(es) subida(s) correctamente.";
                else $error = 'No se pudieron subir las imágenes.';
                $action = 'list';

            } elseif ($action === 'update') {
                $updateId = intval($_POST['id'] ?? 0);
                if ($updateId > 0) {
                    $existing = Storage::findById('sliders', $updateId);
                    if ($existing) {
                        $existing['titulo'] = trim($_POST['titulo'] ?? '');
                        Storage::update('sliders', $updateId, $existing);
                        $message = 'Slider actualizado.';
                    }
                }
                $action = 'list';

            } elseif ($action === 'reorder') {
                $order = $_POST['order'] ?? array();
                $allSliders = Storage::read('sliders');
                foreach ($order as $idx => $idVal) {
                    foreach ($allSliders as &$s) { if ($s['id'] === intval($idVal)) { $s['orden'] = $idx + 1; break; } }
                }
                Storage::write('sliders', $allSliders);
                $message = 'Orden actualizado.';
                $action = 'list';
            }

        // --- CONTADORES ---
        } elseif ($tab === 'contadores') {
            $postAction = $_POST['action'] ?? '';
            $counters = Storage::read('counters');

            if ($postAction === 'create') {
                $newId = 1;
                foreach ($counters as $c) { if ($c['id'] >= $newId) $newId = $c['id'] + 1; }
                $counters[] = array('id' => $newId, 'numero' => intval($_POST['numero'] ?? 0), 'label' => trim($_POST['label'] ?? ''), 'orden' => intval($_POST['orden'] ?? count($counters) + 1));
                Storage::write('counters', $counters);
                header('Location: inicio.php?tab=contadores&msg=created');
                exit;

            } elseif ($postAction === 'update') {
                $updateId = intval($_POST['id'] ?? 0);
                foreach ($counters as &$c) { if ($c['id'] === $updateId) { $c['numero'] = intval($_POST['numero'] ?? $c['numero']); $c['label'] = trim($_POST['label'] ?? $c['label']); $c['orden'] = intval($_POST['orden'] ?? $c['orden']); break; } }
                Storage::write('counters', $counters);
                header('Location: inicio.php?tab=contadores&msg=updated');
                exit;

            } elseif ($postAction === 'delete') {
                $deleteId = intval($_POST['id'] ?? 0);
                $counters = array_filter($counters, function($c) use ($deleteId) { return $c['id'] !== $deleteId; });
                $counters = array_values($counters);
                Storage::write('counters', $counters);
                header('Location: inicio.php?tab=contadores&msg=deleted');
                exit;

            } elseif ($postAction === 'reorder') {
                $order = $_POST['order'] ?? array();
                foreach ($order as $idx => $idVal) { foreach ($counters as &$c) { if ($c['id'] === intval($idVal)) { $c['orden'] = $idx + 1; break; } } }
                Storage::write('counters', $counters);
                header('Location: inicio.php?tab=contadores&msg=reordered');
                exit;
            }
        // --- OPINIONES ---
        } elseif ($tab === 'opiniones') {
            $postAction = $_POST['action'] ?? '';

            if ($postAction === 'create') {
                $nombre = trim($_POST['nombre'] ?? '');
                $cargo = trim($_POST['cargo'] ?? '');
                $texto = trim($_POST['texto'] ?? '');
                $allOpiniones = Storage::read('opiniones');
                $orden = intval($_POST['orden'] ?? count($allOpiniones) + 1);
                $imagen = '';
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/opiniones/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                    $mime = mime_content_type($_FILES['imagen']['tmp_name']);
                    if (in_array($ext, $allowedExts) && in_array($mime, $allowedMime) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
                        $filename = 'opinion_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $filename)) {
                            $imagen = 'uploads/opiniones/' . $filename;
                        }
                    }
                }
                $newId = 1;
                foreach ($allOpiniones as $o) { if (isset($o['id']) && $o['id'] >= $newId) $newId = $o['id'] + 1; }
                $allOpiniones[] = array('id' => $newId, 'nombre' => $nombre, 'cargo' => $cargo, 'texto' => $texto, 'imagen' => $imagen, 'orden' => $orden);
                Storage::write('opiniones', $allOpiniones);
                header('Location: inicio.php?tab=opiniones&msg=created');
                exit;

            } elseif ($postAction === 'update') {
                $updateId = intval($_POST['id'] ?? 0);
                $allOpiniones = Storage::read('opiniones');
                foreach ($allOpiniones as &$o) {
                    if (isset($o['id']) && $o['id'] === $updateId) {
                        $o['nombre'] = trim($_POST['nombre'] ?? $o['nombre']);
                        $o['cargo'] = trim($_POST['cargo'] ?? $o['cargo']);
                        $o['texto'] = trim($_POST['texto'] ?? $o['texto']);
                        $o['orden'] = intval($_POST['orden'] ?? $o['orden']);
                        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                            $uploadDir = '../uploads/opiniones/';
                            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                            $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                            $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                            $mime = mime_content_type($_FILES['imagen']['tmp_name']);
                            if (in_array($ext, $allowedExts) && in_array($mime, $allowedMime) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
                                $filename = 'opinion_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $filename)) {
                                    if (!empty($o['imagen'])) { $oldPath = '../' . $o['imagen']; if (file_exists($oldPath)) unlink($oldPath); }
                                    $o['imagen'] = 'uploads/opiniones/' . $filename;
                                }
                            }
                        }
                        break;
                    }
                }
                unset($o);
                Storage::write('opiniones', $allOpiniones);
                header('Location: inicio.php?tab=opiniones&msg=updated');
                exit;

            } elseif ($postAction === 'delete') {
                $deleteId = intval($_POST['id'] ?? 0);
                $allOpiniones = Storage::read('opiniones');
                foreach ($allOpiniones as $o) {
                    if (isset($o['id']) && $o['id'] === $deleteId) {
                        if (!empty($o['imagen'])) { $imgPath = '../' . $o['imagen']; if (file_exists($imgPath)) unlink($imgPath); }
                        break;
                    }
                }
                $allOpiniones = array_values(array_filter($allOpiniones, function($o) use ($deleteId) { return !isset($o['id']) || $o['id'] !== $deleteId; }));
                Storage::write('opiniones', $allOpiniones);
                header('Location: inicio.php?tab=opiniones&msg=deleted');
                exit;
            }
        }
    }
}

// === LOAD DATA ===
$slider = null;
if ($action === 'edit' && $id > 0 && $tab === 'portada') {
    $slider = Storage::findById('sliders', $id);
    if (!$slider) { $action = 'list'; $error = 'Slider no encontrado.'; }
}

$slidersList = array();
if ($action === 'list' && $tab === 'portada') {
    $slidersList = Storage::read('sliders');
    usort($slidersList, function($a, $b) { return ($a['orden'] ?? 0) - ($b['orden'] ?? 0); });
}

$countersList = array();
if ($tab === 'contadores') {
    $countersList = Storage::read('counters');
    usort($countersList, function($a, $b) { return ($a['orden'] ?? 0) - ($b['orden'] ?? 0); });
}

$opinionesList = array();
if ($tab === 'opiniones') {
    $opinionesList = Storage::read('opiniones');
    usort($opinionesList, function($a, $b) { return ($a['orden'] ?? 0) - ($b['orden'] ?? 0); });
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=1, user-scalable=no';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Inicio</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <style>
        .tabs-nav { display:flex; gap:0; margin-bottom:20px; border-bottom:2px solid #e0e0e0; }
        .tabs-nav a { padding:10px 24px; text-decoration:none; color:#666; font-weight:600; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
        .tabs-nav a:hover { color:#004966; }
        .tabs-nav a.active { color:#00A0E1; border-bottom-color:#00A0E1; }
        .slider-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:16px; margin-top:20px; }
        .slider-item { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); cursor:grab; }
        .slider-item.dragging { opacity:0.5; }
        .slider-item img { width:100%; height:150px; object-fit:cover; display:block; }
        .slider-item-info { padding:12px; }
        .slider-item-info h4 { font-size:0.85rem; margin-bottom:2px; color:#1a1a2e; }
        .slider-item-info .orden-badge { display:inline-block; background:#e8f0fe; color:#1a73e8; font-size:0.75rem; padding:2px 8px; border-radius:10px; margin-top:4px; }
        .slider-item-actions { display:flex; gap:6px; padding:0 12px 12px; }
        .slider-item .drag-handle { display:block; text-align:center; padding:4px 0; color:#ccc; font-size:1rem; user-select:none; border-bottom:1px solid #f0f0f0; }
        .counter-preview { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:30px; }
        .counter-preview-item { background:linear-gradient(135deg, #003c6e, #008674); color:#fff; padding:24px; border-radius:12px; text-align:center; }
        .counter-preview-item .num { font-size:2.2rem; font-weight:900; }
        .counter-preview-item .lbl { font-size:0.85rem; opacity:0.85; margin-top:4px; }
        .counter-row { display:flex; align-items:center; gap:16px; padding:16px 20px; background:#fff; border-radius:10px; margin-bottom:10px; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
        .counter-row .drag-handle { cursor:grab; color:#ccc; font-size:1.2rem; user-select:none; }
        .counter-row .counter-data { flex:1; }
        .counter-row .counter-data h4 { color:#1a1a2e; font-size:1rem; }
        .counter-row .counter-data span { color:#888; font-size:0.82rem; }
        .counter-row .actions { display:flex; gap:8px; }
        .edit-form { background:#f8f9fa; padding:20px; border-radius:12px; margin-bottom:20px; border:2px dashed #e0e0e0; display:none; }
        .edit-form.active { display:block; }
        .inline-grid { display:grid; grid-template-columns:1fr 2fr 1fr; gap:12px; }
        .opinion-preview { display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:30px; }
        .opinion-preview-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
        .opinion-preview-card .quote { font-size:2rem; color:#008674; line-height:1; }
        .opinion-preview-card .text { font-size:0.85rem; color:#555; margin:8px 0; font-style:italic; }
        .opinion-preview-card .author { display:flex; align-items:center; gap:10px; margin-top:12px; }
        .opinion-preview-card .author img { width:40px; height:40px; border-radius:50%; object-fit:cover; }
        .opinion-preview-card .author-placeholder { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #003c6e, #008674); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem; }
        .opinion-preview-card .author-name { font-weight:700; font-size:0.85rem; color:#1a1a2e; }
        .opinion-preview-card .author-cargo { font-size:0.75rem; color:#888; }
        .opinion-row { display:flex; align-items:center; gap:16px; padding:16px 20px; background:#fff; border-radius:10px; margin-bottom:10px; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
        .opinion-row .opinion-thumb { width:50px; height:50px; border-radius:50%; object-fit:cover; }
        .opinion-row .opinion-thumb-placeholder { width:50px; height:50px; border-radius:50%; background:linear-gradient(135deg, #003c6e, #008674); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.1rem; flex-shrink:0; }
        .opinion-row .opinion-data { flex:1; }
        .opinion-row .opinion-data h4 { color:#1a1a2e; font-size:0.95rem; margin:0; }
        .opinion-row .opinion-data span { color:#888; font-size:0.8rem; }
        .opinion-row .actions { display:flex; gap:8px; }
        @media (max-width:768px) { .counter-preview { grid-template-columns:1fr 1fr; } .inline-grid { grid-template-columns:1fr; } .opinion-preview { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php
                    $tabTitles = array('portada'=>'Portada', 'contadores'=>'Contadores', 'opiniones'=>'Opiniones');
                    echo $tabTitles[$tab] ?? 'Inicio';
                ?></h1>
                <?php if ($action === 'list' && $tab === 'portada'): ?>
                    <a href="?action=upload&tab=portada" class="btn btn-primary">+ Subir</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list' || $tab === 'contadores' || $tab === 'opiniones'): ?>
                    <!-- TABS NAV -->
                    <div class="tabs-nav">
                        <a href="?tab=portada" class="<?php echo $tab === 'portada' ? 'active' : ''; ?>">Portada</a>
                        <a href="?tab=contadores" class="<?php echo $tab === 'contadores' ? 'active' : ''; ?>">Contadores</a>
                        <a href="?tab=opiniones" class="<?php echo $tab === 'opiniones' ? 'active' : ''; ?>">Opiniones</a>
                    </div>

                    <!-- PORTADA LIST -->
                    <?php if ($tab === 'portada'): ?>
                        <p style="color:#888;margin-bottom:4px;">Arrastra las imágenes para reordenar. Los cambios se guardan automáticamente.</p>
                        <?php if (empty($slidersList)): ?>
                            <p class="empty">No hay sliders. Sube el primero.</p>
                        <?php else: ?>
                            <div class="slider-grid" id="sliderGrid">
                                <?php foreach ($slidersList as $s): ?>
                                <div class="slider-item" data-id="<?php echo $s['id']; ?>">
                                    <div class="drag-handle">☰</div>
                                    <img src="/<?php echo htmlspecialchars($s['imagen']); ?>" alt="<?php echo htmlspecialchars($s['titulo'] ?? ''); ?>">
                                    <div class="slider-item-info">
                                        <h4><?php echo htmlspecialchars($s['titulo'] ?: 'Sin título'); ?></h4>
                                        <span class="orden-badge">Orden: <?php echo intval($s['orden']); ?></span>
                                    </div>
                                    <div class="slider-item-actions">
                                        <a href="?action=edit&id=<?php echo $s['id']; ?>&tab=portada" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete&tab=portada" data-confirm="¿Eliminar este slider?" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <!-- CONTADORES -->
                    <?php elseif ($tab === 'contadores'): ?>
                        <h3 style="margin-bottom:16px;color:#1a1a2e;">Vista previa</h3>
                        <div class="counter-preview">
                            <?php if (empty($countersList)): ?>
                                <p class="empty" style="grid-column:1/-1;">No hay contadores. Crea uno nuevo.</p>
                            <?php else: ?>
                                <?php foreach ($countersList as $c): ?>
                                    <div class="counter-preview-item">
                                        <div class="num"><?php echo intval($c['numero']); ?></div>
                                        <div class="lbl"><?php echo htmlspecialchars($c['label']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="grid-2">
                            <div class="panel">
                                <div class="panel-header"><h2>Crear nuevo contador</h2></div>
                                <div class="panel-body">
                                    <form method="POST">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="create">
                                        <div class="form-group">
                                            <label for="new_label">Etiqueta (ej: Empresas incubadas)</label>
                                            <input type="text" id="new_label" name="label" required placeholder="Nombre de la categoría">
                                        </div>
                                        <div class="inline-grid">
                                            <div class="form-group">
                                                <label for="new_numero">Número</label>
                                                <input type="number" id="new_numero" name="numero" required min="0" value="0">
                                            </div>
                                            <div class="form-group">
                                                <label for="new_orden">Orden</label>
                                                <input type="number" id="new_orden" name="orden" required min="1" value="<?php echo count($countersList) + 1; ?>">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success">Crear Contador</button>
                                    </form>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-header"><h2>Contadores actuales</h2></div>
                                <div class="panel-body" id="counterList">
                                    <?php if (empty($countersList)): ?>
                                        <p class="empty">No hay contadores creados aun.</p>
                                    <?php else: ?>
                                        <?php foreach ($countersList as $c): ?>
                                            <div class="counter-row" id="counter-<?php echo $c['id']; ?>" data-id="<?php echo $c['id']; ?>">
                                                <span class="drag-handle">☰</span>
                                                <div class="counter-data">
                                                    <h4><?php echo htmlspecialchars($c['label']); ?></h4>
                                                    <span>Número: <?php echo intval($c['numero']); ?> | Orden: <?php echo intval($c['orden']); ?></span>
                                                </div>
                                                <div class="actions">
                                                    <button class="btn btn-sm btn-primary" data-toggle-edit="<?php echo $c['id']; ?>">Editar</button>
                                                    <form class="delete-form" method="POST" data-confirm="Eliminar este contador?" style="display:inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="edit-form" id="edit-<?php echo $c['id']; ?>">
                                                <form method="POST">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                    <div class="form-group">
                                                        <label>Etiqueta</label>
                                                        <input type="text" name="label" required value="<?php echo htmlspecialchars($c['label']); ?>">
                                                    </div>
                                                    <div class="inline-grid">
                                                        <div class="form-group">
                                                            <label>Número</label>
                                                            <input type="number" name="numero" required min="0" value="<?php echo intval($c['numero']); ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Orden</label>
                                                            <input type="number" name="orden" required min="1" value="<?php echo intval($c['orden']); ?>">
                                                        </div>
                                                    </div>
                                                    <div style="display:flex;gap:10px;">
                                                        <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle-edit="<?php echo $c['id']; ?>">Cancelar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <!-- OPINIONES -->
                    <?php elseif ($tab === 'opiniones'): ?>
                        <h3 style="margin-bottom:16px;color:#1a1a2e;">Vista previa</h3>
                        <div class="opinion-preview">
                            <?php if (empty($opinionesList)): ?>
                                <p class="empty" style="grid-column:1/-1;">No hay opiniones. Crea una nueva.</p>
                            <?php else: ?>
                                <?php foreach ($opinionesList as $o): ?>
                                    <div class="opinion-preview-card">
                                        <div class="quote">&#10077;</div>
                                        <div class="text"><?php echo htmlspecialchars($o['texto']); ?></div>
                                        <div class="author">
                                            <?php if (!empty($o['imagen'])): ?>
                                                <img src="/<?php echo htmlspecialchars($o['imagen']); ?>" alt="<?php echo htmlspecialchars($o['nombre']); ?>">
                                            <?php else: ?>
                                                <div class="author-placeholder"><?php echo htmlspecialchars(mb_substr($o['nombre'], 0, 1)); ?></div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="author-name"><?php echo htmlspecialchars($o['nombre']); ?></div>
                                                <div class="author-cargo"><?php echo htmlspecialchars($o['cargo']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
                            <button class="btn btn-primary" onclick="toggleCreateOpinion()">+ Nueva opinión</button>
                        </div>

                        <div class="edit-form" id="create-opinion-form" style="margin-bottom:20px;">
                            <div class="panel" style="box-shadow:none;padding:0;">
                                <div class="panel-header"><h2>Crear nueva opinion</h2></div>
                                <div class="panel-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="create">
                                        <div class="form-group">
                                            <label for="new_nombre">Nombre *</label>
                                            <input type="text" id="new_nombre" name="nombre" required placeholder="Nombre completo">
                                        </div>
                                        <div class="form-group">
                                            <label for="new_cargo">Cargo *</label>
                                            <input type="text" id="new_cargo" name="cargo" required placeholder="Cargo o titulo">
                                        </div>
                                        <div class="form-group">
                                            <label for="new_texto">Texto de la opinion *</label>
                                            <textarea id="new_texto" name="texto" required rows="3" placeholder="Escribe la opinion..."></textarea>
                                        </div>
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                            <div class="form-group">
                                                <label for="new_imagen">Foto de perfil</label>
                                                <input type="file" id="new_imagen" name="imagen" accept="image/*">
                                            </div>
                                            <div class="form-group">
                                                <label for="new_orden">Orden</label>
                                                <input type="number" id="new_orden" name="orden" min="1" value="<?php echo count($opinionesList) + 1; ?>">
                                            </div>
                                        </div>
                                        <div style="display:flex;gap:10px;">
                                            <button type="submit" class="btn btn-success">Crear Opinion</button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleCreateOpinion()">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="panel">
                                <div class="panel-header"><h2>Opiniones actuales</h2></div>
                                <div class="panel-body">
                                    <?php if (empty($opinionesList)): ?>
                                        <p class="empty">No hay opiniones creadas aun.</p>
                                    <?php else: ?>
                                        <?php foreach ($opinionesList as $o): ?>
                                            <div class="opinion-row">
                                                <?php if (!empty($o['imagen'])): ?>
                                                    <img src="/<?php echo htmlspecialchars($o['imagen']); ?>" class="opinion-thumb" alt="">
                                                <?php else: ?>
                                                    <div class="opinion-thumb-placeholder"><?php echo htmlspecialchars(mb_substr($o['nombre'], 0, 1)); ?></div>
                                                <?php endif; ?>
                                                <div class="opinion-data">
                                                    <h4><?php echo htmlspecialchars($o['nombre']); ?></h4>
                                                    <span><?php echo htmlspecialchars($o['cargo']); ?> | Orden: <?php echo intval($o['orden']); ?></span>
                                                </div>
                                                <div class="actions">
                                                    <button class="btn btn-sm btn-primary" onclick="toggleOpinionEdit(<?php echo $o['id']; ?>)">Editar</button>
                                                    <form class="delete-form" method="POST" data-confirm="Eliminar esta opinion?" style="display:inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="edit-form" id="edit-opinion-<?php echo $o['id']; ?>">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                                                    <div class="form-group">
                                                        <label>Nombre</label>
                                                        <input type="text" name="nombre" required value="<?php echo htmlspecialchars($o['nombre']); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Cargo</label>
                                                        <input type="text" name="cargo" required value="<?php echo htmlspecialchars($o['cargo']); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Texto</label>
                                                        <textarea name="texto" required rows="3"><?php echo htmlspecialchars($o['texto']); ?></textarea>
                                                    </div>
                                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                                        <div class="form-group">
                                                            <label>Foto (dejar vacio para mantener)</label>
                                                            <input type="file" name="imagen" accept="image/*">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Orden</label>
                                                            <input type="number" name="orden" min="1" value="<?php echo intval($o['orden']); ?>">
                                                        </div>
                                                    </div>
                                                    <div style="display:flex;gap:10px;">
                                                        <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleOpinionEdit(<?php echo $o['id']; ?>)">Cancelar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php endif; ?>

                <?php elseif ($action === 'upload' && $tab === 'portada'): ?>
                    <!-- PORTADA UPLOAD -->
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="upload">
                            <div class="form-group">
                                <label for="titulo">Título (opcional, se aplica a todas)</label>
                                <input type="text" id="titulo" name="titulo">
                            </div>
                            <div class="form-group">
                                <label for="imagenes">Imágenes (seleccione varias)</label>
                                <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple required>
                                <small>Se guardan en uploads/sliders/. Máx 5MB cada una.</small>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Subir</button>
                                <a href="?tab=portada" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($action === 'edit' && $tab === 'portada' && $slider): ?>
                    <!-- PORTADA EDIT -->
                    <div class="form-card">
                        <div style="margin-bottom:20px;">
                            <img src="../<?php echo htmlspecialchars($slider['imagen']); ?>" style="max-width:400px; border-radius:8px;">
                        </div>
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $slider['id']; ?>">
                            <div class="form-group">
                                <label for="titulo">Título</label>
                                <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($slider['titulo'] ?? ''); ?>">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?tab=portada" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
    // --- Opinion inline toggles (globales para onclick) ---
    function toggleOpinionEdit(id) { var form = document.getElementById('edit-opinion-' + id); if (form) form.style.display = form.style.display === 'block' ? 'none' : 'block'; }
    function toggleCreateOpinion() { var form = document.getElementById('create-opinion-form'); if (form) form.style.display = form.style.display === 'block' ? 'none' : 'block'; }

    (function() {
        // --- Slider drag-reorder ---
        var grid = document.getElementById('sliderGrid');
        if (grid) {
            var dragItem = null;
            var csrf = document.querySelector('input[name="csrf_token"]').value;
            grid.addEventListener('dragstart', function(e) {
                var target = e.target.closest('.slider-item');
                if (!target) return;
                dragItem = target;
                target.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            grid.addEventListener('dragover', function(e) {
                e.preventDefault();
                var target = e.target.closest('.slider-item');
                if (!target || target === dragItem) return;
                var rect = target.getBoundingClientRect();
                if (e.clientY < rect.top + rect.height / 2) { grid.insertBefore(dragItem, target); }
                else { grid.insertBefore(dragItem, target.nextSibling); }
            });
            grid.addEventListener('dragend', function() {
                if (!dragItem) return;
                dragItem.classList.remove('dragging');
                var items = grid.querySelectorAll('.slider-item');
                var order = [];
                items.forEach(function(item) { order.push(item.dataset.id); });
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '?action=reorder&tab=portada';
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = '<?php echo CSRF_TOKEN_NAME; ?>';
                inp.value = csrf;
                form.appendChild(inp);
                order.forEach(function(id) {
                    var o = document.createElement('input');
                    o.type = 'hidden';
                    o.name = 'order[]';
                    o.value = id;
                    form.appendChild(o);
                });
                document.body.appendChild(form);
                form.submit();
                dragItem = null;
            });
            grid.querySelectorAll('.slider-item').forEach(function(el) { el.draggable = true; });
        }

        // --- Counter inline edit toggles ---
        var toggleBtns = document.querySelectorAll('[data-toggle-edit]');
        toggleBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.toggleEdit;
                var form = document.getElementById('edit-' + id);
                if (form) form.classList.toggle('active');
            });
        });

        // --- Counter drag-reorder ---
        var counterList = document.getElementById('counterList');
        if (counterList) {
            var dragCounter = null;
            var csrf2 = document.querySelector('input[name="csrf_token"]').value;
            counterList.addEventListener('dragstart', function(e) {
                var row = e.target.closest('.counter-row');
                if (!row) return;
                dragCounter = row;
                row.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            counterList.addEventListener('dragover', function(e) {
                e.preventDefault();
                var row = e.target.closest('.counter-row');
                if (!row || row === dragCounter) return;
                var rect = row.getBoundingClientRect();
                if (e.clientY < rect.top + rect.height / 2) { counterList.insertBefore(dragCounter, row); }
                else { counterList.insertBefore(dragCounter, row.nextSibling); }
            });
            counterList.addEventListener('dragend', function() {
                if (!dragCounter) return;
                dragCounter.classList.remove('dragging');
                var rows = counterList.querySelectorAll('.counter-row');
                var order = [];
                rows.forEach(function(r) { order.push(r.dataset.id); });
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '?tab=contadores';
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = '<?php echo CSRF_TOKEN_NAME; ?>';
                inp.value = csrf2;
                form.appendChild(inp);
                var act = document.createElement('input');
                act.type = 'hidden';
                act.name = 'action';
                act.value = 'reorder';
                form.appendChild(act);
                order.forEach(function(id) {
                    var o = document.createElement('input');
                    o.type = 'hidden';
                    o.name = 'order[]';
                    o.value = id;
                    form.appendChild(o);
                });
                document.body.appendChild(form);
                form.submit();
                dragCounter = null;
            });
            counterList.querySelectorAll('.counter-row').forEach(function(el) { el.draggable = true; });
        }
    })();
    </script>
</body>
</html>
