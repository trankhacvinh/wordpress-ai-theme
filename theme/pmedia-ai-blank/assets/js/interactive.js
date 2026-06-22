document.addEventListener('DOMContentLoaded', function () {
    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('pmedia-modal-open');
        var close = modal.querySelector('[data-modal-close]');
        if (close) close.focus();
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('pmedia-modal-open');
    }

    document.addEventListener('click', function (event) {
        var openButton = event.target.closest('[data-modal-open]');
        if (openButton) {
            event.preventDefault();
            openModal(openButton.getAttribute('data-modal-open'));
            return;
        }

        var closeButton = event.target.closest('[data-modal-close]');
        if (closeButton) {
            event.preventDefault();
            closeModal(closeButton.closest('.pmedia-modal'));
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.pmedia-modal:not([hidden])').forEach(closeModal);
            var lightbox = document.querySelector('.pmedia-lightbox');
            if (lightbox) lightbox.hidden = true;
        }
    });

    var lightbox = document.createElement('div');
    lightbox.className = 'pmedia-lightbox';
    lightbox.hidden = true;
    lightbox.innerHTML = '<button type="button" aria-label="Đóng">×</button><img src="" alt="">';
    document.body.appendChild(lightbox);

    lightbox.addEventListener('click', function (event) {
        if (event.target === lightbox || event.target.tagName === 'BUTTON') {
            lightbox.hidden = true;
        }
    });

    document.addEventListener('click', function (event) {
        var item = event.target.closest('[data-lightbox-item]');
        if (!item) return;
        event.preventDefault();
        var img = lightbox.querySelector('img');
        img.src = item.getAttribute('href');
        img.alt = item.querySelector('img') ? item.querySelector('img').alt : '';
        lightbox.hidden = false;
    });

    document.querySelectorAll('[data-component="slider"]').forEach(function (slider) {
        var track = slider.querySelector('.pmedia-slider-track');
        var slides = Array.prototype.slice.call(slider.querySelectorAll('.pmedia-slider-slide'));
        var prev = slider.querySelector('[data-slider-prev]');
        var next = slider.querySelector('[data-slider-next]');
        var dotsWrap = slider.querySelector('[data-slider-dots]');
        var index = 0;
        var autoplay = slider.getAttribute('data-autoplay') === 'true';
        var interval = parseInt(slider.getAttribute('data-interval') || '5000', 10);
        var timer = null;

        if (!track || slides.length === 0) return;

        function perView() {
            return window.matchMedia('(max-width: 900px)').matches ? 1 : (slider.classList.contains('pmedia-slider-cards') || slider.classList.contains('pmedia-slider-logos') ? 3 : 1);
        }

        function maxIndex() {
            return Math.max(0, slides.length - perView());
        }

        function update() {
            index = Math.max(0, Math.min(index, maxIndex()));
            track.style.transform = 'translateX(' + (-index * (100 / perView())) + '%)';
            if (dotsWrap) {
                dotsWrap.querySelectorAll('.pmedia-slider-dot').forEach(function (dot, i) {
                    dot.classList.toggle('is-active', i === index);
                });
            }
        }

        if (dotsWrap) {
            for (var i = 0; i <= maxIndex(); i++) {
                var dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'pmedia-slider-dot';
                dot.setAttribute('aria-label', 'Slide ' + (i + 1));
                dot.addEventListener('click', (function (dotIndex) {
                    return function () { index = dotIndex; update(); restart(); };
                })(i));
                dotsWrap.appendChild(dot);
            }
        }

        function restart() {
            if (timer) window.clearInterval(timer);
            if (autoplay) {
                timer = window.setInterval(function () {
                    index = index >= maxIndex() ? 0 : index + 1;
                    update();
                }, interval);
            }
        }

        if (prev) prev.addEventListener('click', function () { index = index <= 0 ? maxIndex() : index - 1; update(); restart(); });
        if (next) next.addEventListener('click', function () { index = index >= maxIndex() ? 0 : index + 1; update(); restart(); });
        window.addEventListener('resize', update);
        update();
        restart();
    });

    document.querySelectorAll('[data-component="tabs"]').forEach(function (tabs) {
        var buttons = tabs.querySelectorAll('.pmedia-tab-button');
        var panels = tabs.querySelectorAll('.pmedia-tab-panel');
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var index = button.getAttribute('data-tab-index');
                buttons.forEach(function (btn) { btn.classList.toggle('is-active', btn === button); });
                panels.forEach(function (panel) { panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === index); });
            });
        });
    });

    document.querySelectorAll('[data-component="portfolio-filters"]').forEach(function (filters) {
        var section = filters.closest('.pmedia-portfolio-section');
        var cards = section ? section.querySelectorAll('.pmedia-portfolio-card') : [];
        filters.querySelectorAll('[data-filter]').forEach(function (button, index) {
            button.addEventListener('click', function () {
                var filter = button.getAttribute('data-filter');
                filters.querySelectorAll('[data-filter]').forEach(function (btn) { btn.classList.toggle('is-active', btn === button); });
                cards.forEach(function (card) {
                    card.style.display = index === 0 || card.getAttribute('data-category') === filter ? '' : 'none';
                });
            });
        });
    });

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        document.querySelectorAll('[data-animation]').forEach(function (el) { observer.observe(el); });
    } else {
        document.querySelectorAll('[data-animation]').forEach(function (el) { el.classList.add('is-visible'); });
    }
});
