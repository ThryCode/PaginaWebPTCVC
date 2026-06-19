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
function getProyectoIcon($area) {
    $area = strtolower(trim(html_entity_decode($area)));
    $map = array(
        'biotecnología' => 'proy-biotec',
        'tic' => 'proy-tic',
        'energía' => 'proy-energia',
        'industria' => 'proy-industria',
        'agricultura' => 'proy-agricultura',
        'medio ambiente' => 'proy-ambiente',
        'salud' => 'proy-salud',
        'educación' => 'proy-educacion',
    );
    foreach ($map as $key => $class) {
        if (strpos($area, $key) !== false) return $class;
    }
    return 'proy-otros';
}

function renderProyectoIcon($area) {
    $svgs = array(
        'proy-biotec' => '<svg class="icon-proy-biotec" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>',
        'proy-tic' => '<svg class="icon-proy-tic" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>',
        'proy-energia' => '<svg class="icon-proy-energia" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>',
        'proy-industria' => '<svg class="icon-proy-industria" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'proy-agricultura' => '<svg class="icon-proy-agricultura" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c0 0-8-4-8-12 0-3 2-5 5-5 6 0 12 6 13 12 .5 3-10 5-10 5z"/><path d="M12 12l5-5"/></svg>',
        'proy-ambiente' => '<svg class="icon-proy-ambiente" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>',
        'proy-salud' => '<svg class="icon-proy-salud" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>',
        'proy-educacion' => '<svg class="icon-proy-educacion" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
    );
    $icon = getProyectoIcon($area);
    if (isset($svgs[$icon])) return $svgs[$icon];
    return '<svg class="icon-proy-otros" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>';
}

include 'includes/header.php'; ?>

        <section class="page-header">
            <div class="container">
                <h2>Proyectos</h2>
                <p>Proyectos de innovación y desarrollo</p>
            </div>
        </section>

        <section class="intro-text-section">
            <div class="container">
                <p>En el Parque Científico Tecnológico de Villa Clara desarrollamos proyectos de innovación, ciencia y tecnología que impulsan el desarrollo sostenible de la región. Nuestros proyectos abarcan diversas áreas estratégicas.</p>
            </div>
        </section>

        <section class="services-preview" style="background: #E6F4FA;">
            <div class="container">
                <div class="section-title">
                    <h3>Nuestros Proyectos</h3>
                    <p>Soluciones innovadoras para los desafíos actuales.</p>
                </div>
                <div class="grid">
                    <?php if (empty($proyectos)): ?>
                        <p class="empty" style="grid-column:1/-1;text-align:center;">No hay proyectos disponibles.</p>
                    <?php else: ?>
                        <?php foreach ($proyectos as $i => $p): ?>
                        <div class="project-card animate-on-scroll<?php echo $i > 0 ? ' delay-' . $i : ''; ?>">
                            <div class="project-card-top"></div>
                            <div class="project-card-body">
                                <div class="project-card-icon"><?php echo renderProyectoIcon($p['area'] ?? ''); ?></div>
                                <h4><?php echo htmlspecialchars($p['titulo'] ?? ''); ?></h4>
                                <p><?php echo htmlspecialchars($p['resumen'] ?? ''); ?></p>
                                <a href="#" class="btn btn-outline-green">Conocer m&aacute;s</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <?php
                    $counters = Storage::read('proyectos_counters');
                    usort($counters, function($a, $b) {
                        return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
                    });
                    foreach ($counters as $i => $c):
                    ?>
                    <div class="stat-item animate-on-scroll<?php echo $i > 0 ? ' delay-' . $i : ''; ?>">
                        <div class="stat-number"><?php echo intval($c['numero']); ?>+</div>
                        <div class="stat-label"><?php echo htmlspecialchars($c['label']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>
