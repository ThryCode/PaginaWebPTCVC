<?php
$pageTitle = 'Flyers - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Flyers de eventos y actividades destacadas del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara.';
$canonicalUrl = 'https://pctvc.cu/flyers.php';
include 'includes/header.php';

$flyersFile = __DIR__ . '/data/flyers.json';
$flyers = array();

if (file_exists($flyersFile)) {
    $raw = file_get_contents($flyersFile);
    $all = json_decode($raw, true);
    if (is_array($all)) {
        usort($all, function($a, $b) {
            return $a['orden'] - $b['orden'];
        });
        $flyers = $all;
    }
}
?>

        <section class="page-header">
            <div class="container">
                <h1>Flyers</h1>
                <p>Explora nuestra colecci&oacute;n de flyers de eventos y actividades destacadas.</p>
            </div>
        </section>

        <?php if (!empty($flyers)): ?>
        <section class="flyers-section">
            <div class="container">
                <div class="flyers-grid">
                    <?php foreach ($flyers as $f): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($f['imagen']); ?>" alt="<?php echo htmlspecialchars($f['titulo']); ?>" loading="lazy">
                        <div class="gallery-overlay">
                            <span><?php echo htmlspecialchars($f['titulo']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php else: ?>
        <section class="empty-state">
            <div class="container">
                <p>No hay flyers disponibles en este momento.</p>
            </div>
        </section>
        <?php endif; ?>


        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var items = document.querySelectorAll('.flyers-grid .gallery-item');
            items.forEach(function(item) {
                item.addEventListener('click', function() {
                    var img = this.querySelector('img');
                    if (img) openLightbox(img.src, img.alt);
                });
            });
        });
        </script>

<?php include 'includes/footer.php'; ?>
