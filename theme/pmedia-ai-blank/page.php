<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) {
    the_post();

    if (function_exists('pmedia_ai_has_sections') && pmedia_ai_has_sections(get_the_ID())) {
        pmedia_ai_render_sections(get_the_ID());
    } else {
        echo '<div class="pmedia-container pmedia-page-container">';
        pmedia_ai_blank_render_content_fallback();
        echo '</div>';
    }
}

get_footer();
