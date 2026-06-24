<?php
/**
 * Setup: Crea los archivos JSON iniciales en DATA_DIR.
 * Ejecutar una sola vez despues de subir a InfinityFree.
 */
require_once __DIR__ . '/api/config.php';

if (file_exists(DATA_DIR . '/admin_auth.json')) {
    echo "Ya existe la carpeta data/. Setup completo.<br>";
    echo '<a href="index.php">Ir al sitio</a> | <a href="admin/login.php">Ir al admin</a>';
    exit;
}

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Admin user: marioc@pctvc.cu / 12345678
$adminAuth = [
    'next_id' => 2,
    'system_pac_hash' => password_hash('admin123', PASSWORD_BCRYPT),
    'system_pac_created_at' => date('Y-m-d H:i:s'),
    'users' => [
        [
            'id' => 1,
            'nombre' => 'marioc@pctvc.cu',
            'email' => 'marioc@pctvc.cu',
            'rol' => 'admin',
            'activo' => true,
            'password' => password_hash('12345678', PASSWORD_BCRYPT),
            'last_login' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'pacs' => []
        ]
    ],
    'audit' => []
];
file_put_contents(DATA_DIR . '/admin_auth.json', json_encode($adminAuth, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Empty data files
$empty = json_encode([], JSON_PRETTY_PRINT);
file_put_contents(DATA_DIR . '/noticias.json', $empty);
file_put_contents(DATA_DIR . '/mensajes.json', $empty);
file_put_contents(DATA_DIR . '/flyers.json', $empty);
file_put_contents(DATA_DIR . '/galeria.json', $empty);
file_put_contents(DATA_DIR . '/proyectos.json', $empty);
file_put_contents(DATA_DIR . '/opiniones.json', $empty);
file_put_contents(DATA_DIR . '/rate_limits.json', $empty);

// Config
$config = [
    'site_name' => 'PCT Villa Clara',
    'site_description' => 'Parque Cientifico Tecnologico de Villa Clara - Centro de innovacion y desarrollo tecnologico',
    'contact_email' => 'info@pctvc.cu',
    'contact_phone' => '+53 (555) 123-4567',
    'contact_address' => 'Av. Principal 123, Col. Centro, Villa Clara, Cuba'
];
file_put_contents(DATA_DIR . '/config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Counters (stats)
$counters = [
    ['id' => 1, 'icono' => 'briefcase', 'valor' => 15, 'sufijo' => '+', 'texto' => 'Proyectos en ejecucion'],
    ['id' => 2, 'icono' => 'users', 'valor' => 30, 'sufijo' => '+', 'texto' => 'Empresas vinculadas'],
    ['id' => 3, 'icono' => 'graduation', 'valor' => 50, 'sufijo' => '+', 'texto' => 'Investigadores'],
    ['id' => 4, 'icono' => 'award', 'valor' => 10, 'sufijo' => '+', 'texto' => 'Premios obtenidos']
];
file_put_contents(DATA_DIR . '/counters.json', json_encode($counters, JSON_PRETTY_PRINT));

// Sliders (homepage)
$sliders = [
    ['id' => 1, 'titulo' => 'Bienvenidos', 'subtitulo' => 'Parque Cientifico Tecnologico de Villa Clara', 'imagen' => 'assets/img/sliders/slider-01.jpg', 'orden' => 1, 'activo' => true],
    ['id' => 2, 'titulo' => 'Innovacion', 'subtitulo' => 'Impulsando el desarrollo tecnologico', 'imagen' => 'assets/img/sliders/slider-02.jpg', 'orden' => 2, 'activo' => true]
];
file_put_contents(DATA_DIR . '/sliders.json', json_encode($sliders, JSON_PRETTY_PRINT));

// Servicios
$servicios = [
    ['id' => 1, 'categoria' => 'primaria', 'titulo' => 'Consultoria Tecnologica', 'descripcion' => 'Asesoramiento especializado en tecnologias de la informacion', 'icono' => 'cpu'],
    ['id' => 2, 'categoria' => 'primaria', 'titulo' => 'Desarrollo de Software', 'descripcion' => 'Soluciones a medida para su empresa', 'icono' => 'code']
];
file_put_contents(DATA_DIR . '/servicios.json', json_encode($servicios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// TIC
$tic = [
    ['id' => 1, 'titulo' => 'Transformacion Digital', 'descripcion' => 'Impulsamos la transformacion digital de las empresas', 'icono' => 'monitor', 'activo' => true]
];
file_put_contents(DATA_DIR . '/tic.json', json_encode($tic, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Proyectos stats
$proyStats = [
    ['id' => 1, 'valor' => 0, 'sufijo' => '', 'texto' => 'Proyectos gestionados'],
    ['id' => 2, 'valor' => 0, 'sufijo' => '', 'texto' => 'Empresariales'],
    ['id' => 3, 'valor' => 0, 'sufijo' => '', 'texto' => 'Cooperacion'],
    ['id' => 4, 'valor' => 0, 'sufijo' => '', 'texto' => 'Innovacion tecnologica']
];
file_put_contents(DATA_DIR . '/proyectos_stats.json', json_encode($proyStats, JSON_PRETTY_PRINT));

// Categorias
$categorias = [
    ['id' => 1, 'nombre' => 'Noticias', 'descripcion' => 'Ultimas novedades', 'created_at' => date('Y-m-d H:i:s')],
    ['id' => 2, 'nombre' => 'Eventos', 'descripcion' => 'Proximos eventos', 'created_at' => date('Y-m-d H:i:s')]
];
file_put_contents(DATA_DIR . '/categorias.json', json_encode($categorias, JSON_PRETTY_PRINT));
?>
<h1>Setup completado</h1>
<p>Carpeta <strong>data/</strong> creada con los archivos iniciales.</p>
<ul>
    <li>Usuario: marioc@pctvc.cu</li>
    <li>Contrase&ntilde;a: 12345678</li>
</ul>
<p><a href="index.php">Ir al sitio</a> | <a href="admin/login.php">Ir al admin</a></p>
