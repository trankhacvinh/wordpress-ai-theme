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
        PMEDIA_AI_Site_Generator::hooks();
        PMEDIA_AI_Prompt_Workflow::hooks();

        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_admin_page(): void
    {
        add_menu_page(__('PMEDIA AI', 'pmedia-ai-core'), __('PMEDIA AI', 'pmedia-ai-core'), 'manage_options', 'pmedia-ai-core', [$this, 'render_admin_page'], 'dashicons-layout', 30);
    }

    public function enqueue_admin_assets(string $hook): void
    {
        $allowed_hooks = ['toplevel_page_pmedia-ai-core', 'pmedia-ai_page_pmedia-ai-site-generator', 'pmedia-ai_page_pmedia-ai-prompt-builder', 'post.php', 'post-new.php'];

        if (!in_array($hook, $allowed_hooks, true)) {
            return;
        }

        wp_enqueue_style('pmedia-ai-core-admin', PMEDIA_AI_CORE_URL . 'assets/admin.css', [], PMEDIA_AI_CORE_VERSION);
        wp_enqueue_media();
        wp_enqueue_script('pmedia-ai-core-admin', PMEDIA_AI_CORE_URL . 'assets/admin.js', ['jquery', 'jquery-ui-sortable'], PMEDIA_AI_CORE_VERSION, true);

        if ($hook === 'pmedia-ai_page_pmedia-ai-prompt-builder') {
            wp_enqueue_script('pmedia-ai-core-prompt-builder', PMEDIA_AI_CORE_URL . 'assets/prompt-builder.js', [], PMEDIA_AI_CORE_VERSION, true);
        }

        wp_localize_script('pmedia-ai-core-admin', 'PMEDIA_AI_BUILDER', [
            'schema' => PMEDIA_AI_Component_Registry::schema(),
            'defaults' => PMEDIA_AI_Component_Registry::defaults(),
            'i18n' => [
                'addSection' => 'Thêm section',
                'deleteSection' => 'Xóa section',
                'duplicateSection' => 'Nhân bản',
                'collapse' => 'Thu gọn/Mở rộng',
                'addItem' => 'Thêm mục',
                'deleteItem' => 'Xóa mục',
                'invalidJson' => 'JSON section không hợp lệ.',
                'confirmDelete' => 'Xóa section này?',
            ],
        ]);
    }

    public function render_admin_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $sample = PMEDIA_AI_Section_Schema::sample_sections_json();
        ?>
        <div class="wrap pmedia-ai-admin-page">
            <h1>PMEDIA AI Core</h1>
            <p class="description">
                Plugin này giữ dữ liệu động cho website: Section Builder, Nested Component Builder, Prompt Builder không cần API key, Site Generator, custom post type, SEO field và renderer helper.
                Theme chỉ cần lo giao diện.
            </p>

            <div class="pmedia-ai-admin-grid">
                <div class="pmedia-ai-card">
                    <h2>Quy trình dùng nhanh</h2>
                    <ol>
                        <li>Không có API key: vào <strong>PMEDIA AI > Prompt Builder</strong>, copy prompt, dán kết quả JSON để import.</li>
                        <li>Cần tạo nhanh theo rule: vào <strong>PMEDIA AI > Site Generator</strong>.</li>
                        <li>Vào từng Page để chỉnh section bằng form builder.</li>
                        <li>Với cấu trúc phức tạp, dùng JSON nâng cao có <code>children</code>, <code>modal</code>, gallery/slider/tabs/accordion/portfolio.</li>
                    </ol>
                    <p>
                        <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=pmedia-ai-prompt-builder')); ?>">Mở Prompt Builder</a>
                        <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=pmedia-ai-site-generator')); ?>">Mở Site Generator</a>
                    </p>
                </div>

                <div class="pmedia-ai-card">
                    <h2>Component đang hỗ trợ</h2>
                    <ul>
                        <li><code>hero</code>, <code>content</code>, <code>services</code>, <code>pricing</code>, <code>faq</code>, <code>cta</code>, <code>contact</code></li>
                        <li><code>modal</code>, <code>gallery</code>, <code>slider</code>, <code>tabs</code>, <code>accordion</code>, <code>portfolio</code></li>
                        <li>Nested: <code>modal.children</code>, <code>tabs.items[].children</code>, <code>accordion.items[].children</code>, <code>portfolio.items[].modal</code></li>
                    </ul>
                </div>
            </div>

            <div class="pmedia-ai-card pmedia-ai-card-wide">
                <h2>JSON mẫu cơ bản</h2>
                <textarea readonly rows="24" class="large-text code"><?php echo esc_textarea($sample); ?></textarea>
            </div>
        </div>
        <?php
    }
}
