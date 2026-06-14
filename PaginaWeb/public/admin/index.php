<?php
/**
 * Dashboard del admin
 */
require_once '../../api/auth.php';
require_once '../../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();

$totalNoticias = Storage::count('noticias', array('tipo' => 'noticia'));
$totalEventos = Storage::count('noticias', array('tipo' => 'evento'));
$totalMensajes = Storage::count('mensajes');
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>Admin</h2></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><a href="noticias.php">Noticias / Eventos</a></li>
                    <li><a href="mensajes.php">Mensajes <?php if ($mensajesNoLeidos > 0): ?><span class="badge"><?php echo $mensajesNoLeidos; ?></span><?php endif; ?></a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="configuracion.php">Configuración</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p><?php echo htmlspecialchars($user['nombre']); ?></p>
                <a href="logout.php" class="btn-logout">Cerrar sesión</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Dashboard</h1>
                <span>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></span>
            </header>
            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">&#128196;</div>
                        <div class="stat-info"><h3><?php echo $totalNoticias; ?></h3><p>Noticias</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#128197;</div>
                        <div class="stat-info"><h3><?php echo $totalEventos; ?></h3><p>Eventos</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#9993;</div>
                        <div class="stat-info"><h3><?php echo $totalMensajes; ?></h3><p>Mensajes</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">&#128276;</div>
                        <div class="stat-info"><h3><?php echo $mensajesNoLeidos; ?></h3><p>Sin leer</p></div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="panel">
                        <div class="panel-header">
                            <h2>Últimas Publicaciones</h2>
                            <a href="noticias.php" class="btn btn-sm">Ver todas</a>
                        </div>
                        <div class="panel-body">
                            <?php if (empty($ultimasNoticias)): ?>
                                <p class="empty">No hay publicaciones aún.</p>
                            <?php else: ?>
                                <table class="table">
                                    <thead><tr><th>Título</th><th>Tipo</th><th>Estado</th><th>Fecha</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($ultimasNoticias as $n): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(substr($n['titulo'], 0, 40)); ?></td>
                                            <td><span class="tag tag-<?php echo $n['tipo']; ?>"><?php echo ucfirst($n['tipo']); ?></span></td>
                                            <td><span class="tag tag-<?php echo $n['publicada'] ? 'publicado' : 'borrador'; ?>"><?php echo $n['publicada'] ? 'Publicado' : 'Borrador'; ?></span></td>
                                            <td><?php echo date('d/m/Y', strtotime($n['created_at'])); ?></td>
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
                            <a href="mensajes.php" class="btn btn-sm">Ver todos</a>
                        </div>
                        <div class="panel-body">
                            <?php if (empty($ultimosMensajes)): ?>
                                <p class="empty">No hay mensajes aún.</p>
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
            </div>
        </main>
    </div>
</body>
</html>
