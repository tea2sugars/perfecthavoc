<?php get_header(); ?>

                <div id ="content" class="grid_12">
                        
                        <div id="blog_content">

                                <?php
                                get_template_part('loop');
                                ?>

                                <div id="pagination">
                                        <?php gg_pagination(); ?>
                                </div><!-- #pagination-->

                        </div><!-- #blog_content-->

                 </div><!-- #content-->

                

                 <div class="clear">
                 </div><!-- .clear-->

<?php get_footer(); ?>