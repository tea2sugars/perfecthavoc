<?php

// REGISTER VIDEO POST TYPE

add_action('init', 'posttype_video');

function posttype_video() {
        $labels = array(
                'name' => __('Videos', 'gxg_textdomain'),
                'singular_name' => __('Video', 'gxg_textdomain'),
                'add_new' => __('Add Video', 'gxg_textdomain'),
                'add_new_item' => __('Add New Video', 'gxg_textdomain'),
                'edit_item' => __('Edit Video', 'gxg_textdomain'),
                'new_item' => __('New Video', 'gxg_textdomain'),
                'view_item' => __('View Details', 'gxg_textdomain'),
                'search_items' => __('Search Video', 'gxg_textdomain'),
                'not_found' =>  __('No Video was found with that criteria', 'gxg_textdomain'),
                'not_found_in_trash' => __('No Video found in the Trash with that criteria', 'gxg_textdomain'),
                'view' =>  __('View Video', 'gxg_textdomain')
        );

        $imagepath =  get_template_directory_uri() . '/images/posttypeimg/';
        
        global $wp_version;
	if( version_compare( $wp_version, '3.8', '>=') ) {
	    	$img =  'vid.png';
	} else {
		$img =  'vid_.png';
	}

        $args = array(
                'labels' => $labels,
                'description' => 'This is the holding location for all Videos',
                'public' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => false,
                'show_ui' => true,
                'rewrite' => true,
                'hierarchical' => true,
                'menu_position' => 5,
                'menu_icon' => $imagepath . $img,
                'supports' => array('thumbnail', 'title')
        );

register_post_type('video',$args);
}

?>