<?php
/*
Plugin Name: Goodpep Recent Posts Widget
Plugin URI: http://goodpep.com
Description: Recent Posts
Author: Spencer Goodrich
Version: 1.5
Author URI: http://spencergoodrich.com
*/
class RecentPostss extends WP_Widget
{
  function RecentPostss()
  {
    $widget_ops = array('classname' => 'RecentPostss', 'description' => 'Displays Recent Posts' );
    $this->WP_Widget('RecentPostss', 'Recent Posts by Goodpep', $widget_ops);
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
      echo $before_title . $title . $after_title;;
  query_posts('posts_per_page=1');
if (have_posts()) : 
	echo "";
	while (have_posts()) : the_post(); 
    echo '<div class="album_item mosaic-block bar">';                       
    echo '<a href="';
    echo the_permalink();
    echo '">';
    
// Must be inside a loop.

if ( has_post_thumbnail() ) {
	the_post_thumbnail();
}
else {
   
// get an image field (return type = object)
$image = get_field('1_feat_img');
 
// each image has a custom field (link)
$link = get_field('link', $image['id']);
	echo '<img src="' .  $image['url'] . '"/>';
}

    
    echo '</a>';
    echo '<div class="details mosaic-overlay aud-size" style="display: block; left: 0px; bottom: -50px;">';
    echo get_the_title();
    echo '</div>';
echo '</div>';
		echo "";	
	endwhile;
	echo "";
endif; 
wp_reset_query();
    echo $after_widget;
  }
}
add_action( 'widgets_init', create_function('', 'return register_widget("RecentPostss");') );?>