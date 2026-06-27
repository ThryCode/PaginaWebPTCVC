<?php

if (file_exists(__DIR__ . '/public/data/admin_auth.json')) {
    echo "Setup ya completado. Accede a public/index.php\n";
    exit;
}

require __DIR__ . '/public/setup.php';