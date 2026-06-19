<?php
require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();
if (!$auth->isAdmin()) {
    header('Location: index.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inv&aacute;lido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0 && $deleteId != $_SESSION['user_id']) {
                $auth->deleteUser($deleteId);
                $message = 'Usuario eliminado.';
            } else {
                $error = 'No puedes eliminar tu propia cuenta.';
            }
            $action = 'list';
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $rol = $_POST['rol'] ?? 'editor';
            $activo = isset($_POST['activo']) ? true : false;
            $newPassword = trim($_POST['password'] ?? '');

            if (empty($nombre) || empty($email)) {
                $error = 'Nombre y email son obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email no v&aacute;lido.';
            } else {
                if ($action === 'new') {
                    if (empty($newPassword)) {
                        $error = 'Contrase&ntilde;a es obligatoria para nuevos usuarios.';
                    } elseif (strlen($newPassword) < 8) {
                        $error = 'Contrase&ntilde;a m&iacute;nimo 8 caracteres.';
                    } else {
                        $auth->createUser($nombre, $email, $rol, $newPassword);
                        $message = 'Usuario creado.';
                        $action = 'list';
                    }
                } elseif ($action === 'edit' && $id > 0) {
                    $auth->updateUser($id, array(
                        'nombre' => $nombre,
                        'email' => $email,
                        'rol' => $rol,
                        'activo' => $activo
                    ));
                    $message = 'Usuario actualizado.';
                    $action = 'list';
                }
            }
        }
    }
}

$usuario = null;
if ($action === 'edit' && $id > 0) {
    $usuarios = $auth->getUsers();
    foreach ($usuarios as $u) {
        if ($u['id'] == $id) {
            $usuario = $u;
            break;
        }
    }
    if (!$usuario) {
        $action = 'list';
        $error = 'Usuario no encontrado.';
    }
}

$usuarios = null;
if ($action === 'list') {
    $usuarios = $auth->getUsers();
    usort($usuarios, function($a, $b) {
        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
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
    <title>Admin - Usuarios</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none;">&#9776;</button>
                <h1>Usuarios</h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">+ Nuevo</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($usuarios)): ?>
                            <p class="empty">No hay usuarios.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><span class="tag tag-<?php echo $u['rol'] === 'admin' ? 'publicado' : 'evento'; ?>"><?php echo ucfirst($u['rol']); ?></span></td>
                                    <td><span class="tag tag-<?php echo $u['activo'] ? 'publicado' : 'borrador'; ?>"><?php echo $u['activo'] ? 'Activo' : 'Inactivo'; ?></span></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <form class="delete-form" method="POST" action="?action=delete" onsubmit="return confirm('&iquest;Eliminar este usuario?')">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">X</button>
                                            </form>
                                        <?php endif; ?>
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
                                    <input type="text" id="nombre" name="nombre" required value="<?php echo $usuario ? htmlspecialchars($usuario['nombre']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" required value="<?php echo $usuario ? htmlspecialchars($usuario['email']) : ''; ?>">
                                </div>
                            </div>
                            <?php if ($action === 'new'): ?>
                            <div class="form-group">
                                <label for="password">Contrase&ntilde;a *</label>
                                <input type="password" id="password" name="password" required minlength="8">
                            </div>
                            <?php endif; ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="rol">Rol</label>
                                    <select id="rol" name="rol">
                                        <option value="editor" <?php echo ($usuario && $usuario['rol'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                                        <option value="admin" <?php echo ($usuario && $usuario['rol'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" name="activo" value="1" <?php echo (!$usuario || $usuario['activo']) ? 'checked' : ''; ?>> Activo</label>
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
