<?php
/**
 * Gestión de Mensajes
 */
require_once '../../api/auth.php';
require_once '../../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

if ($action === 'read' && $id > 0) {
    $msg = Storage::findById('mensajes', $id);
    if ($msg) {
        $msg['leido'] = 1;
        Storage::update('mensajes', $id, $msg);
    }
    $action = 'view';
}

if ($action === 'delete' && $id > 0) {
    Storage::delete('mensajes', $id);
    $message = 'Mensaje eliminado.';
    $action = 'list';
}

if ($action === 'readall') {
    $all = Storage::read('mensajes');
    foreach ($all as &$m) {
        $m['leido'] = 1;
    }
    Storage::write('mensajes', $all);
    $message = 'Todos marcados como leídos.';
    $action = 'list';
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Mensajes</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>Admin</h2></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="noticias.php">Noticias / Eventos</a></li>
                    <li><a href="mensajes.php" class="active">Mensajes <?php if ($noLeidos > 0): ?><span class="badge"><?php echo $noLeidos; ?></span><?php endif; ?></a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="configuracion.php">Configuración</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></p>
                <a href="logout.php" class="btn-logout">Cerrar sesión</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <h1>Mensajes de Contacto</h1>
                <?php if ($action === 'list' && $noLeidos > 0): ?>
                    <a href="?action=readall" class="btn btn-sm btn-secondary">Marcar todos leídos</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>

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
                                    <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($m['email']); ?></td>
                                    <td><?php echo htmlspecialchars($m['asunto']); ?></td>
                                    <td><span class="tag tag-<?php echo $m['leido'] ? 'leido' : 'noleido'; ?>"><?php echo $m['leido'] ? 'Leído' : 'Nuevo'; ?></span></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=view&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                                        <a href="?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">X</a>
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
                            <div><strong>Nombre:</strong> <?php echo htmlspecialchars($msg['nombre']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($msg['email']); ?></div>
                            <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($msg['telefono']); ?></div>
                            <div><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></div>
                        </div>
                        <div style="background:#f9f9f9; padding:20px; border-radius:5px; margin-bottom:20px;">
                            <?php echo nl2br(htmlspecialchars($msg['mensaje'])); ?>
                        </div>
                        <div class="form-actions">
                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['asunto']); ?>" class="btn btn-primary">Responder</a>
                            <a href="?action=list" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
