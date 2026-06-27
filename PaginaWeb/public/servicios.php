<?php
$pageTitle = 'Servicios - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Servicios de innovaci&oacute;n, transferencia tecnol&oacute;gica, incubaci&oacute;n de empresas, capacitaci&oacute;n y consultor&iacute;a del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara.';
$canonicalUrl = 'https://pctvc.cu/servicios.php';
require_once __DIR__ . '/api/storage.php';
require_once __DIR__ . '/api/functions.php';
require_once __DIR__ . '/api/icons.php';
include 'includes/header.php';

$jsonLd = array(
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Servicios del Parque Científico Tecnológico de Villa Clara',
    'description' => $pageDescription,
    'url' => $canonicalUrl,
    'itemListElement' => array()
);



$all = Storage::read('servicios');
$primarias = array();
$secundarias = array();
$estrategicos = array();

usort($all, function($a, $b) {
    $tipoA = $a['tipo'] ?? '';
    $tipoB = $b['tipo'] ?? '';
    if ($tipoA === $tipoB) {
        return $a['orden'] - $b['orden'];
    }
    return strcmp($tipoA, $tipoB);
});
foreach ($all as $s) {
    $pag = $s['pagina'] ?? 'servicios';
    if ($pag !== 'servicios') continue;
    $tipo = $s['tipo'] ?? '';
    if ($tipo === 'primaria') {
        $primarias[] = $s;
    } elseif ($tipo === 'estrategico') {
        $estrategicos[] = $s;
    } else {
        $secundarias[] = $s;
    }
    $jsonLd['itemListElement'][] = array(
        '@type' => 'Service',
        'position' => $s['orden'] ?? 0,
        'name' => $s['nombre'],
        'description' => $s['descripcion'] ?? ''
    );
}

$ticItems = Storage::read('tic');
usort($ticItems, function($a, $b) {
    return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
});

?>

        <section class="page-header">
            <div class="container">
                <h1>Servicios</h1>
                <p>Descubre nuestros servicios de innovaci&oacute;n, transferencia de tecnolog&iacute;a, incubaci&oacute;n y capacitaci&oacute;n empresarial.</p>
            </div>
        </section>

        <?php if (!empty($primarias)): ?>
        <section class="services-preview">
            <div class="container">
                <div class="section-title">
                    <h3>Actividades Principales</h3>
                </div>
                <div class="grid">
                    <?php foreach ($primarias as $i => $s): ?>
                    <div class="card animate-on-scroll<?php echo ($i % 4 > 0) ? ' delay-' . ($i % 4) : ''; ?>">
                        <div class="card-icon">
                            <?php echo getIcono($s['icono'], $iconos); ?>
                        </div>
                        <h4><?php echo htmlspecialchars($s['nombre']); ?></h4>
                        <p><?php echo htmlspecialchars($s['descripcion']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($estrategicos)): ?>
        <section class="services-preview" style="background: #E6F4FA;">
            <div class="container">
                <div class="section-title">
                    <h3>Servicios Estrat&eacute;gicos y Tecnol&oacute;gicos</h3>
                </div>
                <div class="grid">
                    <?php foreach ($estrategicos as $i => $s): ?>
                    <div class="card animate-on-scroll<?php echo ($i % 4 > 0) ? ' delay-' . ($i % 4) : ''; ?>">
                        <div class="card-icon">
                            <?php echo getIcono($s['icono'], $iconos); ?>
                        </div>
                        <h4><?php echo htmlspecialchars($s['nombre']); ?></h4>
                        <p><?php echo htmlspecialchars($s['descripcion']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($ticItems)): ?>
                <div class="tic-grid">
                    <h4 class="tic-title">Servicios vinculados a las Tecnolog&iacute;as de la Informaci&oacute;n y las Comunicaciones (TIC)</h4>
                    <div class="check-list cols-3">
                        <?php foreach ($ticItems as $it): ?>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p><?php echo htmlspecialchars($it['texto']); ?></p></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($secundarias)): ?>
        <section class="services-preview">
            <div class="container">
                <div class="section-title">
                    <h3>Actividades Secundarias</h3>
                </div>
                <div class="grid">
                    <?php foreach ($secundarias as $i => $s): ?>
                    <div class="card animate-on-scroll<?php echo ($i % 4 > 0) ? ' delay-' . ($i % 4) : ''; ?>">
                        <div class="card-icon">
                            <?php echo getIcono($s['icono'], $iconos); ?>
                        </div>
                        <h4><?php echo htmlspecialchars($s['nombre']); ?></h4>
                        <p><?php echo htmlspecialchars($s['descripcion']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="services-preview" style="background: #E6F4FA;">
            <div class="container">
                <div class="section-title">
                    <h3>Servicios Especializados</h3>
                    <p>Conoce nuestras &aacute;reas de servicio especializado.</p>
                </div>
                <div class="grid">
                    <div class="card animate-on-scroll">
                        <div class="card-icon">
                            <svg class="icon-coop-estrategica" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h4>Producciones Cooperadas</h4>
                        <p>Alianzas estrat&eacute;gicas entre empresas para compartir recursos, acceder a nuevos mercados y fomentar la innovaci&oacute;n.</p>
                        <a href="producciones-cooperadas.php" class="btn btn-outline-green" style="margin-top: 15px;">Conocer m&aacute;s</a>
                    </div>
                    <div class="card animate-on-scroll delay-1">
                        <div class="card-icon">
                            <svg class="icon-incubacion" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c6 3 10 0 12-2v-5"/></svg>
                        </div>
                        <h4>Incubaci&oacute;n de Empresas</h4>
                        <p>Apoyo integral a emprendedores y startups en sus etapas iniciales con capacitaci&oacute;n, mentor&iacute;a y financiamiento.</p>
                        <a href="incubacion-empresas.php" class="btn btn-outline-green" style="margin-top: 15px;">Conocer m&aacute;s</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="cta-blob cta-blob-1"></div>
            <div class="cta-blob cta-blob-2"></div>
            <div class="container">
                <div class="cta-card animate-on-scroll">
                    <div class="cta-grid">
                        <div>
                            <h3>¿Interesado en nuestros servicios?</h3>
                            <p>Si su empresa, proyecto o iniciativa se encuentra en proceso de creaci&oacute;n, incubaci&oacute;n o crecimiento, nuestro equipo puede acompa&ntilde;arle con soluciones t&eacute;cnicas, tecnol&oacute;gicas y de gesti&oacute;n adaptadas a sus necesidades.</p>
                            <p>Cont&aacute;ctenos para recibir informaci&oacute;n personalizada o coordinar una primera consulta con nuestros especialistas.</p>
                        </div>
                        <div class="cta-actions">
                            <a href="contacto.php" class="cta-btn">
                                Solicitar informaci&oacute;n
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                            <span class="cta-meta">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                Atenci&oacute;n personalizada y confidencial
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

<script type="application/ld+json">
<?php echo json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<?php include 'includes/footer.php'; ?>