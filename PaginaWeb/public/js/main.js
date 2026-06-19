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

        var isTouchDevice = /Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // Mobile: toggle dropdown on parents, close nav on sub-items
        var navLinks = nav.querySelectorAll('a');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (isTouchDevice) {
                    var isDropdownParent = link.closest('.dropdown') && !link.closest('.dropdown-menu');
                    if (isDropdownParent) {
                        e.preventDefault();
                        var menu = link.parentElement.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.classList.toggle('open');
                        }
                        return;
                    }
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
    var autoPlayDelay = 3500;

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
                        setTimeout(function() {
                            messageDiv.style.display = 'none';
                            messageDiv.textContent = '';
                        }, 20000);
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
// HELPER - Obtener array de imagenes
// ============================================
function getImagenes(item) {
    if (item.imagenes && Array.isArray(item.imagenes) && item.imagenes.length > 0) {
        return item.imagenes;
    }
    if (item.imagen) {
        return [item.imagen];
    }
    return [];
}

// ============================================
// HELPER - Renderizar imagen o carrusel
// ============================================
function renderCardImage(imagenes, titulo, tipo) {
    if (imagenes.length === 0) {
        return '<div class="news-card-img">' + (tipo === 'evento' ? '&#128197;' : '&#128196;') + '</div>';
    }
    if (imagenes.length === 1) {
        return '<div class="news-card-img"><img src="' + imagenes[0] + '" alt="' + escapeHtml(titulo) + '"></div>';
    }
    var html = '<div class="card-carousel" data-count="' + imagenes.length + '">';
    html += '<div class="carousel-track">';
    imagenes.forEach(function(src) {
        html += '<div class="carousel-slide"><img src="' + src + '" alt="' + escapeHtml(titulo) + '"></div>';
    });
    html += '</div>';
    html += '<div class="carousel-dots">';
    for (var i = 0; i < imagenes.length; i++) {
        html += '<span class="carousel-dot' + (i === 0 ? ' active' : '') + '"></span>';
    }
    html += '</div>';
    html += '</div>';
    return html;
}

