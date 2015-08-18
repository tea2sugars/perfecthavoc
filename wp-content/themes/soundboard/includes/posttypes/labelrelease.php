<?php

// REGISTER Label Release POST TYPE

add_action('init', 'posttype_labelr');

function posttype_labelr() {
        $labels = array(
                'name' => __('Label Releases', 'gxg_textdomain'),
                'singular_name' => __('Release', 'gxg_textdomain'),
                'add_new' => __('Add Release', 'gxg_textdomain'),
                'add_new_item' => __('Add New Release','gxg_textdomain'),
                'edit_item' => __('Edit Label Release','gxg_textdomain'),
                'new_item' => __('New Release','gxg_textdomain'),
                'view_item' => __('View Details','gxg_textdomain'),
                'search_items' => __('Search Label Releases','gxg_textdomain'),
                'not_found' =>  __('No Label Release was found with that criteria','gxg_textdomain'),
                'not_found_in_trash' => __('No Label found in the Trash with that criteria','gxg_textdomain'),
                'view' =>  __('View Release', 'gxg_textdomain')
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
                'description' => 'This is the holding location for all Albums',
                'public' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => false,
                'show_ui' => true,
                'rewrite' => true,
                'hierarchical' => true,
                'menu_position' => 5,
            		'menu_name' => 'Label Releases',
                'menu_icon' => $imagepath . $img,
                'supports' => array('thumbnail','title','comments','revisions')
        );

register_post_type('labelr',$args);
}

?>