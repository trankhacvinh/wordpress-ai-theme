<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = pmedia_ai_section_value($section, 'items', []);
$tabs_id = wp_unique_id('pmedia-tabs-');
?>
<section <?php echo pmedia_ai_section_attrs($section, 'pmedia-section pmedia-tabs-section'); ?>>
    <div class="pmedia-container">
        <?php if (pmedia_ai_section_value($section, 'title') || pmedia_ai_section_value($section, 'description')) : ?>
            <div class="pmedia-section-heading">
                <?php if (pmedia_ai_section_value($section, 'title')) : ?><h2><?php echo esc_html((string) pmedia_ai_section_value($section, 'title')); ?></h2><?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'description')) : ?><p class="pmedia-section-description"><?php echo esc_html((string) pmedia_ai_section_value($section, 'description')); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div id="<?php echo esc_attr($tabs_id); ?>" class="pmedia-tabs" data-component="tabs">
                <div class="pmedia-tabs-nav" role="tablist">
                    <?php foreach ($items as $index => $item) : ?>
                        <?php if (!is_array($item)) { continue; } ?>
                        <button type="button" role="tab" class="pmedia-tab-button <?php echo $index === 0 ? 'is-active' : ''; ?>" data-tab-index="<?php echo esc_attr((string) $index); ?>">
                            <?php echo esc_html((string) ($item['label'] ?? ('Tab ' . ($index + 1)))); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="pmedia-tabs-panels">
                    <?php foreach ($items as $index => $item) : ?>
                        <?php if (!is_array($item)) { continue; } ?>
                        <?php
                        $children = isset($item['children']) && is_array($item['children']) ? $item['children'] : [];
                        if (empty($children) && !empty($item['children_json'])) {
                            $decoded = json_decode((string) $item['children_json'], true);
                            $children = is_array($decoded) ? $decoded : [];
                        }
                        ?>
                        <div class="pmedia-tab-panel <?php echo $index === 0 ? 'is-active' : ''; ?>" data-tab-panel="<?php echo esc_attr((string) $index); ?>">
                            <?php if (!empty($item['content'])) : ?><div class="pmedia-content"><?php echo wp_kses_post((string) $item['content']); ?></div><?php endif; ?>
                            <?php if (!empty($children)) : ?><?php pmedia_ai_render_components($children); ?><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
