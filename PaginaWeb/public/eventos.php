<?php include 'includes/header.php'; ?>

        <section class="page-header">
            <div class="container">
                <h2 class="animate-fade-down">Eventos</h2>
                <p class="animate-fade-up">PrÃ³ximos eventos y actividades</p>
            </div>
        </section>

        <section class="events-section" style="background: #E6F4FA;">
            <div class="container">
                <div class="events-header">
                    <div class="events-subtitle">Calendario de Eventos</div>
                    <h2>PrÃ³ximos Eventos</h2>
                </div>
                <div id="calendarContainer" class="calendar-wrapper"></div>
                <div class="search-bar" style="margin-bottom: 30px;">
                    <input type="text" id="eventSearchInput" placeholder="Buscar eventos..." data-container="eventsContainer">
                    <button type="button" aria-label="Buscar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </button>
                </div>
                <div id="eventsContainer" class="events-grid">
                    <p class="empty">Cargando eventos...</p>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <p>Â¿Tienes un evento que quieras realizar con nosotros?</p>
                <a href="contacto.php" class="btn btn-primary">ContÃ¡ctanos</a>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        renderCalendar('calendarContainer');
        loadEvents('eventsContainer', { limit: 10 });
    });
    </script>
