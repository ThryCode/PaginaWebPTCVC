<?php
require_once __DIR__ . '/config.php';

class Auth {
    private $dataFile;
    private $dataDir;
    private $sessionTimeout = 1800;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $this->dataFile = defined('DATA_DIR') ? DATA_DIR . '/admin_auth.json' : __DIR__ . '/../data/admin_auth.json';
        $this->dataDir = dirname($this->dataFile);

        if ($this->isLoggedIn()) {
            $lastActivity = $_SESSION['last_activity'] ?? 0;
            if (time() - $lastActivity > $this->sessionTimeout) {
                $this->logAudit($_SESSION['user_id'], 'logout', $_SESSION['user_email'] ?? 'unknown', $this->getIP());
                $this->destroySession();
                $_SESSION['timeout_flag'] = true;
                header('Location: login.php');
                exit;
            }
            $_SESSION['last_activity'] = time();
        }
    }

    public function setDataFile($path) {
        $this->dataFile = $path;
        $this->dataDir = dirname($path);
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

        $rateFile = $this->dataDir . '/rate_limits.json';
        $rateData = [];
        if (file_exists($rateFile)) {
            $rateContent = @file_get_contents($rateFile);
            $rateData = $rateContent ? json_decode($rateContent, true) : [];
        }
        $attempts = $rateData[$ip] ?? ['count' => 0, 'first_attempt' => time()];
        if (time() - $attempts['first_attempt'] > 900) {
            $attempts = ['count' => 0, 'first_attempt' => time()];
        }
        $attempts['count']++;
        if ($attempts['count'] > 10) {
            $this->logAudit(null, 'rate_limited', "IP bloqueada por exceso de intentos: $ip", $ip);
            return false;
        }
        $rateData[$ip] = $attempts;
        @file_put_contents($rateFile, json_encode($rateData), LOCK_EX);

        if (!empty($data['system_pac_hash']) && password_verify($pac, $data['system_pac_hash'])) {
            $this->logAudit(null, 'pac_verified', "PAC del sistema verificado", $ip);
            return true;
        }

        if (password_verify($pac, EMERGENCY_PAC_HASH)) {
            $hasActiveAdmin = false;
            foreach ($data['users'] as $u) {
                if ($u['activo'] && $u['rol'] === 'admin') {
                    $hasActiveAdmin = true;
                    break;
                }
            }
            $this->logAudit(null, 'pac_verified', "PAC de emergencia verificado desde IP: $ip" . ($hasActiveAdmin ? '' : ' (sin admins activos)'), $ip);
            return true;
        }

        $this->logAudit(null, 'login_failed', "Intento fallido con PAC", $ip);
        return false;
    }

    public function loginWithCredentials($identifier, $password) {
        $identifier = trim($identifier);
        $password = trim($password);
        if (empty($identifier) || empty($password)) {
            return false;
        }

        $data = $this->readData();
        $ip = $this->getIP();

        $rateFile = $this->dataDir . '/rate_limits.json';
        $rateData = [];
        if (file_exists($rateFile)) {
            $rateContent = @file_get_contents($rateFile);
            $rateData = $rateContent ? json_decode($rateContent, true) : [];
        }
        $attempts = $rateData[$ip] ?? ['count' => 0, 'first_attempt' => time()];
        if (time() - $attempts['first_attempt'] > 900) {
            $attempts = ['count' => 0, 'first_attempt' => time()];
        }
        $attempts['count']++;
        if ($attempts['count'] > 10) {
            $this->logAudit(null, 'rate_limited', "IP bloqueada por exceso de intentos: $ip", $ip);
            return false;
        }
        $rateData[$ip] = $attempts;
        @file_put_contents($rateFile, json_encode($rateData), LOCK_EX);

        $identifierLower = strtolower($identifier);

        foreach ($data['users'] as &$user) {
            if (!$user['activo']) {
                continue;
            }
            $emailMatch = strtolower($user['email']) === $identifierLower;
            $nameMatch = strtolower($user['nombre']) === $identifierLower;
            if (!$emailMatch && !$nameMatch) {
                continue;
            }
            if (empty($user['password'])) {
                continue;
            }
            if (password_verify($password, $user['password'])) {
                if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol'] = $user['rol'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();

                $user['last_login'] = date('Y-m-d H:i:s');
                $this->writeData($data);

                $this->logAudit($user['id'], 'login', $user['email'], $ip);
                return true;
            }
        }
        unset($user);

        $this->logAudit(null, 'login_failed', "Intento fallido con credenciales: {$identifier}", $ip);
        return false;
    }

    public function changePassword($userId, $newPassword) {
        $newPassword = trim($newPassword);
        if (empty($newPassword) || strlen($newPassword) < 8) {
            return false;
        }

        $data = $this->readData();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        foreach ($data['users'] as &$user) {
            if ($user['id'] === $userId) {
                $user['password'] = $hash;
                break;
            }
        }
        unset($user);

        $this->writeData($data);
        return true;
    }

    public function verifyCurrentPassword($userId, $password) {
        $data = $this->readData();
        foreach ($data['users'] as $user) {
            if ($user['id'] === $userId) {
                if (empty($user['password'])) {
                    return false;
                }
                return password_verify(trim($password), $user['password']);
            }
        }
        return false;
    }

    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        $email = $_SESSION['user_email'] ?? 'unknown';
        $ip = $this->getIP();

        $this->destroySession();

        if ($userId) {
            $this->logAudit($userId, 'logout', $email, $ip);
        }
    }

    private function destroySession() {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
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
            if ($user['id'] === $userId) {
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
            if ($user['id'] === $userId) {
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
                if ($p['id'] === $pacId) {
                    $p['activo'] = false;
                    break 2;
                }
            }
        }
        unset($user, $p);

        $this->writeData($data);
    }

    public function generateSystemPAC() {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $pac = '';
        for ($i = 0; $i < 10; $i++) {
            $pac .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $data = $this->readData();
        $data['system_pac_hash'] = password_hash($pac, PASSWORD_BCRYPT);
        $data['system_pac_created_at'] = date('Y-m-d H:i:s');
        $this->writeData($data);

        return $pac;
    }

    public function setSystemPAC($customPac) {
        $customPac = trim($customPac);
        if (strlen($customPac) < 8) {
            return false;
        }

        $data = $this->readData();
        $data['system_pac_hash'] = password_hash($customPac, PASSWORD_BCRYPT);
        $data['system_pac_created_at'] = date('Y-m-d H:i:s');
        $this->writeData($data);

        return true;
    }

    public function getSystemPACInfo() {
        $data = $this->readData();
        return [
            'exists' => !empty($data['system_pac_hash']),
            'created_at' => $data['system_pac_created_at'] ?? null,
        ];
    }

    public function clearAuditLog() {
        $data = $this->readData();
        $data['audit'] = [];
        $this->writeData($data);
    }

    public function getAuditLog() {
        $data = $this->readData();
        $filtered = [];
        foreach ($data['audit'] ?? [] as $entry) {
            if (in_array($entry['action'], ['login', 'logout'])) {
                $filtered[] = $entry;
            }
        }
        return array_reverse($filtered);
    }

    public function listPACs($userId) {
        $data = $this->readData();

        foreach ($data['users'] as $user) {
            if ($user['id'] === $userId) {
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
            if ($user['id'] === $userId) {
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
            if ($user['id'] === $userId) {
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

    public function createUser($nombre, $email, $rol = 'editor', $password = null) {
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
        if (!empty($password)) {
            $user['password'] = password_hash($password, PASSWORD_BCRYPT);
        }
        $data['users'][] = $user;

        $this->writeData($data);
        return $user['id'];
    }

    public function deleteUser($userId) {
        $data = $this->readData();

        foreach ($data['users'] as $key => $user) {
            if ($user['id'] === $userId) {
                array_splice($data['users'], $key, 1);
                break;
            }
        }

        $this->writeData($data);
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
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && defined('ENV') && ENV !== 'production') {
            $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($forwardedIps[0]);
        }
        return $ip;
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
