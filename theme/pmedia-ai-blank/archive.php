<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="pmedia-container pmedia-page-container">
    <header class="pmedia-archive-header">
        <?php the_archive_title('<h1>', '</h1>'); ?>
        <?php the_archive_description('<div class="pmedia-archive-description">', '</div>'); ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="pmedia-post-list">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('pmedia-card pmedia-post-card'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <a class="pmedia-post-thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large'); ?></a>
                    <?php endif; ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="pmedia-entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php endif; ?>
</div>
<?php
get_footer();
