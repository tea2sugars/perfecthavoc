<?php
/**
 * Registering meta boxes
 */

/**
 * Add field type: 'taxonomy'
 *
 * Note: The class name must be in format "RWMB_{$field_type}_Field"
 */
if ( !class_exists( 'RWMB_Taxonomy_Field' ) )
{
	class RWMB_Taxonomy_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_print_styles()
		{
			wp_enqueue_style( 'rwmb-taxonomy', RWMB_CSS_URL . 'taxonomy.css', RWMB_VER );
			wp_enqueue_script( 'rwmb-taxonomy', RWMB_JS_URL . 'taxonomy.js', array( 'jquery', 'wp-ajax-response' ), RWMB_VER, true );
		}

		/**
		 * Add default value for 'taxonomy' field
		 *
		 * @param $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			// Default query arguments for get_terms() function
			$default_args = array(
				'hide_empty' => false
			);
			if ( !isset( $field['options']['args'] ) )
				$field['options']['args'] = $default_args;
			else
				$field['options']['args'] = wp_parse_args( $field['options']['args'], $default_args );

			// Show field as checkbox list by default
			if ( !isset( $field['options']['type'] ) )
				$field['options']['type'] = 'checkbox_list';

			// If field is shown as checkbox list, add multiple value
			if ( 'checkbox_list' == $field['options']['type'] ||  'checkbox_tree' == $field['options']['type']){
				$field['multiple'] = true;
				$field['field_name'] = $field['field_name'] . '[]'; 
			}

			if('checkbox_tree' == $field['options']['type'] && !isset( $field['options']['args']['parent'] ) )
				$field['options']['args']['parent'] = 0;

			return $field;
		}

		/**
		 * Get field HTML
		 *
		 * @param $html
		 * @param $field
		 * @param $meta
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{
			global $post;

			$options = $field['options'];

			$terms = get_terms( $options['taxonomy'], $options['args'] );

			$html = '';
			// Checkbox_list
			if ( 'checkbox_list' == $options['type'] )
			{
				foreach ( $terms as $term )
				{
					$html .= "<input type='checkbox' name='{$field['field_name']}' value='{$term->slug}'" . checked( in_array( $term->slug, $meta ), true, false ) . " /> {$term->name}<br/>";
				}
			}
			//Checkbox Tree
			elseif ( 'checkbox_tree' == $options['type'] )
			{
				$html .= self::walk_checkbox_tree($meta, $field, true);
			}
			// Select
			else
			{
				
				$html .= "<select name='{$field['field_name']}'" . ( $field['multiple'] ? " multiple='multiple' style='height: auto;'" : "'" ) . ">";
				foreach ( $terms as $term )
				{
					$html .= "<option value='{$term->slug}'" . selected( in_array( $term->slug, $meta ), true, false ) . ">{$term->name}</option>";
				}
				$html .= "</select>";
			}

			return $html;
		}

		/**
		 * Walker for displaying checkboxes in treeformat
		 *
		 * @param $meta
		 * @param $field
		 * @param bool $active
		 *
		 * @return string
		 */
		static function walk_checkbox_tree( $meta, $field, $active = false )
		{
			$options = $field['options'];
			$terms = get_terms( $options['taxonomy'], $options['args'] );
			$count = count($terms);
			$html = '';
			$hidden = ( !$active ? 'hidden' : '' );
			if ( $count > 0 )
			{
				$html = "<ul class = 'rw-taxonomy-tree {$hidden}'>";
				foreach ( $terms as $term )
				{
					$html .= "<li> <input type='checkbox' name='{$field['field_name']}' value='{$term->slug}'" . checked( in_array( $term->slug, $meta ), true, false ) . disabled($active,false,false) . " /> {$term->name}";
					$field['options']['args']['parent'] = $term->term_id;
					$html .= self::walk_checkbox_tree($meta, $field, (in_array( $term->slug, $meta))) . "</li>";
				}
				$html .= "</ul>";
			}
			return $html;
		}

		/**
		 * Save post taxonomy
		 * @param $post_id
		 * @param $field
		 * @param $old
		 * @param $new
		 */
		static function save( $new, $old, $post_id, $field )
		{
			wp_set_object_terms( $post_id, $new, $field['options']['taxonomy'] );
		}
		
		/**
		 * Standard meta retrieval
		 *
		 * @param mixed 	$meta
		 * @param int		$post_id
		 * @param array  	$field
		 * @param bool  	$saved
		 *
		 * @return mixed
		 */
		static function meta( $meta, $post_id, $saved, $field )
		{
			
			$options = $field['options'];
			$meta = wp_get_post_terms( $post_id, $options['taxonomy'] );
			$meta = is_array( $meta ) ? $meta : ( array ) $meta;
			$meta = wp_list_pluck($meta, 'slug');
			return $meta;
		}
	}
}

