document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggle = document.getElementById('sidebarToggle');
    function toggleSidebar() {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }
    if (toggle) {
        toggle.addEventListener('click', toggleSidebar);
    }
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }
    var touchStartX = 0;
    sidebar.addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });
    sidebar.addEventListener('touchmove', function(e) {
        if (!sidebar.classList.contains('open')) return;
        var deltaX = e.touches[0].clientX - touchStartX;
        if (deltaX < -50) {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
    }, { passive: true });
    sidebar.querySelectorAll('.sidebar-nav a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }
            pageLoader.classList.add('active');
        });
    });
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('hamburger')) {
            toggleSidebar();
        }
        var toggleEditId = e.target.getAttribute('data-toggle-edit');
        if (toggleEditId) {
            var el = document.getElementById('edit-' + toggleEditId);
            if (el) {
                el.classList.toggle('active');
            }
        }
        if (e.target.id === 'btnCopyPAC') {
            copyPAC();
        }
    });
    var modalOverlay = document.createElement('div');
    modalOverlay.className = 'modal-overlay';
    modalOverlay.innerHTML = '<div class="modal-box"><p id="modalMessage"></p><div class="modal-actions"><button class="btn btn-danger" id="modalConfirm">Confirmar</button><button class="btn btn-light" id="modalCancel">Cancelar</button></div></div>';
    document.body.appendChild(modalOverlay);
    var pageLoader = document.createElement('div');
    pageLoader.className = 'page-loader';
    document.body.appendChild(pageLoader);
    var modalMessage = document.getElementById('modalMessage');
    var modalConfirm = document.getElementById('modalConfirm');
    var modalCancel = document.getElementById('modalCancel');
    var pendingForm = null;
    function closeModal() {
        modalOverlay.classList.remove('active');
        pendingForm = null;
    }
    modalCancel.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) closeModal();
    });
    modalConfirm.addEventListener('click', function() {
        if (pendingForm) {
            var form = pendingForm;
            closeModal();
            pageLoader.classList.add('active');
            form.submit();
        }
    });
    document.addEventListener('submit', function(e) {
        var form = e.target;
        var msg = form.getAttribute('data-confirm');
        if (msg) {
            e.preventDefault();
            modalMessage.textContent = msg;
            pendingForm = form;
            modalOverlay.classList.add('active');
        } else {
            pageLoader.classList.add('active');
        }
    });
    document.addEventListener('change', function(e) {
        if (e.target.hasAttribute('data-delete-img')) {
            var label = e.target.parentElement;
            label.style.background = e.target.checked ? 'rgba(192,57,43,1)' : 'rgba(192,57,43,0.85)';
        }
    });
    function copyPAC() {
        var code = document.getElementById('pacCodeText');
        if (!code) return;
        var text = code.textContent || code.innerText;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyFeedback();
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showCopyFeedback();
        }
    }
    function showCopyFeedback() {
        var btn = document.getElementById('btnCopyPAC');
        if (!btn) return;
        var orig = btn.textContent;
        btn.textContent = 'Copiado!';
        btn.style.background = '#16a34a';
        setTimeout(function() {
            btn.textContent = orig;
            btn.style.background = '';
        }, 2000);
    }
    var alertEl = document.querySelector('.alert-success, .alert-error');
    if (alertEl) {
        setTimeout(function() {
            alertEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
});
