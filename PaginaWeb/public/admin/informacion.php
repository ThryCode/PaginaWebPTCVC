<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/admin_error.log');

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

require_once 'includes/helpers.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'noticias';
$message = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'creado') $message = 'Creado correctamente.';
    elseif ($msg === 'editado') $message = 'Actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Eliminado correctamente.';
    elseif ($msg === 'stats_guardados') $message = 'Estadísticas guardadas correctamente.';
}

$categorias = Storage::read('categorias');

$areas = array('Biotecnología', 'TIC', 'Energía', 'Industria', 'Agricultura', 'Medio Ambiente', 'Salud', 'Educación', 'Otros');
$estados = array('en ejecución', 'finalizado', 'en desarrollo', 'propuesta');
$catProyectos = array('Fuentes renovables de energía', 'Producción de alimentos', 'Automatización e Inteligencia Artificial', 'Producción de materiales de la construcción', 'Incubación de nuevas empresas');

// === POST HANDLER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        // --- DELETE (noticias/eventos/galeria/proyectos) ---
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                if ($tab === 'galeria') {
                    $existing = Storage::findById('galeria', $deleteId);
                    if ($existing && isset($existing['imagen'])) {
                        $imgPath = '../' . ltrim($existing['imagen'], '/');
                        if (strpos($imgPath, '/../') === false && strpos($imgPath, '..\\') === false && file_exists($imgPath)) unlink($imgPath);
                    }
                    Storage::delete('galeria', $deleteId);
                } elseif ($tab === 'proyectos') {
                    $existing = Storage::findById('proyectos', $deleteId);
                    if ($existing) {
                        $imgs = array();
                        if (!empty($existing['imagenes']) && is_array($existing['imagenes'])) $imgs = $existing['imagenes'];
                        elseif (!empty($existing['imagen'])) $imgs = array($existing['imagen']);
                        foreach ($imgs as $img) { $clean = ltrim($img, '/'); if (strpos($clean, '..') === false) { $imgPath = '../' . $clean; if (file_exists($imgPath)) unlink($imgPath); } }
                        deleteFolder(getProyectoFolder($deleteId));
                    }
                    Storage::delete('proyectos', $deleteId);
                } else {
                    $existing = Storage::findById('noticias', $deleteId);
                    if ($existing) {
                        $allImages = array();
                        if (isset($existing['imagenes']) && is_array($existing['imagenes'])) $allImages = $existing['imagenes'];
                        elseif (isset($existing['imagen'])) $allImages = array($existing['imagen']);
                        foreach ($allImages as $img) { $clean = ltrim($img, '/'); if (strpos($clean, '..') === false) { $imgPath = '../' . $clean; if (file_exists($imgPath)) unlink($imgPath); } }
                        $folder = ($existing['tipo'] === 'evento') ? getEventoFolder($deleteId) : getNoticiaFolder($deleteId);
                        deleteFolder($folder);
                    }
                    Storage::delete('noticias', $deleteId);
                }
                header('Location: informacion.php?tab=' . $tab . '&msg=eliminado');
                exit;
            }
        }
        // --- DELETE GROUP (galeria - elimina todas las imágenes de un carrusel) ---
        if ($action === 'delete_group' && $tab === 'galeria') {
            $grupoTitulo = $_POST['grupo_titulo'] ?? '';
            if ($grupoTitulo !== '') {
                $all = Storage::read('galeria');
                foreach ($all as $item) {
                    $t = ($item['titulo'] ?: 'Sin título');
                    if ($t === $grupoTitulo) {
                        if (!empty($item['imagen'])) {
                            $imgPath = '../' . $item['imagen'];
                            if (file_exists($imgPath)) unlink($imgPath);
                        }
                        Storage::delete('galeria', $item['id']);
                    }
                }
                header('Location: informacion.php?tab=galeria&msg=eliminado');
                exit;
            }
        }
        // --- NOTICIAS / EVENTOS create/edit ---
        elseif ($tab === 'noticias' || $tab === 'eventos') {
            $titulo = mb_substr(trim($_POST['titulo'] ?? ''), 0, 100);
            $tipo = ($tab === 'eventos') ? 'evento' : 'noticia';
            $categoria_id = ($tab === 'eventos') ? 2 : 1;
            $contenido = $_POST['contenido'] ?? '';
            $resumen = autoResumen($contenido);
            $fecha_evento_date = trim($_POST['fecha_evento_date'] ?? '');
            $fecha_evento_time = ($tab === 'eventos') ? trim($_POST['fecha_evento_time'] ?? '') : '';
            $fecha_evento = ($tab === 'eventos')
                ? (($fecha_evento_date && $fecha_evento_time) ? $fecha_evento_date . ' ' . $fecha_evento_time : null)
                : ($fecha_evento_date ?: null);
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            $publicada = isset($_POST['publicada']) ? 1 : 0;
            $destacada = isset($_POST['destacada']) ? 1 : 0;

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo), '-'));
            $slug = substr($slug, 0, 280);

            $data = array(
                'titulo' => $titulo, 'slug' => $slug, 'resumen' => $resumen,
                'contenido' => $contenido, 'tipo' => $tipo,
                'categoria_id' => $categoria_id, 'autor_id' => $_SESSION['user_id'],
                'fecha_evento' => $fecha_evento, 'ubicacion' => $ubicacion,
                'publicada' => $publicada, 'destacada' => $destacada
            );

            $getFolder = ($tab === 'eventos') ? 'getEventoFolder' : 'getNoticiaFolder';
            $getFolderUrl = ($tab === 'eventos') ? 'getEventoFolderUrl' : 'getNoticiaFolderUrl';

            if ($action === 'edit' && $id > 0) {
                $existing = Storage::findById('noticias', $id);
                $imagenes = array();
                if ($existing) {
                    if (isset($existing['imagenes']) && is_array($existing['imagenes'])) $imagenes = $existing['imagenes'];
                    elseif (isset($existing['imagen'])) $imagenes = array($existing['imagen']);
                }
                if (isset($_POST['delete_imagenes']) && is_array($_POST['delete_imagenes'])) {
                    $deleteList = $_POST['delete_imagenes'];
                    $kept = array();
                    foreach ($imagenes as $img) {
                        if (!in_array($img, $deleteList)) { $kept[] = $img; }
                        else { $delPath = '../' . $img; if (file_exists($delPath)) unlink($delPath); }
                    }
                    $imagenes = $kept;
                }
                $folderDir = $getFolder($id);
                $folderUrl = $getFolderUrl($id);
                ensureFolder($folderDir);
                migrateOldImages($imagenes, $folderDir, $folderUrl);

                if (isset($_FILES['imagenes'])) {
                    $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                    $files = $_FILES['imagenes'];
                    $fileCount = is_array($files['name']) ? count($files['name']) : 0;
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                            if ($files['error'][$i] === UPLOAD_ERR_INI_SIZE || $files['error'][$i] === UPLOAD_ERR_FORM_SIZE) $error = 'Una o más imágenes exceden el tamaño máximo permitido (5MB).';
                            continue;
                        }
                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        $mime = mime_content_type($files['tmp_name'][$i]);
                        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMime) || $files['size'][$i] > 5 * 1024 * 1024) { $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).'; continue; }
                        $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        if (move_uploaded_file($files['tmp_name'][$i], $folderDir . $filename)) $imagenes[] = $folderUrl . $filename;
                    }
                }
                $data['imagenes'] = $imagenes;
                $data['imagen'] = !empty($imagenes) ? $imagenes[0] : '';
                if (empty($error)) {
                    try { Storage::update('noticias', $id, $data); header('Location: informacion.php?tab=' . $tab . '&msg=editado'); exit; }
                    catch (Exception $e) { $error = 'Error al guardar.'; }
                }
            } else {
                $data['imagenes'] = array();
                $data['imagen'] = '';
                if (empty($error)) {
                    try {
                        $saved = Storage::insert('noticias', $data);
                        $newId = $saved['id'];
                        $folderDir = $getFolder($newId);
                        $folderUrl = $getFolderUrl($newId);
                        ensureFolder($folderDir);
                        $imagenes = array();
                        if (isset($_FILES['imagenes'])) {
                            $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                            $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                            $files = $_FILES['imagenes'];
                            $fileCount = is_array($files['name']) ? count($files['name']) : 0;
                            for ($i = 0; $i < $fileCount; $i++) {
                                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                                    if ($files['error'][$i] === UPLOAD_ERR_INI_SIZE || $files['error'][$i] === UPLOAD_ERR_FORM_SIZE) $error = 'Una o más imágenes exceden el tamaño máximo permitido (5MB).';
                                    continue;
                                }
                                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                                $mime = mime_content_type($files['tmp_name'][$i]);
                                if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMime) || $files['size'][$i] > 5 * 1024 * 1024) { $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).'; continue; }
                                $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                                if (move_uploaded_file($files['tmp_name'][$i], $folderDir . $filename)) $imagenes[] = $folderUrl . $filename;
                            }
                        }
                        $saved['imagenes'] = $imagenes;
                        $saved['imagen'] = !empty($imagenes) ? $imagenes[0] : '';
                        Storage::update('noticias', $newId, $saved);
                        header('Location: informacion.php?tab=' . $tab . '&msg=creado');
                        exit;
                    } catch (Exception $e) { $error = 'Error al guardar.'; }
                }
            }
        }
        // --- GALERIA ---
        elseif ($tab === 'galeria') {
            if ($action === 'upload') {
                if (isset($_FILES['imagenes'])) {
                    $uploadDir = '../uploads/galeria/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $uploaded = 0;
                    $titulo = trim($_POST['titulo'] ?? '');
                    foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                        if ($_FILES['imagenes']['error'][$key] !== UPLOAD_ERR_OK) continue;
                        $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                        $ext = strtolower(pathinfo($_FILES['imagenes']['name'][$key], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedExts) || $_FILES['imagenes']['size'][$key] > 5 * 1024 * 1024) continue;
                        $filename = 'galeria_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                            $item = array('imagen' => 'uploads/galeria/' . $filename, 'titulo' => htmlspecialchars($titulo), 'orden' => $uploaded);
                            Storage::insert('galeria', $item);
                            $uploaded++;
                        }
                    }
                    if ($uploaded > 0) $message = "$uploaded imagen(es) subida(s) correctamente.";
                    else $error = 'No se pudieron subir las imágenes.';
                }
                $action = 'list';
            } elseif ($action === 'update') {
                $updateId = intval($_POST['id'] ?? 0);
                if ($updateId > 0) {
                    $data = array('titulo' => htmlspecialchars(trim($_POST['titulo'] ?? '')));
                    Storage::update('galeria', $updateId, $data);
                    $message = 'Imagen actualizada.';
                }
                $action = 'list';
            }
        }
        // --- PROYECTOS ---
        elseif ($tab === 'proyectos') {
            if (isset($_POST['stat_action'])) {
                $stats = Storage::read('proyectos_stats');
                $statAction = $_POST['stat_action'];

                if ($statAction === 'create') {
                    $newId = 1;
                    foreach ($stats as $s) { if (($s['id'] ?? 0) >= $newId) $newId = $s['id'] + 1; }
                    $stats[] = array('id' => $newId, 'label' => trim($_POST['stat_label'] ?? ''), 'count' => intval($_POST['stat_count'] ?? 0));
                    Storage::write('proyectos_stats', $stats);
                    header('Location: informacion.php?tab=proyectos&msg=creado');
                    exit;

                } elseif ($statAction === 'update') {
                    $updateId = intval($_POST['stat_id'] ?? 0);
                    foreach ($stats as &$s) {
                        if (($s['id'] ?? 0) === $updateId) {
                            $s['label'] = trim($_POST['stat_label'] ?? $s['label']);
                            $s['count'] = intval($_POST['stat_count'] ?? $s['count']);
                            break;
                        }
                    }
                    unset($s);
                    Storage::write('proyectos_stats', $stats);
                    header('Location: informacion.php?tab=proyectos&msg=editado');
                    exit;

                } elseif ($statAction === 'delete') {
                    $deleteId = intval($_POST['stat_id'] ?? 0);
                    $stats = array_values(array_filter($stats, function($s) use ($deleteId) { return ($s['id'] ?? 0) !== $deleteId; }));
                    Storage::write('proyectos_stats', $stats);
                    header('Location: informacion.php?tab=proyectos&msg=eliminado');
                    exit;
                }
            }

            $titulo = trim($_POST['titulo'] ?? '');
            $contenido = $_POST['contenido'] ?? '';
            $resumen = autoResumen($contenido);
            $area = trim($_POST['area'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $estado = trim($_POST['estado'] ?? '');
            $responsable = trim($_POST['responsable'] ?? '');
            $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
            $fecha_fin = trim($_POST['fecha_fin'] ?? '');
            $resultados = trim($_POST['resultados'] ?? '');
            $publicada = isset($_POST['publicada']) ? 1 : 0;
            $destacada = isset($_POST['destacada']) ? 1 : 0;

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo), '-'));
            $slug = substr($slug, 0, 280);

            $data = array(
                'titulo' => $titulo, 'slug' => $slug, 'resumen' => $resumen,
                'contenido' => $contenido, 'area' => $area, 'categoria' => $categoria,
                'estado' => $estado, 'responsable' => $responsable,
                'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin,
                'resultados' => $resultados, 'publicada' => $publicada, 'destacada' => $destacada
            );

            $imagenes = array();
            if ($action === 'edit' && $id > 0) {
                $existing = Storage::findById('proyectos', $id);
                if (!empty($existing['imagenes']) && is_array($existing['imagenes'])) $imgsExisting = $existing['imagenes'];
                elseif (!empty($existing['imagen'])) $imgsExisting = array($existing['imagen']);
                else $imgsExisting = array();
                $keepStr = $_POST['keep_imgs'] ?? '';
                $keepArr = $keepStr !== '' ? explode(',', $keepStr) : array();
                foreach ($imgsExisting as $img) {
                    if (in_array($img, $keepArr)) $imagenes[] = $img;
                    else { $delPath = '../' . $img; if (file_exists($delPath)) unlink($delPath); }
                }
                $folderDir = getProyectoFolder($id);
                $folderUrl = getProyectoFolderUrl($id);
                ensureFolder($folderDir);
                migrateOldImages($imagenes, $folderDir, $folderUrl);
            }

            if (isset($_FILES['imagenes'])) {
                $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                $files = $_FILES['imagenes'];
                $cnt = is_array($files['tmp_name']) ? count($files['tmp_name']) : 0;
                if ($action === 'edit' && $id > 0) {
                    $folderDir = getProyectoFolder($id);
                    $folderUrl = getProyectoFolderUrl($id);
                }
                for ($i = 0; $i < $cnt; $i++) {
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $mime = mime_content_type($files['tmp_name'][$i]);
                    if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMime) || $files['size'][$i] > 5 * 1024 * 1024) { $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).'; continue; }
                    $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if ($action === 'edit' && $id > 0 && move_uploaded_file($files['tmp_name'][$i], $folderDir . $filename)) {
                        $imagenes[] = $folderUrl . $filename;
                    }
                }
            }

            if (!empty($imagenes)) $data['imagenes'] = $imagenes;
            else $data['imagenes'] = array();

            if (empty($error)) {
                try {
                    if ($action === 'edit' && $id > 0) {
                        Storage::update('proyectos', $id, $data);
                        header('Location: informacion.php?tab=proyectos&msg=editado');
                        exit;
                    } else {
                        $saved = Storage::insert('proyectos', $data);
                        $newId = $saved['id'];
                        $folderDir = getProyectoFolder($newId);
                        $folderUrl = getProyectoFolderUrl($newId);
                        ensureFolder($folderDir);
                        $newImagenes = array();
                        if (isset($_FILES['imagenes'])) {
                            $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                            $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                            $files = $_FILES['imagenes'];
                            $cnt = is_array($files['tmp_name']) ? count($files['tmp_name']) : 0;
                            for ($i = 0; $i < $cnt; $i++) {
                                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                                $mime = mime_content_type($files['tmp_name'][$i]);
                                if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMime) || $files['size'][$i] > 5 * 1024 * 1024) { $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).'; continue; }
                                $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                                if (move_uploaded_file($files['tmp_name'][$i], $folderDir . $filename)) $newImagenes[] = $folderUrl . $filename;
                            }
                        }
                        $saved['imagenes'] = $newImagenes;
                        $saved['imagen'] = !empty($newImagenes) ? $newImagenes[0] : '';
                        Storage::update('proyectos', $newId, $saved);
                        header('Location: informacion.php?tab=proyectos&msg=creado');
                        exit;
                    }
                } catch (Exception $e) { $error = 'Error al guardar.'; }
            }
        }
    }
}

