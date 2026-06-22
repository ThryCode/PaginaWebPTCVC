<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../data/admin_error.log');

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'primaria';
$message = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'creado') $message = 'Servicio creado correctamente.';
    elseif ($msg === 'editado') $message = 'Servicio actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Servicio eliminado.';
}

$iconos = array(
    'proyectos'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
    'chart'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',
    'equipo'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'enlace'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
    'carpeta'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
    'edificio'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21h18"/><path d="M3 7v14"/><path d="M21 7v14"/><path d="M6 11h3v4H6z"/><path d="M15 11h3v4h-3z"/><path d="M9 3h6v4H9z"/></svg>',
    'libro'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8"/><path d="M8 11h6"/></svg>',
    'moneda'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'documento'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>',
    'globo'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    'personas'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
    'clipboard'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
    'estrella'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2 9.5 9.5 2 12l7.5 2.5L12 22l2.5-7.5L22 12l-7.5-2.5z"/></svg>',
    'calendario'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><path d="M21 6v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'casa'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 22V12h6v10"/></svg>',
    'impresora'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 17v2a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-2"/><path d="M5 7h14"/><path d="M3 7V4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3"/><path d="M19 7v8H5V7"/></svg>',
    'monitor'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>',
    'carrito'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    'restaurante'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><path d="M6 1v3"/><path d="M10 1v3"/><path d="M14 1v3"/></svg>',
    'llave'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
    'checked'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inv&aacute;lido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                Storage::delete('servicios', $deleteId);
                header('Location: servicios.php?tab=' . $tab . '&msg=eliminado');
                exit;
            }
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'primaria');
            $icono = trim($_POST['icono'] ?? 'documento');
            $orden = intval($_POST['orden'] ?? 0);

            if (empty($nombre)) {
                $error = 'El nombre es obligatorio.';
            } else {
                $data = array(
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'tipo' => $tipo,
                    'icono' => $icono,
                    'orden' => $orden
                );

                try {
                    if ($action === 'edit' && $id > 0) {
                        Storage::update('servicios', $id, $data);
                        header('Location: servicios.php?tab=' . $tipo . '&msg=editado');
                        exit;
                    } else {
                        Storage::insert('servicios', $data);
                        header('Location: servicios.php?tab=' . $tipo . '&msg=creado');
                        exit;
                    }
                } catch (Exception $e) {
                    $error = 'Error al guardar.';
                }
            }
        }
    }
}

$servicio = null;
if ($action === 'edit' && $id > 0) {
    $servicio = Storage::findById('servicios', $id);
    if (!$servicio) {
        $action = 'list';
        $error = 'Servicio no encontrado.';
    } else {
        $tab = $servicio['tipo'] ?? 'primaria';
    }
}