/********************* META BOXES DEFINITION ***********************/

/**
 * Prefix of meta keys (optional)
 * Wse underscore (_) at the beginning to make keys hidden
 * You also can make prefix empty to disable it
 */
$prefix = 'gxg_';

global $meta_boxes;

$meta_boxes = array();


/** TOURDATES **/
$meta_boxes[] = array(
        'id' => 'tourdates',
        'title' =>  __('TOUR DATES','gxg_textdomain'), 
        'pages' => array('tourdates'),
        'fields' => array(                
                array(
                        'name' =>   __('Date','gxg_textdomain'),             
                        'desc' => '',        
                        'id' => $prefix . 'date',      
                        'type' => 'date',
                        'format' => 'yy/mm/dd',               
                        'std' => '',                    
                ),
                array(
                        'name' =>   __('Time','gxg_textdomain'),             
                        'desc' => '',        
                        'id' => $prefix . 'time',      
                        'type' => 'text',              
                        'std' => '',                    
                ),                 
                array(
                        'name' => __('City / Country','gxg_textdomain'),          
                        'desc' => '',    
                        'id' => $prefix . 'city',            
                        'type' => 'text',                    
                        'std' => '',                         
                ),
                array(
                        'name' => __('Venue','gxg_textdomain'),                     
                        'desc' => '',           
                        'id' => $prefix . 'venue',           
                        'type' => 'text',                    
                        'std' => '',                         
                ),                
                array(
                        'name' => __('Ticket URL','gxg_textdomain'),                          
                        'desc' => __('Enter the full URL to the ticket sales website','gxg_textdomain'),           
                        'id' => $prefix . 'url',                 
                        'type' => 'text',                        
                        'std' => '',                             
                ),
                array(
                        'name' => __('Ticket URL Button Text','gxg_textdomain'),                          
                        'desc' => __('Enter Button Text','gxg_textdomain'),           
                        'id' => $prefix . 'button_text',                 
                        'type' => 'text',                        
                        'std' => 'Buy Tickets',                             
                ),              
                array(
			'name' => __('Sold Out?','gxg_textdomain'),      
			'id' => $prefix . 'soldout',
			'type' => 'checkbox',
			'desc' => __('Check if show is sold out.','gxg_textdomain'),    
			'std' => ''                      // Value can be 0 or 1
		),
                array(
			'name' => __('Cancelled?','gxg_textdomain'),       
			'id' => $prefix . 'cancelled',
			'type' => 'checkbox',
			'desc' => __('Check if show is cancelled.','gxg_textdomain'),    
			'std' => ''                      // Value can be 0 or 1
		),
                array(
                        'name' => __('More Info','gxg_textdomain'),                     
                        'desc' => '',           
                        'id' => $prefix . 'more',           
                        'type' => 'wysiwyg',                    
                        'std' => '',                         
                ),                 
        )
);


/** GALLERY **/
$meta_boxes[] = array(
	'id' => 'gallery',
	'title' => __('GALLERY','gxg_textdomain'), 
	'pages' => array( 'gallery' ),

	'fields' => array(
		array(
			'name' => __('Upload your images. </br></br> Drag and drop images to reorder them.','gxg_textdomain'), 
			'desc' => '',
			'id' => $prefix . 'gallery_images',
			'type' => 'image_advanced'       
		)
	)
);


