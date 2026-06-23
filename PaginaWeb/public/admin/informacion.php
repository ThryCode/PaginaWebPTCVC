<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../data/admin_error.log');

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

function autoResumen($texto, $max = 200) {
    $limpio = trim(strip_tags($texto));
    if (mb_strlen($limpio) <= $max) return $limpio;
    return mb_substr($limpio, 0, $max) . '…';
}

function getNoticiaFolder($id) { return '../uploads/noticia_' . $id . '/'; }
function getNoticiaFolderUrl($id) { return 'uploads/noticia_' . $id . '/'; }
function getEventoFolder($id) { return '../uploads/evento_' . $id . '/'; }
function getEventoFolderUrl($id) { return 'uploads/evento_' . $id . '/'; }
function ensureFolder($dir) { if (!is_dir($dir)) { mkdir($dir, 0755, true); } }
function deleteFolder($dir) {
    if (!is_dir($dir)) return;
    $files = glob($dir . '*');
    if ($files) { foreach ($files as $f) { if (is_file($f)) unlink($f); } }
    rmdir($dir);
}
function migrateOldImages(&$imagenes, $folderDir, $folderUrl) {
    $result = array();
    foreach ($imagenes as $img) {
        if (strpos($img, $folderUrl) === 0) { $result[] = $img; }
        elseif (preg_match('#^uploads/[^/]+$#', $img)) {
            $oldFile = '../' . $img;
            if (file_exists($oldFile)) {
                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (copy($oldFile, $folderDir . $newName)) { unlink($oldFile); $result[] = $folderUrl . $newName; }
                else { $result[] = $img; }
            } else { $result[] = $img; }
        } else { $result[] = $img; }
    }
    $imagenes = $result;
}

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
}

$categorias = Storage::read('categorias');

// === POST HANDLER ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        // --- DELETE (noticias/eventos/galeria) ---
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                if ($tab === 'galeria') {
                    $existing = Storage::findById('galeria', $deleteId);
                    if ($existing && isset($existing['imagen'])) {
                        $imgPath = '../' . $existing['imagen'];
                        if (file_exists($imgPath)) unlink($imgPath);
                    }
                    Storage::delete('galeria', $deleteId);
                } else {
                    $existing = Storage::findById('noticias', $deleteId);
                    if ($existing) {
                        $allImages = array();
                        if (isset($existing['imagenes']) && is_array($existing['imagenes'])) $allImages = $existing['imagenes'];
                        elseif (isset($existing['imagen'])) $allImages = array($existing['imagen']);
                        foreach ($allImages as $img) { $imgPath = '../' . $img; if (file_exists($imgPath)) unlink($imgPath); }
                        $folder = ($existing['tipo'] === 'evento') ? getEventoFolder($deleteId) : getNoticiaFolder($deleteId);
                        deleteFolder($folder);
                    }
                    Storage::delete('noticias', $deleteId);
                }
                header('Location: informacion.php?tab=' . $tab . '&msg=eliminado');
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
                    $files = $_FILES['imagenes'];
                    $fileCount = is_array($files['name']) ? count($files['name']) : 0;
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                            if ($files['error'][$i] === UPLOAD_ERR_INI_SIZE || $files['error'][$i] === UPLOAD_ERR_FORM_SIZE) $error = 'Una o más imágenes exceden el tamaño máximo permitido (5MB).';
                            continue;
                        }
                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedExts) || $files['size'][$i] > 5 * 1024 * 1024) { $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).'; continue; }
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
                            $files = $_FILES['imagenes'];
                            $fileCount = is_array($files['name']) ? count($files['name']) : 0;
                            for ($i = 0; $i < $fileCount; $i++) {
                                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                                    if ($files['error'][$i] === UPLOAD_ERR_INI_SIZE || $files['error'][$i] === UPLOAD_ERR_FORM_SIZE) $error = 'Una o más imágenes exceden el tamaño máximo permitido (5MB).';
                                    continue;
                                }
                                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                                if (!in_array($ext, $allowedExts) || $files['size'][$i] > 5 * 1024 * 1024) { $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).'; continue; }
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
                    $descripcion = trim($_POST['descripcion'] ?? '');
                    foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                        if ($_FILES['imagenes']['error'][$key] !== UPLOAD_ERR_OK) continue;
                        $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                        $ext = strtolower(pathinfo($_FILES['imagenes']['name'][$key], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedExts) || $_FILES['imagenes']['size'][$key] > 5 * 1024 * 1024) continue;
                        $filename = 'galeria_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                            $item = array('imagen' => 'uploads/galeria/' . $filename, 'titulo' => htmlspecialchars($titulo), 'descripcion' => htmlspecialchars($descripcion), 'orden' => $uploaded);
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
                    $data = array('titulo' => htmlspecialchars(trim($_POST['titulo'] ?? '')), 'descripcion' => htmlspecialchars(trim($_POST['descripcion'] ?? '')));
                    Storage::update('galeria', $updateId, $data);
                    $message = 'Imagen actualizada.';
                }
                $action = 'list';
            }
        }
    }
}

