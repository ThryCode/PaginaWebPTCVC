<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'pctvc.cu';
$base = $protocol . '://' . $host;

header('Content-Type: application/xml; charset=utf-8');

require_once __DIR__ . '/api/storage.php';

$static = array(
    array('loc' => '/index.php', 'freq' => 'weekly', 'prio' => '1.0'),
    array('loc' => '/quienes-somos.php', 'freq' => 'monthly', 'prio' => '0.8'),
    array('loc' => '/servicios.php', 'freq' => 'monthly', 'prio' => '0.9'),
    array('loc' => '/noticias.php', 'freq' => 'daily', 'prio' => '0.8'),
    array('loc' => '/eventos.php', 'freq' => 'daily', 'prio' => '0.8'),
    array('loc' => '/contacto.php', 'freq' => 'monthly', 'prio' => '0.7'),
    array('loc' => '/proyectos.php', 'freq' => 'monthly', 'prio' => '0.7'),
    array('loc' => '/galeria.php', 'freq' => 'weekly', 'prio' => '0.6'),
    array('loc' => '/incubacion-empresas.php', 'freq' => 'monthly', 'prio' => '0.7'),
    array('loc' => '/producciones-cooperadas.php', 'freq' => 'monthly', 'prio' => '0.6'),
    array('loc' => '/flyers.php', 'freq' => 'weekly', 'prio' => '0.5'),
);

$dynamic = array();
$noticias = Storage::read('noticias');
foreach ($noticias as $n) {
    if (!empty($n['publicada'])) {
        $dynamic[] = array(
            'loc' => '/noticia.php?id=' . $n['id'],
            'freq' => 'monthly',
            'prio' => '0.6',
            'lastmod' => !empty($n['updated_at']) ? $n['updated_at'] : ($n['created_at'] ?? '')
        );
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($static as $u): ?>
  <url>
    <loc><?php echo $base . $u['loc']; ?></loc>
    <changefreq><?php echo $u['freq']; ?></changefreq>
    <priority><?php echo $u['prio']; ?></priority>
  </url>
<?php endforeach; ?>
<?php foreach ($dynamic as $u): ?>
  <url>
    <loc><?php echo $base . $u['loc']; ?></loc>
    <changefreq><?php echo $u['freq']; ?></changefreq>
    <priority><?php echo $u['prio']; ?></priority>
    <?php if (!empty($u['lastmod'])): ?><lastmod><?php echo htmlspecialchars($u['lastmod']); ?></lastmod><?php endif; ?>
  </url>
<?php endforeach; ?>
</urlset>