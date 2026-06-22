<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e('Skip to content', 'pmedia-ai-blank'); ?></a>

<header class="site-header">
    <div class="pmedia-container site-header-inner">
        <div class="site-brand">
            <?php pmedia_ai_blank_render_logo(); ?>
        </div>

        <button class="site-menu-toggle" type="button" aria-expanded="false" aria-controls="site-primary-menu">
            <span></span>
            <span></span>
            <span></span>
            <span class="screen-reader-text"><?php esc_html_e('Menu', 'pmedia-ai-blank'); ?></span>
        </button>

        <nav class="site-navigation" aria-label="<?php esc_attr_e('Primary menu', 'pmedia-ai-blank'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'menu_id' => 'site-primary-menu',
                'menu_class' => 'site-menu',
                'fallback_cb' => false,
                'depth' => 2,
            ]);
            ?>
        </nav>
    </div>
</header>

<main id="main-content" class="site-main">
