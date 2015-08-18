<?php
/*
Plugin Name: Tourdates Widget
Plugin URI: http://www.red-sun-design.com
Description: Display Tourdates
Version: 1.0
Author: Gerda Gimpl
Author URI: http://www.red-sun-design.com
*/

class gg_Tourdates_Widget extends WP_Widget {

	/*--------------------------------------------------*/
	/* CONSTRUCT THE WIDGET
	/*--------------------------------------------------*/

	function gg_Tourdates_Widget() {
  
	/* Widget name and description */
	$widget_opts = array (
		'classname' => 'gg_tourdates_widget', 
		'description' => __('Display upcoming  tour dates', 'gxg_textdomain')
	);	

	$this->WP_Widget('gg-tourdates-widget', __('SOUNDBOARD - Tour Dates', 'gxg_textdomain'), $widget_opts);
		
	}


	/*--------------------------------------------------*/
	/* DISPLAY THE WIDGET
	/*--------------------------------------------------*/	
	/* outputs the content of the widget
	 * @args --> The array of form elements*/	
	function widget($args, $instance) {	
		extract($args, EXTR_SKIP);
		
		$title = apply_filters('widget_title', $instance['title'] );
		$number = $instance['number'];

		/* before widget */
		echo $before_widget;

		/* display title */
		if ( $title )
		echo $before_title . $title . $after_title;
   
		/* display the widget */
                ?> <ul> <?php
                
                global $post;
                
                $args = array(
                                'orderby' => 'meta_value',
                                'meta_key' => 'gxg_date',                                        
                                'meta_value' => strftime("%Y/%m/%d", time()- (60 * 60 * 24) ),
                                'meta_compare' => '>',
                                'order_by' => 'meta_value',                                        
                                'order' => 'ASC',
                                'post_type' => 'tourdates',
                                'posts_per_page' => $number );

                
                $loop = new WP_Query( $args );
                        
                if ($loop->have_posts()) : while ( $loop->have_posts() ) : $loop->the_post();
                        
                        $today = date('U') - (60 * 60 * 24);
                        $date = get_post_meta($post->ID, 'gxg_date', true);
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
                        $cancelled= get_post_meta($post->ID, 'gxg_cancelled', true);                                

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
                <li class="tourwidget-item">                                
                        <div class="tour-date-w">                                        
                                <div class="tour-day-w"><?php echo $pretty_date_d; ?></div>
                                <div class="tour-month-w"><?php echo $pretty_date_M; ?></div>                               
                        </div>                                        

                        
                        <div class="tourright">
                                <div class="city"> <?php echo $city; ?> </div>
                                <div class="venue"> <?php echo $venue; ?> </div>
                                <div class="tour-url-w">  
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
                                </div><!-- .buyticket -->        
                        </div><!-- .tourright -->
                        <div class="clear">  </div>

                </li>
        
                <?php
                
                endwhile; else: 
              
                ?>
                        <!-- what if there are no dates? -->
                        <div class="no-dates">
                        <p> <?php _e('There are no dates yet.', 'gxg_textdomain'); ?> </p>
                        </div>
                <?php
                 
                endif;
                
                
                        
                // Always include a reset at the end of a loop to prevent conflicts with other possible loops                 
                wp_reset_query();
                ?>
                
                </ul>
                        
		<?php
		
		/* after widget */
		echo $after_widget;		
	}
	
	
	/*--------------------------------------------------*/
	/* UPDATE THE WIDGET
	/*--------------------------------------------------*/
	function update($new_instance, $old_instance) {		
		$instance = $old_instance;
 	
    	$instance['title'] = strip_tags( $new_instance['title'] );
    	$instance['number'] = strip_tags( $new_instance['number']);
    	
	return $instance;		
	} 
	
	
	/*--------------------------------------------------*/
	/* WIDGET ADMIN FORM
	/*--------------------------------------------------*/
	/* @instance	The array of keys and values for the widget. */
	function form($instance) {
	
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title' => 'Tour',
				'number' => '3'
			)
		);
		
	
	// Display the admin form
	?>
        <p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'gxg_textdomain') ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
	</p>
		
	<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Number of Dates:', 'gxg_textdomain') ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" />
	</p>
	<?php		
		
	} // end form

	
} // end class
add_action('widgets_init', create_function('', 'register_widget("gg_Tourdates_Widget");'));
?>