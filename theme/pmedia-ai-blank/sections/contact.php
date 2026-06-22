<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
?>
<section class="pmedia-section pmedia-contact-section">
    <div class="pmedia-container pmedia-contact-grid">
        <div>
            <p class="pmedia-eyebrow"><?php echo esc_html((string) ($section['eyebrow'] ?? 'Liên hệ')); ?></p>
            <h2><?php echo esc_html((string) ($section['title'] ?? 'Trao đổi với chúng tôi')); ?></h2>
            <?php if (!empty($section['description'])) : ?>
                <p class="pmedia-section-description"><?php echo esc_html((string) $section['description']); ?></p>
            <?php endif; ?>
        </div>

        <div class="pmedia-card pmedia-contact-card">
            <?php if (!empty($section['phone'])) : ?>
                <p><strong>Điện thoại:</strong> <?php echo esc_html((string) $section['phone']); ?></p>
            <?php endif; ?>
            <?php if (!empty($section['email'])) : ?>
                <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr((string) $section['email']); ?>"><?php echo esc_html((string) $section['email']); ?></a></p>
            <?php endif; ?>
            <?php if (!empty($section['address'])) : ?>
                <p><strong>Địa chỉ:</strong> <?php echo esc_html((string) $section['address']); ?></p>
            <?php endif; ?>
            <?php if (!empty($section['form_shortcode'])) : ?>
                <div class="pmedia-contact-form"><?php echo do_shortcode((string) $section['form_shortcode']); ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>
