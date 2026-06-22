<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Custom_Code
{
    public const OPTION_NAME = 'pmedia_ai_custom_code';

    public static function hooks(): void
    {
        add_action('admin_menu', [self::class, 'register_submenu']);
        add_action('admin_post_pmedia_ai_save_custom_code', [self::class, 'handle_save_global']);
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
        add_action('save_post', [self::class, 'save_post_meta']);
        add_action('wp_head', [self::class, 'print_global_head'], 98);
        add_action('wp_head', [self::class, 'print_page_head'], 99);
        add_action('wp_footer', [self::class, 'print_global_footer'], 98);
        add_action('wp_footer', [self::class, 'print_page_footer'], 99);
        add_filter('body_class', [self::class, 'add_body_classes']);
    }

    public static function defaults(): array
    {
        return [
            'enabled' => '1',
            'global_css' => '',
            'global_js_head' => '',
            'global_js_footer' => '',
            'before_body_html' => '',
        ];
    }

    public static function settings(): array
    {
        $settings = get_option(self::OPTION_NAME, []);
        return array_merge(self::defaults(), is_array($settings) ? $settings : []);
    }

    public static function register_submenu(): void
    {
        add_submenu_page(
            'pmedia-ai-core',
            __('Custom Code', 'pmedia-ai-core'),
            __('Custom Code', 'pmedia-ai-core'),
            'manage_options',
            'pmedia-ai-custom-code',
            [self::class, 'render_admin_page']
        );
    }

    public static function render_admin_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $s = self::settings();
        ?>
        <div class="wrap pmedia-ai-admin-page pmedia-ai-custom-code-page">
            <h1>PMEDIA AI Custom Code</h1>
            <p class="description">Quản lý CSS/JS dùng chung toàn site. JS/HTML chỉ nên dùng bởi admin/dev vì có thể làm lỗi frontend nếu dán sai.</p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="pmedia_ai_save_custom_code">
                <?php wp_nonce_field('pmedia_ai_save_custom_code', 'pmedia_ai_custom_code_nonce'); ?>

                <div class="pmedia-ai-card pmedia-ai-card-wide">
                    <p><label><input type="checkbox" name="enabled" value="1" <?php checked(!empty($s['enabled'])); ?>> <strong>Bật Global Custom Code</strong></label></p>
                    <p class="description">Tắt mục này nếu website bị lỗi sau khi thêm CSS/JS.</p>
                </div>

                <div class="pmedia-ai-admin-grid">
                    <div class="pmedia-ai-card">
                        <h2>Global CSS</h2>
                        <p class="description">In trong thẻ <code>style</code> ở <code>wp_head</code>. Không cần nhập thẻ style.</p>
                        <textarea name="global_css" rows="18" class="large-text code"><?php echo esc_textarea((string) $s['global_css']); ?></textarea>
                    </div>

                    <div class="pmedia-ai-card">
                        <h2>Global JS Footer</h2>
                        <p class="description">In trong footer, phù hợp cho animation, tracking, popup. Không cần nhập thẻ script.</p>
                        <textarea name="global_js_footer" rows="18" class="large-text code"><?php echo esc_textarea((string) $s['global_js_footer']); ?></textarea>
                    </div>
                </div>

                <div class="pmedia-ai-admin-grid">
                    <div class="pmedia-ai-card">
                        <h2>Global JS Head</h2>
                        <p class="description">Chỉ dùng khi script bắt buộc chạy ở head. Không cần nhập thẻ script.</p>
                        <textarea name="global_js_head" rows="12" class="large-text code"><?php echo esc_textarea((string) $s['global_js_head']); ?></textarea>
                    </div>

                    <div class="pmedia-ai-card">
                        <h2>Before Body HTML</h2>
                        <p class="description">In trực tiếp trước <code>&lt;/body&gt;</code>. Dùng cho chat widget/tracking script nâng cao.</p>
                        <textarea name="before_body_html" rows="12" class="large-text code"><?php echo esc_textarea((string) $s['before_body_html']); ?></textarea>
                    </div>
                </div>

                <p><button type="submit" class="button button-primary button-hero">Lưu Custom Code</button></p>
            </form>
        </div>
        <?php
    }

    public static function handle_save_global(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'pmedia-ai-core'));
        }

        if (!isset($_POST['pmedia_ai_custom_code_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pmedia_ai_custom_code_nonce'])), 'pmedia_ai_save_custom_code')) {
            wp_die(esc_html__('Nonce không hợp lệ.', 'pmedia-ai-core'));
        }

        $settings = [
            'enabled' => !empty($_POST['enabled']) ? '1' : '',
            'global_css' => self::clean_css(wp_unslash((string) ($_POST['global_css'] ?? ''))),
            'global_js_head' => self::clean_js(wp_unslash((string) ($_POST['global_js_head'] ?? ''))),
            'global_js_footer' => self::clean_js(wp_unslash((string) ($_POST['global_js_footer'] ?? ''))),
            'before_body_html' => self::clean_raw(wp_unslash((string) ($_POST['before_body_html'] ?? ''))),
        ];

        update_option(self::OPTION_NAME, $settings, false);
        wp_safe_redirect(admin_url('admin.php?page=pmedia-ai-custom-code&updated=1'));
        exit;
    }

    public static function add_meta_boxes(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        foreach (['page', 'post', 'pmedia_service', 'pmedia_project'] as $screen) {
            add_meta_box('pmedia-ai-page-custom-code', __('PMEDIA AI Page Custom Code', 'pmedia-ai-core'), [self::class, 'render_page_meta_box'], $screen, 'normal', 'default');
        }
    }

    public static function render_page_meta_box(WP_Post $post): void
    {
        wp_nonce_field('pmedia_ai_save_page_custom_code', 'pmedia_ai_page_custom_code_nonce');
        $css = get_post_meta($post->ID, '_pmedia_page_css', true);
        $js = get_post_meta($post->ID, '_pmedia_page_js_footer', true);
        $body_class = get_post_meta($post->ID, '_pmedia_page_body_class', true);
        $disable_global = get_post_meta($post->ID, '_pmedia_disable_global_custom_code', true);
        ?>
        <p class="description">CSS/JS riêng cho trang này. Chỉ admin mới thấy và lưu được mục này.</p>
        <p><label><strong>Body class riêng</strong></label><input type="text" name="pmedia_page_body_class" class="widefat" value="<?php echo esc_attr((string) $body_class); ?>" placeholder="landing-page campaign-tet"></p>
        <p><label><input type="checkbox" name="pmedia_disable_global_custom_code" value="1" <?php checked(!empty($disable_global)); ?>> <strong>Tắt Global Custom Code trên trang này</strong></label></p>
        <p><label><strong>Page CSS</strong></label><textarea name="pmedia_page_css" rows="10" class="large-text code"><?php echo esc_textarea((string) $css); ?></textarea></p>
        <p><label><strong>Page JS Footer</strong></label><textarea name="pmedia_page_js_footer" rows="10" class="large-text code"><?php echo esc_textarea((string) $js); ?></textarea></p>
        <?php
    }

    public static function save_post_meta(int $post_id): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (!isset($_POST['pmedia_ai_page_custom_code_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pmedia_ai_page_custom_code_nonce'])), 'pmedia_ai_save_page_custom_code')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        update_post_meta($post_id, '_pmedia_page_body_class', sanitize_text_field(wp_unslash((string) ($_POST['pmedia_page_body_class'] ?? ''))));
        update_post_meta($post_id, '_pmedia_disable_global_custom_code', !empty($_POST['pmedia_disable_global_custom_code']) ? '1' : '');
        update_post_meta($post_id, '_pmedia_page_css', self::clean_css(wp_unslash((string) ($_POST['pmedia_page_css'] ?? ''))));
        update_post_meta($post_id, '_pmedia_page_js_footer', self::clean_js(wp_unslash((string) ($_POST['pmedia_page_js_footer'] ?? ''))));
    }

    public static function add_body_classes(array $classes): array
    {
        if (!is_singular()) {
            return $classes;
        }

        $custom = get_post_meta(get_queried_object_id(), '_pmedia_page_body_class', true);
        foreach (preg_split('/\s+/', (string) $custom) ?: [] as $class) {
            $class = sanitize_html_class($class);
            if ($class !== '') {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    public static function print_global_head(): void
    {
        if (self::is_global_disabled_for_current_page()) {
            return;
        }
        $s = self::settings();
        if (empty($s['enabled'])) {
            return;
        }
        if (!empty($s['global_css'])) {
            printf("\n<style id=\"pmedia-ai-global-custom-css\">\n%s\n</style>\n", self::safe_style((string) $s['global_css']));
        }
        if (!empty($s['global_js_head'])) {
            printf("\n<script id=\"pmedia-ai-global-js-head\">\n%s\n</script>\n", self::safe_script((string) $s['global_js_head']));
        }
    }

    public static function print_page_head(): void
    {
        if (!is_singular()) {
            return;
        }
        $css = get_post_meta(get_queried_object_id(), '_pmedia_page_css', true);
        if (!empty($css)) {
            printf("\n<style id=\"pmedia-ai-page-custom-css\">\n%s\n</style>\n", self::safe_style((string) $css));
        }
    }

    public static function print_global_footer(): void
    {
        if (self::is_global_disabled_for_current_page()) {
            return;
        }
        $s = self::settings();
        if (empty($s['enabled'])) {
            return;
        }
        if (!empty($s['global_js_footer'])) {
            printf("\n<script id=\"pmedia-ai-global-js-footer\">\n%s\n</script>\n", self::safe_script((string) $s['global_js_footer']));
        }
        if (!empty($s['before_body_html'])) {
            echo "\n<!-- PMEDIA AI before body HTML -->\n" . self::safe_raw_html((string) $s['before_body_html']) . "\n";
        }
    }

    public static function print_page_footer(): void
    {
        if (!is_singular()) {
            return;
        }
        $js = get_post_meta(get_queried_object_id(), '_pmedia_page_js_footer', true);
        if (!empty($js)) {
            printf("\n<script id=\"pmedia-ai-page-js-footer\">\n%s\n</script>\n", self::safe_script((string) $js));
        }
    }

    private static function is_global_disabled_for_current_page(): bool
    {
        return is_singular() && (bool) get_post_meta(get_queried_object_id(), '_pmedia_disable_global_custom_code', true);
    }

    private static function clean_css(string $value): string
    {
        return trim(str_replace(['<style>', '</style>'], '', wp_check_invalid_utf8($value)));
    }

    private static function clean_js(string $value): string
    {
        $value = preg_replace('#</?script[^>]*>#i', '', $value);
        return trim(wp_check_invalid_utf8((string) $value));
    }

    private static function clean_raw(string $value): string
    {
        return trim(wp_check_invalid_utf8($value));
    }

    private static function safe_style(string $value): string
    {
        return str_replace('</style', '<\/style', $value);
    }

    private static function safe_script(string $value): string
    {
        return str_replace('</script', '<\/script', $value);
    }

    private static function safe_raw_html(string $value): string
    {
        return $value;
    }
}
