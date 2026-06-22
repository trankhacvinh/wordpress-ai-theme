<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = $section['items'] ?? [];
?>
<section class="pmedia-section pmedia-services-section">
    <div class="pmedia-container">
        <div class="pmedia-section-heading">
            <?php if (!empty($section['eyebrow'])) : ?>
                <p class="pmedia-eyebrow"><?php echo esc_html((string) $section['eyebrow']); ?></p>
            <?php endif; ?>
            <h2><?php echo esc_html((string) ($section['title'] ?? 'Dịch vụ')); ?></h2>
            <?php if (!empty($section['description'])) : ?>
                <p class="pmedia-section-description"><?php echo esc_html((string) $section['description']); ?></p>
            <?php endif; ?>
        </div>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div class="pmedia-grid">
                <?php foreach ($items as $item) : ?>
                    <?php if (!is_array($item)) { continue; } ?>
                    <article class="pmedia-card">
                        <?php if (!empty($item['icon'])) : ?>
                            <div class="pmedia-card-icon"><?php echo esc_html((string) $item['icon']); ?></div>
                        <?php endif; ?>
                        <h3><?php echo esc_html((string) ($item['title'] ?? 'Dịch vụ')); ?></h3>
                        <?php if (!empty($item['description'])) : ?>
                            <p><?php echo esc_html((string) $item['description']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
