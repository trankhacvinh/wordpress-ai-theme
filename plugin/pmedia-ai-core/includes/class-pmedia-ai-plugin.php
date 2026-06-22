<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Plugin
{
    private static ?PMEDIA_AI_Plugin $instance = null;

    public static function instance(): PMEDIA_AI_Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        PMEDIA_AI_CPT::hooks();
        PMEDIA_AI_Meta_Boxes::hooks();
        PMEDIA_AI_Renderer::hooks();

        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_admin_page(): void
    {
        add_menu_page(
            __('PMEDIA AI', 'pmedia-ai-core'),
            __('PMEDIA AI', 'pmedia-ai-core'),
            'manage_options',
            'pmedia-ai-core',
            [$this, 'render_admin_page'],
            'dashicons-layout',
            30
        );
    }

    public function enqueue_admin_assets(string $hook): void
    {
        if ($hook !== 'toplevel_page_pmedia-ai-core' && $hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        wp_enqueue_style(
            'pmedia-ai-core-admin',
            PMEDIA_AI_CORE_URL . 'assets/admin.css',
            [],
            PMEDIA_AI_CORE_VERSION
        );
    }

    public function render_admin_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $sample = PMEDIA_AI_Meta_Boxes::sample_sections_json();
        ?>
        <div class="wrap pmedia-ai-admin-page">
            <h1>PMEDIA AI Core</h1>
            <p class="description">
                Plugin này giữ phần dữ liệu động cho website: section JSON, custom post type, SEO field và renderer helper.
                Theme chỉ cần lo giao diện.
            </p>

            <div class="pmedia-ai-admin-grid">
                <div class="pmedia-ai-card">
                    <h2>Quy trình dùng nhanh</h2>
                    <ol>
                        <li>Tạo hoặc sửa một Page trong WordPress.</li>
                        <li>Tại box <strong>PMEDIA AI Sections</strong>, dán JSON section.</li>
                        <li>Publish page.</li>
                        <li>Theme <strong>PMEDIA AI Blank</strong> sẽ render theo dữ liệu này.</li>
                    </ol>
                </div>

                <div class="pmedia-ai-card">
                    <h2>Section đang hỗ trợ</h2>
                    <ul>
                        <li><code>hero</code></li>
                        <li><code>content</code></li>
                        <li><code>services</code></li>
                        <li><code>pricing</code></li>
                        <li><code>faq</code></li>
                        <li><code>cta</code></li>
                        <li><code>contact</code></li>
                    </ul>
                </div>
            </div>

            <div class="pmedia-ai-card pmedia-ai-card-wide">
                <h2>JSON mẫu</h2>
                <textarea readonly rows="24" class="large-text code"><?php echo esc_textarea($sample); ?></textarea>
            </div>
        </div>
        <?php
    }
}
