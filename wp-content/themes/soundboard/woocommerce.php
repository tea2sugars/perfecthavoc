<?php get_header(); ?>

        <div id="content" class="grid_9">
                
                <div class="page-content">
                
                        <?php woocommerce_content(); ?>
                
                </div><!-- .page-content-->
                                
        </div><!-- #content-->

                <?php if ( is_active_sidebar( 'woocommerce_sidebar' ) ) :  ?>
                
                <div id="sidebar" class="grid_3">  
                         <div id="woocommerce_sidebar" class="widget-area">
                              <?php dynamic_sidebar( 'woocommerce_sidebar' ); ?>
                         </div><!-- #woocommerce_sidebar .widget-area -->
                </div><!-- #sidebar-->
                
                <?php endif; ?> 

        <div class="clear">
        </div><!-- .clear-->

<?php get_footer(); ?>