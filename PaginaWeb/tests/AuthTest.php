<?php
require_once __DIR__ . '/../public/api/auth.php';

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $auth;
    private $testUser;

    protected function setUp(): void
    {
        $this->auth = new Auth();
        Storage::insert('usuarios', [
            'nombre' => 'Test',
            'email' => 'test@test.cu',
            'password' => password_hash('pass123', PASSWORD_BCRYPT),
            'rol' => 'admin',
            'activo' => 1
        ]);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $file = Storage::getFilePath('usuarios');
        if (file_exists($file)) {
            $users = Storage::read('usuarios');
            foreach ($users as $u) {
                if ($u['email'] === 'test@test.cu') {
                    Storage::delete('usuarios', $u['id']);
                }
            }
        }
        $file = Storage::getFilePath('rate_limits');
        if (file_exists($file)) {
            $data = Storage::read('rate_limits');
            foreach ($data as $key => $val) {
                if (strpos($key, 'login_') === 0) {
                    unset($data[$key]);
                }
            }
            Storage::write('rate_limits', $data);
        }
    }

    public function testLoginSuccess()
    {
        $this->assertTrue($this->auth->login('test@test.cu', 'pass123'));
    }

    public function testLoginFailsWithWrongPassword()
    {
        $this->assertFalse($this->auth->login('test@test.cu', 'wrong'));
    }

    public function testLoginFailsWithUnknownEmail()
    {
        $this->assertFalse($this->auth->login('noexiste@test.cu', 'pass123'));
    }

    public function testLoginFailsForInactiveUser()
    {
        Storage::insert('usuarios', [
            'nombre' => 'Inactive',
            'email' => 'inactive@test.cu',
            'password' => password_hash('pass123', PASSWORD_BCRYPT),
            'rol' => 'editor',
            'activo' => 0
        ]);
        $this->assertFalse($this->auth->login('inactive@test.cu', 'pass123'));
        $users = Storage::read('usuarios');
        foreach ($users as $u) {
            if ($u['email'] === 'inactive@test.cu') {
                Storage::delete('usuarios', $u['id']);
            }
        }
    }

    public function testLoginReturnsLockedAfterMaxAttempts()
    {
        for ($i = 0; $i < MAX_LOGIN_ATTEMPTS; $i++) {
            $this->auth->login('test@test.cu', 'wrong');
        }
        $result = $this->auth->login('test@test.cu', 'pass123');
        $this->assertEquals('locked', $result);
    }

    public function testIsLoggedInAfterLogin()
    {
        $this->auth->login('test@test.cu', 'pass123');
        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testGetUserReturnsData()
    {
        $this->auth->login('test@test.cu', 'pass123');
        $user = $this->auth->getUser();
        $this->assertEquals('test@test.cu', $user['email']);
        $this->assertEquals('Test', $user['nombre']);
    }

    public function testIsAdmin()
    {
        $this->auth->login('test@test.cu', 'pass123');
        $this->assertTrue($this->auth->isAdmin());
    }

    public function testLogoutClearsSession()
    {
        $this->auth->login('test@test.cu', 'pass123');
        $this->assertTrue($this->auth->isLoggedIn());
        $this->auth->logout();
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testHashPassword()
    {
        $hash = $this->auth->hashPassword('mypassword');
        $this->assertTrue(password_verify('mypassword', $hash));
    }
}
