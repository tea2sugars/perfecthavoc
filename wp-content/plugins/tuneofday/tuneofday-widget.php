<?php
/*
Plugin Name: Goodpep Tune of the Day Widget
Plugin URI: http://goodpep.com
Description: Tune of the Day Plugin
Author: Spencer Goodrich
Version: 1
Author URI: http://spencergoodrich.com
*/


class TuneofDay extends WP_Widget
{
  function TuneofDay()
  {
    $widget_ops = array('classname' => 'TuneofDay', 'description' => 'Displays a Tune of the Day' );
    $this->WP_Widget('TuneofDay', 'Tune of the Day (Goodpep)', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;
 $loop = new WP_Query( array( 'post_type' => 'tuneoftheday', 'posts_per_page' => 1 ) );
 
if (have_posts()) : 
	echo "";
while ( $loop->have_posts() ) : $loop->the_post(); 
     $soundcloudb = get_post_meta(get_the_ID( ), 'gxg_soundcloud', true);
    echo '<div class="marquee">
    <ul>
        <h3>';
		echo "".get_the_title();
  	echo  '</h3>';

    echo '</ul>
</div>';
		echo $soundcloudb;
		echo "";	
			
	endwhile;
	echo "";
endif; 
wp_reset_query();
 
    echo $after_widget;
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("TuneofDay");') );?>