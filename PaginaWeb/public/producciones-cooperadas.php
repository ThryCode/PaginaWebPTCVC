<?php
$pageTitle = 'Producciones Cooperadas - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Producciones cooperadas del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara. Beneficios de la cooperaci&oacute;n empresarial para el desarrollo tecnol&oacute;gico.';
$canonicalUrl = 'https://pctvc.cu/producciones-cooperadas.php';

require_once __DIR__ . '/api/icons.php';
require_once __DIR__ . '/api/storage.php';

$items = array();
$all = Storage::read('servicios');
foreach ($all as $s) {
    if (($s['pagina'] ?? '') === 'producciones-cooperadas') {
        $items[] = $s;
    }
}
usort($items, function($a, $b) { return $a['orden'] - $b['orden']; });

include 'includes/header.php';
?>

        <section class="page-header">
            <div class="container">
                <h1>Producciones Cooperadas</h1>
                <p>Alianzas estratégicas para el crecimiento empresarial</p>
            </div>
        </section>

        <section class="about-preview">
            <div class="container">
                <div class="about-content">
                    <h3>¿Qué son las Producciones Cooperadas?</h3>
                    <p>Las producciones cooperadas entre empresas, tambi&eacute;n conocidas como cooperaci&oacute;n empresarial, son alianzas estrat&eacute;gicas donde dos o m&aacute;s empresas colaboran para alcanzar objetivos comunes. Este tipo de cooperaci&oacute;n puede tomar varias formas y tiene m&uacute;ltiples beneficios:</p>
                </div>
                <div class="about-image">
                    <div class="about-image-card">
                        <div class="about-image-bg"></div>
                        <div class="about-image-content">
                            <svg aria-hidden="true" class="about-image-icon" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 56L40 20L68 56" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                <path d="M24 56H56" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="26" cy="40" r="6" stroke="white" stroke-width="2" fill="none"/>
                                <circle cx="54" cy="40" r="6" stroke="white" stroke-width="2" fill="none"/>
                                <path d="M32 40H48" stroke="white" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 3"/>
                                <path d="M40 48V56" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <path d="M36 62H44" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="about-image-label">Cooperaci&oacute;n e Innovaci&oacute;n Empresarial</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="services-preview">
            <div class="container">
                <div class="section-title">
                    <h3>Beneficios de la Cooperaci&oacute;n Empresarial</h3>
                    <p>Descubre c&oacute;mo la colaboraci&oacute;n entre empresas impulsa el &eacute;xito.</p>
                </div>
                <div class="grid">
                    <?php if (empty($items)): ?>
                    <p style="color:#888;text-align:center;grid-column:1/-1;">No hay beneficios disponibles en este momento.</p>
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
                <h3 style="font-size: 2.2rem; font-weight: 900; margin-bottom: 20px; color: #004966;">&iquest;Listo para colaborar?</h3>
                <p style="color: #666; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto; font-size: 1.05rem;">Descubre todos nuestros servicios y encuentra la soluci&oacute;n que tu empresa necesita.</p>
                <a href="servicios.php" class="btn btn-primary">Ver Todos los Servicios</a>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>
