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

// POST handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        // Delete action
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                // Delete image file if exists
                $existing = Storage::findById('noticias', $deleteId);
                if ($existing && isset($existing['imagen'])) {
                    $imgPath = '../' . $existing['imagen'];
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
                Storage::delete('noticias', $deleteId);
                $message = 'Publicación eliminada.';
            }
            $action = 'list';
        } else {
            // Create/Update
            $titulo = trim($_POST['titulo'] ?? '');
            $tipo = $_POST['tipo'] ?? 'noticia';
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
                'tipo' => $tipo,
                'categoria_id' => $categoria_id,
                'autor_id' => $_SESSION['user_id'],
                'fecha_evento' => $fecha_evento,
                'ubicacion' => htmlspecialchars($ubicacion),
                'publicada' => $publicada,
                'destacada' => $destacada
            );

            // Handle image upload
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowedExts) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
                    $filename = 'noticia_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
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
                        $message = 'Publicación actualizada correctamente.';
                    } else {
                        Storage::insert('noticias', $data);
                        $message = 'Publicación creada correctamente.';
                    }
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error al guardar.';
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
    $noticias = Storage::read('noticias');
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
                                    <td><?php echo date('d/m/Y', strtotime($n['created_at'])); ?></td>
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
                                    <input type="text" id="titulo" name="titulo" required value="<?php echo $noticia ? htmlspecialchars($noticia['titulo']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="tipo">Tipo *</label>
                                    <select id="tipo" name="tipo" required>
                                        <option value="noticia" <?php echo ($noticia && $noticia['tipo'] === 'noticia') ? 'selected' : ''; ?>>Noticia</option>
                                        <option value="evento" <?php echo ($noticia && $noticia['tipo'] === 'evento') ? 'selected' : ''; ?>>Evento</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="categoria_id">Categoría</label>
                                    <select id="categoria_id" name="categoria_id">
                                        <option value="">Sin categoría</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo ($noticia && isset($noticia['categoria_id']) && $noticia['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="imagen">Imagen</label>
                                    <input type="file" id="imagen" name="imagen" accept="image/*">
                                    <?php if ($noticia && isset($noticia['imagen'])): ?>
                                        <small>Actual: <?php echo basename($noticia['imagen']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_evento_date">Fecha del Evento</label>
                                    <input type="date" id="fecha_evento_date" name="fecha_evento_date" value="<?php echo ($noticia && $noticia['fecha_evento']) ? date('Y-m-d', strtotime($noticia['fecha_evento'])) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="fecha_evento_time">Hora del Evento</label>
                                    <input type="time" id="fecha_evento_time" name="fecha_evento_time" value="<?php echo ($noticia && $noticia['fecha_evento']) ? date('H:i', strtotime($noticia['fecha_evento'])) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" value="<?php echo $noticia ? htmlspecialchars($noticia['ubicacion']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="resumen">Resumen</label>
                                <textarea id="resumen" name="resumen" rows="3"><?php echo $noticia ? htmlspecialchars($noticia['resumen']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="contenido">Contenido *</label>
                                <textarea id="contenido" name="contenido" rows="10" required><?php echo $noticia ? $noticia['contenido'] : ''; ?></textarea>
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
