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

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'creado') $message = 'Proyecto creado correctamente.';
    elseif ($msg === 'editado') $message = 'Proyecto actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Proyecto eliminado.';
    elseif ($msg === 'cont_creado') $message = 'Contador creado correctamente.';
    elseif ($msg === 'cont_editado') $message = 'Contador actualizado correctamente.';
    elseif ($msg === 'cont_eliminado') $message = 'Contador eliminado.';
}

$areas = array('Biotecnolog&iacute;a', 'TIC', 'Energ&iacute;a', 'Industria', 'Agricultura', 'Medio Ambiente', 'Salud', 'Educaci&oacute;n', 'Otros');
$estados = array('en ejecuci&oacute;n', 'finalizado', 'en desarrollo', 'propuesta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inv&aacute;lido.';
    } else {
        $counterAction = $_POST['counter_action'] ?? '';
        if ($counterAction) {
            $counters = Storage::read('proyectos_counters');
            if ($counterAction === 'create') {
                $newId = 1;
                foreach ($counters as $c) {
                    if ($c['id'] >= $newId) $newId = $c['id'] + 1;
                }
                $counters[] = array(
                    'id' => $newId,
                    'numero' => intval($_POST['numero'] ?? 0),
                    'label' => trim($_POST['label'] ?? ''),
                    'orden' => intval($_POST['orden'] ?? count($counters) + 1)
                );
                Storage::write('proyectos_counters', $counters);
                header('Location: proyectos.php?msg=cont_creado');
                exit;
            } elseif ($counterAction === 'update') {
                $cid = intval($_POST['id'] ?? 0);
                foreach ($counters as &$c) {
                    if ($c['id'] === $cid) {
                        $c['numero'] = intval($_POST['numero'] ?? $c['numero']);
                        $c['label'] = trim($_POST['label'] ?? $c['label']);
                        $c['orden'] = intval($_POST['orden'] ?? $c['orden']);
                        break;
                    }
                }
                Storage::write('proyectos_counters', $counters);
                header('Location: proyectos.php?msg=cont_editado');
                exit;
            } elseif ($counterAction === 'delete') {
                $cid = intval($_POST['id'] ?? 0);
                $counters = array_values(array_filter($counters, function($c) use ($cid) {
                    return $c['id'] !== $cid;
                }));
                Storage::write('proyectos_counters', $counters);
                header('Location: proyectos.php?msg=cont_eliminado');
                exit;
            }
        } elseif ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $existing = Storage::findById('proyectos', $deleteId);
                if ($existing && isset($existing['imagen'])) {
                    $imgPath = '../' . $existing['imagen'];
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
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
                'titulo' => htmlspecialchars($titulo),
                'slug' => $slug,
                'resumen' => $resumen,
                'contenido' => $contenido,
                'area' => htmlspecialchars($area),
                'estado' => htmlspecialchars($estado),
                'responsable' => htmlspecialchars($responsable),
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'resultados' => htmlspecialchars($resultados),
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
                    $filename = 'proyecto_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $filepath = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                        $data['imagen'] = 'uploads/' . $filename;
                    }
                } else {
                    $error = 'Imagen no v&aacute;lida (m&aacute;x 5MB, JPG/PNG/GIF/WEBP).';
                }
            }

            if (empty($error)) {
                try {
                    if ($action === 'edit' && $id > 0) {
                        if (!isset($data['imagen'])) {
                            $existing = Storage::findById('proyectos', $id);
                            if ($existing && isset($existing['imagen'])) {
                                $data['imagen'] = $existing['imagen'];
                            }
                        }
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
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none;">☰</button>
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
                                <thead><tr><th>T&iacute;tulo</th><th>&Aacute;rea</th><th>Estado</th><th>Responsable</th><th>Inicio</th><th>Estado Pub.</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($proyectos as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($p['titulo'] ?? 'Sin t&iacute;tulo', 0, 50)); ?></td>
                                    <td><?php echo htmlspecialchars($p['area'] ?: '—'); ?></td>
                                    <td><span class="tag tag-<?php echo $p['estado'] === 'finalizado' ? 'publicado' : 'borrador'; ?>"><?php echo htmlspecialchars(ucfirst($p['estado'] ?: 'propuesta')); ?></span></td>
                                    <td><?php echo htmlspecialchars($p['responsable'] ?: '—'); ?></td>
                                    <td><?php echo $p['fecha_inicio'] ? date('d/m/Y', strtotime($p['fecha_inicio'])) : '—'; ?></td>
                                    <td><span class="tag tag-<?php echo $p['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $p['publicada'] ? 'Publicado' : 'Borrador'; ?></span></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete" onsubmit="return confirm('¿Eliminar este proyecto?')">
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
                        <div class="panel-header">
                            <h2>Contadores de la sección Proyectos</h2>
                        </div>
                        <div class="panel-body">
                            <?php
                            $proyCounters = Storage::read('proyectos_counters');
                            usort($proyCounters, function($a, $b) {
                                return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
                            });
                            ?>
                            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                                <?php foreach ($proyCounters as $c): ?>
                                <div style="background:linear-gradient(135deg,#00A0E1,#004966);color:#fff;padding:20px;border-radius:12px;text-align:center;">
                                    <div style="font-size:2rem;font-weight:900;"><?php echo intval($c['numero']); ?>+</div>
                                    <div style="font-size:0.85rem;opacity:0.85;"><?php echo htmlspecialchars($c['label']); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                                <div>
                                    <h3 style="font-size:1rem;color:#004966;margin-bottom:12px;">Nuevo contador</h3>
                                    <form method="POST">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="counter_action" value="create">
                                        <div class="form-group">
                                            <label>Etiqueta</label>
                                            <input type="text" name="label" required placeholder="Ej: Proyectos activos">
                                        </div>
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                            <div class="form-group">
                                                <label>Número</label>
                                                <input type="number" name="numero" required min="0" value="0">
                                            </div>
                                            <div class="form-group">
                                                <label>Orden</label>
                                                <input type="number" name="orden" required min="1" value="<?php echo count($proyCounters) + 1; ?>">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success">Crear</button>
                                    </form>
                                </div>
                                <div>
                                    <h3 style="font-size:1rem;color:#004966;margin-bottom:12px;">Contadores actuales</h3>
                                    <?php foreach ($proyCounters as $c): ?>
                                    <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:#fff;border-radius:8px;margin-bottom:8px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
                                        <div style="flex:1;">
                                            <strong><?php echo htmlspecialchars($c['label']); ?></strong>
                                            <span style="color:#888;font-size:0.82rem;display:block;"><?php echo intval($c['numero']); ?> | Orden: <?php echo intval($c['orden']); ?></span>
                                        </div>
                                        <button class="btn btn-sm btn-primary" onclick="document.getElementById('edit-counter-<?php echo $c['id']; ?>').style.display=document.getElementById('edit-counter-<?php echo $c['id']; ?>').style.display==='none'?'block':'none'">Editar</button>
                                        <form method="POST" onsubmit="return confirm('Eliminar este contador?')" style="margin:0;">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="counter_action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </div>
                                    <div id="edit-counter-<?php echo $c['id']; ?>" style="display:none;background:#f8f9fa;padding:16px;border-radius:10px;margin-bottom:12px;border:2px dashed #e0e0e0;">
                                        <form method="POST">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="counter_action" value="update">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <div class="form-group">
                                                <label>Etiqueta</label>
                                                <input type="text" name="label" required value="<?php echo htmlspecialchars($c['label']); ?>">
                                            </div>
                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                                <div class="form-group">
                                                    <label>Número</label>
                                                    <input type="number" name="numero" required min="0" value="<?php echo intval($c['numero']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Orden</label>
                                                    <input type="number" name="orden" required min="1" value="<?php echo intval($c['orden']); ?>">
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:8px;">
                                                <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="this.closest('#edit-counter-<?php echo $c['id']; ?>').style.display='none'">Cancelar</button>
                                            </div>
                                        </form>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
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
                                    <label for="imagen">Imagen</label>
                                    <input type="file" id="imagen" name="imagen" accept="image/*">
                                    <?php if ($proyecto && isset($proyecto['imagen'])): ?>
                                        <small>Actual: <?php echo basename($proyecto['imagen']); ?></small>
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
