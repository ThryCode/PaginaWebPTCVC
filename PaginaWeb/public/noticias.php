<?php include 'includes/header.php'; ?>

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
                <div class="news-filters text-center mb-40">
                    <button class="btn filter-btn active" data-filter="all">Todos</button>
                    <button class="btn filter-btn" data-filter="noticia">Noticias</button>
                    <button class="btn filter-btn" data-filter="evento">Eventos</button>
                </div>
                <div id="allNewsContainer" class="grid">
                    <p class="empty">Cargando publicaciones...</p>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var currentFilter = 'all';

        function loadFilteredNews(filter) {
            var options = { limit: 20 };
            if (filter !== 'all') {
                options.tipo = filter;
            }
            loadNews('allNewsContainer', options);
        }

        var filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                filterBtns.forEach(function(b) { b.classList.remove('active'); });
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-filter');
                loadFilteredNews(currentFilter);
            });
        });

        loadFilteredNews('all');
    });
    </script>
