<?php
$pageTitle = 'Eventos - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Pr&oacute;ximos eventos, talleres y conferencias del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara. Calendario de actividades de innovaci&oacute;n y tecnolog&iacute;a.';
$canonicalUrl = 'https://pctvc.cu/eventos.php';
include 'includes/header.php';
?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "Eventos - Parque Científico Tecnológico de Villa Clara",
    "description": "<?php echo $pageDescription; ?>",
    "url": "<?php echo $canonicalUrl; ?>",
    "mainEntity": {
        "@type": "ItemList",
        "name": "Próximos eventos",
        "itemListElement": []
    }
}
</script>
        <section class="page-header">
            <div class="container">
                <h1 class="animate-fade-down">Eventos</h1>
                <p class="animate-fade-up">Próximos eventos y actividades</p>
            </div>
        </section>

        <section class="events-section" style="background: #E6F4FA;">
            <div class="container">
                <div class="events-header">
                    <div class="events-subtitle">Calendario de Eventos</div>
                </div>
                <div id="calendarContainer" class="calendar-wrapper"></div>
                <div class="search-bar" style="margin-bottom: 30px;">
                    <label for="eventSearchInput" class="sr-only">Buscar eventos</label>
                    <input type="text" id="eventSearchInput" placeholder="Buscar eventos..." data-container="eventsContainer">
                    <button type="button" aria-label="Buscar">
                        <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </button>
                </div>
                <div id="eventsContainer" class="events-grid">
                    <p class="empty">Cargando eventos...</p>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <p>¿Tienes un evento que quieras realizar con nosotros?</p>
                <a href="contacto.php" class="btn btn-primary">Contáctanos</a>
            </div>
        </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        renderCalendar('calendarContainer');
        loadEvents('eventsContainer', { limit: 10 });
    });
    </script>

<?php include 'includes/footer.php'; ?>
