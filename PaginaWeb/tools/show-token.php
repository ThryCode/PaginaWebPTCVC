<?php
$tokenFile = __DIR__ . '/../public/data/admin_token.json';
if (!file_exists($tokenFile)) {
    echo "No hay token generado.\n";
    exit(1);
}
$data = json_decode(file_get_contents($tokenFile), true);
echo "Token de acceso: " . $data['token'] . "\n";
echo "Creado: " . ($data['created_at'] ?? 'desconocida') . "\n";
