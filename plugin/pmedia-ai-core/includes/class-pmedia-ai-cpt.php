<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_CPT
{
    public static function hooks(): void
    {
        add_action('init', [self::class, 'register_post_types']);
    }

    public static function register_post_types(): void
    {
        self::register_service_post_type();
        self::register_project_post_type();
        self::register_testimonial_post_type();
    }

    private static function register_service_post_type(): void
    {
        register_post_type('pmedia_service', [
            'labels' => [
                'name' => __('Services', 'pmedia-ai-core'),
                'singular_name' => __('Service', 'pmedia-ai-core'),
                'add_new_item' => __('Add New Service', 'pmedia-ai-core'),
                'edit_item' => __('Edit Service', 'pmedia-ai-core'),
            ],
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-screenoptions',
            'has_archive' => true,
            'rewrite' => ['slug' => 'dich-vu'],
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields'],
        ]);
    }

    private static function register_project_post_type(): void
    {
        register_post_type('pmedia_project', [
            'labels' => [
                'name' => __('Projects', 'pmedia-ai-core'),
                'singular_name' => __('Project', 'pmedia-ai-core'),
                'add_new_item' => __('Add New Project', 'pmedia-ai-core'),
                'edit_item' => __('Edit Project', 'pmedia-ai-core'),
            ],
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-portfolio',
            'has_archive' => true,
            'rewrite' => ['slug' => 'du-an'],
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields'],
        ]);
    }

    private static function register_testimonial_post_type(): void
    {
        register_post_type('pmedia_testimonial', [
            'labels' => [
                'name' => __('Testimonials', 'pmedia-ai-core'),
                'singular_name' => __('Testimonial', 'pmedia-ai-core'),
                'add_new_item' => __('Add New Testimonial', 'pmedia-ai-core'),
                'edit_item' => __('Edit Testimonial', 'pmedia-ai-core'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-format-quote',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
        ]);
    }
}
