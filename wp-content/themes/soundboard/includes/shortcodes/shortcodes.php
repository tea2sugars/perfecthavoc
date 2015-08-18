<?php

/* enable shortcodes in text widgets ---------------------------------------------------*/
add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode', 11);



/* clean up shortcode----------------------------------------------------------------------*/
function parse_shortcode_content($content){
        
    $array = array (
        '<p>[' => '[', 
        ']</p>' => ']', 
        ']<br />' => ']'
    );

    $content = strtr($content, $array);
    return $content;
}

add_filter('the_content', 'parse_shortcode_content');





/* COLUMNS -------------------------------------------------------------------*/
function one_half( $atts, $content = null ) {
        return '<div class="one_half">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_half', 'one_half');

function one_half_last( $atts, $content = null ) {
        return '<div class="one_half last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('one_half_last', 'one_half_last');

function one_third( $atts, $content = null ) {
        return '<div class="one_third">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_third', 'one_third');

function one_third_last( $atts, $content = null ) {
        return '<div class="one_third last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('one_third_last', 'one_third_last');

function two_third( $atts, $content = null ) {
        return '<div class="two_third">' . do_shortcode($content) . '</div>';
}
add_shortcode('two_third', 'two_third');

function two_third_last( $atts, $content = null ) {
        return '<div class="two_third last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('two_third_last', 'two_third_last');

function one_fourth( $atts, $content = null ) {
        return '<div class="one_fourth">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_fourth', 'one_fourth');

function one_fourth_last( $atts, $content = null ) {
        return '<div class="one_fourth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('one_fourth_last', 'one_fourth_last');

function three_fourth( $atts, $content = null ) {
        return '<div class="three_fourth">' . do_shortcode($content) . '</div>';
}
add_shortcode('three_fourth', 'three_fourth');

function three_fourth_last( $atts, $content = null ) {
        return '<div class="three_fourth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('three_fourth_last', 'three_fourth_last');

function one_fifth( $atts, $content = null ) {
        return '<div class="one_fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_fifth', 'one_fifth');

function one_fifth_last( $atts, $content = null ) {
        return '<div class="one_fifth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('one_fifth_last', 'one_fifth_last');        

function two_fifth( $atts, $content = null ) {
        return '<div class="two_fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('two_fifth', 'two_fifth');

function two_fifth_last( $atts, $content = null ) {
        return '<div class="two_fifth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('two_fifth_last', 'two_fifth_last');        

function three_fifth( $atts, $content = null ) {
        return '<div class="three_fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('three_fifth', 'three_fifth');

function three_fifth_last( $atts, $content = null ) {
        return '<div class="three_fifth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('three_fifth_last', 'three_fifth_last');        

function four_fifth( $atts, $content = null ) {
        return '<div class="four_fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('four_fifth', 'four_fifth');

function four_fifth_last( $atts, $content = null ) {
        return '<div class="four_fifth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('four_fifth_last', 'four_fifth_last');        

function one_sixth( $atts, $content = null ) {
        return '<div class="one_sixth">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_sixth', 'one_sixth');

function one_sixth_last( $atts, $content = null ) {
        return '<div class="one_sixth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('one_sixth_last', 'one_sixth_last');        

function five_sixth( $atts, $content = null ) {
        return '<div class="five_sixth">' . do_shortcode($content) . '</div>';
}
add_shortcode('five_sixth', 'five_sixth');

function five_sixth_last( $atts, $content = null ) {
        return '<div class="five_sixth last">' . do_shortcode($content) . '</div>
        <div class="clearboth"></div>';
}
add_shortcode('five_sixth_last', 'five_sixth_last');        



/* DROPCAPS ------------------------------------------------------------------*/
function dropcap($atts, $content= null, $code="") {
        
        $return = '<span class="dropcap">'.$content.'</span>';
        return $return;
}
add_shortcode('dropcap' , 'dropcap' );



/* PULLQUOTE -----------------------------------------------------------------*/
function pullquote_left($atts, $content=null, $code="") {
        $return = '<span class="pullquote_left">'.$content.'</span>';
        return $return;
}
add_shortcode('pullquote_left' , 'pullquote_left' );

function pullquote_right($atts, $content=null, $code="") {
        $return = '<span class="pullquote_right">'.$content.'</span>';
        return $return;
}
add_shortcode('pullquote_right' , 'pullquote_right' );



/* HIGHLIGHT -----------------------------------------------------------------*/
function highlight1($atts, $content=null, $code="") {
        $return = '<span class="highlight1">' . $content . '</span>';
        return $return;
}
add_shortcode('highlight1' , 'highlight1' );

function highlight2($atts, $content=null, $code="") {
        $return = '<span class="highlight2">' . $content . '</span>';
        return $return;
}
add_shortcode('highlight2' , 'highlight2' );


/* DIVIDER -----------------------------------------------------------------*/
function divider_hr( $atts, $content = null ) {
        return '<div class="divider_hr"></div>';
}
add_shortcode('divider', 'divider_hr');



/* TABS ----------------------------------------------------------------------*/
if (!function_exists('redsun_tabs')) {
	function redsun_tabs( $atts, $content = null ) {
		$defaults = array();
		extract( shortcode_atts( $defaults, $atts ) );
		
		STATIC $i = 0;
		$i++;

		// Extract the tab titles for use in the tab widget.
		preg_match_all( '/tab title="([^\"]+)"/i', $content, $matches, PREG_OFFSET_CAPTURE );
		
		$tab_titles = array();
		if( isset($matches[1]) ){ $tab_titles = $matches[1]; }
		
		$output = '';
		
		if( count($tab_titles) ){
		    $output .= '<div id="redsun-tabs-'. $i .'" class="tabs"><div class="redsun-tab-inner">';
			$output .= '<ul class="redsun-nav tabs clearfix">';
			
			foreach( $tab_titles as $tab ){
				$output .= '<li><a href="#redsun-tab-'. sanitize_title( $tab[0] ) .'">' . $tab[0] . '</a></li>';
			}
		    
		    $output .= '</ul>';
		    $output .= do_shortcode( $content );
		    $output .= '</div></div>';
		} else {
			$output .= do_shortcode( $content );
		}
		
		return $output;
	}
	add_shortcode( 'tabs', 'redsun_tabs' );
}

if (!function_exists('redsun_tab')) {
	function redsun_tab( $atts, $content = null ) {
		$defaults = array( 'title' => 'Tab' );
		extract( shortcode_atts( $defaults, $atts ) );
		
		return '<div id="redsun-tab-'. sanitize_title( $title ) .'" class="pane">'. do_shortcode( $content ) .'</div>';
	}
	add_shortcode( 'tab', 'redsun_tab' );
}




/* TOGGLES--------------------------------------------------------------------*/
function gg_toggle( $atts, $content = null ){
        extract( shortcode_atts( array(
                'title' => 'Click To Open'
        ), $atts ) );
        return '<h6 class="trigger"><a href="#">'. $title .'</a></h6>
                <div class="toggle_container">' . '<div class="block">' . do_shortcode($content) . '</div>' . '</div>';
}
add_shortcode('toggle', 'gg_toggle');



/* BUTTONS ------------------------------------------------------------------*/
function sButton($atts, $content = null) {
   extract(shortcode_atts(array(
        'link' => '#',
        'target' => '_self',
        'color' => '',
        ), $atts));
   
   return '<a class="buttonS" href="'.$link.'" target="'.$target.'" style="background-color:'.$color.'"><span>' . do_shortcode($content) . '</span></a>';
}
add_shortcode('button', 'sButton');




/* JWPLAYER AUDIO / VIDEO---------------------------------------------------------------------*/

$jwversion = of_get_option('gg_jwversion');

if( $jwversion == 'version6' ){

function audio($atts, $content = null) {   extract(shortcode_atts(array(
        'width' => '100%'
        ), $atts));
        
        global $post;

        $rand_id = mt_rand(999, 99999);        
        $bloginfo_link = get_bloginfo('template_directory');
        $skin = $bloginfo_link . '/jwplayer/JWPlayer6/six.xml';        
	$content = parse_shortcode_content($content);
        
        $jwplayer_code = <<<HTML
  
        <div id="mediaspace$rand_id"></div>
        <script type="text/javascript">
           jwplayer('mediaspace$rand_id').setup({
           'file': '$content',
           'skin': '$skin',
           'controlbar': 'bottom',
           'width': '$width',
           'primary': 'flash',
           'height': '28'
           });
        </script>

HTML;

        return $jwplayer_code;
}
add_shortcode('audio', 'audio');



function video($atts, $content = null) {
   extract(shortcode_atts(array(
        'image' => '#',
        'width' => '100%'
        ), $atts));
        
        global $post;
        $rand_id = mt_rand(999, 99999);
        $bloginfo_link = get_bloginfo('template_directory');
        $skin = $bloginfo_link . '/jwplayer/JWPlayer6/sixVideo.xml';        
	$content =  parse_shortcode_content($content);

        $jwplayer_code = <<<HTML
  
        <div id="mediaspace$rand_id">Video</div>
        <script type="text/javascript">
           jwplayer('mediaspace$rand_id').setup({
           'file': '$content',
           'skin': '$skin',
           'image': '$image',
           'controlbar': 'bottom',
           'width': '$width',
           'primary': 'flash',
           'height': '320'
           });
        </script>    

HTML;

        return $jwplayer_code;

}
add_shortcode('video', 'video');

}                        
                   


/* JW PLAYER 5*/

if( $jwversion == 'version5' ){      

function audio($atts, $content = null) {   extract(shortcode_atts(array(
        'width' => '100%'
        ), $atts));
        
        global $post;
        $bloginfo_link = get_bloginfo('template_directory');
        $flashplayer = $bloginfo_link . '/jwplayer/player.swf';
        $skin = $bloginfo_link . '/jwplayer/JWPlayer5/soundboard_audio.zip';
        $rand_id = mt_rand(999, 99999);

	$content =  parse_shortcode_content($content);

        $jwplayer_code = <<<HTML
  
        <div id="mediaspace$rand_id">Audio</div>
        <script type="text/javascript">
           jwplayer('mediaspace$rand_id').setup({
           'flashplayer': '$flashplayer',
           'file': '$content',
           'skin': '$skin',
           'controlbar': 'bottom',
           'width': '$width',
           'height': '28'
           });
        </script>   

HTML;

        return $jwplayer_code;
}
add_shortcode('audio', 'audio');


function video($atts, $content = null) {
   extract(shortcode_atts(array(
        'image' => '#',
        'width' => '100%'
        ), $atts));
        
        global $post;
        $bloginfo_link = get_bloginfo('template_directory');
        $flashplayer = $bloginfo_link . '/jwplayer/player.swf';
        $skin = $bloginfo_link . '/jwplayer/JWPlayer5/soundboard.zip';
        $skin2 = $bloginfo_link . '/jwplayer/JWPlayer5/soundboard/soundboard.xml';
        $rand_id = mt_rand(999, 99999);

	$content =  parse_shortcode_content($content);

        $jwplayer_code = <<<HTML
  
        <div id="mediaspace$rand_id">Video</div>
        <script type="text/javascript">
           jwplayer('mediaspace$rand_id').setup({
           'flashplayer': '$flashplayer',
           'file': '$content',
           'skin': '$skin',
           'skin': '$skin2',
           'image': '$image',
           'controlbar': 'bottom',
           'width': '$width',
           'height': '320'
           });
        </script>    

HTML;

        return $jwplayer_code;

}
add_shortcode('video', 'video');

}

?>