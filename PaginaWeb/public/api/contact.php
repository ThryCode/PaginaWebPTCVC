<?php

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once 'storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Método no permitido'));
    exit;
}

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Solicitud no válida'));
    exit;
}

session_start();

$token = isset($_POST[CSRF_TOKEN_NAME]) ? $_POST[CSRF_TOKEN_NAME] : '';
if (!empty($token) && !validateCSRFToken($token)) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Token de seguridad inválido.'));
    exit;
}

$sessionId = session_id();

$rateKey = 'contact_rate_' . $sessionId;
$rateData = Storage::read('rate_limits');
$submissions = isset($rateData[$rateKey]) ? $rateData[$rateKey] : 0;

if ($submissions >= MAX_FORM_SUBMISSIONS) {
    echo json_encode(array('success' => false, 'message' => 'Ha excedido el límite de envíos. Intente más tarde.'));
    exit;
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$asunto = isset($_POST['asunto']) ? trim($_POST['asunto']) : '';
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

$errors = array();

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

if (!empty($errors)) {
    echo json_encode(array('success' => false, 'message' => implode(' ', $errors)));
    exit;
}

Storage::insert('mensajes', array(
    'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
    'apellidos' => htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8'),
    'correo' => htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'),
    'telefono' => htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8'),
    'asunto' => htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8'),
    'mensaje' => htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'),
    'leido' => 0
));

$rateData[$rateKey] = $submissions + 1;
Storage::write('rate_limits', $rateData);

echo json_encode(array(
    'success' => true,
    'message' => 'Mensaje enviado correctamente. Nos pondremos en contacto pronto.'
));