// === LOAD DATA ===
$noticia = null;
$evento = null;
$imagen = null;

if ($action === 'edit' && $id > 0) {
    if ($tab === 'galeria') {
        $imagen = Storage::findById('galeria', $id);
        if (!$imagen) { $action = 'list'; $error = 'Imagen no encontrada.'; }
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

if ($action === 'list') {
    if ($tab === 'galeria') {
        $galeriaList = Storage::read('galeria');
        usort($galeriaList, function($a, $b) { return ($a['orden'] ?? 0) - ($b['orden'] ?? 0); });
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
    <link rel="stylesheet" href="css/admin.css">
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
                    $tabTitles = array('noticias'=>'Noticias', 'eventos'=>'Eventos', 'galeria'=>'Galería');
                    $listTitle = $tabTitles[$tab] ?? 'Información';
                    if ($action === 'list') { echo $listTitle; }
                    else { echo ($action === 'edit' ? 'Editar ' : 'Nuevo ') . ($tab === 'galeria' ? 'Elemento' : ($tab === 'eventos' ? 'Evento' : 'Publicación')); }
                ?></h1>
                <?php if ($action === 'list' && $tab !== 'galeria'): ?>
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
                                    <td><span class="tag tag-<?php echo $n['tipo']; ?>"><?php echo ucfirst($n['tipo']); ?></span></td>
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
                            <div class="gallery-grid">
                                <?php foreach ($galeriaList as $img): ?>
                                <div class="gallery-item">
                                    <img src="../<?php echo htmlspecialchars($img['imagen']); ?>" alt="<?php echo htmlspecialchars($img['titulo'] ?? ''); ?>">
                                    <div class="gallery-item-info">
                                        <h4><?php echo htmlspecialchars($img['titulo'] ?: 'Sin título'); ?></h4>
                                        <small><?php echo htmlspecialchars(substr($img['descripcion'] ?? '', 0, 50)); ?></small>
                                    </div>
                                    <div class="gallery-item-actions">
                                        <a href="?action=edit&id=<?php echo $img['id']; ?>&tab=galeria" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete&tab=galeria" data-confirm="¿Eliminar esta imagen?" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $img['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php elseif ($action === 'upload' && $tab === 'galeria'): ?>
                    <!-- GALERIA UPLOAD -->
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="upload">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="titulo">Título</label>
                                    <input type="text" id="titulo" name="titulo">
                                </div>
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <input type="text" id="descripcion" name="descripcion">
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
                            <img src="../<?php echo htmlspecialchars($imagen['imagen']); ?>" style="max-width:300px; border-radius:8px;">
                        </div>
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $imagen['id']; ?>">
                            <div class="form-group">
                                <label for="titulo">Título</label>
                                <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($imagen['titulo'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($imagen['descripcion'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?tab=galeria" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($action === 'new' || $action === 'edit'): ?>
                    <!-- NOTICIAS / EVENTOS FORM -->
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
                                                <img src="../<?php echo $ei; ?>" style="width:100%;height:100%;object-fit:cover;">
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
            </div>
        </main>
    </div>
</body>
</html>
