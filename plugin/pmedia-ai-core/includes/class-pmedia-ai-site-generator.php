<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Site_Generator
{
    private const OPTION_STATE = 'pmedia_ai_site_generator_state';

    public static function hooks(): void
    {
        add_action('admin_menu', [self::class, 'register_submenu']);
        add_action('admin_post_pmedia_ai_generate_site', [self::class, 'handle_generate_site']);
    }

    public static function register_submenu(): void
    {
        add_submenu_page(
            'pmedia-ai-core',
            __('Site Generator', 'pmedia-ai-core'),
            __('Site Generator', 'pmedia-ai-core'),
            'manage_options',
            'pmedia-ai-site-generator',
            [self::class, 'render_page']
        );
    }

    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $state = self::get_state();
        $generated_pages = self::get_generated_pages();
        $default_sitemap = self::default_sitemap();
        ?>
        <div class="wrap pmedia-ai-admin-page pmedia-ai-generator-page">
            <h1>PMEDIA AI Site Generator</h1>
            <p class="description">
                Nhập brief/prompt và sitemap để tạo hàng loạt Page. Hệ thống sẽ tự tạo section JSON cho từng trang, sau đó anh vẫn chỉnh được bằng Section Builder.
            </p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pmedia-ai-generator-form">
                <input type="hidden" name="action" value="pmedia_ai_generate_site">
                <?php wp_nonce_field('pmedia_ai_generate_site', 'pmedia_ai_generate_site_nonce'); ?>

                <div class="pmedia-ai-admin-grid pmedia-ai-admin-grid-2-1">
                    <div class="pmedia-ai-card">
                        <h2>Brief / Prompt dự án</h2>

                        <p>
                            <label><strong>Tên thương hiệu</strong></label>
                            <input type="text" name="brand_name" class="widefat" value="<?php echo esc_attr((string) ($state['brand_name'] ?? get_bloginfo('name'))); ?>" placeholder="Ví dụ: PMEDIA">
                        </p>

                        <p>
                            <label><strong>Dịch vụ/sản phẩm chính</strong></label>
                            <input type="text" name="primary_service" class="widefat" value="<?php echo esc_attr((string) ($state['primary_service'] ?? 'thiết kế website doanh nghiệp')); ?>" placeholder="Ví dụ: thiết kế website doanh nghiệp">
                        </p>

                        <p>
                            <label><strong>Khách hàng mục tiêu</strong></label>
                            <input type="text" name="target_customers" class="widefat" value="<?php echo esc_attr((string) ($state['target_customers'] ?? 'doanh nghiệp nhỏ và vừa')); ?>" placeholder="Ví dụ: doanh nghiệp nhỏ và vừa">
                        </p>

                        <p>
                            <label><strong>Mô tả/prompt tổng quan</strong></label>
                            <textarea name="business_summary" rows="5" class="widefat" placeholder="Mô tả doanh nghiệp, lợi thế, phong cách nội dung, điểm bán hàng chính..."><?php echo esc_textarea((string) ($state['business_summary'] ?? '')); ?></textarea>
                        </p>

                        <p>
                            <label><strong>Prompt bổ sung cho AI/nội dung</strong></label>
                            <textarea name="generation_prompt" rows="5" class="widefat" placeholder="Ví dụ: Viết nội dung chuyên nghiệp, rõ ràng, hướng đến khách hàng B2B, CTA mạnh..."><?php echo esc_textarea((string) ($state['generation_prompt'] ?? '')); ?></textarea>
                        </p>
                    </div>

                    <div class="pmedia-ai-card">
                        <h2>Thông tin liên hệ</h2>

                        <p>
                            <label><strong>Điện thoại</strong></label>
                            <input type="text" name="phone" class="widefat" value="<?php echo esc_attr((string) ($state['phone'] ?? '')); ?>">
                        </p>

                        <p>
                            <label><strong>Email</strong></label>
                            <input type="text" name="email" class="widefat" value="<?php echo esc_attr((string) ($state['email'] ?? '')); ?>">
                        </p>

                        <p>
                            <label><strong>Địa chỉ</strong></label>
                            <textarea name="address" rows="3" class="widefat"><?php echo esc_textarea((string) ($state['address'] ?? '')); ?></textarea>
                        </p>

                        <p>
                            <label><strong>CTA chính</strong></label>
                            <input type="text" name="primary_cta_text" class="widefat" value="<?php echo esc_attr((string) ($state['primary_cta_text'] ?? 'Liên hệ tư vấn')); ?>">
                        </p>

                        <p>
                            <label><strong>Link CTA chính</strong></label>
                            <input type="text" name="primary_cta_link" class="widefat" value="<?php echo esc_attr((string) ($state['primary_cta_link'] ?? '/lien-he')); ?>">
                        </p>
                    </div>
                </div>

                <div class="pmedia-ai-card pmedia-ai-card-wide">
                    <h2>Sitemap</h2>
                    <p class="description">
                        Mỗi dòng một trang. Hỗ trợ định dạng <code>/duong-dan | Tên trang</code> hoặc <code>Tên trang | /duong-dan</code>. Trang con dùng path dạng <code>/dich-vu/thiet-ke-website</code>.
                    </p>
                    <textarea name="sitemap" rows="12" class="large-text code"><?php echo esc_textarea((string) ($state['sitemap'] ?? $default_sitemap)); ?></textarea>

                    <div class="pmedia-ai-generator-options">
                        <label>
                            <input type="checkbox" name="set_homepage" value="1" <?php checked(!empty($state['set_homepage'])); ?>>
                            Đặt trang <code>/</code> làm Homepage
                        </label>

                        <label>
                            <input type="checkbox" name="overwrite_existing" value="1" <?php checked(true, !array_key_exists('overwrite_existing', $state) || !empty($state['overwrite_existing'])); ?>>
                            Cập nhật lại section nếu trang đã tồn tại
                        </label>

                        <label>
                            Trạng thái:
                            <select name="post_status">
                                <option value="draft" <?php selected((string) ($state['post_status'] ?? 'draft'), 'draft'); ?>>Draft</option>
                                <option value="publish" <?php selected((string) ($state['post_status'] ?? 'draft'), 'publish'); ?>>Publish</option>
                            </select>
                        </label>
                    </div>

                    <p>
                        <button type="submit" class="button button-primary button-hero">Tạo / cập nhật website từ sitemap</button>
                    </p>
                </div>
            </form>

            <div class="pmedia-ai-card pmedia-ai-card-wide">
                <h2>Trang đã tạo bằng PMEDIA AI</h2>
                <?php self::render_generated_pages_table($generated_pages); ?>
            </div>
        </div>
        <?php
    }

    public static function handle_generate_site(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'pmedia-ai-core'));
        }

        if (!isset($_POST['pmedia_ai_generate_site_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pmedia_ai_generate_site_nonce'])), 'pmedia_ai_generate_site')) {
            wp_die(esc_html__('Nonce không hợp lệ.', 'pmedia-ai-core'));
        }

        $context = self::sanitize_context($_POST);
        $pages = self::parse_sitemap((string) $context['sitemap']);

        if (empty($pages)) {
            self::set_notice('Sitemap không có trang hợp lệ.', 'error');
            self::redirect_back();
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $page_ids_by_path = [];
        $homepage_id = 0;

        usort($pages, static function (array $a, array $b): int {
            return substr_count($a['path'], '/') <=> substr_count($b['path'], '/');
        });

        foreach ($pages as $page) {
            $result = self::create_or_update_page($page, $context, $page_ids_by_path);
            if ($result['status'] === 'created') {
                $created++;
            } elseif ($result['status'] === 'updated') {
                $updated++;
            } else {
                $skipped++;
            }

            if (!empty($result['post_id'])) {
                $page_ids_by_path[$page['path']] = (int) $result['post_id'];
                if ($page['path'] === '/') {
                    $homepage_id = (int) $result['post_id'];
                }
            }
        }

        if (!empty($context['set_homepage']) && $homepage_id > 0) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $homepage_id);
        }

        update_option(self::OPTION_STATE, $context, false);
        self::set_notice(sprintf('Đã xử lý sitemap: tạo mới %d trang, cập nhật %d trang, bỏ qua %d trang.', $created, $updated, $skipped), 'success');
        self::redirect_back();
    }

    public static function parse_sitemap(string $sitemap): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $sitemap);
        $pages = [];

        if (!is_array($lines)) {
            return [];
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $line = preg_replace('/^[-*\d\.\)\s]+/', '', $line);
            $parts = array_map('trim', preg_split('/\s+[|>→]\s+|\s+[|>→]|[|>→]\s+/', $line));
            $path = '';
            $title = '';

            if (is_array($parts) && count($parts) >= 2) {
                foreach ($parts as $part) {
                    if (self::looks_like_path($part)) {
                        $path = $part;
                    } elseif ($title === '') {
                        $title = $part;
                    }
                }
            } elseif (self::looks_like_path($line)) {
                $path = $line;
            } else {
                $title = $line;
            }

            if ($path === '' && $title !== '') {
                $path = '/' . sanitize_title($title);
            }

            $path = self::normalize_path($path);
            if ($title === '') {
                $title = self::title_from_path($path);
            }

            if ($path === '') {
                continue;
            }

            $pages[$path] = [
                'path' => $path,
                'title' => $title,
                'slug' => self::slug_from_path($path),
                'parent_path' => self::parent_path($path),
            ];
        }

        return array_values($pages);
    }

    private static function create_or_update_page(array $page, array $context, array $page_ids_by_path): array
    {
        $path = (string) $page['path'];
        $existing = get_page_by_path(trim($path, '/'), OBJECT, 'page');
        if ($path === '/') {
            $existing = self::find_generated_homepage();
        }

        $parent_id = 0;
        if (!empty($page['parent_path']) && isset($page_ids_by_path[$page['parent_path']])) {
            $parent_id = (int) $page_ids_by_path[$page['parent_path']];
        }

        $post_data = [
            'post_title' => (string) $page['title'],
            'post_name' => $path === '/' ? 'trang-chu' : (string) $page['slug'],
            'post_content' => '',
            'post_status' => (string) $context['post_status'],
            'post_type' => 'page',
            'post_parent' => $parent_id,
        ];

        $sections = PMEDIA_AI_Section_Schema::generate_sections_for_page($page, $context);
        $sections_json = PMEDIA_AI_Section_Schema::encode_sections($sections);

        if ($existing instanceof WP_Post) {
            $post_id = (int) $existing->ID;
            if (empty($context['overwrite_existing'])) {
                return ['status' => 'skipped', 'post_id' => $post_id];
            }

            $post_data['ID'] = $post_id;
            wp_update_post(wp_slash($post_data));
            self::update_generated_meta($post_id, $page, $context, $sections_json);
            return ['status' => 'updated', 'post_id' => $post_id];
        }

        $post_id = wp_insert_post(wp_slash($post_data), true);
        if (is_wp_error($post_id) || !$post_id) {
            return ['status' => 'skipped', 'post_id' => 0];
        }

        self::update_generated_meta((int) $post_id, $page, $context, $sections_json);
        return ['status' => 'created', 'post_id' => (int) $post_id];
    }

    private static function update_generated_meta(int $post_id, array $page, array $context, string $sections_json): void
    {
        update_post_meta($post_id, '_pmedia_sections', $sections_json);
        update_post_meta($post_id, '_pmedia_ai_generated', '1');
        update_post_meta($post_id, '_pmedia_ai_sitemap_path', (string) $page['path']);
        update_post_meta($post_id, '_pmedia_ai_generated_at', current_time('mysql'));
        update_post_meta($post_id, '_pmedia_ai_source_prompt', (string) ($context['generation_prompt'] ?? ''));
        update_post_meta($post_id, '_pmedia_seo_title', (string) $page['title']);
        update_post_meta($post_id, '_pmedia_seo_description', wp_trim_words((string) ($context['business_summary'] ?? ''), 28, ''));
    }

    private static function get_generated_pages(): array
    {
        return get_posts([
            'post_type' => 'page',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 100,
            'meta_key' => '_pmedia_ai_generated',
            'meta_value' => '1',
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);
    }

    private static function render_generated_pages_table(array $pages): void
    {
        if (empty($pages)) {
            echo '<p>Chưa có trang nào được tạo bằng Site Generator.</p>';
            return;
        }

        echo '<table class="widefat striped pmedia-ai-generated-pages"><thead><tr>';
        echo '<th>Trang</th><th>Path</th><th>Trạng thái</th><th>Cập nhật</th><th>Thao tác</th>';
        echo '</tr></thead><tbody>';

        foreach ($pages as $page) {
            if (!$page instanceof WP_Post) {
                continue;
            }

            $path = get_post_meta($page->ID, '_pmedia_ai_sitemap_path', true);
            $edit_link = get_edit_post_link($page->ID);
            $view_link = get_permalink($page->ID);

            echo '<tr>';
            echo '<td><strong>' . esc_html(get_the_title($page)) . '</strong></td>';
            echo '<td><code>' . esc_html((string) $path) . '</code></td>';
            echo '<td>' . esc_html($page->post_status) . '</td>';
            echo '<td>' . esc_html(get_the_modified_date('Y-m-d H:i', $page)) . '</td>';
            echo '<td>';
            if ($edit_link) {
                echo '<a class="button button-small" href="' . esc_url($edit_link) . '">Sửa</a> ';
            }
            if ($view_link) {
                echo '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url($view_link) . '">Xem</a>';
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    private static function sanitize_context(array $input): array
    {
        return [
            'brand_name' => sanitize_text_field(wp_unslash($input['brand_name'] ?? '')),
            'primary_service' => sanitize_text_field(wp_unslash($input['primary_service'] ?? '')),
            'target_customers' => sanitize_text_field(wp_unslash($input['target_customers'] ?? '')),
            'business_summary' => sanitize_textarea_field(wp_unslash($input['business_summary'] ?? '')),
            'generation_prompt' => sanitize_textarea_field(wp_unslash($input['generation_prompt'] ?? '')),
            'phone' => sanitize_text_field(wp_unslash($input['phone'] ?? '')),
            'email' => sanitize_text_field(wp_unslash($input['email'] ?? '')),
            'address' => sanitize_textarea_field(wp_unslash($input['address'] ?? '')),
            'primary_cta_text' => sanitize_text_field(wp_unslash($input['primary_cta_text'] ?? 'Liên hệ tư vấn')),
            'primary_cta_link' => sanitize_text_field(wp_unslash($input['primary_cta_link'] ?? '/lien-he')),
            'sitemap' => sanitize_textarea_field(wp_unslash($input['sitemap'] ?? '')),
            'post_status' => in_array(($input['post_status'] ?? 'draft'), ['draft', 'publish'], true) ? (string) $input['post_status'] : 'draft',
            'set_homepage' => !empty($input['set_homepage']) ? '1' : '',
            'overwrite_existing' => !empty($input['overwrite_existing']) ? '1' : '',
        ];
    }

    private static function get_state(): array
    {
        $state = get_option(self::OPTION_STATE, []);
        return is_array($state) ? $state : [];
    }

    private static function default_sitemap(): string
    {
        return implode("\n", [
            '/ | Trang chủ',
            '/gioi-thieu | Giới thiệu',
            '/dich-vu | Dịch vụ',
            '/dich-vu/thiet-ke-website | Thiết kế website',
            '/dich-vu/mini-cms-wordpress | Mini CMS WordPress',
            '/bang-gia | Bảng giá',
            '/cau-hoi-thuong-gap | Câu hỏi thường gặp',
            '/lien-he | Liên hệ',
        ]);
    }

    private static function looks_like_path(string $value): bool
    {
        $value = trim($value);
        return $value === '/' || strpos($value, '/') === 0 || preg_match('/^[a-z0-9\-\/]+$/i', $value) === 1;
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
        $path = '/' . implode('/', $parts);

        return $path === '/' ? '/' : untrailingslashit($path);
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
        if (empty($parts)) {
            return '';
        }

        return '/' . implode('/', $parts);
    }

    private static function title_from_path(string $path): string
    {
        if ($path === '/') {
            return 'Trang chủ';
        }

        $slug = self::slug_from_path($path);
        return ucwords(str_replace('-', ' ', $slug));
    }

    private static function find_generated_homepage(): ?WP_Post
    {
        $posts = get_posts([
            'post_type' => 'page',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_pmedia_ai_sitemap_path',
                    'value' => '/',
                ],
            ],
        ]);

        return !empty($posts) && $posts[0] instanceof WP_Post ? $posts[0] : null;
    }

    private static function set_notice(string $message, string $type = 'info'): void
    {
        set_transient('pmedia_ai_site_generator_notice_' . get_current_user_id(), ['message' => $message, 'type' => $type], 60);
    }

    public static function maybe_render_notice(): void
    {
        $notice = get_transient('pmedia_ai_site_generator_notice_' . get_current_user_id());
        if (!$notice || !is_array($notice)) {
            return;
        }

        delete_transient('pmedia_ai_site_generator_notice_' . get_current_user_id());
        printf('<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr((string) ($notice['type'] ?? 'info')), esc_html((string) ($notice['message'] ?? '')));
    }

    private static function redirect_back(): void
    {
        wp_safe_redirect(admin_url('admin.php?page=pmedia-ai-site-generator'));
        exit;
    }
}
