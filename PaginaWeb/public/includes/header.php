<?php
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/../api/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>pctvc</title>
    <link rel="icon" type="image/x-icon" href="assets/img/logo/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/img/logo/logo.png" alt="PCTVC" class="logo-img" onerror="this.style.display='none'">
                <h1>Parque Científico Tecnológico<span>de Villa Clara</span></h1>
            </a>
            <nav class="nav" id="nav">
                <ul>
                    <li><a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Inicio</a></li>
                    <li class="dropdown">
                        <a href="quienes-somos.php">Información</a>
                        <ul class="dropdown-menu">
                            <li><a href="quienes-somos.php">Quiénes somos</a></li>
                            <li><a href="proyectos.php">Proyectos</a></li>
                            <li><a href="eventos.php">Eventos</a></li>
                            <li><a href="noticias.php">Noticias</a></li>
                            <li><a href="galeria.php">Galería</a></li>
                        </ul>
                    </li>
                    <li><a href="servicios.php" class="<?php echo $currentPage === 'servicios.php' ? 'active' : ''; ?>">Servicios</a></li>
                    <li><a href="contacto.php" class="<?php echo $currentPage === 'contacto.php' ? 'active' : ''; ?>">Contacto</a></li>
                </ul>
            </nav>
            <button class="nav-toggle" id="navToggle" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <main>
