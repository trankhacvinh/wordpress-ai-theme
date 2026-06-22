<?php
if (!defined('ABSPATH')) {
    exit;
}

$setting = static function (string $key, $default = '') {
    return function_exists('pmedia_ai_setting') ? pmedia_ai_setting($key, $default) : $default;
};

$parse_links = static function (string $text): array {
    if (function_exists('pmedia_ai_parse_links')) {
        return pmedia_ai_parse_links($text);
    }

    $items = [];
    foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '[') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line, 2));
        if (!empty($parts[0])) {
            $items[] = ['label' => $parts[0], 'url' => $parts[1] ?? '#'];
        }
    }

    return $items;
};

$render_manual_menu = static function (array $items, string $class): void {
    if (empty($items)) {
        return;
    }
    echo '<ul class="' . esc_attr($class) . '">';
    foreach ($items as $item) {
        echo '<li><a href="' . esc_url((string) ($item['url'] ?? '#')) . '">' . esc_html((string) ($item['label'] ?? 'Link')) . '</a></li>';
    }
    echo '</ul>';
};

$brand = (string) $setting('brand_name', get_bloginfo('name'));
$logo = (string) $setting('logo_url', '');
$mobile_logo = (string) $setting('mobile_logo_url', '');
$nav_mode = (string) $setting('nav_mode', 'wordpress');
$manual_nav = (string) $setting('manual_nav', '');
$mobile_nav_mode = (string) $setting('mobile_nav_mode', 'inherit');
$mobile_nav = (string) $setting('mobile_nav', '');
$header_layout = sanitize_html_class((string) $setting('header_layout', 'default'));
$sticky_header = !empty($setting('sticky_header', '1'));
$desktop_cta = !empty($setting('desktop_header_cta_visible', '1'));
$mobile_cta = !empty($setting('mobile_header_cta_visible', ''));
$cta_text = (string) $setting('header_cta_text', '');
$cta_link = (string) $setting('header_cta_link', '/lien-he');

$render_logo = static function () use ($brand, $logo, $mobile_logo): void {
    if ($logo !== '') {
        echo '<a class="site-logo-link" href="' . esc_url(home_url('/')) . '" rel="home">';
        echo '<img class="site-logo site-logo-desktop ' . ($mobile_logo !== '' ? 'has-mobile-logo' : '') . '" src="' . esc_url($logo) . '" alt="' . esc_attr($brand) . '">';
        if ($mobile_logo !== '') {
            echo '<img class="site-logo site-logo-mobile" src="' . esc_url($mobile_logo) . '" alt="' . esc_attr($brand) . '">';
        }
        echo '</a>';
        return;
    }

    if (has_custom_logo()) {
        the_custom_logo();
        return;
    }

    echo '<a class="site-brand-text" href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html($brand) . '</a>';
};

$render_menu = static function (string $context = 'desktop') use ($nav_mode, $manual_nav, $mobile_nav_mode, $mobile_nav, $parse_links, $render_manual_menu): void {
    if ($context === 'mobile' && $mobile_nav_mode === 'manual' && trim($mobile_nav) !== '') {
        $render_manual_menu($parse_links($mobile_nav), 'site-menu site-mobile-menu-list');
        return;
    }

    if ($nav_mode === 'manual') {
        $render_manual_menu($parse_links($manual_nav), 'site-menu');
        return;
    }

    wp_nav_menu([
        'theme_location' => 'primary',
        'container' => false,
        'menu_id' => $context === 'mobile' ? 'site-mobile-menu' : 'site-primary-menu',
        'menu_class' => 'site-menu',
        'fallback_cb' => false,
        'depth' => 2,
    ]);
};
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/global-layout.css'); ?>">
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e('Skip to content', 'pmedia-ai-blank'); ?></a>

<header class="site-header site-header-layout-<?php echo esc_attr($header_layout); ?> <?php echo $sticky_header ? 'is-sticky' : 'is-static'; ?>">
    <div class="pmedia-container site-header-inner">
        <div class="site-brand">
            <?php $render_logo(); ?>
        </div>

        <nav class="site-navigation" aria-label="<?php esc_attr_e('Primary menu', 'pmedia-ai-blank'); ?>">
            <?php $render_menu('desktop'); ?>
        </nav>

        <div class="site-header-actions">
            <?php if ($desktop_cta && $cta_text !== '') : ?>
                <a class="pmedia-btn pmedia-btn-primary site-header-cta site-header-cta-desktop" href="<?php echo esc_url($cta_link); ?>"><?php echo esc_html($cta_text); ?></a>
            <?php endif; ?>

            <button class="site-menu-toggle" type="button" aria-expanded="false" aria-controls="site-mobile-panel">
                <span></span>
                <span></span>
                <span></span>
                <span class="screen-reader-text"><?php esc_html_e('Menu', 'pmedia-ai-blank'); ?></span>
            </button>
        </div>

        <div id="site-mobile-panel" class="site-mobile-panel" aria-label="<?php esc_attr_e('Mobile menu', 'pmedia-ai-blank'); ?>">
            <?php $render_menu('mobile'); ?>
            <?php if ($mobile_cta && $cta_text !== '') : ?>
                <a class="pmedia-btn pmedia-btn-primary site-header-cta site-header-cta-mobile" href="<?php echo esc_url($cta_link); ?>"><?php echo esc_html($cta_text); ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main id="main-content" class="site-main">
