<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->SMTPDebug = SMTP_DEBUG;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('[sendMail] Error enviando a ' . $to . ': ' . $e->getMessage());
        return false;
    }
}

function sendTokenToAllUsers($token) {
    $users = Storage::read('usuarios');
    $subject = 'Token de acceso al panel admin';
    $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url = $scheme . '://' . $host . '/admin/login.php?token=' . $token;

    $body = "Se ha solicitado el token de acceso al panel de administración.\n\n";
    $body .= "Token: " . $token . "\n\n";
    $body .= "URL de acceso: " . $url . "\n\n";
    $body .= "Este token es personal e intransferible.\n";

    $sent = 0;
    $total = count($users);

    foreach ($users as $user) {
        if (!empty($user['email'])) {
            if (sendMail($user['email'], $subject, $body)) {
                $sent++;
            } else {
                error_log('[sendTokenToAllUsers] Fallo al enviar a: ' . $user['email']);
            }
        }
    }

    error_log('[sendTokenToAllUsers] Enviado a ' . $sent . ' de ' . $total . ' usuarios');
    return $sent;
}
