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

    public function testHashPassword()
    {
        $hash = $this->auth->hashPassword('mypassword');
        $this->assertTrue(password_verify('mypassword', $hash));
    }
}
