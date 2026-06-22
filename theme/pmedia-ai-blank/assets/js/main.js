document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.site-menu-toggle');

    if (!toggle) {
        return;
    }

    toggle.addEventListener('click', function () {
        var isOpen = document.body.classList.toggle('menu-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
});
