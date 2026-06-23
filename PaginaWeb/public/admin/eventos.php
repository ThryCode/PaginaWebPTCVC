<?php
header('Location: informacion.php?tab=eventos');
exit;
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

function getEventoFolder($id) {
    return '../uploads/evento_' . $id . '/';
}
function getEventoFolderUrl($id) {
    return 'uploads/evento_' . $id . '/';
}
function ensureFolder($dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
function migrateOldImages(&$imagenes, $folderDir, $folderUrl) {
    $result = array();
    foreach ($imagenes as $img) {
        if (strpos($img, $folderUrl) === 0) {
            $result[] = $img;
        } elseif (preg_match('#^uploads/[^/]+$#', $img)) {
            $oldFile = '../' . $img;
            if (file_exists($oldFile)) {
                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (copy($oldFile, $folderDir . $newName)) {
                    unlink($oldFile);
                    $result[] = $folderUrl . $newName;
                } else {
                    $result[] = $img;
                }
            } else {
                $result[] = $img;
            }
        } else {
            $result[] = $img;
        }
    }
    $imagenes = $result;
}
function deleteFolder($dir) {
    if (!is_dir($dir)) return;
    $files = glob($dir . '*');
    if ($files) {
        foreach ($files as $f) {
            if (is_file($f)) unlink($f);
        }
    }
    rmdir($dir);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'creado') $message = 'Evento creado correctamente.';
    elseif ($msg === 'editado') $message = 'Evento actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Evento eliminado.';
}

$categorias = Storage::read('categorias');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $existing = Storage::findById('noticias', $deleteId);
                if ($existing) {
                    $allImages = array();
                    if (isset($existing['imagenes']) && is_array($existing['imagenes'])) {
                        $allImages = $existing['imagenes'];
                    } elseif (isset($existing['imagen'])) {
                        $allImages = array($existing['imagen']);
                    }
                    foreach ($allImages as $img) {
                        $imgPath = '../' . $img;
                        if (file_exists($imgPath)) {
                            unlink($imgPath);
                        }
                    }
                    deleteFolder(getEventoFolder($deleteId));
                }
                Storage::delete('noticias', $deleteId);
                header('Location: eventos.php?msg=eliminado');
                exit;
            }
        } else {
            $titulo = mb_substr(trim($_POST['titulo'] ?? ''), 0, 100);
            $categoria_id = 2;
            $contenido = $_POST['contenido'] ?? '';
            $resumen = autoResumen($contenido);
            $fecha_evento_date = trim($_POST['fecha_evento_date'] ?? '');
            $fecha_evento_time = trim($_POST['fecha_evento_time'] ?? '');
            $fecha_evento = ($fecha_evento_date && $fecha_evento_time) ? $fecha_evento_date . ' ' . $fecha_evento_time : null;
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            $publicada = isset($_POST['publicada']) ? 1 : 0;
            $destacada = isset($_POST['destacada']) ? 1 : 0;

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo), '-'));
            $slug = substr($slug, 0, 280);

            $data = array(
                'titulo' => $titulo,
                'slug' => $slug,
                'resumen' => $resumen,
                'contenido' => $contenido,
                'tipo' => 'evento',
                'categoria_id' => $categoria_id,
                'autor_id' => $_SESSION['user_id'],
                'fecha_evento' => $fecha_evento,
                'ubicacion' => $ubicacion,
                'publicada' => $publicada,
                'destacada' => $destacada
            );

            if ($action === 'edit' && $id > 0) {
                // === EDIT ===
                $existing = Storage::findById('noticias', $id);
                $imagenes = array();
                if ($existing) {
                    if (isset($existing['imagenes']) && is_array($existing['imagenes'])) {
                        $imagenes = $existing['imagenes'];
                    } elseif (isset($existing['imagen'])) {
                        $imagenes = array($existing['imagen']);
                    }
                }

                // Handle image deletions
                if (isset($_POST['delete_imagenes']) && is_array($_POST['delete_imagenes'])) {
                    $deleteList = $_POST['delete_imagenes'];
                    $kept = array();
                    foreach ($imagenes as $img) {
                        if (!in_array($img, $deleteList)) {
                            $kept[] = $img;
                        } else {
                            $delPath = '../' . $img;
                            if (file_exists($delPath)) {
                                unlink($delPath);
                            }
                        }
                    }
                    $imagenes = $kept;
                }

                // Ensure folder and migrate old-format images
                $folderDir = getEventoFolder($id);
                $folderUrl = getEventoFolderUrl($id);
                ensureFolder($folderDir);
                migrateOldImages($imagenes, $folderDir, $folderUrl);

                // Handle new uploads
                if (isset($_FILES['imagenes'])) {
                    $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $files = $_FILES['imagenes'];
                    $fileCount = is_array($files['name']) ? count($files['name']) : 0;
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                            if ($files['error'][$i] === UPLOAD_ERR_INI_SIZE || $files['error'][$i] === UPLOAD_ERR_FORM_SIZE) {
                                $error = 'Una o más imágenes exceden el tamaño máximo permitido (5MB).';
                            }
                            continue;
                        }
                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedExts) || $files['size'][$i] > 5 * 1024 * 1024) {
                            $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).';
                            continue;
                        }
                        $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        if (move_uploaded_file($files['tmp_name'][$i], $folderDir . $filename)) {
                            $imagenes[] = $folderUrl . $filename;
                        }
                    }
                }

                $data['imagenes'] = $imagenes;
                $data['imagen'] = !empty($imagenes) ? $imagenes[0] : '';

                if (empty($error)) {
                    try {
                        Storage::update('noticias', $id, $data);
                        header('Location: eventos.php?msg=editado');
                        exit;
                    } catch (Exception $e) {
                        $error = 'Error al guardar.';
                    }
                }
            } else {
                // === CREATE ===
                $data['imagenes'] = array();
                $data['imagen'] = '';

                if (empty($error)) {
                    try {
                        $saved = Storage::insert('noticias', $data);
                        $newId = $saved['id'];

                        $folderDir = getEventoFolder($newId);
                        $folderUrl = getEventoFolderUrl($newId);
                        ensureFolder($folderDir);

                        $imagenes = array();
                        if (isset($_FILES['imagenes'])) {
                            $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                            $files = $_FILES['imagenes'];
                            $fileCount = is_array($files['name']) ? count($files['name']) : 0;
                            for ($i = 0; $i < $fileCount; $i++) {
                                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                                    if ($files['error'][$i] === UPLOAD_ERR_INI_SIZE || $files['error'][$i] === UPLOAD_ERR_FORM_SIZE) {
                                        $error = 'Una o más imágenes exceden el tamaño máximo permitido (5MB).';
                                    }
                                    continue;
                                }
                                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                                if (!in_array($ext, $allowedExts) || $files['size'][$i] > 5 * 1024 * 1024) {
                                    $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).';
                                    continue;
                                }
                                $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                                if (move_uploaded_file($files['tmp_name'][$i], $folderDir . $filename)) {
                                    $imagenes[] = $folderUrl . $filename;
                                }
                            }
                        }

                        $saved['imagenes'] = $imagenes;
                        $saved['imagen'] = !empty($imagenes) ? $imagenes[0] : '';
                        Storage::update('noticias', $newId, $saved);

                        header('Location: eventos.php?msg=creado');
                        exit;
                    } catch (Exception $e) {
                        $error = 'Error al guardar.';
                    }
                }
            }
        }
    }
}

