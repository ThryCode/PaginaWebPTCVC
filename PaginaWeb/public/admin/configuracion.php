<?php
require_once '../api/auth.php';
require_once '../api/storage.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();
$isAdmin = $auth->isAdmin();

$message = '';
$error = '';
$newPacCode = '';

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

        if (isset($_POST['generate_system_pac']) && $isAdmin) {
            $newPacCode = $auth->generateSystemPAC();
            $message = 'Nuevo PAC generado.';
        }

        if (isset($_POST['set_system_pac']) && $isAdmin) {
            $customPac = trim($_POST['custom_pac'] ?? '');
            if (empty($customPac)) {
                $error = 'Debe ingresar un PAC.';
            } elseif (strlen($customPac) < 8) {
                $error = 'El PAC debe tener al menos 8 caracteres.';
            } else {
                $auth->setSystemPAC($customPac);
                $newPacCode = $customPac;
                $message = 'PAC personalizado establecido.';
            }
        }

        if (isset($_POST['clear_audit']) && $isAdmin) {
            $auth->clearAuditLog();
            $message = 'Historial de entradas limpiado.';
        }

        if (isset($_POST['change_password'])) {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (strlen($newPass) < 8) {
                $error = 'La nueva contrase&ntilde;a debe tener al menos 8 caracteres.';
            } elseif ($newPass !== $confirmPass) {
                $error = 'Las contrase&ntilde;as nuevas no coinciden.';
            } elseif (!$auth->verifyCurrentPassword($user['id'], $currentPass)) {
                $error = 'La contrase&ntilde;a actual es incorrecta.';
            } else {
                if ($auth->changePassword($user['id'], $newPass)) {
                    $message = 'Contrase&ntilde;a actualizada correctamente.';
                } else {
                    $error = 'Error al actualizar la contrase&ntilde;a.';
                }
            }
        }
    }
}

