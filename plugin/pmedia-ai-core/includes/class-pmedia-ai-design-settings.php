<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Design_Settings
{
    public const OPTION_NAME = 'pmedia_ai_design_settings';

    public static function hooks(): void
    {
        add_action('admin_menu', [self::class, 'register_submenu']);
        add_action('admin_post_pmedia_ai_save_design_settings', [self::class, 'handle_save']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_fonts']);
        add_action('wp_head', [self::class, 'print_design_css'], 31);
    }

    public static function defaults(): array
    {
        return [
            'google_fonts_url' => '',
            'body_font_family' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            'heading_font_family' => 'inherit',
            'base_font_size' => '16',
            'body_line_height' => '1.65',
            'heading_line_height' => '1.15',
            'heading_letter_spacing' => '-0.03em',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'text_color' => '#111827',
            'muted_color' => '#64748b',
            'border_radius' => '18',
            'button_radius' => '999',
            'section_spacing' => '80',
            'card_shadow' => '0 18px 50px rgba(15, 23, 42, 0.10)',
            'custom_root_vars' => '',
        ];
    }

    public static function settings(): array
    {
        $settings = get_option(self::OPTION_NAME, []);
        return array_merge(self::defaults(), is_array($settings) ? $settings : []);
    }

    public static function register_submenu(): void
    {
        add_submenu_page('pmedia-ai-core', __('Design Settings', 'pmedia-ai-core'), __('Design Settings', 'pmedia-ai-core'), 'manage_options', 'pmedia-ai-design-settings', [self::class, 'render_page']);
    }

    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $s = self::settings();
        ?>
        <div class="wrap pmedia-ai-admin-page pmedia-ai-design-settings">
            <h1>PMEDIA AI Design Settings</h1>
            <p class="description">Quản lý font, màu sắc, typography và style tokens dùng chung toàn website.</p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="pmedia_ai_save_design_settings">
                <?php wp_nonce_field('pmedia_ai_save_design_settings', 'pmedia_ai_design_settings_nonce'); ?>

                <div class="pmedia-ai-admin-grid">
                    <div class="pmedia-ai-card">
                        <h2>Fonts</h2>
                        <?php self::text_field('google_fonts_url', 'Google Fonts CSS URL', $s, 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'); ?>
                        <?php self::text_field('body_font_family', 'Body font-family', $s); ?>
                        <?php self::text_field('heading_font_family', 'Heading font-family', $s); ?>
                        <p class="description">Ví dụ font-family: <code>Inter, sans-serif</code>. Nếu dùng Google Fonts, dán link CSS vào ô trên.</p>
                    </div>

                    <div class="pmedia-ai-card">
                        <h2>Typography</h2>
                        <?php self::number_field('base_font_size', 'Base font size px', $s); ?>
                        <?php self::text_field('body_line_height', 'Body line-height', $s); ?>
                        <?php self::text_field('heading_line_height', 'Heading line-height', $s); ?>
                        <?php self::text_field('heading_letter_spacing', 'Heading letter-spacing', $s); ?>
                    </div>
                </div>

                <div class="pmedia-ai-admin-grid">
                    <div class="pmedia-ai-card">
                        <h2>Colors</h2>
                        <?php self::color_field('primary_color', 'Primary color', $s); ?>
                        <?php self::color_field('secondary_color', 'Secondary color', $s); ?>
                        <?php self::color_field('text_color', 'Text color', $s); ?>
                        <?php self::color_field('muted_color', 'Muted color', $s); ?>
                    </div>

                    <div class="pmedia-ai-card">
                        <h2>Shape / Spacing</h2>
                        <?php self::number_field('border_radius', 'Border radius px', $s); ?>
                        <?php self::number_field('button_radius', 'Button radius px', $s); ?>
                        <?php self::number_field('section_spacing', 'Section spacing px', $s); ?>
                        <?php self::text_field('card_shadow', 'Card shadow CSS', $s); ?>
                    </div>
                </div>

                <div class="pmedia-ai-card pmedia-ai-card-wide">
                    <h2>Custom Root Variables</h2>
                    <p class="description">Mỗi dòng một biến CSS, ví dụ: <code>--hero-height: 92vh;</code></p>
                    <textarea name="custom_root_vars" rows="10" class="large-text code"><?php echo esc_textarea((string) $s['custom_root_vars']); ?></textarea>
                </div>

                <p><button type="submit" class="button button-primary button-hero">Lưu Design Settings</button></p>
            </form>
        </div>
        <?php
    }

    public static function handle_save(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'pmedia-ai-core'));
        }
        if (!isset($_POST['pmedia_ai_design_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pmedia_ai_design_settings_nonce'])), 'pmedia_ai_save_design_settings')) {
            wp_die(esc_html__('Nonce không hợp lệ.', 'pmedia-ai-core'));
        }

        $defaults = self::defaults();
        $settings = [];
        foreach ($defaults as $key => $default) {
            $value = wp_unslash($_POST[$key] ?? '');
            if ($key === 'google_fonts_url') {
                $settings[$key] = esc_url_raw((string) $value);
            } elseif ($key === 'custom_root_vars') {
                $settings[$key] = wp_strip_all_tags((string) $value);
            } elseif (in_array($key, ['base_font_size', 'border_radius', 'button_radius', 'section_spacing'], true)) {
                $settings[$key] = (string) max(0, min(240, (int) $value));
            } elseif (in_array($key, ['primary_color', 'secondary_color', 'text_color', 'muted_color'], true)) {
                $settings[$key] = sanitize_hex_color((string) $value) ?: (string) $default;
            } else {
                $settings[$key] = sanitize_text_field((string) $value);
            }
        }

        update_option(self::OPTION_NAME, $settings, false);
        wp_safe_redirect(admin_url('admin.php?page=pmedia-ai-design-settings&updated=1'));
        exit;
    }

    public static function enqueue_fonts(): void
    {
        $s = self::settings();
        $url = (string) $s['google_fonts_url'];
        if ($url !== '' && strpos($url, 'fonts.googleapis.com') !== false) {
            wp_enqueue_style('pmedia-ai-google-fonts', esc_url($url), [], null);
        }
    }

    public static function print_design_css(): void
    {
        $s = self::settings();
        $vars = [
            '--pmedia-primary' => $s['primary_color'],
            '--pmedia-secondary' => $s['secondary_color'],
            '--pmedia-text' => $s['text_color'],
            '--pmedia-muted' => $s['muted_color'],
            '--pmedia-font-body' => $s['body_font_family'],
            '--pmedia-font-heading' => $s['heading_font_family'],
            '--pmedia-base-font-size' => ((int) $s['base_font_size']) . 'px',
            '--pmedia-body-line-height' => $s['body_line_height'],
            '--pmedia-heading-line-height' => $s['heading_line_height'],
            '--pmedia-heading-letter-spacing' => $s['heading_letter_spacing'],
            '--pmedia-radius' => ((int) $s['border_radius']) . 'px',
            '--pmedia-radius-lg' => max(0, (int) $s['border_radius'] + 8) . 'px',
            '--pmedia-button-radius' => ((int) $s['button_radius']) . 'px',
            '--pmedia-section-spacing' => ((int) $s['section_spacing']) . 'px',
            '--pmedia-shadow' => $s['card_shadow'],
        ];

        $css = ':root{';
        foreach ($vars as $key => $value) {
            $css .= $key . ':' . esc_attr((string) $value) . ';';
        }
        if (!empty($s['custom_root_vars'])) {
            $css .= wp_strip_all_tags((string) $s['custom_root_vars']);
        }
        $css .= '}body{font-family:var(--pmedia-font-body);font-size:var(--pmedia-base-font-size);line-height:var(--pmedia-body-line-height);color:var(--pmedia-text);}h1,h2,h3,h4,h5,h6{font-family:var(--pmedia-font-heading);line-height:var(--pmedia-heading-line-height);letter-spacing:var(--pmedia-heading-letter-spacing);} .pmedia-section{padding-block:var(--pmedia-section-spacing);} .pmedia-btn{border-radius:var(--pmedia-button-radius);}';

        printf("\n<style id=\"pmedia-ai-design-settings-css\">%s</style>\n", $css);
    }

    private static function text_field(string $key, string $label, array $s, string $placeholder = ''): void
    {
        printf('<p><label><strong>%s</strong></label><input type="text" name="%s" class="widefat" value="%s" placeholder="%s"></p>', esc_html($label), esc_attr($key), esc_attr((string) ($s[$key] ?? '')), esc_attr($placeholder));
    }

    private static function number_field(string $key, string $label, array $s): void
    {
        printf('<p><label><strong>%s</strong></label><input type="number" name="%s" class="widefat" value="%s"></p>', esc_html($label), esc_attr($key), esc_attr((string) ($s[$key] ?? '')));
    }

    private static function color_field(string $key, string $label, array $s): void
    {
        printf('<p><label><strong>%s</strong></label><input type="color" name="%s" value="%s"> <code>%s</code></p>', esc_html($label), esc_attr($key), esc_attr((string) ($s[$key] ?? '#000000')), esc_html((string) ($s[$key] ?? '')));
    }
}
