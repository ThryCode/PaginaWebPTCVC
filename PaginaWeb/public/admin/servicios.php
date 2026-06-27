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
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'primaria';
$message = '';
$error = '';

// TIC items managed via Storage::read('tic') / Storage::write('tic')

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'creado') $message = 'Servicio creado correctamente.';
    elseif ($msg === 'editado') $message = 'Servicio actualizado correctamente.';
    elseif ($msg === 'eliminado') $message = 'Servicio eliminado.';
    elseif ($msg === 'tic_creado') $message = 'Ítem TIC agregado correctamente.';
    elseif ($msg === 'tic_editado') $message = 'Ítem TIC actualizado correctamente.';
    elseif ($msg === 'tic_eliminado') $message = 'Ítem TIC eliminado correctamente.';
    elseif ($msg === 'tic_reordenado') $message = 'Ítems TIC reordenados correctamente.';
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
        } elseif ($action === 'tic_create') {
            $texto = trim($_POST['tic_texto'] ?? '');
            $orden = intval($_POST['tic_orden'] ?? count(Storage::read('tic')) + 1);
            if (!empty($texto)) {
                $items = Storage::read('tic');
                $newId = 1;
                foreach ($items as $it) {
                    if ($it['id'] >= $newId) $newId = $it['id'] + 1;
                }
                $items[] = array('id' => $newId, 'texto' => $texto, 'orden' => $orden);
                Storage::write('tic', $items);
                header('Location: servicios.php?tab=estrategico&msg=tic_creado');
                exit;
            } else {
                $error = 'El texto del ítem TIC es obligatorio.';
            }
        } elseif ($action === 'tic_update') {
            $id = intval($_POST['id'] ?? 0);
            $texto = trim($_POST['tic_texto'] ?? '');
            $orden = intval($_POST['tic_orden'] ?? 1);
            if ($id > 0 && !empty($texto)) {
                $items = Storage::read('tic');
                foreach ($items as &$it) {
                    if ($it['id'] === $id) {
                        $it['texto'] = $texto;
                        $it['orden'] = $orden;
                        break;
                    }
                }
                Storage::write('tic', $items);
                header('Location: servicios.php?tab=estrategico&msg=tic_editado');
                exit;
            } else {
                $error = 'Datos inválidos para actualizar ítem TIC.';
            }
        } elseif ($action === 'tic_delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $items = Storage::read('tic');
                $items = array_filter($items, function($it) use ($deleteId) {
                    return $it['id'] !== $deleteId;
                });
                $items = array_values($items);
                Storage::write('tic', $items);
                header('Location: servicios.php?tab=estrategico&msg=tic_eliminado');
                exit;
            }
        } elseif ($action === 'tic_reorder') {
            $order = $_POST['order'] ?? array();
            $items = Storage::read('tic');
            foreach ($order as $idx => $idVal) {
                foreach ($items as &$it) {
                    if ($it['id'] === intval($idVal)) {
                        $it['orden'] = $idx + 1;
                        break;
                    }
                }
            }
            Storage::write('tic', $items);
            header('Location: servicios.php?tab=estrategico&msg=tic_reordenado');
            exit;
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'primaria');
            $pagina = trim($_POST['pagina'] ?? 'servicios');
            $icono = trim($_POST['icono'] ?? 'documento');
            $orden = intval($_POST['orden'] ?? 0);

            if (empty($nombre)) {
                $error = 'El nombre es obligatorio.';
            } else {
                $data = array(
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'pagina' => $pagina,
                    'tipo' => $pagina === 'servicios' ? $tipo : '',
                    'icono' => $icono,
                    'orden' => $orden
                );

                try {
                    if ($action === 'edit' && $id > 0) {
                        Storage::update('servicios', $id, $data);
                        header('Location: servicios.php?tab=' . $tab . '&msg=editado');
                        exit;
                    } else {
                        Storage::insert('servicios', $data);
                        header('Location: servicios.php?tab=' . $tab . '&msg=creado');
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
        $pag = $servicio['pagina'] ?? 'servicios';
        if ($pag === 'servicios') {
            $tab = $servicio['tipo'] ?? 'primaria';
        } else {
            $tab = $pag;
        }
    }
}

$primarias = array();
$secundarias = array();
$estrategicos = array();
$prodCoop = array();
$incEmp = array();
if ($action === 'list') {
    $all = Storage::read('servicios');
    foreach ($all as $s) {
        $pag = $s['pagina'] ?? 'servicios';
        if ($pag === 'servicios') {
            $t = $s['tipo'] ?? 'secundaria';
            if ($t === 'primaria') {
                $primarias[] = $s;
            } elseif ($t === 'estrategico') {
                $estrategicos[] = $s;
            } else {
                $secundarias[] = $s;
            }
        } elseif ($pag === 'producciones-cooperadas') {
            $prodCoop[] = $s;
        } elseif ($pag === 'incubacion-empresas') {
            $incEmp[] = $s;
        }
    }
    usort($primarias, function($a, $b) { return $a['orden'] - $b['orden']; });
    usort($secundarias, function($a, $b) { return $a['orden'] - $b['orden']; });
    usort($estrategicos, function($a, $b) { return $a['orden'] - $b['orden']; });
    usort($prodCoop, function($a, $b) { return $a['orden'] - $b['orden']; });
    usort($incEmp, function($a, $b) { return $a['orden'] - $b['orden']; });
}

$ticItems = Storage::read('tic');
usort($ticItems, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=1, user-scalable=no';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Servicios</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
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

        .tic-section { margin-top:32px; border-top:2px solid #e0e0e0; padding-top:24px; }
        .tic-section h3 { color:#1a1a2e; margin-bottom:16px; font-size:1.1rem; }
        .tic-form { display:flex; gap:12px; align-items:flex-end; margin-bottom:20px; flex-wrap:wrap; }
        .tic-form .form-group { margin-bottom:0; flex:1; min-width:200px; }
        .tic-form .form-group label { font-size:0.82rem; }
        .tic-list { }
        .tic-row { display:flex; align-items:center; gap:12px; padding:10px 14px; background:#fff; border-radius:8px; margin-bottom:6px; box-shadow:0 1px 4px rgba(0,0,0,0.05); cursor:grab; }
        .tic-row.dragging { opacity:0.5; }
        .tic-row .drag-handle { cursor:grab; color:#ccc; font-size:1rem; user-select:none; }
        .tic-row .tic-text { flex:1; font-size:0.9rem; }
        .tic-row .tic-orden { color:#888; font-size:0.78rem; min-width:40px; text-align:center; }
        .tic-row .actions { display:flex; gap:4px; }
        .tic-inline-edit { margin-bottom:10px; }
        .tic-inline-edit form { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; }
        .tic-inline-edit .form-group { margin-bottom:0; }
        .tic-inline-edit input[type="text"] { min-width:250px; }
        @media (max-width:768px) { .tic-form { flex-direction:column; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu" style="display:none;">☰</button>
                <h1><?php
                    $pagTitles = array('primaria'=>'Servicios - Primarias', 'secundaria'=>'Servicios - Secundarias', 'estrategico'=>'Servicios - Estratégicos', 'producciones-cooperadas'=>'Producciones Cooperadas', 'incubacion-empresas'=>'Incubación de Empresas');
                    $listTitle = $pagTitles[$tab] ?? 'Servicios';
                    if ($action === 'list') {
                        echo $listTitle;
                    } else {
                        $isServicios = in_array($tab, array('primaria','secundaria','estrategico'));
                        echo ($action === 'edit' ? 'Editar ' : 'Nuevo ') . ($isServicios ? 'Servicio' : 'Elemento');
                    }
                ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new&tab=<?php echo $tab; ?>" class="btn btn-primary">+ Nuevo</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="tabs-nav">
                        <a href="?tab=primaria" class="<?php echo $tab === 'primaria' ? 'active' : ''; ?>">Primarias</a>
                        <a href="?tab=secundaria" class="<?php echo $tab === 'secundaria' ? 'active' : ''; ?>">Secundarias</a>
                        <a href="?tab=estrategico" class="<?php echo $tab === 'estrategico' ? 'active' : ''; ?>">Estratégicos</a>
                        <a href="?tab=producciones-cooperadas" class="<?php echo $tab === 'producciones-cooperadas' ? 'active' : ''; ?>">Prod. Cooperadas</a>
                        <a href="?tab=incubacion-empresas" class="<?php echo $tab === 'incubacion-empresas' ? 'active' : ''; ?>">Incubación</a>
                    </div>

                    <div class="panel"><div class="panel-body">
                        <?php
                        if ($tab === 'primaria') $items = $primarias;
                        elseif ($tab === 'secundaria') $items = $secundarias;
                        elseif ($tab === 'estrategico') $items = $estrategicos;
                        elseif ($tab === 'producciones-cooperadas') $items = $prodCoop;
                        elseif ($tab === 'incubacion-empresas') $items = $incEmp;
                        else $items = array();
                        ?>
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

                    <?php if ($tab === 'estrategico'): ?>
                    <div class="tic-section">
                        <h3>Servicios TIC</h3>
                        <p style="color:#888;font-size:0.85rem;margin-bottom:16px;">Ítems que aparecen en la lista "Servicios vinculados a las Tecnologías de la Información y las Comunicaciones".</p>

                        <form class="tic-form" method="POST" action="?action=tic_create">
                            <?php echo csrfField(); ?>
                            <div class="form-group">
                                <label for="tic_texto">Texto del servicio</label>
                                <input type="text" id="tic_texto" name="tic_texto" required placeholder="Ej: Desarrollo de sitios web.">
                            </div>
                            <div class="form-group" style="min-width:80px;flex:0 0 80px;">
                                <label for="tic_orden">Orden</label>
                                <input type="number" id="tic_orden" name="tic_orden" min="1" value="<?php echo count($ticItems) + 1; ?>">
                            </div>
                            <button type="submit" class="btn btn-success">Agregar</button>
                        </form>

                        <?php if (empty($ticItems)): ?>
                            <p class="empty">No hay ítems TIC. Agrega el primero.</p>
                        <?php else: ?>
                            <div class="tic-list" id="ticList">
                                <?php foreach ($ticItems as $it): ?>
                                <div class="tic-row" data-id="<?php echo $it['id']; ?>">
                                    <span class="drag-handle">☰</span>
                                    <span class="tic-text"><?php echo htmlspecialchars($it['texto']); ?></span>
                                    <span class="tic-orden">Ord. <?php echo intval($it['orden']); ?></span>
                                    <div class="actions">
                                        <button class="btn btn-sm btn-primary" data-tic-edit="<?php echo $it['id']; ?>">Editar</button>
                                        <form class="delete-form" method="POST" action="?action=tic_delete" data-confirm="Eliminar este ítem TIC?">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $it['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="tic-inline-edit" id="ticEdit-<?php echo $it['id']; ?>" style="display:none;">
                                    <form method="POST" action="?action=tic_update">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $it['id']; ?>">
                                        <div class="form-group">
                                            <input type="text" name="tic_texto" required value="<?php echo htmlspecialchars($it['texto']); ?>">
                                        </div>
                                        <div class="form-group" style="min-width:70px;flex:0 0 70px;">
                                            <input type="number" name="tic_orden" min="1" value="<?php echo intval($it['orden']); ?>">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-tic-cancel="<?php echo $it['id']; ?>">Cancelar</button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p style="color:#aaa;font-size:0.78rem;margin-top:8px;">Arrastra los ítems para reordenar (el orden se guarda automáticamente).</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                <?php elseif ($action === 'new' || $action === 'edit'):
                    $currentPagina = 'servicios';
                    $currentTipo = 'primaria';
                    if ($servicio) {
                        $currentPagina = $servicio['pagina'] ?? 'servicios';
                        $currentTipo = $servicio['tipo'] ?? 'primaria';
                    } else {
                        $servTabs = array('primaria','secundaria','estrategico');
                        if (in_array($tab, $servTabs)) {
                            $currentPagina = 'servicios';
                            $currentTipo = $tab;
                        } else {
                            $currentPagina = $tab;
                        }
                    }
                    $isServiciosForm = $currentPagina === 'servicios';
                ?>
                    <div class="form-card">
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="pagina" value="<?php echo $currentPagina; ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" id="nombre" name="nombre" required value="<?php echo $servicio ? htmlspecialchars($servicio['nombre']) : ''; ?>">
                                </div>
                                <?php if ($isServiciosForm): ?>
                                <div class="form-group">
                                    <label for="tipo">Tipo *</label>
                                    <select id="tipo" name="tipo" required>
                                        <option value="primaria" <?php echo ($servicio && $currentTipo === 'primaria') ? 'selected' : ($currentTipo === 'primaria' ? 'selected' : ''); ?>>Actividad Primaria</option>
                                        <option value="secundaria" <?php echo ($servicio && $currentTipo === 'secundaria') ? 'selected' : ($currentTipo === 'secundaria' ? 'selected' : ''); ?>>Actividad Secundaria</option>
                                        <option value="estrategico" <?php echo ($servicio && $currentTipo === 'estrategico') ? 'selected' : ($currentTipo === 'estrategico' ? 'selected' : ''); ?>>Estratégico</option>
                                    </select>
                                </div>
                                <?php endif; ?>
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

    <script>
    (function() {
        var ticEditBtns = document.querySelectorAll('[data-tic-edit]');
        ticEditBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.ticEdit;
                var form = document.getElementById('ticEdit-' + id);
                if (form) form.style.display = form.style.display === 'none' ? 'flex' : 'none';
            });
        });

        var ticCancelBtns = document.querySelectorAll('[data-tic-cancel]');
        ticCancelBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.ticCancel;
                var form = document.getElementById('ticEdit-' + id);
                if (form) form.style.display = 'none';
            });
        });

        var ticList = document.getElementById('ticList');
        if (ticList) {
            var dragItem = null;
            var csrf = document.querySelector('input[name="csrf_token"]').value;

            ticList.addEventListener('dragstart', function(e) {
                var row = e.target.closest('.tic-row');
                if (!row) return;
                dragItem = row;
                row.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            ticList.addEventListener('dragover', function(e) {
                e.preventDefault();
                var row = e.target.closest('.tic-row');
                if (!row || row === dragItem) return;
                var rect = row.getBoundingClientRect();
                var midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    ticList.insertBefore(dragItem, row);
                } else {
                    ticList.insertBefore(dragItem, row.nextSibling);
                }
            });

            ticList.addEventListener('dragend', function() {
                if (!dragItem) return;
                dragItem.classList.remove('dragging');

                var rows = ticList.querySelectorAll('.tic-row');
                var order = [];
                rows.forEach(function(r) { order.push(r.dataset.id); });

                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '?action=tic_reorder';
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = '<?php echo CSRF_TOKEN_NAME; ?>';
                inp.value = csrf;
                form.appendChild(inp);
                order.forEach(function(id) {
                    var o = document.createElement('input');
                    o.type = 'hidden';
                    o.name = 'order[]';
                    o.value = id;
                    form.appendChild(o);
                });
                document.body.appendChild(form);
                form.submit();
                dragItem = null;
            });

            ticList.querySelectorAll('.tic-row').forEach(function(el) {
                el.draggable = true;
            });
        }
    })();
    </script>
</body>
</html>
