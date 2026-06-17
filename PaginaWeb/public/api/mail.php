<?php
function sendMail($to, $subject, $body) {
    $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . CONTACT_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return mail($to, $encodedSubject, $body, $headers);
}

function sendTokenToAllUsers($token) {
    $users = Storage::read('usuarios');
    $subject = 'Token de acceso al panel admin';
    $body = "Se ha solicitado el token de acceso al panel de administración.\n\n";
    $body .= "Token: " . $token . "\n\n";
    $body .= "URL de acceso: " . (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https') . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/admin/login.php?token=" . $token . "\n\n";
    $body .= "Este token es personal e intransferible.\n";
    $sent = 0;
    foreach ($users as $user) {
        if (!empty($user['email'])) {
            if (sendMail($user['email'], $subject, $body)) {
                $sent++;
            }
        }
    }
    return $sent;
}
