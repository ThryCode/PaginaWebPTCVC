<?php
/**
 * Setup administrador inicial.
 * Crea public/data/admin_auth.json con usuario admin y un PAC.
 * Run: php tools/setup-admin.php
 */

$dataDir = __DIR__ . '/../public/data';
$authFile = $dataDir . '/admin_auth.json';

echo "=== Setup Admin PAC ===\n\n";

function generatePAC($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $pac = '';
    for ($i = 0; $i < $length; $i++) {
        $pac .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pac;
}

$pac = generatePAC();
$hash = password_hash($pac, PASSWORD_BCRYPT);

$data = [
    'next_id' => 2,
    'users' => [
        [
            'id' => 1,
            'nombre' => 'Administrador',
            'email' => 'admin@pctvc.cu',
            'rol' => 'admin',
            'activo' => true,
            'last_login' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'pacs' => [
                [
                    'id' => 1,
                    'hash' => $hash,
                    'alias' => 'Inicial',
                    'activo' => true,
                    'last_used' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ],
        ],
    ],
    'audit' => [],
];

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$result = file_put_contents($authFile, $json, LOCK_EX);

if ($result === false) {
    echo "ERROR: No se pudo escribir $authFile\n";
    exit(1);
}

echo "  Archivo: $authFile\n";
echo "  Email:   admin@pctvc.cu\n";
echo "  PAC:     $pac\n\n";
echo "  Guarda este PAC, solo se muestra una vez.\n";
echo "  Para acceder: /admin/ (5 clicks en '404')\n";
echo "========================================\n";
