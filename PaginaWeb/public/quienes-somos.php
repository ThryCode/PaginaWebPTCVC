<?php
$pageTitle = 'Qui&eacute;nes Somos - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Conoce la historia, misi&oacute;n, visi&oacute;n y junta directiva del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara. Alianza, oportunidad y desarrollo.';
$canonicalUrl = 'https://pctvc.cu/quienes-somos.php';
include 'includes/header.php';
?>
        <script type="application/ld+json">
        <?php echo json_encode(array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Parque Científico Tecnológico de Villa Clara',
            'url' => 'https://pctvc.cu',
            'logo' => 'https://pctvc.cu/assets/img/logo/logo.png',
            'foundingDate' => '2021',
            'description' => 'Centro de innovación que promueve la colaboración entre gobierno, el sector del conocimiento y el sector empresarial para impulsar el desarrollo científico-tecnológico en Cuba.',
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => 'Carretera a Planta Mecánica, No. 39 B',
                'addressLocality' => 'Santa Clara',
                'addressRegion' => 'Villa Clara',
                'addressCountry' => 'CU'
            ),
            'telephone' => '+53-42281551',
            'email' => 'pctvillaclara@pctvc.cu'
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        </script>

        <section class="page-header">
            <div class="container">
                <h1>Quiénes Somos</h1>
                <p>Conoce más sobre el Parque Científico Tecnológico de Villa Clara</p>
            </div>
        </section>

        <section class="about-preview">
            <div class="container">
                <div class="about-content">
                    <h3>Nuestra Historia</h3>
                    <p class="about-intro">El Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara surge como respuesta a las necesidades de desarrollo industrial identificadas en la estrategia territorial de la provincia. La industria villaclare&ntilde;a aporta m&aacute;s del 50% de la producci&oacute;n mercantil del territorio y cuenta con f&aacute;bricas &uacute;nicas en el pa&iacute;s.</p>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <span class="timeline-year">Oct 2020</span>
                                <span class="timeline-dot"></span>
                            </div>
                            <div class="timeline-card">
                                <h4>Visita Gubernamental</h4>
                                <p>La Vice Primera Ministra In&eacute;s Mar&iacute;a Chapman destaca la conveniencia de crear el PCT en Villa Clara, dada su madurez industrial y potencial humano del territorio.</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <span class="timeline-year">2021</span>
                                <span class="timeline-dot"></span>
                            </div>
                            <div class="timeline-card">
                                <h4>Creaci&oacute;n del PCT</h4>
                                <p>MINDUS, UCLV y CITMA establecen un trabajo conjunto para analizar la viabilidad y se constituye el Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara.</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <span class="timeline-year">Hoy</span>
                                <span class="timeline-dot"></span>
                            </div>
                            <div class="timeline-card">
                                <h4>Innovaci&oacute;n y Desarrollo</h4>
                                <p>El PCT impulsa la modernizaci&oacute;n industrial, la incubaci&oacute;n de nuevas empresas y la articulaci&oacute;n entre el conocimiento, el gobierno y las entidades productivas.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <div class="about-image-card">
                        <div class="about-image-bg"></div>
                        <div class="about-image-content">
                            <svg aria-hidden="true" class="about-image-icon" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="8" y="28" width="64" height="44" rx="4" stroke="white" stroke-width="2.5" fill="none"/>
                                <path d="M28 28V18C28 15.8 29.8 14 32 14H48C50.2 14 52 15.8 52 18V28" stroke="white" stroke-width="2.5" fill="none"/>
                                <circle cx="40" cy="42" r="6" stroke="white" stroke-width="2.5" fill="none"/>
                                <path d="M40 48V54" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M30 58H50" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M34 62H46" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M20 34H24" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <path d="M56 34H60" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="about-image-label">Parque Científico Tecnológico de Villa Clara</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mission-vision-section">
            <div class="container">
                <div class="section-title">
                    <h3>Misión y Visión</h3>
                    <p>Los pilares que guían nuestro camino.</p>
                </div>
                <div class="mission-vision-grid">
                    <div class="mv-card animate-on-scroll">
                        <div class="mv-icon mission-icon">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        </div>
                        <h4>Nuestra Misión</h4>
                        <p>Contribuir al desarrollo industrial, participando y coadyuvando a la creaci&oacute;n, implantaci&oacute;n, funcionamiento, fortalecimiento, lanzamiento de nuevas entidades (productos nuevos o mejorados y empresas), adoptando sistemas de gesti&oacute;n integrada que incrementen el fondo de bienes exportables y la sustituci&oacute;n de importaciones en armon&iacute;a con el medio ambiente.</p>
                    </div>
                    <div class="mv-card animate-on-scroll delay-1">
                        <div class="mv-icon vision-icon">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h4>Nuestra Visión</h4>
                        <p>Ser un referente de ecosistema de innovaci&oacute;n y transferencia cient&iacute;fica en la rama de las industrias, con un m&iacute;nimo viable de industria 4.0, para promover la generaci&oacute;n y sostenibilidad de nuevas y exigentes empresas, proporcionando productos y servicios de alto valor agregado y un sistema de gesti&oacute;n alineado a la Pol&iacute;tica de Desarrollo Industrial.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="services-preview">
            <div class="container">
                <div class="section-title">
                    <h3>Nuestros Valores</h3>
                    <p>Principios que guían nuestro trabajo diario.</p>
                </div>
                <div class="grid">
                    <div class="card animate-on-scroll">
                        <div class="card-icon">
                            <svg aria-hidden="true" class="icon-alianza" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 25C8 25 6 23 6 20C6 17 8 15 10 15C12 15 14 17 14 19V21" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M32 25C32 25 34 23 34 20C34 17 32 15 30 15C28 15 26 17 26 19V21" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13 26L20 22L27 26" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 20L13 26L20 30L27 26L31 20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13 26C13 26 15 21 20 21C25 21 27 26 27 26" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                <path d="M5 29L9 25" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <path d="M35 29L31 25" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h4>Alianza</h4>
                        <p>Trabajamos de la mano con empresas, universidades y gobierno para crear sinergias que impulsen el desarrollo.</p>
                    </div>
                    <div class="card animate-on-scroll delay-1">
                        <div class="card-icon">
                            <svg aria-hidden="true" class="icon-oportunidad" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13 24C13 24 10 20 10 15C10 10 14 6 20 6C26 6 30 10 30 15C30 20 27 24 27 24H13Z" stroke="white" stroke-width="2" stroke-linejoin="round" fill="none"/>
                                <line x1="15" y1="28" x2="25" y2="28" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <line x1="17" y1="31" x2="23" y2="31" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <line x1="20" y1="24" x2="20" y2="28" stroke="white" stroke-width="2"/>
                            </svg>
                        </div>
                        <h4>Oportunidad</h4>
                        <p>Generamos espacios y oportunidades para el emprendimiento innovador basado en ciencia y tecnología.</p>
                    </div>
                    <div class="card animate-on-scroll delay-2">
                        <div class="card-icon">
                            <svg aria-hidden="true" class="icon-desarrollo" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 4L12 18L20 22L28 18L20 4Z" stroke="white" stroke-width="2" stroke-linejoin="round" fill="none"/>
                                <circle cx="20" cy="13" r="2" stroke="white" stroke-width="1.5" fill="none"/>
                                <path d="M12 18L8 24L16 20" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                <path d="M28 18L32 24L24 20" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                <path d="M18 22L16 30H24L22 22" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                        </div>
                        <h4>Desarrollo</h4>
                        <p>Fomentamos el crecimiento sostenible a través de la innovación y la transferencia de tecnología.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="colaboradores-section">
            <div class="container">
                <div class="section-title">
                    <h3>Instituciones Colaboradoras</h3>
                    <p>Entidades que forman parte de nuestro ecosistema de innovaci&oacute;n.</p>
                </div>
                <div class="colaboradores-grid">
                    <div class="colaborador-item animate-on-scroll">
                        <a href="https://www.tecnosime.cu/" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/tecnosime.png" alt="TECNOSIME" loading="lazy">
                        </a>
                    </div>
                    <div class="colaborador-item animate-on-scroll delay-1">
                        <a href="https://www.facebook.com/p/CEDAI-Empresa-de-Automatizaci%C3%B3n-Integral-100064048390294/" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/cedai.png" alt="CEDAI" loading="lazy">
                        </a>
                    </div>
                    <div class="colaborador-item animate-on-scroll delay-2">
                        <a href="https://www.plantamec.co.cu/" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/planta-mecanica.png" alt="Planta Mec&aacute;nica" loading="lazy">
                        </a>
                    </div>
                    <div class="colaborador-item animate-on-scroll delay-3">
                        <a href="https://www.facebook.com/empresaindustrialminerva/" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/minerva.png" alt="Minerva" loading="lazy">
                        </a>
                    </div>
                    <div class="colaborador-item animate-on-scroll delay-4">
                        <a href="http://www.ermpvc.co.cu/" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/ermp.png" alt="ERMP Villa Clara" loading="lazy">
                        </a>
                    </div>
                    <div class="colaborador-item animate-on-scroll delay-5">
                        <a href="https://sicte.uclv.cu/" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/sicte.png" alt="SICTE S.A." loading="lazy">
                        </a>
                    </div>
                    <div class="colaborador-item animate-on-scroll delay-6">
                        <a href="https://www.mindus.gob.cu/es" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/mi.png" alt="Ministerio de Industrias" loading="lazy">
                        </a>
                    </div>
                    <div class="colaborador-item animate-on-scroll delay-7">
                        <a href="https://www.sime.cu/" target="_blank" rel="noopener noreferrer">
                            <img src="/assets/img/colaboradores/gesime.png" alt="GESIME" loading="lazy">
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="membresia-section">
            <div class="container">
                <div class="section-title">
                    <h3>Membres&iacute;a</h3>
                    <p>Reconocimientos y afiliaciones internacionales.</p>
                </div>
                <div class="membresia-logos">
                    <a href="https://www.iasp.ws/" target="_blank" rel="noopener noreferrer">
                        <img src="/assets/img/colaboradores/iasp.png" alt="IASP" loading="lazy">
                    </a>
                    <div class="iasp-latam-logo">
                        <img src="/assets/img/colaboradores/iasp-latam.png" alt="IASP Latam" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {"@type":"Question","name":"¿Qué es el Parque Científico Tecnológico de Villa Clara?","acceptedAnswer":{"@type":"Answer","text":"El Parque Científico Tecnológico de Villa Clara (PCT Villa Clara) es una empresa cubana que apoya a otras empresas y emprendedores a desarrollar proyectos innovadores y a insertar sus productos en el mercado internacional."}},
                {"@type":"Question","name":"¿Se obtiene algún beneficio económico por participar en un proyecto?","acceptedAnswer":{"@type":"Answer","text":"Sí, durante los primeros cinco años de funcionamiento de tu proyecto o empresa incubada hay exención de impuestos sobre las utilidades y exención de aranceles de importación de partes, piezas y equipamiento necesario."}},
                {"@type":"Question","name":"Tengo una idea de proyecto. ¿Cómo puedo desarrollarla en el PCT Villa Clara?","acceptedAnswer":{"@type":"Answer","text":"Debes contactar al PCT para una reunión inicial, presentar tu propuesta formalmente al Consejo Técnico Asesor y, una vez aprobada, firmar un contrato para la fase de incubación."}},
                {"@type":"Question","name":"¿Puedo inscribirme y participar en proyectos si vivo fuera de Villa Clara?","acceptedAnswer":{"@type":"Answer","text":"Sí, absolutamente. No hay ningún requisito de residencia. Puedes inscribirte y participar en proyectos del PCT Villa Clara aunque vivas fuera de la provincia."}},
                {"@type":"Question","name":"¿Cuáles son los requisitos para pertenecer al PCT Villa Clara?","acceptedAnswer":{"@type":"Answer","text":"Los requisitos son flexibles. El principal requisito es tener una idea o proyecto con potencial innovador y la voluntad de desarrollarlo en un ecosistema de colaboración."}},
                {"@type":"Question","name":"¿Qué tipo de apoyo brinda el PCT Villa Clara a las empresas y emprendedores?","acceptedAnswer":{"@type":"Answer","text":"El PCT ofrece apoyo a la innovación y desarrollo de proyectos, apoyo legal y comercial, formación y networking, infraestructura, incentivos fiscales y apoyo a la internacionalización."}},
                {"@type":"Question","name":"¿Qué tipos de proyectos se desarrollan en el PCT Villa Clara?","acceptedAnswer":{"@type":"Answer","text":"Se desarrollan proyectos industriales, agroindustriales y de biotecnología, energías renovables, transformación digital, e innovación de nuevos productos con alto valor agregado."}},
                {"@type":"Question","name":"¿Quiénes pueden participar en los proyectos del PCT Villa Clara?","acceptedAnswer":{"@type":"Answer","text":"Personas naturales (emprendedores, estudiantes), empresas estatales y privadas, formas de gestión no estatal, y el sector del conocimiento como investigadores y universidades."}},
                {"@type":"Question","name":"¿El PCT Villa Clara ofrece servicios de incubación de empresas?","acceptedAnswer":{"@type":"Answer","text":"Sí, la incubación de empresas es una de sus actividades principales, proporcionando un entorno especializado, asesoría, incentivos fiscales, infraestructura y conexiones estratégicas."}},
                {"@type":"Question","name":"¿Cómo puedo contactar con el PCT Villa Clara para obtener más información?","acceptedAnswer":{"@type":"Answer","text":"Puedes contactar por correo electrónico a dalgys@pctvc.cu o por teléfono al 42281551."}}
            ]
        }
        </script>

        <section class="faq-section">
            <div class="container">
                <div class="section-title">
                    <h3>Preguntas Frecuentes</h3>
                    <p>Respuestas a las dudas m&aacute;s comunes sobre el PCTVC.</p>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <button class="faq-question">¿Qu&eacute; es el Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara?</button>
                        <div class="faq-answer">
                            <p>El Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara (PCT Villa Clara) es una empresa cubana que apoya a otras empresas y emprendedores a desarrollar proyectos innovadores y a insertar sus productos en el mercado internacional.</p>
                            <p>Es un espacio donde la ciencia, la tecnolog&iacute;a y los negocios se unen para crear nuevas oportunidades, generar divisas para el pa&iacute;s y fortalecer la industria cubana.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿Se obtiene alg&uacute;n beneficio econ&oacute;mico por participar en un proyecto?</button>
                        <div class="faq-answer">
                            <p>S&iacute;, participar en un proyecto en el PCT Villa Clara tiene importantes beneficios econ&oacute;micos.</p>
                            <p>Durante los primeros <strong>cinco (5) a&ntilde;os</strong> de funcionamiento de tu proyecto o empresa incubada las ventajas son:</p>
                            <ul>
                                <li>Exenci&oacute;n de impuestos sobre las utilidades.</li>
                                <li>Exenci&oacute;n de aranceles de importaci&oacute;n de partes, piezas y equipamiento necesario para tu proyecto.</li>
                            </ul>
                            <p>Esto te permite ahorrar dinero desde el inicio y hacer tu proyecto m&aacute;s competitivo, especialmente si planeas exportar. Adem&aacute;s, puedes enfocarte en innovar y crecer sin la presi&oacute;n de los costos tributarios iniciales.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">Tengo una idea de proyecto. ¿C&oacute;mo puedo desarrollarla en el PCT Villa Clara?</button>
                        <div class="faq-answer">
                            <p>Desarrollar tu idea de proyecto en el PCT Villa Clara es un proceso que te gu&iacute;a desde el concepto inicial hasta su lanzamiento.</p>
                            <p><strong>Pasos para desarrollar tu proyecto:</strong></p>
                            <ul>
                                <li><strong>Reuni&oacute;n inicial:</strong> Puedes contactar al PCT a trav&eacute;s de sus canales oficiales para presentar tu idea de forma general y explorar las posibilidades de colaboraci&oacute;n.</li>
                                <li><strong>Presenta tu propuesta formalmente:</strong> Deber&aacute;s presentar la informaci&oacute;n de tu proyecto, el cual ser&aacute; evaluado por el Consejo T&eacute;cnico Asesor (CTA), el &oacute;rgano consultivo que avala los proyectos del PCT Villa Clara.</li>
                                <li><strong>Firma del contrato y fase de incubaci&oacute;n:</strong> Una vez aprobado, se firma un contrato y tu proyecto entra en la fase de incubaci&oacute;n. Aqu&iacute; recibir&aacute;s acompa&ntilde;amiento personalizado para desarrollar tu modelo de negocio, acceder a talento y gestionar tu proyecto.</li>
                                <li><strong>Desarrollo y lanzamiento:</strong> Con el apoyo del PCT, tu idea se convierte en un producto o servicio listo para el mercado, aprovechando un ecosistema que fomenta la innovaci&oacute;n.</li>
                            </ul>
                            <p><strong>¿C&oacute;mo empezar?</strong> Puedes iniciar el proceso contactando al PCT a trav&eacute;s de los siguientes medios:</p>
                            <ul>
                                <li>Correo electr&oacute;nico: dalgys@pctvc.cu</li>
                                <li>Tel&eacute;fono: +42281551</li>
                                <li>Visita: Puedes acercarte a sus instalaciones en la zona industrial de Santa Clara.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿Puedo inscribirme y participar en proyectos si vivo fuera de Villa Clara?</button>
                        <div class="faq-answer">
                            <p>S&iacute;, absolutamente. Puedes inscribirte y participar en proyectos del PCT Villa Clara aunque vivas fuera de la provincia. <strong>No hay ning&uacute;n requisito de residencia.</strong></p>
                            <p>En cualquier lugar se puede hacer un proyecto y el PCT ofrece acompa&ntilde;amiento t&eacute;cnico hasta que se decida crear una empresa. El PCT est&aacute; abierto a personas de todo el pa&iacute;s, sin importar de qu&eacute; provincia vengas.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿Cu&aacute;les son los requisitos para pertenecer al PCT Villa Clara?</button>
                        <div class="faq-answer">
                            <p>Los requisitos para pertenecer al PCT Villa Clara son flexibles y se adaptan al tipo de participaci&oacute;n que busques. No existen barreras geogr&aacute;ficas ni requisitos excluyentes.</p>
                            <p><strong>¿Qui&eacute;nes pueden pertenecer?</strong></p>
                            <ul>
                                <li><strong>Empresas cubanas:</strong> tanto estatales como privadas.</li>
                                <li><strong>Empresas extranjeras:</strong> que deseen desarrollar proyectos en Cuba.</li>
                                <li><strong>Trabajadores por cuenta propia y personas naturales:</strong> incluyendo estudiantes y j&oacute;venes emprendedores.</li>
                            </ul>
                            <p>Si tienes una idea o proyecto con potencial innovador, eres bienvenido.</p>
                            <p><strong>El proceso de incorporaci&oacute;n:</strong></p>
                            <ul>
                                <li><strong>Contacto Inicial:</strong> Te acercas al PCT para presentar tu idea.</li>
                                <li><strong>Evaluaci&oacute;n:</strong> Tu propuesta es analizada por el Consejo T&eacute;cnico Asesor (CTA), que eval&uacute;a su viabilidad y potencial innovador.</li>
                                <li><strong>Incubaci&oacute;n y Desarrollo:</strong> Una vez aprobada, firmas un contrato y tu proyecto entra en una fase de incubaci&oacute;n, donde recibir&aacute;s todo el apoyo del parque.</li>
                            </ul>
                            <p>En esencia, el principal &ldquo;requisito&rdquo; es tener una idea o proyecto con potencial innovador y la voluntad de desarrollarlo en un ecosistema de colaboraci&oacute;n.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿Qu&eacute; tipo de apoyo brinda el PCT Villa Clara a las empresas y emprendedores?</button>
                        <div class="faq-answer">
                            <p>El PCT Villa Clara ofrece un sistema de apoyo integral que abarca desde el momento en que tienes una idea hasta que tu producto o servicio llega al mercado. Su objetivo es ser un &ldquo;puente&rdquo; entre el conocimiento, la industria y el gobierno para que puedas innovar y crecer.</p>
                            <p><strong>Principales tipos de apoyo:</strong></p>
                            <ul>
                                <li>Apoyo a la innovaci&oacute;n, al desarrollo de proyectos y empresas.</li>
                                <li>Apoyo legal, comercial y de gesti&oacute;n.</li>
                                <li>Formaci&oacute;n, talento y networking.</li>
                                <li>Infraestructura y espacios de trabajo.</li>
                                <li>Incentivos fiscales y financieros.</li>
                                <li>Apoyo a la internacionalizaci&oacute;n y el comercio exterior.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿Qu&eacute; tipos de proyectos se desarrollan en el PCT Villa Clara?</button>
                        <div class="faq-answer">
                            <p>En el PCT Villa Clara se desarrollan proyectos de innovaci&oacute;n con perfil industrial, que abarcan desde la creaci&oacute;n de nuevos productos hasta la mejora de los ya existentes.</p>
                            <p><strong>Categor&iacute;as principales:</strong></p>
                            <ul>
                                <li><strong>Industriales y de Manufactura:</strong> Nuevos procesos y productos para la industria cubana.</li>
                                <li><strong>Agroindustriales y de Biotecnolog&iacute;a:</strong> Soluciones para el sector agropecuario y la salud.</li>
                                <li><strong>Energ&iacute;as Renovables y Eficiencia Energ&eacute;tica:</strong> Proyectos que promueven el uso de fuentes limpias.</li>
                                <li><strong>Transformaci&oacute;n Digital y Comercio Electr&oacute;nico:</strong> Soluciones tecnol&oacute;gicas para la digitalizaci&oacute;n.</li>
                                <li><strong>Innovaci&oacute;n y Nuevos Productos:</strong> Desarrollo de productos con alto valor agregado.</li>
                            </ul>
                            <p>El objetivo es siempre impulsar la innovaci&oacute;n, la sustituci&oacute;n de importaciones y la generaci&oacute;n de exportaciones.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿Qui&eacute;nes pueden participar en los proyectos del PCT Villa Clara?</button>
                        <div class="faq-answer">
                            <p>En el PCT Villa Clara pueden participar personas y organizaciones de todo tipo:</p>
                            <ul>
                                <li><strong>Personas naturales:</strong> emprendedores, estudiantes, trabajadores por cuenta propia.</li>
                                <li><strong>Empresas y organizaciones:</strong> tanto estatales como privadas, nacionales o internacionales.</li>
                                <li><strong>Formas de gesti&oacute;n no estatal:</strong> incluye cooperativas, PDL, MIPYMES, etc.</li>
                                <li><strong>Sector del conocimiento:</strong> investigadores, universidades y centros de I+D.</li>
                            </ul>
                            <p>El parque est&aacute; abierto a todo tipo de actores que tengan una idea innovadora o un proyecto con potencial de desarrollo tecnol&oacute;gico e industrial.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿El PCT Villa Clara ofrece servicios de incubaci&oacute;n de empresas?</button>
                        <div class="faq-answer">
                            <p>S&iacute;, el PCT Villa Clara ofrece servicios de incubaci&oacute;n de empresas. Es, de hecho, una de sus actividades principales.</p>
                            <p><strong>¿En qu&eacute; consiste?</strong></p>
                            <p>La incubaci&oacute;n es un proceso de acompa&ntilde;amiento para ayudar a convertir una idea en un negocio real y sostenible. El parque fue creado con el objetivo de incubar nuevos productos y empresas que dinamicen la econom&iacute;a del pa&iacute;s.</p>
                            <p><strong>El PCT Villa Clara te proporciona:</strong></p>
                            <ul>
                                <li>Un entorno especializado.</li>
                                <li>Asesor&iacute;a y acompa&ntilde;amiento.</li>
                                <li>Incentivos fiscales.</li>
                                <li>Infraestructura.</li>
                                <li>Conexiones estrat&eacute;gicas.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">¿C&oacute;mo puedo contactar con el PCT Villa Clara para obtener m&aacute;s informaci&oacute;n?</button>
                        <div class="faq-answer">
                            <p>Si est&aacute;s interesado en incubar tu proyecto o en otro servicio del PCT, puedes contactarnos directamente.</p>
                            <ul>
                                <li>Correo electr&oacute;nico: dalgys@pctvc.cu</li>
                                <li>Tel&eacute;fono: 42281551</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="directivos-section">
            <div class="container">
                <div class="section-title">
                    <h3>Junta Directiva</h3>
                    <p>Conoce a nuestro equipo directivo.</p>
                </div>
                <div class="directivos-grid">
                    <div class="directivo-card animate-on-scroll">
                        <img src="/assets/img/junta/edelys.jpg" alt="MSc. Edelys Ada Saavedra Rodr&iacute;guez" class="directivo-foto" loading="lazy">
                        <h4>MSc. Edelys Ada Saavedra Rodr&iacute;guez</h4>
                        <p>Presidenta de la Junta General de Accionistas y de la Junta Directiva</p>
                    </div>
                    <div class="directivo-card animate-on-scroll delay-1">
                        <img src="/assets/img/junta/danay.jpeg" alt="MSc. Danay Alvarez Mesa" class="directivo-foto" loading="lazy">
                        <h4>MSc. Danay Alvarez Mesa</h4>
                        <p>Vicepresidenta Primera</p>
                    </div>
                    <div class="directivo-card animate-on-scroll delay-2">
                        <img src="/assets/img/junta/dalgys.jpeg" alt="Ing. Dalgys La Rosa Morales" class="directivo-foto" loading="lazy">
                        <h4>Ing. Dalgys La Rosa Morales</h4>
                        <p>Vicepresidenta Cient&iacute;fica</p>
                    </div>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>
