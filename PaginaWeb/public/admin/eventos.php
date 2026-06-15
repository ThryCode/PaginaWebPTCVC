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

$categorias = Storage::read('categorias');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $existing = Storage::findById('noticias', $deleteId);
                if ($existing && isset($existing['imagen'])) {
                    $imgPath = '../' . $existing['imagen'];
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
                Storage::delete('noticias', $deleteId);
                $message = 'Evento eliminado.';
            }
            $action = 'list';
        } else {
            $titulo = trim($_POST['titulo'] ?? '');
            $categoria_id = intval($_POST['categoria_id'] ?? 0) ?: null;
            $resumen = trim($_POST['resumen'] ?? '');
            $contenido = $_POST['contenido'] ?? '';
            $fecha_evento_date = trim($_POST['fecha_evento_date'] ?? '');
            $fecha_evento_time = trim($_POST['fecha_evento_time'] ?? '');
            $fecha_evento = ($fecha_evento_date && $fecha_evento_time) ? $fecha_evento_date . ' ' . $fecha_evento_time : null;
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            $publicada = isset($_POST['publicada']) ? 1 : 0;
            $destacada = isset($_POST['destacada']) ? 1 : 0;

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo), '-'));
            $slug = substr($slug, 0, 280);

            $data = array(
                'titulo' => htmlspecialchars($titulo),
                'slug' => $slug,
                'resumen' => htmlspecialchars($resumen),
                'contenido' => $contenido,
                'tipo' => 'evento',
                'categoria_id' => $categoria_id,
                'autor_id' => $_SESSION['user_id'],
                'fecha_evento' => $fecha_evento,
                'ubicacion' => htmlspecialchars($ubicacion),
                'publicada' => $publicada,
                'destacada' => $destacada
            );

            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowedExts) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
                    $filename = 'evento_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $filepath = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                        $data['imagen'] = 'uploads/' . $filename;
                    }
                } else {
                    $error = 'Imagen no válida (máx 5MB, JPG/PNG/GIF/WEBP).';
                }
            }

            if (empty($error)) {
                try {
                    if ($action === 'edit' && $id > 0) {
                        if (!isset($data['imagen'])) {
                            $existing = Storage::findById('noticias', $id);
                            if ($existing && isset($existing['imagen'])) {
                                $data['imagen'] = $existing['imagen'];
                            }
                        }
                        Storage::update('noticias', $id, $data);
                        $message = 'Evento actualizado correctamente.';
                    } else {
                        Storage::insert('noticias', $data);
                        $message = 'Evento creado correctamente.';
                    }
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error al guardar.';
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
    <title>Admin - Eventos</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none;">☰</button>
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
                                        <form class="delete-form" method="POST" action="?action=delete" onsubmit="return confirm('¿Eliminar este evento?')">
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
                                    <input type="text" id="titulo" name="titulo" required value="<?php echo $evento ? htmlspecialchars($evento['titulo']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="categoria_id">Categoría</label>
                                    <select id="categoria_id" name="categoria_id">
                                        <option value="">Sin categoría</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo ($evento && isset($evento['categoria_id']) && $evento['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
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
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ubicacion">Ubicación</label>
                                    <input type="text" id="ubicacion" name="ubicacion" value="<?php echo $evento ? htmlspecialchars($evento['ubicacion']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="imagen">Imagen</label>
                                    <input type="file" id="imagen" name="imagen" accept="image/*">
                                    <?php if ($evento && isset($evento['imagen'])): ?>
                                        <small>Actual: <?php echo basename($evento['imagen']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group"></div>
                            </div>
                            <div class="form-group">
                                <label for="resumen">Resumen</label>
                                <textarea id="resumen" name="resumen" rows="3"><?php echo $evento ? htmlspecialchars($evento['resumen']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="contenido">Contenido *</label>
                                <textarea id="contenido" name="contenido" rows="10" required><?php echo $evento ? $evento['contenido'] : ''; ?></textarea>
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
