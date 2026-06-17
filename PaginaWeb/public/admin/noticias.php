<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/data/error_log');

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

function autoResumen($texto, $max = 200) {
    $limpio = trim(strip_tags($texto));
    if (mb_strlen($limpio) <= $max) return $limpio;
    return mb_substr($limpio, 0, $max) . '…';
}

function getNoticiaFolder($id) {
    return '../uploads/noticia_' . $id . '/';
}
function getNoticiaFolderUrl($id) {
    return 'uploads/noticia_' . $id . '/';
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
    if ($msg === 'creado') $message = 'Publicación creada correctamente.';
    elseif ($msg === 'editado') $message = 'Publicación actualizada correctamente.';
    elseif ($msg === 'eliminado') $message = 'Publicación eliminada.';
}

$categorias = Storage::read('categorias');

// POST handlers
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
                    $tipo = $existing['tipo'] ?? 'noticia';
                    $folder = ($tipo === 'evento') ? getEventoFolder($deleteId) : getNoticiaFolder($deleteId);
                    deleteFolder($folder);
                }
                Storage::delete('noticias', $deleteId);
                header('Location: noticias.php?msg=eliminado');
                exit;
            }
        } else {
            // Create/Update
            $titulo = mb_substr(trim($_POST['titulo'] ?? ''), 0, 100);
            $tipo = 'noticia';
            $categoria_id = 1;
            $contenido = $_POST['contenido'] ?? '';
            $resumen = autoResumen($contenido);
            $fecha_evento_date = trim($_POST['fecha_evento_date'] ?? '');
            $fecha_evento = $fecha_evento_date ?: null;
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
                'tipo' => $tipo,
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
                $folderDir = getNoticiaFolder($id);
                $folderUrl = getNoticiaFolderUrl($id);
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
                        header('Location: noticias.php?msg=editado');
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

                        $folderDir = getNoticiaFolder($newId);
                        $folderUrl = getNoticiaFolderUrl($newId);
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

                        header('Location: noticias.php?msg=creado');
                        exit;
                    } catch (Exception $e) {
                        $error = 'Error al guardar.';
                    }
                }
            }
        }
    }
}

$noticia = null;
if ($action === 'edit' && $id > 0) {
    $noticia = Storage::findById('noticias', $id);
    if (!$noticia) {
        $action = 'list';
        $error = 'Publicación no encontrada.';
    }
}

$noticias = null;
if ($action === 'list') {
    $allNoticias = Storage::read('noticias');
    $noticias = array();
    foreach ($allNoticias as $n) {
        if (isset($n['tipo']) && $n['tipo'] === 'noticia') {
            $noticias[] = $n;
        }
    }
    usort($noticias, function($a, $b) {
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
    <title>Admin - Noticias</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php echo $action === 'list' ? 'Noticias' : ($action === 'edit' ? 'Editar Publicación' : 'Nueva Publicación'); ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">+ Nueva</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($noticias)): ?>
                            <p class="empty">No hay publicaciones.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Título</th><th>Tipo</th><th>Categoría</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($noticias as $n): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($n['titulo'] ?? 'Sin título', 0, 50)); ?></td>
                                    <td><span class="tag tag-<?php echo $n['tipo']; ?>"><?php echo ucfirst($n['tipo']); ?></span></td>
                                    <td><?php
                                        $catName = '—';
                                        foreach ($categorias as $cat) {
                                            if ($cat['id'] == $n['categoria_id']) {
                                                $catName = htmlspecialchars($cat['nombre']);
                                                break;
                                            }
                                        }
                                        echo $catName;
                                    ?></td>
                                    <td><span class="tag tag-<?php echo $n['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $n['publicada'] ? 'Publicado' : 'Borrador'; ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($n['fecha_evento'] ?? $n['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete" onsubmit="return confirm('¿Eliminar esta publicación?')">
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

                <?php elseif ($action === 'new' || $action === 'edit'): ?>
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
                                        if (isset($noticia['imagenes']) && is_array($noticia['imagenes'])) {
                                            $existingImages = $noticia['imagenes'];
                                        } elseif (isset($noticia['imagen'])) {
                                            $existingImages = array($noticia['imagen']);
                                        }
                                        if (!empty($existingImages)): ?>
                                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">
                                        <?php foreach ($existingImages as $ei): ?>
                                            <div style="position:relative;width:100px;height:80px;border-radius:8px;overflow:hidden;border:2px solid #E6F4FA;">
                                                <img src="../<?php echo $ei; ?>" style="width:100%;height:100%;object-fit:cover;">
                                                <label style="position:absolute;bottom:2px;left:2px;background:rgba(192,57,43,0.85);color:#fff;padding:2px 6px;border-radius:4px;font-size:0.7rem;cursor:pointer;">
                                                    <input type="checkbox" name="delete_imagenes[]" value="<?php echo $ei; ?>" onchange="this.parentElement.style.background=this.checked?'rgba(192,57,43,1)':'rgba(192,57,43,0.85)'"> Eliminar
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
                                    <label for="fecha_evento_date">Fecha de la noticia</label>
                                    <input type="date" id="fecha_evento_date" name="fecha_evento_date" value="<?php echo ($noticia && $noticia['fecha_evento']) ? date('Y-m-d', strtotime($noticia['fecha_evento'])) : ''; ?>">
                                    <small>Si no se especifica, se usa la fecha de publicaci&oacute;n.</small>
                                </div>
                                <div class="form-group"></div>
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
