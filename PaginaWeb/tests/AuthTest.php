<?php
require_once __DIR__ . '/../public/api/auth.php';

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $auth;
    private $dataFile;

    protected function setUp(): void
    {
        if (!defined('DATA_DIR')) {
            define('DATA_DIR', __DIR__ . '/tmp_data');
        }
        if (!is_dir(DATA_DIR)) {
            mkdir(DATA_DIR, 0755, true);
        }

        $this->dataFile = DATA_DIR . '/admin_auth.json';
        $this->cleanData();

        $this->auth = new Auth();
        $userId = $this->auth->createUser('Test User', 'test@test.cu', 'admin');
        $this->testUserId = $userId;
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->cleanData();
    }

    private function cleanData() {
        if (file_exists($this->dataFile)) {
            unlink($this->dataFile);
        }
        $dir = dirname($this->dataFile);
        if (is_dir($dir) && strpos($dir, 'tmp_data') !== false) {
            $files = glob($dir . '/*');
            foreach ($files as $f) {
                unlink($f);
            }
            rmdir($dir);
        }
    }

    public function testGeneratePAC()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'test-pac');
        $this->assertEquals(10, strlen($pac));
        $this->assertRegExp('/^[A-Za-z0-9]+$/', $pac);
    }

    public function testLoginWithValidPAC()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'login-test');
        $result = $this->auth->loginWithPAC($pac);
        $this->assertTrue($result);
        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testLoginFailsWithWrongPAC()
    {
        $this->auth->generatePAC($this->testUserId, 'wrong-test');
        $result = $this->auth->loginWithPAC('WrongPAC123');
        $this->assertFalse($result);
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testLoginFailsWithShortPAC()
    {
        $result = $this->auth->loginWithPAC('short');
        $this->assertFalse($result);
    }

    public function testLoginFailsForInactiveUser()
    {
        $this->auth->updateUser($this->testUserId, ['activo' => false]);
        $pac = $this->auth->generatePAC($this->testUserId, 'inactive-test');
        $result = $this->auth->loginWithPAC($pac);
        $this->assertFalse($result);
    }

    public function testIsLoggedInAfterLogin()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'loggedin-test');
        $this->auth->loginWithPAC($pac);
        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testGetUserReturnsData()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'user-test');
        $this->auth->loginWithPAC($pac);
        $user = $this->auth->getUser();
        $this->assertEquals('test@test.cu', $user['email']);
        $this->assertEquals('Test User', $user['nombre']);
        $this->assertEquals('admin', $user['rol']);
    }

    public function testIsAdmin()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'admin-test');
        $this->auth->loginWithPAC($pac);
        $this->assertTrue($this->auth->isAdmin());
    }

    public function testLogoutClearsSession()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'logout-test');
        $this->auth->loginWithPAC($pac);
        $this->assertTrue($this->auth->isLoggedIn());
        $this->auth->logout();
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testRegeneratePAC()
    {
        $oldPac = $this->auth->generatePAC($this->testUserId, 'old-test');
        $this->assertTrue($this->auth->loginWithPAC($oldPac));
        $this->auth->logout();

        $newPac = $this->auth->regeneratePAC($this->testUserId, 'new-test');
        $this->assertEquals(10, strlen($newPac));
        $this->assertNotEquals($oldPac, $newPac);

        $this->assertFalse($this->auth->loginWithPAC($oldPac));
        $this->assertTrue($this->auth->loginWithPAC($newPac));
    }

    public function testRevokePAC()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'revoke-test');
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $this->auth->logout();

        $pacs = $this->auth->listPACs($this->testUserId);
        foreach ($pacs as $p) {
            if ($p['activo']) {
                $this->auth->revokePAC($p['id']);
                break;
            }
        }

        $this->assertFalse($this->auth->loginWithPAC($pac));
    }

    public function testGetUsers()
    {
        $users = $this->auth->getUsers();
        $this->assertCount(1, $users);
        $this->assertEquals('Test User', $users[0]['nombre']);
    }

    public function testCreateUser()
    {
        $id = $this->auth->createUser('Editor', 'editor@test.cu', 'editor');
        $user = $this->auth->getUserById($id);
        $this->assertEquals('Editor', $user['nombre']);
        $this->assertEquals('editor', $user['rol']);
    }
}
