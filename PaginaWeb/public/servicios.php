<?php
$pageTitle = 'Servicios - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Servicios de innovaci&oacute;n, transferencia tecnol&oacute;gica, incubaci&oacute;n de empresas, capacitaci&oacute;n y consultor&iacute;a del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara.';
$canonicalUrl = 'https://pctvc.cu/servicios.php';
include 'includes/header.php';

$iconos = array(
    'proyectos'    => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
    'chart'        => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',
    'equipo'       => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'enlace'       => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
    'carpeta'      => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
    'edificio'     => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M3 7v14"/><path d="M21 7v14"/><path d="M6 11h3v4H6z"/><path d="M15 11h3v4h-3z"/><path d="M9 3h6v4H9z"/></svg>',
    'libro'        => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8"/><path d="M8 11h6"/></svg>',
    'moneda'       => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'documento'    => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>',
    'globo'        => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    'personas'     => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
    'clipboard'    => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
    'estrella'     => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 9.5 9.5 2 12l7.5 2.5L12 22l2.5-7.5L22 12l-7.5-2.5z"/></svg>',
    'calendario'   => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><path d="M21 6v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'casa'         => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 22V12h6v10"/></svg>',
    'impresora'    => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 17v2a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-2"/><path d="M5 7h14"/><path d="M3 7V4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3"/><path d="M19 7v8H5V7"/></svg>',
    'monitor'      => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>',
    'carrito'      => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    'restaurante'  => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><path d="M6 1v3"/><path d="M10 1v3"/><path d="M14 1v3"/></svg>',
    'llave'        => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
    'checked'      => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>'
);

function getIcono($key, $iconos) {
    return isset($iconos[$key]) ? $iconos[$key] : $iconos['documento'];
}

$serviciosFile = __DIR__ . '/data/servicios.json';
$flyersFile = __DIR__ . '/data/flyers.json';
$primarias = array();
$secundarias = array();
$flyers = array();

if (file_exists($serviciosFile)) {
    $raw = file_get_contents($serviciosFile);
    $all = json_decode($raw, true);
    if (is_array($all)) {
        usort($all, function($a, $b) {
            if ($a['tipo'] === $b['tipo']) {
                return $a['orden'] - $b['orden'];
            }
            return strcmp($a['tipo'], $b['tipo']);
        });
        foreach ($all as $s) {
            if ($s['tipo'] === 'primaria') {
                $primarias[] = $s;
            } else {
                $secundarias[] = $s;
            }
        }
    }
}

