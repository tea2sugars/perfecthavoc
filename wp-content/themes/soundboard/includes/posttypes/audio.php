<?php

// REGISTER AUDIO POST TYPE

add_action('init', 'posttype_audio');

function posttype_audio() {
        $labels = array(
                'name' => __('Audio', 'gxg_textdomain'),
                'singular_name' => __('Album', 'gxg_textdomain'),
                'add_new' => __('Add Album', 'gxg_textdomain'),
                'add_new_item' => __('Add New Album','gxg_textdomain'),
                'edit_item' => __('Edit Album','gxg_textdomain'),
                'new_item' => __('New Album','gxg_textdomain'),
                'view_item' => __('View Details','gxg_textdomain'),
                'search_items' => __('Search Audio','gxg_textdomain'),
                'not_found' =>  __('No Audio was found with that criteria','gxg_textdomain'),
                'not_found_in_trash' => __('No Audio found in the Trash with that criteria','gxg_textdomain'),
                'view' =>  __('View Album', 'gxg_textdomain')
        );

        $imagepath =  get_template_directory_uri() . '/images/posttypeimg/';

        global $wp_version;
	if( version_compare( $wp_version, '3.8', '>=') ) {
	    	$img =  'aud.png';
	} else {
		$img =  'aud_.png';
	}

        $args = array(
                'labels' => $labels,
                'description' => 'This is the holding location for all Albums',
                'public' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => false,
                'show_ui' => true,
                'rewrite' => true,
                'hierarchical' => true,
                'menu_position' => 5,
                'menu_icon' => $imagepath . $img,
                'supports' => array('thumbnail','title','comments','revisions')
        );

register_post_type('audio',$args);
}

?>