<?php
$pageTitle = 'Flyers - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Flyers de eventos y actividades destacadas del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara.';
$canonicalUrl = 'https://pctvc.cu/flyers.php';
include 'includes/header.php';

$flyersFile = __DIR__ . '/data/flyers.json';
$flyers = array();

if (file_exists($flyersFile)) {
    $raw = file_get_contents($flyersFile);
    $all = json_decode($raw, true);
    if (is_array($all)) {
        usort($all, function($a, $b) {
            return $a['orden'] - $b['orden'];
        });
        $flyers = $all;
    }
}
?>

        <section class="page-header">
            <div class="container">
                <h1>Flyers</h1>
                <p>Conoce nuestros eventos y actividades destacadas.</p>
            </div>
        </section>

        <?php if (!empty($flyers)): ?>
        <section class="flyers-section">
            <div class="container">
                <div class="flyers-carousel-wrapper">
                    <button class="flyers-arrow flyers-prev" id="flyersPrev" aria-label="Anterior">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <div class="flyers-carousel" id="flyersCarousel">
                        <div class="flyers-track" id="flyersTrack">
                            <?php foreach ($flyers as $f): ?>
                            <div class="flyers-slide">
                                <img src="<?php echo htmlspecialchars($f['imagen']); ?>" alt="<?php echo htmlspecialchars($f['titulo']); ?>" loading="lazy">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="flyers-arrow flyers-next" id="flyersNext" aria-label="Siguiente">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>
                <div class="flyers-dots" id="flyersDots"></div>
            </div>
        </section>
        <?php else: ?>
        <section class="empty-state">
            <div class="container">
                <p>No hay flyers disponibles en este momento.</p>
            </div>
        </section>
        <?php endif; ?>

        <style>
            .flyers-section { padding: 60px 0; background: #fff; }
            .flyers-carousel-wrapper { display: flex; align-items: center; gap: 16px; max-width: 700px; margin: 0 auto; }
            .flyers-carousel { flex: 1; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
            .flyers-track { display: flex; transition: transform 0.4s ease; }
            .flyers-slide { min-width: 100%; position: relative; display: flex; align-items: center; justify-content: center; background: #f5f5f5; }
            .flyers-slide img { max-width: 100%; max-height: 500px; height: auto; display: block; object-fit: contain; }
            .flyers-arrow { flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; border: none; background: rgba(255,255,255,0.9); color: #004966; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: background 0.2s, transform 0.2s; }
            .flyers-arrow:hover { background: #00A0E1; color: #fff; }
            .flyers-dots { display: flex; justify-content: center; gap: 8px; padding: 14px 0; }
            .flyers-dot { width: 10px; height: 10px; border-radius: 50%; border: 2px solid #00A0E1; background: transparent; cursor: pointer; padding: 0; transition: background 0.2s; }
            .flyers-dot.active { background: #00A0E1; }
            .empty-state { padding: 80px 0; text-align: center; color: #666; font-size: 1.1rem; }
            @media (max-width: 768px) {
                .flyers-arrow { width: 36px; height: 36px; }
                .flyers-carousel-wrapper { gap: 8px; }
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

<?php include 'includes/footer.php'; ?>