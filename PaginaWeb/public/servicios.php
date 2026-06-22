<?php
$pageTitle = 'Servicios - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Servicios de innovaci&oacute;n, transferencia tecnol&oacute;gica, incubaci&oacute;n de empresas, capacitaci&oacute;n y consultor&iacute;a del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara.';
$canonicalUrl = 'https://pctvc.cu/servicios.php';
require_once 'api/storage.php';
require_once 'admin/includes/servicios_icons.php';
$servicios = Storage::read('servicios');
if (empty($servicios)) { $servicios = array(); }
include 'includes/header.php';
function renderServicioCard($s, $icons, $delay) {
    $html = '<div class="card animate-on-scroll' . ($delay > 0 ? ' delay-' . $delay : '') . '">';
    if (!empty($s['icono']) && isset($icons[$s['icono']])) {
        $icono = $icons[$s['icono']];
        $icono = str_replace('<svg', '<svg width="30" height="30"', $icono);
        $icono = str_replace('currentColor', 'white', $icono);
        if (!empty($GLOBALS['SERVICIO_ICONS_ANIM'][$s['icono']])) {
            $icono = str_replace('<svg', '<svg class="anim-' . $GLOBALS['SERVICIO_ICONS_ANIM'][$s['icono']] . '"', $icono);
        }
        $html .= '<div class="card-icon">' . $icono . '</div>';
    }
    $html .= '<h4>' . htmlspecialchars($s['titulo']) . '</h4>';
    $html .= '<p>' . htmlspecialchars($s['descripcion']) . '</p>';
    if (!empty($s['link_url'])) {
        $html .= '<a href="' . htmlspecialchars($s['link_url']) . '" class="btn btn-outline-green" style="margin-top:15px;">' . htmlspecialchars($s['link_text']?:'Conocer m&aacute;s') . '</a>';
    }
    $html .= '</div>';
    return $html;
}
function renderServiciosGrid($items, $icons) {
    if (empty($items)) return '<p class="empty" style="grid-column:1/-1;text-align:center;">No hay servicios disponibles.</p>';
    $html = '';
    $i = 0;
    foreach ($items as $s) {
        $html .= renderServicioCard($s, $icons, $i);
        $i++;
    }
    return $html;
}
function renderTicGrid($items) {
    if (empty($items)) return '';
    $html = '<div class="tic-grid"><h4 class="tic-title">Servicios vinculados a las Tecnolog&iacute;as de la Informaci&oacute;n y las Comunicaciones (TIC)</h4>';
    $html .= '<div class="check-list cols-3">';
    foreach ($items as $s) {
        $html .= '<div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>' . htmlspecialchars($s['titulo']) . '</p></div>';
    }
    $html .= '</div></div>';
    return $html;
}
function filterServicios($servicios, $categoria, $tipo = 'card') {
    $result = array();
    foreach ($servicios as $s) {
        if (($s['pagina'] ?? '') === 'servicios' && ($s['categoria'] ?? '') === $categoria && ($s['tipo'] ?? 'card') === $tipo && ($s['activo'] ?? true)) {
            $result[] = $s;
        }
    }
    usort($result, function($a, $b) { return ($a['orden']??0)-($b['orden']??0); });
    return $result;
}
?>

        <section class="page-header">
            <div class="container">
                <h2>Servicios</h2>
                <p>Descubre nuestros servicios de innovaci&oacute;n, transferencia de tecnolog&iacute;a, incubaci&oacute;n y capacitaci&oacute;n empresarial.</p>
            </div>
        </section>

        <section class="services-preview">
            <div class="container">
                <div class="section-title">
                    <h3>Actividades Principales</h3>
                </div>
                <div class="grid">
                    <?php echo renderServiciosGrid(filterServicios($servicios, 'principales'), $SERVICIO_ICONS); ?>
                </div>
            </div>
        </section>

        <section class="services-preview" style="background: #E6F4FA;">
            <div class="container">
                <div class="section-title">
                    <h3>Servicios Estrat&eacute;gicos y Tecnol&oacute;gicos</h3>
                </div>
                <div class="grid">
                    <?php echo renderServiciosGrid(filterServicios($servicios, 'estrategicos'), $SERVICIO_ICONS); ?>
                </div>
                <?php echo renderTicGrid(filterServicios($servicios, 'estrategicos', 'tic_item')); ?>
            </div>
        </section>

        <section class="services-preview">
            <div class="container">
                <div class="section-title">
                    <h3>Actividades Secundarias</h3>
                </div>
                <div class="grid">
                    <?php echo renderServiciosGrid(filterServicios($servicios, 'secundarias'), $SERVICIO_ICONS); ?>
                </div>
            </div>
        </section>

        <section class="services-preview" style="background: #E6F4FA;">
            <div class="container">
                <div class="section-title">
                    <h3>Servicios Especializados</h3>
                    <p>Conoce nuestras &aacute;reas de servicio especializado.</p>
                </div>
                <div class="grid">
                    <?php echo renderServiciosGrid(filterServicios($servicios, 'especializados'), $SERVICIO_ICONS); ?>
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

<?php include 'includes/footer.php'; ?>
