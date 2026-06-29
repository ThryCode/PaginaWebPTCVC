<?php

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/admin_error.log');

require_once '../api/auth.php';
require_once '../api/storage.php';
require_once 'includes/helpers.php';

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
                    $clean = ltrim($existing['imagen'], '/');
                    $realBase = realpath(__DIR__ . '/../uploads');
                    $realPath = realpath(__DIR__ . '/../' . $clean);
                    if ($realPath !== false && $realBase !== false && strpos($realPath, $realBase) === 0 && file_exists($realPath)) {
                        unlink($realPath);
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
                    $err = validateUploadedImage($_FILES['imagen'], 10 * 1024 * 1024);
                    if ($err === null) {
                        $fn = moveUploadedImage($_FILES['imagen'], '../uploads/flyers/', 'flyer');
                        if ($fn) $data['imagen'] = 'uploads/flyers/' . $fn;
                    } else {
                        $error = $err;
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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=w<=768;document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Flyers</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <style>
        .flyer-preview { max-width:100%; max-height:180px; border-radius:8px; object-fit:cover; border:1px solid #e0e0e0; }
        .flyer-thumb { width:100px; height:70px; border-radius:6px; object-fit:cover; border:1px solid #e0e0e0; }
    </style>
</head>
<body>
    <div class="admin-wrapper">

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu">☰</button>
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
                                    <td data-label="Imagen">
                                        <?php if (isset($f['imagen'])): ?>
                                            <img src="/<?php echo htmlspecialchars($f['imagen']); ?>" loading="lazy" class="flyer-thumb" alt="<?php echo htmlspecialchars($f['titulo']); ?>">
                                        <?php else: ?>
                                            Sin imagen
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Título"><strong><?php echo htmlspecialchars($f['titulo']); ?></strong></td>
                                    <td data-label="Orden"><?php echo htmlspecialchars($f['orden']); ?></td>
                                    <td data-label="Acciones">
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
                                <input type="number" id="orden" name="orden" min="0" value="<?php echo htmlspecialchars($flyer['orden'] ?? 0); ?>">
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
        <?php include 'includes/sidebar.php'; ?>
    </div>
</body>
</html>