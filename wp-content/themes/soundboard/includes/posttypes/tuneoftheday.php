<?php

// REGISTER Label Release POST TYPE

add_action('init', 'posttype_tuneoftheday');

function posttype_tuneoftheday() {
        $labels = array(
                'name' => __('Tune of the Day', 'gxg_textdomain'),
                'singular_name' => __('Tune', 'gxg_textdomain'),
                'add_new' => __('Add New Tune', 'gxg_textdomain'),
                'add_new_item' => __('Add New Tune of the Day','gxg_textdomain'),
                'edit_item' => __('Edit Tune','gxg_textdomain'),
                'new_item' => __('New Tune','gxg_textdomain'),
                'view_item' => __('View Details','gxg_textdomain'),
                'search_items' => __('Search Tune of the Day','gxg_textdomain'),
                'not_found' =>  __('No Tune was found with that criteria','gxg_textdomain'),
                'not_found_in_trash' => __('No Tune found in the Trash with that criteria','gxg_textdomain'),
                'view' =>  __('View Release', 'gxg_textdomain')
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
            		'menu_name' => 'Tune of the Day',
                'menu_icon' => $imagepath . $img,
                'supports' => array('thumbnail','title','comments','revisions')
        );

register_post_type('tuneoftheday',$args);
}

?>