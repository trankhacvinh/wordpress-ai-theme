<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = $section['items'] ?? [];
?>
<section class="pmedia-section pmedia-pricing-section">
    <div class="pmedia-container">
        <div class="pmedia-section-heading">
            <h2><?php echo esc_html((string) ($section['title'] ?? 'Bảng giá')); ?></h2>
            <?php if (!empty($section['description'])) : ?>
                <p class="pmedia-section-description"><?php echo esc_html((string) $section['description']); ?></p>
            <?php endif; ?>
        </div>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div class="pmedia-grid pmedia-pricing-grid">
                <?php foreach ($items as $item) : ?>
                    <?php if (!is_array($item)) { continue; } ?>
                    <article class="pmedia-card pmedia-price-card">
                        <h3><?php echo esc_html((string) ($item['name'] ?? 'Gói dịch vụ')); ?></h3>
                        <?php if (!empty($item['price'])) : ?>
                            <p class="pmedia-price"><?php echo esc_html((string) $item['price']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['description'])) : ?>
                            <p><?php echo esc_html((string) $item['description']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['features']) && is_array($item['features'])) : ?>
                            <ul>
                                <?php foreach ($item['features'] as $feature) : ?>
                                    <li><?php echo esc_html((string) $feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
