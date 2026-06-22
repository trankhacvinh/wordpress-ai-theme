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
            add_meta_box(
                'pmedia-ai-sections',
                __('PMEDIA AI Sections', 'pmedia-ai-core'),
                [self::class, 'render_sections_meta_box'],
                $screen,
                'normal',
                'high'
            );

            add_meta_box(
                'pmedia-ai-seo',
                __('PMEDIA AI SEO', 'pmedia-ai-core'),
                [self::class, 'render_seo_meta_box'],
                $screen,
                'side',
                'default'
            );
        }
    }

    public static function render_sections_meta_box(WP_Post $post): void
    {
        wp_nonce_field('pmedia_ai_save_meta', 'pmedia_ai_meta_nonce');

        $sections = get_post_meta($post->ID, '_pmedia_sections', true);
        if (empty($sections)) {
            $sections = self::sample_sections_json();
        }

        ?>
        <p>
            Nhập JSON section để theme render giao diện động. Mỗi object cần có field <code>type</code>, ví dụ:
            <code>hero</code>, <code>services</code>, <code>pricing</code>, <code>faq</code>, <code>cta</code>, <code>contact</code>.
        </p>
        <textarea name="pmedia_sections" rows="24" class="large-text code pmedia-ai-json-field"><?php echo esc_textarea((string) $sections); ?></textarea>
        <p class="description">
            Gợi ý: dùng AI sinh JSON theo format này, sau đó khách chỉ sửa text/hình/nút trong admin mà không sửa code theme.
        </p>
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
        $notice = get_transient('pmedia_ai_admin_notice_' . get_current_user_id());
        if (!$notice || !is_array($notice)) {
            return;
        }

        delete_transient('pmedia_ai_admin_notice_' . get_current_user_id());

        $type = $notice['type'] ?? 'info';
        $message = $notice['message'] ?? '';

        if ($message === '') {
            return;
        }

        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($type),
            esc_html($message)
        );
    }

    public static function sample_sections_json(): string
    {
        $sample = [
            [
                'type' => 'hero',
                'eyebrow' => 'PMEDIA AI Website',
                'title' => 'Website đẹp, nhẹ, dễ cập nhật nội dung',
                'description' => 'WordPress giữ vai trò CMS và routing. Theme trắng render giao diện AI-generated. Khách sửa nội dung bằng form/JSON trong admin.',
                'button_text' => 'Tư vấn ngay',
                'button_link' => '/lien-he',
                'secondary_button_text' => 'Xem dịch vụ',
                'secondary_button_link' => '/dich-vu',
                'image' => '',
            ],
            [
                'type' => 'services',
                'title' => 'Dịch vụ chính',
                'description' => 'Các nhóm dịch vụ có thể cập nhật dễ dàng trong WordPress admin.',
                'items' => [
                    [
                        'title' => 'Thiết kế website',
                        'description' => 'Landing page, website công ty, website dịch vụ.',
                    ],
                    [
                        'title' => 'Mini CMS',
                        'description' => 'Quản trị nội dung gọn hơn WordPress truyền thống.',
                    ],
                    [
                        'title' => 'Tối ưu SEO',
                        'description' => 'Cấu trúc trang, thẻ meta và tốc độ tải trang.',
                    ],
                ],
            ],
            [
                'type' => 'pricing',
                'title' => 'Bảng giá tham khảo',
                'items' => [
                    [
                        'name' => 'Starter',
                        'price' => '5.000.000đ',
                        'features' => ['1 landing page', 'Form liên hệ', 'Responsive'],
                    ],
                    [
                        'name' => 'Business',
                        'price' => '12.000.000đ',
                        'features' => ['5-7 trang', 'Quản trị nội dung', 'SEO cơ bản'],
                    ],
                ],
            ],
            [
                'type' => 'faq',
                'title' => 'Câu hỏi thường gặp',
                'items' => [
                    [
                        'question' => 'Có cần dùng WordPress builder không?',
                        'answer' => 'Không bắt buộc. Theme render bằng section riêng để tránh khách làm vỡ layout.',
                    ],
                    [
                        'question' => 'Có sửa nội dung được không?',
                        'answer' => 'Có. Nội dung nằm trong WordPress admin, không cần sửa file HTML.',
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'title' => 'Sẵn sàng triển khai website nhẹ hơn WordPress truyền thống?',
                'description' => 'Dùng WordPress làm lõi CMS, còn giao diện và section do PMEDIA kiểm soát.',
                'button_text' => 'Liên hệ PMEDIA',
                'button_link' => '/lien-he',
            ],
        ];

        return wp_json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
        set_transient(
            'pmedia_ai_admin_notice_' . get_current_user_id(),
            [
                'message' => $message,
                'type' => $type,
            ],
            60
        );
    }
}
