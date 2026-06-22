<?php
if (!defined('ABSPATH')) {
    exit;
}

$html = (string) pmedia_ai_section_value($pmedia_section, 'html', '');
$allow_shortcode = (bool) pmedia_ai_section_value($pmedia_section, 'allow_shortcode', false);
if ($allow_shortcode) {
    $html = do_shortcode($html);
}
?>
<section <?php echo pmedia_ai_section_attrs($pmedia_section, 'pmedia-section pmedia-html-section'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="pmedia-container">
        <div class="pmedia-html-block">
            <?php echo wp_kses_post($html); ?>
        </div>
    </div>
</section>