if (file_exists($flyersFile)) {
    $rawF = file_get_contents($flyersFile);
    $allF = json_decode($rawF, true);
    if (is_array($allF)) {
        usort($allF, function($a, $b) {
            return $a['orden'] - $b['orden'];
        });
        $flyers = $allF;
    }
}
?>

        <section class="page-header">
            <div class="container">
                <h2>Servicios</h2>
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

        <section class="services-preview" style="background: #E6F4FA;">
            <div class="container">
                <div class="section-title">
                    <h3>Servicios Estrat&eacute;gicos y Tecnol&oacute;gicos</h3>
                </div>
                <div class="grid">
                    <div class="card animate-on-scroll">
                        <div class="card-icon">
                            <svg class="icon-asesoria" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h4>Asesor&iacute;a t&eacute;cnica y legal</h4>
                        <p>Acompa&ntilde;amiento integral a empresas de reciente creaci&oacute;n o en proceso de incubaci&oacute;n, orientado a la toma de decisiones t&eacute;cnicas, jur&iacute;dicas y organizacionales.</p>
                    </div>
                    <div class="card animate-on-scroll delay-1">
                        <div class="card-icon">
                            <svg class="icon-inversionistas" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V9Z"/><path d="m8 14 2 2 4-4"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        </div>
                        <h4>Acceso a redes de inversionistas</h4>
                        <p>Vinculaci&oacute;n con redes de inversionistas y actores estrat&eacute;gicos que facilitan el financiamiento y escalamiento de proyectos innovadores.</p>
                    </div>
                    <div class="card animate-on-scroll delay-2">
                        <div class="card-icon">
                            <svg class="icon-arrendamiento" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5Z"/><path d="M9 22V12h6v10"/></svg>
                        </div>
                        <h4>Arrendamiento de espacios tecnol&oacute;gicos</h4>
                        <p>Arrendamiento de espacios con servicios de conectividad, suministro el&eacute;ctrico y condiciones adecuadas para el desarrollo empresarial y tecnol&oacute;gico.</p>
                    </div>
                </div>
                <div class="tic-grid">
                    <h4 class="tic-title">Servicios vinculados a las Tecnolog&iacute;as de la Informaci&oacute;n y las Comunicaciones (TIC)</h4>
                    <div class="check-list cols-3">
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Desarrollo de sitios web.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Sistemas de informaci&oacute;n a la medida.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Implementaci&oacute;n de t&eacute;cnicas y modelos de Inteligencia Artificial.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Ecosistemas inteligentes.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Formaci&oacute;n y capacitaci&oacute;n en sistemas de gesti&oacute;n de la informaci&oacute;n.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Formaci&oacute;n y capacitaci&oacute;n en IA para empresarios.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Gesti&oacute;n de plataformas ORM y ERP.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>An&aacute;lisis cienciom&eacute;trico y estad&iacute;stico.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Dise&ntilde;o y gesti&oacute;n de bases de datos.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Sistemas de informaci&oacute;n geogr&aacute;fica (SIG).</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Computaci&oacute;n gr&aacute;fica.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Dise&ntilde;o de interfaces visuales Backend y Frontend.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Gesti&oacute;n de repositorios.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Web 3.0 y 4.0.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>An&aacute;lisis de datos.</p></div>
                        <div class="check-item"><div class="check-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A0E1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div><p>Ciencia de datos.</p></div>
                    </div>
                </div>
            </div>
        </section>

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

        <?php if (!empty($flyers)): ?>
        <section class="flyers-section">
            <div class="container">
                <div class="section-title">
                    <h3>Flyers</h3>
                    <p>Conoce nuestros eventos y actividades destacadas.</p>
                </div>
                <div class="flyers-carousel" id="flyersCarousel">
                    <button class="flyers-arrow flyers-prev" id="flyersPrev" aria-label="Anterior">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <div class="flyers-track" id="flyersTrack">
                        <?php foreach ($flyers as $f): ?>
                        <div class="flyers-slide">
                            <img src="<?php echo htmlspecialchars($f['imagen']); ?>" alt="<?php echo htmlspecialchars($f['titulo']); ?>" loading="lazy">
                            <div class="flyers-caption"><?php echo htmlspecialchars($f['titulo']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="flyers-arrow flyers-next" id="flyersNext" aria-label="Siguiente">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                    <div class="flyers-dots" id="flyersDots"></div>
                </div>
            </div>
        </section>
        <?php endif; ?>

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

        <?php if (!empty($flyers)): ?>
        <style>
            .flyers-section { padding: 60px 0; background: #fff; }
            .flyers-carousel { position: relative; max-width: 700px; margin: 0 auto; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
            .flyers-track { display: flex; transition: transform 0.4s ease; }
            .flyers-slide { min-width: 100%; position: relative; }
            .flyers-slide img { width: 100%; height: 420px; object-fit: cover; display: block; }
            .flyers-caption { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); color: #fff; padding: 20px 24px 16px; font-size: 1rem; font-weight: 600; }
            .flyers-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border-radius: 50%; border: none; background: rgba(255,255,255,0.9); color: #004966; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 10; transition: background 0.2s, transform 0.2s; }
            .flyers-arrow:hover { background: #00A0E1; color: #fff; }
            .flyers-prev { left: 12px; }
            .flyers-next { right: 12px; }
            .flyers-dots { display: flex; justify-content: center; gap: 8px; padding: 14px 0; background: #fff; }
            .flyers-dot { width: 10px; height: 10px; border-radius: 50%; border: 2px solid #00A0E1; background: transparent; cursor: pointer; padding: 0; transition: background 0.2s; }
            .flyers-dot.active { background: #00A0E1; }
            @media (max-width: 768px) {
                .flyers-slide img { height: 260px; }
                .flyers-arrow { width: 36px; height: 36px; }
                .flyers-prev { left: 6px; }
                .flyers-next { right: 6px; }
                .flyers-caption { font-size: 0.88rem; padding: 14px 16px 10px; }
            }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var track = document.getElementById('flyersTrack');
            var prevBtn = document.getElementById('flyersPrev');
            var nextBtn = document.getElementById('flyersNext');
            var dotsContainer = document.getElementById('flyersDots');
            if (!track) return;
            var slides = track.querySelectorAll('.flyers-slide');
            var total = slides.length;
            var current = 0;
            if (total === 0) return;
            for (var i = 0; i < total; i++) {
                var dot = document.createElement('button');
                dot.className = 'flyers-dot' + (i === 0 ? ' active' : '');
                dot.setAttribute('aria-label', 'Slide ' + (i + 1));
                dot.addEventListener('click', (function(idx) {
                    return function() { goTo(idx); };
                })(i));
                dotsContainer.appendChild(dot);
            }
            function goTo(idx) {
                current = idx;
                track.style.transform = 'translateX(-' + (current * 100) + '%)';
                var dots = dotsContainer.querySelectorAll('.flyers-dot');
                for (var j = 0; j < dots.length; j++) {
                    dots[j].classList.toggle('active', j === current);
                }
            }
            prevBtn.addEventListener('click', function() {
                goTo(current === 0 ? total - 1 : current - 1);
            });
            nextBtn.addEventListener('click', function() {
                goTo(current === total - 1 ? 0 : current + 1);
            });
        });
        </script>
        <?php endif; ?>

<?php include 'includes/footer.php'; ?>