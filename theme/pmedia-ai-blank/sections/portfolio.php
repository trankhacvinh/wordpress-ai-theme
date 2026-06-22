<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = pmedia_ai_section_value($section, 'items', []);
$filters = pmedia_ai_section_value($section, 'filters', []);
$variant = sanitize_key((string) pmedia_ai_section_value($section, 'variant', 'grid'));
?>
<section <?php echo pmedia_ai_section_attrs($section, 'pmedia-section pmedia-portfolio-section'); ?>>
    <div class="pmedia-container">
        <?php if (pmedia_ai_section_value($section, 'title') || pmedia_ai_section_value($section, 'description')) : ?>
            <div class="pmedia-section-heading">
                <?php if (pmedia_ai_section_value($section, 'title')) : ?><h2><?php echo esc_html((string) pmedia_ai_section_value($section, 'title')); ?></h2><?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'description')) : ?><p class="pmedia-section-description"><?php echo esc_html((string) pmedia_ai_section_value($section, 'description')); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($variant === 'filterable_grid' && is_array($filters) && !empty($filters)) : ?>
            <div class="pmedia-portfolio-filters" data-component="portfolio-filters">
                <?php foreach ($filters as $index => $filter) : ?>
                    <button type="button" class="pmedia-filter-button <?php echo $index === 0 ? 'is-active' : ''; ?>" data-filter="<?php echo esc_attr(sanitize_title((string) $filter)); ?>"><?php echo esc_html((string) $filter); ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div class="pmedia-portfolio-grid" data-component="portfolio">
                <?php foreach ($items as $index => $item) : ?>
                    <?php if (!is_array($item)) { continue; } ?>
                    <?php
                    $category = (string) ($item['category'] ?? 'Tất cả');
                    $modal = isset($item['modal']) && is_array($item['modal']) ? $item['modal'] : [];
                    if (empty($modal) && !empty($item['modal_json'])) {
                        $decoded = json_decode((string) $item['modal_json'], true);
                        $modal = is_array($decoded) ? $decoded : [];
                    }
                    $modal_id = !empty($modal) ? wp_unique_id('portfolio-modal-') : '';
                    ?>
                    <article class="pmedia-card pmedia-portfolio-card" data-category="<?php echo esc_attr(sanitize_title($category)); ?>">
                        <?php if (!empty($item['image'])) : ?><img src="<?php echo esc_url((string) $item['image']); ?>" alt="<?php echo esc_attr((string) ($item['title'] ?? '')); ?>"><?php endif; ?>
                        <?php if (!empty($item['category'])) : ?><p class="pmedia-eyebrow"><?php echo esc_html((string) $item['category']); ?></p><?php endif; ?>
                        <?php if (!empty($item['title'])) : ?><h3><?php echo esc_html((string) $item['title']); ?></h3><?php endif; ?>
                        <?php if (!empty($item['description'])) : ?><p><?php echo esc_html((string) $item['description']); ?></p><?php endif; ?>
                        <div class="pmedia-actions">
                            <?php if (!empty($item['link'])) : ?><a class="pmedia-btn pmedia-btn-secondary" href="<?php echo esc_url((string) $item['link']); ?>">Xem trang</a><?php endif; ?>
                            <?php if (!empty($modal)) : ?><button type="button" class="pmedia-btn pmedia-btn-primary" data-modal-open="<?php echo esc_attr($modal_id); ?>">Xem chi tiết</button><?php endif; ?>
                        </div>
                    </article>

                    <?php if (!empty($modal)) : ?>
                        <?php
                        $modal_children = isset($modal['children']) && is_array($modal['children']) ? $modal['children'] : [];
                        $modal_title = (string) ($modal['title'] ?? ($item['title'] ?? 'Chi tiết dự án'));
                        ?>
                        <div id="<?php echo esc_attr($modal_id); ?>" class="pmedia-modal pmedia-modal-large" hidden aria-hidden="true" role="dialog" aria-modal="true">
                            <div class="pmedia-modal-overlay" data-modal-close></div>
                            <div class="pmedia-modal-dialog" role="document">
                                <button type="button" class="pmedia-modal-close" data-modal-close aria-label="Đóng">×</button>
                                <h2><?php echo esc_html($modal_title); ?></h2>
                                <?php if (!empty($modal['description'])) : ?><p class="pmedia-section-description"><?php echo esc_html((string) $modal['description']); ?></p><?php endif; ?>
                                <?php if (!empty($modal_children)) : ?><?php pmedia_ai_render_components($modal_children); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
