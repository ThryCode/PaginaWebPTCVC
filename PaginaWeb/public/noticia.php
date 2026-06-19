<?php
require_once 'api/storage.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$item = null;
if ($id > 0) {
    $item = Storage::findById('noticias', $id);
}
if (!$item || empty($item['publicada'])) {
include 'includes/header.php';
?>
        <section class="page-header">
            <div class="container">
                <h2>Publicaci&oacute;n no encontrada</h2>
                <p>La publicaci&oacute;n que buscas no existe o ha sido eliminada.</p>
                <a href="noticias.php" class="btn btn-primary">&larr; Volver a Noticias</a>
            </div>
        </section>
<?php include 'includes/footer.php'; exit; }

$fechaRaw = $item['fecha_evento'] ?? $item['created_at'];
$fecha = new DateTime($fechaRaw);
$fechaStr = $fecha->format('d/m/Y');
$tipoLabel = ucfirst($item['tipo'] ?? 'noticia');

$imagenes = array();
if (isset($item['imagenes']) && is_array($item['imagenes'])) {
    $imagenes = $item['imagenes'];
} elseif (isset($item['imagen'])) {
    $imagenes = array($item['imagen']);
}

$pageTitle = htmlspecialchars($item['titulo']) . ' - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = !empty($item['resumen']) ? $item['resumen'] : $item['titulo'];
$canonicalUrl = 'https://pctvc.cu/noticia.php?id=' . $id;
$ogType = 'article';
if (!empty($imagenes)) {
    $ogImage = 'https://pctvc.cu/' . ltrim($imagenes[0], '/');
}

include 'includes/header.php';
?>
        <script type="application/ld+json">
        <?php echo json_encode(array(
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $item['titulo'],
            'description' => !empty($item['resumen']) ? $item['resumen'] : '',
            'datePublished' => $fechaRaw,
            'author' => array(
                '@type' => 'Organization',
                'name' => 'Parque Científico Tecnológico de Villa Clara'
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => 'Parque Científico Tecnológico de Villa Clara',
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => 'https://pctvc.cu/assets/img/logo/logo.png'
                )
            ),
            'image' => !empty($imagenes) ? array('https://pctvc.cu/' . ltrim($imagenes[0], '/')) : array()
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        </script>
        <section class="page-header">
            <div class="container">
                <h2 class="animate-fade-down"><?php echo htmlspecialchars($item['titulo']); ?></h2>
                <p class="animate-fade-up"><?php echo $tipoLabel; ?> &mdash; <?php echo $fechaStr; ?></p>
            </div>
        </section>

        <section class="news-section">
            <div class="container">
                <div class="detail-card">
                    <?php if (!empty($imagenes)): ?>
                    <div class="detail-images">
                        <?php if (count($imagenes) === 1): ?>
                        <div class="detail-img-main">
                            <img src="<?php echo $imagenes[0]; ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                        </div>
                        <?php else: ?>
                        <div class="card-carousel" data-count="<?php echo count($imagenes); ?>">
                            <div class="carousel-track">
                                <?php foreach ($imagenes as $src): ?>
                                <div class="carousel-slide"><img src="<?php echo $src; ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>"></div>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-dots">
                                <?php for ($i = 0; $i < count($imagenes); $i++): ?>
                                <span class="carousel-dot<?php echo $i === 0 ? ' active' : ''; ?>"></span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="detail-meta">
                        <span class="news-tag <?php echo $item['tipo'] === 'evento' ? 'news-tag-evento' : 'news-tag-noticia'; ?>"><?php echo $tipoLabel; ?></span>
                        <span class="detail-date"><?php echo $fechaStr; ?></span>
                        <?php if (!empty($item['ubicacion'])): ?>
                        <span class="detail-location">&#128205; <?php echo htmlspecialchars($item['ubicacion']); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['resumen'])): ?>
                    <div class="detail-resumen">
                        <p><?php echo nl2br(htmlspecialchars($item['resumen'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($item['contenido'])): ?>
                    <div class="detail-contenido">
                        <?php echo nl2br(htmlspecialchars($item['contenido'])); ?>
                    </div>
                    <?php endif; ?>

                    <div class="detail-footer">
                        <a href="<?php echo $item['tipo'] === 'evento' ? 'eventos.php' : 'noticias.php'; ?>" class="btn btn-primary">&larr; Volver</a>
                    </div>
                </div>
            </div>
        </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var carousels = document.querySelectorAll('.card-carousel');
        carousels.forEach(function(carousel) {
            var track = carousel.querySelector('.carousel-track');
            var slides = track.querySelectorAll('.carousel-slide');
            var dots = carousel.querySelectorAll('.carousel-dot');
            var count = slides.length;
            if (count < 2) return;
            var cw = carousel.offsetWidth;
            if (cw === 0) cw = carousel.getBoundingClientRect().width;
            if (cw === 0) cw = carousel.parentElement.offsetWidth;
            track.style.width = (count * cw) + 'px';
            for (var i = 0; i < slides.length; i++) {
                slides[i].style.width = cw + 'px';
            }
            var current = 0;
            setInterval(function() {
                current = (current + 1) % count;
                track.style.transform = 'translateX(-' + (current * cw) + 'px)';
                dots.forEach(function(d) { d.classList.remove('active'); });
                if (dots[current]) dots[current].classList.add('active');
            }, 4000);
        });
    });
    </script>

<?php include 'includes/footer.php'; ?>
