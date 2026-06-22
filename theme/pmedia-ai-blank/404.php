<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="pmedia-container pmedia-page-container">
    <article class="pmedia-entry pmedia-not-found">
        <h1><?php esc_html_e('Không tìm thấy trang', 'pmedia-ai-blank'); ?></h1>
        <p><?php esc_html_e('Đường dẫn này không tồn tại hoặc đã được thay đổi.', 'pmedia-ai-blank'); ?></p>
        <a class="pmedia-btn pmedia-btn-primary" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Về trang chủ', 'pmedia-ai-blank'); ?></a>
    </article>
</div>
<?php
get_footer();
