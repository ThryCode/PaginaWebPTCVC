<?php
require_once 'api/storage.php';
$proyectos = Storage::read('proyectos');
$proyectos = array_filter($proyectos, function($p) { return !empty($p['publicada']); });
usort($proyectos, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$proyectos = array_values($proyectos);
$iconos = array('&#128300;', '&#9881;', '&#127758;');
include 'includes/header.php'; ?>

        <section class="page-header">
            <div class="container">
                <h2>Proyectos</h2>
                <p>Proyectos de innovaciÃ³n y desarrollo</p>
            </div>
        </section>

        <section class="intro-text-section">
            <div class="container">
                <p>En el Parque CientÃ­fico TecnolÃ³gico de Villa Clara desarrollamos proyectos de innovaciÃ³n, ciencia y tecnologÃ­a que impulsan el desarrollo sostenible de la regiÃ³n. Nuestros proyectos abarcan diversas Ã¡reas estratÃ©gicas.</p>
            </div>
        </section>

        <section class="services-preview" style="background: #E6F4FA;">
            <div class="container">
                <div class="section-title">
                    <h3>Nuestros Proyectos</h3>
                    <p>Soluciones innovadoras para los desafÃ­os actuales.</p>
                </div>
                <div class="grid">
                    <?php if (empty($proyectos)): ?>
                        <p class="empty" style="grid-column:1/-1;text-align:center;">No hay proyectos disponibles.</p>
                    <?php else: ?>
                        <?php foreach ($proyectos as $i => $p): ?>
                        <div class="project-card animate-on-scroll<?php echo $i > 0 ? ' delay-' . $i : ''; ?>">
                            <div class="project-card-top"></div>
                            <div class="project-card-body">
                                <div class="project-card-icon icon-<?php echo ($i % 3) + 1; ?>"><?php echo $iconos[$i % 3]; ?></div>
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
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Proyectos activos</div>
                    </div>
                    <div class="stat-item animate-on-scroll delay-1">
                        <div class="stat-number">40+</div>
                        <div class="stat-label">Empresas asociadas</div>
                    </div>
                    <div class="stat-item animate-on-scroll delay-2">
                        <div class="stat-number">25+</div>
                        <div class="stat-label">Patentes registradas</div>
                    </div>
                    <div class="stat-item animate-on-scroll delay-3">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Innovaciones</div>
                    </div>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>