// ============================================
// HELPER - Iniciar carruseles
// ============================================
function initCardCarousels(container) {
    container.querySelectorAll('.card-carousel').forEach(function(carousel) {
        var track = carousel.querySelector('.carousel-track');
        var slides = track.querySelectorAll('.carousel-slide');
        var dots = carousel.querySelectorAll('.carousel-dot');
        var count = slides.length;
        if (count < 2) return;
        var cw = carousel.offsetWidth;
        if (cw === 0) cw = carousel.getBoundingClientRect().width;
        if (cw === 0) cw = carousel.parentElement.offsetWidth;
        track.style.width = (count * cw) + 'px';
        for (var i = 0; i < slides.length; i++) {
            slides[i].style.width = cw + 'px';
        }
        var current = 0;
        setInterval(function() {
            current = (current + 1) % count;
            track.style.transform = 'translateX(-' + (current * cw) + 'px)';
            dots.forEach(function(d) { d.classList.remove('active'); });
            if (dots[current]) dots[current].classList.add('active');
        }, 4000);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// ============================================
// SKELETON LOADERS
// ============================================
function skeletonCards(count) {
    var html = '';
    for (var i = 0; i < count; i++) {
        html += '<div class="skeleton-card">' +
            '<div class="skeleton-img"></div>' +
            '<div class="skeleton-body">' +
            '<div class="skeleton-tag"></div>' +
            '<div class="skeleton-text wide"></div>' +
            '<div class="skeleton-text"></div>' +
            '<div class="skeleton-text medium"></div>' +
            '</div></div>';
    }
    return html;
}

function skeletonGallery(count) {
    var html = '';
    for (var i = 0; i < count; i++) {
        html += '<div class="skeleton-gallery-item">' +
            '<div class="skeleton-img"></div>' +
            '</div>';
    }
    return html;
}

// ============================================
// CARGAR NOTICIAS DESDE LA API
// ============================================
function loadNews(containerId, options) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var limit = (options && options.limit) || 6;
    var tipo = (options && options.tipo) || '';
    var q = (options && options.q) || '';
    var baseUrl = 'api/news.php';

    var url = baseUrl + '?limit=' + limit;
    if (tipo) url += '&tipo=' + tipo;
    if (q) url += '&q=' + encodeURIComponent(q);

    container.innerHTML = skeletonCards(Math.min(limit, 6));

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success && response.data.length > 0) {
                    var html = '';
                    response.data.forEach(function(item) {
                        var dateSource = (item.fecha_evento || item.created_at).split(' ')[0];
                        var fecha = new Date(dateSource + 'T12:00:00');
                        var fechaStr = fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
                        var tagClass = item.tipo === 'evento' ? 'news-tag-evento' : 'news-tag-noticia';
                        var imagenes = getImagenes(item);

                        html += '<div class="news-card">';
                        html += renderCardImage(imagenes, item.titulo, item.tipo);
                        html += '<div class="news-card-body">';
                        html += '<div class="news-card-meta">';
                        html += '<span class="news-tag ' + tagClass + '">' + item.tipo.charAt(0).toUpperCase() + item.tipo.slice(1) + '</span>';
                        if (item.categoria) {
                            html += '<span class="news-card-date">' + escapeHtml(item.categoria) + '</span>';
                        }
                        html += '</div>';
                        html += '<h4>' + escapeHtml(item.titulo) + '</h4>';
                        html += '<p>' + escapeHtml(item.resumen || '') + '</p>';
                        html += '<a href="noticia.php?id=' + item.id + '" class="news-read-more">Leer m&aacute;s <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>';
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
                    initCardCarousels(container);
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

// ============================================
// CARGAR EVENTOS DESDE LA API
// ============================================
function loadEvents(containerId, options) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var limit = (options && options.limit) || 10;
    var q = (options && options.q) || '';
    var baseUrl = 'api/events.php';

    var url = baseUrl + '?limit=' + limit;
    if (q) url += '&q=' + encodeURIComponent(q);

    container.innerHTML = skeletonCards(Math.min(limit, 6));

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success && response.data.length > 0) {
                    var html = '';
                    response.data.forEach(function(item) {
                        var dateSource = (item.fecha_evento || item.created_at).split(' ')[0];
                        var fecha = new Date(dateSource + 'T12:00:00');
                        var fechaStr = fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
                        var imagenes = getImagenes(item);

                        html += '<div class="news-card">';
                        html += renderCardImage(imagenes, item.titulo, 'evento');
                        html += '<div class="news-card-body">';
                        html += '<div class="news-card-meta">';
                        html += '<span class="news-tag news-tag-evento">Evento</span>';
                        if (item.categoria) {
                            html += '<span class="news-card-date">' + escapeHtml(item.categoria) + '</span>';
                        }
                        html += '</div>';
                        html += '<h4>' + escapeHtml(item.titulo) + '</h4>';
                        html += '<p>' + escapeHtml(item.resumen || '') + '</p>';
                        html += '<a href="noticia.php?id=' + item.id + '" class="news-read-more">Leer m&aacute;s <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>';
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
                    initCardCarousels(container);
                } else {
                    container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">No hay eventos disponibles.</p>';
                }
            } catch (e) {
                container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">Error al cargar eventos.</p>';
            }
        }
    };

    xhr.onerror = function() {
        container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">Error de conexion.</p>';
    };

    xhr.send();
}

// ============================================
// CARGAR OPINIONES DESDE LA API
// ============================================
function loadOpiniones(containerId) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/opiniones.php', true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success && response.data.length > 0) {
                    var html = '';
                    response.data.forEach(function(item, index) {
                        html += '<div class="opinion-card' + (index === 0 ? ' active' : '') + '">';
                        html += '<p class="opinion-text">"' + escapeHtml(item.texto) + '"</p>';
                        html += '<div class="opinion-author">';
                        if (item.imagen) {
                            html += '<img src="' + item.imagen + '" alt="' + escapeHtml(item.nombre) + '" class="opinion-img">';
                        } else {
                            html += '<div class="opinion-img opinion-img-placeholder">' + escapeHtml(item.nombre.charAt(0)) + '</div>';
                        }
                        html += '<div class="opinion-info">';
                        html += '<strong>' + escapeHtml(item.nombre) + '</strong>';
                        html += '<span>' + escapeHtml(item.cargo) + '</span>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    });
                    container.innerHTML = html;
                    initOpinionesCarousel();
                } else {
                    container.closest('.opiniones-section').style.display = 'none';
                }
            } catch (e) {
                container.innerHTML = '';
            }
        }
    };

    xhr.onerror = function() {
        container.innerHTML = '';
    };

    xhr.send();
}

