document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.pmedia-ai-global-pick-image');
        if (!button || !window.wp || !wp.media) {
            return;
        }

        event.preventDefault();
        var input = button.parentNode ? button.parentNode.querySelector('.pmedia-ai-global-image') : null;
        var frame = wp.media({
            title: 'Chọn ảnh',
            button: { text: 'Dùng ảnh này' },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            if (input && attachment && attachment.url) {
                input.value = attachment.url;
            }
        });

        frame.open();
    });
});