$config = Storage::read('config');
$pacInfo = $auth->getSystemPACInfo();
$auditLog = $isAdmin ? $auth->getAuditLog() : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=w<=768;document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Configuracion</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
</head>
<body>
    <div class="admin-wrapper">

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" aria-label="Menu">&#9776;</button>
                <h1>Configuracion</h1>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if (!empty($newPacCode)): ?>
                <div class="pac-result-box" id="pacResultBox">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div>
                            <span style="font-size:0.85rem; color:#666;">Tu nuevo PAC es:</span>
                            <div class="pac-code" id="pacCodeText"><?php echo htmlspecialchars($newPacCode); ?></div>
                        </div>
                        <button type="button" class="btn btn-primary" id="btnCopyPAC">Copiar PAC</button>
                    </div>
                    <small style="color:#888; display:block; margin-top:8px;">Guardalo ahora. No se mostrara de nuevo.</small>
                </div>
                <?php endif; ?>

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
                                <input type="email" inputmode="email" name="contact_email" value="<?php echo htmlspecialchars($config['contact_email'] ?? ''); ?>">
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

                <?php if ($isAdmin): ?>
                <div class="form-card" style="margin-bottom:30px;">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Clave de Acceso (PAC)</h2>
                    <p style="color:#666; font-size:0.9rem; margin-bottom:20px;">
                        La PAC es una clave de 10 caracteres que se ingresa en la pagina de login tras hacer 5 clicks en "404".
                        Es la misma para todos los usuarios (admin y editor).
                    </p>

                    <div style="background:#f0f4f8; border-radius:10px; padding:16px; margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                            <div>
                                <strong style="color:#2c3e50;">Estado actual:</strong>
                                <?php if ($pacInfo['exists']): ?>
                                    <span style="color:#16a34a; font-weight:600;">Activo</span>
                                    <?php if ($pacInfo['created_at']): ?>
                                        <span style="color:#888; font-size:0.85rem; margin-left:8px;">Creado: <?php echo $pacInfo['created_at']; ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#dc2626; font-weight:600;">No configurado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:24px;">
                        <form method="POST" style="display:inline;" data-confirm="Generar nuevo PAC aleatorio? El anterior dejara de funcionar.">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="generate_system_pac" value="1">
                            <button type="submit" class="btn btn-primary">Generar PAC aleatorio</button>
                        </form>
                        <form method="POST" style="display:inline; flex:1; min-width:250px; display:flex; gap:8px;" data-confirm="Establecer este PAC personalizado? El anterior dejara de funcionar.">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="set_system_pac" value="1">
                            <input type="text" name="custom_pac" placeholder="PAC personalizado (min. 8 chars)" minlength="8" style="flex:1; padding:8px 12px; border:1px solid #d2d2d7; border-radius:8px; font-size:0.9rem;">
                            <button type="submit" class="btn btn-secondary">Establecer</button>
                        </form>
                    </div>

                    <hr style="border:none; border-top:1px solid #e5e7eb; margin:20px 0;">

                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div>
                            <h3 style="font-size:1rem; color:#2c3e50; margin-bottom:4px;">Historial de entradas</h3>
                            <p style="color:#888; font-size:0.85rem; margin:0;">Registro de intentos de login y verificaciones PAC.</p>
                        </div>
                        <form method="POST" style="display:inline;" data-confirm="Limpiar todo el historial de entradas? Esta accion no se puede deshacer.">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="clear_audit" value="1">
                            <button type="submit" class="btn btn-danger">Limpiar historial</button>
                        </form>
                    </div>

                    <?php if (!empty($auditLog)): ?>
                    <div style="margin-top:16px; max-height:400px; overflow:auto;">
                        <table class="table" style="width:100%; font-size:0.85rem; border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:2px solid #ddd; position:sticky; top:0; background:#f8f9fa;">
                                    <th style="text-align:left; padding:8px 10px; color:#666;">Usuario</th>
                                    <th style="text-align:left; padding:8px 10px; color:#666;">Accion</th>
                                    <th style="text-align:left; padding:8px 10px; color:#666;">Fecha y Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auditLog as $entry): ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:8px 10px; font-weight:500;" data-label="Usuario"><?php echo htmlspecialchars($entry['details']); ?></td>
                                    <td style="padding:8px 10px;" data-label="Acción">
                                        <?php if ($entry['action'] === 'login'): ?>
                                            <span style="color:#16a34a; font-weight:600;">Entro</span>
                                        <?php else: ?>
                                            <span style="color:#888; font-weight:600;">Salio</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:8px 10px; color:#555;" data-label="Fecha y Hora"><?php echo htmlspecialchars($entry['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p style="color:#999; font-size:0.85rem; margin-top:16px;">No hay entradas registradas.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="form-card" style="margin-bottom:30px;">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Cambiar Contrase&ntilde;a</h2>
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <div class="form-group">
                            <label>Contrase&ntilde;a Actual</label>
                            <input type="password" name="current_password" required autocomplete="current-password">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nueva Contrase&ntilde;a</label>
                                <input type="password" name="new_password" minlength="8" required autocomplete="new-password">
                            </div>
                            <div class="form-group">
                                <label>Confirmar Contrase&ntilde;a</label>
                                <input type="password" name="confirm_password" minlength="8" required autocomplete="new-password">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="change_password" value="1" class="btn btn-success">Actualizar Contrase&ntilde;a</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <style>
        .pac-result-box {
            background: #f0fdf4; border: 2px solid #16a34a; border-radius: 12px;
            padding: 20px; margin-bottom: 24px; animation: fadeIn 0.3s ease;
        }
        .pac-code {
            font-family: 'SF Mono', 'Courier New', monospace; font-size: 1.6rem;
            font-weight: 700; color: #16a34a; letter-spacing: 3px; margin-top: 6px;
            background: #fff; padding: 10px 16px; border-radius: 8px;
            display: inline-block; border: 1px solid #bbf7d0;
        }
        .btn-danger { background: #dc2626; color: #fff; border: none; border-radius: 8px; cursor: pointer; padding: 8px 16px; font-size: 0.9rem; }
        .btn-danger:hover { background: #b91c1c; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    </style>

</body>
</html>
