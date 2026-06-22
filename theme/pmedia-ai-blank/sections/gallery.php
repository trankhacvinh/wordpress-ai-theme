<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = pmedia_ai_section_value($section, 'items', []);
$variant = sanitize_key((string) pmedia_ai_section_value($section, 'variant', 'grid'));
$lightbox = filter_var(pmedia_ai_section_value($section, 'lightbox', false), FILTER_VALIDATE_BOOLEAN);
$gallery_id = wp_unique_id('pmedia-gallery-');
?>
<section <?php echo pmedia_ai_section_attrs($section, 'pmedia-section pmedia-gallery-section'); ?>>
    <div class="pmedia-container">
        <?php if (pmedia_ai_section_value($section, 'title') || pmedia_ai_section_value($section, 'description')) : ?>
            <div class="pmedia-section-heading">
                <?php if (pmedia_ai_section_value($section, 'title')) : ?>
                    <h2><?php echo esc_html((string) pmedia_ai_section_value($section, 'title')); ?></h2>
                <?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'description')) : ?>
                    <p class="pmedia-section-description"><?php echo esc_html((string) pmedia_ai_section_value($section, 'description')); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div class="pmedia-gallery pmedia-gallery-<?php echo esc_attr($variant); ?>" data-component="gallery" data-lightbox="<?php echo $lightbox ? 'true' : 'false'; ?>" data-gallery-id="<?php echo esc_attr($gallery_id); ?>">
                <?php foreach ($items as $index => $item) : ?>
                    <?php if (!is_array($item)) { continue; } ?>
                    <?php $image = (string) ($item['image'] ?? ''); ?>
                    <figure class="pmedia-gallery-item">
                        <?php if ($image) : ?>
                            <a href="<?php echo esc_url($image); ?>" <?php echo $lightbox ? 'data-lightbox-item' : ''; ?> data-gallery-index="<?php echo esc_attr((string) $index); ?>">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr((string) ($item['title'] ?? '')); ?>">
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($item['title']) || !empty($item['description'])) : ?>
                            <figcaption>
                                <?php if (!empty($item['title'])) : ?><strong><?php echo esc_html((string) $item['title']); ?></strong><?php endif; ?>
                                <?php if (!empty($item['description'])) : ?><p><?php echo esc_html((string) $item['description']); ?></p><?php endif; ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
