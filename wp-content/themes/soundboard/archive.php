<?php get_header(); ?>

                <div id ="content" class="grid_9">
                        
                        <div id="blog_content">
                                
                        <h1 class="pagetitle"> 
                                <?php
                                global $wp_query;
                                $curauth = $wp_query->get_queried_object();
                                ?>
        
                                <?php /* If this is a category archive */ if (is_category()) { ?>
                                <?php _e('Category:', 'gxg_textdomain') ?> <?php echo single_cat_title(); ?>
        
                                <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
                                <?php _e('All posts on', 'gxg_textdomain') ?> <?php the_time('F jS, Y'); ?>
        
                                <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
                                <?php _e('All posts in', 'gxg_textdomain') ?>  <?php the_time('F Y'); ?>
        
                                <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
                                <?php _e('All posts in', 'gxg_textdomain') ?>  <?php the_time('Y'); ?>
        
                                <?php /* If this is an author archive */ } elseif (is_author()) { ?>
                                <?php _e('Author:', 'gxg_textdomain') ?> <?php echo $curauth->nickname; ?>
        
                                <?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
                                <?php _e('Blog Archives', 'gxg_textdomain') ?>
                                <?php } ?>                        
                        </h1>               

                        <?php
                        get_template_part('loop');
                        ?>

                        <div id="pagination">
                                <?php gg_pagination(); ?>
                        </div><!-- #pagination-->

                        </div><!-- #blog_content-->

                </div><!-- #content-->

                <div id="sidebar" class="grid_3">
                        <?php get_sidebar(); ?>
                </div><!-- #sidebar-->

                <div class="clear">
                </div><!-- .clear-->

<?php get_footer(); ?>
