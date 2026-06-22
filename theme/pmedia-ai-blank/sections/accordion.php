<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = pmedia_ai_section_value($section, 'items', []);
?>
<section <?php echo pmedia_ai_section_attrs($section, 'pmedia-section pmedia-accordion-section'); ?>>
    <div class="pmedia-container pmedia-narrow">
        <?php if (pmedia_ai_section_value($section, 'title') || pmedia_ai_section_value($section, 'description')) : ?>
            <div class="pmedia-section-heading">
                <?php if (pmedia_ai_section_value($section, 'title')) : ?><h2><?php echo esc_html((string) pmedia_ai_section_value($section, 'title')); ?></h2><?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'description')) : ?><p class="pmedia-section-description"><?php echo esc_html((string) pmedia_ai_section_value($section, 'description')); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div class="pmedia-accordion" data-component="accordion">
                <?php foreach ($items as $item) : ?>
                    <?php if (!is_array($item)) { continue; } ?>
                    <?php
                    $children = isset($item['children']) && is_array($item['children']) ? $item['children'] : [];
                    if (empty($children) && !empty($item['children_json'])) {
                        $decoded = json_decode((string) $item['children_json'], true);
                        $children = is_array($decoded) ? $decoded : [];
                    }
                    ?>
                    <details class="pmedia-accordion-item">
                        <summary><?php echo esc_html((string) ($item['title'] ?? 'Nội dung')); ?></summary>
                        <?php if (!empty($item['content'])) : ?><div class="pmedia-content"><?php echo wp_kses_post((string) $item['content']); ?></div><?php endif; ?>
                        <?php if (!empty($children)) : ?><?php pmedia_ai_render_components($children); ?><?php endif; ?>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
