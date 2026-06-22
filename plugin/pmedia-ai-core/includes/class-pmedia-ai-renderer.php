<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Renderer
{
    public static function hooks(): void
    {
        add_shortcode('pmedia_sections', [self::class, 'shortcode']);
        add_action('wp_head', [self::class, 'print_seo_meta'], 2);
        add_filter('document_title_parts', [self::class, 'filter_document_title']);
    }

    public static function shortcode($atts = []): string
    {
        if (!is_array($atts)) {
            $atts = [];
        }

        $atts = shortcode_atts(['id' => get_the_ID()], $atts, 'pmedia_sections');

        ob_start();
        self::render_sections((int) $atts['id']);
        return (string) ob_get_clean();
    }

    public static function get_sections(int $post_id = 0): array
    {
        if ($post_id <= 0) {
            $post_id = get_the_ID();
        }
        if (!$post_id) {
            return [];
        }

        $raw = get_post_meta($post_id, '_pmedia_sections', true);
        if (is_array($raw)) {
            return $raw;
        }
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }

    public static function has_sections(int $post_id = 0): bool
    {
        return count(self::get_sections($post_id)) > 0;
    }

    public static function render_sections(int $post_id = 0): void
    {
        self::render_components(self::get_sections($post_id));
    }

    public static function render_components(array $components): void
    {
        foreach ($components as $component) {
            if (is_array($component)) {
                self::render_section($component);
            }
        }
    }

    public static function render_section(array $section): void
    {
        $type = sanitize_key(self::get_value($section, 'type', 'content'));
        if ($type === '') {
            $type = 'content';
        }

        $template = locate_template('sections/' . $type . '.php');
        if ($template) {
            $pmedia_section = $section;
            include $template;
            return;
        }

        self::render_fallback_section($section, $type);
    }

    public static function get_value(array $section, string $key, $default = '')
    {
        if (array_key_exists($key, $section)) {
            return $section[$key];
        }
        if (isset($section['settings']) && is_array($section['settings']) && array_key_exists($key, $section['settings'])) {
            return $section['settings'][$key];
        }
        return $default;
    }

    public static function get_children(array $section): array
    {
        $children = self::get_value($section, 'children', []);
        return is_array($children) ? $children : [];
    }

    public static function attrs(array $section, string $base_class = 'pmedia-section'): string
    {
        $type = sanitize_key((string) self::get_value($section, 'type', 'content'));
        $variant = sanitize_key((string) self::get_value($section, 'variant', 'default'));
        $animation = sanitize_key((string) self::get_value($section, 'animation', 'none'));
        $bg = sanitize_key((string) self::get_value($section, 'background_effect', 'none'));
        $component_id = sanitize_key((string) self::get_value($section, 'custom_id', self::get_value($section, 'id', '')));
        $custom_class = (string) self::get_value($section, 'custom_class', '');
        $style_vars = self::normalize_key_value(self::get_value($section, 'style_vars', []));
        $data_attrs = self::normalize_key_value(self::get_value($section, 'data_attrs', []));

        $classes = array_filter([
            $base_class,
            'pmedia-' . $type . '-section',
            $variant !== '' && $variant !== 'default' ? 'pmedia-variant-' . $variant : '',
            $bg !== '' && $bg !== 'none' ? 'pmedia-bg-' . $bg : '',
        ]);

        foreach (preg_split('/\s+/', $custom_class) ?: [] as $class) {
            $class = sanitize_html_class($class);
            if ($class !== '') {
                $classes[] = $class;
            }
        }

        $attrs = 'class="' . esc_attr(implode(' ', array_unique($classes))) . '" data-component="' . esc_attr($type) . '"';
        if ($component_id !== '') {
            $attrs .= ' id="' . esc_attr($component_id) . '"';
        }
        if ($animation !== '' && $animation !== 'none') {
            $attrs .= ' data-animation="' . esc_attr($animation) . '"';
        }
        foreach ($data_attrs as $key => $value) {
            $key = sanitize_key((string) $key);
            if ($key !== '') {
                $attrs .= ' data-' . esc_attr($key) . '="' . esc_attr((string) $value) . '"';
            }
        }
        if (!empty($style_vars)) {
            $style = [];
            foreach ($style_vars as $key => $value) {
                $key = self::sanitize_css_var((string) $key);
                if ($key !== '') {
                    $style[] = $key . ':' . esc_attr((string) $value);
                }
            }
            if (!empty($style)) {
                $attrs .= ' style="' . esc_attr(implode(';', $style)) . '"';
            }
        }

        return $attrs;
    }

    private static function normalize_key_value($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private static function sanitize_css_var(string $key): string
    {
        $key = trim($key);
        if (strpos($key, '--') !== 0) {
            return '';
        }
        return preg_match('/^--[a-zA-Z0-9_-]+$/', $key) ? $key : '';
    }

    public static function print_seo_meta(): void
    {
        if (!is_singular()) {
            return;
        }
        $post_id = get_queried_object_id();
        $description = $post_id ? get_post_meta($post_id, '_pmedia_seo_description', true) : '';
        if ($description) {
            printf("\n<meta name=\"description\" content=\"%s\">\n", esc_attr((string) $description));
        }
    }

    public static function filter_document_title(array $title): array
    {
        if (!is_singular()) {
            return $title;
        }
        $post_id = get_queried_object_id();
        $seo_title = $post_id ? get_post_meta($post_id, '_pmedia_seo_title', true) : '';
        if ($seo_title) {
            $title['title'] = (string) $seo_title;
        }
        return $title;
    }

    private static function render_fallback_section(array $section, string $type): void
    {
        echo '<section ' . self::attrs($section, 'pmedia-section pmedia-section-' . sanitize_html_class($type)) . '><div class="pmedia-container">';
        self::render_content($section);
        echo '</div></section>';
    }

    private static function render_content(array $section): void
    {
        if (self::get_value($section, 'eyebrow')) {
            echo '<p class="pmedia-eyebrow">' . esc_html((string) self::get_value($section, 'eyebrow')) . '</p>';
        }
        if (self::get_value($section, 'title')) {
            echo '<h2>' . esc_html((string) self::get_value($section, 'title')) . '</h2>';
        }
        if (self::get_value($section, 'description')) {
            echo '<p class="pmedia-section-description">' . esc_html((string) self::get_value($section, 'description')) . '</p>';
        }
        $content = self::get_value($section, 'content', '');
        if ($content) {
            echo '<div class="pmedia-content">' . wp_kses_post((string) $content) . '</div>';
        }
        self::render_components(self::get_children($section));
    }
}

function pmedia_ai_get_sections(int $post_id = 0): array
{
    return PMEDIA_AI_Renderer::get_sections($post_id);
}

function pmedia_ai_has_sections(int $post_id = 0): bool
{
    return PMEDIA_AI_Renderer::has_sections($post_id);
}

function pmedia_ai_render_sections(int $post_id = 0): void
{
    PMEDIA_AI_Renderer::render_sections($post_id);
}

function pmedia_ai_render_components(array $components): void
{
    PMEDIA_AI_Renderer::render_components($components);
}

function pmedia_ai_section_value(array $section, string $key, $default = '')
{
    return PMEDIA_AI_Renderer::get_value($section, $key, $default);
}

function pmedia_ai_section_children(array $section): array
{
    return PMEDIA_AI_Renderer::get_children($section);
}

function pmedia_ai_section_attrs(array $section, string $base_class = 'pmedia-section'): string
{
    return PMEDIA_AI_Renderer::attrs($section, $base_class);
}
