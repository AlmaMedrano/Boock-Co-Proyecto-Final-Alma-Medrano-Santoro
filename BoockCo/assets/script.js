document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar || !overlay) return;
    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        overlay.classList.toggle('is-visible', open);
        toggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('menu-open', open);
    };
    toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
    overlay.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setOpen(false); });
});
