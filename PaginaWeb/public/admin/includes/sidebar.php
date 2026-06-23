<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = isset($user) ? $user : (isset($_SESSION['user_nombre']) ? array('nombre' => $_SESSION['user_nombre']) : array('nombre' => 'Admin'));
$mensajesNoLeidos = isset($mensajesNoLeidos) ? $mensajesNoLeidos : Storage::count('mensajes');
$isAdmin = isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Panel Admin</h2>
        <button class="hamburger" id="sidebarToggle" aria-label="Menu">☰</button>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li><a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                Dashboard
            </a></li>
            <li><a href="noticias.php" class="<?php echo $currentPage === 'noticias.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                Noticias
            </a></li>
            <li><a href="eventos.php" class="<?php echo $currentPage === 'eventos.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                Eventos
            </a></li>
            <li><a href="proyectos.php" class="<?php echo $currentPage === 'proyectos.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                Proyectos
            </a></li>
            <li><a href="galeria.php" class="<?php echo $currentPage === 'galeria.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                Galería
            </a></li>
            <li><a href="sliders.php" class="<?php echo $currentPage === 'sliders.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                Portada
            </a></li>
            <li><a href="servicios.php" class="<?php echo $currentPage === 'servicios.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                Servicios
            </a></li>
            <li><a href="flyers.php" class="<?php echo $currentPage === 'flyers.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                Flyers
            </a></li>
            <li><a href="mensajes.php" class="<?php echo $currentPage === 'mensajes.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Mensajes<?php if ($mensajesNoLeidos > 0): ?><span class="badge"><?php echo $mensajesNoLeidos; ?></span><?php endif; ?>
            </a></li>
            <li><a href="contadores.php" class="<?php echo $currentPage === 'contadores.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                Contadores
            </a></li>
            <li><a href="opiniones.php" class="<?php echo $currentPage === 'opiniones.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Opiniones
            </a></li>
            <?php if ($isAdmin): ?>
            <li><a href="usuarios.php" class="<?php echo $currentPage === 'usuarios.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Usuarios
            </a></li>
            <?php endif; ?>
            <li><a href="configuracion.php" class="<?php echo $currentPage === 'configuracion.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Configuración
            </a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <p><?php echo htmlspecialchars($user['nombre']); ?></p>
        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</aside>
<script src="js/admin.js" defer></script>
