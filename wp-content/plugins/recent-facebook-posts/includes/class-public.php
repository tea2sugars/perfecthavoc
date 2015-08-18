<?php

class RFBP_Public {

	private static $instance = null;

	public static function instance() 
	{
		if(!self::$instance) {
			self::$instance = new RFBP_Public();
		}

		return self::$instance;
	}

	public function __construct() {

		$opts = rfbp_get_settings();

		include_once RFBP_PLUGIN_DIR . 'includes/helper-functions.php';
		include_once RFBP_PLUGIN_DIR . 'includes/template-functions.php';

		add_shortcode( 'recent_facebook_posts', array( $this, 'output' ) );
		add_shortcode( 'recent-facebook-posts', array( $this, 'output' ) );

		if ( $opts['load_css'] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_css' ) );
		}

		add_filter( 'rfbp_content', 'wptexturize' ) ;
		add_filter( 'rfbp_content', 'convert_smilies' );
		add_filter( 'rfbp_content', 'convert_chars' );
		add_filter( 'rfbp_content', 'wpautop' );
	}

	public function load_css() {
		wp_register_style( 'recent-facebook-posts-css', plugins_url( 'recent-facebook-posts/assets/css/default.css' ), array(), RFBP_VERSION );
		wp_enqueue_style( 'recent-facebook-posts-css' );
	}

	public function get_fallback_posts() {
		$posts = get_transient( 'rfbp_posts_fallback' );

		if ( !$posts ) {
			return array();
		}

		return $posts;
	}

	public function get_posts() {

		// try to get posts from cache
		if ( ( $posts = get_transient( 'rfbp_posts' ) ) ) {
			return $posts;
		}

		$opts = rfbp_get_settings();

		$api = rfbp_get_api();
		$data = $api->get_posts();

		// did api call succeed?
		if ( !$data ) {
			return $this->get_fallback_posts();
		}

		$posts = array();
		foreach ( $data as $p ) {

			// skip this "post" if it is not of one of the following types
			if ( !in_array( $p->type, array( 'status', 'photo', 'video', 'link' ) ) ) {
				continue;
			}

			// skip empty status updates
			if ($p->type == 'status' && (!isset($p->message) || empty($p->message) ) ) {
				continue;
			} 

			// skip empty links.
			if ($p->type == 'link' && !isset($p->name) && (!isset($p->message) || empty($p->message))) {
				continue;
			}

			// skip friend approvals
			if ( $p->type == 'status' && $p->status_type == 'approved_friend' ) { continue; }

			//split user and post ID (userID_postID)
			$idArray = explode( "_", $p->id );

			$post = array();
			$post['type'] = $p->type;
			$post['content'] = isset( $p->message ) ? utf8_encode( $p->message ) : '';
			$post['image'] = null;

			// set post content and image
			if ( $p->type == 'photo' ) {

				$image = "//graph.facebook.com/". $p->object_id . '/picture';
				$post['image'] = $image;

			} elseif ( $p->type == 'video' ) {

				$image = $p->picture;

				// hacky
				if ( $opts['img_size'] == 'normal' ) {
					$image = str_replace( array( "_s.jpg", "_s.png" ), array( "_n.jpg", "_n.png" ), $image );
				}

				$post['image'] = $image;

			} elseif ( $p->type == 'link' ) {
				$post['link_image'] = ( isset( $p->picture ) ) ? $p->picture : '';
				$post['link_name'] = ( isset( $p->name ) ) ? $p->name : '';
				$post['link_caption'] = ( isset( $p->caption ) ) ? $p->caption : '';
				$post['link_description'] = ( isset( $p->description ) ) ? $p->description : '';
				$post['link_url'] = $p->link;
			}

			// calculate post like and comment counts
			if ( isset( $p->likes->summary->total_count ) ) {
				$like_count = $p->likes->summary->total_count;
			} else {
				$like_count = 0;
			}

			if ( isset( $p->comments->summary->total_count ) ) {
				$comment_count = $p->comments->summary->total_count;
			} else {
				$comment_count = 0;
			}

			$post['timestamp'] = strtotime( $p->created_time );
			$post['like_count'] = $like_count;
			$post['comment_count'] = $comment_count;
			$post['url'] = "http://www.facebook.com/".$opts['fb_id']."/posts/".$idArray[1];
			$posts[] = $post;

		}

		// store results in cache for later use
		if ( $posts ) {
			set_transient( 'rfbp_posts', $posts, apply_filters( 'rfbp_cache_time', 3600 ) ); // user set cache time
			set_transient( 'rfbp_posts_fallback', $posts, 2629744 ); // 1 month
		}

		return $posts;
	}

