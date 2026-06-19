<?php
$pageTitle = 'Noticias - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Noticias t&eacute;cnicas, entrevistas y art&iacute;culos del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara. Informaci&oacute;n actualizada sobre innovaci&oacute;n y tecnolog&iacute;a.';
$canonicalUrl = 'https://pctvc.cu/noticias.php';
include 'includes/header.php';
?>

        <section class="page-header">
            <div class="container">
                <h2 class="animate-fade-down">Noticias</h2>
                <p class="animate-fade-up">Mantente al día con las últimas novedades</p>
            </div>
        </section>

        <section class="news-section">
            <div class="container">
                <div class="search-bar animate-fade-down">
                    <input type="text" id="searchInput" placeholder="Buscar noticias, eventos..." data-container="allNewsContainer" data-type="">
                    <button type="button" aria-label="Buscar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </button>
                </div>
                <div id="allNewsContainer" class="grid">
                    <p class="empty">Cargando publicaciones...</p>
                </div>
            </div>
        </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        loadNews('allNewsContainer', { limit: 20, tipo: 'noticia' });
    });
    </script>

<?php include 'includes/footer.php'; ?>