function initOpinionesCarousel() {
    var cards = document.querySelectorAll('#homeOpinionesContainer .opinion-card');
    var prevBtn = document.getElementById('opinionesPrev');
    var nextBtn = document.getElementById('opinionesNext');
    if (cards.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        return;
    }
    var current = 0;
    var autoPlay;
    function showOpinion(index) {
        cards[current].classList.remove('active');
        cards[current].classList.add('fade-out');
        setTimeout(function() {
            cards[current].classList.remove('fade-out');
            current = index;
            cards[current].classList.add('active');
        }, 600);
        resetAutoPlay();
    }
    function nextOpinion() {
        showOpinion(current === cards.length - 1 ? 0 : current + 1);
    }
    function resetAutoPlay() {
        clearInterval(autoPlay);
        autoPlay = setInterval(nextOpinion, 5000);
    }
    if (prevBtn) {
        prevBtn.onclick = function() {
            showOpinion(current === 0 ? cards.length - 1 : current - 1);
        };
    }
    if (nextBtn) {
        nextBtn.onclick = function() {
            showOpinion(current === cards.length - 1 ? 0 : current + 1);
        };
    }
    resetAutoPlay();
}

// ============================================
// CALENDARIO DE EVENTOS
// ============================================
var calendarState = {};

function renderCalendar(containerId) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var now = new Date();
    calendarState.year = now.getFullYear();
    calendarState.month = now.getMonth() + 1;
    calendarState.containerId = containerId;

    loadCalendarMonth();
}

function loadCalendarMonth() {
    var container = document.getElementById(calendarState.containerId);
    if (!container) return;

    var year = calendarState.year;
    var month = calendarState.month;

    var monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    var dayNames = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];

    var firstDay = new Date(year, month - 1, 1).getDay();
    var daysInMonth = new Date(year, month, 0).getDate();
    var startDay = (firstDay === 0) ? 6 : firstDay - 1;

    var currentYear = new Date().getFullYear();
    var yearOptions = '';
    for (var y = currentYear - 5; y <= currentYear + 2; y++) {
        yearOptions += '<option value="' + y + '"' + (y === year ? ' selected' : '') + '>' + y + '</option>';
    }

    var monthOptions = '';
    for (var m = 0; m < 12; m++) {
        monthOptions += '<option value="' + (m + 1) + '"' + ((m + 1) === month ? ' selected' : '') + '>' + monthNames[m] + '</option>';
    }

    var html = '<div class="calendar">';
    html += '<div class="calendar-header">';
    html += '<button class="calendar-nav" onclick="calendarPrev()" aria-label="Mes anterior">&#9664;</button>';
    html += '<div class="calendar-selectors">';
    html += '<select class="calendar-select calendar-month-select" onchange="calendarGoToMonth(parseInt(this.value))" aria-label="Mes">' + monthOptions + '</select>';
    html += '<select class="calendar-select calendar-year-select" onchange="calendarGoToYear(parseInt(this.value))" aria-label="Año">' + yearOptions + '</select>';
    html += '</div>';
    html += '<button class="calendar-nav" onclick="calendarNext()" aria-label="Mes siguiente">&#9654;</button>';
    html += '</div>';

    html += '<div class="calendar-grid">';
    for (var d = 0; d < 7; d++) {
        html += '<div class="calendar-day-name">' + dayNames[d] + '</div>';
    }

    for (var i = 0; i < startDay; i++) {
        html += '<div class="calendar-day empty"></div>';
    }

    for (var day = 1; day <= daysInMonth; day++) {
        html += '<div class="calendar-day" data-day="' + day + '">' + day + '<span class="calendar-dot"></span></div>';
    }

    html += '</div>';
    html += '<div id="calendarEventDetail" class="calendar-event-detail"></div>';
    html += '</div>';

    container.innerHTML = html;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/events.php?calendar=1&year=' + year + '&month=' + month, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    calendarState.dates = response.dates;
                    calendarState.currentYear = year;
                    calendarState.currentMonth = month;
                    highlightCalendarDays();
                }
            } catch (e) {}
        }
    };
    xhr.send();
}

function highlightCalendarDays() {
    if (calendarState.currentYear !== calendarState.year || calendarState.currentMonth !== calendarState.month) return;

    var dates = calendarState.dates;
    var days = document.querySelectorAll('.calendar-day[data-day]');
    for (var i = 0; i < days.length; i++) {
        var dayNum = parseInt(days[i].getAttribute('data-day'), 10);
        if (dates && dates[dayNum]) {
            days[i].classList.add('has-event');
            days[i].onclick = (function(d) {
                return function() { showCalendarEvents(d); };
            })(dayNum);
        }
    }

    var today = new Date();
    if (today.getFullYear() === calendarState.year && (today.getMonth() + 1) === calendarState.month) {
        var todayEl = document.querySelector('.calendar-day[data-day="' + today.getDate() + '"]');
        if (todayEl) todayEl.classList.add('today');
    }
}

