<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log');

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $existing = Storage::findById('galeria', $deleteId);
                if ($existing && isset($existing['imagen'])) {
                    $imgPath = '../' . $existing['imagen'];
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
                Storage::delete('galeria', $deleteId);
                $message = 'Imagen eliminada.';
            }
            $action = 'list';
        } elseif ($action === 'upload') {
            if (isset($_FILES['imagenes'])) {
                $uploadDir = '../uploads/galeria/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                $uploaded = 0;
                $titulo = trim($_POST['titulo'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');

                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['imagenes']['error'][$key] !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $ext = strtolower(pathinfo($_FILES['imagenes']['name'][$key], PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowedExts) || $_FILES['imagenes']['size'][$key] > 5 * 1024 * 1024) {
                        continue;
                    }

                    $filename = 'galeria_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $filepath = $uploadDir . $filename;

                    if (move_uploaded_file($tmpName, $filepath)) {
                        $item = array(
                            'imagen' => 'uploads/galeria/' . $filename,
                            'titulo' => htmlspecialchars($titulo),
                            'descripcion' => htmlspecialchars($descripcion),
                            'orden' => $uploaded
                        );
                        Storage::insert('galeria', $item);
                        $uploaded++;
                    }
                }

                if ($uploaded > 0) {
                    $message = "$uploaded imagen(es) subida(s) correctamente.";
                } else {
                    $error = 'No se pudieron subir las imágenes.';
                }
            }
            $action = 'list';
        } elseif ($action === 'update') {
            $updateId = intval($_POST['id'] ?? 0);
            if ($updateId > 0) {
                $data = array(
                    'titulo' => htmlspecialchars(trim($_POST['titulo'] ?? '')),
                    'descripcion' => htmlspecialchars(trim($_POST['descripcion'] ?? ''))
                );
                Storage::update('galeria', $updateId, $data);
                $message = 'Imagen actualizada.';
            }
            $action = 'list';
        }
    }
}

$imagen = null;
if ($action === 'edit' && $id > 0) {
    $imagen = Storage::findById('galeria', $id);
    if (!$imagen) {
        $action = 'list';
        $error = 'Imagen no encontrada.';
    }
}

$galeria = null;
if ($action === 'list') {
    $galeria = Storage::read('galeria');
    usort($galeria, function($a, $b) {
        return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
    });
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Galería</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .gallery-item { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .gallery-item img { width: 100%; height: 150px; object-fit: cover; }
        .gallery-item-info { padding: 10px; }
        .gallery-item-info h4 { font-size: 0.85rem; margin-bottom: 5px; }
        .gallery-item-actions { display: flex; gap: 5px; padding: 0 10px 10px; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php echo $action === 'list' ? 'Galería' : 'Subir Imágenes'; ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=upload" class="btn btn-primary">+ Subir</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <?php if (empty($galeria)): ?>
                        <p class="empty">No hay imágenes en la galería.</p>
                    <?php else: ?>
                        <div class="gallery-grid">
                            <?php foreach ($galeria as $img): ?>
                            <div class="gallery-item">
                                <img src="../<?php echo htmlspecialchars($img['imagen']); ?>" alt="<?php echo htmlspecialchars($img['titulo'] ?? ''); ?>">
                                <div class="gallery-item-info">
                                    <h4><?php echo htmlspecialchars($img['titulo'] ?: 'Sin título'); ?></h4>
                                    <small><?php echo htmlspecialchars(substr($img['descripcion'] ?? '', 0, 50)); ?></small>
                                </div>
                                <div class="gallery-item-actions">
                                    <a href="?action=edit&id=<?php echo $img['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <form class="delete-form" method="POST" action="?action=delete" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $img['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php elseif ($action === 'upload'): ?>
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
                                <a href="?action=list" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($action === 'edit' && $imagen): ?>
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
