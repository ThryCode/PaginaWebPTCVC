<?php
$pageTitle = 'Galer&iacute;a - Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara';
$pageDescription = 'Galer&iacute;a de fotos y videos del Parque Cient&iacute;fico Tecnol&oacute;gico de Villa Clara. Im&aacute;genes de eventos, instalaciones y actividades.';
$canonicalUrl = 'https://pctvc.cu/galeria.php';
include 'includes/header.php';
?>

        <section class="page-header">
            <div class="container">
                <h2 class="animate-fade-down">Galería</h2>
                <p class="animate-fade-up">Los mejores momentos del PCTVC</p>
            </div>
        </section>

        <section class="intro-text-section">
            <div class="container">
                <p>Revive los mejores momentos del Parque Científico Tecnológico de Villa Clara a través de nuestra galería de imágenes. Aquí encontrarás fotos de eventos, instalaciones, proyectos y actividades realizadas en el parque.</p>
            </div>
        </section>

        <section class="gallery-section">
            <div class="container">
                <div id="galleryContainer" class="gallery-grid">
                    <p class="empty">Cargando galería...</p>
                </div>
            </div>
        </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        loadGallery('galleryContainer', { limit: 20 });
    });
    </script>

<?php include 'includes/footer.php'; ?>
