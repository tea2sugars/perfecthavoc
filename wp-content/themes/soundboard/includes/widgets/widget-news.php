<?php
/*
Plugin Name: Blog Widget
Plugin URI: http://www.red-sun-design.com
Description: Display your latest News Entries
Version: 1.0
Author: Gerda Gimpl
Author URI: http://www.red-sun-design.com
*/

class gg_News_Widget extends WP_Widget {

	/*--------------------------------------------------*/
	/* CONSTRUCT THE WIDGET
	/*--------------------------------------------------*/

	function gg_News_Widget() {
  
	/* Widget name and description */
	$widget_opts = array (
		'classname' => 'gg_news_widget', 
		'description' => __('Display your latest News Entries.', 'gxg_textdomain')
	);	

	$this->WP_Widget('gg-news-widget', __('SOUNDBOARD - News', 'gxg_textdomain'), $widget_opts);
		
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
		?>
		
        	<?php 
		$query = new WP_Query();
		$query->query('posts_per_page='. $number);
		while ($query->have_posts()) : $query->the_post(); 
		?>
				
			<div <?php post_class(); ?> id="news-widget-<?php the_ID(); ?>">
                                
                                <div class="post-info">                                        
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
                                
				<div>
					<h1 class="news-widget-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
				</div>                            
                                
			</div><!-- .post-? --> 
            
		
			
		<?php endwhile; wp_reset_query(); ?>
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
				'title' => 'News',
				'number' => 3
			)
		);
		
	
	// Display the admin form
	?>
        <p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'gxg_textdomain') ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
	</p>
		
	<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Posts Number:', 'gxg_textdomain') ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" />
	</p>
	<?php		
		
	} // end form

	
} // end class
add_action('widgets_init', create_function('', 'register_widget("gg_News_Widget");'));
?>