<?php
if (!defined('ABSPATH')) {
    exit;
}

$section = $pmedia_section ?? [];
$items = $section['items'] ?? [];
?>
<section class="pmedia-section pmedia-faq-section">
    <div class="pmedia-container pmedia-narrow">
        <div class="pmedia-section-heading">
            <h2><?php echo esc_html((string) ($section['title'] ?? 'Câu hỏi thường gặp')); ?></h2>
            <?php if (!empty($section['description'])) : ?>
                <p class="pmedia-section-description"><?php echo esc_html((string) $section['description']); ?></p>
            <?php endif; ?>
        </div>

        <?php if (is_array($items) && !empty($items)) : ?>
            <div class="pmedia-faq-list">
                <?php foreach ($items as $item) : ?>
                    <?php if (!is_array($item)) { continue; } ?>
                    <details class="pmedia-faq-item">
                        <summary><?php echo esc_html((string) ($item['question'] ?? 'Câu hỏi')); ?></summary>
                        <?php if (!empty($item['answer'])) : ?>
                            <p><?php echo esc_html((string) $item['answer']); ?></p>
                        <?php endif; ?>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
