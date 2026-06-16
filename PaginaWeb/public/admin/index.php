<?php
require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();

$totalNoticias = Storage::count('noticias', array('tipo' => 'noticia'));
$totalEventos = Storage::count('noticias', array('tipo' => 'evento'));
$totalGalería = Storage::count('galeria');
$totalMensajes = Storage::count('mensajes');
$totalProyectos = Storage::count('proyectos');
$mensajesNoLeidos = Storage::count('mensajes', array('leido' => 0));

$ultimasNoticias = Storage::read('noticias');
usort($ultimasNoticias, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$ultimasNoticias = array_slice($ultimasNoticias, 0, 5);

$ultimosMensajes = Storage::read('mensajes');
usort($ultimosMensajes, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$ultimosMensajes = array_slice($ultimosMensajes, 0, 5);

$ultimosProyectos = Storage::read('proyectos');
usort($ultimosProyectos, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$ultimosProyectos = array_slice($ultimosProyectos, 0, 5);

$iniciales = '';
if (!empty($user['nombre'])) {
    $parts = explode(' ', $user['nombre']);
    $iniciales = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $iniciales .= strtoupper(substr(end($parts), 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">☰</button>
                    <h1>Dashboard</h1>
                </div>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></span>
                    <div class="user-avatar"><?php echo $iniciales; ?></div>
                </div>
            </header>
            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">&#128196;</div>
                        <div class="stat-info">
                            <h3><?php echo $totalNoticias; ?></h3>
                            <p>Noticias</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#128197;</div>
                        <div class="stat-info">
                            <h3><?php echo $totalEventos; ?></h3>
                            <p>Eventos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#127748;</div>
                        <div class="stat-info">
                            <h3><?php echo $totalGalería; ?></h3>
                            <p>Galería</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#128640;</div>
                        <div class="stat-info">
                            <h3><?php echo $totalProyectos; ?></h3>
                            <p>Proyectos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#9993;</div>
                        <div class="stat-info">
                            <h3><?php echo $totalMensajes; ?></h3>
                            <p>Mensajes <?php if ($mensajesNoLeidos > 0): ?><span class="badge"><?php echo $mensajesNoLeidos; ?> nuevos</span><?php endif; ?></p>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="panel">
                        <div class="panel-header">
                            <h2>Últimas Publicaciones</h2>
                            <a href="noticias.php" class="btn btn-sm btn-primary">Ver todas</a>
                        </div>
                        <div class="panel-body">
                            <?php if (empty($ultimasNoticias)): ?>
                                <p class="empty">No hay publicaciones aun.</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead><tr><th>Título</th><th>Tipo</th><th>Estado</th><th>Fecha</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($ultimasNoticias as $n): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(substr($n['titulo'], 0, 40)); ?></td>
                                            <td><span class="tag tag-<?php echo $n['tipo']; ?>"><?php echo ucfirst($n['tipo']); ?></span></td>
                                            <td><span class="tag tag-<?php echo $n['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $n['publicada'] ? 'Publicado' : 'Borrador'; ?></span></td>
                                            <td><?php echo date('d/m/Y', strtotime($n['fecha_evento'] ?? $n['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h2>Últimos Mensajes</h2>
                            <a href="mensajes.php" class="btn btn-sm btn-primary">Ver todos</a>
                        </div>
                        <div class="panel-body">
                            <?php if (empty($ultimosMensajes)): ?>
                                <p class="empty">No hay mensajes aun.</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead><tr><th>Nombre</th><th>Asunto</th><th>Estado</th><th>Fecha</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($ultimosMensajes as $m): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(substr($m['nombre'], 0, 25)); ?></td>
                                            <td><?php echo htmlspecialchars($m['asunto']); ?></td>
                                            <td><span class="tag tag-<?php echo $m['leido'] ? 'leido' : 'noleido'; ?>"><?php echo $m['leido'] ? 'Leído' : 'Nuevo'; ?></span></td>
                                            <td><?php echo date('d/m/Y', strtotime($m['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2>&Uacute;ltimos Proyectos</h2>
                        <a href="proyectos.php" class="btn btn-sm btn-primary">Ver todos</a>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($ultimosProyectos)): ?>
                            <p class="empty">No hay proyectos aun.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>T&iacute;tulo</th><th>&Aacute;rea</th><th>Estado</th><th>Inicio</th></tr></thead>
                                <tbody>
                                    <?php foreach ($ultimosProyectos as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(substr($p['titulo'] ?? 'Sin t&iacute;tulo', 0, 40)); ?></td>
                                        <td><?php echo htmlspecialchars($p['area'] ?: '—'); ?></td>
                                        <td><span class="tag tag-<?php echo $p['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo htmlspecialchars(ucfirst($p['estado'] ?: 'propuesta')); ?></span></td>
                                        <td><?php echo $p['fecha_inicio'] ? date('d/m/Y', strtotime($p['fecha_inicio'])) : '—'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
