<?php get_header(); ?>

        <div id="content" class="grid_9">

	<!-- Start the Loop. -->
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <div class="tour-single">

                <h1 class="title"><?php the_title(); ?></h1>                

                        <?php 
                        $today = date('U') - (60 * 60 * 24);
                        $date = get_post_meta($post->ID, 'gxg_date', true);
                        $time = get_post_meta($post->ID, 'gxg_time', true);
                        $timestamp = strtotime($date);
                        $timestamp = strtotime($date);   
                        $pretty_date_yy = date('Y', $timestamp);
                        $pretty_date_F = date('F', $timestamp);
                        $pretty_date_d = date('d', $timestamp);
                        $pretty_date_l = date('l', $timestamp); 
                        $city = get_post_meta($post->ID, 'gxg_city', true);
                        $venue = get_post_meta($post->ID, 'gxg_venue', true);
                        $url= get_post_meta($post->ID, 'gxg_url', true);
                        $button_text= get_post_meta($post->ID, 'gxg_button_text', true);
                        $soldout= get_post_meta($post->ID, 'gxg_soldout', true);
                        $cancelled = get_post_meta($post->ID, 'gxg_cancelled', true);
                        $more = get_post_meta($post->ID, 'gxg_more', true);
                                                                                        
                                switch($pretty_date_l) /*make weekday translation ready */
                                {
                                                case "Monday":  $pretty_date_l = __('Monday', 'gxg_textdomain');  break;
                                                case "Tuesday":  $pretty_date_l = __('Tuesday', 'gxg_textdomain');  break;
                                                case "Wednesday":  $pretty_date_l = __('Wednesday', 'gxg_textdomain');  break;
                                                case "Thursday":  $pretty_date_l = __('Thursday', 'gxg_textdomain');  break;
                                                case "Friday":  $pretty_date_l = __('Friday', 'gxg_textdomain');  break;
                                                case "Saturday":  $pretty_date_l = __('Saturday', 'gxg_textdomain');  break;
                                                case "Sunday":  $pretty_date_l = __('Sunday', 'gxg_textdomain');  break;
                                                default:     $pretty_date_l = ""; break;
                                        }
                                        
                                        switch($pretty_date_F) /*make month translation ready */
                                        {
                                                case "January":  $pretty_date_F = __('January', 'gxg_textdomain');  break;
                                                case "February":  $pretty_date_F = __('February', 'gxg_textdomain');  break;
                                                case "March":  $pretty_date_F = __('March', 'gxg_textdomain');  break;
                                                case "April":  $pretty_date_F = __('April', 'gxg_textdomain');  break;
                                                case "May":  $pretty_date_F = __('May', 'gxg_textdomain');  break;
                                                case "June":  $pretty_date_F = __('June', 'gxg_textdomain');  break;
                                                case "July":  $pretty_date_F = __('July', 'gxg_textdomain');  break;                                                
                                                case "August":  $pretty_date_F = __('August', 'gxg_textdomain');  break;
                                                case "September":  $pretty_date_F = __('September', 'gxg_textdomain');  break;
                                                case "October":  $pretty_date_F = __('October', 'gxg_textdomain');  break;
                                                case "November":  $pretty_date_F = __('November', 'gxg_textdomain');  break;
                                                case "December":  $pretty_date_F = __('December', 'gxg_textdomain');  break;                                                
                                                default:     $pretty_date_F = ""; break;
                                }
                        
                        ?>

                        <div class="tour-date">
                                <div class="tour-weekday"><?php echo $pretty_date_l; ?> - </div>
                                <div class="tour-month"><?php echo $pretty_date_F; ?></div>
                                <div class="tour-day"><?php echo $pretty_date_d; ?>, </div>   
                                <div class="tour-year"><?php echo $pretty_date_yy; ?></div>
                                <?php if($time){?> <div class="tour-time"> <?php echo $time; ?></div> <?php } ?>                                
                        </div>
                        
                        <div class="tour-venue"> <?php echo $venue; ?> </div>
                        
                        <div class=" tour-city"> <?php echo $city; ?> </div>                      

                        <div class="tour-url">
   
                                <?php if ($cancelled){
                                ?>
                                <div class="cancelled"> <?php _e('Cancelled!', 'gxg_texdivomain') ?>
                                </div>
                                
                                <?php
                                } elseif ($soldout) {                                                                                   
                                ?>
                                <div class="soldout"> <?php _e('Sold Out', 'gxg_texdivomain') ?>
                                </div>                                                     
                                
                                <?php
                                } elseif ($url) {                                                                                   
                                ?>                                                        
                                
                                <a href="<?php echo $url; ?>" class="button2 tour-button"><?php if ($button_text) { echo $button_text; } else { _e('Buy Tickets', 'gxg_texdivomain'); } ?></a>
                                
                                <?php
                                }                                                        
                                ?>

                        </div>
                        
                        <div class="clear"> </div>
                        <div class="tour-more"> <?php echo $more; ?> </div>
                        
                        <div class="clear"> </div>

                        <h6 class="infotitle sb-share"> <?php _e('share:', 'gxg_textdomain') ?></h6>
                        <div class="clear"> </div>
                                
                        <ul class="tour-social sb-share">                               
                                <li class="tweet-button">               
                                        <a href="https://twitter.com/share" 
                                        class="twitter-share-button" 
                                        data-count="none" 
                                        data-lang="en" 
                                        data-url="<?php the_permalink(); ?>" 
                                        data-text="<?php the_title(); ?>"
                                        >
                                        Tweet
                                        </a>
                                        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
                                        </script>  
                           
                                </li>  
                                
                                <li class="fb-button">                                              
                                        <!--[if IE]>
                                        <iframe class="fb-like" src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>%2F&amp;layout=button_count&amp;show_faces=false&amp;width=300&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21&amp;locale=en_US" scrolling="no" frameborder="0" style="border-style:none; overflow:hidden; width:45px; height:21px;" allowTransparency="true">
                                        </iframe>
                                        <![endif]-->
                                        <!--[if !IE]>-->                        
                                        <?php
                                        //test if mobile device
                                        $detect = new Mobile_Detect();                        
                                        if ($detect->isMobile()) {
                                        ?>
                                        <iframe class="fb-like" src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>%2F&amp;layout=button_count&amp;show_faces=false&amp;width=300&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21&amp;locale=en_US" scrolling="no" frameborder="0" style="border-style:none; overflow:hidden; width:45px; height:21px;" allowTransparency="true">
                                        </iframe>                        
                                        <?php
                                        } else {
                                        ?> 
                                        <iframe class="fb-like" src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>%2F&amp;layout=button_count&amp;show_faces=false&amp;width=300&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21&amp;locale=en_US" style="border-style:none; overflow:hidden; width:45px; height:21px;">
                                        </iframe>
                                        <?php
                                        } ?> 
                                        <!--<![endif]-->                                         
                                </li>
                                
                        </ul>
                        
        </div><!-- .tour-single--> 

	<div class="clear"> </div>
	
	<?php endwhile; else: ?>
	
	<!-- what if there are no Posts? -->
	<div id="no_posts">
	<p> <br /> <br />  <?php _e('Sorry, no posts matched your criteria.', 'gxg_texdivomain'); ?> </p>
	</div>
	
	<!-- REALLY stop The Loop. -->
	<?php endif; ?>

        </div><!-- #content-->

        <div id="sidebar" class="grid_3"> 
                       <?php  if ( is_active_sidebar( 'tour_sidebar' ) ) :  ?>
                       <div id="tour_sidebar" class="widget-area">
                            <?php dynamic_sidebar( 'tour_sidebar' ); ?>
                       </div><!-- #tour_sidebar .widget-area -->
                       <?php endif; ?>  
        </div><!-- #sidebar-->

        <div class="clear">
        </div><!-- .clear-->

<?php get_footer(); ?>