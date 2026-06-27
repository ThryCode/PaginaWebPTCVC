<?php
function _cacheBust($path) {
    $abs = __DIR__ . '/' . $path;
    $v = file_exists($abs) ? filemtime($abs) : time();
    return $path . '?v=' . $v;
}

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

<?php
// Helper to resolve flyer image path robustly
function resolve_flyer_image_path($relativePath) {
    $publicDir = __DIR__;
    $rel = ltrim($relativePath, '/');
    $full = $publicDir . '/' . $rel;

    // 1) exact match
    if (file_exists($full) && is_file($full)) {
        return $rel;
    }

    // 2) try common extensions using same filename base
    $filename = pathinfo($rel, PATHINFO_FILENAME);
    $dir = $publicDir . '/uploads/flyers/';
    $exts = ['png','jpg','jpeg','webp','gif'];
    foreach ($exts as $ext) {
        $try = $dir . $filename . '.' . $ext;
        if (file_exists($try) && is_file($try)) {
            return 'uploads/flyers/' . basename($try);
        }
    }

    // 3) glob by filename base
    $matches = glob($dir . $filename . '.*');
    if (!empty($matches)) {
        return 'uploads/flyers/' . basename($matches[0]);
    }

    // 4) try to extract numeric ID and search
    if (preg_match('/(\d{6,})/', $filename, $m)) {
        $id = $m[1];
        $found = glob($dir . '*' . $id . '*');
        if (!empty($found)) {
            return 'uploads/flyers/' . basename($found[0]);
        }
    }

    // 5) fallback placeholder
    error_log('[flyers] imagen no encontrada: ' . $relativePath);
    return 'assets/img/general/placeholder-flyer.svg';
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
                        <img src="<?php echo htmlspecialchars(_cacheBust(resolve_flyer_image_path($f['imagen']))); ?>" alt="<?php echo htmlspecialchars($f['titulo']); ?>" loading="lazy">
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
