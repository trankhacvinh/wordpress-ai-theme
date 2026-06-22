<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Prompt_Workflow
{
    public static function hooks(): void
    {
        add_action('admin_menu', [self::class, 'register_submenu']);
        add_action('admin_post_pmedia_ai_import_ai_pages', [self::class, 'handle_import_ai_pages']);
    }

    public static function register_submenu(): void
    {
        add_submenu_page(
            'pmedia-ai-core',
            __('Prompt Builder', 'pmedia-ai-core'),
            __('Prompt Builder', 'pmedia-ai-core'),
            'manage_options',
            'pmedia-ai-prompt-builder',
            [self::class, 'render_page']
        );
    }

    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $default_sitemap = implode("\n", [
            '/ | Trang chủ',
            '/gioi-thieu | Giới thiệu',
            '/dich-vu | Dịch vụ',
            '/dich-vu/thiet-ke-website | Thiết kế website',
            '/bang-gia | Bảng giá',
            '/cau-hoi-thuong-gap | Câu hỏi thường gặp',
            '/lien-he | Liên hệ',
        ]);
        ?>
        <div class="wrap pmedia-ai-admin-page pmedia-ai-prompt-page">
            <h1>PMEDIA AI Prompt Builder</h1>
            <p class="description">
                Dùng khi không có API key. Anh nhập brief và sitemap, bấm copy prompt, mang sang ChatGPT/Claude/Gemini để sinh JSON, sau đó dán kết quả vào ô import bên dưới.
            </p>

            <div class="pmedia-ai-admin-grid pmedia-ai-admin-grid-2-1">
                <div class="pmedia-ai-card">
                    <h2>1. Thông tin để sinh prompt</h2>

                    <p><label><strong>Tên thương hiệu</strong></label><input id="pmedia_prompt_brand" type="text" class="widefat" value="<?php echo esc_attr(get_bloginfo('name')); ?>"></p>
                    <p><label><strong>Dịch vụ/sản phẩm chính</strong></label><input id="pmedia_prompt_service" type="text" class="widefat" value="thiết kế website doanh nghiệp"></p>
                    <p><label><strong>Khách hàng mục tiêu</strong></label><input id="pmedia_prompt_target" type="text" class="widefat" value="doanh nghiệp nhỏ và vừa tại Việt Nam"></p>
                    <p><label><strong>Phong cách nội dung</strong></label><input id="pmedia_prompt_tone" type="text" class="widefat" value="chuyên nghiệp, hiện đại, rõ ràng, dễ bán hàng"></p>
                    <p><label><strong>Mô tả/prompt tổng quan</strong></label><textarea id="pmedia_prompt_summary" rows="5" class="widefat" placeholder="Mô tả doanh nghiệp, lợi thế, điểm bán hàng, yêu cầu nội dung..."></textarea></p>
                    <p><label><strong>Số điện thoại</strong></label><input id="pmedia_prompt_phone" type="text" class="widefat"></p>
                    <p><label><strong>Email</strong></label><input id="pmedia_prompt_email" type="text" class="widefat"></p>
                    <p><label><strong>Địa chỉ</strong></label><textarea id="pmedia_prompt_address" rows="3" class="widefat"></textarea></p>
                    <p><label><strong>Sitemap</strong></label><textarea id="pmedia_prompt_sitemap" rows="10" class="large-text code"><?php echo esc_textarea($default_sitemap); ?></textarea></p>
                </div>

                <div class="pmedia-ai-card">
                    <h2>2. Prompt để copy</h2>
                    <p class="description">Prompt này yêu cầu AI chỉ trả về JSON đúng format mà plugin có thể import.</p>
                    <p><button type="button" class="button button-primary" id="pmedia_copy_prompt">Copy prompt</button> <button type="button" class="button" id="pmedia_refresh_prompt">Cập nhật prompt</button></p>
                    <textarea id="pmedia_generated_prompt" rows="26" class="large-text code" readonly></textarea>
                </div>
            </div>

            <div class="pmedia-ai-card pmedia-ai-card-wide">
                <h2>3. Dán kết quả JSON từ AI để tạo/cập nhật trang</h2>
                <p class="description">
                    Sau khi AI trả về JSON, dán nguyên kết quả vào đây. Hệ thống hỗ trợ cả dạng <code>{"pages": [...]}</code> hoặc JSON array các page.
                </p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="pmedia_ai_import_ai_pages">
                    <?php wp_nonce_field('pmedia_ai_import_ai_pages', 'pmedia_ai_import_nonce'); ?>

                    <textarea name="ai_result" rows="20" class="large-text code" placeholder='{"pages":[{"path":"/","title":"Trang chủ","sections":[{"type":"hero","title":"..."}]}]}'></textarea>

                    <div class="pmedia-ai-generator-options">
                        <label><input type="checkbox" name="overwrite_existing" value="1" checked> Cập nhật nếu trang đã tồn tại</label>
                        <label><input type="checkbox" name="set_homepage" value="1" checked> Đặt trang <code>/</code> làm Homepage</label>
                        <label>Trạng thái: <select name="post_status"><option value="draft">Draft</option><option value="publish">Publish</option></select></label>
                    </div>

                    <p><button type="submit" class="button button-primary button-hero">Import JSON và tạo/cập nhật Page</button></p>
                </form>
            </div>
        </div>

        <script>
        (function () {
            function v(id) {
                var el = document.getElementById(id);
                return el ? el.value.trim() : '';
            }
            function buildPrompt() {
                var prompt = [];
                prompt.push('Tôi đang dùng WordPress theme PMEDIA AI Blank và plugin PMEDIA AI Core.');
                prompt.push('Plugin sẽ import JSON để tạo hàng loạt Page và render bằng section.');
                prompt.push('Hãy tạo nội dung website theo sitemap dưới đây.');
                prompt.push('');
                prompt.push('THÔNG TIN DỰ ÁN:');
                prompt.push('- Tên thương hiệu: ' + v('pmedia_prompt_brand'));
                prompt.push('- Dịch vụ/sản phẩm chính: ' + v('pmedia_prompt_service'));
                prompt.push('- Khách hàng mục tiêu: ' + v('pmedia_prompt_target'));
                prompt.push('- Phong cách nội dung: ' + v('pmedia_prompt_tone'));
                prompt.push('- Mô tả/prompt tổng quan: ' + v('pmedia_prompt_summary'));
                prompt.push('- Điện thoại: ' + v('pmedia_prompt_phone'));
                prompt.push('- Email: ' + v('pmedia_prompt_email'));
                prompt.push('- Địa chỉ: ' + v('pmedia_prompt_address'));
                prompt.push('');
                prompt.push('SITEMAP:');
                prompt.push(v('pmedia_prompt_sitemap'));
                prompt.push('');
                prompt.push('SECTION TYPE ĐƯỢC HỖ TRỢ: hero, content, services, pricing, faq, cta, contact.');
                prompt.push('');
                prompt.push('YÊU CẦU OUTPUT:');
                prompt.push('- Chỉ trả về JSON hợp lệ, không markdown, không giải thích, không comment.');
                prompt.push('- Output phải có dạng: {"pages": [...]}.');
                prompt.push('- Mỗi page gồm: path, title, seo_title, seo_description, sections.');
                prompt.push('- Mỗi section phải có field type.');
                prompt.push('- Nội dung tiếng Việt tự nhiên, rõ ràng, có CTA.');
                prompt.push('- Không dùng dấu phẩy thừa.');
                prompt.push('');
                prompt.push('SCHEMA MẪU:');
                prompt.push('{');
                prompt.push('  "pages": [');
                prompt.push('    {');
                prompt.push('      "path": "/",');
                prompt.push('      "title": "Trang chủ",');
                prompt.push('      "seo_title": "",');
                prompt.push('      "seo_description": "",');
                prompt.push('      "sections": [');
                prompt.push('        {"type":"hero","eyebrow":"","title":"","description":"","button_text":"","button_link":"/lien-he","secondary_button_text":"","secondary_button_link":"/dich-vu","image":"","image_alt":""},');
                prompt.push('        {"type":"services","eyebrow":"","title":"","description":"","items":[{"icon":"01","title":"","description":""}]},');
                prompt.push('        {"type":"content","title":"","content":"<p>Nội dung HTML ngắn.</p>"},');
                prompt.push('        {"type":"pricing","title":"","description":"","items":[{"name":"","price":"","description":"","features":[""]}]},');
                prompt.push('        {"type":"faq","title":"","description":"","items":[{"question":"","answer":""}]},');
                prompt.push('        {"type":"cta","title":"","description":"","button_text":"","button_link":"/lien-he"},');
                prompt.push('        {"type":"contact","eyebrow":"Liên hệ","title":"","description":"","phone":"","email":"","address":"","form_shortcode":""}');
                prompt.push('      ]');
                prompt.push('    }');
                prompt.push('  ]');
                prompt.push('}');
                document.getElementById('pmedia_generated_prompt').value = prompt.join('\n');
            }
            function copyPrompt() {
                buildPrompt();
                var el = document.getElementById('pmedia_generated_prompt');
                el.focus();
                el.select();
                document.execCommand('copy');
            }
            document.getElementById('pmedia_refresh_prompt').addEventListener('click', buildPrompt);
            document.getElementById('pmedia_copy_prompt').addEventListener('click', copyPrompt);
            ['pmedia_prompt_brand','pmedia_prompt_service','pmedia_prompt_target','pmedia_prompt_tone','pmedia_prompt_summary','pmedia_prompt_phone','pmedia_prompt_email','pmedia_prompt_address','pmedia_prompt_sitemap'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) { el.addEventListener('input', buildPrompt); }
            });
            buildPrompt();
        })();
        </script>
        <?php
    }

    public static function handle_import_ai_pages(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'pmedia-ai-core'));
        }

        if (!isset($_POST['pmedia_ai_import_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pmedia_ai_import_nonce'])), 'pmedia_ai_import_ai_pages')) {
            wp_die(esc_html__('Nonce không hợp lệ.', 'pmedia-ai-core'));
        }

        $raw = trim(wp_unslash((string) ($_POST['ai_result'] ?? '')));
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::set_notice('JSON không hợp lệ: ' . json_last_error_msg(), 'error');
            self::redirect_back();
        }

        $pages = [];
        if (isset($decoded['pages']) && is_array($decoded['pages'])) {
            $pages = $decoded['pages'];
        } elseif (is_array($decoded) && isset($decoded[0])) {
            $pages = $decoded;
        }

        if (empty($pages)) {
            self::set_notice('Không tìm thấy danh sách pages trong JSON.', 'error');
            self::redirect_back();
        }

        $overwrite = !empty($_POST['overwrite_existing']);
        $set_homepage = !empty($_POST['set_homepage']);
        $post_status = in_array(($_POST['post_status'] ?? 'draft'), ['draft', 'publish'], true) ? (string) $_POST['post_status'] : 'draft';

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $page_ids_by_path = [];
        $homepage_id = 0;

        usort($pages, static function (array $a, array $b): int {
            return substr_count((string) ($a['path'] ?? ''), '/') <=> substr_count((string) ($b['path'] ?? ''), '/');
        });

        foreach ($pages as $page) {
            if (!is_array($page)) {
                $skipped++;
                continue;
            }

            $result = self::import_page($page, $post_status, $overwrite, $page_ids_by_path);
            if ($result['status'] === 'created') {
                $created++;
            } elseif ($result['status'] === 'updated') {
                $updated++;
            } else {
                $skipped++;
            }

            if (!empty($result['post_id'])) {
                $path = self::normalize_path((string) ($page['path'] ?? ''));
                $page_ids_by_path[$path] = (int) $result['post_id'];
                if ($path === '/') {
                    $homepage_id = (int) $result['post_id'];
                }
            }
        }

        if ($set_homepage && $homepage_id > 0) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $homepage_id);
        }

        self::set_notice(sprintf('Import xong: tạo mới %d trang, cập nhật %d trang, bỏ qua %d trang.', $created, $updated, $skipped), 'success');
        self::redirect_back();
    }

    private static function import_page(array $page, string $post_status, bool $overwrite, array $page_ids_by_path): array
    {
        $path = self::normalize_path((string) ($page['path'] ?? ''));
        if ($path === '') {
            return ['status' => 'skipped', 'post_id' => 0];
        }

        $title = sanitize_text_field((string) ($page['title'] ?? self::title_from_path($path)));
        $slug = self::slug_from_path($path);
        $parent_path = self::parent_path($path);
        $parent_id = isset($page_ids_by_path[$parent_path]) ? (int) $page_ids_by_path[$parent_path] : 0;
        $existing = $path === '/' ? self::find_generated_homepage() : get_page_by_path(trim($path, '/'), OBJECT, 'page');

        $sections = isset($page['sections']) && is_array($page['sections']) ? $page['sections'] : [];
        $sections_json = PMEDIA_AI_Section_Schema::encode_sections($sections);

        $post_data = [
            'post_title' => $title,
            'post_name' => $path === '/' ? 'trang-chu' : $slug,
            'post_content' => '',
            'post_status' => $post_status,
            'post_type' => 'page',
            'post_parent' => $parent_id,
        ];

        if ($existing instanceof WP_Post) {
            $post_id = (int) $existing->ID;
            if (!$overwrite) {
                return ['status' => 'skipped', 'post_id' => $post_id];
            }
            $post_data['ID'] = $post_id;
            wp_update_post(wp_slash($post_data));
            self::save_page_meta($post_id, $path, $page, $sections_json);
            return ['status' => 'updated', 'post_id' => $post_id];
        }

        $post_id = wp_insert_post(wp_slash($post_data), true);
        if (is_wp_error($post_id) || !$post_id) {
            return ['status' => 'skipped', 'post_id' => 0];
        }

        self::save_page_meta((int) $post_id, $path, $page, $sections_json);
        return ['status' => 'created', 'post_id' => (int) $post_id];
    }

    private static function save_page_meta(int $post_id, string $path, array $page, string $sections_json): void
    {
        update_post_meta($post_id, '_pmedia_sections', $sections_json);
        update_post_meta($post_id, '_pmedia_ai_generated', '1');
        update_post_meta($post_id, '_pmedia_ai_sitemap_path', $path);
        update_post_meta($post_id, '_pmedia_ai_generated_at', current_time('mysql'));
        update_post_meta($post_id, '_pmedia_ai_source_prompt', 'manual-copy-prompt');

        if (!empty($page['seo_title'])) {
            update_post_meta($post_id, '_pmedia_seo_title', sanitize_text_field((string) $page['seo_title']));
        }
        if (!empty($page['seo_description'])) {
            update_post_meta($post_id, '_pmedia_seo_description', sanitize_textarea_field((string) $page['seo_description']));
        }
    }

    private static function normalize_path(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === 'home' || $path === 'trang-chu') {
            return '/';
        }
        $path = '/' . trim($path, '/');
        $parts = array_filter(explode('/', $path));
        $parts = array_map('sanitize_title', $parts);
        return '/' . implode('/', $parts);
    }

    private static function slug_from_path(string $path): string
    {
        if ($path === '/') {
            return 'trang-chu';
        }
        $parts = explode('/', trim($path, '/'));
        return sanitize_title((string) end($parts));
    }

    private static function parent_path(string $path): string
    {
        if ($path === '/') {
            return '';
        }
        $parts = explode('/', trim($path, '/'));
        array_pop($parts);
        return empty($parts) ? '' : '/' . implode('/', $parts);
    }

    private static function title_from_path(string $path): string
    {
        if ($path === '/') {
            return 'Trang chủ';
        }
        return ucwords(str_replace('-', ' ', self::slug_from_path($path)));
    }

    private static function find_generated_homepage(): ?WP_Post
    {
        $posts = get_posts([
            'post_type' => 'page',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 1,
            'meta_query' => [[
                'key' => '_pmedia_ai_sitemap_path',
                'value' => '/',
            ]],
        ]);
        return !empty($posts) && $posts[0] instanceof WP_Post ? $posts[0] : null;
    }

    private static function set_notice(string $message, string $type = 'info'): void
    {
        set_transient('pmedia_ai_site_generator_notice_' . get_current_user_id(), ['message' => $message, 'type' => $type], 60);
    }

    private static function redirect_back(): void
    {
        wp_safe_redirect(admin_url('admin.php?page=pmedia-ai-prompt-builder'));
        exit;
    }
}
