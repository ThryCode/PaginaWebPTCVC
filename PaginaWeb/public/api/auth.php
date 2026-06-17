<?php
/**
 * Autenticación con JSON
 */

require_once 'storage.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {

    public function login($email, $password) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateData = Storage::read('rate_limits');
        $rateKey = 'login_' . $ip;
        $attempts = isset($rateData[$rateKey]) ? $rateData[$rateKey] : array('count' => 0, 'time' => 0);

        if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
            $elapsed = time() - $attempts['time'];
            if ($elapsed < LOGIN_LOCKOUT_MINUTES * 60) {
                return 'locked';
            }
            $attempts = array('count' => 0, 'time' => 0);
        }

        $users = Storage::findWhere('usuarios', array('email' => $email));
        if (empty($users)) {
            $attempts['count']++;
            $attempts['time'] = time();
            $rateData[$rateKey] = $attempts;
            Storage::write('rate_limits', $rateData);
            return false;
        }

        $user = $users[0];
        if (!isset($user['activo']) || !$user['activo']) {
            $attempts['count']++;
            $attempts['time'] = time();
            $rateData[$rateKey] = $attempts;
            Storage::write('rate_limits', $rateData);
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            $attempts['count']++;
            $attempts['time'] = time();
            $rateData[$rateKey] = $attempts;
            Storage::write('rate_limits', $rateData);
            return false;
        }

        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nombre'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_rol'] = $user['rol'];
        $_SESSION['logged_in'] = true;

        $rateData[$rateKey] = array('count' => 0, 'time' => 0);
        Storage::write('rate_limits', $rateData);

        return true;
    }

    public function logout() {
        $_SESSION = array();
        session_destroy();
        if (!headers_sent()) {
            header('Location: login.php');
            exit;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return array(
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'],
            'email' => $_SESSION['user_email'],
            'rol' => $_SESSION['user_rol']
        );
    }

    public function isAdmin() {
        return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
