// ============================================
// NAVBAR - Scroll effect + Hamburger menu
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    var header = document.getElementById('header');
    var navToggle = document.getElementById('navToggle');
    var nav = document.getElementById('nav');

    // Navbar: transparente sobre carrusel, blanco al hacer scroll
    function handleScroll() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', handleScroll);
    handleScroll();

    // Hamburger menu toggle
    if (navToggle && nav) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            nav.classList.toggle('active');
        });

        // Mobile dropdown toggle
        var dropdowns = nav.querySelectorAll('.dropdown');
        dropdowns.forEach(function(dropdown) {
            var link = dropdown.querySelector('a');
            var menu = dropdown.querySelector('.dropdown-menu');
            if (link && menu) {
                link.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        menu.classList.toggle('open');
                    }
                });
            }
        });

        // Close menu when clicking a link
        var navLinks = nav.querySelectorAll('a');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    navToggle.classList.remove('active');
                    nav.classList.remove('active');
                }
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!nav.contains(e.target) && !navToggle.contains(e.target)) {
                navToggle.classList.remove('active');
                nav.classList.remove('active');
            }
        });
    }
});

// ============================================
// CARRUSEL DE IMAGENES
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    var carousel = document.getElementById('carousel');
    if (!carousel) return;

    var slides = carousel.querySelectorAll('.carousel-slide');
    var dotsContainer = document.getElementById('carouselDots');
    var prevBtn = document.getElementById('carouselPrev');
    var nextBtn = document.getElementById('carouselNext');
    var currentSlide = 0;
    var autoPlayInterval = null;
    var autoPlayDelay = 4000;

    // Create dots
    slides.forEach(function(_, index) {
        var dot = document.createElement('div');
        dot.className = 'carousel-dot' + (index === 0 ? ' active' : '');
        dot.addEventListener('click', function() {
            goToSlide(index);
            resetAutoPlay();
        });
        dotsContainer.appendChild(dot);
    });

    var dots = dotsContainer.querySelectorAll('.carousel-dot');

    function goToSlide(index) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        currentSlide = index;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        var next = (currentSlide + 1) % slides.length;
        goToSlide(next);
    }

    function prevSlide() {
        var prev = (currentSlide - 1 + slides.length) % slides.length;
        goToSlide(prev);
    }

    function startAutoPlay() {
        autoPlayInterval = setInterval(nextSlide, autoPlayDelay);
    }

    function stopAutoPlay() {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
            autoPlayInterval = null;
        }
    }

    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }

    // Arrow controls
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            nextSlide();
            resetAutoPlay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            prevSlide();
            resetAutoPlay();
        });
    }

    // Pause on hover
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);

    // Touch support
    var touchStartX = 0;
    var touchEndX = 0;

    carousel.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
    }, { passive: true });

    carousel.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        var diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
        startAutoPlay();
    }, { passive: true });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            resetAutoPlay();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            resetAutoPlay();
        }
    });

    // Start auto-play
    startAutoPlay();
});

// ============================================
// FORMULARIO DE CONTACTO
// ============================================
function validateForm(form) {
    var errors = [];
    var nombre = form.nombre.value.trim();
    var correo = form.correo.value.trim();
    var asunto = form.asunto.value.trim();
    var mensaje = form.mensaje.value.trim();

    if (nombre === '') {
        errors.push('El nombre es obligatorio.');
    } else if (nombre.length < 2) {
        errors.push('El nombre debe tener al menos 2 caracteres.');
    }

    if (correo === '') {
        errors.push('El correo es obligatorio.');
    } else if (!isValidEmail(correo)) {
        errors.push('El correo no es valido.');
    }

    if (asunto === '') {
        errors.push('El asunto es obligatorio.');
    }

    if (mensaje === '') {
        errors.push('El mensaje es obligatorio.');
    } else if (mensaje.length < 10) {
        errors.push('El mensaje debe tener al menos 10 caracteres.');
    }

    return errors;
}

function isValidEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function handleSubmit(event) {
    event.preventDefault();

    var form = event.target;
    var messageDiv = document.getElementById('formMessage');
    var submitBtn = form.querySelector('button[type="submit"]');

    var errors = validateForm(form);

    if (errors.length > 0) {
        messageDiv.className = 'form-message error';
        messageDiv.innerHTML = '<strong>Por favor corrige los siguientes errores:</strong><ul>' +
            errors.map(function(e) { return '<li>' + e + '</li>'; }).join('') + '</ul>';
        messageDiv.style.display = 'block';
        return false;
    }

    var formData = new FormData(form);

    submitBtn.disabled = true;
    submitBtn.textContent = 'Enviando...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/contact.php', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Mensaje';

            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        messageDiv.className = 'form-message success';
                        messageDiv.textContent = response.message || 'Mensaje enviado correctamente. Nos pondremos en contacto pronto.';
                        form.reset();
                    } else {
                        messageDiv.className = 'form-message error';
                        messageDiv.textContent = response.message || 'Error al enviar el mensaje.';
                    }
                } catch (e) {
                    messageDiv.className = 'form-message error';
                    messageDiv.textContent = 'Error al procesar la respuesta del servidor.';
                }
            } else {
                messageDiv.className = 'form-message error';
                messageDiv.textContent = 'Error de conexion. Intente nuevamente.';
            }
            messageDiv.style.display = 'block';

            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    xhr.onerror = function() {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Enviar Mensaje';
        messageDiv.className = 'form-message error';
        messageDiv.textContent = 'Error de conexion. Verifique su acceso a internet.';
        messageDiv.style.display = 'block';
    };

    xhr.send(formData);
    return false;
}

// ============================================
// CARGAR NOTICIAS DESDE LA API
// ============================================
function loadNews(containerId, options) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var limit = (options && options.limit) || 6;
    var tipo = (options && options.tipo) || '';
    var baseUrl = 'api/news.php';

    var url = baseUrl + '?limit=' + limit;
    if (tipo) url += '&tipo=' + tipo;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success && response.data.length > 0) {
                    var html = '';
                    response.data.forEach(function(item) {
                        var fecha = new Date(item.created_at);
                        var fechaStr = fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
                        var tagClass = item.tipo === 'evento' ? 'news-tag-evento' : 'news-tag-noticia';

                        html += '<div class="news-card">';
                        if (item.imagen) {
                            html += '<div class="news-card-img"><img src="' + item.imagen + '" alt="' + escapeHtml(item.titulo) + '"></div>';
                        } else {
                            html += '<div class="news-card-img">' + (item.tipo === 'evento' ? '&#128197;' : '&#128196;') + '</div>';
                        }
                        html += '<div class="news-card-body">';
                        html += '<div class="news-card-meta">';
                        html += '<span class="news-tag ' + tagClass + '">' + item.tipo.charAt(0).toUpperCase() + item.tipo.slice(1) + '</span>';
                        if (item.categoria) {
                            html += '<span class="news-card-date">' + escapeHtml(item.categoria) + '</span>';
                        }
                        html += '</div>';
                        html += '<h4>' + escapeHtml(item.titulo) + '</h4>';
                        html += '<p>' + escapeHtml(item.resumen || '') + '</p>';
                        html += '</div>';
                        html += '<div class="news-card-footer">';
                        html += '<span class="news-card-date">' + fechaStr + '</span>';
                        if (item.ubicacion) {
                            html += '<span class="news-card-location">&#128205; ' + escapeHtml(item.ubicacion) + '</span>';
                        }
                        html += '</div>';
                        html += '</div>';
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">No hay publicaciones disponibles.</p>';
                }
            } catch (e) {
                container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">Error al cargar noticias.</p>';
            }
        }
    };

    xhr.onerror = function() {
        container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">Error de conexion.</p>';
    };

    xhr.send();
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// Cargar noticias en el index
document.addEventListener('DOMContentLoaded', function() {
    var newsContainer = document.getElementById('newsContainer');
    if (newsContainer) {
        loadNews('newsContainer', { limit: 6 });
    }
});

// ============================================
// SCROLL ANIMATIONS - Intersection Observer
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    var animatedElements = document.querySelectorAll('.animate-on-scroll');
    if (animatedElements.length === 0) return;

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    animatedElements.forEach(function(el) {
        observer.observe(el);
    });
});
