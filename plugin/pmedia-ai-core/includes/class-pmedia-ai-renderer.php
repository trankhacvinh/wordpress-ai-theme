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

        $atts = shortcode_atts([
            'id' => get_the_ID(),
        ], $atts, 'pmedia_sections');

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
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }

        return $decoded;
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
            if (!is_array($component)) {
                continue;
            }

            self::render_section($component);
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
        $component_id = sanitize_key((string) self::get_value($section, 'id', ''));

        $classes = array_filter([
            $base_class,
            'pmedia-' . $type . '-section',
            $variant !== '' && $variant !== 'default' ? 'pmedia-variant-' . $variant : '',
            $bg !== '' && $bg !== 'none' ? 'pmedia-bg-' . $bg : '',
        ]);

        $attrs = 'class="' . esc_attr(implode(' ', $classes)) . '" data-component="' . esc_attr($type) . '"';
        if ($component_id !== '') {
            $attrs .= ' id="' . esc_attr($component_id) . '"';
        }
        if ($animation !== '' && $animation !== 'none') {
            $attrs .= ' data-animation="' . esc_attr($animation) . '"';
        }

        return $attrs;
    }

    public static function print_seo_meta(): void
    {
        if (!is_singular()) {
            return;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return;
        }

        $description = get_post_meta($post_id, '_pmedia_seo_description', true);
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
        if (!$post_id) {
            return $title;
        }

        $seo_title = get_post_meta($post_id, '_pmedia_seo_title', true);
        if ($seo_title) {
            $title['title'] = (string) $seo_title;
        }

        return $title;
    }

    private static function render_fallback_section(array $section, string $type): void
    {
        echo '<section ' . self::attrs($section, 'pmedia-section pmedia-section-' . sanitize_html_class($type)) . '><div class="pmedia-container">';

        switch ($type) {
            case 'hero':
                self::render_hero($section);
                break;
            case 'services':
                self::render_card_list($section, 'Dịch vụ');
                break;
            case 'pricing':
                self::render_pricing($section);
                break;
            case 'faq':
                self::render_faq($section);
                break;
            case 'cta':
                self::render_cta($section);
                break;
            case 'contact':
                self::render_contact($section);
                break;
            default:
                self::render_content($section);
                break;
        }

        echo '</div></section>';
    }

    private static function render_hero(array $section): void
    {
        if (self::get_value($section, 'eyebrow')) {
            echo '<p class="pmedia-eyebrow">' . esc_html((string) self::get_value($section, 'eyebrow')) . '</p>';
        }
        if (self::get_value($section, 'title')) {
            echo '<h1>' . esc_html((string) self::get_value($section, 'title')) . '</h1>';
        }
        if (self::get_value($section, 'description')) {
            echo '<p class="pmedia-lead">' . esc_html((string) self::get_value($section, 'description')) . '</p>';
        }
        self::render_buttons($section);
    }

    private static function render_card_list(array $section, string $fallback_title): void
    {
        self::render_heading($section, $fallback_title);
        $items = self::get_value($section, 'items', []);
        if (!is_array($items) || empty($items)) {
            return;
        }

        echo '<div class="pmedia-grid">';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            echo '<article class="pmedia-card">';
            if (!empty($item['title'])) {
                echo '<h3>' . esc_html((string) $item['title']) . '</h3>';
            }
            if (!empty($item['description'])) {
                echo '<p>' . esc_html((string) $item['description']) . '</p>';
            }
            echo '</article>';
        }
        echo '</div>';
    }

    private static function render_pricing(array $section): void
    {
        self::render_heading($section, 'Bảng giá');
        $items = self::get_value($section, 'items', []);
        if (!is_array($items) || empty($items)) {
            return;
        }

        echo '<div class="pmedia-grid pmedia-pricing-grid">';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            echo '<article class="pmedia-card pmedia-price-card">';
            if (!empty($item['name'])) {
                echo '<h3>' . esc_html((string) $item['name']) . '</h3>';
            }
            if (!empty($item['price'])) {
                echo '<p class="pmedia-price">' . esc_html((string) $item['price']) . '</p>';
            }
            $features = $item['features'] ?? [];
            if (is_array($features) && !empty($features)) {
                echo '<ul>';
                foreach ($features as $feature) {
                    echo '<li>' . esc_html((string) $feature) . '</li>';
                }
                echo '</ul>';
            }
            echo '</article>';
        }
        echo '</div>';
    }

    private static function render_faq(array $section): void
    {
        self::render_heading($section, 'FAQ');
        $items = self::get_value($section, 'items', []);
        if (!is_array($items) || empty($items)) {
            return;
        }

        echo '<div class="pmedia-faq-list">';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            echo '<details class="pmedia-faq-item">';
            echo '<summary>' . esc_html((string) ($item['question'] ?? 'Câu hỏi')) . '</summary>';
            if (!empty($item['answer'])) {
                echo '<p>' . esc_html((string) $item['answer']) . '</p>';
            }
            echo '</details>';
        }
        echo '</div>';
    }

    private static function render_cta(array $section): void
    {
        echo '<div class="pmedia-cta-box">';
        self::render_heading($section, 'Liên hệ');
        self::render_buttons($section);
        echo '</div>';
    }

    private static function render_contact(array $section): void
    {
        self::render_heading($section, 'Liên hệ');
        $items = ['phone' => 'Điện thoại', 'email' => 'Email', 'address' => 'Địa chỉ'];
        echo '<div class="pmedia-contact-list">';
        foreach ($items as $key => $label) {
            $value = self::get_value($section, $key, '');
            if (!$value) {
                continue;
            }
            echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html((string) $value) . '</p>';
        }
        echo '</div>';
    }

    private static function render_content(array $section): void
    {
        self::render_heading($section, 'Nội dung');
        $content = self::get_value($section, 'content', '');
        if ($content) {
            echo '<div class="pmedia-content">' . wp_kses_post((string) $content) . '</div>';
        }
        self::render_components(self::get_children($section));
    }

    private static function render_heading(array $section, string $fallback_title): void
    {
        if (self::get_value($section, 'eyebrow')) {
            echo '<p class="pmedia-eyebrow">' . esc_html((string) self::get_value($section, 'eyebrow')) . '</p>';
        }

        echo '<h2>' . esc_html((string) self::get_value($section, 'title', $fallback_title)) . '</h2>';

        if (self::get_value($section, 'description')) {
            echo '<p class="pmedia-section-description">' . esc_html((string) self::get_value($section, 'description')) . '</p>';
        }
    }

    private static function render_buttons(array $section): void
    {
        $primary_text = self::get_value($section, 'button_text', '');
        $primary_link = self::get_value($section, 'button_link', '#');
        $secondary_text = self::get_value($section, 'secondary_button_text', '');
        $secondary_link = self::get_value($section, 'secondary_button_link', '#');

        if (!$primary_text && !$secondary_text) {
            return;
        }

        echo '<div class="pmedia-actions">';
        if ($primary_text) {
            echo '<a class="pmedia-btn pmedia-btn-primary" href="' . esc_url((string) $primary_link) . '">' . esc_html((string) $primary_text) . '</a>';
        }
        if ($secondary_text) {
            echo '<a class="pmedia-btn pmedia-btn-secondary" href="' . esc_url((string) $secondary_link) . '">' . esc_html((string) $secondary_text) . '</a>';
        }
        echo '</div>';
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