/** AUDIO **/
$meta_boxes[] = array(
        'id' => 'songs',
        'title' => __('SONGTITLES','gxg_textdomain'), 
        'pages' => array('audio'),
        'fields' => array(                
                array(
                        'name' => __('Add the Album\'s songtitles','gxg_textdomain'), 
                        'id' => $prefix . 'song',
                        'clone' => true,
                        'type' => 'text',
                        ), 
        )
);

$meta_boxes[] = array(
        'id' => 'soundcloud',
        'title' => __('SOUNDCLOUD PLAYER','gxg_textdomain'), 
        'pages' => array('audio'),
        'fields' => array(                
                array(
                        'name' => __('Paste Embed Code','gxg_textdomain'),              
                        'desc' => '',        
                        'id' => $prefix . 'soundcloud',      
                        'type' => 'textarea',               
                        'std' => '',                    
                ),            
        )
);


$meta_boxes[] = array(
        'id' => 'jwplayer',
        'title' => __('AUDIO PLAYER (requires JW Player)','gxg_textdomain'), 
        'pages' => array('audio'),
        'fields' => array(
                array(
			'name' => __('Display Audio Player?','gxg_textdomain'),  
			'id' => $prefix . 'audioplayer',
			'type' => 'checkbox',
			'desc' => __('Check if you would like to display an Audio Player','gxg_textdomain'), 
			'std' => '' 
		),  
                array( 
                		'name' => __('Upload music files. They will start uploading once you hit the <b>Publish / Update</b> button above. If you upload many songtitles at once, this might take a while. </br> </br> If you have trouble uploading a file, please check your Maximum upload file size in <b>Media > Add New </b>','gxg_textdomain'), 
                		'id' => $prefix . 'jwplayer',
                                'type' => 'file_advanced',
                ),             
        )
);


$meta_boxes[] = array(
        'id' => 'album-buy',
        'title' => __('BUY / DOWNLOAD LINKS','gxg_textdomain'), 
        'pages' => array('audio'),
        'fields' => array(   
                array(
                        'name' => __('Amazon','gxg_textdomain'),                      
                        'desc' => __('Enter the full URL to the album on Amazon','gxg_textdomain'),        
                        'id' => $prefix . 'amazon',                 
                        'type' => 'text',                        
                        'std' => '',                             
                ),
                array(
                        'name' => __('iTunes','gxg_textdomain'),                        
                        'desc' => __('Enter the full URL to the album on iTunes','gxg_textdomain'),          
                        'id' => $prefix . 'itunes',                 
                        'type' => 'text',                        
                        'std' => '',                             
                ),                
                array(
                        'name' => __('Other buying / downloading link','gxg_textdomain'),                       
                        'desc' => __('Enter the full URL to the album','gxg_textdomain'),         
                        'id' => $prefix . 'buy_other',                 
                        'type' => 'text',                        
                        'std' => '',
                ),      
                array(
                        'name' => __('Button Text for other buying / downloading link','gxg_textdomain'),                      
                        'desc' => '',      
                        'id' => $prefix . 'buy_other_text',                 
                        'type' => 'text',                        
                        'std' => '',                          
                ),                
        )
);


