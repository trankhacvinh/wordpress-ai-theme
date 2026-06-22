<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
?>
<section class="pmedia-section pmedia-hero-section">
    <div class="pmedia-container pmedia-hero-grid">
        <div class="pmedia-hero-content">
            <?php if (!empty($section['eyebrow'])) : ?>
                <p class="pmedia-eyebrow"><?php echo esc_html((string) $section['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (!empty($section['title'])) : ?>
                <h1><?php echo esc_html((string) $section['title']); ?></h1>
            <?php endif; ?>

            <?php if (!empty($section['description'])) : ?>
                <p class="pmedia-lead"><?php echo esc_html((string) $section['description']); ?></p>
            <?php endif; ?>

            <?php if (!empty($section['button_text']) || !empty($section['secondary_button_text'])) : ?>
                <div class="pmedia-actions">
                    <?php if (!empty($section['button_text'])) : ?>
                        <a class="pmedia-btn pmedia-btn-primary" href="<?php echo esc_url((string) ($section['button_link'] ?? '#')); ?>"><?php echo esc_html((string) $section['button_text']); ?></a>
                    <?php endif; ?>
                    <?php if (!empty($section['secondary_button_text'])) : ?>
                        <a class="pmedia-btn pmedia-btn-secondary" href="<?php echo esc_url((string) ($section['secondary_button_link'] ?? '#')); ?>"><?php echo esc_html((string) $section['secondary_button_text']); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($section['image'])) : ?>
            <div class="pmedia-hero-media">
                <img src="<?php echo esc_url((string) $section['image']); ?>" alt="<?php echo esc_attr((string) ($section['image_alt'] ?? $section['title'] ?? '')); ?>">
            </div>
        <?php else : ?>
            <div class="pmedia-hero-panel" aria-hidden="true">
                <span>AI</span>
                <strong>CMS</strong>
            </div>
        <?php endif; ?>
    </div>
</section>
