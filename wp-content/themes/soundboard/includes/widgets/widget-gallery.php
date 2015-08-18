<?php
/*
Plugin Name: Gallery Widget
Plugin URI: http://www.red-sun-design.com
Description: Display images from your Gallery
Version: 1.0
Author: Gerda Gimpl
Author URI: http://www.red-sun-design.com
*/

class gg_Gallery_Widget extends WP_Widget {

	/*--------------------------------------------------*/
	/* CONSTRUCT THE WIDGET
	/*--------------------------------------------------*/

	function gg_Gallery_Widget() {
  
	/* Widget name and description */
	$widget_opts = array (
		'classname' => 'gg_gallery_widget', 
		'description' => __('Display images from your Gallery', 'gxg_textdomain')
	);	

	$this->WP_Widget('gg-galery-widget', __('SOUNDBOARD - Gallery', 'gxg_textdomain'), $widget_opts);
		
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
				
			<div <?php post_class(); ?> id="gallery-widget-<?php the_ID(); ?>">
                        
                        <div class="gallery-widget">
                        
                        <ul>
                        <?php  
                                global $post;
                                        
                                $args = array(
                                                        'order' => 'DESC',
                                                        'post_type' => 'gallery',
                                                        'posts_per_page' => $number );
                                        
                                $loop = new WP_Query( $args );
                                        
                                while ( $loop->have_posts() ) : $loop->the_post();
                                
                                $gallery_title = $post->post_title;
                                $gallery_thumb = get_the_post_thumbnail($post->ID, 'square3');

                                ?>
                                <li>                                        
                                        <div class="gallery_item mosaic-block-gw bar">
                                                <a href="<?php the_permalink() ?>">                                        
                                                        <div class="details mosaic-overlay gw-size">
                                                                <?php echo $gallery_title; ?>
                                                        </div>
                                                
                                                        <div class="mosaic-backdrop">                                                
                                                                <?php echo $gallery_thumb; ?>     
                                                        </div>
                                                </a>
                                        </div><!-- .gallery_item-->                                           
                                </li>                       
            	
                                <?php endwhile; wp_reset_query(); ?>
                
                        </ul>
                        </div><!-- .gallery-widget --> 
                        </div><!-- .post-? --> 
               
		
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
				'title' => 'Latest Images',
				'number' => 4
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
add_action('widgets_init', create_function('', 'register_widget("gg_Gallery_Widget");'));
?>