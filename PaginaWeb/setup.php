<?php
/**
 * Setup: Crea la carpeta data/ y los archivos JSON iniciales
 * Ejecutar una sola vez
 */

$dataDir = __DIR__ . '/public/data';

if (is_dir($dataDir)) {
    echo "Ya existe la carpeta data/. Setup completo.<br>";
    echo '<a href="index.php">Ir al sitio</a>';
    exit;
}

mkdir($dataDir, 0755, true);

// Usuario admin (password: admin123)
$usuarios = array(
    array(
        'id' => 1,
        'nombre' => 'Administrador',
        'email' => 'admin@pctvc.cu',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'rol' => 'admin',
        'activo' => 1,
        'created_at' => date('Y-m-d H:i')
    )
);
file_put_contents($dataDir . '/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));

// Categorías
$categorias = array(
    array('id' => 1, 'nombre' => 'Noticias', 'descripcion' => 'Últimas novedades de la empresa', 'created_at' => date('Y-m-d H:i')),
    array('id' => 2, 'nombre' => 'Eventos', 'descripcion' => 'Próximos eventos y actividades', 'created_at' => date('Y-m-d H:i')),
    array('id' => 3, 'nombre' => 'Anuncios', 'descripcion' => 'Comunicados oficiales', 'created_at' => date('Y-m-d H:i'))
);
file_put_contents($dataDir . '/categorias.json', json_encode($categorias, JSON_PRETTY_PRINT));

// Noticias vacías
file_put_contents($dataDir . '/noticias.json', json_encode(array(), JSON_PRETTY_PRINT));

// Mensajes vacíos
file_put_contents($dataDir . '/mensajes.json', json_encode(array(), JSON_PRETTY_PRINT));

// Configuración
$config = array(
    'site_name' => 'PCT Villa Clara',
    'site_description' => 'Parque Cientifico Tecnologico de Villa Clara - Centro de innovacion y desarrollo tecnologico',
    'contact_email' => 'info@pctvc.cu',
    'contact_phone' => '+53 (555) 123-4567',
    'contact_address' => 'Av. Principal 123, Col. Centro, Villa Clara, Cuba'
);
file_put_contents($dataDir . '/config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Rate limits vacío
file_put_contents($dataDir . '/rate_limits.json', json_encode(array(), JSON_PRETTY_PRINT));

echo "<h1>Setup completado</h1>";
echo "<p>Carpeta <strong>data/</strong> creada con los archivos iniciales.</p>";
echo "<ul>";
echo "<li>Usuario: admin@pctvc.cu</li>";
echo "<li>Contraseña: admin123</li>";
echo "</ul>";
echo '<p>Accede al sitio desde <strong>public/index.php</strong> y al admin desde <strong>public/admin/login.php</strong>.</p>';
