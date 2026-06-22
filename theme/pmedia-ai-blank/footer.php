<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
</main>

<footer class="site-footer">
    <div class="pmedia-container site-footer-inner">
        <div>
            <strong><?php echo esc_html(get_bloginfo('name')); ?></strong>
            <p><?php echo esc_html(get_bloginfo('description')); ?></p>
        </div>

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
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
