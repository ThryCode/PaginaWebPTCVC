<?php

use PHPMailer\PHPMailer\PHPMailer;

function sendMail($to, $subject, $body) {
    $log = function($msg) use ($to) { error_log('[sendMail][' . $to . '] ' . $msg); };

    if (!class_exists('PHPMailer')) {
        $log('PHPMailer no disponible - usando mail() nativo');
        $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return mail($to, $encodedSubject, $body, $headers);
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        if (isset($_GET['smtp_debug'])) {
            $mail->SMTPDebug = max(0, min(4, intval($_GET['smtp_debug'])));
            if ($mail->SMTPDebug > 0) {
                $mail->Debugoutput = function($str) use ($log) { $log(trim($str)); };
            }
        }

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $result = $mail->send();
        $log('Enviado correctamente');
        return $result;
    } catch (Exception $e) {
        $log('PHPMailer fallo: ' . $e->getMessage());
        $log('Usando fallback mail() nativo');
        $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $mailResult = mail($to, $encodedSubject, $body, $headers);
        $log('mail() nativo resulto: ' . ($mailResult ? 'true' : 'false'));
        return $mailResult;
    }
}

function sendTokenToAllUsers($token) {
    $log = function($msg) { error_log('[sendTokenToAllUsers] ' . $msg); };

    $users = Storage::read('usuarios');
    $total = is_array($users) ? count($users) : 0;
    $log('Storage::read("usuarios") devolvio ' . $total . ' usuarios');

    if ($total === 0) {
        $dataDir = defined('DATA_DIR') ? DATA_DIR : 'NO DEFINIDO';
        $log('DATA_DIR = ' . $dataDir);
        $archivo = rtrim(DATA_DIR, '/\\') . '/usuarios.json';
        $log('Archivo esperado: ' . $archivo);
        $log('Existe: ' . (file_exists($archivo) ? 'SI' : 'NO'));
        $log('Legible: ' . (is_readable($archivo) ? 'SI' : 'NO'));
        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            $log('Tamano del archivo: ' . strlen($contenido) . ' bytes');
            $log('Primeros 200 chars: ' . substr($contenido, 0, 200));
        }
        return 0;
    }

    $emails = array();
    foreach ($users as $u) {
        $emails[] = $u['email'] ?? 'SIN_EMAIL';
    }
    $log('Emails encontrados: ' . implode(', ', $emails));

    $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url = $scheme . '://' . $host . '/admin/login.php?token=' . $token;

    $body = "Se ha solicitado el token de acceso al panel de administración.\n\n";
    $body .= "Token: " . $token . "\n\n";
    $body .= "URL de acceso: " . $url . "\n\n";
    $body .= "Este token es personal e intransferible.\n";

    $sent = 0;
    foreach ($users as $user) {
        if (!empty($user['email'])) {
            $log('Intentando enviar a: ' . $user['email']);
            if (sendMail($user['email'], 'Token de acceso al panel admin', $body)) {
                $sent++;
                $log('ENVIADO a: ' . $user['email']);
            } else {
                $log('FALLO al enviar a: ' . $user['email']);
            }
        } else {
            $log('Usuario sin email: ' . json_encode($user));
        }
    }

    $log('RESUMEN: Enviado a ' . $sent . ' de ' . $total . ' usuarios');
    return $sent;
}
