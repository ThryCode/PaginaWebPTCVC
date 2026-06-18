<?php
require_once '../api/auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pac'])) {
    $result = $auth->loginWithPAC($_POST['pac']);
    if ($result === true) {
        header('Location: index.php');
        exit;
    }
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada</title>
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
        .container { text-align: center; padding: 40px 20px; max-width: 480px; }
        .code { font-size: 80px; font-weight: 700; color: #86868b; letter-spacing: -4px; margin-bottom: 8px; user-select: none; }
        .title { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
        .desc { font-size: 14px; color: #86868b; line-height: 1.5; margin-bottom: 32px; }
        .pac-wrap { display: none; margin-top: 8px; }
        .pac-wrap.visible { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .pac-input {
            width: 100%; padding: 14px 16px; font-size: 18px;
            font-family: 'SF Mono', 'Courier New', monospace;
            letter-spacing: 2px; text-align: center;
            border: 2px solid #d2d2d7; border-radius: 12px;
            background: #fff; color: #1d1d1f;
            outline: none; transition: border-color 0.2s;
        }
        .pac-input:focus { border-color: #0071e3; }
        .pac-submit {
            width: 100%; padding: 14px; margin-top: 10px;
            background: #0071e3; color: #fff;
            border: none; border-radius: 12px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.2s;
        }
        .pac-submit:hover { background: #0066cc; }
        .pac-submit:active { background: #0055b3; }
        .pac-error {
            display: none; margin-top: 10px;
            padding: 10px 14px; background: #fff2f0;
            border: 1px solid #ffccc7; border-radius: 8px;
            color: #cf1322; font-size: 13px;
        }
        .pac-error.visible { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code" id="secretTrigger">404</div>
        <div class="title">Página no encontrada</div>
        <div class="desc">La página que buscas no existe o ha sido movida.<br>Verifica la dirección e intenta nuevamente.</div>
        <div class="pac-wrap" id="pacWrap">
            <form method="POST" autocomplete="off">
                <input type="text" name="pac" class="pac-input" id="pacInput"
                       placeholder="Clave de acceso" maxlength="20" autocomplete="off">
                <button type="submit" class="pac-submit">Acceder</button>
            </form>
            <div class="pac-error <?php if ($error) echo 'visible'; ?>" id="pacError">
                Clave incorrecta
            </div>
        </div>
    </div>

    <script>
    (function() {
        var trigger = document.getElementById('secretTrigger');
        var wrap = document.getElementById('pacWrap');
        var input = document.getElementById('pacInput');
        var error = document.getElementById('pacError');

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

        input.addEventListener('input', function() {
            error.classList.remove('visible');
        });
    })();
    </script>
</body>
</html>
