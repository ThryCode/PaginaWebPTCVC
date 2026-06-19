<?php
require_once '../api/auth.php';
require_once '../api/storage.php';
require_once '../api/mail.php';

$auth = new Auth();
$auth->requireLogin();

$message = '';
$error = '';
$pwMessage = '';
$pwError = '';
$tokenMessage = '';
$tokenError = '';

$tokenFile = __DIR__ . '/../data/admin_token.json';
$tokenData = file_exists($tokenFile) ? json_decode(file_get_contents($tokenFile), true) : array('token' => '', 'created_at' => '');
$currentToken = $tokenData['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
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
            $message = 'Configuración actualizada.';
        }

        if (isset($_POST['new_password'])) {
            $newPass = $_POST['new_password'];
            $confirmPass = $_POST['confirm_password'];
            if (strlen($newPass) < 6) {
                $pwError = 'Mínimo 6 caracteres.';
            } elseif ($newPass !== $confirmPass) {
                $pwError = 'Las contraseñas no coinciden.';
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

        if (isset($_POST['regenerar_token'])) {
            $newToken = bin2hex(random_bytes(32));
            $tokenData = array('token' => $newToken, 'created_at' => date('Y-m-d H:i:s'));
            file_put_contents($tokenFile, json_encode($tokenData));
            $currentToken = $newToken;

            if (isset($_POST['token_action']) && $_POST['token_action'] === 'send_mail') {
                $sent = sendTokenToAllUsers($newToken);
                $tokenMessage = 'Token regenerado y enviado a ' . $sent . ' usuario(s) por correo.';
            } elseif (isset($_POST['token_action']) && $_POST['token_action'] === 'download') {
                header('Location: download_token.php');
                exit;
            }
        }

        if (isset($_POST['reenviar_token'])) {
            $sent = sendTokenToAllUsers($currentToken);
            $tokenMessage = 'Token enviado a ' . $sent . ' usuario(s) por correo.';
        }
    }
}

$config = Storage::read('config');
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=1, user-scalable=no';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Configuración</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu" style="display:none;">☰</button>
                <h1>Configuración</h1>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <div class="form-card" style="margin-bottom:30px;">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Información del Sitio</h2>
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
                        <div class="alert alert-success"><?php echo $pwMessage; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($pwError)): ?>
                        <div class="alert alert-error"><?php echo $pwError; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <?php echo csrfField(); ?>
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

                <div class="form-card">
                    <h2 style="margin-bottom:20px; color:#2c3e50;">Token de Acceso</h2>
                    <?php if (!empty($tokenMessage)): ?>
                        <div class="alert alert-success"><?php echo $tokenMessage; ?></div>
                    <?php endif; ?>
                    <?php $mailErrors = getMailErrors(); if (!empty($mailErrors)): ?>
                        <div class="alert alert-error" style="text-align:left;">
                            <strong>Detalles del env&iacute;o:</strong>
                            <?php foreach ($mailErrors as $err): ?>
                                <div style="margin-top:4px;">&bull; <?php echo htmlspecialchars($err); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($tokenError)): ?>
                        <div class="alert alert-error"><?php echo $tokenError; ?></div>
                    <?php endif; ?>
                    <div class="token-display" style="background:#f4f6f8;border-radius:10px;padding:16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <div>
                            <span style="color:#666;font-size:0.85rem;display:block;margin-bottom:4px;">Token actual</span>
                            <code id="tokenValue" style="font-size:0.9rem;color:#003c6e;background:#fff;padding:6px 12px;border-radius:6px;border:1px solid #ddd;display:inline-block;">
                                ••••••••••••••••••••••••••••••••••••••••••••••••••••••
                            </code>
                            <span id="tokenCreated" style="color:#999;font-size:0.8rem;margin-left:8px;">(<?php echo $tokenData['created_at'] ?? ''; ?>)</span>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleToken()">Mostrar</button>
                            <button type="button" class="btn btn-sm btn-warning" onclick="openRegenerateModal()">Regenerar</button>
                            <form method="POST" style="display:inline;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="reenviar_token" value="1">
                                <button type="submit" class="btn btn-sm btn-primary">Enviar a todos</button>
                            </form>
                        </div>
                    </div>
                    <p style="color:#888;font-size:0.85rem;">Al regenerar el token, el anterior deja de funcionar.</p>
                </div>
            </div>
        </main>
    </div>

    <div id="regenerateModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:fadeIn 0.3s ease;">
            <h3 style="margin-bottom:16px;color:#2c3e50;">Regenerar Token</h3>
            <p style="color:#666;margin-bottom:24px;font-size:0.9rem;">¿Qué deseas hacer con el nuevo token?</p>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <form method="POST" action="configuracion.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="regenerar_token" value="1">
                    <input type="hidden" name="token_action" value="download">
                    <button type="submit" class="btn btn-block btn-primary">Descargar .txt</button>
                </form>
                <form method="POST" action="configuracion.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="regenerar_token" value="1">
                    <input type="hidden" name="token_action" value="send_mail">
                    <button type="submit" class="btn btn-block btn-success">Enviar a todos los usuarios</button>
                </form>
            </div>
            <button type="button" class="btn btn-block btn-secondary" onclick="closeRegenerateModal()" style="margin-top:12px;">Cancelar</button>
        </div>
    </div>

    <script>
        var tokenRevealed = false;
        var realToken = '<?php echo $currentToken; ?>';

        function toggleToken() {
            var el = document.getElementById('tokenValue');
            if (!tokenRevealed) {
                el.textContent = realToken;
                tokenRevealed = true;
            } else {
                el.textContent = '••••••••••••••••••••••••••••••••••••••••••••••••••••••';
                tokenRevealed = false;
            }
        }

        function openRegenerateModal() {
            document.getElementById('regenerateModal').style.display = 'flex';
        }

        function closeRegenerateModal() {
            document.getElementById('regenerateModal').style.display = 'none';
        }

        document.getElementById('regenerateModal').addEventListener('click', function(e) {
            if (e.target === this) closeRegenerateModal();
        });
    </script>

    <style>
        #regenerateModal .btn-block { width: 100%; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</body>
</html>
