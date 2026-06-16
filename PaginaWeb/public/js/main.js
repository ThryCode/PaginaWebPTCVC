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
                        var fecha = new Date(item.created_at);
                        var fechaStr = fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
                        var tagClass = item.tipo === 'evento' ? 'news-tag-evento' : 'news-tag-noticia';

                        html += '<div class="news-card">';
                        if (item.imagen) {
                            html += '<div class="news-card-img"><img src="' + item.imagen + '" alt="' + escapeHtml(item.titulo) + '" loading="lazy"></div>';
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
                        var fecha = new Date(item.created_at);
                        var fechaStr = fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });

                        html += '<div class="news-card">';
                        if (item.imagen) {
                            html += '<div class="news-card-img"><img src="' + item.imagen + '" alt="' + escapeHtml(item.titulo) + '" loading="lazy"></div>';
                        } else {
                            html += '<div class="news-card-img">&#128197;</div>';
                        }
                        html += '<div class="news-card-body">';
                        html += '<div class="news-card-meta">';
                        html += '<span class="news-tag news-tag-evento">Evento</span>';
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

    var html = '<div class="calendar">';
    html += '<div class="calendar-header">';
    html += '<button class="calendar-nav" onclick="calendarPrev()" aria-label="Mes anterior">&#9664;</button>';
    html += '<span class="calendar-title">' + monthNames[month - 1] + ' ' + year + '</span>';
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
