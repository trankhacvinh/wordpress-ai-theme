<?php
if (!defined('ABSPATH')) {
    exit;
}

$setting = static function (string $key, $default = '') {
    return function_exists('pmedia_ai_setting') ? pmedia_ai_setting($key, $default) : $default;
};

$parse_links = static function (string $text): array {
    if (function_exists('pmedia_ai_parse_links')) {
        return pmedia_ai_parse_links($text);
    }

    $items = [];
    foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '[') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line, 2));
        if (!empty($parts[0])) {
            $items[] = ['label' => $parts[0], 'url' => $parts[1] ?? '#'];
        }
    }

    return $items;
};

$columns = function_exists('pmedia_ai_parse_footer_columns') ? pmedia_ai_parse_footer_columns((string) $setting('footer_columns', '')) : [];
$socials = $parse_links((string) $setting('footer_socials', ''));
$footer_layout = sanitize_html_class((string) $setting('footer_layout', 'columns'));
$brand = (string) $setting('brand_name', get_bloginfo('name'));
$description = (string) $setting('brand_description', get_bloginfo('description'));
$contact_title = (string) $setting('footer_contact_title', 'Liên hệ');
$phone = (string) $setting('footer_phone', '');
$email = (string) $setting('footer_email', '');
$address = (string) $setting('footer_address', '');
$copyright = (string) $setting('copyright', '© ' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.');
?>
</main>

<footer class="site-footer site-footer-<?php echo esc_attr($footer_layout); ?>">
    <div class="pmedia-container">
        <div class="site-footer-grid">
            <div class="site-footer-brand">
                <strong><?php echo esc_html($brand); ?></strong>
                <?php if ($description !== '') : ?>
                    <p><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($columns)) : ?>
                <?php foreach ($columns as $column) : ?>
                    <div class="site-footer-column">
                        <?php if (!empty($column['title'])) : ?>
                            <h3 class="site-footer-title"><?php echo esc_html((string) $column['title']); ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($column['items']) && is_array($column['items'])) : ?>
                            <ul class="site-footer-links">
                                <?php foreach ($column['items'] as $item) : ?>
                                    <li><a href="<?php echo esc_url((string) ($item['url'] ?? '#')); ?>"><?php echo esc_html((string) ($item['label'] ?? 'Link')); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <nav class="site-footer-nav" aria-label="<?php esc_attr_e('Footer menu', 'pmedia-ai-blank'); ?>">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'container' => false,
                        'menu_class' => 'site-footer-menu',
                        'fallback_cb' => false,
                        'depth' => 1,
                    ]);
                    ?>
                </nav>
            <?php endif; ?>

            <div class="site-footer-contact">
                <h3 class="site-footer-title"><?php echo esc_html($contact_title); ?></h3>
                <?php if ($phone !== '') : ?><p><strong>ĐT:</strong> <?php echo esc_html($phone); ?></p><?php endif; ?>
                <?php if ($email !== '') : ?><p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p><?php endif; ?>
                <?php if ($address !== '') : ?><p><?php echo nl2br(esc_html($address)); ?></p><?php endif; ?>
                <?php if (!empty($socials)) : ?>
                    <ul class="site-footer-socials">
                        <?php foreach ($socials as $item) : ?>
                            <li><a href="<?php echo esc_url((string) ($item['url'] ?? '#')); ?>" target="_blank" rel="noopener"><?php echo esc_html((string) ($item['label'] ?? 'Social')); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="site-footer-bottom">
            <p class="site-footer-copyright"><?php echo esc_html($copyright); ?></p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
