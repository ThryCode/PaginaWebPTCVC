<?php
require_once '../api/auth.php';

$auth = new Auth();
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($email) || empty($password)) {
            $error = 'Por favor complete todos los campos.';
        } else {
            if ($auth->login($email, $password)) {
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Iniciar Sesión</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Lato', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #003c6e 0%, #008674 100%);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: bgShift 20s linear infinite;
        }

        @keyframes bgShift {
            0% { transform: translate(0, 0); }
            100% { transform: translate(40px, 40px); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: cardIn 0.6s ease-out;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .login-logo img {
            height: 60px;
        }

        .login-logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #003c6e, #008674);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .login-logo-icon svg {
            width: 32px;
            height: 32px;
            fill: #fff;
        }

        .login-card h1 {
            text-align: center;
            color: #1a1a2e;
            font-size: 1.6rem;
            font-weight: 900;
            margin-bottom: 6px;
        }

        .login-card .subtitle {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            stroke: #999;
            pointer-events: none;
            transition: stroke 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            color: #333;
            background: #fafafa;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #003c6e;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0, 60, 110, 0.1);
        }

        .form-group input:focus + svg,
        .form-group input:focus ~ svg {
            stroke: #003c6e;
        }

        .input-wrap:focus-within svg {
            stroke: #003c6e;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #003c6e, #005a9e);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #008674, #00a88e);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 134, 116, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
            border: 1px solid #fecaca;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #fff;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px;
            }
            .login-card h1 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="login-logo-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/></svg>
                </div>
            </div>
            <h1>Panel Admin</h1>
            <p class="subtitle">Parque Científico Tecnológico de Villa Clara</p>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrfToken; ?>">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" required placeholder="admin@pctvc.cu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                </div>
                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>
        </div>
        <div class="back-link">
            <a href="../index.php">&larr; Volver al sitio</a>
        </div>
    </div>
</body>
</html>