$meta_boxes[] = array(
        'id' => 'albums',
        'title' => __('ALBUM INFO','gxg_textdomain'), 
        'pages' => array('audio'),
        'fields' => array(                
                array(
                        'name' => __('Release Date','gxg_textdomain'),              
                        'desc' => '',        
                        'id' => $prefix . 'releasedate', 
                        'type' => 'date',
                        'format' => 'd M yy',                
                        'std' => '',                    
                ),  
                array(
                        'name' => __('Additional Info LEFT column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info about the Album. It will be displayed in the left column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo_left',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                ),                 
                array(
                        'name' => __('Additional Info CENTER column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info about the Album. It will be displayed in the center column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo_center',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                ),                    
                array(
                        'name' => __('Additional Info RIGHT column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info (Lyrics, etc...) about the Album. It will be displayed in the right column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                )                
        )
);



/** VIDEO **/
$meta_boxes[] = array(
        'id' => 'video',
        'title' => __('YOUTUBE OR VIMEO VIDEO','gxg_textdomain'), 
        'pages' => array('video'),
        'fields' => array(        
                array(
                        'name' => __('Enter the link to a YouTube or Vimeo video','gxg_textdomain'),               
                        'desc' => __('It should look like this: http://vimeo.com/31956969 or http://youtu.be/nVssYUGs-R4','gxg_textdomain'),  
                        'id' => $prefix . 'videoembedcode',      
                        'type' => 'text',               
                        'std' => '',                    
                ),                    
        )
);
$meta_boxes[] = array(
        'id' => 'video2',
        'title' => __('SELF-HOSTED VIDEO (requires JW Player)','gxg_textdomain'), 
        'pages' => array('video'),
        'fields' => array(        
                array(
                        'name' => __('Enter full URL to the video','gxg_textdomain'),              
                        'desc' => '',        
                        'id' => $prefix . 'videofile',      
                        'type' => 'text',               
                        'std' => '',                    
                ),                 
        )
);

/** LABEL Release **/
$meta_boxes[] = array(
        'id' => 'songs',
        'title' => __('SONGTITLES','gxg_textdomain'), 
        'pages' => array('labelr'),
        'fields' => array(                
                array(
                        'name' => __('Add the Album\'s songtitles','gxg_textdomain'), 
                        'id' => $prefix . 'song',
                        'clone' => true,
                        'type' => 'text',
                        ), 
        )
);

$meta_boxes[] = array(
        'id' => 'soundcloud',
        'title' => __('SOUNDCLOUD PLAYER','gxg_textdomain'), 
        'pages' => array('labelr'),
        'fields' => array(                
                array(
                        'name' => __('Paste Embed Code','gxg_textdomain'),              
                        'desc' => '',        
                        'id' => $prefix . 'soundcloud',      
                        'type' => 'textarea',               
                        'std' => '',                    
                ),            
        )
);


$meta_boxes[] = array(
        'id' => 'jwplayer',
        'title' => __('AUDIO PLAYER (requires JW Player)','gxg_textdomain'), 
        'pages' => array('labelr'),
        'fields' => array(
                array(
			'name' => __('Display Audio Player?','gxg_textdomain'),  
			'id' => $prefix . 'audioplayer',
			'type' => 'checkbox',
			'desc' => __('Check if you would like to display an Audio Player','gxg_textdomain'), 
			'std' => '' 
		),  
                array( 
                		'name' => __('Upload music files. They will start uploading once you hit the <b>Publish / Update</b> button above. If you upload many songtitles at once, this might take a while. </br> </br> If you have trouble uploading a file, please check your Maximum upload file size in <b>Media > Add New </b>','gxg_textdomain'), 
                		'id' => $prefix . 'jwplayer',
                                'type' => 'file_advanced',
                ),             
        )
);


$meta_boxes[] = array(
        'id' => 'album-buy',
        'title' => __('BUY / DOWNLOAD LINKS','gxg_textdomain'), 
        'pages' => array('labelr'),
        'fields' => array(   
                array(
                        'name' => __('Amazon','gxg_textdomain'),                      
                        'desc' => __('Enter the full URL to the album on Amazon','gxg_textdomain'),        
                        'id' => $prefix . 'amazon',                 
                        'type' => 'text',                        
                        'std' => '',                             
                ),
                array(
                        'name' => __('iTunes','gxg_textdomain'),                        
                        'desc' => __('Enter the full URL to the album on iTunes','gxg_textdomain'),          
                        'id' => $prefix . 'itunes',                 
                        'type' => 'text',                        
                        'std' => '',                             
                ),                
                array(
                        'name' => __('Other buying / downloading link','gxg_textdomain'),                       
                        'desc' => __('Enter the full URL to the album','gxg_textdomain'),         
                        'id' => $prefix . 'buy_other',                 
                        'type' => 'text',                        
                        'std' => '',
                ),      
                array(
                        'name' => __('Button Text for other buying / downloading link','gxg_textdomain'),                      
                        'desc' => '',      
                        'id' => $prefix . 'buy_other_text',                 
                        'type' => 'text',                        
                        'std' => '',                          
                ),                
        )
);


$meta_boxes[] = array(
        'id' => 'albums',
        'title' => __('ALBUM INFO','gxg_textdomain'), 
        'pages' => array('labelr'),
        'fields' => array(                
                array(
                        'name' => __('Release Date','gxg_textdomain'),              
                        'desc' => '',        
                        'id' => $prefix . 'releasedate', 
                        'type' => 'date',
                        'format' => 'd M yy',                
                        'std' => '',                    
                ),  
                array(
                        'name' => __('Additional Info LEFT column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info about the Album. It will be displayed in the left column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo_left',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                ),                 
                array(
                        'name' => __('Additional Info CENTER column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info about the Album. It will be displayed in the center column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo_center',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                ),                    
                array(
                        'name' => __('Additional Info RIGHT column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info (Lyrics, etc...) about the Album. It will be displayed in the right column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                )                
        )
);



/** SLIDER **/
$meta_boxes[] = array(
	'id' => 'slider',
	'title' => __('SLIDER','gxg_textdomain'), 
	'pages' => array( 'slider' ),

	'fields' => array(
		array(
			'name' => __('Image must be 940x360px. </br></br>
                                        Drag and drop images to reorder them. </br></br>
                                        To enter an URL where the image should link to, click <b>Edit</b> and enter the full URL under <b>SLIDER Image Links To</b>. </br></br>
                                        To display a caption over the slider image,click <b>Edit</b> and enter the text under <b>SLIDER Caption</b>.','gxg_textdomain'), 
			'desc' => '',
			'id' => $prefix . 'slider_images',
			'type' => 'image_advanced'       
		)
	)
);



/** Tune of the day Release **/
$meta_boxes[] = array(
        'id' => 'songs',
        'title' => __('SONGTITLES','gxg_textdomain'), 
        'pages' => array('tuneoftheday'),
        'fields' => array(                
                array(
                        'name' => __('Add the Album\'s songtitles','gxg_textdomain'), 
                        'id' => $prefix . 'song',
                        'clone' => true,
                        'type' => 'text',
                        ), 
        )
);

$meta_boxes[] = array(
        'id' => 'soundcloud',
        'title' => __('SOUNDCLOUD PLAYER','gxg_textdomain'), 
        'pages' => array('tuneoftheday'),
        'fields' => array(                
                array(
                        'name' => __('Paste Embed Code','gxg_textdomain'),              
                        'desc' => '',        
                        'id' => $prefix . 'soundcloud',      
                        'type' => 'textarea',               
                        'std' => '',                    
                ),            
        )
);


$meta_boxes[] = array(
        'id' => 'jwplayer',
        'title' => __('AUDIO PLAYER (requires JW Player)','gxg_textdomain'), 
        'pages' => array('tuneoftheday'),
        'fields' => array(
                array(
			'name' => __('Display Audio Player?','gxg_textdomain'),  
			'id' => $prefix . 'audioplayer',
			'type' => 'checkbox',
			'desc' => __('Check if you would like to display an Audio Player','gxg_textdomain'), 
			'std' => '' 
		),  
                array( 
                		'name' => __('Upload music files. They will start uploading once you hit the <b>Publish / Update</b> button above. If you upload many songtitles at once, this might take a while. </br> </br> If you have trouble uploading a file, please check your Maximum upload file size in <b>Media > Add New </b>','gxg_textdomain'), 
                		'id' => $prefix . 'jwplayer',
                                'type' => 'file_advanced',
                ),             
        )
);


$meta_boxes[] = array(
        'id' => 'album-buy',
        'title' => __('BUY / DOWNLOAD LINKS','gxg_textdomain'), 
        'pages' => array('labelr'),
        'fields' => array(   
                array(
                        'name' => __('Amazon','gxg_textdomain'),                      
                        'desc' => __('Enter the full URL to the album on Amazon','gxg_textdomain'),        
                        'id' => $prefix . 'amazon',                 
                        'type' => 'text',                        
                        'std' => '',                             
                ),
                array(
                        'name' => __('iTunes','gxg_textdomain'),                        
                        'desc' => __('Enter the full URL to the album on iTunes','gxg_textdomain'),          
                        'id' => $prefix . 'itunes',                 
                        'type' => 'text',                        
                        'std' => '',                             
                ),                
                array(
                        'name' => __('Other buying / downloading link','gxg_textdomain'),                       
                        'desc' => __('Enter the full URL to the album','gxg_textdomain'),         
                        'id' => $prefix . 'buy_other',                 
                        'type' => 'text',                        
                        'std' => '',
                ),      
                array(
                        'name' => __('Button Text for other buying / downloading link','gxg_textdomain'),                      
                        'desc' => '',      
                        'id' => $prefix . 'buy_other_text',                 
                        'type' => 'text',                        
                        'std' => '',                          
                ),                
        )
);


$meta_boxes[] = array(
        'id' => 'albums',
        'title' => __('ALBUM INFO','gxg_textdomain'), 
        'pages' => array('labelr'),
        'fields' => array(                
                array(
                        'name' => __('Release Date','gxg_textdomain'),              
                        'desc' => '',        
                        'id' => $prefix . 'releasedate', 
                        'type' => 'date',
                        'format' => 'd M yy',                
                        'std' => '',                    
                ),  
                array(
                        'name' => __('Additional Info LEFT column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info about the Album. It will be displayed in the left column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo_left',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                ),                 
                array(
                        'name' => __('Additional Info CENTER column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info about the Album. It will be displayed in the center column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo_center',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                ),                    
                array(
                        'name' => __('Additional Info RIGHT column','gxg_textdomain'),                          
                        'desc' =>  __('Enter any additional info (Lyrics, etc...) about the Album. It will be displayed in the right column. You can use HTML too.','gxg_textdomain'),        
                        'id' => $prefix . 'albuminfo',                 
                        'type' => 'textarea',                        
                        'std' => '',                             
                )                
        )
);


/** "IMAGE LINKS TO" FIELD ON MEDIA EDITOR - FOR SLIDER **/
function gg_image_attachment_fields_to_edit($form_fields, $post) {
        
        $form_fields["sliderurl"] = array(  
            "label" => __('SLIDER Image Links To','gxg_textdomain'),  
            "input" => "text",
            "value" => get_post_meta($post->ID, "_sliderurl", true),
            "helps" => __('Enter full URL. To be used with SLIDER images only.' ,'gxg_textdomain'),    
        );
        
        $form_fields["slidercaption"] = array(  
        "label" => __('SLIDER Caption','gxg_textdomain'),  
        "input" => "text",
        "value" => get_post_meta($post->ID, "_slidercaption", true),
        "helps" => __('Slider Caption. To be used with SLIDER images only. Use &#60;/br> for line breaks','gxg_textdomain'),
        );
    
    return $form_fields;  
}  

function gg_image_attachment_fields_to_save($post, $attachment) {    
        if( isset($attachment['sliderurl']) ){  
            update_post_meta($post['ID'], '_sliderurl', $attachment['sliderurl']);  
        }
        if( isset($attachment['sliderurl']) ){  
            update_post_meta($post['ID'], '_slidercaption', $attachment['slidercaption']);  
        }  
        return $post;  
}  

add_filter("attachment_fields_to_edit", "gg_image_attachment_fields_to_edit", null, 2); 
add_filter("attachment_fields_to_save", "gg_image_attachment_fields_to_save", null, 2); 





// Hook to 'admin_init' to make sure the meta box class is loaded before (in case using the meta box class in another plugin)
// This is also helpful for some conditionals like checking page template, categories, etc.
add_action( 'admin_init', 'your_prefix_register_meta_boxes' );

/**
 * Register meta boxes
 *
 * @return void
 */
function your_prefix_register_meta_boxes()
{
	global $meta_boxes;

	// Make sure there's no errors when the plugin is deactivated or during upgrade
	if ( class_exists( 'RW_Meta_Box' ) )
	{
		foreach ( $meta_boxes as $meta_box )
		{
			new RW_Meta_Box( $meta_box );
		}
	}
}











