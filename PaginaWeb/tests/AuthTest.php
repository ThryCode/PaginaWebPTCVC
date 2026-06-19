<?php
require_once __DIR__ . '/../public/api/auth.php';

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $auth;
    private $dataFile;
    private $tmpDir;
    private $testUserId;
    private $testPassword = 'testPass123';

    protected function setUp(): void
    {
        $this->tmpDir = __DIR__ . '/tmp_data';
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }

        $this->dataFile = $this->tmpDir . '/admin_auth.json';
        $this->cleanData();

        $this->auth = new Auth();
        $this->auth->setDataFile($this->dataFile);
        $userId = $this->auth->createUser('Test User', 'test@test.cu', 'admin', $this->testPassword);
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
        $resolved = realpath($this->dataFile);
        $prefix = realpath($this->tmpDir);
        if ($resolved === false || $prefix === false) {
            return;
        }
        if (strpos($resolved, $prefix) !== 0) {
            return;
        }
        if (file_exists($resolved)) {
            unlink($resolved);
        }
        $dir = dirname($resolved);
        if (is_dir($dir) && strpos($dir, $prefix) === 0) {
            $files = glob($dir . '/*');
            foreach ($files as $f) {
                @unlink($f);
            }
            rmdir($dir);
        }
    }

    private function fullLogin()
    {
        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $this->assertTrue($this->auth->loginWithCredentials('test@test.cu', $this->testPassword));
        return $pac;
    }

    public function testGeneratePAC()
    {
        $pac = $this->auth->generatePAC($this->testUserId, 'test-pac');
        $this->assertEquals(10, strlen($pac));
        $this->assertRegExp('/^[A-Za-z0-9]+$/', $pac);
    }

    public function testLoginWithValidSystemPAC()
    {
        $pac = $this->auth->generateSystemPAC();
        $result = $this->auth->loginWithPAC($pac);
        $this->assertTrue($result);
    }

    public function testLoginFailsWithWrongPAC()
    {
        $result = $this->auth->loginWithPAC('WrongPAC123');
        $this->assertFalse($result);
    }

    public function testLoginFailsWithShortPAC()
    {
        $result = $this->auth->loginWithPAC('short');
        $this->assertFalse($result);
    }

    public function testLoginFailsForInactiveUser()
    {
        $this->auth->updateUser($this->testUserId, ['activo' => false]);
        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $result = $this->auth->loginWithCredentials('test@test.cu', $this->testPassword);
        $this->assertFalse($result);
    }

    public function testIsLoggedInAfterFullLogin()
    {
        $this->fullLogin();
        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testGetUserReturnsData()
    {
        $this->fullLogin();
        $user = $this->auth->getUser();
        $this->assertEquals('test@test.cu', $user['email']);
        $this->assertEquals('Test User', $user['nombre']);
        $this->assertEquals('admin', $user['rol']);
    }

    public function testIsAdmin()
    {
        $this->fullLogin();
        $this->assertTrue($this->auth->isAdmin());
    }

    public function testLogoutClearsSession()
    {
        $this->fullLogin();
        $this->assertTrue($this->auth->isLoggedIn());
        $this->auth->logout();
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testLoginWithCredentialsValid()
    {
        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $result = $this->auth->loginWithCredentials('test@test.cu', $this->testPassword);
        $this->assertTrue($result);
        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testLoginWithCredentialsWrongPassword()
    {
        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $result = $this->auth->loginWithCredentials('test@test.cu', 'wrongPassword');
        $this->assertFalse($result);
    }

    public function testLoginWithCredentialsByIdentifier()
    {
        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $result = $this->auth->loginWithCredentials('Test User', $this->testPassword);
        $this->assertTrue($result);
        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testGenerateSystemPAC()
    {
        $pac = $this->auth->generateSystemPAC();
        $this->assertEquals(10, strlen($pac));
        $this->assertRegExp('/^[A-Za-z0-9]+$/', $pac);
    }

    public function testSetSystemPAC()
    {
        $customPac = 'MyCustomPAC123';
        $result = $this->auth->setSystemPAC($customPac);
        $this->assertTrue($result);
        $this->assertTrue($this->auth->loginWithPAC($customPac));
    }

    public function testSetSystemPACTooShort()
    {
        $result = $this->auth->setSystemPAC('short');
        $this->assertFalse($result);
    }

    public function testGetSystemPACInfo()
    {
        $this->auth->generateSystemPAC();
        $info = $this->auth->getSystemPACInfo();
        $this->assertTrue($info['exists']);
        $this->assertNotNull($info['created_at']);
    }

    public function testSystemPACInfoWhenNotSet()
    {
        $info = $this->auth->getSystemPACInfo();
        $this->assertFalse($info['exists']);
    }

    public function testChangePassword()
    {
        $newPassword = 'newPassword456';
        $result = $this->auth->changePassword($this->testUserId, $newPassword);
        $this->assertTrue($result);

        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $this->assertTrue($this->auth->loginWithCredentials('test@test.cu', $newPassword));
    }

    public function testChangePasswordTooShort()
    {
        $result = $this->auth->changePassword($this->testUserId, 'short');
        $this->assertFalse($result);
    }

    public function testVerifyCurrentPassword()
    {
        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $this->assertTrue($this->auth->verifyCurrentPassword($this->testUserId, $this->testPassword));
    }

    public function testVerifyCurrentPasswordWrong()
    {
        $this->assertFalse($this->auth->verifyCurrentPassword($this->testUserId, 'wrongPassword'));
    }

    public function testRegeneratePerUserPAC()
    {
        $oldPac = $this->auth->generatePAC($this->testUserId, 'old-test');
        $this->assertEquals(10, strlen($oldPac));

        $newPac = $this->auth->regeneratePAC($this->testUserId, 'new-test');
        $this->assertEquals(10, strlen($newPac));
        $this->assertNotEquals($oldPac, $newPac);
    }

    public function testRevokePerUserPAC()
    {
        $this->auth->generatePAC($this->testUserId, 'revoke-test');
        $pacs = $this->auth->listPACs($this->testUserId);
        $this->assertCount(1, $pacs);
        $this->assertTrue($pacs[0]['activo']);

        $this->auth->revokePAC($pacs[0]['id']);
        $pacs2 = $this->auth->listPACs($this->testUserId);
        $this->assertFalse($pacs2[0]['activo']);
    }

    public function testGetUsers()
    {
        $users = $this->auth->getUsers();
        $this->assertCount(1, $users);
        $this->assertEquals('Test User', $users[0]['nombre']);
    }

    public function testCreateUserWithPassword()
    {
        $id = $this->auth->createUser('Editor', 'editor@test.cu', 'editor', 'editorPass789');
        $user = $this->auth->getUserById($id);
        $this->assertEquals('Editor', $user['nombre']);
        $this->assertEquals('editor', $user['rol']);
    }

    public function testDeleteUser()
    {
        $id = $this->auth->createUser('ToDelete', 'delete@test.cu', 'editor', 'deletePass');
        $this->assertCount(2, $this->auth->getUsers());
        $this->auth->deleteUser($id);
        $this->assertCount(1, $this->auth->getUsers());
    }

    public function testClearAuditLog()
    {
        $this->fullLogin();
        $this->auth->logout();
        $audit = $this->auth->getAuditLog();
        $this->assertGreaterThan(0, count($audit));

        $this->auth->clearAuditLog();
        $audit2 = $this->auth->getAuditLog();
        $this->assertCount(0, $audit2);
    }

    public function testLoginWithCredentialsWrongIdentifier()
    {
        $pac = $this->auth->generateSystemPAC();
        $this->assertTrue($this->auth->loginWithPAC($pac));
        $result = $this->auth->loginWithCredentials('nonexistent@test.cu', $this->testPassword);
        $this->assertFalse($result);
    }
}
