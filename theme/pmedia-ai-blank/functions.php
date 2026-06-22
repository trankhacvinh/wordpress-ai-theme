<?php

if (!defined('ABSPATH')) {
    exit;
}

define('PMEDIA_AI_BLANK_VERSION', '1.0.0');
define('PMEDIA_AI_BLANK_DIR', get_template_directory());
define('PMEDIA_AI_BLANK_URI', get_template_directory_uri());

add_action('after_setup_theme', 'pmedia_ai_blank_setup');
function pmedia_ai_blank_setup(): void
{
    load_theme_textdomain('pmedia-ai-blank', PMEDIA_AI_BLANK_DIR . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height' => 80,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
    ]);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');

    register_nav_menus([
        'primary' => __('Primary Menu', 'pmedia-ai-blank'),
        'footer' => __('Footer Menu', 'pmedia-ai-blank'),
    ]);
}

add_action('wp_enqueue_scripts', 'pmedia_ai_blank_enqueue_assets');
function pmedia_ai_blank_enqueue_assets(): void
{
    wp_enqueue_style('pmedia-ai-blank-main', PMEDIA_AI_BLANK_URI . '/assets/css/main.css', [], PMEDIA_AI_BLANK_VERSION);
    wp_enqueue_script('pmedia-ai-blank-main', PMEDIA_AI_BLANK_URI . '/assets/js/main.js', [], PMEDIA_AI_BLANK_VERSION, true);
}

add_filter('body_class', 'pmedia_ai_blank_body_classes');
function pmedia_ai_blank_body_classes(array $classes): array
{
    $classes[] = 'pmedia-ai-theme';

    if (function_exists('pmedia_ai_has_sections') && is_singular() && pmedia_ai_has_sections(get_queried_object_id())) {
        $classes[] = 'has-pmedia-sections';
    }

    return $classes;
}

function pmedia_ai_blank_render_logo(): void
{
    if (has_custom_logo()) {
        the_custom_logo();
        return;
    }

    printf('<a class="site-brand-text" href="%1$s" rel="home">%2$s</a>', esc_url(home_url('/')), esc_html(get_bloginfo('name')));
}

function pmedia_ai_blank_render_content_fallback(): void
{
    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('pmedia-entry'); ?>>
        <header class="pmedia-entry-header">
            <?php the_title('<h1 class="pmedia-entry-title">', '</h1>'); ?>
        </header>
        <div class="pmedia-entry-content">
            <?php the_content(); ?>
        </div>
    </article>
    <?php
}
