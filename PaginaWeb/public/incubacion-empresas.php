<?php
$pageTitle = 'Incubaci&oacute;n de Empresas - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Programa de incubaci&oacute;n de empresas del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara. Servicios de formaci&oacute;n, asesor&iacute;a y acompa&ntilde;amiento para emprendedores.';
$canonicalUrl = 'https://pctvc.cu/incubacion-empresas.php';

require_once __DIR__ . '/api/icons.php';
require_once __DIR__ . '/api/storage.php';

$items = array();
$all = Storage::read('servicios');
foreach ($all as $s) {
    if (($s['pagina'] ?? '') === 'incubacion-empresas') {
        $items[] = $s;
    }
}
usort($items, function($a, $b) { return $a['orden'] - $b['orden']; });

include 'includes/header.php';
?>

        <section class="page-header">
            <div class="container">
                <h1>Incubaci&oacute;n de Empresas</h1>
                <p>Apoyo integral para emprendedores y startups</p>
            </div>
        </section>

        <section class="about-preview">
            <div class="container">
                <div class="about-content">
                    <h3>¿Qué es la incubaci&oacute;n de empresas?</h3>
                    <p>La incubaci&oacute;n de empresas es un proceso dise&ntilde;ado para apoyar a emprendedores y startups en sus etapas iniciales de desarrollo. Las incubadoras de empresas proporcionan una variedad de recursos y servicios.</p>
                </div>
                <div class="about-image">
                    <div class="about-image-card">
                        <div class="about-image-bg"></div>
                        <div class="about-image-content">
                            <svg aria-hidden="true" class="about-image-icon" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M40 16V28" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M28 36H52V64C52 66.2 50.2 68 48 68H32C29.8 68 28 66.2 28 64V36Z" stroke="white" stroke-width="2.5" fill="none"/>
                                <path d="M22 36H58" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M32 46H48" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <path d="M32 54H44" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <path d="M40 28C44.4 28 48 31.6 48 36" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                <path d="M40 28C35.6 28 32 31.6 32 36" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                            </svg>
                            <span class="about-image-label">Incubaci&oacute;n de Empresas</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="services-preview">
            <div class="container">
                <div class="section-title">
                    <h3>Servicios de Incubaci&oacute;n</h3>
                    <p>Todo lo que necesitas para hacer crecer tu idea de negocio.</p>
                </div>
                <div class="grid">
                    <?php if (empty($items)): ?>
                    <p style="color:#888;text-align:center;grid-column:1/-1;">No hay servicios disponibles en este momento.</p>
                    <?php else: ?>
                    <?php foreach ($items as $i => $s): ?>
                    <div class="card animate-on-scroll<?php echo $i > 0 ? ' delay-' . $i : ''; ?>">
                        <div class="card-icon">
                            <?php echo getIcono($s['icono'] ?? 'documento', $iconos); ?>
                        </div>
                        <h4><?php echo htmlspecialchars($s['nombre']); ?></h4>
                        <p><?php echo htmlspecialchars($s['descripcion']); ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="about-preview" style="background-color: #E6F4FA;">
            <div class="container" style="text-align: center; grid-template-columns: 1fr;">
                <h3 style="font-size: 2.2rem; font-weight: 900; margin-bottom: 20px; color: #004966;">&iquest;Tienes una idea innovadora?</h3>
                <p style="color: #666; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto; font-size: 1.05rem;">Conoce todos nuestros servicios y descubre c&oacute;mo podemos ayudarte a hacerla realidad.</p>
                <a href="servicios.php" class="btn btn-primary">Ver Todos los Servicios</a>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>