	public function output( $atts = array() ) {

		$opts = rfbp_get_settings();
		$posts = $this->get_posts();

		if ( isset( $atts['show_link'] ) ) {
			$atts['show_page_link'] = $atts['show_link'];
		}

		extract( shortcode_atts( array(
					'number' => '5',
					'likes' => 1,
					'comments' => 1,
					'excerpt_length' => 140,
					'el' => 'div',
					'origin' => 'shortcode',
					'show_page_link' => false,
					'show_link_previews' => ( $opts['load_css'] )
				), $atts ) );

		ob_start();
?>
		<!-- Recent Facebook Posts v<?php echo RFBP_VERSION; ?> - http://wordpress.org/plugins/recent-facebook-posts/ -->
		<div class="recent-facebook-posts rfbp rfbp-container rfbp-<?php echo $origin; ?>">
			<?php

		if ( $posts && !empty( $posts ) ) {

			if ( $el == 'li' ) { echo '<ul class="rfbp-posts-wrap">'; }

			$posts = array_slice( $posts, 0, $number );
			$link_target = ( $opts['link_new_window'] ) ? "_blank" : '';

			foreach ( $posts as $p ) {

				$content = utf8_decode($p['content']);

				$shortened = false;

				if ( strlen( $content ) > $excerpt_length ) {
					$limit = strpos( $content, ' ', $excerpt_length );
					if ( $limit ) {
						$content = substr( $content, 0, $limit );
						$shortened = true;
					}
				}
?>

					<<?php echo $el; ?> class="rfbp-post">
					<div class="rfbp-text">

						<?php
				$content = make_clickable( $content, $link_target );
				$content = ( $shortened ) ? $content . apply_filters( 'rfbp_read_more', '..', $p['url'] ) : $content;
				$content = apply_filters( 'rfbp_content', $content, $p['url'] );

				echo $content; ?>

					</div><i class="icon-caret-right"></i>

					<?php if ( $show_link_previews && isset( $p['link_url'] ) && !empty( $p['link_url'] ) && !empty( $p['link_name'] ) ) { ?>

					<p class="rfbp-link-wrap">
						<a class="rfbp-link" href="<?php echo $p['link_url']; ?>" rel="external nofollow" target="<?php echo $link_target; ?>">
							<?php if ( !empty( $p['link_image'] ) && ( apply_filters( 'rfbp_show_link_images', true ) !== false ) ) { ?>
							<span class="rfbp-link-image-wrap">
								<img class="rfbp-link-image" src="<?php echo esc_attr( $p['link_image'] ); ?>" width="114" />
							</span>
							<?php } ?>

							<span class="rfbp-link-text-wrap">
								<span class="rfbp-link-name"><?php echo $p['link_name']; ?></span>
								<?php if ( isset( $p['link_caption'] ) ) { ?><span class="rfbp-link-caption"><?php echo $p['link_caption']; ?></span><?php } ?>
								<?php if ( isset( $p['link_description'] ) && !empty( $p['link_description'] ) ) { ?><span class="rfbp-link-description"><?php echo $p['link_description']; ?></span><?php } ?>
							</span>
						</a>
					</p>

					<?php } ?>

					<?php if ( $opts['img_size'] != 'dont_show' && isset( $p['image'] ) && !empty( $p['image'] ) ) { ?>
					<p class="rfbp-image-wrap">
						<a class="rfbp-image-link" target="<?php echo $link_target; ?>" href="<?php echo $p['url']; ?>" rel="external nofollow">
							<?php $max_img_width = ( !empty( $opts['img_width'] ) ) ? $opts['img_width'].'px' : '100%'; $max_img_height = ( !empty( $opts['img_height'] ) ) ? $opts['img_height'].'px' : 'none'; ?>
							<img class="rfbp-image" src="<?php echo esc_attr($p['image'] . '?type=' . $opts['img_size']); ?>" style="max-width: <?php echo $max_img_width; ?>; max-height: <?php echo $max_img_height; ?>" alt="" />
						</a>
					</p>
					<?php } ?>
					<p class="rfbp-post-link-wrap">
						<a target="<?php echo $link_target; ?>" class="rfbp-post-link" href="<?php echo $p['url']; ?>" rel="external nofolloW">
							<?php if ( $likes ) { ?><span class="rfbp-like-count"><?php echo $p['like_count']; ?> <span><?php _e( "likes", 'recent-facebook-posts' ); ?><?php if ( $comments ) { ?>, <?php } ?></span></span><?php } ?>
							<?php if ( $comments ) { ?><span class="rfbp-comment-count"><?php echo $p['comment_count']; ?> <span><?php _e( "comments", 'recent-facebook-posts' ); ?></span></span><?php } ?>
							<?php if ( $likes || $comments ) { ?> &sdot; <?php } ?>
							<span class="rfbp-timestamp" title="<?php printf( __( '%1$s at %2$s', 'recent-facebook-posts' ), date( 'l, F j, Y', $p['timestamp'] ), date( 'G:i', $p['timestamp'] ) ); ?>"><?php echo rfbp_time_ago( $p['timestamp'] ); ?></span>
						</a>
					</p>
					</<?php echo $el; ?>>
				<?php

			} // end foreach $posts

			if ( $el == 'li' ) { echo '</ul>'; }

		} else {
			?><p><?php _e( "No recent Facebook posts to show", 'recent-facebook-posts' ); ?></p><?php
			if ( current_user_can( 'manage_options' ) ) {
				?><p><strong><?php _e( "Admins only notice", 'recent-facebook-posts' ); ?>:</strong> Did you <a href="<?php echo admin_url( 'options-general.php?page=rfbp' ); ?>">configure the plugin</a> properly?</p><?php
			}
		} ?>

			<?php if ( $show_page_link ) { ?>
				<p class="rfbp-page-link-wrap"><a class="rfbp-page-link" href="http://www.facebook.com/<?php echo $opts['fb_id']; ?>/" rel="external nofollow" target="<?php echo $link_target; ?>"><?php echo $opts['page_link_text']; ?></a></p>
			<?php } ?>

			</div>
			<!-- / Recent Facebook Posts -->
			<?php
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

}
