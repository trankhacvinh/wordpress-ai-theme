<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Meta_Boxes
{
    public static function hooks(): void
    {
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
        add_action('save_post', [self::class, 'save_meta_boxes']);
        add_action('admin_notices', [self::class, 'render_admin_notices']);
    }

    public static function add_meta_boxes(): void
    {
        $screens = ['page', 'post', 'pmedia_service', 'pmedia_project'];

        foreach ($screens as $screen) {
            add_meta_box('pmedia-ai-sections', __('PMEDIA AI Section Builder', 'pmedia-ai-core'), [self::class, 'render_sections_meta_box'], $screen, 'normal', 'high');
            add_meta_box('pmedia-ai-seo', __('PMEDIA AI SEO', 'pmedia-ai-core'), [self::class, 'render_seo_meta_box'], $screen, 'side', 'default');
        }
    }

    public static function render_sections_meta_box(WP_Post $post): void
    {
        wp_nonce_field('pmedia_ai_save_meta', 'pmedia_ai_meta_nonce');

        $sections = get_post_meta($post->ID, '_pmedia_sections', true);
        if (empty($sections)) {
            $sections = PMEDIA_AI_Section_Schema::sample_sections_json();
        }

        $schema = PMEDIA_AI_Component_Registry::schema();
        ?>
        <div class="pmedia-ai-builder" data-builder="pmedia-ai-sections">
            <p class="description">
                Quản lý layout bằng section/component. Hỗ trợ component nâng cao như modal, gallery, slider, tabs, accordion, portfolio. Với cấu trúc lồng nhau, dùng tab JSON hoặc các field JSON nâng cao.
            </p>

            <textarea name="pmedia_sections" class="pmedia-ai-sections-json" hidden><?php echo esc_textarea((string) $sections); ?></textarea>

            <div class="pmedia-ai-builder-toolbar">
                <select class="pmedia-ai-section-type">
                    <?php foreach ($schema as $type => $config) : ?>
                        <option value="<?php echo esc_attr((string) $type); ?>"><?php echo esc_html((string) ($config['label'] ?? $type)); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="button button-primary pmedia-ai-add-section">Thêm section</button>
                <button type="button" class="button pmedia-ai-expand-all">Mở tất cả</button>
                <button type="button" class="button pmedia-ai-collapse-all">Thu gọn tất cả</button>
                <button type="button" class="button pmedia-ai-toggle-json">Xem JSON</button>
            </div>

            <div class="pmedia-ai-builder-list" aria-live="polite"></div>

            <div class="pmedia-ai-json-panel" hidden>
                <p><strong>JSON section</strong></p>
                <textarea rows="22" class="large-text code pmedia-ai-json-editor"><?php echo esc_textarea((string) $sections); ?></textarea>
                <p>
                    <button type="button" class="button pmedia-ai-apply-json">Áp dụng JSON vào builder</button>
                    <span class="description">Dùng khi muốn copy JSON từ AI hoặc chỉnh nhanh bằng tay. Có thể dùng <code>children</code>, <code>settings</code>, <code>modal</code>.</span>
                </p>
            </div>
        </div>
        <?php
    }

    public static function render_seo_meta_box(WP_Post $post): void
    {
        $seo_title = get_post_meta($post->ID, '_pmedia_seo_title', true);
        $seo_description = get_post_meta($post->ID, '_pmedia_seo_description', true);
        ?>
        <p>
            <label for="pmedia_seo_title"><strong>SEO Title</strong></label>
            <input type="text" id="pmedia_seo_title" name="pmedia_seo_title" value="<?php echo esc_attr((string) $seo_title); ?>" class="widefat" />
        </p>
        <p>
            <label for="pmedia_seo_description"><strong>SEO Description</strong></label>
            <textarea id="pmedia_seo_description" name="pmedia_seo_description" rows="5" class="widefat"><?php echo esc_textarea((string) $seo_description); ?></textarea>
        </p>
        <?php
    }

    public static function save_meta_boxes(int $post_id): void
    {
        if (!isset($_POST['pmedia_ai_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pmedia_ai_meta_nonce'])), 'pmedia_ai_save_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['pmedia_sections'])) {
            $sections_raw = wp_unslash($_POST['pmedia_sections']);
            $sections_raw = self::normalize_json_string($sections_raw);

            if ($sections_raw === '') {
                delete_post_meta($post_id, '_pmedia_sections');
            } elseif (self::is_valid_json($sections_raw)) {
                update_post_meta($post_id, '_pmedia_sections', $sections_raw);
            } else {
                self::set_admin_notice(__('PMEDIA AI Sections JSON không hợp lệ. Dữ liệu cũ được giữ nguyên.', 'pmedia-ai-core'), 'error');
            }
        }

        if (isset($_POST['pmedia_seo_title'])) {
            update_post_meta($post_id, '_pmedia_seo_title', sanitize_text_field(wp_unslash($_POST['pmedia_seo_title'])));
        }

        if (isset($_POST['pmedia_seo_description'])) {
            update_post_meta($post_id, '_pmedia_seo_description', sanitize_textarea_field(wp_unslash($_POST['pmedia_seo_description'])));
        }
    }

    public static function render_admin_notices(): void
    {
        self::render_notice_transient('pmedia_ai_admin_notice_');
        PMEDIA_AI_Site_Generator::maybe_render_notice();
    }

    public static function sample_sections_json(): string
    {
        return PMEDIA_AI_Section_Schema::sample_sections_json();
    }

    private static function normalize_json_string($value): string
    {
        if (!is_string($value)) {
            return '';
        }
        return trim(wp_check_invalid_utf8($value));
    }

    private static function is_valid_json(string $json): bool
    {
        json_decode($json, true);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private static function set_admin_notice(string $message, string $type = 'info'): void
    {
        set_transient('pmedia_ai_admin_notice_' . get_current_user_id(), ['message' => $message, 'type' => $type], 60);
    }

    private static function render_notice_transient(string $prefix): void
    {
        $notice = get_transient($prefix . get_current_user_id());
        if (!$notice || !is_array($notice)) {
            return;
        }

        delete_transient($prefix . get_current_user_id());
        $type = $notice['type'] ?? 'info';
        $message = $notice['message'] ?? '';
        if ($message === '') {
            return;
        }

        printf('<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr($type), esc_html($message));
    }
}
