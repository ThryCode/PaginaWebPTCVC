<?php
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/../api/config.php';

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), camera=(), microphone=(), midi=(), sync-xhr=()");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200&&!document.querySelector('meta[name="viewport"][content*="width=1200"]')){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=2';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title><?php echo isset($pageTitle) ? html_entity_decode($pageTitle, ENT_QUOTES, 'UTF-8') : 'Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara'; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars(html_entity_decode($pageDescription, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') : ''; ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo isset($canonicalUrl) ? htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <script type="application/ld+json">
    <?php
    $pageNames = array(
        'index.php' => 'Inicio',
        'quienes-somos.php' => 'Qui&eacute;nes Somos',
        'servicios.php' => 'Servicios',
        'proyectos.php' => 'Proyectos',
        'eventos.php' => 'Eventos',
        'noticias.php' => 'Noticias',
        'noticia.php' => 'Noticia',
        'galeria.php' => 'Galer&iacute;a',
        'contacto.php' => 'Contacto',
        'producciones-cooperadas.php' => 'Producciones Cooperadas',
        'incubacion-empresas.php' => 'Incubaci&oacute;n de Empresas',
        'flyers.php' => 'Flyers'
    );
    $breadcrumbName = isset($pageNames[$currentPage]) ? $pageNames[$currentPage] : $pageTitle;
    $breadcrumbUrl = isset($canonicalUrl) ? $canonicalUrl : ('https://pctvc.cu/' . $currentPage);
    ?>
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type":"ListItem","position":1,"name":"Inicio","item":"https://pctvc.cu/"},
            {"@type":"ListItem","position":2,"name":"<?php echo htmlspecialchars(html_entity_decode($breadcrumbName, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>","item":"<?php echo htmlspecialchars($breadcrumbUrl, ENT_QUOTES, 'UTF-8'); ?>"}
        ]
    }
    </script>
    <?php if (isset($pageTitle)): ?>
    <meta property="og:title" content="<?php echo htmlspecialchars(html_entity_decode($pageTitle, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <?php if (isset($pageDescription)): ?>
    <meta property="og:description" content="<?php echo htmlspecialchars(html_entity_decode($pageDescription, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <?php
    $defaultOgImage = 'https://pctvc.cu/assets/img/logo/logo.png';
    $ogImageFinal = isset($ogImage) ? $ogImage : $defaultOgImage;
    ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImageFinal, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImageFinal, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if (isset($canonicalUrl)): ?>
    <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <meta property="og:type" content="<?php echo isset($ogType) ? $ogType : 'website'; ?>">
    <meta property="og:locale" content="es_CU">
    <meta name="twitter:card" content="summary_large_image">
    <?php if (isset($pageTitle)): ?>
    <meta name="twitter:title" content="<?php echo htmlspecialchars(html_entity_decode($pageTitle, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <?php if (isset($pageDescription)): ?>
    <meta name="twitter:description" content="<?php echo htmlspecialchars(html_entity_decode($pageDescription, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <link rel="icon" type="image/x-icon" href="assets/img/logo/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="preload" href="css/style.css" as="style">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
    <header class="header" id="header">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/img/logo/logo.png" alt="Logo Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara" width="50" height="50" loading="lazy" class="logo-img" id="headerLogo">
                <span class="logo-text">Parque Cient&iacute;fico Tecnol&oacute;gico<span>de Villa Clara</span></span>
            </a>
            <nav class="nav" id="nav">
                <ul>
                    <li><a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>"<?php echo $currentPage === 'index.php' ? ' aria-current="page"' : ''; ?>>Inicio</a></li>
                    <?php
                    $infoPages = array('quienes-somos.php','proyectos.php','eventos.php','noticias.php','galeria.php');
                    $servPages = array('servicios.php','producciones-cooperadas.php','incubacion-empresas.php','flyers.php');
                    ?>
                    <li class="dropdown">
                        <a href="quienes-somos.php"<?php echo in_array($currentPage, $infoPages) ? ' aria-current="page"' : ''; ?>>Informaci&oacute;n</a>
                        <ul class="dropdown-menu">
                            <li><a href="quienes-somos.php">Qui&eacute;nes somos</a></li>
                            <li><a href="proyectos.php">Proyectos</a></li>
                            <li><a href="eventos.php">Eventos</a></li>
                            <li><a href="noticias.php">Noticias</a></li>
                            <li><a href="galeria.php">Galer&iacute;a</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="servicios.php"<?php echo in_array($currentPage, $servPages) ? ' aria-current="page"' : ''; ?>>Servicios</a>
                        <ul class="dropdown-menu">
                            <li><a href="servicios.php">Todos los Servicios</a></li>
                            <li><a href="producciones-cooperadas.php">Producciones Cooperadas</a></li>
                            <li><a href="incubacion-empresas.php">Incubaci&oacute;n de Empresas</a></li>
                            <li><a href="flyers.php">Flyers</a></li>
                        </ul>
                    </li>
                    <li><a href="contacto.php" class="<?php echo $currentPage === 'contacto.php' ? 'active' : ''; ?>"<?php echo $currentPage === 'contacto.php' ? ' aria-current="page"' : ''; ?>>Contacto</a></li>
                </ul>
            </nav>
            <button class="nav-toggle" id="navToggle" aria-label="Abrir men&uacute;">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <main id="main-content">
