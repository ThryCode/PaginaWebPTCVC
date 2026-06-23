<?php
header('Location: informacion.php?tab=proyectos');
exit;

function autoResumen($texto, $max = 200) {
    $limpio = trim(strip_tags($texto));
    if (mb_strlen($limpio) <= $max) return $limpio;
    return mb_substr($limpio, 0, $max) . '…';
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'creado') $message = 'Proyecto creado correctamente.';
    elseif ($msg === 'editado') $message = 'Proyecto actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Proyecto eliminado.';
    elseif ($msg === 'stats_guardados') $message = 'Estad&iacute;sticas guardadas correctamente.';
}

$areas = array('Biotecnolog&iacute;a', 'TIC', 'Energ&iacute;a', 'Industria', 'Agricultura', 'Medio Ambiente', 'Salud', 'Educaci&oacute;n', 'Otros');
$estados = array('en ejecuci&oacute;n', 'finalizado', 'en desarrollo', 'propuesta');
$categorias = array('Fuentes renovables de energ&iacute;a', 'Producci&oacute;n de alimentos', 'Automatizaci&oacute;n e Inteligencia Artificial', 'Producci&oacute;n de materiales de la construcci&oacute;n', 'Incubaci&oacute;n de nuevas empresas');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inv&aacute;lido.';
    } else {
        if ($action === 'save_stats') {
            $stats = isset($_POST['stats']) ? $_POST['stats'] : array();
            $filtered = array();
            if (is_array($stats)) {
                foreach ($stats as $s) {
                    $label = trim($s['label'] ?? '');
                    $count = intval($s['count'] ?? 0);
                    if ($label !== '') {
                        $filtered[] = array('label' => $label, 'count' => $count);
                    }
                }
            }
            Storage::write('proyectos_stats', $filtered);
            header('Location: proyectos.php?msg=stats_guardados');
            exit;
        }
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $existing = Storage::findById('proyectos', $deleteId);
                $imgs = array();
                if (!empty($existing['imagenes']) && is_array($existing['imagenes'])) {
                    $imgs = $existing['imagenes'];
                } elseif (!empty($existing['imagen'])) {
                    $imgs = array($existing['imagen']);
                }
                foreach ($imgs as $img) {
                    $imgPath = '../' . $img;
                    if (file_exists($imgPath)) { unlink($imgPath); }
                }
                Storage::delete('proyectos', $deleteId);
                header('Location: proyectos.php?msg=eliminado');
                exit;
            }
        } else {
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
                'titulo' => trim($titulo),
                'slug' => $slug,
                'resumen' => $resumen,
                'contenido' => $contenido,
                'area' => trim($area),
                'categoria' => trim($categoria),
                'estado' => trim($estado),
                'responsable' => trim($responsable),
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'resultados' => trim($resultados),
                'publicada' => $publicada,
                'destacada' => $destacada
            );

            $imagenes = array();
            if ($action === 'edit' && $id > 0) {
                $existing = Storage::findById('proyectos', $id);
                if (!empty($existing['imagenes']) && is_array($existing['imagenes'])) {
                    $imgsExisting = $existing['imagenes'];
                } elseif (!empty($existing['imagen'])) {
                    $imgsExisting = array($existing['imagen']);
                } else {
                    $imgsExisting = array();
                }
                $keepStr = $_POST['keep_imgs'] ?? '';
                $keepArr = $keepStr !== '' ? explode(',', $keepStr) : array();
                foreach ($imgsExisting as $img) {
                    if (in_array($img, $keepArr)) {
                        $imagenes[] = $img;
                    } else {
                        $delPath = '../' . $img;
                        if (file_exists($delPath)) { unlink($delPath); }
                    }
                }
            }

            if (isset($_FILES['imagenes'])) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                $files = $_FILES['imagenes'];
                $cnt = is_array($files['tmp_name']) ? count($files['tmp_name']) : 0;
                for ($i = 0; $i < $cnt; $i++) {
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExts) || $files['size'][$i] > 5 * 1024 * 1024) {
                        $error = 'Imagen no v&aacute;lida (m&aacute;x 5MB, JPG/PNG/GIF/WEBP).';
                        continue;
                    }
                    $filename = 'proyecto_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $filepath = $uploadDir . $filename;
                    if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                        $imagenes[] = 'uploads/' . $filename;
                    }
                }
            }

            if (!empty($imagenes)) {
                $data['imagenes'] = $imagenes;
            } else {
                $data['imagenes'] = array();
            }

            if (empty($error)) {
                try {
                    if ($action === 'edit' && $id > 0) {
                        Storage::update('proyectos', $id, $data);
                        header('Location: proyectos.php?msg=editado');
                        exit;
                    } else {
                        Storage::insert('proyectos', $data);
                        header('Location: proyectos.php?msg=creado');
                        exit;
                    }
                } catch (Exception $e) {
                    $error = 'Error al guardar.';
                }
            }
        }
    }
}

$proyecto = null;
if ($action === 'edit' && $id > 0) {
    $proyecto = Storage::findById('proyectos', $id);
    if (!$proyecto) {
        $action = 'list';
        $error = 'Proyecto no encontrado.';
    }
}

