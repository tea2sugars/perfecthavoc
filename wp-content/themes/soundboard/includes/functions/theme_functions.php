<?php


/** AUTOMATICALLY ADD REL PRETTYPHOTO TO <A> TAGS THAT LINK TO AN IMAGE********/
add_filter('the_content', 'addlightboxrel_replace', 12);
add_filter('get_comment_text', 'addlightboxrel_replace');

function addlightboxrel_replace ($content) {
         global $post;
         $pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>(.*?)<\/a>/i";
         $replacement = '<a$1href=$2$3.$4$5 class="pretty_image" data-rel="prettyPhoto['.$post->ID.']"$6>$7</a>';
         $content = preg_replace($pattern, $replacement, $content);
         return $content;
}



/** PAGINATION ****************************************************************/
function gg_pagination($pages = '', $range = 2) {
     $showitems = ($range * 2)+1;

     global $paged;
     if ( empty($paged) ) $paged = 1;

     if ($pages == '') {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if (!$pages) {
             $pages = 1;
         }
     }

     if (1 != $pages) {
         echo "<div class='pagination_main'>";
         if ($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>&laquo;</a>";
         if ($paged > 1 && $showitems < $pages) echo "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo;</a>";

         for ($i=1; $i <= $pages; $i++) {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
             }
         }

         if ($paged < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($paged + 1)."'>&rsaquo;</a>";
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>&raquo;</a>";
         echo "</div>\n";
     }
}



/** TIME AGO FUNCTION FOR POST DATE *******************************************/
function time_ago( $type = 'post' ) {
	$d = 'comment' == $type ? 'get_comment_time' : 'get_post_time';
	return human_time_diff($d('U'), current_time('timestamp')) . " " . __('ago', 'gxg_textdomain');
}

function time_ago_comment( $type = 'comment' ) {
	$d = 'comment' == $type ? 'get_comment_time' : 'get_post_time';
	return human_time_diff($d('U'), current_time('timestamp')) . " " . __('ago', 'gxg_textdomain');
}




/** STYLE COMMENTS ************************************************************/
function gg_comment($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        
        static $counter;
        if (!isset($counter))
        $counter = $args['per_page'] * ($args['page'] - 1) + 1;
        elseif ($comment->comment_parent==0) {
        $counter++;
        }
        
        ?>   
        <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
        
                <div id="comment-<?php comment_ID(); ?>" class="single-comment">
                
                        <div class="comment-author vcard">
                           <?php echo get_avatar( $comment->comment_author_email, 40 ); ?>
                        </div>
                        
                        <div class="comment-body">
                                
                                <div class="comment-meta commentmetadata">
                                        <?php printf('<cite class="fn">%s</cite>', get_comment_author_link()) ?>
                                        <div class="comment-date">
                                                <?php echo time_ago_comment(); ?>
                                        </div>                                  
                                </div>
                                
                                <div class="comment-text">
                                        <?php if ($comment->comment_approved == '0') : ?>
                                           <em class="moderation"><?php _e('Your comment is awaiting moderation.', 'gxg_textdomain') ?></em>
                                           <br />
                                        <?php endif; ?>
                                                    
                                        <?php comment_text() ?>
                                </div>
                        </div>

                        <span class="reply"><?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?></span>
                      	
                        <div class="comment-counter"><?php echo $counter; ?> </div>
                        <div class="comment-arrow"> &uarr; </div>
                        
                </div>
<?php
}



/** CUSTOM LOGIN FORM *********************************************************/
function gg_custom_login() {
        
        if (of_get_option('gg_login_image')) {
                 echo '<style type="text/css">
                 h1 a { background-image:url(' . of_get_option('gg_login_image') . ') !important; }
                 .login h1 a {width: 100%;}
                 </style>';
        }
        
        echo '<style type="text/css">
        
        #login { padding: 20px 0 0; width: 100%; }
        
        .login form { border: none; box-shadow: none; padding: 20px; }
        
        .login h1 a { margin: 0 auto;} 
     
        #registerform,
        #loginform,
        #lostpasswordform { overflow: hidden; }
        
        div.updated,
        .login .message,
        .login form, 
        .login #nav,
        .login #backtoblog,
        #login_error {
                width: 400px;
                margin: 0 auto;
                }
        
        #login_error {margin-bottom: 20px;}        

        .login .button-primary {   
                background: none;
                background-color: #444;
                border-radius: 0;
                border: none;
                color: #FFFFFF;
                text-transform: uppercase;
                font-weight: normal;
                text-shadow: none;
                display: inline-block;
                height: 31px;
                padding: 0 10px;
                text-decoration: none;
                cursor:pointer;         
                margin-bottom: 0;
                }
                
        .login .button-primary,
        .login #nav a,
        .login #backtoblog a {
                -moz-transition: all 0.3s ease-in-out;
                -webkit-transition: all 0.3s ease-in-out;
                -o-transition: all 0.3s ease-in-out;
                transition: all 0.3s ease-in-out;
                box-shadow: none;
                }
                
        .login .button-primary:hover {
                background: none;
                background-color: #666 !important;
                color: #FFFFFF;
                text-shadow: none;
                box-shadow: none;
                }
     
        div.updated, .login .message {
                background-color: #888;
                border-radius: 0;
                color: #fff;
                border: none;
                padding: 15px 20px;
                margin-bottom: 20px;
                }

        </style>';
        
        $color = of_get_option('gg_link_color');
        if ( of_get_option('gg_link_color') ) {
                echo '<style type="text/css">
                .login .button-primary {background-color:' . $color . '!important;}
                .login .button-primary:hover { background-color: #666 !important;}
                .login #nav a, .login #backtoblog a {color:' . $color . '!important;}
                .login #nav a:hover, .login #backtoblog a:hover {color: #666 !important;}               
                </style>';
        }
        
        $colorpicker = of_get_option('gg_link_colorpicker');
        if ( of_get_option('gg_link_colorpicker') ) {
                echo '<style type="text/css">
                .login .button-primary {background-color:' . $colorpicker . '!important;}
                .login .button-primary:hover { background-color: #666 !important;}
                .login #nav a, .login #backtoblog a {color:' . $colorpicker . '!important;}
                .login #nav a:hover, .login #backtoblog a:hover {color: #666 !important;}               
                </style>';
        }        
}
add_action('login_head', 'gg_custom_login');


function gg_custom_login_url() {
if (of_get_option('gg_login_image')) {
         return home_url("/wp-admin/");
         }
}
add_filter( 'login_headerurl', 'gg_custom_login_url', 10, 4 );


function my_login_redirect($redirect_to, $request){
    global $current_user;
    get_currentuserinfo();
    //is there a user to check?
    if(is_array($current_user->roles))
    {
        //check for admins
        if(in_array("administrator", $current_user->roles))
            return home_url("/wp-admin/");
        else
            return home_url("/wp-admin/");
    }
}
add_filter("login_redirect", "my_login_redirect", 10, 3);

?>