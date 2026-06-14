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
        $users = Storage::findWhere('usuarios', array('email' => $email));
        if (empty($users)) {
            return false;
        }

        $user = $users[0];
        if (!isset($user['activo']) || !$user['activo']) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nombre'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_rol'] = $user['rol'];
        $_SESSION['logged_in'] = true;

        return true;
    }

    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
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
