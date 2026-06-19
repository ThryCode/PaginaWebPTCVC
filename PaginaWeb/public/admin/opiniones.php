<?php
require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$opiniones = Storage::read('opiniones');
usort($opiniones, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

$message = '';
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'created') $message = 'Opinión creada correctamente.';
    elseif ($msg === 'updated') $message = 'Opinión actualizada correctamente.';
    elseif ($msg === 'deleted') $message = 'Opinión eliminada correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $nombre = trim($_POST['nombre'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');
            $texto = trim($_POST['texto'] ?? '');
            $orden = intval($_POST['orden'] ?? count($opiniones) + 1);

            $imagen = '';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowedExts) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
                    $filename = 'opinion_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $filepath = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                        $imagen = 'uploads/' . $filename;
                    }
                }
            }

            $newId = 1;
            foreach ($opiniones as $o) {
                if (isset($o['id']) && $o['id'] >= $newId) $newId = $o['id'] + 1;
            }

            $opiniones[] = array(
                'id' => $newId,
                'nombre' => trim($nombre),
                'cargo' => trim($cargo),
                'texto' => trim($texto),
                'imagen' => $imagen,
                'orden' => $orden
            );

            Storage::write('opiniones', $opiniones);
            header('Location: opiniones.php?msg=created');
            exit;

        } elseif ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            foreach ($opiniones as &$o) {
                if (isset($o['id']) && $o['id'] === $id) {
                    $o['nombre'] = trim($_POST['nombre'] ?? $o['nombre']);
                    $o['cargo'] = trim($_POST['cargo'] ?? $o['cargo']);
                    $o['texto'] = trim($_POST['texto'] ?? $o['texto']);
                    $o['orden'] = intval($_POST['orden'] ?? $o['orden']);

                    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../uploads/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowedExts) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
                            $filename = 'opinion_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                            $filepath = $uploadDir . $filename;
                            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                                if (!empty($o['imagen'])) {
                                    $oldPath = '../' . $o['imagen'];
                                    if (file_exists($oldPath)) unlink($oldPath);
                                }
                                $o['imagen'] = 'uploads/' . $filename;
                            }
                        }
                    }
                    break;
                }
            }
            unset($o);
            Storage::write('opiniones', $opiniones);
            header('Location: opiniones.php?msg=updated');
            exit;

        } elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            foreach ($opiniones as $o) {
                if (isset($o['id']) && $o['id'] === $id) {
                    if (!empty($o['imagen'])) {
                        $imgPath = '../' . $o['imagen'];
                        if (file_exists($imgPath)) unlink($imgPath);
                    }
                    break;
                }
            }
            $opiniones = array_values(array_filter($opiniones, function($o) use ($id) {
                return !isset($o['id']) || $o['id'] !== $id;
            }));
            Storage::write('opiniones', $opiniones);
            header('Location: opiniones.php?msg=deleted');
            exit;
        }
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
    <title>Admin - Opiniones</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .opinion-preview { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .opinion-preview-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .opinion-preview-card .quote { font-size: 2rem; color: #008674; line-height: 1; }
        .opinion-preview-card .text { font-size: 0.85rem; color: #555; margin: 8px 0; font-style: italic; }
        .opinion-preview-card .author { display: flex; align-items: center; gap: 10px; margin-top: 12px; }
        .opinion-preview-card .author img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .opinion-preview-card .author-placeholder { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #003c6e, #008674); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; }
        .opinion-preview-card .author-name { font-weight: 700; font-size: 0.85rem; color: #1a1a2e; }
        .opinion-preview-card .author-cargo { font-size: 0.75rem; color: #888; }
        .opinion-row { display: flex; align-items: center; gap: 16px; padding: 16px 20px; background: #fff; border-radius: 10px; margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .opinion-row .opinion-thumb { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        .opinion-row .opinion-thumb-placeholder { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #003c6e, #008674); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; }
        .opinion-row .opinion-data { flex: 1; }
        .opinion-row .opinion-data h4 { color: #1a1a2e; font-size: 0.95rem; margin: 0; }
        .opinion-row .opinion-data span { color: #888; font-size: 0.8rem; }
        .opinion-row .actions { display: flex; gap: 8px; }
        .edit-form { background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 10px; border: 2px dashed #e0e0e0; display: none; }
        .edit-form.active { display: block; }
        @media (max-width: 768px) { .opinion-preview { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="hamburger" aria-label="Menu">&#9776;</button>
                    <h1>Opiniones</h1>
                </div>
            </header>
            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <h3 style="margin-bottom:16px;color:#1a1a2e;">Vista previa</h3>
                <div class="opinion-preview">
                    <?php if (empty($opiniones)): ?>
                        <p class="empty" style="grid-column:1/-1;">No hay opiniones. Crea una nueva.</p>
                    <?php else: ?>
                        <?php foreach ($opiniones as $o): ?>
                            <div class="opinion-preview-card">
                                <div class="quote">&#10077;</div>
                                <div class="text"><?php echo htmlspecialchars($o['texto']); ?></div>
                                <div class="author">
                                    <?php if (!empty($o['imagen'])): ?>
                                        <img src="<?php echo $o['imagen']; ?>" alt="<?php echo htmlspecialchars($o['nombre']); ?>">
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

                <div class="grid-2">
                    <div class="panel">
                        <div class="panel-header">
                            <h2>Crear nueva opinion</h2>
                        </div>
                        <div class="panel-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="create">
                                <div class="form-group">
                                    <label for="new_nombre">Nombre *</label>
                                    <input type="text" id="new_nombre" name="nombre" required placeholder=" Nombre completo">
                                </div>
                                <div class="form-group">
                                    <label for="new_cargo">Cargo *</label>
                                    <input type="text" id="new_cargo" name="cargo" required placeholder=" Cargo o titulo">
                                </div>
                                <div class="form-group">
                                    <label for="new_texto">Texto de la opinion *</label>
                                    <textarea id="new_texto" name="texto" required rows="3" placeholder=" Escribe la opinion..."></textarea>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                    <div class="form-group">
                                        <label for="new_imagen">Foto de perfil</label>
                                        <input type="file" id="new_imagen" name="imagen" accept="image/*">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_orden">Orden</label>
                                        <input type="number" id="new_orden" name="orden" min="1" value="<?php echo count($opiniones) + 1; ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Crear Opinion</button>
                            </form>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h2>Opiniones actuales</h2>
                        </div>
                        <div class="panel-body">
                            <?php if (empty($opiniones)): ?>
                                <p class="empty">No hay opiniones creadas aun.</p>
                            <?php else: ?>
                                <?php foreach ($opiniones as $o): ?>
                                    <div class="opinion-row">
                                        <?php if (!empty($o['imagen'])): ?>
                                            <img src="<?php echo $o['imagen']; ?>" class="opinion-thumb" alt="">
                                        <?php else: ?>
                                            <div class="opinion-thumb-placeholder"><?php echo htmlspecialchars(mb_substr($o['nombre'], 0, 1)); ?></div>
                                        <?php endif; ?>
                                        <div class="opinion-data">
                                            <h4><?php echo htmlspecialchars($o['nombre']); ?></h4>
                                            <span><?php echo htmlspecialchars($o['cargo']); ?> | Orden: <?php echo intval($o['orden']); ?></span>
                                        </div>
                                        <div class="actions">
                                            <button class="btn btn-sm btn-primary" data-toggle-edit="<?php echo $o['id']; ?>">Editar</button>
                                            <form class="delete-form" method="POST" data-confirm="Eliminar esta opinion?">
                                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="edit-form" id="edit-<?php echo $o['id']; ?>">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
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
                                                <button type="button" class="btn btn-sm btn-secondary" data-toggle-edit="<?php echo $o['id']; ?>">Cancelar</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
