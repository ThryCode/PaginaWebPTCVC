<?php
require_once '../api/auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = false;
$errorMsg = '';
$timeoutMsg = isset($_GET['timeout']);
$step = isset($_SESSION['pac_verified']) && $_SESSION['pac_verified'] === true ? 2 : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pac']) && $step === 1) {
        $result = $auth->loginWithPAC($_POST['pac']);
        if ($result === true) {
            $_SESSION['pac_verified'] = true;
            $step = 2;
        } else {
            $error = true;
            $errorMsg = 'Clave incorrecta';
        }
    } elseif (isset($_POST['identifier']) && $step === 2) {
        $result = $auth->loginWithCredentials($_POST['identifier'], $_POST['password']);
        if ($result === true) {
            unset($_SESSION['pac_verified']);
            header('Location: index.php');
            exit;
        }
        $error = true;
        $errorMsg = 'Correo/usuario o contrase&ntilde;a incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&aacute;gina no encontrada</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo/favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f7;
            color: #1d1d1f;
        }
        .container { text-align: center; padding: 40px 20px; max-width: 480px; width: 100%; }
        .code { font-size: 80px; font-weight: 700; color: #86868b; letter-spacing: -4px; margin-bottom: 8px; user-select: none; }
        .title { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
        .desc { font-size: 14px; color: #86868b; line-height: 1.5; margin-bottom: 32px; }

        .pac-wrap { display: none; margin-top: 8px; }
        .pac-wrap.visible { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .login-wrap { display: none; margin-top: 8px; }
        .login-wrap.visible { display: block; animation: fadeIn 0.4s ease; }

        .pac-input {
            width: 100%; padding: 14px 16px; font-size: 18px;
            font-family: 'SF Mono', 'Courier New', monospace;
            letter-spacing: 2px; text-align: center;
            border: 2px solid #d2d2d7; border-radius: 12px;
            background: #fff; color: #1d1d1f;
            outline: none; transition: border-color 0.2s;
        }
        .pac-input:focus { border-color: #0071e3; }

        .login-field {
            width: 100%; padding: 14px 16px; font-size: 15px;
            border: 2px solid #d2d2d7; border-radius: 12px;
            background: #fff; color: #1d1d1f;
            outline: none; transition: border-color 0.2s;
            text-align: left; font-family: inherit;
            margin-bottom: 12px;
        }
        .login-field:focus { border-color: #0071e3; }

        .login-label {
            display: block; text-align: left;
            font-size: 13px; font-weight: 600; color: #6e6e73;
            margin-bottom: 6px;
        }

        .pac-submit, .login-submit {
            width: 100%; padding: 14px; margin-top: 6px;
            background: #0071e3; color: #fff;
            border: none; border-radius: 12px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.2s;
        }
        .pac-submit:hover, .login-submit:hover { background: #0066cc; }
        .pac-submit:active, .login-submit:active { background: #0055b3; }

        .error-box {
            display: none; margin-top: 12px;
            padding: 10px 14px; background: #fff2f0;
            border: 1px solid #ffccc7; border-radius: 8px;
            color: #cf1322; font-size: 13px;
        }
        .error-box.visible { display: block; }

        .timeout-box {
            display: none; margin-bottom: 24px;
            padding: 16px; background: #fffbe6;
            border: 1px solid #ffe58f; border-radius: 12px;
            color: #ad6800; text-align: center;
        }
        .timeout-box.visible { display: block; animation: fadeIn 0.3s ease; }

        .step-indicator {
            display: flex; justify-content: center; gap: 8px; margin-bottom: 24px;
        }
        .step-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #d2d2d7; transition: background 0.3s;
        }
        .step-dot.active { background: #0071e3; }
        .step-dot.done { background: #34c759; }

        .login-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($timeoutMsg): ?>
        <div class="timeout-box visible">
            <div style="font-size:40px; margin-bottom:12px;">&#9200;</div>
            <div style="font-weight:600; margin-bottom:6px;">Sesi&oacute;n expirada</div>
            <div style="color:#86868b; font-size:14px;">Tu sesi&oacute;n expir&oacute; por inactividad. Inicia sesi&oacute;n nuevamente.</div>
        </div>
        <?php endif; ?>
        <?php if ($step === 1): ?>
        <div class="code" id="secretTrigger">404</div>
        <div class="title">P&aacute;gina no encontrada</div>
        <div class="desc">La p&aacute;gina que buscas no existe o ha sido movida.<br>Verifica la direcci&oacute;n e intenta nuevamente.</div>
        <div class="pac-wrap" id="pacWrap">
            <div class="step-indicator">
                <div class="step-dot active"></div>
                <div class="step-dot"></div>
            </div>
            <form method="POST" autocomplete="off">
                <input type="text" name="pac" class="pac-input" id="pacInput"
                       placeholder="Clave de acceso" maxlength="20" autocomplete="off">
                <button type="submit" class="pac-submit">Verificar Clave</button>
            </form>
            <div class="error-box <?php if ($error) echo 'visible'; ?>" id="pacError">
                <?php echo $errorMsg; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($step === 2): ?>
        <div class="code" style="color: #34c759; font-size: 60px;">&#10003;</div>
        <div class="login-wrap visible" id="loginWrap">
            <div class="step-indicator">
                <div class="step-dot done"></div>
                <div class="step-dot active"></div>
            </div>
            <div class="login-title">Iniciar Sesi&oacute;n</div>
            <form method="POST" autocomplete="off">
                <label class="login-label" for="identifier">Correo o Usuario</label>
                <input type="text" name="identifier" class="login-field" id="identifier"
                       placeholder="admin@pctvc.cu" autocomplete="username" required>
                <label class="login-label" for="password">Contrase&ntilde;a</label>
                <input type="password" name="password" class="login-field" id="password"
                       placeholder="Tu contrase&ntilde;a" autocomplete="current-password" required>
                <button type="submit" class="login-submit">Iniciar Sesi&oacute;n</button>
            </form>
            <div class="error-box <?php if ($error) echo 'visible'; ?>" id="loginError">
                <?php echo $errorMsg; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        var trigger = document.getElementById('secretTrigger');
        var wrap = document.getElementById('pacWrap');
        var input = document.getElementById('pacInput');

        if (!trigger || !wrap) return;

        var clicks = 0;
        var clickTimer = null;
        var startTouch = null;
        var holdTimer = null;
        var revealed = false;

        function reveal() {
            if (revealed) return;
            revealed = true;
            wrap.classList.add('visible');
            setTimeout(function() { input.focus(); }, 100);
        }

        trigger.addEventListener('click', function(e) {
            if (revealed) return;
            clicks++;
            if (clicks >= 5) {
                reveal();
                return;
            }
            clearTimeout(clickTimer);
            clickTimer = setTimeout(function() {
                clicks = 0;
            }, 3000);
        });

        trigger.addEventListener('touchstart', function(e) {
            if (revealed) return;
            startTouch = Date.now();
            holdTimer = setTimeout(function() {
                reveal();
            }, 5000);
        }, { passive: true });

        trigger.addEventListener('touchend', function(e) {
            if (revealed) return;
            clearTimeout(holdTimer);
            startTouch = null;
        }, { passive: true });

        trigger.addEventListener('touchmove', function(e) {
            clearTimeout(holdTimer);
        }, { passive: true });

        if (input) {
            input.addEventListener('input', function() {
                var err = document.getElementById('pacError');
                if (err) err.classList.remove('visible');
            });
        }

        var loginInput = document.getElementById('identifier');
        if (loginInput) {
            loginInput.focus();
        }
    })();
    </script>
</body>
</html>