$primarias = array();
$secundarias = array();
if ($action === 'list') {
    $all = Storage::read('servicios');
    usort($all, function($a, $b) {
        if ($a['tipo'] === $b['tipo']) {
            return $a['orden'] - $b['orden'];
        }
        return strcmp($a['tipo'], $b['tipo']);
    });
    foreach ($all as $s) {
        if ($s['tipo'] === 'primaria') {
            $primarias[] = $s;
        } else {
            $secundarias[] = $s;
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
    <title>Admin - Servicios</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .tabs-nav { display:flex; gap:0; margin-bottom:20px; border-bottom:2px solid #e0e0e0; }
        .tabs-nav a { padding:10px 24px; text-decoration:none; color:#666; font-weight:600; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
        .tabs-nav a:hover { color:#004966; }
        .tabs-nav a.active { color:#00A0E1; border-bottom-color:#00A0E1; }
        .icono-preview { width:48px; height:48px; display:flex; align-items:center; justify-content:center; border:2px solid #e0e0e0; border-radius:10px; color:#00A0E1; transition:all 0.2s; }
        .icono-preview:hover { border-color:#00A0E1; background:#f0f9ff; }
        .icono-preview.selected { border-color:#00A0E1; background:#e6f4fa; box-shadow:0 0 0 3px rgba(0,160,225,0.2); }
        .icono-preview svg { width:24px; height:24px; }
        .icono-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(60px, 1fr)); gap:8px; }
        .icono-option { cursor:pointer; position:relative; }
        .icono-option input[type="radio"] { position:absolute; opacity:0; width:0; height:0; }
        .icono-option input[type="radio"]:checked + .icono-preview { border-color:#00A0E1; background:#e6f4fa; box-shadow:0 0 0 3px rgba(0,160,225,0.2); }
        .icono-label { text-align:center; font-size:0.7rem; color:#888; margin-top:2px; }
        .servicio-icono { width:36px; height:36px; color:#00A0E1; flex-shrink:0; }
        .servicio-icono svg { width:100%; height:100%; }
        .servicio-row { display:flex; align-items:center; gap:10px; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php echo $action === 'list' ? 'Servicios' : ($action === 'edit' ? 'Editar Servicio' : 'Nuevo Servicio'); ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new&tab=<?php echo $tab; ?>" class="btn btn-primary">+ Nuevo</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="tabs-nav">
                        <a href="?tab=primaria" class="<?php echo $tab === 'primaria' ? 'active' : ''; ?>">Actividades Primarias</a>
                        <a href="?tab=secundaria" class="<?php echo $tab === 'secundaria' ? 'active' : ''; ?>">Actividades Secundarias</a>
                    </div>

                    <div class="panel"><div class="panel-body">
                        <?php $items = $tab === 'primaria' ? $primarias : $secundarias; ?>
                        <?php if (empty($items)): ?>
                            <p class="empty">No hay servicios en esta categor&iacute;a.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Icono</th><th>Nombre</th><th>Descripci&oacute;n</th><th>Orden</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($items as $s): ?>
                                <tr>
                                    <td>
                                        <div class="servicio-row">
                                            <div class="servicio-icono"><?php echo $iconos[$s['icono']] ?? $iconos['documento']; ?></div>
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($s['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(substr($s['descripcion'], 0, 80)); ?><?php echo mb_strlen($s['descripcion']) > 80 ? '…' : ''; ?></td>
                                    <td><?php echo $s['orden']; ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $s['id']; ?>&tab=<?php echo $tab; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form class="delete-form" method="POST" action="?action=delete&tab=<?php echo $tab; ?>" data-confirm="¿Eliminar este servicio?">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
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
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" id="nombre" name="nombre" required value="<?php echo $servicio ? htmlspecialchars($servicio['nombre']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="tipo">Tipo *</label>
                                    <select id="tipo" name="tipo" required>
                                        <option value="primaria" <?php echo ($servicio && $servicio['tipo'] === 'primaria') ? 'selected' : ($tab === 'primaria' ? 'selected' : ''); ?>>Actividad Primaria</option>
                                        <option value="secundaria" <?php echo ($servicio && $servicio['tipo'] === 'secundaria') ? 'selected' : ($tab === 'secundaria' ? 'selected' : ''); ?>>Actividad Secundaria</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripci&oacute;n *</label>
                                <textarea id="descripcion" name="descripcion" rows="4" required><?php echo $servicio ? htmlspecialchars($servicio['descripcion']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="orden">Orden</label>
                                <input type="number" id="orden" name="orden" min="0" value="<?php echo $servicio ? $servicio['orden'] : 0; ?>">
                            </div>
                            <div class="form-group">
                                <label>Icono</label>
                                <div class="icono-grid">
                                    <?php foreach ($iconos as $key => $svg): ?>
                                    <label class="icono-option">
                                        <input type="radio" name="icono" value="<?php echo $key; ?>" <?php echo ($servicio && $servicio['icono'] === $key) ? 'checked' : ($key === 'documento' && !$servicio ? 'checked' : ''); ?>>
                                        <div class="icono-preview"><?php echo $svg; ?></div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?tab=<?php echo $tab; ?>" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>