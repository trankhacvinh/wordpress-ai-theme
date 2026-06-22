<?php
if (!defined('ABSPATH')) {
    exit;
}

$title = (string) pmedia_ai_section_value($pmedia_section, 'title', '');
$description = (string) pmedia_ai_section_value($pmedia_section, 'description', '');
$url = (string) pmedia_ai_section_value($pmedia_section, 'video_url', '');
$poster = (string) pmedia_ai_section_value($pmedia_section, 'poster', '');
$ratio = sanitize_html_class((string) pmedia_ai_section_value($pmedia_section, 'aspect_ratio', '16/9'));
$controls = (bool) pmedia_ai_section_value($pmedia_section, 'controls', true);
$autoplay = (bool) pmedia_ai_section_value($pmedia_section, 'autoplay', false);
$muted = (bool) pmedia_ai_section_value($pmedia_section, 'muted', true);
$loop = (bool) pmedia_ai_section_value($pmedia_section, 'loop', false);
?>
<section <?php echo pmedia_ai_section_attrs($pmedia_section, 'pmedia-section pmedia-video-section'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="pmedia-container">
        <?php if ($title !== '' || $description !== '') : ?>
            <div class="pmedia-section-heading">
                <?php if ($title !== '') : ?><h2><?php echo esc_html($title); ?></h2><?php endif; ?>
                <?php if ($description !== '') : ?><p><?php echo esc_html($description); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($url !== '') : ?>
            <div class="pmedia-video-wrap pmedia-ratio-<?php echo esc_attr($ratio); ?>">
                <video class="pmedia-video" <?php echo $controls ? 'controls' : ''; ?> <?php echo $autoplay ? 'autoplay playsinline' : ''; ?> <?php echo $muted ? 'muted' : ''; ?> <?php echo $loop ? 'loop' : ''; ?> <?php if ($poster !== '') : ?>poster="<?php echo esc_url($poster); ?>"<?php endif; ?>>
                    <source src="<?php echo esc_url($url); ?>">
                </video>
            </div>
        <?php endif; ?>
    </div>
</section>
