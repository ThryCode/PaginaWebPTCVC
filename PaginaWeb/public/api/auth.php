<?php

class Auth {
    private $dataFile;

    public function __construct() {
        $this->dataFile = defined('DATA_DIR') ? DATA_DIR . '/admin_auth.json' : __DIR__ . '/../data/admin_auth.json';
    }

    public function setDataFile($path) {
        $this->dataFile = $path;
    }

    private function readData() {
        if (!file_exists($this->dataFile)) {
            error_log('[AUTH] dataFile no existe: ' . $this->dataFile);
            return ['next_id' => 1, 'users' => [], 'audit' => []];
        }
        $content = file_get_contents($this->dataFile);
        if ($content === false || $content === '') {
            return ['next_id' => 1, 'users' => [], 'audit' => []];
        }
        $data = json_decode($content, true);
        return is_array($data) ? $data : ['next_id' => 1, 'users' => [], 'audit' => []];
    }

    private function writeData($data) {
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $result = file_put_contents($this->dataFile, $json, LOCK_EX) !== false;
        if (!$result) {
            error_log('[AUTH] Error al escribir: ' . $this->dataFile);
        }
        return $result;
    }

    public function loginWithPAC($pac) {
        $pac = trim($pac);
        if (empty($pac) || strlen($pac) < 8) {
            return false;
        }

        $data = $this->readData();
        $ip = $this->getIP();

        foreach ($data['users'] as &$user) {
            if (!$user['activo']) {
                continue;
            }
            foreach ($user['pacs'] as &$p) {
                if (!$p['activo']) {
                    continue;
                }
                if (password_verify($pac, $p['hash'])) {
                    if (!headers_sent()) {
                        session_regenerate_id(true);
                    }

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nombre'] = $user['nombre'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_rol'] = $user['rol'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();

                    $user['last_login'] = date('Y-m-d H:i:s');
                    $p['last_used'] = date('Y-m-d H:i:s');
                    $this->writeData($data);

                    $this->logAudit($user['id'], 'login', "PAC login: {$user['email']}", $ip);
                    return true;
                }
            }
        }
        unset($user, $p);

        $this->logAudit(null, 'login_failed', "Intento fallido con PAC", $ip);
        return false;
    }

    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $this->getIP();

        $_SESSION = [];
        if (!headers_sent()) {
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();
        }

        if ($userId) {
            $this->logAudit($userId, 'logout', 'Cierre de sesion', $ip);
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
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
        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'],
            'email' => $_SESSION['user_email'],
            'rol' => $_SESSION['user_rol'],
        ];
    }

    public function isAdmin() {
        return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
    }

    public function generatePAC($userId, $alias = null) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $pac = '';
        for ($i = 0; $i < 10; $i++) {
            $pac .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $hash = password_hash($pac, PASSWORD_BCRYPT);
        $data = $this->readData();

        $pacId = $data['next_id']++;

        foreach ($data['users'] as &$user) {
            if ($user['id'] == $userId) {
                $user['pacs'][] = [
                    'id' => $pacId,
                    'hash' => $hash,
                    'alias' => $alias,
                    'activo' => true,
                    'last_used' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                break;
            }
        }
        unset($user);

        $this->writeData($data);
        return $pac;
    }

    public function regeneratePAC($userId, $alias = null) {
        $data = $this->readData();

        foreach ($data['users'] as &$user) {
            if ($user['id'] == $userId) {
                foreach ($user['pacs'] as &$p) {
                    $p['activo'] = false;
                }
                unset($p);
                break;
            }
        }
        unset($user);

        $this->writeData($data);
        return $this->generatePAC($userId, $alias);
    }

    public function revokePAC($pacId) {
        $data = $this->readData();

        foreach ($data['users'] as &$user) {
            foreach ($user['pacs'] as &$p) {
                if ($p['id'] == $pacId) {
                    $p['activo'] = false;
                    break 2;
                }
            }
        }
        unset($user, $p);

        $this->writeData($data);
    }

    public function listPACs($userId) {
        $data = $this->readData();

        foreach ($data['users'] as $user) {
            if ($user['id'] == $userId) {
                return $user['pacs'];
            }
        }
        return [];
    }

    public function getUsers() {
        $data = $this->readData();
        $result = [];
        foreach ($data['users'] as $user) {
            $result[] = [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'rol' => $user['rol'],
                'activo' => $user['activo'],
                'last_login' => $user['last_login'] ?? null,
                'created_at' => $user['created_at'],
            ];
        }
        return $result;
    }

    public function getUserById($userId) {
        $data = $this->readData();

        foreach ($data['users'] as $user) {
            if ($user['id'] == $userId) {
                return [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'rol' => $user['rol'],
                    'activo' => $user['activo'],
                ];
            }
        }
        return null;
    }

    public function updateUser($userId, $fields) {
        $data = $this->readData();

        foreach ($data['users'] as &$user) {
            if ($user['id'] == $userId) {
                foreach (['nombre', 'email', 'rol', 'activo'] as $key) {
                    if (array_key_exists($key, $fields)) {
                        $user[$key] = $fields[$key];
                    }
                }
                break;
            }
        }
        unset($user);

        $this->writeData($data);
    }

    public function createUser($nombre, $email, $rol = 'editor') {
        $data = $this->readData();

        $user = [
            'id' => $data['next_id']++,
            'nombre' => $nombre,
            'email' => $email,
            'rol' => $rol,
            'activo' => true,
            'last_login' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'pacs' => [],
        ];
        $data['users'][] = $user;

        $this->writeData($data);
        return $user['id'];
    }

    private function logAudit($userId, $action, $details, $ip) {
        $data = $this->readData();
        $data['audit'][] = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->writeData($data);
    }

    private function getIP() {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
