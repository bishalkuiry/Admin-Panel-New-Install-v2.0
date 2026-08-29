/**
 * Auto-collapse the admin sidebar while viewing the App Content Builder page
 * and restore the user's previous preference on navigation away.
 *
 * Moved out of resources/views/admin/settings/app-content.blade.php so the
 * view no longer carries inline business logic.
 */
(function () {
    'use strict';

    var KEY = 'sb_col';
    var SESSION_KEY = '_sb_before_builder';

    // Save current value before we change it; only touch if not already collapsed,
    // so we know what to restore when the user navigates away.
    var current = localStorage.getItem(KEY);
    if (current !== '1') {
        sessionStorage.setItem(SESSION_KEY, current === null ? '__null__' : current);
        localStorage.setItem(KEY, '1');
    }

    function restoreSidebar() {
        var saved = sessionStorage.getItem(SESSION_KEY);
        if (saved === null) {
            return;
        }
        sessionStorage.removeItem(SESSION_KEY);
        if (saved === '__null__') {
            localStorage.removeItem(KEY);
        } else {
            localStorage.setItem(KEY, saved);
        }
    }

    window.addEventListener('pagehide', restoreSidebar);
    window.addEventListener('beforeunload', restoreSidebar);
})();
