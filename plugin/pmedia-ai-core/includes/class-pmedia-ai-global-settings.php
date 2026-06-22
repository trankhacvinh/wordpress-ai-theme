<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Global_Settings
{
    public const OPTION_NAME = 'pmedia_ai_global_settings';

    public static function hooks(): void
    {
        add_action('admin_menu', [self::class, 'register_submenu']);
        add_action('admin_post_pmedia_ai_save_global_settings', [self::class, 'handle_save']);
        add_action('wp_head', [self::class, 'print_custom_css'], 30);
    }

    public static function defaults(): array
    {
        return [
            'brand_name' => get_bloginfo('name'),
            'brand_description' => get_bloginfo('description'),
            'logo_url' => '',
            'mobile_logo_url' => '',
            'nav_mode' => 'wordpress',
            'manual_nav' => "Trang chủ | /\nGiới thiệu | /gioi-thieu\nDịch vụ | /dich-vu\nLiên hệ | /lien-he",
            'mobile_nav_mode' => 'inherit',
            'mobile_nav' => '',
            'header_layout' => 'default',
            'sticky_header' => '1',
            'header_cta_text' => '',
            'header_cta_link' => '/lien-he',
            'desktop_header_cta_visible' => '1',
            'mobile_header_cta_visible' => '',
            'mobile_menu_style' => 'drawer',
            'mobile_breakpoint' => '900',
            'container_width' => '1180',
            'footer_layout' => 'columns',
            'footer_columns' => "[Công ty]\nGiới thiệu | /gioi-thieu\nDịch vụ | /dich-vu\n\n[Hỗ trợ]\nLiên hệ | /lien-he\nCâu hỏi thường gặp | /cau-hoi-thuong-gap",
            'footer_contact_title' => 'Liên hệ',
            'footer_phone' => '',
            'footer_email' => '',
            'footer_address' => '',
            'footer_socials' => "Facebook | #\nZalo | #",
            'copyright' => '© ' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.',
            'custom_css' => '',
        ];
    }

    public static function get_settings(): array
    {
        $settings = get_option(self::OPTION_NAME, []);
        if (!is_array($settings)) {
            $settings = [];
        }

        return array_merge(self::defaults(), $settings);
    }

    public static function get(string $key, $default = '')
    {
        $settings = self::get_settings();
        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public static function register_submenu(): void
    {
        add_submenu_page(
            'pmedia-ai-core',
            __('Global Settings', 'pmedia-ai-core'),
            __('Global Settings', 'pmedia-ai-core'),
            'manage_options',
            'pmedia-ai-global-settings',
            [self::class, 'render_page']
        );
    }

    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $s = self::get_settings();
        ?>
        <div class="wrap pmedia-ai-admin-page pmedia-ai-global-settings">
            <h1>PMEDIA AI Global Settings</h1>
            <p class="description">Quản lý header, menu desktop/mobile, footer và responsive cho theme PMEDIA AI Blank.</p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="pmedia_ai_save_global_settings">
                <?php wp_nonce_field('pmedia_ai_save_global_settings', 'pmedia_ai_global_settings_nonce'); ?>

                <div class="pmedia-ai-admin-grid pmedia-ai-admin-grid-2-1">
                    <div class="pmedia-ai-card">
                        <h2>Header / Brand</h2>
                        <?php self::text_field('brand_name', 'Tên thương hiệu', $s); ?>
                        <?php self::textarea_field('brand_description', 'Mô tả ngắn', $s, 3); ?>
                        <?php self::image_field('logo_url', 'Logo desktop URL', $s); ?>
                        <?php self::image_field('mobile_logo_url', 'Logo mobile URL', $s); ?>
                        <?php self::select_field('header_layout', 'Header layout', $s, ['default' => 'Logo trái, menu phải', 'centered' => 'Logo giữa', 'split' => 'Menu chia hai bên']); ?>
                        <?php self::checkbox_field('sticky_header', 'Sticky header', $s); ?>
                        <?php self::text_field('header_cta_text', 'Text nút CTA trên header', $s); ?>
                        <?php self::text_field('header_cta_link', 'Link nút CTA', $s); ?>
                        <?php self::checkbox_field('desktop_header_cta_visible', 'Hiện CTA trên desktop', $s); ?>
                        <?php self::checkbox_field('mobile_header_cta_visible', 'Hiện CTA trong menu mobile', $s); ?>
                    </div>

                    <div class="pmedia-ai-card">
                        <h2>Responsive / Mobile</h2>
                        <?php self::select_field('mobile_menu_style', 'Kiểu menu mobile', $s, ['dropdown' => 'Dropdown dưới header', 'drawer' => 'Drawer trượt bên phải', 'fullscreen' => 'Fullscreen overlay']); ?>
                        <?php self::number_field('mobile_breakpoint', 'Mobile breakpoint px', $s); ?>
                        <?php self::number_field('container_width', 'Container width px', $s); ?>
                        <p class="description">CSS hiện hỗ trợ tối ưu ở các breakpoint phổ biến 1024/900/768/560px. Trường breakpoint dùng để thêm class/setting và phục vụ mở rộng sau.</p>
                    </div>
                </div>

                <div class="pmedia-ai-admin-grid">
                    <div class="pmedia-ai-card">
                        <h2>Navigation</h2>
                        <?php self::select_field('nav_mode', 'Nguồn menu desktop', $s, ['wordpress' => 'WordPress Menu: Primary', 'manual' => 'Manual menu bên dưới']); ?>
                        <?php self::textarea_field('manual_nav', 'Manual desktop menu, mỗi dòng: Label | URL', $s, 8); ?>
                        <?php self::select_field('mobile_nav_mode', 'Menu mobile', $s, ['inherit' => 'Dùng giống desktop', 'manual' => 'Dùng menu mobile riêng']); ?>
                        <?php self::textarea_field('mobile_nav', 'Manual mobile menu riêng', $s, 8); ?>
                    </div>

                    <div class="pmedia-ai-card">
                        <h2>Footer</h2>
                        <?php self::select_field('footer_layout', 'Footer layout', $s, ['columns' => 'Nhiều cột', 'compact' => 'Compact']); ?>
                        <?php self::textarea_field('footer_columns', 'Footer columns. Dùng [Tên cột], sau đó Label | URL', $s, 10); ?>
                        <?php self::text_field('footer_contact_title', 'Tiêu đề cột liên hệ', $s); ?>
                        <?php self::text_field('footer_phone', 'Điện thoại', $s); ?>
                        <?php self::text_field('footer_email', 'Email', $s); ?>
                        <?php self::textarea_field('footer_address', 'Địa chỉ', $s, 3); ?>
                        <?php self::textarea_field('footer_socials', 'Social links, mỗi dòng: Label | URL', $s, 5); ?>
                        <?php self::text_field('copyright', 'Copyright', $s); ?>
                    </div>
                </div>

                <div class="pmedia-ai-card pmedia-ai-card-wide">
                    <h2>Custom CSS nâng cao</h2>
                    <p class="description">Chỉ admin/dev nên dùng. Nội dung sẽ in ra frontend trong thẻ style.</p>
                    <textarea name="custom_css" rows="12" class="large-text code"><?php echo esc_textarea((string) $s['custom_css']); ?></textarea>
                </div>

                <p><button type="submit" class="button button-primary button-hero">Lưu Global Settings</button></p>
            </form>
        </div>
        <?php
    }

    public static function handle_save(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'pmedia-ai-core'));
        }

        if (!isset($_POST['pmedia_ai_global_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pmedia_ai_global_settings_nonce'])), 'pmedia_ai_save_global_settings')) {
            wp_die(esc_html__('Nonce không hợp lệ.', 'pmedia-ai-core'));
        }

        $defaults = self::defaults();
        $settings = [];

        foreach ($defaults as $key => $default) {
            $value = wp_unslash($_POST[$key] ?? '');
            if (in_array($key, ['sticky_header', 'desktop_header_cta_visible', 'mobile_header_cta_visible'], true)) {
                $settings[$key] = !empty($_POST[$key]) ? '1' : '';
                continue;
            }

            if (in_array($key, ['brand_description', 'manual_nav', 'mobile_nav', 'footer_columns', 'footer_address', 'footer_socials'], true)) {
                $settings[$key] = sanitize_textarea_field($value);
                continue;
            }

            if ($key === 'custom_css') {
                $settings[$key] = wp_strip_all_tags((string) $value);
                continue;
            }

            if (in_array($key, ['mobile_breakpoint', 'container_width'], true)) {
                $settings[$key] = (string) max(320, min(1920, (int) $value));
                continue;
            }

            $settings[$key] = sanitize_text_field($value);
        }

        update_option(self::OPTION_NAME, $settings, false);
        wp_safe_redirect(admin_url('admin.php?page=pmedia-ai-global-settings&updated=1'));
        exit;
    }

    public static function parse_links(string $text): array
    {
        $items = [];
        $lines = preg_split('/\r\n|\r|\n/', $text);
        if (!is_array($lines)) {
            return [];
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '[') === 0) {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            if (empty($parts[0])) {
                continue;
            }
            $items[] = ['label' => $parts[0], 'url' => $parts[1] ?? '#'];
        }

        return $items;
    }

    public static function parse_footer_columns(string $text): array
    {
        $columns = [];
        $current = null;
        $lines = preg_split('/\r\n|\r|\n/', $text);
        if (!is_array($lines)) {
            return [];
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\[(.+)\]$/', $line, $matches)) {
                if ($current !== null) {
                    $columns[] = $current;
                }
                $current = ['title' => trim($matches[1]), 'items' => []];
                continue;
            }

            if ($current === null) {
                $current = ['title' => 'Liên kết', 'items' => []];
            }

            $parts = array_map('trim', explode('|', $line, 2));
            if (!empty($parts[0])) {
                $current['items'][] = ['label' => $parts[0], 'url' => $parts[1] ?? '#'];
            }
        }

        if ($current !== null) {
            $columns[] = $current;
        }

        return $columns;
    }

    public static function print_custom_css(): void
    {
        $s = self::get_settings();
        $container = max(320, min(1920, (int) $s['container_width']));
        $css = ':root{--pmedia-container-width:' . $container . 'px;}';
        if (!empty($s['custom_css'])) {
            $css .= "\n" . (string) $s['custom_css'];
        }

        printf("\n<style id=\"pmedia-ai-global-settings-css\">%s</style>\n", wp_strip_all_tags($css));
    }

    private static function text_field(string $key, string $label, array $s): void
    {
        printf('<p><label><strong>%s</strong></label><input type="text" name="%s" class="widefat" value="%s"></p>', esc_html($label), esc_attr($key), esc_attr((string) ($s[$key] ?? '')));
    }

    private static function number_field(string $key, string $label, array $s): void
    {
        printf('<p><label><strong>%s</strong></label><input type="number" name="%s" class="widefat" value="%s"></p>', esc_html($label), esc_attr($key), esc_attr((string) ($s[$key] ?? '')));
    }

    private static function textarea_field(string $key, string $label, array $s, int $rows = 4): void
    {
        printf('<p><label><strong>%s</strong></label><textarea name="%s" rows="%d" class="widefat">%s</textarea></p>', esc_html($label), esc_attr($key), $rows, esc_textarea((string) ($s[$key] ?? '')));
    }

    private static function select_field(string $key, string $label, array $s, array $options): void
    {
        echo '<p><label><strong>' . esc_html($label) . '</strong></label><select name="' . esc_attr($key) . '" class="widefat">';
        foreach ($options as $value => $text) {
            echo '<option value="' . esc_attr((string) $value) . '" ' . selected((string) ($s[$key] ?? ''), (string) $value, false) . '>' . esc_html((string) $text) . '</option>';
        }
        echo '</select></p>';
    }

    private static function checkbox_field(string $key, string $label, array $s): void
    {
        printf('<p><label><input type="checkbox" name="%s" value="1" %s> <strong>%s</strong></label></p>', esc_attr($key), checked(!empty($s[$key]), true, false), esc_html($label));
    }

    private static function image_field(string $key, string $label, array $s): void
    {
        printf('<p><label><strong>%s</strong></label><span class="pmedia-ai-image-line"><input type="text" name="%s" class="widefat pmedia-ai-global-image" value="%s"><button type="button" class="button pmedia-ai-global-pick-image">Chọn ảnh</button></span></p>', esc_html($label), esc_attr($key), esc_attr((string) ($s[$key] ?? '')));
    }
}

function pmedia_ai_global_settings(): array
{
    return PMEDIA_AI_Global_Settings::get_settings();
}

function pmedia_ai_setting(string $key, $default = '')
{
    return PMEDIA_AI_Global_Settings::get($key, $default);
}

function pmedia_ai_parse_links(string $text): array
{
    return PMEDIA_AI_Global_Settings::parse_links($text);
}

function pmedia_ai_parse_footer_columns(string $text): array
{
    return PMEDIA_AI_Global_Settings::parse_footer_columns($text);
}
