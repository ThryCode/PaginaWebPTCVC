<?php
$countersFile = __DIR__ . '/data/counters.json';
$counters = array();
if (file_exists($countersFile)) {
    $json = file_get_contents($countersFile);
    $counters = json_decode($json, true);
    if (!is_array($counters)) $counters = array();
    usort($counters, function($a, $b) {
        return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
    });
}
include 'includes/header.php';
?>

        <section class="carousel" id="carousel">
            <div class="carousel-inner">
                <div class="carousel-slide active">
                    <img src="assets/img/sliders/slider-01.jpg" alt="PCTVC 1">
                </div>
                <div class="carousel-slide">
                    <img src="assets/img/sliders/slider-02.jpg" alt="PCTVC 2">
                </div>
                <div class="carousel-slide">
                    <img src="assets/img/sliders/slider-03.jpg" alt="PCTVC 3">
                </div>
                <div class="carousel-slide">
                    <img src="assets/img/sliders/slider-04.jpg" alt="PCTVC 4">
                </div>
                <div class="carousel-slide">
                    <img src="assets/img/sliders/slider-05.jpg" alt="PCTVC 5">
                </div>
                <div class="carousel-slide">
                    <img src="assets/img/sliders/slider-06.jpg" alt="PCTVC 6">
                </div>
                <div class="carousel-slide">
                    <img src="assets/img/sliders/slider-07.jpg" alt="PCTVC 7">
                </div>
                <div class="carousel-slide">
                    <img src="assets/img/sliders/slider-08.jpeg" alt="PCTVC 8">
                </div>
            </div>
            <div class="carousel-overlay">
                <div class="carousel-content">
                    <h2>Alianza, Oportunidad y Desarrollo</h2>
                </div>
            </div>
            <button class="carousel-arrow prev" id="carouselPrev">&#10094;</button>
            <button class="carousel-arrow next" id="carouselNext">&#10095;</button>
            <div class="carousel-dots" id="carouselDots"></div>
        </section>

        <section class="intro-section">
            <div class="container">
                <div class="intro-content animate-fade-left">
                    <h3>Parque Cientifico Tecnologico de Villa Clara</h3>
                    <p>El Parque Cientifico Tecnologico de Villa Clara es un centro de innovacion que promueve la colaboracion entre gobierno, el sector del conocimiento y el sector empresarial para impulsar el desarrollo cientifico-tecnologico en Cuba. Ofrece un entorno dinamico para crear y hacer crecer empresas tecnologicas, facilitando la transferencia de conocimientos y tecnologias.</p>
                    <p>Nuestro compromiso es ser el puente entre la academia, la industria y el gobierno, facilitando la transferencia de conocimiento y tecnologia que genere impacto positivo en la sociedad.</p>
                </div>
                <div class="intro-image animate-fade-right">
                    <img src="assets/img/general/feriaC4.jpg" alt="PCTVC - Feria Cientifica">
                </div>
            </div>
        </section>

        <section class="sectors-section">
            <div class="sectors-container">
                <div class="sectors-grid">
                    <div class="sector-card animate-scale-in">
                        <div class="sector-icon"><svg width="40" height="40" fill="none" stroke="#2563eb" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg></div>
                        <h4>Sector Empresarial</h4>
                    </div>
                    <div class="sector-card animate-scale-in delay-1">
                        <div class="sector-icon"><svg width="40" height="40" fill="none" stroke="#8b5cf6" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 13v4c0 1.1 3.134 2 7 2s7-.9 7-2v-4"/></svg></div>
                        <h4>Sector del Conocimiento</h4>
                    </div>
                    <div class="sector-card animate-scale-in delay-2">
                        <div class="sector-icon"><svg width="40" height="40" fill="none" stroke="#f97316" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z"/></svg></div>
                        <h4>Sector Gubernamental</h4>
                    </div>
                    <div class="sector-card animate-scale-in delay-3">
                        <div class="sector-icon"><svg width="40" height="40" fill="none" stroke="#eab308" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg></div>
                        <h4>Emprendedores</h4>
                    </div>
                </div>
                <div class="sectors-text animate-fade-right">
                    <h2>Un modelo de colaboracion para un futuro sostenible</h2>
                    <p>El Parque Cientifico Tecnologico funciona como un espacio de articulacion donde confluyen el talento, el conocimiento, la produccion y los servicios y la gestion gubernamental.</p>
                    <p>El sector del conocimiento aporta investigacion, formacion y capital humano, que en vinculo con las demandas y recursos empresariales y de la sociedad, se alinean e integran con las estrategias de desarrollo gubernamentales.</p>
                </div>
            </div>
        </section>

        <section class="solutions-section">
            <div class="parallax-bg"></div>
            <div class="solutions-content">
                <h3 class="solutions-title animate-fade-down">Soluciones Integrales</h3>
                <div class="solutions-grid">
                    <div class="solution-card-wrapper animate-fade-up">
                        <div class="solution-card-inner">
                            <div class="solution-icon-wrap">
                                <svg viewBox="0 0 64 64" class="solution-svg">
                                    <circle cx="32" cy="32" r="14" fill="#fbbf24" stroke="#f59e0b" stroke-width="2"/>
                                    <text x="32" y="37" text-anchor="middle" fill="#92400e" font-size="16" font-weight="bold" font-family="Arial">$</text>
                                </svg>
                            </div>
                            <h4 class="solution-title">Financiacion</h4>
                            <p class="solution-desc">Fondos e inversion</p>
                            <div class="solution-bar bar-amber"></div>
                        </div>
                    </div>
                    <div class="solution-card-wrapper animate-fade-up delay-1">
                        <div class="solution-card-inner">
                            <div class="solution-icon-wrap">
                                <svg viewBox="0 0 64 64" class="solution-svg">
                                    <path d="M32 18 L35 22 L40 22 L42 26 L46 28 L46 32 L46 36 L42 38 L40 42 L35 42 L32 46 L29 42 L24 42 L22 38 L18 36 L18 32 L18 28 L22 26 L24 22 L29 22 Z" fill="#10b981" stroke="#059669" stroke-width="1"/>
                                    <circle cx="32" cy="32" r="6" fill="#065f46"/>
                                </svg>
                            </div>
                            <h4 class="solution-title">Usos</h4>
                            <p class="solution-desc">Equipos y materiales</p>
                            <div class="solution-bar bar-emerald"></div>
                        </div>
                    </div>
                    <div class="solution-card-wrapper animate-fade-up delay-2">
                        <div class="solution-card-inner">
                            <div class="solution-icon-wrap">
                                <svg viewBox="0 0 64 64" class="solution-svg">
                                    <rect x="20" y="20" width="24" height="30" rx="2" fill="#475569" stroke="#334155" stroke-width="1"/>
                                    <rect x="24" y="24" width="6" height="5" rx="1" fill="#94a3b8"/>
                                    <rect x="34" y="24" width="6" height="5" rx="1" fill="#94a3b8"/>
                                    <rect x="24" y="32" width="6" height="5" rx="1" fill="#94a3b8"/>
                                    <rect x="34" y="32" width="6" height="5" rx="1" fill="#94a3b8"/>
                                </svg>
                            </div>
                            <h4 class="solution-title">Espacios</h4>
                            <p class="solution-desc">Oficinas compartidas</p>
                            <div class="solution-bar bar-slate"></div>
                        </div>
                    </div>
                    <div class="solution-card-wrapper animate-fade-up delay-3">
                        <div class="solution-card-inner">
                            <div class="solution-icon-wrap">
                                <svg viewBox="0 0 64 64" class="solution-svg">
                                    <path d="M32 8 L50 16 L50 32 C50 44 42 52 32 56 C22 52 14 44 14 32 L14 16 Z" fill="#6366f1" stroke="#4f46e5" stroke-width="2"/>
                                    <path d="M24 32 L30 38 L42 26" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h4 class="solution-title">Riesgos</h4>
                            <p class="solution-desc">Inversiones seguras</p>
                            <div class="solution-bar bar-indigo"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ventajas-section">
            <div class="container">
                <h2 class="ventajas-title animate-fade-down">Principales ventajas competitivas del Parque</h2>
                <div class="ventajas-grid">
                    <div class="ventaja-card animate-fade-left">
                        <div class="ventaja-icon bg-blue">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <h3>Incentivos fiscales sostenidos</h3>
                        <p>Las empresas incubadas disfrutan de una exencion total de tributos sobre utilidades durante los primeros cinco anos de funcionamiento.</p>
                    </div>
                    <div class="ventaja-card animate-fade-right delay-1">
                        <div class="ventaja-icon bg-emerald">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        </div>
                        <h3>Facilitacion de importaciones</h3>
                        <p>Se otorga la exencion del pago de aranceles por la importacion de partes, piezas y equipamiento para la produccion.</p>
                    </div>
                    <div class="ventaja-card animate-fade-left delay-2">
                        <div class="ventaja-icon bg-purple">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                        </div>
                        <h3>Ecosistema de ciencia e innovacion</h3>
                        <p>El Parque ofrece un entorno especializado para la investigacion, el desarrollo tecnologico y la innovacion.</p>
                    </div>
                    <div class="ventaja-card animate-fade-right delay-3">
                        <div class="ventaja-icon bg-orange">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        </div>
                        <h3>Ubicacion estrategica y conectividad</h3>
                        <p>Ubicado en la zona industrial de Santa Clara, en el centro del pais, con acceso a multiples vias de comunicacion.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="news-section" id="noticias">
            <div class="container">
                <div class="news-header animate-fade-down">
                    <div>
                        <p class="news-subtitle">Mantente Informado</p>
                        <h2>Articulos tecnicos, entrevistas y noticias nacionales e internacionales</h2>
                    </div>
                    <a href="noticias.php" class="btn btn-primary">Ver Todas</a>
                </div>
                <div id="homeNewsContainer" class="news-grid">
                    <p class="empty">Cargando noticias...</p>
                </div>
            </div>
        </section>

        <section class="events-section" id="eventos">
            <div class="container">
                <div class="events-header animate-fade-down">
                    <p class="events-subtitle">Agenda</p>
                    <h2>Eventos</h2>
                    <p>Participa en nuestras actividades y conecta con la comunidad</p>
                </div>
                <div id="homeEventsContainer" class="events-grid">
                    <p class="empty">Cargando eventos...</p>
                </div>
                <div class="events-cta">
                    <a href="eventos.php" class="btn btn-outline-green">Ver Calendario Completo</a>
                </div>
            </div>
        </section>

        <?php if (!empty($counters)): ?>
        <section class="counters-section">
            <div class="container">
                <?php
                $total = count($counters);
                $cols = $total <= 4 ? $total : 4;
                $lastRow = $total % 4;
                $lastClass = ($total > 4 && $lastRow > 0 && $lastRow < 4) ? ' last-' . $lastRow : '';
                ?>
                <div class="counters-grid<?php echo $lastClass; ?>" style="--cols:<?php echo $cols; ?>">
                    <?php foreach ($counters as $idx => $c): ?>
                    <div class="counter-item animate-scale-in<?php echo $idx > 0 ? ' delay-' . min($idx, 3) : ''; ?>">
                        <div class="counter-number" data-target="<?php echo intval($c['numero']); ?>">0</div>
                        <div class="counter-label"><?php echo htmlspecialchars($c['label']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="contact-section" id="contacto">
            <div class="container">
                <div class="contact-grid">
                    <div class="contact-info animate-fade-left">
                        <h2>Ponte en Contacto</h2>
                        <p>No dudes en comunicarte. Simplemente complete el formulario de contacto y nos aseguraremos de responderle lo mas rapido posible.</p>
                        <div class="contact-items">
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                </div>
                                <div>
                                    <h4>Visita nuestra oficina</h4>
                                    <p>Carretera a Planta Mecanica, No. 39 B</p>
                                    <p>Santa Clara, Villa Clara, Cuba</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                </div>
                                <div>
                                    <h4>Correos</h4>
                                    <p>pctvillaclara@pctvc.cu</p>
                                    <p>clientes@pctvc.cu</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                </div>
                                <div>
                                    <h4>Telefono Fijo</h4>
                                    <p>+53 42281551</p>
                                    <p>Extensiones: 101-107</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-item-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </div>
                                <div>
                                    <h4>Horario</h4>
                                    <p>Lunes - Jueves: 8:00 AM - 5:00 PM</p>
                                    <p>Viernes: 8:00 AM - 4:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contact-form-wrap animate-fade-right">
                        <form class="contact-form" id="contactForm" onsubmit="return handleSubmit(event)">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" id="nombre" name="nombre" required placeholder="Nombre">
                                </div>
                                <div class="form-group">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" id="apellidos" name="apellidos" placeholder="Apellidos">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="correo">Correo</label>
                                <input type="email" id="correo" name="correo" required placeholder="Direccion de Correo">
                            </div>
                            <div class="form-group">
                                <label for="telefono">Telefono</label>
                                <input type="tel" id="telefono" name="telefono" placeholder="+53 555 12345">
                            </div>
                            <div class="form-group">
                                <label for="asunto">Asunto</label>
                                <input type="text" id="asunto" name="asunto" placeholder="Asunto">
                            </div>
                            <div class="form-group">
                                <label for="mensaje">Mensaje</label>
                                <textarea id="mensaje" name="mensaje" rows="5" required placeholder="Mensaje"></textarea>
                            </div>
                            <div id="formMessage" class="form-message"></div>
                            <button type="submit" class="btn btn-primary" style="width:100%">Enviar Mensaje</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        loadNews('homeNewsContainer', { limit: 3, tipo: 'noticia' });
        loadEvents('homeEventsContainer', { limit: 3 });
    });
    </script>
