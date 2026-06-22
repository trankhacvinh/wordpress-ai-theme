<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="pmedia-container pmedia-page-container">
    <?php if (have_posts()) : ?>
        <div class="pmedia-post-list">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('pmedia-card pmedia-post-card'); ?>>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="pmedia-entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <article class="pmedia-entry">
            <h1><?php esc_html_e('No content found', 'pmedia-ai-blank'); ?></h1>
        </article>
    <?php endif; ?>
</div>
<?php
get_footer();
