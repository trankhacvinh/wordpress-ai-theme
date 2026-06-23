<?php
if (!defined('ABSPATH')) {
    exit;
}

$eyebrow = (string) pmedia_ai_section_value($pmedia_section, 'eyebrow', '');
$title = (string) pmedia_ai_section_value($pmedia_section, 'title', '');
$content = (string) pmedia_ai_section_value($pmedia_section, 'content', '');
$align = sanitize_html_class((string) pmedia_ai_section_value($pmedia_section, 'align', 'left'));
$font_size = sanitize_html_class((string) pmedia_ai_section_value($pmedia_section, 'font_size', 'md'));
$font_weight = sanitize_html_class((string) pmedia_ai_section_value($pmedia_section, 'font_weight', 'inherit'));
$text_transform = sanitize_html_class((string) pmedia_ai_section_value($pmedia_section, 'text_transform', 'none'));
$max_width = (string) pmedia_ai_section_value($pmedia_section, 'max_width', '760px');
?>
<section <?php echo pmedia_ai_section_attrs($pmedia_section, 'pmedia-section pmedia-rich-text-section'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="pmedia-container">
        <div class="pmedia-rich-text pmedia-align-<?php echo esc_attr($align); ?> pmedia-font-size-<?php echo esc_attr($font_size); ?> pmedia-font-weight-<?php echo esc_attr($font_weight); ?> pmedia-text-transform-<?php echo esc_attr($text_transform); ?>" style="max-width: <?php echo esc_attr($max_width); ?>;">
            <?php if ($eyebrow !== '') : ?><p class="pmedia-eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
            <?php if ($title !== '') : ?><h2><?php echo esc_html($title); ?></h2><?php endif; ?>
            <?php if ($content !== '') : ?><div class="pmedia-content"><?php echo wp_kses_post($content); ?></div><?php endif; ?>
        </div>
    </div>
</section>
