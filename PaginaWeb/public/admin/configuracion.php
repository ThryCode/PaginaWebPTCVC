<?php
require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();
$isAdmin = $auth->isAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad invalido.';
    } else {
        if (isset($_POST['site_name'])) {
            $campos = array('site_name', 'site_description', 'contact_email', 'contact_phone', 'contact_address');
            $config = Storage::read('config');
            foreach ($campos as $campo) {
                if (isset($_POST[$campo])) {
                    $config[$campo] = trim($_POST[$campo]);
                }
            }
            Storage::write('config', $config);
            $message = 'Configuracion actualizada.';
        }

        if (isset($_POST['generate_pac']) && $isAdmin) {
            $targetUserId = isset($_POST['user_id']) ? intval($_POST['user_id']) : $user['id'];
            $alias = isset($_POST['pac_alias']) ? trim($_POST['pac_alias']) : null;
            $newPAC = $auth->generatePAC($targetUserId, $alias);
            $targetUser = $auth->getUserById($targetUserId);
            $message = "Nuevo PAC generado para {$targetUser['nombre']}: <strong>" . htmlspecialchars($newPAC) . "</strong>";
            $message .= '<br><small style="color:#888;">Copialo ahora, no se mostrara de nuevo.</small>';
        }

        if (isset($_POST['regenerate_pac']) && $isAdmin) {
            $targetUserId = isset($_POST['user_id']) ? intval($_POST['user_id']) : $user['id'];
            $alias = isset($_POST['pac_alias']) ? trim($_POST['pac_alias']) : null;
            $newPAC = $auth->regeneratePAC($targetUserId, $alias);
            $targetUser = $auth->getUserById($targetUserId);
            $message = "PAC regenerado para {$targetUser['nombre']}: <strong>" . htmlspecialchars($newPAC) . "</strong>";
            $message .= '<br><small style="color:#888;">El PAC anterior ya no funciona. Copialo ahora.</small>';
        }

        if (isset($_POST['revoke_pac']) && $isAdmin) {
            $pacId = intval($_POST['pac_id']);
            $auth->revokePAC($pacId);
            $message = 'PAC desactivado.';
        }
    }
}

$config = Storage::read('config');
$csrfToken = generateCSRFToken();

$users = $isAdmin ? $auth->getUsers() : [$auth->getUserById($user['id'])];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Configuracion</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none;">☰</button>
                <h1>Configuracion</h1>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <div class="form-card" style="margin-bottom:30px;">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Informacion del Sitio</h2>
                    <form method="POST">
                        <?php echo csrfField(); ?>
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
                            <label>Descripcion</label>
                            <textarea name="site_description" rows="3"><?php echo htmlspecialchars($config['site_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Telefono</label>
                                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($config['contact_phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Direccion</label>
                                <input type="text" name="contact_address" value="<?php echo htmlspecialchars($config['contact_address'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </form>
                </div>

                <div class="form-card" style="margin-bottom:30px;">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Claves de Acceso (PAC)</h2>
                    <p style="color:#666; font-size:0.9rem; margin-bottom:20px;">
                        Cada administrador/editor tiene una Clave Personal de Acceso (PAC) de 10 caracteres.
                        Se ingresa en la pagina de login tras la secuencia de 5 clicks.
                    </p>

                    <?php foreach ($users as $u): ?>
                        <div style="background:#f8f9fa; border-radius:12px; padding:16px; margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                                <div>
                                    <strong style="font-size:1rem; color:#2c3e50;"><?php echo htmlspecialchars($u['nombre']); ?></strong>
                                    <span style="color:#666; font-size:0.85rem; margin-left:8px;"><?php echo htmlspecialchars($u['email']); ?></span>
                                    <span class="badge <?php echo $u['rol'] === 'admin' ? 'badge-admin' : 'badge-editor'; ?>" style="margin-left:8px; padding:2px 8px; border-radius:4px; font-size:0.75rem; background:<?php echo $u['rol']==='admin'?'#003c6e':'#008674';?>; color:#fff;">
                                        <?php echo $u['rol']; ?>
                                    </span>
                                </div>
                                <span style="font-size:0.8rem; color:#999;">
                                    <?php if ($u['last_login']): ?>Ultimo acceso: <?php echo $u['last_login']; ?><?php else: ?>Nunca accedio<?php endif; ?>
                                </span>
                            </div>

                            <?php if ($isAdmin): ?>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <form method="POST" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="generate_pac" value="1">
                                        <input type="hidden" name="pac_alias" value="">
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Generar nuevo PAC para <?php echo htmlspecialchars(addslashes($u['nombre'])); ?>?')">Generar PAC</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="regenerate_pac" value="1">
                                        <input type="hidden" name="pac_alias" value="">
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Regenerar PAC para <?php echo htmlspecialchars(addslashes($u['nombre'])); ?>? El anterior dejara de funcionar.')">Regenerar PAC</button>
                                    </form>
                                </div>

                                <?php
                                $pacs = $auth->listPACs($u['id']);
                                if (!empty($pacs)):
                                ?>
                                <div style="margin-top:12px;">
                                    <table style="width:100%; font-size:0.8rem; border-collapse:collapse;">
                                        <thead>
                                            <tr style="border-bottom:1px solid #ddd;">
                                                <th style="text-align:left; padding:4px 8px; color:#666;">Alias</th>
                                                <th style="text-align:left; padding:4px 8px; color:#666;">Estado</th>
                                                <th style="text-align:left; padding:4px 8px; color:#666;">Ultimo uso</th>
                                                <th style="text-align:left; padding:4px 8px; color:#666;">Creado</th>
                                                <th style="text-align:left; padding:4px 8px; color:#666;">Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pacs as $pac): ?>
                                            <tr style="border-bottom:1px solid #eee;">
                                                <td style="padding:4px 8px;"><?php echo htmlspecialchars($pac['alias'] ?? '-'); ?></td>
                                                <td style="padding:4px 8px;">
                                                    <?php if ($pac['activo']): ?>
                                                        <span style="color:#16a34a;">Activo</span>
                                                    <?php else: ?>
                                                        <span style="color:#dc2626;">Revocado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding:4px 8px; color:#666;"><?php echo $pac['last_used'] ?? 'Nunca'; ?></td>
                                                <td style="padding:4px 8px; color:#666;"><?php echo $pac['created_at']; ?></td>
                                                <td style="padding:4px 8px;">
                                                    <?php if ($pac['activo']): ?>
                                                        <form method="POST" style="display:inline;">
                                                            <?php echo csrfField(); ?>
                                                            <input type="hidden" name="pac_id" value="<?php echo $pac['id']; ?>">
                                                            <input type="hidden" name="revoke_pac" value="1">
                                                            <button type="submit" class="btn btn-sm btn-danger" style="font-size:0.75rem; padding:2px 8px;" onclick="return confirm('Revocar este PAC?')">Revocar</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <style>
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        .btn-danger { background: #dc2626; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        .btn-danger:hover { background: #b91c1c; }
    </style>
</body>
</html>
