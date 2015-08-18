<?php
/*
Plugin Name: Album Widget
Plugin URI: http://www.red-sun-design.com
Description: Display your latest Album
Version: 1.0
Author: Gerda Gimpl
Author URI: http://www.red-sun-design.com
*/

class gg_Album_Widget extends WP_Widget {

	/*--------------------------------------------------*/
	/* CONSTRUCT THE WIDGET
	/*--------------------------------------------------*/

	function gg_Album_Widget() {
  
	/* Widget name and description */
	$widget_opts = array (
		'classname' => 'gg_album_widget', 
		'description' => __('Display your latest Album', 'gxg_textdomain')
	);	

	$this->WP_Widget('gg-album-widget', __('SOUNDBOARD - Album', 'gxg_textdomain'), $widget_opts);
		
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
				
			<div <?php post_class(); ?> id="album-widget-<?php the_ID(); ?>">
                        
                        <div class="album-widget">
                        
                        <ul>
                        <?php  
                                global $post;
                                        
                                $args = array(
                                                        'order' => 'DESC',
                                                        'post_type' => 'audio',
                                                        'posts_per_page' => 1 );
                                        
                                $loop = new WP_Query( $args );
                                        
                                while ( $loop->have_posts() ) : $loop->the_post();
                                
                                $album_title = $post->post_title;
                                $album_thumb = get_the_post_thumbnail($post->ID, 'square2');
                                $amazon = get_post_meta($post->ID, 'gxg_amazon', true);
                                $itunes = get_post_meta($post->ID, 'gxg_itunes', true);
                                $buy_other = get_post_meta($post->ID, 'gxg_buy_other', true);
                                $buy_other_text = get_post_meta($post->ID, 'gxg_buy_other_text', true);  

                                ?>
                                <li>
                                        <div class="album_item mosaic-block-a bar">
                                                <a href="<?php the_permalink() ?>">                                                        
                                                        <div class="details mosaic-overlay aw-size">
                                                                <?php echo $album_title; ?>
                                                        </div>
                                                      
                                                        <div class="mosaic-backdrop">                                                                
                                                                        <?php echo $album_thumb; ?>                                                                    
                                                        </div>
                                                </a>          
                                        </div><!-- .album_item-->
                                        
                                        <?php
                                        if ($amazon){ ?>                  	                              
                                                <a href="<?php echo $amazon; ?>" class="button2 buy-amazon"><?php _e('AMAZON', 'gxg_textdomain') ?></a>     
                                        <?php }
                
                                        if ($itunes){ ?>
                                                <a href="<?php echo $itunes; ?>" class="button2 buy-itunes"><?php _e('iTUNES', 'gxg_textdomain') ?></a>       
                                        <?php }
                                        
                                        if ($buy_other){ ?>
                                                <a href="<?php echo $buy_other; ?>" class="button2 buy-other"><?php echo $buy_other_text; ?></a>         
                                        <?php }                                          
                                        ?> 
                                        
                                </li>                       
            	
                                <?php endwhile; wp_reset_query(); ?>
                
                        </ul>
                        </div><!-- .album-widget --> 
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
				'title' => 'New Album'
			)
		);
		
	
	// Display the admin form
	?>
        <p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'gxg_textdomain') ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
	</p>

	<?php		
		
	} // end form

	
} // end class
add_action('widgets_init', create_function('', 'register_widget("gg_Album_Widget");'));
?>