function showCalendarEvents(day) {
    var detail = document.getElementById('calendarEventDetail');
    if (!detail) return;

    var events = calendarState.dates[day];
    if (!events || events.length === 0) {
        detail.innerHTML = '';
        detail.style.display = 'none';
        return;
    }

    var html = '<div class="calendar-event-list">';
    html += '<h4>Dia ' + day + '</h4>';
    for (var i = 0; i < events.length; i++) {
        var ev = events[i];
        var time = ev.fecha_evento.length > 10 ? ev.fecha_evento.substring(11, 16) : '';
        html += '<div class="calendar-event-item">';
        html += '<span class="calendar-event-time">' + time + '</span>';
        html += '<span class="calendar-event-title">' + escapeHtml(ev.titulo) + '</span>';
        if (ev.ubicacion) {
            html += '<span class="calendar-event-location">&#128205; ' + escapeHtml(ev.ubicacion) + '</span>';
        }
        html += '</div>';
    }
    html += '</div>';

    detail.innerHTML = html;
    detail.style.display = 'block';

    var allDays = document.querySelectorAll('.calendar-day.selected');
    for (var j = 0; j < allDays.length; j++) {
        allDays[j].classList.remove('selected');
    }
    var selected = document.querySelector('.calendar-day[data-day="' + day + '"]');
    if (selected) selected.classList.add('selected');
}

function calendarPrev() {
    calendarState.month--;
    if (calendarState.month < 1) {
        calendarState.month = 12;
        calendarState.year--;
    }
    loadCalendarMonth();
}

function calendarNext() {
    calendarState.month++;
    if (calendarState.month > 12) {
        calendarState.month = 1;
        calendarState.year++;
    }
    loadCalendarMonth();
}

function calendarGoToMonth(month) {
    calendarState.month = month;
    loadCalendarMonth();
}

function calendarGoToYear(year) {
    calendarState.year = year;
    loadCalendarMonth();
}
function loadGallery(containerId, options) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var limit = (options && options.limit) || 20;
    var baseUrl = 'api/gallery.php';

    var url = baseUrl + '?limit=' + limit;

    container.innerHTML = skeletonGallery(Math.min(limit, 12));

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success && response.data.length > 0) {
                    var html = '';
                    response.data.forEach(function(item) {
                        var src = item.imagen || item.url || '';
                        var title = escapeHtml(item.titulo || item.descripcion || '');
                        html += '<div class="gallery-item" onclick="openLightbox(\'' + src.replace(/'/g, "\\'") + '\', \'' + title.replace(/'/g, "\\'") + '\')">';
                        html += '<img src="' + src + '" alt="' + title + '" loading="lazy">';
                        if (title) {
                            html += '<div class="gallery-overlay"><span>' + title + '</span></div>';
                        }
                        html += '</div>';
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">No hay fotos en la galeria.</p>';
                }
            } catch (e) {
                container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">Error al cargar galeria.</p>';
            }
        }
    };

    xhr.onerror = function() {
        container.innerHTML = '<p class="empty" style="grid-column: 1/-1;">Error de conexion.</p>';
    };

    xhr.send();
}

// ============================================
// LIGHTBOX
// ============================================
function openLightbox(src, alt) {
    var existing = document.getElementById('lightbox');
    if (existing) existing.remove();

    var lb = document.createElement('div');
    lb.id = 'lightbox';
    lb.className = 'lightbox-overlay';
    lb.innerHTML = '<button class="lightbox-close" aria-label="Cerrar">&times;</button>' +
        '<button class="lightbox-prev" aria-label="Anterior">&#10094;</button>' +
        '<button class="lightbox-next" aria-label="Siguiente">&#10095;</button>' +
        '<div class="lightbox-content"><img src="' + src + '" alt="' + (alt || '') + '"></div>';
    document.body.appendChild(lb);
    document.body.style.overflow = 'hidden';

    setTimeout(function() { lb.classList.add('active'); }, 10);

    lb.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
    lb.querySelector('.lightbox-prev').addEventListener('click', function() { navigateLightbox(-1); });
    lb.querySelector('.lightbox-next').addEventListener('click', function() { navigateLightbox(1); });
    lb.addEventListener('click', function(e) {
        if (e.target === lb) closeLightbox();
    });
}

