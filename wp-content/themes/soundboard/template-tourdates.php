<?php
/*
Template Name: Tour Dates
*/
?>

<?php get_header(); ?>
<h1 class="pagetitle"> <?php _e('Upcoming Dates', 'gxg_textdomain'); ?> </h1>
        <div id ="content" class="grid_12">
                
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>                          	                               
                        <?php the_content(); ?> 
                <?php endwhile; endif; ?>    
                
                <div class="clear">
                </div><!-- .clear-->   
                
                
                
                <div class="tour upcoming">
                                
                        <?php  
                        global $post;
                        
                        $args = array(
                                        'orderby' => 'meta_value',
                                        'meta_key' => 'gxg_date',                                        
                                        'meta_value' => strftime("%Y/%m/%d", time()- (60 * 60 * 24) ),
                                        'meta_compare' => '>',
                                        'order_by' => 'meta_value',                                        
                                        'order' => 'ASC',
                                        'post_type' => 'tourdates',
                                        'posts_per_page' => -1 );

                        
                        $loop = new WP_Query( $args );
                            
                                
                        if ($loop->have_posts()) : while ( $loop->have_posts() ) : $loop->the_post();
                                
                                $today = date('U') - (60 * 60 * 24);
                                $date = get_post_meta($post->ID, 'gxg_date', true);
                                $time = get_post_meta($post->ID, 'gxg_time', true);
                                $timestamp = strtotime($date);
                                $timestamp = strtotime($date);   
                                $pretty_date_yy = date('Y', $timestamp);
                                $pretty_date_M = date('M', $timestamp);
                                $pretty_date_d = date('d', $timestamp);
                                $pretty_date_D = date('D', $timestamp); 
                                $city = get_post_meta($post->ID, 'gxg_city', true);
                                $venue = get_post_meta($post->ID, 'gxg_venue', true);
                                $url= get_post_meta($post->ID, 'gxg_url', true);
                                $button_text= get_post_meta($post->ID, 'gxg_button_text', true);
                                $soldout= get_post_meta($post->ID, 'gxg_soldout', true);
                                $cancelled = get_post_meta($post->ID, 'gxg_cancelled', true);
                                $more = get_post_meta($post->ID, 'gxg_more', true);
                                                                                                
                                        switch($pretty_date_D) /*make weekday translation ready */
                                        {
                                                case "Mon":  $pretty_date_D = __('Mon', 'gxg_textdomain');  break;
                                                case "Tue":  $pretty_date_D = __('Tue', 'gxg_textdomain');  break;
                                                case "Wed":  $pretty_date_D = __('Wed', 'gxg_textdomain');  break;
                                                case "Thu":  $pretty_date_D = __('Thu', 'gxg_textdomain');  break;
                                                case "Fri":  $pretty_date_D = __('Fri', 'gxg_textdomain');  break;
                                                case "Sat":  $pretty_date_D = __('Sat', 'gxg_textdomain');  break;
                                                case "Sun":  $pretty_date_D = __('Sun', 'gxg_textdomain');  break;
                                                default:     $pretty_date_D = ""; break;
                                        }
                                        
                                        switch($pretty_date_M) /*make month translation ready */
                                        {
                                                case "Jan":  $pretty_date_M = __('Jan', 'gxg_textdomain');  break;
                                                case "Feb":  $pretty_date_M = __('Feb', 'gxg_textdomain');  break;
                                                case "Mar":  $pretty_date_M = __('Mar', 'gxg_textdomain');  break;
                                                case "Apr":  $pretty_date_M = __('Apr', 'gxg_textdomain');  break;
                                                case "May":  $pretty_date_M = __('May', 'gxg_textdomain');  break;
                                                case "Jun":  $pretty_date_M = __('Jun', 'gxg_textdomain');  break;
                                                case "Jul":  $pretty_date_M = __('Jul', 'gxg_textdomain');  break;                                                
                                                case "Aug":  $pretty_date_M = __('Aug', 'gxg_textdomain');  break;
                                                case "Sep":  $pretty_date_M = __('Sep', 'gxg_textdomain');  break;
                                                case "Oct":  $pretty_date_M = __('Oct', 'gxg_textdomain');  break;
                                                case "Nov":  $pretty_date_M = __('Nov', 'gxg_textdomain');  break;
                                                case "Dec":  $pretty_date_M = __('Dec', 'gxg_textdomain');  break;                                                
                                                default:     $pretty_date_M = ""; break;
                                        }
                                
                                ?>
                                
                                <table>
                                        <tr>        
                                                <td class="tour-item tour-date">
                                                        <div class="tour-weekday"><?php echo $pretty_date_D; ?> -  </div>
                                                        <div class="tour-month"><?php echo $pretty_date_M; ?></div>
                                                        <div class="tour-day"><?php echo $pretty_date_d; ?>, </div>   
                                                        <div class="tour-year"><?php echo $pretty_date_yy; ?></div>
                                                        <?php if($time){?>
                                                        <div class="tour-time"> <?php echo $time; ?></div>
                                                        <?php } ?>
                                                        
                                                        <?php if($more){?>
                                                        <div class="tour-more"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent link to', 'gxg_textdomain') ?> <?php the_title_attribute(); ?>"><?php _e('more info', 'gxg_textdomain') ?></a></div>
                                                        <?php } ?>                                                         
                                                        
                                                        
                                                </td>
                                                <td class="tour-item tour-city"> <?php echo $city; ?> </td>
                                                <td class="tour-item tour-venue"> <?php echo $venue; ?> </td>
                                                <td class="tour-item tour-url">
                           
                                                        <?php if ($cancelled){
                                                        ?>
                                                        <div class="cancelled"> <?php _e('Cancelled!', 'gxg_textdomain') ?>
                                                        </div>
                                                        
                                                        <?php
                                                        } elseif ($soldout) {                                                                                   
                                                        ?>
                                                        <div class="soldout"> <?php _e('Sold Out', 'gxg_textdomain') ?>
                                                        </div>                                                     
                                                        
                                                        <?php
                                                        } elseif ($url) {                                                                                   
                                                        ?>                                                        
                                                        
                                                        <a href="<?php echo $url; ?>" class="button2 tour-button"><?php if ($button_text) { echo $button_text; } else { _e('Buy Tickets', 'gxg_textdomain'); } ?></a>
                                                        
                                                        <?php
                                                        }                                                        
                                                        ?>
                                                           
                                                </td>
                                        </tr>
                                        
                                </table> 
                                
                        <?php
                        
                        endwhile;
                        
                        else:  ?>
                                             
                                <!-- what if there are no dates? -->
                                <div class="no_dates">
                                <p> <?php _e('There are no dates yet.', 'gxg_textdomain'); ?> </p>
                                </div>
                        <?php
                         
                        endif;
                        
                        // Always include a reset at the end of a loop to prevent conflicts with other possible loops                 
                        wp_reset_query();                        
                        
                        ?>
                        
                </div><!-- .tour upcoming-->  
      
       
        
        <?php if (!of_get_option('gg_pastdates')) { ?> 
                
                <h1 class="pagetitle past-dates"> <?php _e('Past Dates', 'gxg_textdomain'); ?> </h1>
                
                <div class="tour past">
     
                        <?php  
                        global $post;
                        
                        $order = of_get_option('gg_pastdatesorder');
                        
                        $args = array(
                                        'orderby' => 'meta_value',
                                        'meta_key' => 'gxg_date',                                        
                                        'meta_value' => strftime("%Y/%m/%d", time() ),
                                        'meta_compare' => '<',
                                        'order_by' => 'meta_value',                                        
                                        'order' => $order,
                                        'post_type' => 'tourdates',
                                        'posts_per_page' => -1 );

                        
                        $loop = new WP_Query( $args );
                                
                        if ($loop->have_posts()) : while ( $loop->have_posts() ) : $loop->the_post();

                                $today = date('U') - (60 * 60 * 24);
                                $date = get_post_meta($post->ID, 'gxg_date', true);
                                $time = get_post_meta($post->ID, 'gxg_time', true);
                                $timestamp = strtotime($date);
                                $pretty_date_yy = date('Y', $timestamp);
                                $pretty_date_M = date('M', $timestamp);
                                $pretty_date_d = date('d', $timestamp);
                                $pretty_date_D = date('D', $timestamp); 
                                $city = get_post_meta($post->ID, 'gxg_city', true);
                                $venue = get_post_meta($post->ID, 'gxg_venue', true);
                                $url= get_post_meta($post->ID, 'gxg_url', true);
                                $soldout= get_post_meta($post->ID, 'gxg_soldout', true);
                                $cancelled= get_post_meta($post->ID, 'gxg_cancelled', true);
                                $more = get_post_meta($post->ID, 'gxg_more', true);

                                        switch($pretty_date_D) /*make weekday translation ready */
                                        {
                                                case "Mon":  $pretty_date_D = __('Mon', 'gxg_textdomain');  break;
                                                case "Tue":  $pretty_date_D = __('Tue', 'gxg_textdomain');  break;
                                                case "Wed":  $pretty_date_D = __('Wed', 'gxg_textdomain');  break;
                                                case "Thu":  $pretty_date_D = __('Thu', 'gxg_textdomain');  break;
                                                case "Fri":  $pretty_date_D = __('Fri', 'gxg_textdomain');  break;
                                                case "Sat":  $pretty_date_D = __('Sat', 'gxg_textdomain');  break;
                                                case "Sun":  $pretty_date_D = __('Sun', 'gxg_textdomain');  break;
                                                default:     $pretty_date_D = ""; break;
                                        }
                                        
                                        switch($pretty_date_M) /*make month translation ready */
                                        {
                                                case "Jan":  $pretty_date_M = __('Jan', 'gxg_textdomain');  break;
                                                case "Feb":  $pretty_date_M = __('Feb', 'gxg_textdomain');  break;
                                                case "Mar":  $pretty_date_M = __('Mar', 'gxg_textdomain');  break;
                                                case "Apr":  $pretty_date_M = __('Apr', 'gxg_textdomain');  break;
                                                case "May":  $pretty_date_M = __('May', 'gxg_textdomain');  break;
                                                case "Jun":  $pretty_date_M = __('Jun', 'gxg_textdomain');  break;
                                                case "Jul":  $pretty_date_M = __('Jul', 'gxg_textdomain');  break;                                                
                                                case "Aug":  $pretty_date_M = __('Aug', 'gxg_textdomain');  break;
                                                case "Sep":  $pretty_date_M = __('Sep', 'gxg_textdomain');  break;
                                                case "Oct":  $pretty_date_M = __('Oct', 'gxg_textdomain');  break;
                                                case "Nov":  $pretty_date_M = __('Nov', 'gxg_textdomain');  break;
                                                case "Dec":  $pretty_date_M = __('Dec', 'gxg_textdomain');  break;                                                
                                                default:     $pretty_date_M = ""; break;
                                        }
                                        
                                
                                
                                
                                $year = date('Y', $timestamp);
                                
                                if (!isset($year_check) || isset($year_check) && $year !== $year_check) { ?>
                                
                                <h5 class="pastdates-year"><?php echo $year; ?></h5>
                                
                                <?php }
                                $year_check = $year;
                                ?>
                                
                                <table>
                                        <tr>        
                                                <td class="tour-item tour-date">
                                                        <div class="tour-weekday"><?php echo $pretty_date_D; ?> -  </div>
                                                        <div class="tour-month"><?php echo $pretty_date_M; ?></div>
                                                        <div class="tour-day"><?php echo $pretty_date_d; ?>, </div>   
                                                        <div class="tour-year"><?php echo $pretty_date_yy; ?></div>
                                                        <?php if($time){?>
                                                        <div class="tour-time"> <?php echo $time; ?></div>
                                                        <?php } ?>
                                                        
                                                        <?php if($more){?>
                                                        <div class="tour-more"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent link to', 'gxg_textdomain') ?> <?php the_title_attribute(); ?>"><?php _e('more info', 'gxg_textdomain') ?></a></div>
                                                        <?php } ?>                                                         
                                                        
                                                </td>
                                                <td class="tour-item tour-city"> <?php echo $city; ?> </td>
                                                <td class="tour-item tour-venue"> <?php echo $venue; ?> </td>
                                                <td class="tour-item tour-url">
                           
                                                        <div class="img-link"> 
                                                        </div>                                         
                                                           
                                                </td>
                                        </tr>
                                        
                                </table> 
 
                        <?php
                        
                        endwhile;                       
                      
                        
                        else:  ?>
                        
                                <!-- what if there are no dates? -->
                                <div class="no_dates">
                                <p> <?php _e('There are no dates yet.', 'gxg_textdomain'); ?> </p>
                                </div>
                
                        <?php
                         
                        endif;
                        
                        // Always include a reset at the end of a loop to prevent conflicts with other possible loops                 
                        wp_reset_query();                        
                        
                        ?>
                        
                </div><!-- .tour past-->  
     
        <?php } ?>
        
        </div><!-- #content-->
        
     

        
        <div class="clear">
        </div><!-- .clear-->

<?php get_footer(); ?>