$proyectos = null;
if ($action === 'list') {
    $proyectos = Storage::read('proyectos');
    usort($proyectos, function($a, $b) {
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
    <title>Admin - Proyectos</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php echo $action === 'list' ? 'Proyectos' : ($action === 'edit' ? 'Editar Proyecto' : 'Nuevo Proyecto'); ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">+ Nuevo</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($proyectos)): ?>
                            <p class="empty">No hay proyectos.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>T&iacute;tulo</th><th>Categor&iacute;a</th><th>&Aacute;rea</th><th>Estado</th><th>Responsable</th><th>Inicio</th><th>Img</th><th>Pub.</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($proyectos as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($p['titulo'] ?? 'Sin t&iacute;tulo', 0, 50)); ?></td>
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
                                    <td><span class="tag tag-<?php echo $p['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $p['publicada'] ? 'S&iacute;' : 'No'; ?></span></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete" data-confirm="¿Eliminar este proyecto?">
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

                    <div class="panel" style="margin-top:30px;">
                        <div class="panel-header"><h2>Estad&iacute;sticas</h2></div>
                        <div class="panel-body">
                            <?php $stats = Storage::read('proyectos_stats'); ?>
                            <form method="POST" action="?action=save_stats">
                                <?php echo csrfField(); ?>
                                <table class="table" id="stats-table">
                                    <thead><tr><th>Etiqueta</th><th>Valor</th><th></th></tr></thead>
                                    <tbody>
                                        <?php foreach ($stats as $i => $s): ?>
                                        <tr>
                                            <td><input type="text" name="stats[<?php echo $i; ?>][label]" value="<?php echo htmlspecialchars($s['label']); ?>" class="form-input" style="width:100%;" required></td>
                                            <td><input type="number" name="stats[<?php echo $i; ?>][count]" value="<?php echo intval($s['count']); ?>" class="form-input" style="width:100px;" required></td>
                                            <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">X</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div style="margin-top:12px;display:flex;gap:12px;align-items:center;">
                                    <button type="button" class="btn btn-secondary" onclick="addStatRow()">+ A&ntilde;adir</button>
                                    <button type="submit" class="btn btn-success">Guardar Estad&iacute;sticas</button>
                                </div>
                            </form>
                            <script>
                            function addStatRow() {
                                var tbody = document.querySelector('#stats-table tbody');
                                var idx = tbody.children.length;
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td><input type="text" name="stats['+idx+'][label]" class="form-input" style="width:100%;" required></td>' +
                                    '<td><input type="number" name="stats['+idx+'][count]" value="0" class="form-input" style="width:100px;" required></td>' +
                                    '<td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'tr\').remove()">X</button></td>';
                                tbody.appendChild(tr);
                            }
                            </script>
                        </div>
                    </div>

                <?php elseif ($action === 'new' || $action === 'edit'): ?>
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="titulo">T&iacute;tulo *</label>
                                    <input type="text" id="titulo" name="titulo" required value="<?php echo $proyecto ? htmlspecialchars($proyecto['titulo']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="imagenes">Im&aacute;genes</label>
                                    <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>
                                    <small style="display:block;margin-top:6px;color:#666;">Selecciona varias im&aacute;genes. Se mostrar&aacute;n en un carrusel.</small>
                                    <?php
                                    $imgsMostrar = array();
                                    if ($proyecto) {
                                        if (!empty($proyecto['imagenes']) && is_array($proyecto['imagenes'])) {
                                            $imgsMostrar = $proyecto['imagenes'];
                                        } elseif (!empty($proyecto['imagen'])) {
                                            $imgsMostrar = array($proyecto['imagen']);
                                        }
                                    }
                                    if (!empty($imgsMostrar)): ?>
                                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">
                                        <?php foreach ($imgsMostrar as $img): ?>
                                        <div style="position:relative;width:100px;height:80px;border:2px solid #ddd;border-radius:8px;overflow:hidden;">
                                            <img src="../<?php echo htmlspecialchars($img); ?>" style="width:100%;height:100%;object-fit:cover;">
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
                                    <label for="area">&Aacute;rea tem&aacute;tica *</label>
                                    <select id="area" name="area" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($areas as $a): ?>
                                        <option value="<?php echo $a; ?>" <?php echo ($proyecto && $proyecto['area'] === $a) ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="categoria">Categor&iacute;a *</label>
                                    <select id="categoria" name="categoria" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($categorias as $c): ?>
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
                                    <label for="fecha_fin">Fecha de finalizaci&oacute;n</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $proyecto ? ($proyecto['fecha_fin'] ?? '') : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="responsable">Responsable</label>
                                <input type="text" id="responsable" name="responsable" value="<?php echo $proyecto ? htmlspecialchars($proyecto['responsable'] ?? '') : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="contenido">Descripci&oacute;n completa *</label>
                                <textarea id="contenido" name="contenido" rows="10" required><?php echo $proyecto ? $proyecto['contenido'] : ''; ?></textarea>
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
