<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<!-- BEGIN head -->
<head>
        <!-- meta -->
        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
   <meta property="og:title" content="Perfect Havoc">
<meta property="og:description" content="Oliver Nelson, Skogsra, Tobtok and Televisor">
<meta property="og:image" content="http://localhost:8888/perfecthavoc/wp-content/uploads/2014/08/new-link.jpg">
<meta property="og:image:type" content="image/png">
<meta property="og:url" content="http://localhost:8888/perfecthavoc/"
<meta property="og:image:width" content="475">
<meta property="og:image:height" content="319">
<link rel=”image_src” href=”http://localhost:8888/perfecthavoc/wp-content/uploads/2014/08/new-link.jpg” />
        <!-- title -->
        <title> <?php wp_title(''); ?> <?php bloginfo('name'); ?></title>
        <!-- stylesheets -->
        <link href='http://fonts.googleapis.com/css?family=Ruda:900' rel='stylesheet' type='text/css'>        
        <?php if ( of_get_option('gg_font2') ) { ?>
                <link href='http://fonts.googleapis.com/css?family=<?php echo urlencode(of_get_option('gg_font2')); ?>' rel='stylesheet' type='text/css'>
        <?php } elseif ( of_get_option('gg_font') ) { ?>
                <link href='http://fonts.googleapis.com/css?family=<?php echo urlencode(of_get_option('gg_font')); ?>' rel='stylesheet' type='text/css'>
        <?php } ?>
        <!-- Pingbacks -->
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
        <?php
        if ( of_get_option('gg_favicon') ) { ?>
                <!-- Favicon -->
                <link rel="shortcut icon" href="<?php echo of_get_option('gg_favicon'); ?>" />
        <?php } ?>
        <?php
        if ( of_get_option('gg_google_analytics') ) {
                ?>
                <script type="text/javascript">
                <!-- Google Analytics -->
                <?php
                echo of_get_option('gg_google_analytics');  
                ?>
                </script>
                <?php         
        } ?>
        <!-- Calls Wordpress head functions -->
        <?php wp_head(); ?>                
<?php if( !is_front_page() ) : ?>
  <style>
    li.menu-item {
background: none;
}
.sf-menu li:hover {
background: #fff;
border: 3px transparent solid;
}
    #topnavi {
background: none;
}
    #bg-wrapper, #topnavi, #slide-bg {
box-shadow: none;
}
    </style>
  <?php endif; ?>
  <?php if( is_front_page() || is_page( 999 ) || is_page( 320 ) ): ?>
  <style>
        /* Main Menu Main Menu Main Menu*/
li.artists {
background-color: #f4b729;
}
li.artists:hover {
border: solid 3px #f4b729;
  color: #f4b729;
}
li.artists:hover a {
  color: #f4b729!important;
}
li.label-releases {
background-color: #019f4b;
}
li.label-releases:hover {
border: #019f4b solid 3px;
     color: #019f4b;
}
    li.label-releases:hover a {
  color: #019f4b!important;
}
li.events {
background-color: #0087c5;
}
li.events:hover {
border: solid 3px #0087c5;
     color:  #0087c5!important;
}
    li.events:hover a {
  color: #0087c5!important;
}
li.videos {
background-color: #fd393a;
}
li.videos:hover {
border: solid 3px #fd393a;
  color: #fd393a;
}
        li.videos:hover a {
  color:#fd393a!important;
}
li.events {
background-color: #0087c5;
}
li.events:hover {
border: solid 3px #0087c5;
  color:  #0087c5;
}
            li.events:hover a {
  color:#0087c5!important;
}
li.shop {
background-color: #f4b729;
}
li.shop:hover {
border: solid 3px #f4b729;
  color:  #f4b729;
}
            li.shop:hover a {
  color:#f4b729!important;
}
li.blog {
background-color: #0087c5;
}
li.blog:hover {
border: solid 3px #0087c5;
    color:#0087c5;
}
            li.blog:hover a {
  color:#0087c5!important;
}
li.about-us {
background-color: #fd393a;
}
li.about-us:hover {
border: solid 3px #fd393a;
    color:#fd393a;
}
        li.about-us:hover a {
  color:#fd393a!important;
}
li.contact-us {
background-color: #019f4b;
}
li.contact-us:hover {
border: solid 3px #019f4b;
   color:#019f4b;
}
        li.contact-us:hover a {
  color:#019f4b!important;
}
li.demos {
background-color: #0087c5;
}
li.demos:hover {
border: solid 3px #0087c5;
  color: #0087c5;
}
            li.demos:hover a {
  color:#0087c5!important;
}
li.mailing-list {
background-color: #f4b729;
}
li.mailing-list:hover {
border: solid 3px #f4b729;
  color:#f4b729;
}
            li.mailing-list:hover a {
  color:#f4b729!important;
}
    li#menu-item-984 {
background: #fd393a;
}
#bg-wrapper, #topnavi, #slide-bg {
box-shadow: none;
}
    </style>
    <?php endif; ?>
  <script type="text/javascript" src="//use.typekit.net/bky7wkj.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
</head><!-- END head -->
<!-- BEGIN body -->
<body <?php body_class(); ?>>
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
			<img src="http://www.perfecthavoc.com/wp-content/uploads/2014/07/perfect-havoc-TEXT_200.jpg" style="width:auto;height:30px;" />
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
