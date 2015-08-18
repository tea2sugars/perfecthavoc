<?php

// REGISTER SLIDER POST TYPE

add_action('init', 'posttype_slider');

function posttype_slider() {
        $labels = array(
                'name' => __('Slider', 'gxg_textdomain'),
                'singular_name' => __('Slider', 'gxg_textdomain'),
                'add_new' => __('Add Slider', 'gxg_textdomain'),
                'add_new_item' => __('Add New Slider', 'gxg_textdomain'),
                'edit_item' => __('Edit Slider', 'gxg_textdomain'),
                'new_item' => __('New Slider', 'gxg_textdomain'),
                'view_item' => __('View Details', 'gxg_textdomain'),
                'search_items' => __('Search Slider', 'gxg_textdomain'),
                'not_found' =>  __('No Slider was found with that criteria', 'gxg_textdomain'),
                'not_found_in_trash' => __('No Slider was found in the Trash with that criteria', 'gxg_textdomain'),
                'view' =>  __('View Slider', 'gxg_textdomain')
        );

        $imagepath =  get_template_directory_uri() . '/images/posttypeimg/';

        global $wp_version;
	if( version_compare( $wp_version, '3.8', '>=') ) {
	    	$img =  'sli.png';
	} else {
		$img =  'sli_.png';
	}

        $args = array(
                'labels' => $labels,
                'description' => 'This is the holding location for all Slider',
                'public' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => false,
                'show_ui' => true,
                'rewrite' => true,
                'hierarchical' => true,
                'menu_position' => 100,
                'menu_icon' => $imagepath . $img,
                'supports' => array('thumbnail','title','revisions'),
                /*'taxonomies' => array( 'post_tag', 'category')*/
        );

register_post_type('slider',$args);
}

?>