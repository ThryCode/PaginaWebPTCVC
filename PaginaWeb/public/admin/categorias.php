<?php
/**
 * CRUD: Categorías
 */
require_once '../../api/auth.php';
require_once '../../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (!empty($nombre)) {
        $data = array('nombre' => $nombre, 'descripcion' => $descripcion);
        if ($action === 'edit' && $id > 0) {
            Storage::update('categorias', $id, $data);
            $message = 'Categoría actualizada.';
        } else {
            Storage::insert('categorias', $data);
            $message = 'Categoría creada.';
        }
        $action = 'list';
    }
}

if ($action === 'delete' && $id > 0) {
    Storage::delete('categorias', $id);
    $message = 'Categoría eliminada.';
    $action = 'list';
}

$categoria = null;
if ($action === 'edit' && $id > 0) {
    $categoria = Storage::findById('categorias', $id);
}

$categorias = Storage::read('categorias');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Categorías</title>
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
                    <li><a href="categorias.php" class="active">Categorías</a></li>
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
                <h1>Categorías</h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">+ Nueva</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="panel"><div class="panel-body">
                        <?php if (empty($categorias)): ?>
                            <p class="empty">No hay categorías.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr></thead>
                                <tbody>
                                <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($cat['descripcion'] ?: '—'); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <a href="?action=delete&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">X</a>
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
                            <div class="form-group">
                                <label for="nombre">Nombre *</label>
                                <input type="text" id="nombre" name="nombre" required value="<?php echo $categoria ? htmlspecialchars($categoria['nombre']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3"><?php echo $categoria ? htmlspecialchars($categoria['descripcion']) : ''; ?></textarea>
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
