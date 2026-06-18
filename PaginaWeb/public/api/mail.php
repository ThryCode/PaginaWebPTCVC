<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$GLOBALS['_mail_errors'] = array();
$GLOBALS['_mail_log'] = array();

function _mailLog($msg) {
    $GLOBALS['_mail_log'][] = $msg;
    error_log('[MAIL] ' . $msg);
}

function sendMail($to, $subject, $body) {
    _mailLog("Iniciando envio a $to");
    _mailLog("Subject: $subject");

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        _mailLog('PHPMailer NO disponible, usando mail() nativo');
        $GLOBALS['_mail_errors'][] = 'PHPMailer no esta instalado o no se cargo el autoloader';
        $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $result = @mail($to, $encodedSubject, $body, $headers);
        _mailLog('mail() nativo resultado: ' . ($result ? 'OK' : 'FALLO'));
        if (!$result) {
            $GLOBALS['_mail_errors'][] = 'mail() nativo devolvio false (posiblemente bloqueado por el hosting)';
        }
        return $result;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        if (isset($_GET['smtp_debug'])) {
            $mail->SMTPDebug = max(1, min(4, intval($_GET['smtp_debug'])));
            $mail->Debugoutput = function($str) use ($to) {
                _mailLog("DEBUG SMTP [$to]: " . trim($str));
            };
        }

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;

        _mailLog("Enviando via SMTP...");

        if (!$mail->send()) {
            _mailLog("PHPMailer send() devolvio false");
            $GLOBALS['_mail_errors'][] = "PHPMailer send() fallo sin excepcion: " . $mail->ErrorInfo;
            return false;
        }

        _mailLog("ENVIADO correctamente");
        return true;

    } catch (Exception $e) {
        $msg = $e->getMessage();
        _mailLog("PHPMailer EXCEPCION: $msg");
        $GLOBALS['_mail_errors'][] = "SMTP Error: $msg";

        _mailLog("Fallback a mail() nativo...");
        $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $mailResult = @mail($to, $encodedSubject, $body, $headers);
        _mailLog('mail() nativo resultado: ' . ($mailResult ? 'OK' : 'FALLO'));
        return $mailResult;
    }
}

function sendTokenToAllUsers($token) {
    _mailLog("=== sendTokenToAllUsers INICIADO ===");

    $users = Storage::read('usuarios');
    $total = is_array($users) ? count($users) : 0;
    _mailLog("Storage::read('usuarios') devolvio $total usuarios");

    if ($total === 0) {
        $dataDir = defined('DATA_DIR') ? DATA_DIR : 'NO DEFINIDO';
        $archivo = rtrim(DATA_DIR, '/\\') . '/usuarios.json';
        _mailLog("DATA_DIR = $dataDir");
        _mailLog("Archivo esperado: $archivo");
        _mailLog("Existe: " . (file_exists($archivo) ? 'SI' : 'NO'));
        _mailLog("Legible: " . (is_readable($archivo) ? 'SI' : 'NO'));
        $GLOBALS['_mail_errors'][] = "No se encontraron usuarios en $archivo";
        return 0;
    }

    $emails = array();
    foreach ($users as $u) {
        $emails[] = $u['email'] ?? 'SIN_EMAIL';
    }
    _mailLog("Emails: " . implode(', ', $emails));

    $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url = $scheme . '://' . $host . '/admin/login.php?token=' . $token;

    $body = "Se ha solicitado el token de acceso al panel de administracion.\n\n";
    $body .= "Token: " . $token . "\n\n";
    $body .= "URL de acceso: " . $url . "\n\n";
    $body .= "Este token es personal e intransferible.\n";

    $sent = 0;
    foreach ($users as $user) {
        if (!empty($user['email'])) {
            _mailLog(">> Enviando a: " . $user['email']);
            if (sendMail($user['email'], 'Token de acceso al panel admin', $body)) {
                $sent++;
                _mailLog(">> ENVIADO a: " . $user['email']);
            } else {
                _mailLog(">> FALLO a: " . $user['email']);
            }
        } else {
            _mailLog("Usuario sin email: " . json_encode($user));
        }
    }

    _mailLog("=== RESULTADO: $sent de $total enviados ===");
    return $sent;
}

function getMailErrors() {
    return $GLOBALS['_mail_errors'];
}

function getMailLog() {
    return $GLOBALS['_mail_log'];
}
