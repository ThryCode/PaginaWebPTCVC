<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/admin_error.log');

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'creado') $message = 'Flyer agregado correctamente.';
    elseif ($msg === 'editado') $message = 'Flyer actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Flyer eliminado.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inv&aacute;lido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $existing = Storage::findById('flyers', $deleteId);
                if ($existing && isset($existing['imagen'])) {
                    $imgPath = '../' . $existing['imagen'];
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
                Storage::delete('flyers', $deleteId);
                header('Location: flyers.php?msg=eliminado');
                exit;
            }
        } else {
            $titulo = trim($_POST['titulo'] ?? '');
            $orden = intval($_POST['orden'] ?? 0);

            if (empty($titulo)) {
                $error = 'El t&iacute;tulo es obligatorio.';
            } else {
                $data = array(
                    'titulo' => $titulo,
                    'orden' => $orden
                );

                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/flyers/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                    $mime = mime_content_type($_FILES['imagen']['tmp_name']);

                    if (in_array($ext, $allowedExts) && in_array($mime, $allowedMime) && $_FILES['imagen']['size'] <= 10 * 1024 * 1024) {
                        $filename = 'flyer_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        $filepath = $uploadDir . $filename;
                        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                            $data['imagen'] = 'uploads/flyers/' . $filename;
                        }
                    } else {
                        $error = 'Imagen no v&aacute;lida (m&aacute;x 10MB, JPG/PNG/GIF/WEBP).';
                    }
                }

                if (empty($error)) {
                    try {
                        if ($action === 'edit' && $id > 0) {
                            if (!isset($data['imagen'])) {
                                $existing = Storage::findById('flyers', $id);
                                if ($existing && isset($existing['imagen'])) {
                                    $data['imagen'] = $existing['imagen'];
                                }
                            }
                            Storage::update('flyers', $id, $data);
                            header('Location: flyers.php?msg=editado');
                            exit;
                        } else {
                            if (!isset($data['imagen'])) {
                                $error = 'La imagen es obligatoria.';
                            } else {
                                Storage::insert('flyers', $data);
                                header('Location: flyers.php?msg=creado');
                                exit;
                            }
                        }
                    } catch (Exception $e) {
                        $error = 'Error al guardar.';
                    }
                }
            }
        }
    }
}

$flyer = null;
if ($action === 'edit' && $id > 0) {
    $flyer = Storage::findById('flyers', $id);
    if (!$flyer) {
        $action = 'list';
        $error = 'Flyer no encontrado.';
    }
}

$flyers = null;
if ($action === 'list') {
    $flyers = Storage::read('flyers');
    usort($flyers, function($a, $b) {
        return $a['orden'] - $b['orden'];
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
    <title>Admin - Flyers</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <style>
        .flyer-preview { max-width:300px; max-height:180px; border-radius:8px; object-fit:cover; border:1px solid #e0e0e0; }
        .flyer-thumb { width:100px; height:70px; border-radius:6px; object-fit:cover; border:1px solid #e0e0e0; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php echo $action === 'list' ? 'Flyers' : ($action === 'edit' ? 'Editar Flyer' : 'Nuevo Flyer'); ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">+ Nuevo</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($flyers)): ?>
                            <p class="empty">No hay flyers. Agrega uno nuevo para mostrar en el carrusel de servicios.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Imagen</th><th>T&iacute;tulo</th><th>Orden</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($flyers as $f): ?>
                                <tr>
                                    <td>
                                        <?php if (isset($f['imagen'])): ?>
                                            <img src="/<?php echo htmlspecialchars($f['imagen']); ?>" class="flyer-thumb" alt="<?php echo htmlspecialchars($f['titulo']); ?>">
                                        <?php else: ?>
                                            Sin imagen
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($f['titulo']); ?></strong></td>
                                    <td><?php echo $f['orden']; ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete" data-confirm="¿Eliminar este flyer?">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
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
                            <div class="form-group">
                                <label for="titulo">T&iacute;tulo *</label>
                                <input type="text" id="titulo" name="titulo" required value="<?php echo $flyer ? htmlspecialchars($flyer['titulo']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="imagen">Imagen <?php echo $action === 'new' ? '*' : ''; ?></label>
                                <input type="file" id="imagen" name="imagen" accept="image/*" <?php echo $action === 'new' ? 'required' : ''; ?>>
                                <?php if ($flyer && isset($flyer['imagen'])): ?>
                                    <div style="margin-top:10px;">
                                        <img src="/<?php echo htmlspecialchars($flyer['imagen']); ?>" class="flyer-preview" alt="Actual">
                                        <p style="font-size:0.85rem;color:#888;margin-top:4px;">Imagen actual. Sube una nueva solo si deseas cambiarla.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="orden">Orden</label>
                                <input type="number" id="orden" name="orden" min="0" value="<?php echo $flyer ? $flyer['orden'] : 0; ?>">
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