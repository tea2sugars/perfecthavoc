<?php
/*
Plugin Name: Images Widget
Plugin URI: http://www.red-sun-design.com
Description: Display your latest images
Version: 1.0
Author: Gerda Gimpl
Author URI: http://www.red-sun-design.com
*/

class gg_Images_Widget extends WP_Widget {

	/*--------------------------------------------------*/
	/* CONSTRUCT THE WIDGET
	/*--------------------------------------------------*/

	function gg_Images_Widget() {
  
	/* Widget name and description */
	$widget_opts = array (
		'classname' => 'gg_images_widget', 
		'description' => __('Display your latest images', 'gxg_textdomain')
	);	

	$this->WP_Widget('gg-images-widget', __('SOUNDBOARD - Images', 'gxg_textdomain'), $widget_opts);
		
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
                echo $before_title . $title . $after_title;
   
		/* display the widget */
		?>
						
		<div <?php post_class(); ?> id="images-widget-<?php the_ID(); ?>">
                        
                        <div class="gallery-widget">
                        
                        <ul>
                        <?php  
                                global $post;
                                        
                                $args = array(
                                                        'order' => 'DESC',
                                                        'post_type' => 'gallery',
                                                        'posts_per_page' => 1 );
                                        
                                $loop = new WP_Query( $args );
                                        
                                while ( $loop->have_posts() ) : $loop->the_post();

                                ?>
  
                                        
                                                       
                                                        <?php       
                                                        global $wpdb, $post;
                                
                                                        $meta = get_post_meta( get_the_ID(  ), 'gxg_gallery_images', false );
                                                        
                                                        if ( !is_array( $meta ) )
                                                            $meta = ( array ) $meta;
                                                        
                                                        if ( !empty( $meta ) ) {
                                                            $meta = implode( ',', $meta );
                                                            
                                                            $images = $wpdb->get_col( "
                                                                SELECT ID FROM $wpdb->posts
                                                                WHERE post_type = 'attachment'
                                                                AND ID IN ( $meta )
                                                                ORDER BY menu_order ASC
                                                                LIMIT $number
                                                            " );
                                                            
                                                            foreach ( $images as $att ) {
                                                                // Get image's source based on size, can be 'thumbnail', 'medium', 'large', 'full' or registed post thumbnails sizes
                                                                $src = wp_get_attachment_image_src( $att, 'square3' );
                                                                $src = $src[0];
                                                                
                                                                $src_full = wp_get_attachment_image($att, 'full');
                                                                preg_match ('/src="(.*)" class/',$src_full,$link);
                                                                
                                                 
                                                        
                                                                // Show image
                                                                echo "<li class='gallery_item prettyimage-wrap'><a class='pretty_image' title='' data-rel='prettyPhoto[pp_gallery]' href='$link[1]'><img src='{$src}' alt='' /></a></li>";
                                                            }
                                                        }        
                                                        ?>                               
            	
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
add_action('widgets_init', create_function('', 'register_widget("gg_Images_Widget");'));
?>