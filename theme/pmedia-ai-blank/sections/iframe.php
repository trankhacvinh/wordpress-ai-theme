<?php
if (!defined('ABSPATH')) {
    exit;
}

$title = (string) pmedia_ai_section_value($pmedia_section, 'title', 'Nội dung nhúng');
$src = (string) pmedia_ai_section_value($pmedia_section, 'src', '');
$ratio_raw = (string) pmedia_ai_section_value($pmedia_section, 'aspect_ratio', '16/9');
$ratio = sanitize_html_class(str_replace('/', '-', $ratio_raw));
$ratio_style = in_array($ratio_raw, ['1/1', '4/3', '16/9', '21/9'], true) ? 'aspect-ratio: ' . str_replace('/', ' / ', $ratio_raw) . ';' : '';
$height = max(120, (int) pmedia_ai_section_value($pmedia_section, 'height', 520));
$loading = (string) pmedia_ai_section_value($pmedia_section, 'loading', 'lazy');
?>
<section <?php echo pmedia_ai_section_attrs($pmedia_section, 'pmedia-section pmedia-iframe-section'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="pmedia-container">
        <?php if ($src !== '') : ?>
            <div class="pmedia-iframe-wrap pmedia-ratio-<?php echo esc_attr($ratio); ?>" style="<?php echo esc_attr($ratio_style !== '' ? $ratio_style : 'height: ' . $height . 'px;'); ?>">
                <iframe src="<?php echo esc_url($src); ?>" title="<?php echo esc_attr($title); ?>" loading="<?php echo esc_attr($loading === 'eager' ? 'eager' : 'lazy'); ?>" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
    </div>
</section>
