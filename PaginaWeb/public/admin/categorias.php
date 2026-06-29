<?php
require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        $deleteId = intval($_POST['id'] ?? 0);
        if ($deleteId > 0) {
            Storage::delete('categorias', $deleteId);
            $message = 'Categoría eliminada.';
        }
    }
}

$categorias = Storage::read('categorias');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=w<=768;document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Categorías</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
</head>
<body>
    <div class="admin-wrapper">

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu">☰</button>
                <h1>Categorías</h1>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <div class="panel"><div class="panel-body">
                    <?php if (empty($categorias)): ?>
                        <p class="empty">No hay categorías.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead><tr><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr></thead>
                            <tbody>
                            <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td data-label="Nombre"><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                <td data-label="Descripción"><?php echo htmlspecialchars($cat['descripcion'] ?: '—'); ?></td>
                                <td data-label="Acciones">
                                    <form class="delete-form" method="POST" data-confirm="¿Eliminar esta categoría?">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div></div>
            </div>
        </main>
        <?php include 'includes/sidebar.php'; ?>
    </div>
</body>
</html>