$evento = null;
if ($action === 'edit' && $id > 0) {
    $evento = Storage::findById('noticias', $id);
    if (!$evento || $evento['tipo'] !== 'evento') {
        $action = 'list';
        $error = 'Evento no encontrado.';
    }
}

$eventos = null;
if ($action === 'list') {
    $allNoticias = Storage::read('noticias');
    $eventos = array();
    foreach ($allNoticias as $n) {
        if (isset($n['tipo']) && $n['tipo'] === 'evento') {
            $eventos[] = $n;
        }
    }
    usort($eventos, function($a, $b) {
        return strcmp($b['created_at'], $a['created_at']);
    });
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=1, user-scalable=no';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Eventos</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php echo $action === 'list' ? 'Eventos' : ($action === 'edit' ? 'Editar Evento' : 'Nuevo Evento'); ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">+ Nuevo</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($eventos)): ?>
                            <p class="empty">No hay eventos.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Título</th><th>Fecha</th><th>Ubicación</th><th>Estado</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($eventos as $e): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($e['titulo'] ?? 'Sin título', 0, 50)); ?></td>
                                    <td><?php echo $e['fecha_evento'] ? date('d/m/Y H:i', strtotime($e['fecha_evento'])) : '—'; ?></td>
                                    <td><?php echo htmlspecialchars($e['ubicacion'] ?: '—'); ?></td>
                                    <td><span class="tag tag-<?php echo $e['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $e['publicada'] ? 'Publicado' : 'Borrador'; ?></span></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $e['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete" data-confirm="¿Eliminar este evento?">
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

                <?php elseif ($action === 'new' || $action === 'edit'): ?>
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="titulo">Título *</label>
                                    <input type="text" id="titulo" name="titulo" required maxlength="100" value="<?php echo $evento ? htmlspecialchars($evento['titulo']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Fotos</label>
                                    <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>
                                    <small>Puede seleccionar varias fotos. Máx 5MB cada una.</small>
                                    <?php if ($evento): 
                                        $existingImages = array();
                                        if (isset($evento['imagenes']) && is_array($evento['imagenes'])) {
                                            $existingImages = $evento['imagenes'];
                                        } elseif (isset($evento['imagen'])) {
                                            $existingImages = array($evento['imagen']);
                                        }
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
                                    <label for="fecha_evento_date">Fecha del Evento *</label>
                                    <input type="date" id="fecha_evento_date" name="fecha_evento_date" required value="<?php echo ($evento && $evento['fecha_evento']) ? date('Y-m-d', strtotime($evento['fecha_evento'])) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="fecha_evento_time">Hora del Evento *</label>
                                    <input type="time" id="fecha_evento_time" name="fecha_evento_time" required value="<?php echo ($evento && $evento['fecha_evento']) ? date('H:i', strtotime($evento['fecha_evento'])) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" value="<?php echo $evento ? htmlspecialchars($evento['ubicacion']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="contenido">Contenido completo</label>
                                <textarea id="contenido" name="contenido" rows="10"><?php echo $evento ? htmlspecialchars($evento['contenido'] ?? '') : ''; ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><input type="checkbox" name="publicada" value="1" <?php echo (!$evento || $evento['publicada']) ? 'checked' : ''; ?>> Publicado</label>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" name="destacada" value="1" <?php echo ($evento && $evento['destacada']) ? 'checked' : ''; ?>> Destacado</label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?action=list" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
