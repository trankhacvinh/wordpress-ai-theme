<?php
if (!defined('ABSPATH')) {
    exit;
}

$shortcode = (string) pmedia_ai_section_value($pmedia_section, 'shortcode', '');
$wrapper = sanitize_html_class((string) pmedia_ai_section_value($pmedia_section, 'wrapper', 'plain'));
?>
<section <?php echo pmedia_ai_section_attrs($pmedia_section, 'pmedia-section pmedia-shortcode-section'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="pmedia-container">
        <div class="pmedia-shortcode-wrap pmedia-shortcode-<?php echo esc_attr($wrapper); ?>">
            <?php echo do_shortcode($shortcode); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
</section>
