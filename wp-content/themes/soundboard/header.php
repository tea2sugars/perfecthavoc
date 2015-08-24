<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<!-- BEGIN head -->
<head>
        <!-- meta -->
        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
   <meta property="og:title" content="Perfect Havoc">
<meta property="og:description" content="Oliver Nelson, Skogsra, Tobtok and Televisor">
<meta property="og:image" content="/wp-content/uploads/2014/08/new-link.jpg">
<meta property="og:image:type" content="image/png">
<meta property="og:url" content="perfecthavoc/"
<meta property="og:image:width" content="475">
<meta property="og:image:height" content="319">
<link rel=”image_src” href=”/wp-content/uploads/2014/08/new-link.jpg” />
        <!-- title -->
        <title> <?php wp_title(''); ?> <?php bloginfo('name'); ?></title>
        <!-- stylesheets -->
        <link href='http://fonts.googleapis.com/css?family=Ruda:900' rel='stylesheet' type='text/css'>        
        <?php if ( of_get_option('gg_font2') ) { ?>
                <link href='http://fonts.googleapis.com/css?family=<?php echo urlencode(of_get_option('gg_font2')); ?>' rel='stylesheet' type='text/css'>
        <?php } elseif ( of_get_option('gg_font') ) { ?>
                <link href='http://fonts.googleapis.com/css?family=<?php echo urlencode(of_get_option('gg_font')); ?>' rel='stylesheet' type='text/css'>
        <?php } ?>
               <!-- Calls Wordpress head functions -->
        <?php wp_head(); ?>                
</head><!-- END head -->
<!-- BEGIN body -->
<body <?php body_class(); ?> id="rik">
<div id="wrapall">
        <div class="seniorita">	<ul><li class="boom boom-1"><form role="search" method="get" class="search-form" action="http://goodpep.com/yellow/">
				<label>
					<span class="screen-reader-text">Search for:</span>
					<input type="search" class="search-field" placeholder="Search …" value="" name="s" title="Search for:">
				</label>
				<input type="submit" class="search-submit" value="Search">
			</form></li><li  class="boom boom-2"> <aside id="text-2" class="widget widget_text">			<div class="textwidget"><li class="soci"><a href="https://www.facebook.com/perfecthavocmusic" target="_blank"><span>Like us</span><i class="icon-facebook"></i></a></li><li class="soci"><a href="https://twitter.com/perfecthavoc" target="_blank"><span>Follow</span><i class="icon-twitter"></i></a></li><li class="soci"><a href="https://soundcloud.com/perfecthavocmusic" target="_blank"><span>Follow</span><i class="icon-soundcloud"></i></a></li><li class="soci"><a href="https://www.youtube.com/user/perfecthavoctv" target="_blank"><span>Subscribe</span><i class="icon-youtube"></i></a></li></div>
		</aside></li>
			<li class="boom boom-3"><a class="home-link" href="http://perfecthavoc.com" title="Perfect Havoc" rel="home">
			<img src="/wp-content/uploads/2014/07/perfect-havoc-TEXT_200.jpg" style="width:auto;height:30px;" />
			</a></li></ul></div>
        <div id="wraptop">
        <div id="header">
                <?php if (!of_get_option('gg_loginsection')) { ?> 
                                <div id="loginwrapper">                     
                                        <ul class="login">
                                                <?php if ( is_user_logged_in() ) {                                          
                                                ?>                                
                                                <li><a href="<?php echo wp_logout_url( home_url() ); ?>" title="Logout"> <?php _e('LOG OUT', 'gxg_textdomain'); ?></a></li>
                                                <?php } 
                                                else { ?>
                                                <li><a href="<?php bloginfo('stylesheet_directory'); ?>/login.php?ajax=true&amp;width=600&amp;height=420" data-rel="prettyPhoto-login[ajax]"><?php _e('LOG IN', 'gxg_textdomain'); ?></a></li>
                                                 <?php } ?>
                                        </ul>                        
                                </div>  <!--#loginwrapper-->
                <?php } ?>
                <div id="headertop">                        
                        <?php
                        if ( of_get_option('gg_logo_image') ) {
                        ?><div id="logo">
                                <a href="<?php echo home_url(); ?>" > <img alt="" src="<?php echo of_get_option('gg_logo_image'); ?>" /> </a>
                        </div> <!-- #logo-->
                        <div class="clear"> </div>
                        <?php } ?>                        
                        <div id="topnavi">
                        <?php
                                wp_nav_menu( array(
                                        'theme_location' => 'main-menu',
                                        'menu_class' => 'sf-menu',
                                        'fallback_cb' => 'wp_page_menu', //if wp_nav_menu is unavailable, WordPress displays wp_page_menu function, which displays the pages.
                                        )
                                );
                        ?></div>                        
                        <?php
                        if( of_get_option('gg_slider') != "" && is_page_template('template-home.php') ) {                        
                                ?>
                                <div id="slide-bg"> 
                                        <div id="slideshow">                         
                                                <?php get_template_part( 'slider' ); ?>
                                        </div><!-- #slideshow-->
                                </div><!-- #slide-bg-->    
                        <?php }
                        ?>
                </div> <!-- #headertop-->
        </div> <!-- #header -->
        <div id="bg-wrapper">
          
   <?php if( is_front_page() || is_page( 997 ) || is_page( 111 ) ): ?>
                 <h1 class="pagetitle"> <?php the_title(); ?> </h1>
          
  
    <?php endif; ?>
        <div id="wrapper">                
