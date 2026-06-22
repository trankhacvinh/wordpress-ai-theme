<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$modal_id = sanitize_key((string) pmedia_ai_section_value($section, 'id', ''));
if ($modal_id === '') {
    $modal_id = wp_unique_id('pmedia-modal-');
}
$size = sanitize_key((string) pmedia_ai_section_value($section, 'modal_size', 'medium'));
$children = pmedia_ai_section_children($section);
if (empty($children)) {
    $children_json = pmedia_ai_section_value($section, 'children_json', '');
    $decoded_children = is_string($children_json) ? json_decode($children_json, true) : [];
    $children = is_array($decoded_children) ? $decoded_children : [];
}
?>
<section <?php echo pmedia_ai_section_attrs($section, 'pmedia-section pmedia-modal-trigger-section'); ?>>
    <div class="pmedia-container">
        <?php if (pmedia_ai_section_value($section, 'title')) : ?>
            <div class="pmedia-section-heading">
                <h2><?php echo esc_html((string) pmedia_ai_section_value($section, 'title')); ?></h2>
                <?php if (pmedia_ai_section_value($section, 'description')) : ?>
                    <p class="pmedia-section-description"><?php echo esc_html((string) pmedia_ai_section_value($section, 'description')); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <button type="button" class="pmedia-btn pmedia-btn-primary" data-modal-open="<?php echo esc_attr($modal_id); ?>">
            <?php echo esc_html((string) pmedia_ai_section_value($section, 'button_text', 'Mở modal')); ?>
        </button>

        <div id="<?php echo esc_attr($modal_id); ?>" class="pmedia-modal pmedia-modal-<?php echo esc_attr($size); ?>" hidden aria-hidden="true" role="dialog" aria-modal="true">
            <div class="pmedia-modal-overlay" data-modal-close></div>
            <div class="pmedia-modal-dialog" role="document">
                <button type="button" class="pmedia-modal-close" data-modal-close aria-label="Đóng">×</button>
                <?php if (pmedia_ai_section_value($section, 'modal_title')) : ?>
                    <h2><?php echo esc_html((string) pmedia_ai_section_value($section, 'modal_title')); ?></h2>
                <?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'modal_description')) : ?>
                    <p class="pmedia-section-description"><?php echo esc_html((string) pmedia_ai_section_value($section, 'modal_description')); ?></p>
                <?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'content')) : ?>
                    <div class="pmedia-content"><?php echo wp_kses_post((string) pmedia_ai_section_value($section, 'content')); ?></div>
                <?php endif; ?>
                <?php if (!empty($children)) : ?>
                    <div class="pmedia-modal-children">
                        <?php pmedia_ai_render_components($children); ?>
                    </div>
                <?php endif; ?>
                <?php if (pmedia_ai_section_value($section, 'form_shortcode')) : ?>
                    <div class="pmedia-modal-form"><?php echo do_shortcode((string) pmedia_ai_section_value($section, 'form_shortcode')); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
