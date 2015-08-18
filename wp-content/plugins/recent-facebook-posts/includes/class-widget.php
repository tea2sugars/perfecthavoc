<?php

class RFBP_Widget extends WP_Widget {

	private $defaults;

	public function __construct() {
		parent::__construct(
			'rfb_widget',
			'Recent Facebook Posts',
			array( 'description' => __( 'Lists a number of your most recent Facebook posts.', 'recent-facebook-posts' ) )
		);

		$this->defaults = array(
			'title' => __( 'Recent Facebook Posts', 'recent-facebook-posts' ),
			'number_of_posts' => 5,
			'excerpt_length' => 140,
			'show_comment_count' => true,
			'show_like_count' => true,
			'show_page_link' => true,
			'show_link_previews' => false
		);
	}

	public function form( $instance ) {

		$instance = array_merge( $this->defaults, $instance );
		extract( $instance );

		$opts = rfbp_get_settings();

		if ( empty( $opts['app_id'] ) ) { ?>
 			<p style="color:red;">You need to <a href="<?php echo admin_url( 'options-general.php?page=rfbp' ); ?>">configure Recent Facebook Posts</a> first.</p>
 		<?php } ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'recent-facebook-posts' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e( 'Number of posts:', 'recent-facebook-posts' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>" type="number" min="1" max="99" value="<?php echo esc_attr( $number_of_posts ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>"><?php _e( 'Excerpt length:', 'recent-facebook-posts' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="number" min="1" max="9999"value="<?php echo esc_attr( $excerpt_length ); ?>" />
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_like_count' ); ?>" name="<?php echo $this->get_field_name( 'show_like_count' ); ?>" value="1" <?php checked( $show_like_count, 1 ); ?>  />
			<label for="<?php echo $this->get_field_id( 'show_like_count' ); ?>"><?php _e( 'Show like count?', 'recent-facebook-posts' ); ?></label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_comment_count' ); ?>" name="<?php echo $this->get_field_name( 'show_comment_count' ); ?>" value="1" <?php checked( $show_comment_count, 1 ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_comment_count' ); ?>"><?php _e( 'Show comment count?', 'recent-facebook-posts' ); ?></label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_page_link' ); ?>" name="<?php echo $this->get_field_name( 'show_page_link' ); ?>" value="1" <?php if ( $show_page_link ) { ?>checked="1"<?php } ?> />
			<label for="<?php echo $this->get_field_id( 'show_page_link' ); ?>"><?php _e( 'Show link to Facebook page?', 'recent-facebook-posts' ); ?></label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_link_previews' ); ?>" name="<?php echo $this->get_field_name( 'show_link_previews' ); ?>" value="1" <?php if ( $show_link_previews ) { ?>checked="1"<?php } ?> />
			<label for="<?php echo $this->get_field_id( 'show_link_previews' ); ?>"><?php _e( 'Show link previews?', 'recent-facebook-posts' ); ?></label>
		</p>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number_of_posts'] = (int) strip_tags( $new_instance['number_of_posts'] );
		$instance['excerpt_length'] = (int) strip_tags( $new_instance['excerpt_length'] );
		$instance['show_like_count'] = isset( $new_instance['show_like_count'] );
		$instance['show_comment_count'] = isset( $new_instance['show_comment_count'] );
		$instance['show_page_link'] = isset( $new_instance['show_page_link'] );
		$instance['show_link_previews'] = isset( $new_instance['show_link_previews'] );
		return $instance;
	}

	public function widget( $args, $instance = array() ) {

		$instance = array_merge( $this->defaults, $instance );

		extract( $instance );
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ( ! empty( $title ) ) { 
			echo $before_title . $title . $after_title; 
		}

		recent_facebook_posts( array(
				'origin' => 'widget',
				'number' => $number_of_posts,
				'likes' => $show_like_count,
				'comments' => $show_comment_count,
				'excerpt_length' => $excerpt_length,
				'el' => apply_filters( 'rfbp_widget_element', 'div' ),
				'show_page_link' => $show_page_link,
				'show_link_previews' => $show_link_previews
			) );


		echo $after_widget;
	}



}
