<?php
/**
 * Configuración del sitio
 */
require_once '../../api/auth.php';
require_once '../../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$message = '';
$pwMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['site_name'])) {
        $campos = array('site_name', 'site_description', 'contact_email', 'contact_phone', 'contact_address');
        $config = Storage::read('config');
        foreach ($campos as $campo) {
            if (isset($_POST[$campo])) {
                $config[$campo] = trim($_POST[$campo]);
            }
        }
        Storage::write('config', $config);
        $message = 'Configuración actualizada.';
    }

    if (isset($_POST['new_password'])) {
        $newPass = $_POST['new_password'];
        $confirmPass = $_POST['confirm_password'];
        if (strlen($newPass) < 6) {
            $pwMessage = 'Mínimo 6 caracteres.';
        } elseif ($newPass !== $confirmPass) {
            $pwMessage = 'Las contraseñas no coinciden.';
        } else {
            $hashed = $auth->hashPassword($newPass);
            $usuarios = Storage::read('usuarios');
            foreach ($usuarios as &$u) {
                if ($u['id'] == $_SESSION['user_id']) {
                    $u['password'] = $hashed;
                    break;
                }
            }
            Storage::write('usuarios', $usuarios);
            $pwMessage = 'Contraseña actualizada.';
        }
    }
}

$config = Storage::read('config');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Configuración</title>
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
                    <li><a href="mensajes.php">Mensajes</a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="configuracion.php" class="active">Configuración</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></p>
                <a href="logout.php" class="btn-logout">Cerrar sesión</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="topbar"><h1>Configuración</h1></header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>

                <div class="form-card" style="margin-bottom:30px;">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Información del Sitio</h2>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nombre del Sitio</label>
                                <input type="text" name="site_name" value="<?php echo htmlspecialchars($config['site_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email de Contacto</label>
                                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($config['contact_email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="site_description" rows="3"><?php echo htmlspecialchars($config['site_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($config['contact_phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" name="contact_address" value="<?php echo htmlspecialchars($config['contact_address'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </form>
                </div>

                <div class="form-card">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Cambiar Contraseña</h2>
                    <?php if (!empty($pwMessage)): ?>
                        <div class="alert <?php echo strpos($pwMessage, 'actualizada') !== false ? 'alert-success' : 'alert-error'; ?>"><?php echo $pwMessage; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nueva Contraseña</label>
                                <input type="password" name="new_password" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label>Confirmar</label>
                                <input type="password" name="confirm_password" required minlength="6">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