// === LOAD DATA ===
$noticia = null;
$evento = null;
$imagen = null;
$proyecto = null;
$proyectosStats = Storage::read('proyectos_stats');

if ($action === 'edit' && $id > 0) {
    if ($tab === 'galeria') {
        $imagen = Storage::findById('galeria', $id);
        if (!$imagen) { $action = 'list'; $error = 'Imagen no encontrada.'; }
    } elseif ($tab === 'proyectos') {
        $proyecto = Storage::findById('proyectos', $id);
        if (!$proyecto) { $action = 'list'; $error = 'Proyecto no encontrado.'; }
    } else {
        $noticia = Storage::findById('noticias', $id);
        if (!$noticia || $noticia['tipo'] !== (($tab === 'eventos') ? 'evento' : 'noticia')) {
            $action = 'list';
            $error = 'Publicación no encontrada.';
        }
    }
}

$noticiasList = array();
$eventosList = array();
$galeriaList = array();
$proyectosList = array();

if ($action === 'list') {
    if ($tab === 'galeria') {
        $galeriaList = Storage::read('galeria');
        usort($galeriaList, function($a, $b) { return ($a['orden'] ?? 0) - ($b['orden'] ?? 0); });
    } elseif ($tab === 'proyectos') {
        $proyectosList = Storage::read('proyectos');
        usort($proyectosList, function($a, $b) { return strcmp($b['created_at'], $a['created_at']); });
    } else {
        $allNoticias = Storage::read('noticias');
        $targetTipo = ($tab === 'eventos') ? 'evento' : 'noticia';
        foreach ($allNoticias as $n) {
            if (isset($n['tipo']) && $n['tipo'] === $targetTipo) {
                if ($tab === 'eventos') $eventosList[] = $n;
                else $noticiasList[] = $n;
            }
        }
        usort($noticiasList, function($a, $b) { return strcmp($b['created_at'], $a['created_at']); });
        usort($eventosList, function($a, $b) { return strcmp($b['created_at'], $a['created_at']); });
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=1, user-scalable=no';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Información</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <style>
        .tabs-nav { display:flex; gap:0; margin-bottom:20px; border-bottom:2px solid #e0e0e0; }
        .tabs-nav a { padding:10px 24px; text-decoration:none; color:#666; font-weight:600; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
        .tabs-nav a:hover { color:#004966; }
        .tabs-nav a.active { color:#00A0E1; border-bottom-color:#00A0E1; }
        .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:15px; margin-top:20px; }
        .gallery-item { background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
        .gallery-item img { width:100%; height:150px; object-fit:cover; }
        .gallery-item-info { padding:10px; }
        .gallery-item-info h4 { font-size:0.85rem; margin-bottom:5px; }
        .gallery-item-actions { display:flex; gap:5px; padding:0 10px 10px; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php
                    $tabTitles = array('noticias'=>'Noticias', 'eventos'=>'Eventos', 'galeria'=>'Galería', 'proyectos'=>'Proyectos');
                    $listTitle = $tabTitles[$tab] ?? 'Información';
                    if ($action === 'list') { echo $listTitle; }
                    else {
                        $typeLabels = array('galeria' => 'Elemento', 'eventos' => 'Evento', 'proyectos' => 'Proyecto');
                        echo ($action === 'edit' ? 'Editar ' : 'Nuevo ') . ($typeLabels[$tab] ?? 'Publicación');
                    }
                ?></h1>
                <?php if ($action === 'list' && ($tab === 'noticias' || $tab === 'eventos' || $tab === 'proyectos')): ?>
                    <a href="?action=new&tab=<?php echo $tab; ?>" class="btn btn-primary">+ Nuevo</a>
                <?php elseif ($action === 'list' && $tab === 'galeria'): ?>
                    <a href="?action=upload&tab=galeria" class="btn btn-primary">+ Subir</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <!-- TABS NAV -->
                    <div class="tabs-nav">
                        <a href="?tab=noticias" class="<?php echo $tab === 'noticias' ? 'active' : ''; ?>">Noticias</a>
                        <a href="?tab=eventos" class="<?php echo $tab === 'eventos' ? 'active' : ''; ?>">Eventos</a>
                        <a href="?tab=galeria" class="<?php echo $tab === 'galeria' ? 'active' : ''; ?>">Galería</a>
                        <a href="?tab=proyectos" class="<?php echo $tab === 'proyectos' ? 'active' : ''; ?>">Proyectos</a>
                    </div>

                    <!-- NOTICIAS LIST -->
                    <?php if ($tab === 'noticias'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($noticiasList)): ?>
                            <p class="empty">No hay publicaciones.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Título</th><th>Tipo</th><th>Categoría</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($noticiasList as $n): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($n['titulo'] ?? 'Sin título', 0, 50)); ?></td>
                                    <td><span class="tag tag-<?php echo preg_replace('/[^a-zA-Z0-9_-]/', '', $n['tipo']); ?>"><?php echo htmlspecialchars(ucfirst($n['tipo'])); ?></span></td>
                                    <td><?php
                                        $catName = '—';
                                        foreach ($categorias as $cat) { if ($cat['id'] == $n['categoria_id']) { $catName = htmlspecialchars($cat['nombre']); break; } }
                                        echo $catName;
                                    ?></td>
                                    <td><span class="tag tag-<?php echo $n['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $n['publicada'] ? 'Publicado' : 'Borrador'; ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($n['fecha_evento'] ?? $n['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $n['id']; ?>&tab=noticias" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete&tab=noticias" data-confirm="¿Eliminar esta publicación?" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $n['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div></div>

                    <!-- EVENTOS LIST -->
                    <?php elseif ($tab === 'eventos'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($eventosList)): ?>
                            <p class="empty">No hay eventos.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Título</th><th>Fecha</th><th>Ubicación</th><th>Estado</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($eventosList as $e): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($e['titulo'] ?? 'Sin título', 0, 50)); ?></td>
                                    <td><?php echo $e['fecha_evento'] ? date('d/m/Y H:i', strtotime($e['fecha_evento'])) : '—'; ?></td>
                                    <td><?php echo htmlspecialchars($e['ubicacion'] ?: '—'); ?></td>
                                    <td><span class="tag tag-<?php echo $e['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $e['publicada'] ? 'Publicado' : 'Borrador'; ?></span></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $e['id']; ?>&tab=eventos" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete&tab=eventos" data-confirm="¿Eliminar este evento?" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div></div>

                    <!-- GALERIA LIST -->
                    <?php elseif ($tab === 'galeria'): ?>
                        <?php if (empty($galeriaList)): ?>
                            <p class="empty">No hay imágenes en la galería.</p>
                        <?php else: ?>
                            <?php
                            $groups = array();
                            foreach ($galeriaList as $img) {
                                $t = $img['titulo'] ?: 'Sin título';
                                if (!isset($groups[$t])) $groups[$t] = array();
                                $groups[$t][] = $img;
                            }
                            ?>
                            <div class="gallery-grid">
                                <?php foreach ($groups as $titulo => $imgs): ?>
                                <div class="gallery-item">
                                    <img src="/<?php echo htmlspecialchars($imgs[0]['imagen']); ?>" alt="<?php echo htmlspecialchars($titulo); ?>">
                                    <div class="gallery-item-info">
                                        <h4><?php echo htmlspecialchars($titulo); ?></h4>
                                        <small><?php echo count($imgs); ?> imagen(es)</small>
                                    </div>
                                    <div class="gallery-item-actions">
                                        <a href="?action=edit&id=<?php echo $imgs[0]['id']; ?>&tab=galeria" class="btn btn-sm btn-primary">Editar</a>
                                        <?php if (count($imgs) > 1): ?>
                                        <form class="delete-form" method="POST" action="?action=delete_group&tab=galeria" data-confirm="¿Eliminar todo el carrusel &ldquo;<?php echo htmlspecialchars($titulo); ?>&rdquo; y sus <?php echo count($imgs); ?> imágenes?" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="grupo_titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar carrusel completo">Eliminar carrusel</button>
                                        </form>
                                        <?php else: ?>
                                        <form class="delete-form" method="POST" action="?action=delete&tab=galeria" data-confirm="¿Eliminar esta imagen?" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $imgs[0]['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar imagen">X</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <!-- PROYECTOS LIST -->
                    <?php elseif ($tab === 'proyectos'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($proyectosList)): ?>
                            <p class="empty">No hay proyectos.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Título</th><th>Categoría</th><th>Área</th><th>Estado</th><th>Responsable</th><th>Inicio</th><th>Img</th><th>Pub.</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($proyectosList as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($p['titulo'] ?? 'Sin título', 0, 50)); ?></td>
                                    <td><span class="tag tag-<?php echo !empty($p['categoria']) ? 'publicado' : 'borrador'; ?>"><?php echo htmlspecialchars(($p['categoria'] ?? '—')); ?></span></td>
                                    <td><?php echo htmlspecialchars($p['area'] ?: '—'); ?></td>
                                    <td><span class="tag tag-<?php echo $p['estado'] === 'finalizado' ? 'publicado' : 'borrador'; ?>"><?php echo htmlspecialchars(ucfirst($p['estado'] ?: 'propuesta')); ?></span></td>
                                    <td><?php echo htmlspecialchars($p['responsable'] ?: '—'); ?></td>
                                    <td><?php echo $p['fecha_inicio'] ? date('d/m/Y', strtotime($p['fecha_inicio'])) : '—'; ?></td>
                                    <td><?php
                                        $imgCnt = 0;
                                        if (!empty($p['imagenes']) && is_array($p['imagenes'])) $imgCnt = count($p['imagenes']);
                                        elseif (!empty($p['imagen'])) $imgCnt = 1;
                                        echo $imgCnt > 0 ? $imgCnt : '—';
                                    ?></td>
                                    <td><span class="tag tag-<?php echo $p['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $p['publicada'] ? 'Sí' : 'No'; ?></span></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>&tab=proyectos" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete&tab=proyectos" data-confirm="¿Eliminar este proyecto?" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div></div>

                    <?php if (!empty($proyectosStats)): ?>
                    <h3 style="margin-top:32px;margin-bottom:16px;color:#1a1a2e;">Vista previa</h3>
                    <div class="stats-grid">
                        <?php foreach ($proyectosStats as $s): ?>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo intval($s['count']); ?></h3>
                                <p><?php echo htmlspecialchars($s['label']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="panel" style="margin-top:30px;">
                        <div class="panel-header"><h2>Estadísticas</h2></div>
                        <div class="panel-body">
                            <div class="grid-2">
                                <div class="panel" style="box-shadow:none;padding:0;">
                                    <h3 style="color:#004966;font-size:1rem;margin-bottom:12px;">Añadir estadística</h3>
                                    <form method="POST">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="stat_action" value="create">
                                        <div class="form-group">
                                            <label for="stat_label">Etiqueta</label>
                                            <input type="text" id="stat_label" name="stat_label" required placeholder="Ej: Proyectos exitosos">
                                        </div>
                                        <div class="form-group">
                                            <label for="stat_count">Valor</label>
                                            <input type="number" id="stat_count" name="stat_count" required min="0" value="0">
                                        </div>
                                        <button type="submit" class="btn btn-success">Crear</button>
                                    </form>
                                </div>

                                <div>
                                    <h3 style="color:#004966;font-size:1rem;margin-bottom:12px;">Estadísticas actuales</h3>
                                    <?php if (empty($proyectosStats)): ?>
                                        <p class="empty">No hay estadísticas.</p>
                                    <?php else: ?>
                                        <?php foreach ($proyectosStats as $s): ?>
                                        <div class="counter-row" style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#f8f9fa;border-radius:10px;margin-bottom:8px;">
                                            <div style="flex:1;">
                                                <strong style="color:#1a1a2e;"><?php echo htmlspecialchars($s['label']); ?></strong>
                                                <span style="color:#888;font-size:0.85rem;margin-left:8px;">Valor: <?php echo intval($s['count']); ?></span>
                                            </div>
                                            <div style="display:flex;gap:6px;">
                                                <button class="btn btn-sm btn-primary" data-toggle-edit-stat="<?php echo $s['id']; ?>">Editar</button>
                                                <form method="POST" data-confirm="Eliminar esta estadística?" style="display:inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="stat_action" value="delete">
                                                    <input type="hidden" name="stat_id" value="<?php echo $s['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">X</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="edit-form" id="edit-stat-<?php echo $s['id']; ?>" style="background:#fff;padding:16px;border-radius:10px;border:2px dashed #e0e0e0;display:none;margin-bottom:12px;">
                                            <form method="POST">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="stat_action" value="update">
                                                <input type="hidden" name="stat_id" value="<?php echo $s['id']; ?>">
                                                <div class="form-group">
                                                    <label>Etiqueta</label>
                                                    <input type="text" name="stat_label" required value="<?php echo htmlspecialchars($s['label']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Valor</label>
                                                    <input type="number" name="stat_count" required min="0" value="<?php echo intval($s['count']); ?>">
                                                </div>
                                                <div style="display:flex;gap:10px;">
                                                    <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" data-toggle-edit-stat="<?php echo $s['id']; ?>">Cancelar</button>
                                                </div>
                                            </form>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                    (function() {
                        var btns = document.querySelectorAll('[data-toggle-edit-stat]');
                        btns.forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var id = this.dataset.toggleEditStat;
                                var form = document.getElementById('edit-stat-' + id);
                                if (form) form.style.display = form.style.display === 'block' ? 'none' : 'block';
                            });
                        });
                    })();
                    </script>
                    <?php endif; ?>

                <?php elseif ($action === 'upload' && $tab === 'galeria'): ?>
                    <!-- GALERIA UPLOAD -->
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="upload">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="titulo">Título del grupo</label>
                                    <input type="text" id="titulo" name="titulo" required placeholder="Ej: Feria de Ciencia 2026">
                                    <small>Todas las imágenes subidas compartirán este título y se mostrarán como carrusel.</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="imagenes">Imágenes (seleccione varias)</label>
                                <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple required>
                                <small>Puede seleccionar múltiples imágenes. Máx 5MB cada una.</small>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Subir</button>
                                <a href="?tab=galeria" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($action === 'edit' && $tab === 'galeria' && $imagen): ?>
                    <!-- GALERIA EDIT -->
                    <div class="form-card">
                        <div style="margin-bottom:20px;">
                            <img src="/<?php echo htmlspecialchars($imagen['imagen']); ?>" style="max-width:300px; border-radius:8px;">
                        </div>
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $imagen['id']; ?>">
                            <div class="form-group">
                                <label for="titulo">Título del grupo</label>
                                <input type="text" id="titulo" name="titulo" required value="<?php echo htmlspecialchars($imagen['titulo'] ?? ''); ?>">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?tab=galeria" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($action === 'new' || $action === 'edit'): ?>
                    <!-- NOTICIAS / EVENTOS FORM -->
                    <?php if ($tab === 'proyectos'): ?>
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="titulo">Título *</label>
                                    <input type="text" id="titulo" name="titulo" required value="<?php echo $proyecto ? htmlspecialchars($proyecto['titulo']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="imagenes">Imágenes</label>
                                    <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>
                                    <small style="display:block;margin-top:6px;color:#666;">Selecciona varias imágenes. Se mostrarán en un carrusel.</small>
                                    <?php
                                    $imgsMostrar = array();
                                    if ($proyecto) {
                                        if (!empty($proyecto['imagenes']) && is_array($proyecto['imagenes'])) $imgsMostrar = $proyecto['imagenes'];
                                        elseif (!empty($proyecto['imagen'])) $imgsMostrar = array($proyecto['imagen']);
                                    }
                                    if (!empty($imgsMostrar)): ?>
                                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">
                                        <?php foreach ($imgsMostrar as $img): ?>
                                        <div style="position:relative;width:100px;height:80px;border:2px solid #ddd;border-radius:8px;overflow:hidden;">
                                            <img src="/<?php echo htmlspecialchars($img); ?>" style="width:100%;height:100%;object-fit:cover;">
                                            <label style="position:absolute;top:2px;right:2px;background:rgba(255,255,255,0.9);border-radius:4px;padding:1px 4px;font-size:11px;cursor:pointer;">
                                                <input type="checkbox" onchange="actualizarKeep()" data-img="<?php echo htmlspecialchars($img); ?>" checked> X
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="keep_imgs" id="keep_imgs" value="<?php echo htmlspecialchars(implode(',', $imgsMostrar)); ?>">
                                    <script>
                                    function actualizarKeep() {
                                        var checks = document.querySelectorAll('[data-img]');
                                        var keep = [];
                                        checks.forEach(function(c) {
                                            if (c.checked) keep.push(c.getAttribute('data-img'));
                                        });
                                        document.getElementById('keep_imgs').value = keep.join(',');
                                    }
                                    </script>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="area">Área temática *</label>
                                    <select id="area" name="area" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($areas as $a): ?>
                                        <option value="<?php echo $a; ?>" <?php echo ($proyecto && $proyecto['area'] === $a) ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="categoria">Categoría *</label>
                                    <select id="categoria" name="categoria" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($catProyectos as $c): ?>
                                        <option value="<?php echo $c; ?>" <?php echo ($proyecto && ($proyecto['categoria'] ?? '') === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="estado">Estado *</label>
                                    <select id="estado" name="estado" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($estados as $e): ?>
                                        <option value="<?php echo $e; ?>" <?php echo ($proyecto && $proyecto['estado'] === $e) ? 'selected' : ''; ?>><?php echo ucfirst($e); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de inicio *</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo $proyecto ? ($proyecto['fecha_inicio'] ?? '') : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha de finalización</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $proyecto ? ($proyecto['fecha_fin'] ?? '') : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="responsable">Responsable</label>
                                <input type="text" id="responsable" name="responsable" value="<?php echo $proyecto ? htmlspecialchars($proyecto['responsable'] ?? '') : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="contenido">Descripción completa *</label>
                                <textarea id="contenido" name="contenido" rows="10" required><?php echo $proyecto ? htmlspecialchars($proyecto['contenido']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="resultados">Resultados alcanzados</label>
                                <textarea id="resultados" name="resultados" rows="4"><?php echo $proyecto ? htmlspecialchars($proyecto['resultados'] ?? '') : ''; ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><input type="checkbox" name="publicada" value="1" <?php echo (!$proyecto || $proyecto['publicada']) ? 'checked' : ''; ?>> Publicado</label>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" name="destacada" value="1" <?php echo ($proyecto && $proyecto['destacada']) ? 'checked' : ''; ?>> Destacado</label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?tab=proyectos" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <?php $isEvento = ($tab === 'eventos'); ?>
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="titulo">Título *</label>
                                    <input type="text" id="titulo" name="titulo" required maxlength="100" value="<?php echo $noticia ? htmlspecialchars($noticia['titulo']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Fotos</label>
                                    <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>
                                    <small>Puede seleccionar varias fotos. Máx 5MB cada una.</small>
                                    <?php if ($noticia): 
                                        $existingImages = array();
                                        if (isset($noticia['imagenes']) && is_array($noticia['imagenes'])) $existingImages = $noticia['imagenes'];
                                        elseif (isset($noticia['imagen'])) $existingImages = array($noticia['imagen']);
                                        if (!empty($existingImages)): ?>
                                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">
                                        <?php foreach ($existingImages as $ei): ?>
                                            <div style="position:relative;width:100px;height:80px;border-radius:8px;overflow:hidden;border:2px solid #E6F4FA;">
                                                <img src="/<?php echo htmlspecialchars($ei); ?>" style="width:100%;height:100%;object-fit:cover;">
                                                <label style="position:absolute;bottom:2px;left:2px;background:rgba(192,57,43,0.85);color:#fff;padding:2px 6px;border-radius:4px;font-size:0.7rem;cursor:pointer;">
                                                    <input type="checkbox" name="delete_imagenes[]" value="<?php echo $ei; ?>" data-delete-img> Eliminar
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_evento_date"><?php echo $isEvento ? 'Fecha del Evento *' : 'Fecha'; ?></label>
                                    <input type="date" id="fecha_evento_date" name="fecha_evento_date" <?php echo $isEvento ? 'required' : ''; ?> value="<?php echo ($noticia && $noticia['fecha_evento']) ? date('Y-m-d', strtotime($noticia['fecha_evento'])) : ''; ?>">
                                    <?php if (!$isEvento): ?><small>Si no se especifica, se usa la fecha de publicación.</small><?php endif; ?>
                                </div>
                                <?php if ($isEvento): ?>
                                <div class="form-group">
                                    <label for="fecha_evento_time">Hora del Evento *</label>
                                    <input type="time" id="fecha_evento_time" name="fecha_evento_time" required value="<?php echo ($noticia && $noticia['fecha_evento']) ? date('H:i', strtotime($noticia['fecha_evento'])) : ''; ?>">
                                </div>
                                <?php else: ?>
                                <div class="form-group"></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" value="<?php echo $noticia ? htmlspecialchars($noticia['ubicacion']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="contenido">Contenido completo</label>
                                <textarea id="contenido" name="contenido" rows="10"><?php echo $noticia ? htmlspecialchars($noticia['contenido'] ?? '') : ''; ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><input type="checkbox" name="publicada" value="1" <?php echo (!$noticia || $noticia['publicada']) ? 'checked' : ''; ?>> Publicada</label>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" name="destacada" value="1" <?php echo ($noticia && $noticia['destacada']) ? 'checked' : ''; ?>> Destacada</label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?tab=<?php echo $tab; ?>" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
