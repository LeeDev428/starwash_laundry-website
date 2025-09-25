// Admin-specific JS placeholder
// Kept intentionally minimal to avoid 404 and allow other scripts to run.

// Example admin-only initialization (expand as needed)
(function(){
    // Notification modal handlers
    function $(sel, ctx) { return (ctx || document).querySelector(sel); }
    function $all(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

    var notifBtn = $('#notifBtn');
    var notifModal = $('#notifModal');

    function openNotif() {
        if (!notifModal || !notifBtn) return;
        notifModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        // position the panel near the bell button
        var panel = notifModal.querySelector('.notif-modal-panel');
        if (panel) {
            var btnRect = notifBtn.getBoundingClientRect();
            var panelWidth = Math.min(420, window.innerWidth - 32);
            var top = btnRect.bottom + 8; // small gap
            var left = Math.max(8, btnRect.right - panelWidth); // align right edge with button
            // if not enough space on the left, clamp
            if (left + panelWidth > window.innerWidth - 8) left = window.innerWidth - panelWidth - 8;
            panel.style.width = panelWidth + 'px';
            panel.style.top = top + 'px';
            panel.style.left = left + 'px';
            panel.style.right = 'auto';
        }
    }

    function closeNotif() {
        if (!notifModal) return;
        notifModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        var panel = notifModal.querySelector('.notif-modal-panel');
        if (panel) {
            panel.style.top = '';
            panel.style.left = '';
            panel.style.width = '';
            panel.style.right = '';
        }
    }

    if (notifBtn && notifModal) {
        notifBtn.addEventListener('click', function(e){
            var hidden = notifModal.getAttribute('aria-hidden') === 'true';
            if (hidden) openNotif(); else closeNotif();
        });

        // close on elements with data-close attribute (backdrop and close button)
        $all('[data-close]', notifModal).forEach(function(el){
            el.addEventListener('click', function(){ closeNotif(); });
        });

        // close on ESC
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeNotif(); });
        // reposition on resize while open
        window.addEventListener('resize', function(){ if (notifModal && notifModal.getAttribute('aria-hidden') === 'false') openNotif(); });
    }
})();
