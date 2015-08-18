<?php

// REGISTER TOURDATES POST TYPE

add_action('init', 'posttype_tourdates');

function posttype_tourdates() {
        $labels = array(
                'name' => __('Tour Dates', 'gxg_textdomain'),
                'singular_name' => __('Date', 'gxg_textdomain'),
                'add_new' => __('Add Date', 'gxg_textdomain'),
                'add_new_item' => __('Add New Date','gxg_textdomain'),
                'edit_item' => __('Edit Date','gxg_textdomain'),
                'new_item' => __('New Date','gxg_textdomain'),
                'view_item' => __('View Details','gxg_textdomain'),
                'search_items' => __('Search Tourdates','gxg_textdomain'),
                'not_found' =>  __('No Dates were found with that criteria','gxg_textdomain'),
                'not_found_in_trash' => __('No Date found in the Trash with that criteria','gxg_textdomain'),
                'view' =>  __('View Date','gxg_textdomain')
        );

        $imagepath =  get_template_directory_uri() . '/images/posttypeimg/';

        global $wp_version;
	if( version_compare( $wp_version, '3.8', '>=') ) {
	    	$img =  'tou.png';
	} else {
		$img =  'tou_.png';
	}

        $args = array(
                'labels' => $labels,
                'description' => 'This is the holding location for all Tourdates',
                'public' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => false,
                'show_ui' => true,
                'rewrite' => true,
                'hierarchical' => true,
                'menu_position' => 5,
                'menu_icon' => $imagepath . $img,
                'supports' => array('title','revisions'),
        );

register_post_type('tourdates',$args);
}

?>