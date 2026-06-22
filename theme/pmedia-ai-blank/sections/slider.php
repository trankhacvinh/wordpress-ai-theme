<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = pmedia_ai_section_value($section, 'items', []);
$variant = sanitize_key((string) pmedia_ai_section_value($section, 'variant', 'cards'));
$autoplay = filter_var(pmedia_ai_section_value($section, 'autoplay', false), FILTER_VALIDATE_BOOLEAN);
$show_arrows = filter_var(pmedia_ai_section_value($section, 'show_arrows', true), FILTER_VALIDATE_BOOLEAN);
$show_dots = filter_var(pmedia_ai_section_value($section, 'show_dots', true), FILTER_VALIDATE_BOOLEAN);
$interval = max(1500, (int) pmedia_ai_section_value($section, 'interval', 5000));
$slider_id = wp_unique_id('pmedia-slider-');
?>
<section <?php echo pmedia_ai_section_attrs($section, 'pmedia-section pmedia-slider-section'); ?>>
    <div class="pmedia-container">
        <?php if (pmedia_ai_section_value($section, 'title') || pmedia_ai_section_value($section, 'description')) : ?>
            <div class="pmedia-section-heading">
                <?php if (pmedia_ai_section_value($section, 'title')) : ?><h2><?php echo esc_html((string) pmedia_ai_section_value($section, 'title')); ?></h2><?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'description')) : ?><p class="pmedia-section-description"><?php echo esc_html((string) pmedia_ai_section_value($section, 'description')); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div id="<?php echo esc_attr($slider_id); ?>" class="pmedia-slider pmedia-slider-<?php echo esc_attr($variant); ?>" data-component="slider" data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>" data-interval="<?php echo esc_attr((string) $interval); ?>">
                <div class="pmedia-slider-track">
                    <?php foreach ($items as $item) : ?>
                        <?php if (!is_array($item)) { continue; } ?>
                        <article class="pmedia-slider-slide pmedia-card">
                            <?php if (!empty($item['image'])) : ?><img src="<?php echo esc_url((string) $item['image']); ?>" alt="<?php echo esc_attr((string) ($item['title'] ?? '')); ?>"><?php endif; ?>
                            <?php if (!empty($item['title'])) : ?><h3><?php echo esc_html((string) $item['title']); ?></h3><?php endif; ?>
                            <?php if (!empty($item['description'])) : ?><p><?php echo esc_html((string) $item['description']); ?></p><?php endif; ?>
                            <?php if (!empty($item['button_text'])) : ?><a class="pmedia-btn pmedia-btn-secondary" href="<?php echo esc_url((string) ($item['button_link'] ?? '#')); ?>"><?php echo esc_html((string) $item['button_text']); ?></a><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($show_arrows) : ?>
                    <button type="button" class="pmedia-slider-nav pmedia-slider-prev" data-slider-prev aria-label="Trước">‹</button>
                    <button type="button" class="pmedia-slider-nav pmedia-slider-next" data-slider-next aria-label="Sau">›</button>
                <?php endif; ?>
                <?php if ($show_dots) : ?><div class="pmedia-slider-dots" data-slider-dots></div><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
