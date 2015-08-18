<?php

function options_panel_styles() {
        
        ?>        
        <style type="text/css">
        <?php
        
        
        /** CUSTOM STYLES (THEME OPTIONS PANEL) **/


        /* center logo*/
        $logo_width = of_get_option('gg_logo_center');
        
        if ( of_get_option('gg_logo_center') ) {
        ?>
                #logo {
                        width: <?php echo $logo_width; ?>px;
                }
        <?php
        }
        
        
        /* background color / background image / background position */
        $bg_color = of_get_option('gg_bg_color');
        $bg_custom = of_get_option('gg_bg_image_custom', 'full');
        
        
        if ( of_get_option('gg_bg_color') ) {
        ?>
                body {
                        background-color: <?php echo $bg_color; ?>;
                }
        <?php                                  
        }
        
        if ( of_get_option('gg_skin') == 'dark' ) {
        ?>
                html {
                        background-color: #292929;
                }
        <?php
        }
        else {
        ?>
                html {
                        background-color: #fff;
                }
        <?php
        }
        
        if ( of_get_option('gg_bg_image_custom') && of_get_option('gg_bg_position') == 'repeat' ) {
        ?>
                html {
                        background: url("<?php echo $bg_custom; ?>");
                        background-repeat: repeat;
                        }
        <?php
        }
        elseif ( of_get_option('gg_bg_image_custom') && of_get_option('gg_bg_position') == 'fixed' ) {
        ?>
                html{
                        background: url("<?php echo $bg_custom; ?>");
                        background-color: <?php echo $bg_color; ?>;
                        background-repeat: no-repeat;
                        background-position: center top;                
                        }
        <?php
        }
        else {
        ?>
                html {
                        background: transparent;
                }
        
        <?php
        }
        
        
        /* content background color image*/
        $contentbgimage = of_get_option('gg_content_bg_image');
        if ( of_get_option('gg_content_bg_image') ) {
        ?>
        #bg-wrapper,
        #topnavi,
        #slide-bg {        
                background:url(<?php echo $contentbgimage; ?>) repeat;
                }
        <?php
        }
        
        
        
        /* box shadow */
        if ( of_get_option('gg_shadow') ) {
        ?>
        #bg-wrapper,
        #topnavi,
        #slide-bg {        
                box-shadow: 0 1px 1px rgba(0,0,0,0.1);
                }
        <?php
        }
        
        
        
        
        /* color */
        $color = of_get_option('gg_link_color');
        if ( of_get_option('gg_link_color') ) {
        ?>
        a, 
        a:visited,
        h1 a:hover, h1 a:active,
        h2 a:hover, h2 a:active,
        h3 a:hover, h3 a:active,
        h4 a:hover, h4 a:active,
        h5 a:hover, h5 a:active,
        h6 a:hover, h6 a:active,
        #footer-widget-area a:hover,
        #footer-widget-area a:active
                 {
                 color: <?php echo $color; ?>;
                 }
                 
        .sf-menu li:hover,

        ul.login li a:hover,
        span.page-numbers,
        a.page-numbers:hover,
        li.comment .reply,
        .login-submit input
                {
                background-color: <?php echo $color; ?>;
                }
                
        a:hover.nivo-nextNav,
        a:hover.nivo-prevNav,
        .nivo-caption p
                {
                background-color: <?php echo $color; ?>;
                }         
                
        <?php
        }
        
        
        
        /* colorpicker */
        $colorpicker = of_get_option('gg_link_colorpicker');
        if ( of_get_option('gg_link_colorpicker') ) {
        ?>
        a, a:link,
        a:visited,
        h1 a:hover, h1 a:active,
        h2 a:hover, h2 a:active,
        h3 a:hover, h3 a:active,
        h4 a:hover, h4 a:active,
        h5 a:hover, h5 a:active,
        h6 a:hover, h6 a:active,
        #footer-widget-area a:hover,
        #footer-widget-area a:active
                 {
                 color: <?php echo $colorpicker; ?>;
                 }
        
        .sf-menu li:hover,
       
        ul.login li a:hover,
        span.page-numbers,
        a.page-numbers:hover,
        li.comment .reply,
        .login-submit input
                {
                background-color: <?php echo $colorpicker; ?>;
                }           
        
        a:hover.nivo-nextNav,
        a:hover.nivo-prevNav,
        .nivo-caption p
                {
                background-color: <?php echo $colorpicker; ?> !important;
                }           
                
        <?php
        }
        
        
        /* footer and copyright color */
        $footercolor = of_get_option('gg_footer_color');
        $copyrightcolor = of_get_option('gg_copyright_color');
        if ( of_get_option('gg_footer_color') ) { ?>
                #footer {
                        background-color: <?php echo $footercolor; ?>;
                }
        <?php }

        if ( of_get_option('gg_copyright_color') ) { ?>
                #copyright, #social   {
                        background-color: <?php echo $copyrightcolor; ?>;
                }
        <?php }        
        
        
        
        /* font */
        $font = of_get_option('gg_font');
        $font2 = of_get_option('gg_font2');
        
        if ( of_get_option('gg_font2') ) {
        ?>
        h1, h2, h3, h4, h5, h6,
        .button,
        .button1,
        .buttonS,
        .button2,
        .submitbutton,
        .cancelled,
        .soldout,
        span.reply,
        .details,
        .dropcap,
        li.comment cite,
        ul.login li a,
        .sf-menu a,
        .comment-reply-link,
        .nivo-caption p
                 {
                 font-family: "<?php echo $font2; ?>" , "Helvetica Neue", Arial, "sans-serif";
                 }
        <?php
        } elseif ( of_get_option('gg_font') ) {
        ?>
        h1, h2, h3, h4, h5, h6,
        .button,
        .button1,
        .buttonS,
        .button2,
        .submitbutton,
        .cancelled,
        .soldout,
        span.reply,
        .details,
        .dropcap,
        li.comment cite,
        ul.login li a,
        .sf-menu a,
        .comment-reply-link,
        .nivo-caption p
                 {
                 font-family: "<?php echo $font; ?>" , "Helvetica Neue", Arial, "sans-serif";
                 }
        <?php
        }
        
        
        $trans = of_get_option('gg_trans');
        if ( of_get_option('gg_trans') ) {
        ?>
        h3.widgettitle,
        .button,
        .button1,
        .buttonS,
        .button2,
        .submitbutton,
        .cancelled,
        .soldout,
        span.reply,
        .date-h,
        h1.pagetitle,
        #content h3.widgettitle,
        ul.login li a,
        .sf-menu a,
        .comment-reply-link,
        .nivo-caption p
                 {
                 text-transform: <?php echo $trans; ?>;
                 }         
        <?php
        }
        
        
        /* navi font size */
        if ( of_get_option('gg_navifontsize') == '12px' ) { ?>
                .sf-menu a { font-size: 12px; }
                #topnavi { height: 42px; }
                .sf-menu ul { margin-top: 14px;}
                
        <?php }
        elseif ( of_get_option('gg_navifontsize') == '13px' ) { ?>
                .sf-menu a { font-size: 13px; }
                #topnavi { height: 43px; }
                .sf-menu ul { margin-top: 15px;}
        <?php }        
        elseif ( of_get_option('gg_navifontsize') == '14px' ) { ?>
                .sf-menu a { font-size: 14px; }
                #topnavi { height: 44px; }
                .sf-menu ul { margin-top: 16px;}
        <?php }
        elseif ( of_get_option('gg_navifontsize') == '15px' ) { ?>
                .sf-menu a { font-size: 15px; }
                #topnavi { height: 45px; }
                .sf-menu ul { margin-top: 17px;}
        <?php }        
        elseif ( of_get_option('gg_navifontsize') == '15px' ) { ?>
                .sf-menu a { font-size: 15px; }
                #topnavi { height: 45px; }
                .sf-menu ul { margin-top: 18px;}
        <?php }          
        elseif ( of_get_option('gg_navifontsize') == '16px' ) { ?>
                .sf-menu a { font-size: 16px; }
                #topnavi { height: 46px; }
                .sf-menu ul { margin-top: 19px;}
        <?php }         
        elseif ( of_get_option('gg_navifontsize') == '17px' ) { ?>
                .sf-menu a { font-size: 17px; }
                #topnavi { height: 47px; }
                .sf-menu ul { margin-top: 20px;}
        <?php }        
        elseif ( of_get_option('gg_navifontsize') == '18px' ) { ?>
                .sf-menu a { font-size: 18px; }
                #topnavi { height: 48px; }
                .sf-menu ul { margin-top: 21px;}
        <?php }        
        

        
        /* hide arrow of Login section in header */
        if (of_get_option('gg_loginsection')) { ?> 
                .arrow-down { border: 0; height: 6px; }        
        <?php }
        
        
        /* hide share buttons */
        if (of_get_option('gg_sbshare')) { ?> 
                .sb-share{ display: none !important;}        
        <?php } 
        
        
        /* social icons in footer*/
        $social1 = of_get_option('gg_social1');
        $social2 = of_get_option('gg_social2');
        $social3 = of_get_option('gg_social3');
        $social4 = of_get_option('gg_social4');
        $social5 = of_get_option('gg_social5');
        $socialwidth1 = of_get_option('gg_socialwidth1');
        $socialwidth2 = of_get_option('gg_socialwidth2');
        $socialwidth3 = of_get_option('gg_socialwidth3');
        $socialwidth4 = of_get_option('gg_socialwidth4');
        $socialwidth5 = of_get_option('gg_socialwidth5');
        
        ?>
        a.social1 {
                width: <?php echo $socialwidth1; ?>px;
                background:url("<?php echo $social1; ?>") no-repeat;
                }
                
        a.social2 {
                width: <?php echo $socialwidth2; ?>px;
                background:url("<?php echo $social2; ?>") no-repeat;
                }
                
        a.social3 {
                width: <?php echo $socialwidth3; ?>px;
                background:url("<?php echo $social3; ?>") no-repeat;
                }
        
        a.social4 {
                width: <?php echo $socialwidth4; ?>px;
                background:url("<?php echo $social4; ?>") no-repeat;
                }
                
        a.social5 {
                width: <?php echo $socialwidth5; ?>px;
                background:url("<?php echo $social5; ?>") no-repeat;
                }        
        <?php        
        
        /* custom css */
        echo of_get_option('gg_custom_css');


        ?>
        </style>
        <?php

}
add_action( 'wp_head', 'options_panel_styles', 100 );

?>