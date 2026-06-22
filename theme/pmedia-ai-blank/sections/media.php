<?php
if (!defined('ABSPATH')) {
    exit;
}

$type = sanitize_key((string) pmedia_ai_section_value($pmedia_section, 'media_type', 'image'));
$url = (string) pmedia_ai_section_value($pmedia_section, 'url', '');
$poster = (string) pmedia_ai_section_value($pmedia_section, 'poster', '');
$alt = (string) pmedia_ai_section_value($pmedia_section, 'alt', '');
$caption = (string) pmedia_ai_section_value($pmedia_section, 'caption', '');
$link = (string) pmedia_ai_section_value($pmedia_section, 'link', '');
$ratio_raw = (string) pmedia_ai_section_value($pmedia_section, 'aspect_ratio', 'auto');
$ratio = sanitize_html_class(str_replace('/', '-', $ratio_raw));
$ratio_style = in_array($ratio_raw, ['1/1', '4/3', '16/9', '21/9'], true) ? 'aspect-ratio: ' . str_replace('/', ' / ', $ratio_raw) . ';' : '';
$fit = sanitize_html_class((string) pmedia_ai_section_value($pmedia_section, 'object_fit', 'cover'));
$controls = (bool) pmedia_ai_section_value($pmedia_section, 'controls', true);
$autoplay = (bool) pmedia_ai_section_value($pmedia_section, 'autoplay', false);
$muted = (bool) pmedia_ai_section_value($pmedia_section, 'muted', true);
$loop = (bool) pmedia_ai_section_value($pmedia_section, 'loop', false);
?>
<section <?php echo pmedia_ai_section_attrs($pmedia_section, 'pmedia-section pmedia-media-section'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <div class="pmedia-container">
        <figure class="pmedia-media pmedia-ratio-<?php echo esc_attr($ratio); ?> pmedia-object-<?php echo esc_attr($fit); ?>" <?php if ($ratio_style !== '') : ?>style="<?php echo esc_attr($ratio_style); ?>"<?php endif; ?>>
            <?php if ($url !== '' && $type === 'video') : ?>
                <video class="pmedia-media-el" <?php echo $controls ? 'controls' : ''; ?> <?php echo $autoplay ? 'autoplay playsinline' : ''; ?> <?php echo $muted ? 'muted' : ''; ?> <?php echo $loop ? 'loop' : ''; ?> <?php if ($poster !== '') : ?>poster="<?php echo esc_url($poster); ?>"<?php endif; ?>>
                    <source src="<?php echo esc_url($url); ?>">
                </video>
            <?php elseif ($url !== '') : ?>
                <?php if ($link !== '') : ?><a href="<?php echo esc_url($link); ?>"><?php endif; ?>
                <img class="pmedia-media-el" src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
                <?php if ($link !== '') : ?></a><?php endif; ?>
            <?php endif; ?>
            <?php if ($caption !== '') : ?><figcaption><?php echo wp_kses_post($caption); ?></figcaption><?php endif; ?>
        </figure>
    </div>
</section>
