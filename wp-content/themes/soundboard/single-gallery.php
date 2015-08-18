        <?php get_header(); ?>

        <div id="content" class="grid_12">

                <!-- Start the Loop. -->
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                <div class="clear"> </div>


                <!-- Display the Title -->
                <h1 class="pagetitle"><?php the_title(); ?></h1>
                <div class="clear"> </div>


                <!-- Display the gallery images in a div box. -->
                <div class="gallery-images">
                
                
                        <ul> 
	                        	<?php 
	                        	
				$images = rwmb_meta( 'gxg_gallery_images', 'type=image&size=square1' );
				


				foreach ( $images as $image ) {

				$caption = $image['caption'];
				$caption = htmlspecialchars($caption, ENT_QUOTES);				
				    		
				echo "<li><a class='pretty_image' title='' alt='{$caption}' data-rel='prettyPhoto[pp_gallery]' href='{$image['full_url']}'><img src='{$image['url']}' /></a></li>";
				 
				 } 




                    
	                        	?>
                        </ul>                
                
                 </div><!-- .gallery-images-->
      

                <div class="clear"> </div>


                <!-- Stop The Loop (but note the "else:" - see next line). -->
                <?php endwhile; else: ?>

                <!-- what if there are no Posts? -->
                <p><?php _e('Sorry, no posts matched your criteria.', 'gxg_textdomain') ?></p>

                <!-- REALLY stop The Loop. -->
                <?php endif; ?>
        
                <?php if (!of_get_option('gg_commentremove')) { ?> 
                <div id="comments" class="grid_9">
                        <?php comments_template(); ?>
                </div><!-- #comments-->  
                <?php } ?>  

        </div><!-- #content-->

        <div class="clear">
        </div><!-- .clear-->

        <?php get_footer(); ?>