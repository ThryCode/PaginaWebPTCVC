<?php
require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                Storage::delete('mensajes', $deleteId);
                $message = 'Mensaje eliminado.';
            }
            $action = 'list';
        } elseif ($action === 'readall') {
            $all = Storage::read('mensajes');
            foreach ($all as &$m) {
                $m['leido'] = 1;
            }
            Storage::write('mensajes', $all);
            $message = 'Todos marcados como leídos.';
            $action = 'list';
        } elseif ($action === 'read') {
            $readId = intval($_POST['id'] ?? 0);
            if ($readId > 0) {
                $msg = Storage::findById('mensajes', $readId);
                if ($msg) {
                    $msg['leido'] = 1;
                    Storage::update('mensajes', $readId, $msg);
                }
            }
            $action = 'view';
            $id = $readId;
        }
    }
}

$msg = null;
if ($action === 'view' && $id > 0) {
    $msg = Storage::findById('mensajes', $id);
    if ($msg && !$msg['leido']) {
        $msg['leido'] = 1;
        Storage::update('mensajes', $id, $msg);
    }
}

$mensajes = null;
if ($action === 'list') {
    $mensajes = Storage::read('mensajes');
    usort($mensajes, function($a, $b) {
        if ($a['leido'] != $b['leido']) return $a['leido'] - $b['leido'];
        return strcmp($b['created_at'], $a['created_at']);
    });
}

$noLeidos = Storage::count('mensajes', array('leido' => 0));
$mensajesNoLeidos = $noLeidos;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=w<=768;document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Mensajes</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
</head>
<body>
    <div class="admin-wrapper">

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu">☰</button>
                <h1>Mensajes de Contacto</h1>
                <?php if ($action === 'list' && $noLeidos > 0): ?>
                    <form method="POST" action="?action=readall" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <button type="submit" class="btn btn-sm btn-secondary">Marcar todos leídos</button>
                    </form>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($mensajes)): ?>
                            <p class="empty">No hay mensajes.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Nombre</th><th>Email</th><th>Asunto</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($mensajes as $m): ?>
                                <tr style="<?php echo !$m['leido'] ? 'font-weight:bold; background:#f0f7ff;' : ''; ?>">
                                    <td data-label="Nombre"><?php echo htmlspecialchars($m['nombre'] . ( !empty($m['apellidos']) ? ' ' . $m['apellidos'] : '' )); ?></td>
                                    <td data-label="Email"><?php echo htmlspecialchars($m['correo']); ?></td>
                                    <td data-label="Asunto"><?php echo htmlspecialchars($m['asunto']); ?></td>
                                    <td data-label="Estado"><span class="tag tag-<?php echo $m['leido'] ? 'leido' : 'noleido'; ?>"><?php echo $m['leido'] ? 'Leído' : 'Nuevo'; ?></span></td>
                                    <td data-label="Fecha"><?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?></td>
                                    <td data-label="Acciones">
                                        <a href="?action=view&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                                        <form class="delete-form" method="POST" action="?action=delete" data-confirm="¿Eliminar este mensaje?">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">X</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div></div>

                <?php elseif ($action === 'view' && $msg): ?>
                    <div class="form-card">
                        <h2 style="margin-bottom:20px; color:#2c3e50;"><?php echo htmlspecialchars($msg['asunto']); ?></h2>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                            <div><strong>Nombre:</strong> <?php echo htmlspecialchars($msg['nombre'] . ( !empty($msg['apellidos']) ? ' ' . $msg['apellidos'] : '' )); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($msg['correo']); ?></div>
                            <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($msg['telefono']); ?></div>
                            <div><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></div>
                        </div>
                        <div style="background:#f9f9f9; padding:20px; border-radius:5px; margin-bottom:20px; word-break:break-word; overflow-wrap:break-word;">
                            <?php echo nl2br(htmlspecialchars($msg['mensaje'])); ?>
                        </div>
                        <div class="form-actions">
                            <a href="mailto:<?php echo htmlspecialchars($msg['correo']); ?>?subject=Re: <?php echo urlencode($msg['asunto']); ?>" class="btn btn-primary">Responder</a>
                            <a href="?action=list" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        <?php include 'includes/sidebar.php'; ?>
    </div>
</body>
</html>
