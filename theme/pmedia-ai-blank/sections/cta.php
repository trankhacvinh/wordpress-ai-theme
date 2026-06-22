<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
?>
<section class="pmedia-section pmedia-cta-section">
    <div class="pmedia-container">
        <div class="pmedia-cta-box">
            <div>
                <h2><?php echo esc_html((string) ($section['title'] ?? 'Sẵn sàng bắt đầu?')); ?></h2>
                <?php if (!empty($section['description'])) : ?>
                    <p><?php echo esc_html((string) $section['description']); ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($section['button_text'])) : ?>
                <a class="pmedia-btn pmedia-btn-light" href="<?php echo esc_url((string) ($section['button_link'] ?? '#')); ?>"><?php echo esc_html((string) $section['button_text']); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
