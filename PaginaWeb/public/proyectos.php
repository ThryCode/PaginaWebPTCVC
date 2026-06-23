<?php
$pageTitle = 'Proyectos - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Nuestros proyectos de innovaci&oacute;n, transferencia tecnol&oacute;gica y desarrollo empresarial en el Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara.';
$canonicalUrl = 'https://pctvc.cu/proyectos.php';
require_once 'api/storage.php';
$proyectos = Storage::read('proyectos');
$proyectos = array_filter($proyectos, function($p) { return !empty($p['publicada']); });
usort($proyectos, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$proyectos = array_values($proyectos);
$categorias = array();
foreach ($proyectos as $p) {
    $cat = trim($p['categoria'] ?? '');
    if ($cat !== '' && !in_array($cat, $categorias)) {
        $categorias[] = $cat;
    }
}
function renderEstadoBadge($estado) {
    $class = 'status-ejecucion';
    if (strtolower(trim($estado)) === 'finalizado') $class = 'status-finalizado';
    return '<span class="status-badge ' . $class . '">' . htmlspecialchars($estado) . '</span>';
}
function renderProyImagenes($p) {
    $imgs = array();
    if (!empty($p['imagenes']) && is_array($p['imagenes'])) {
        $imgs = $p['imagenes'];
    } elseif (!empty($p['imagen'])) {
        $imgs = array($p['imagen']);
    }
    $titulo = htmlspecialchars($p['titulo'] ?? '');
    if (empty($imgs)) {
        return '<img src="assets/img/logo/logo.png" alt="' . $titulo . '" loading="lazy" style="object-fit:contain;padding:20px;width:100%;height:100%;">';
    }
    if (count($imgs) === 1) {
        return '<img src="' . htmlspecialchars($imgs[0]) . '" alt="' . $titulo . '" loading="lazy" style="width:100%;height:100%;object-fit:cover;">';
    }
    $html = '<div class="card-carousel" data-count="' . count($imgs) . '">';
    $html .= '<div class="carousel-track">';
    foreach ($imgs as $src) {
        $html .= '<div class="carousel-slide"><img src="' . htmlspecialchars($src) . '" alt="' . $titulo . '" loading="lazy"></div>';
    }
    $html .= '</div><div class="carousel-dots">';
    foreach ($imgs as $i => $src) {
        $html .= '<span class="carousel-dot' . ($i === 0 ? ' active' : '') . '"></span>';
    }
    $html .= '</div></div>';
    return $html;
}
include 'includes/header.php'; ?>

<section class="proyectos-hero">
    <div class="proyectos-hero-bg"></div>
    <div class="proyectos-hero-blur bl-1"></div>
    <div class="proyectos-hero-blur bl-2"></div>
    <div class="proyectos-hero-blur bl-3"></div>
    <div class="proyectos-hero-grid"></div>
    <div class="proyectos-hero-content">
        <h1 class="anim-scroll">Proyectos</h1>
        <p class="anim-scroll">Los proyectos gestionados por el Parque Cient&iacute;fico Tecnol&oacute;gico Villa Clara se alinean con prioridades nacionales en ciencia, tecnolog&iacute;a e innovaci&oacute;n, con impacto directo en sectores estrat&eacute;gicos de la econom&iacute;a.</p>
    </div>
</section>

<section class="counters-section">
    <div class="container">
        <?php $stats = Storage::read('proyectos_stats'); $totalStats = count($stats); $colsStats = $totalStats <= 4 ? $totalStats : 4; ?>
        <div class="counters-grid" style="--cols:<?php echo $colsStats; ?>">
            <?php foreach ($stats as $s): ?>
            <div class="counter-item">
                <div class="counter-number" data-target="<?php echo intval($s['count']); ?>">0</div>
                <div class="counter-label"><?php echo htmlspecialchars($s['label']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="proyectos-body">
    <div class="container">
        <?php if (!empty($categorias)): ?>
        <div class="proyectos-tabs" role="tablist">
            <?php foreach ($categorias as $i => $cat): ?>
            <button class="proy-tab<?php echo $i === 0 ? ' active' : ''; ?>" role="tab" data-tab="tab-<?php echo $i; ?>"><?php echo htmlspecialchars($cat); ?></button>
            <?php endforeach; ?>
        </div>
        <?php foreach ($categorias as $i => $cat): ?>
        <div class="proy-panel<?php echo $i === 0 ? ' active' : ''; ?>" id="tab-<?php echo $i; ?>" role="tabpanel">
            <div class="proy-grid">
                <?php foreach ($proyectos as $p): ?>
                <?php if (trim($p['categoria'] ?? '') === $cat): ?>
                <div class="proy-card anim-scroll">
                    <div class="proy-card-img">
                        <?php echo renderProyImagenes($p); ?>
                    </div>
                    <div class="proy-card-body">
                        <h3><?php echo htmlspecialchars($p['titulo']); ?></h3>
                        <div class="proy-card-text">
                            <p class="proy-resumen"><?php echo nl2br(htmlspecialchars($p['resumen'] ?? '')); ?></p>
                            <?php if (!empty($p['contenido']) && trim($p['contenido']) !== trim($p['resumen'] ?? '')): ?>
                            <div class="proy-completo" style="display:none;"><?php echo nl2br(htmlspecialchars($p['contenido'])); ?></div>
                            <button class="proy-leer-mas" data-mas="Leer m&aacute;s" data-menos="Mostrar menos">Leer m&aacute;s</button>
                            <?php endif; ?>
                        </div>
                        <div class="proy-card-footer">
                            <?php if (!empty($p['responsable'])): ?>
                            <span class="proy-label"><?php echo htmlspecialchars($p['responsable']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($p['estado'])): ?>
                            <?php echo renderEstadoBadge($p['estado']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p class="empty" style="text-align:center;padding:60px 0;color:#646464;">No hay proyectos disponibles.</p>
        <?php endif; ?>
    </div>
</section>

<script>
(function() {
    var tabs = document.querySelectorAll('.proy-tab');
    var panels = document.querySelectorAll('.proy-panel');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            panels.forEach(function(p) { p.classList.remove('active'); });
            tab.classList.add('active');
            var target = document.getElementById(tab.getAttribute('data-tab'));
            if (target) target.classList.add('active');
        });
    });
    var animEls = document.querySelectorAll('.anim-scroll');
    if (animEls.length) {
        var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                if (e.isIntersecting) { e.target.classList.add('anim-visible'); obs.unobserve(e.target); }
            });
        }, { threshold: 0.15 });
        animEls.forEach(function(el) { obs.observe(el); });
    }
})();
window.addEventListener('load', function() {
    var proyCards = document.querySelector('.proyectos-body');
    if (proyCards && typeof initCardCarousels === 'function') {
        initCardCarousels(proyCards);
    }
    document.querySelectorAll('.proy-leer-mas').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var card = this.closest('.proy-card-text');
            var completo = card.querySelector('.proy-completo');
            if (!completo) return;
            var abierto = completo.style.display !== 'none';
            completo.style.display = abierto ? 'none' : 'block';
            this.textContent = abierto ? this.getAttribute('data-mas') : this.getAttribute('data-menos');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
