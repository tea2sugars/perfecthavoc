<?php /*
Template Name: Fullwidth
*/ ?>

<?php get_header(); ?>
                  
                <!-- Display the Title -->
                <h1 class="pagetitle">
                        <?php the_title(); ?>
                </h1>                                
                                
        <div id="content" class="grid_12">

                <!-- Start the Loop. -->
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
              
                <!-- Display the Post's Content in a div box. -->
                <div class="single_entry">
                        <?php the_content(); ?>
                </div>
                                
                <div class="clear"> </div>
                                
                <!-- Stop The Loop (but note the "else:" - see next line). -->
                <?php endwhile; else: ?>
                                
                <!-- what if there are no Posts? -->
                <p><?php _e('Sorry, no posts matched your criteria.', 'gxg_textdomain'); ?></p>
                                
                <!-- REALLY stop The Loop. -->
                <?php endif; ?>

        </div><!-- #content-->
        
        <div class="clear">
        </div><!-- .clear-->

<?php get_footer(); ?>