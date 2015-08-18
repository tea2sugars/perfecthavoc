<?php
/*
Template Name: Home
*/
?>

<?php get_header(); ?>

<div id ="content" class="home-content">
  <div class="headerhead" style="text-align:center;">
  <h1 style="text-transform:uppercase; font-size:50px;">Welcome to Perfect Havoc</h1>&nbsp;
</div>
        <?php if ( is_active_sidebar( 'homeleft_sidebar' ) ) : ?>
        <div id="homew1" class="widget-area grid_3">
                        <?php dynamic_sidebar( 'homeleft_sidebar' ); ?>
         </div><!-- #homew1 .widget-area -->
        <?php endif; ?>
        
        
                
        <div id="home-news">

	       <?php if  ( of_get_option('gg_titlecenter') ) {?>
                <h3 class="widgettitle"><?php echo of_get_option('gg_titlecenter'); ?></h3>
                <?php } ?>
        
                <!-- Start the Loop. -->
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>                          	                               
                        <?php the_content(); ?> 
                <?php endwhile; endif; ?>    
                
                <div class="clear">
                </div><!-- .clear-->       
 
        
                <?php if  ( of_get_option('gg_newstitle') ) {?>
                <h3 class="widgettitle"><?php echo of_get_option('gg_newstitle'); ?></h3>    
                <?php } ?>     
        
                <?php if ( of_get_option('gg_headlines') ) {
        
                        $query = new WP_Query();
                        $post_number = of_get_option('gg_headlines');
                        $query->query('posts_per_page=' . $post_number);
                        while ($query->have_posts()) : $query->the_post(); 
                        ?>
                                        
                                <div class="headlines">
                                        
	                                        <div class="post-info-h">                                        
	                                                <div class="time-ago">                                                
                                                                <?php if (of_get_option('gg_notimeago')) { ?>
                                                                ++ <?php echo the_date(); ?> ++
                                                                <?php } else { ?>        
                                                                ++ <?php echo time_ago(); ?> ++
                                                                <?php } ?> 
	                                                </div>  
	                              
	                              		   <?php if (!of_get_option('gg_commentremove')) { ?> 
	                                                <div class="comment-nr">
	                                                        <a href="<?php comments_link(); ?>">
	                                                        <?php                                                
	                                                        echo comments_number(' 0 ', ' 1 ', ' % ');
	                                                        _e('comments', 'gxg_textdomain');
	                                                        ?> </a> ++
	                                                </div>
	                                                <?php } ?>
	                                                
	                                        </div><!-- .post-info-->                           
	  
	                                        
	                                        <h1 class="news-home-title">
	                                                <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
	                                                <?php the_title(); ?>
	                                                </a>
	                                        </h1> 
                                        
                			<?php if  ( of_get_option('gg_previewimage')  and  has_post_thumbnail() ){?>
                			
                                                <div class="home-post-content">
                                                        <div class="tnail-h">
                                                                <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
					                        <?php the_post_thumbnail('video', array('title' => ' ')); ?>
                                                                </a>
                                                        </div> <!-- .tnail -->
		                                       
		                                        <div class="entry-ht">
		                                                <?php
                                                                $options_homeposts = of_get_option('gg_homeposts');
                
                                                                if ( of_get_option('gg_homeposts') == 'excerpt' ) {
                                                                        the_excerpt();
                                                                } else {  
                                                                        global $more;
                                                                        $more = 0; 
                                                                        the_content(' read more...');      
                                                                } ?>
		                                        </div><!-- .entry-h -->
                                                </div><!-- .home-post-news -->
                			<?php } 
                			
                			else {
                                        ?>                 			
	                                        <div class="entry-h">
                                                        
                                                        <?php
                                                        $options_homeposts = of_get_option('gg_homeposts');
        
                                                        if ( of_get_option('gg_homeposts') == 'excerpt' ) {
                                                                the_excerpt();
                                                        } else {  
                                                                global $more;
                                                                $more = 0; 
                                                                the_content(' read more...');      
                                                        } ?>
     
	                                        </div><!-- .entry -->                			
                                        <?php
                                        } ?> 
                                        <!--<![endif]-->
             
                                </div><!-- .headlines --> 
                    
                        <?php endwhile; wp_reset_query(); ?>
        
                <div class="clear">
                </div><!-- .clear-->
                
                        <?php if ( of_get_option('gg_homenewsbutton') ) {
                                $posts_page_id = get_option( 'page_for_posts');
                                $posts_page = get_page( $posts_page_id);
                                $posts_page_title = of_get_option('gg_homenewsbutton');
                                $posts_page_url = get_page_uri($posts_page_id  );
                                ?>
                                <a href="<?php echo $posts_page_url; ?>" class="button2 homenewsbutton"><?php echo $posts_page_title; ?></a>
                         <?php }                

                                   
                }
                ?>
  
        </div><!-- #home-news-->

        
        <?php if ( is_active_sidebar( 'homeright_sidebar' ) ) : ?>
                <div id="homew3" class="widget-area grid_3">
                        <?php dynamic_sidebar( 'homeright_sidebar' ); ?>
                </div><!-- #homew3 .widget-area -->
        <?php endif; ?>

</div><!-- #content-->
        
<div class="clear">
</div><!-- .clear-->

<?php get_footer(); ?>