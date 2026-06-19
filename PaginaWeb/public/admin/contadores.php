<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$countersFile = DATA_DIR . '/counters.json';

function loadCounters($file) {
    if (!file_exists($file)) return array();
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : array();
}

function saveCounters($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        $action = $_POST['action'] ?? '';
        $counters = loadCounters($countersFile);

        if ($action === 'create') {
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
            saveCounters($countersFile, $counters);
            header('Location: contadores.php?msg=created');
            exit;

        } elseif ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            foreach ($counters as &$c) {
                if ($c['id'] === $id) {
                    $c['numero'] = intval($_POST['numero'] ?? $c['numero']);
                    $c['label'] = trim($_POST['label'] ?? $c['label']);
                    $c['orden'] = intval($_POST['orden'] ?? $c['orden']);
                    break;
                }
            }
            saveCounters($countersFile, $counters);
            header('Location: contadores.php?msg=updated');
            exit;

        } elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            $counters = array_filter($counters, function($c) use ($id) {
                return $c['id'] !== $id;
            });
            $counters = array_values($counters);
            saveCounters($countersFile, $counters);
            header('Location: contadores.php?msg=deleted');
            exit;

        } elseif ($action === 'reorder') {
            $order = $_POST['order'] ?? array();
            foreach ($order as $idx => $id) {
                foreach ($counters as &$c) {
                    if ($c['id'] === intval($id)) {
                        $c['orden'] = $idx + 1;
                        break;
                    }
                }
            }
            saveCounters($countersFile, $counters);
            header('Location: contadores.php?msg=reordered');
            exit;
        }
    }
}

$counters = loadCounters($countersFile);
usort($counters, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

$csrfToken = generateCSRFToken();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=1, user-scalable=no';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Contadores</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .counter-preview { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 30px; }
        .counter-preview-item { background: linear-gradient(135deg, #003c6e, #008674); color: #fff; padding: 24px; border-radius: 12px; text-align: center; }
        .counter-preview-item .num { font-size: 2.2rem; font-weight: 900; }
        .counter-preview-item .lbl { font-size: 0.85rem; opacity: 0.85; margin-top: 4px; }
        .counter-row { display: flex; align-items: center; gap: 16px; padding: 16px 20px; background: #fff; border-radius: 10px; margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .counter-row .drag-handle { cursor: grab; color: #ccc; font-size: 1.2rem; user-select: none; }
        .counter-row .counter-data { flex: 1; }
        .counter-row .counter-data h4 { color: #1a1a2e; font-size: 1rem; }
        .counter-row .counter-data span { color: #888; font-size: 0.82rem; }
        .counter-row .actions { display: flex; gap: 8px; }
        .edit-form { background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px dashed #e0e0e0; display: none; }
        .edit-form.active { display: block; }
        .inline-grid { display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 12px; }
        @media (max-width: 768px) { .counter-preview { grid-template-columns: 1fr 1fr; } .inline-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="hamburger" aria-label="Menu">☰</button>
                    <h1>Contadores</h1>
                </div>
            </header>
            <div class="content">
                <?php if ($msg === 'created'): ?>
                    <div class="alert alert-success">Contador creado correctamente.</div>
                <?php elseif ($msg === 'updated'): ?>
                    <div class="alert alert-success">Contador actualizado correctamente.</div>
                <?php elseif ($msg === 'deleted'): ?>
                    <div class="alert alert-success">Contador eliminado correctamente.</div>
                <?php endif; ?>

                <h3 style="margin-bottom:16px;color:#1a1a2e;">Vista previa</h3>
                <div class="counter-preview">
                    <?php if (empty($counters)): ?>
                        <p class="empty" style="grid-column:1/-1;">No hay contadores. Crea uno nuevo.</p>
                    <?php else: ?>
                        <?php foreach ($counters as $c): ?>
                            <div class="counter-preview-item">
                                <div class="num"><?php echo intval($c['numero']); ?></div>
                                <div class="lbl"><?php echo htmlspecialchars($c['label']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="grid-2">
                    <div class="panel">
                        <div class="panel-header">
                            <h2>Crear nuevo contador</h2>
                        </div>
                        <div class="panel-body">
                            <form method="POST">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="create">
                                <div class="form-group">
                                    <label for="new_label">Etiqueta (ej: Empresas incubadas)</label>
                                    <input type="text" id="new_label" name="label" required placeholder=" Nombre de la categoría">
                                </div>
                                <div class="inline-grid">
                                    <div class="form-group">
                                        <label for="new_numero">Número</label>
                                        <input type="number" id="new_numero" name="numero" required min="0" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_orden">Orden</label>
                                        <input type="number" id="new_orden" name="orden" required min="1" value="<?php echo count($counters) + 1; ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Crear Contador</button>
                            </form>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h2>Contadores actuales</h2>
                        </div>
                        <div class="panel-body">
                            <?php if (empty($counters)): ?>
                                <p class="empty">No hay contadores creados aun.</p>
                            <?php else: ?>
                                <?php foreach ($counters as $c): ?>
                                    <div class="counter-row" id="counter-<?php echo $c['id']; ?>">
                                        <span class="drag-handle">☰</span>
                                        <div class="counter-data">
                                            <h4><?php echo htmlspecialchars($c['label']); ?></h4>
                                            <span>Número: <?php echo intval($c['numero']); ?> | Orden: <?php echo intval($c['orden']); ?></span>
                                        </div>
                                        <div class="actions">
                                            <button class="btn btn-sm btn-primary" data-toggle-edit="<?php echo $c['id']; ?>">Editar</button>
                                            <form class="delete-form" method="POST" data-confirm="Eliminar este contador?">
                                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="edit-form" id="edit-<?php echo $c['id']; ?>">
                                        <form method="POST">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <div class="form-group">
                                                <label>Etiqueta</label>
                                                <input type="text" name="label" required value="<?php echo htmlspecialchars($c['label']); ?>">
                                            </div>
                                            <div class="inline-grid">
                                                <div class="form-group">
                                                    <label>Número</label>
                                                    <input type="number" name="numero" required min="0" value="<?php echo intval($c['numero']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Orden</label>
                                                    <input type="number" name="orden" required min="1" value="<?php echo intval($c['orden']); ?>">
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:10px;">
                                                <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                                <button type="button" class="btn btn-sm btn-secondary" data-toggle-edit="<?php echo $c['id']; ?>">Cancelar</button>
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
