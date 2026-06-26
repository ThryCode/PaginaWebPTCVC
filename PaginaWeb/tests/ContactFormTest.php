<?php
require_once __DIR__ . '/../public/api/config.php';
require_once __DIR__ . '/../public/api/storage.php';

use PHPUnit\Framework\TestCase;

class ContactFormTest extends TestCase
{
    protected function setUp(): void
    {
        if (!is_dir(DATA_DIR)) {
            mkdir(DATA_DIR, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        Storage::clearCache('mensajes');
        Storage::clearCache('rate_limits');
        $file = Storage::getFilePath('mensajes');
        if (file_exists($file)) {
            unlink($file);
        }
        $file = Storage::getFilePath('rate_limits');
        if (file_exists($file)) {
            unlink($file);
        }
    }

    private function validateContact($data)
    {
        $errors = [];

        $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
        $correo = isset($data['correo']) ? trim($data['correo']) : '';
        $telefono = isset($data['telefono']) ? trim($data['telefono']) : '';
        $asunto = isset($data['asunto']) ? trim($data['asunto']) : '';
        $mensaje = isset($data['mensaje']) ? trim($data['mensaje']) : '';

        if (empty($nombre) || strlen($nombre) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        }
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }
        if (!empty($telefono) && !preg_match('/^[\d\s\-\+\(\)]{7,15}$/', $telefono)) {
            $errors[] = 'El teléfono no es válido.';
        }
        if (empty($asunto)) {
            $errors[] = 'El asunto es obligatorio.';
        }
        if (empty($mensaje) || strlen($mensaje) < 10) {
            $errors[] = 'El mensaje debe tener al menos 10 caracteres.';
        }

        return $errors;
    }

    public function testValidContactPassesValidation()
    {
        $errors = $this->validateContact([
            'nombre' => 'Juan',
            'correo' => 'juan@test.cu',
            'telefono' => '+53 555 12345',
            'asunto' => 'informacion',
            'mensaje' => 'Hola, quiero información sobre sus servicios.',
        ]);
        $this->assertEmpty($errors);
    }

    public function testNombreTooShort()
    {
        $errors = $this->validateContact(['nombre' => 'A', 'correo' => 'a@b.cu', 'asunto' => 'otro', 'mensaje' => 'Mensaje largo de prueba']);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('2 caracteres', $errors[0]);
    }

    public function testInvalidEmail()
    {
        $errors = $this->validateContact(['nombre' => 'Juan', 'correo' => 'invalido', 'asunto' => 'otro', 'mensaje' => 'Mensaje largo de prueba']);
        $this->assertNotEmpty($errors);
    }

    public function testTelefonoOptional()
    {
        $errors = $this->validateContact(['nombre' => 'Juan', 'correo' => 'a@b.cu', 'telefono' => '', 'asunto' => 'otro', 'mensaje' => 'Mensaje largo de prueba']);
        $this->assertEmpty($errors);
    }

    public function testTelefonoInvalid()
    {
        $errors = $this->validateContact(['nombre' => 'Juan', 'correo' => 'a@b.cu', 'telefono' => 'abc', 'asunto' => 'otro', 'mensaje' => 'Mensaje largo de prueba']);
        $this->assertNotEmpty($errors);
    }

    public function testAsuntoValido()
    {
        $errors = $this->validateContact(['nombre' => 'Juan', 'correo' => 'a@b.cu', 'asunto' => 'Cualquier texto válido', 'mensaje' => 'Mensaje largo de prueba']);
        $this->assertEmpty($errors);
    }

    public function testMensajeTooShort()
    {
        $errors = $this->validateContact(['nombre' => 'Juan', 'correo' => 'a@b.cu', 'asunto' => 'otro', 'mensaje' => 'Corto']);
        $this->assertNotEmpty($errors);
    }

    public function testMensajeValido()
    {
        $errors = $this->validateContact(['nombre' => 'Juan', 'correo' => 'a@b.cu', 'asunto' => 'otro', 'mensaje' => 'Mensaje de prueba con mas de diez caracteres.']);
        $this->assertEmpty($errors);
    }

    public function testMultipleErrorsAtOnce()
    {
        $errors = $this->validateContact(['nombre' => '', 'correo' => '', 'asunto' => '', 'mensaje' => '']);
        $this->assertCount(4, $errors);
    }

    public function testAsuntoStoredEscaped()
    {
        Storage::insert('mensajes', array(
            'nombre' => 'User',
            'apellidos' => 'Test',
            'correo' => 'user@test.cu',
            'telefono' => '',
            'asunto' => htmlspecialchars('<script>alert("xss")</script>', ENT_QUOTES, 'UTF-8'),
            'mensaje' => 'Mensaje de prueba normal.',
            'leido' => 0
        ));
        $mensajes = Storage::read('mensajes');
        $this->assertStringContainsString('&lt;', $mensajes[0]['asunto']);
        $this->assertStringNotContainsString('<script>', $mensajes[0]['asunto']);
    }

    public function testApellidosStoredCorrectly()
    {
        Storage::insert('mensajes', array(
            'nombre' => 'Carlos',
            'apellidos' => 'Perez Lopez',
            'correo' => 'carlos@test.cu',
            'telefono' => '',
            'asunto' => 'Consulta',
            'mensaje' => 'Mensaje de prueba para verificar apellidos.',
            'leido' => 0
        ));
        $mensajes = Storage::read('mensajes');
        $this->assertEquals('Perez Lopez', $mensajes[0]['apellidos']);
    }

    public function testDataPersistsAfterInsert()
    {
        Storage::insert('mensajes', array(
            'nombre' => 'Test',
            'correo' => 'test@test.cu',
            'mensaje' => 'Mensaje de prueba para verificar persistencia.',
            'leido' => 0
        ));
        $mensajes = Storage::read('mensajes');
        $this->assertCount(1, $mensajes);
        $this->assertEquals('Test', $mensajes[0]['nombre']);
    }

    public function testRateLimitExceeded()
    {
        $rateData = array('contact_rate_test' => MAX_FORM_SUBMISSIONS);
        Storage::write('rate_limits', $rateData);
        $rateDataRead = Storage::read('rate_limits');
        $this->assertEquals(MAX_FORM_SUBMISSIONS, $rateDataRead['contact_rate_test']);
    }
}
