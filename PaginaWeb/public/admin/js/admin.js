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
    document.addEventListener('submit', function(e) {
        var form = e.target;
        var msg = form.getAttribute('data-confirm');
        if (msg && !confirm(msg)) {
            e.preventDefault();
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
});