function closeLightbox() {
    var lb = document.getElementById('lightbox');
    if (lb) {
        lb.classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(function() { lb.remove(); }, 300);
    }
}

function navigateLightbox(dir) {
    var lb = document.getElementById('lightbox');
    if (!lb) return;
    var img = lb.querySelector('.lightbox-content img');
    var currentSrc = img.src;

    var galleryImages = document.querySelectorAll('.gallery-item img');
    var srcs = [];
    galleryImages.forEach(function(el) { srcs.push(el.src); });

    if (srcs.length <= 1) return;

    var idx = srcs.indexOf(currentSrc);
    idx = (idx + dir + srcs.length) % srcs.length;
    img.src = srcs[idx];

    var overlay = lb.querySelector('.lightbox-overlay');
    img.style.opacity = '0';
    setTimeout(function() { img.style.opacity = '1'; }, 50);
}

document.addEventListener('keydown', function(e) {
    var lb = document.getElementById('lightbox');
    if (!lb) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
});

// ============================================
// CARGAR NOTICIAS EN EL INDEX
// ============================================
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
    var animatedElements = document.querySelectorAll('.animate-on-scroll, .animate-fade-up, .animate-fade-down, .animate-fade-left, .animate-fade-right, .animate-fade-in, .animate-scale-in');
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

// ============================================
// TRANSICIONES DE PAGINA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('pageTransition') === 'in') {
        sessionStorage.removeItem('pageTransition');
        document.body.classList.add('page-transition');
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                document.body.classList.add('active');
            });
        });
    }

    var links = document.querySelectorAll('a[href]');
    links.forEach(function(link) {
        var href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('mailto') || href.startsWith('tel')) return;
        if (link.target === '_blank') return;

        link.addEventListener('click', function(e) {
            if (link.closest('.dropdown') && !link.closest('.dropdown-menu')) return;
            var currentPath = window.location.pathname.split('/').pop();
            if (href === currentPath) return;

            e.preventDefault();
            document.body.classList.remove('active');
            sessionStorage.setItem('pageTransition', 'in');
            setTimeout(function() {
                window.location.href = href;
            }, 250);
        });
    });
});

// ============================================
// BOTON VOLVER ARRIBA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    var backToTop = document.getElementById('backToTop');
    if (!backToTop) return;

    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    backToTop.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

// ============================================
// CONTADORES ANIMADOS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    var counters = document.querySelectorAll('.counter-number');
    if (counters.length === 0) return;

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(function(counter) {
        observer.observe(counter);
    });
});

function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-target'), 10);
    var duration = 2000;
    var start = 0;
    var startTime = null;

    function step(timestamp) {
        if (!startTime) startTime = timestamp;
        var progress = Math.min((timestamp - startTime) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        var current = Math.floor(eased * target);
        el.textContent = current.toLocaleString('es-ES');
        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            el.textContent = target.toLocaleString('es-ES');
        }
    }

    requestAnimationFrame(step);
}

// ============================================
// BUSQUEDA CON DEBOUNCE
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    var debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            var query = searchInput.value.trim();
            var containerId = searchInput.getAttribute('data-container') || 'allNewsContainer';
            var type = searchInput.getAttribute('data-type') || '';

            var options = { limit: 20 };
            if (query) options.q = query;
            if (type) options.tipo = type;

            loadNews(containerId, options);
        }, 300);
    });
});

// Busqueda de eventos
document.addEventListener('DOMContentLoaded', function() {
    var eventSearch = document.getElementById('eventSearchInput');
    if (!eventSearch) return;

    var debounceTimer;

    eventSearch.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            var query = eventSearch.value.trim();
            var containerId = eventSearch.getAttribute('data-container') || 'eventsContainer';

            var options = { limit: 10 };
            if (query) options.q = query;

            loadEvents(containerId, options);
        }, 300);
    });
});

// ============================================
// FAQ ACCORDION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    var faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var item = this.parentElement;
            var isOpen = item.classList.contains('faq-open');
            document.querySelectorAll('.faq-item.faq-open').forEach(function(i) {
                i.classList.remove('faq-open');
            });
            if (!isOpen) {
                item.classList.add('faq-open');
            }
        });
    });
});
