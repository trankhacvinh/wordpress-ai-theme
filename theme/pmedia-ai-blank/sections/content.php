<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
?>
<section class="pmedia-section pmedia-content-section">
    <div class="pmedia-container pmedia-narrow">
        <?php if (!empty($section['title'])) : ?>
            <h2><?php echo esc_html((string) $section['title']); ?></h2>
        <?php endif; ?>
        <?php if (!empty($section['content'])) : ?>
            <div class="pmedia-content">
                <?php echo wp_kses_post((string) $section['content']); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
