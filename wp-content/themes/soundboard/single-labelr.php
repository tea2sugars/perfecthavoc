<?php get_header(); ?>
 	
                  	<div class="album-title">
                  		<h1 class="pagetitle"><?php the_title(); ?></h1>
                  	</div>
        <div id="content" class="grid_12">

                <!-- Start the Loop. -->
                <?php 
                if ( have_posts() ) : while ( have_posts() ) : the_post(); 
                  
                $album_thumb = get_the_post_thumbnail($post->ID, 'square2');
                $releasedate = get_post_meta($post->ID, 'gxg_releasedate', true);
                $timestamp = strtotime($releasedate);
                $pretty_date_M = date('F', $timestamp);
                $pretty_date_d = date('d', $timestamp);
                $pretty_date_Y = date('Y', $timestamp);  
                $amazon = get_post_meta($post->ID, 'gxg_amazon', true);
                $itunes = get_post_meta($post->ID, 'gxg_itunes', true);
                $albuminfo_left = get_post_meta($post->ID, 'gxg_albuminfo_left', true);
                $albuminfo_center = get_post_meta($post->ID, 'gxg_albuminfo_center', true);
                $albuminfo = get_post_meta($post->ID, 'gxg_albuminfo', true);
                $soundcloud = get_post_meta($post->ID, 'gxg_soundcloud', true);
                $audioplayer = get_post_meta($post->ID, 'gxg_audioplayer', true);
                $buy_other = get_post_meta($post->ID, 'gxg_buy_other', true);
                $buy_other_text = get_post_meta($post->ID, 'gxg_buy_other_text', true);
                
                $detect = new Mobile_Detect();
                
                ?>

                <div class="clear"> </div>
                  
                <div class="album-left">
                  	<div class="album-artwork">                                                
                            	<?php echo $album_thumb; ?>    
                  	</div>
                        
                        
                        <div class="album-info">
                                
                                <?php 
                                if ($amazon || $itunes || $buy_other){ ?>                         
                                <div class="album-buybuttons">
                                        <h6 class="infotitle"> <?php _e('Social Media:', 'gxg_textdomain') ?></h6>          
                                        <?php
                                        if ($amazon){ ?>                  	                              
                                                <a href="<?php echo $amazon; ?>" class="button2 buy-amazon"><?php _e('AMAZON', 'gxg_textdomain') ?></a>     
                                        <?php }
                
                                        if ($itunes){ ?>
                                                <a href="<?php echo $itunes; ?>" class="button2 buy-itunes"><?php _e('iTUNES', 'gxg_textdomain') ?></a>       
                                        <?php }
                                        
                                        if ($buy_other){ ?>
                                                <a href="<?php echo $buy_other; ?>" class="button2 buy-other"><?php echo $buy_other_text; ?></a>         
                                        <?php }                                          
                                        ?> 
                                </div><!-- .album-buybuttons-->
                                <?php }
                                
                                 
                                if ($releasedate){ ?>                       
                                <div class="release-date">
                                        <h6 class="infotitle"> <?php _e('release date:', 'gxg_textdomain') ?> </h6>
                                        <p><?php echo $pretty_date_M . ' ' . $pretty_date_d . ', ' . $pretty_date_Y; ?> </p>
                                </div>
                                <?php }
                                ?>
                                
                                <h6 class="infotitle sb-share"> <?php _e('share:', 'gxg_textdomain') ?></h6> 
                                
                                <ul id="album-social sb-share">                               
                                        <li class="tweet-button sb-share">               
                                                	<a href="https://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script> 
                                   
                                        </li>  
                                        
                                        <li class="fb-button sb-share">                                              
                                                <!--[if IE]>
                                                <iframe class="fb-like" src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>%2F&amp;layout=button_count&amp;show_faces=false&amp;width=300&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21&amp;locale=en_US" scrolling="no" frameborder="0" style="border-style:none; overflow:hidden; width:45px; height:21px;" allowTransparency="true">
                                                </iframe>
                                                <![endif]-->
                                                <!--[if !IE]>-->                        
                                                <?php
                                                //test if mobile device
                                                $detect = new Mobile_Detect();                        
                                                if ($detect->isMobile()) {
                                                ?>
                                                <iframe class="fb-like" src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>%2F&amp;layout=button_count&amp;show_faces=false&amp;width=300&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21&amp;locale=en_US" scrolling="no" frameborder="0" style="border-style:none; overflow:hidden; width:45px; height:21px;" allowTransparency="true">
                                                </iframe>                        
                                                <?php
                                                } else {
                                                ?> 
                                                <iframe class="fb-like" src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>%2F&amp;layout=button_count&amp;show_faces=false&amp;width=300&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21&amp;locale=en_US" style="border-style:none; overflow:hidden; width:50px; height:21px;">
                                                </iframe>
                                                <?php
                                                } ?> 
                                                <!--<![endif]-->                                         
                                        </li>
                                        
                                </ul>
                        
                        </div><!-- .album-info-->
                        
                        <?php
                        if ($albuminfo_left){ ?>
                        <div class="album-info"> 
                  		<p> <?php echo do_shortcode($albuminfo_left); ?> </p>
                  	</div>
                        <?php }
                        ?>   
                        
		</div><!-- .album-left-->


		<div class="album-center">
                 
                        
                        <?php

                        if ($detect->isMobile() or (of_get_option('gg_playerdisplay')) and $soundcloud){ ?>
                                <div class="soundcloud-mobile">
                                                <?php echo $soundcloud; ?>
                                </div>
                                <div class="clear"> </div>
                        <?php }
                     
                        
                        elseif ($soundcloud){ ?>
                                <div class="soundcloudplayer-icon">
                                        <a href="#soundcloud" data-rel="prettyPhoto" ></a>
                                        <div  id="soundcloud" class="hidden">
                                                <?php echo $soundcloud; ?>
                                        </div>
                                </div>        
                        <?php }
                        ?> 	



                        <?php
                        
                        /* JW PLAYER 6*/
                        
                        $jwversion = of_get_option('gg_jwversion');
                        
                        if( $jwversion == 'version6' ){
                                if ($detect->isMobile() or (of_get_option('gg_playerdisplay')) and $audioplayer){ ?>
                                        <div class="audioplayer-mobile">
         
                                                <div id='playlist'>JW Player</div>
                                                
                                                        <script type='text/javascript'>
                                                        
                                                        jwplayer('playlist').setup({                                                        
                                                                 
                                                                'playlist':
                                                                [
                                                                        <?php
                                                                        $attachs2 = get_posts(array(
                                                                        'post_type' => 'attachment',
                                                                        'post_parent' => get_the_ID(),
                                                                        'numberposts' => -1,
                                                                        'order' => 'ASC'
                                                                        ));
                                                                        if (!empty($attachs2)) {
                                                                        foreach ($attachs2 as $att2) {
                                                                        if (wp_attachment_is_image($att2->ID)) continue; // don't show attached images
                                                                        echo 
                                                                        "{
                                                                        'file': '" . wp_get_attachment_url($att2->ID) . "',
                                                                        'title': '" . apply_filters('the_title', $att2->post_title) . "'
                                                                        },";
                                                                        }
                                                                        }
                                                                        ?>    
                                                                ],
                
                                                                skin: '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer6/sixPlaylist.xml',
                                                                controlbar: 'top',
                                                                repeat: 'always',
                                                                autostart: 'false',
                                                                listbar: {
                                                                        position: 'bottom',
                                                                        size: 192
                                                                    },
                                                                primary: 'flash',
                                                                width: '420',
                                                                height: '220' 
                                                        });
                                                        
                                                        </script>                                        
                                        </div><!-- .audioplayer-mobile-->

                                <?php }
        
                                elseif ($audioplayer){ ?>
                                        <div class="audioplayer-icon">
                                        
                                                <a href="#jwplayer" data-rel="prettyPhoto"></a>
                                                
                                                <div  id="jwplayer" class="hidden">
                                
                                                <div class="album-artwork-inplayer">                                                
                                                        <?php echo $album_thumb; ?>    
                                                </div>    
        
                                                <div id='playlist'>JW Player</div>
                                                
                                                        <script type='text/javascript'>
                                                        
                                                        jwplayer('playlist').setup({
                                                                
                                                                'playlist':
                                                                [
                                                                        <?php
                                                                        $attachs2 = get_posts(array(
                                                                        'post_type' => 'attachment',
                                                                        'post_parent' => get_the_ID(),
                                                                        'numberposts' => -1,
                                                                        'order' => 'ASC'
                                                                        ));
                                                                        if (!empty($attachs2)) {
                                                                        foreach ($attachs2 as $att2) {
                                                                        if (wp_attachment_is_image($att2->ID)) continue; // don't show attached images
                                                                        echo 
                                                                        "{
                                                                        'file': '" . wp_get_attachment_url($att2->ID) . "',
                                                                        'title': '" . apply_filters('the_title', $att2->post_title) . "'
                                                                        },";
                                                                        }
                                                                        }
                                                                        ?>    
                                                                ],
                
                                                                skin: '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer6/sixPlaylist.xml',
                                                                controlbar: 'top',
                                                                autostart: 'true',
                                                                repeat: 'always',
                                                                listbar: {
                                                                        position: 'bottom',
                                                                        size: 192
                                                                    },
                                                                primary: 'flash',
                                                                width: '310',
                                                                height: '220' 
                                                        });
                                                        
                                                        </script>
                                                </div><!-- #playlist -->
                                        </div><!-- .audioplayer-icon-->       
                                <?php }                            
                        }                        



                        /* JW PLAYER 5*/

                        if( $jwversion == 'version5' ){                              
                        
                        if ($detect->isMobile() or (of_get_option('gg_playerdisplay')) and $audioplayer){ ?>
                                <div class="audioplayer-mobile">
 
                                        <div id='playlist'>JW Player</div>
                                        
                                                <script type='text/javascript'>
                                                
                                                jwplayer('playlist').setup({
                                                        
                                                        'flashplayer': '<?php bloginfo('template_directory'); ?>/jwplayer/player.swf',  
                                                        'playlist':
                                                        [
                                                                <?php
                                                                $attachs2 = get_posts(array(
                                                                'post_type' => 'attachment',
                                                                'post_parent' => get_the_ID(),
                                                                'numberposts' => -1,
                                                                'order' => 'ASC'
                                                                ));
                                                                if (!empty($attachs2)) {
                                                                foreach ($attachs2 as $att2) {
                                                                if (wp_attachment_is_image($att2->ID)) continue; // don't show attached images
                                                                echo 
                                                                "{
                                                                'file': '" . wp_get_attachment_url($att2->ID) . "',
                                                                'title': '" . apply_filters('the_title', $att2->post_title) . "'
                                                                },";
                                                                }
                                                                }
                                                                ?>    
                                                        ],
        
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer5/soundboard_playlist.zip',
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer5/soundboard_playlist/soundboard.xml',
                                                        'controlbar': 'top',
                                                        'repeat': 'always',
                                                        'playlist.position': 'bottom',
                                                        'playlist.size': '192',
                                                        'width': '420',
                                                        'height': '220' 
                                                });
                                                
                                                </script>                                        
                                </div><!-- .audioplayer-mobile-->
                        <?php }

                        elseif ($audioplayer){ ?>
                                <div class="audioplayer-icon">
                                
                                        <a href="#jwplayer" data-rel="prettyPhoto"></a>
                                        
                                        <div  id="jwplayer" class="hidden">
                  	
                                        <div class="album-artwork-inplayer">                                                
                                                <?php echo $album_thumb; ?>    
                                        </div>    

                                        <div id='playlist'>JW Player</div>
                                        
                                                <script type='text/javascript'>
                                                
                                                jwplayer('playlist').setup({
                                                        
                                                        'flashplayer': '<?php bloginfo('template_directory'); ?>/jwplayer/player.swf',  
                                                        'playlist':
                                                        [
                                                                <?php
                                                                $attachs2 = get_posts(array(
                                                                'post_type' => 'attachment',
                                                                'post_parent' => get_the_ID(),
                                                                'numberposts' => -1,
                                                                'order' => 'ASC'
                                                                ));
                                                                if (!empty($attachs2)) {
                                                                foreach ($attachs2 as $att2) {
                                                                if (wp_attachment_is_image($att2->ID)) continue; // don't show attached images
                                                                echo 
                                                                "{
                                                                'file': '" . wp_get_attachment_url($att2->ID) . "',
                                                                'title': '" . apply_filters('the_title', $att2->post_title) . "'
                                                                },";
                                                                }
                                                                }
                                                                ?>    
                                                        ],
        
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer5/soundboard_playlist.zip',
                                                        'controlbar': 'top',
                                                        'autostart': 'true',
                                                        'repeat': 'always',
                                                        'playlist.position': 'bottom',
                                                        'playlist.size': '192',
                                                        'width': '310',
                                                        'height': '220' 
                                                });
                                                
                                                </script>
                                        </div><!-- #playlist -->
                                </div><!-- .audioplayer-icon-->       
                        <?php }
                              
                        } 

                        ?>                   
     
                        <div class="album-tracks">
                                <ul>
                                        <?php                        
                                        $songs = get_post_meta( get_the_ID( ), 'gxg_song', true );                        
                                        foreach ( $songs as $song ) {                                                        
                                        echo '<li>' . $song . '</li>';                                              
                                        } ?>                   
                                </ul>       
                  	</div><!-- #album-tracks -->

                  	<div class="clear"> </div>
                  	
                  	      <?php
	                        if ($albuminfo_center){ ?>
	                        <div class="album-info-center"> 
	                  		<p> <?php echo do_shortcode($albuminfo_center); ?> </p>
	                  	</div>
	                        <?php }
	                        ?>  

                  	<!-- Display the Post's Content in a div box. -->
                  	<div class="single_entry">
                           	<?php the_content(); ?>
                  	</div>
                  	
		</div><!-- .album-center-->
		
                <div class="album-right">
                
                        <?php
                        if ($albuminfo){ ?>
                        <div class="album-info"> 
                  		<p> <?php echo do_shortcode($albuminfo); ?> </p>
                  	</div>
                        <?php }
                        ?>          
                        
                </div><!-- .album-right-->

                <div class="clear"> </div>


                <!-- Stop The Loop (but note the "else:" - see next line). -->
                <?php endwhile; else: ?>

                <!-- what if there are no Posts? -->
                <p><?php _e('Sorry, no posts matched your criteria.', 'gxg_textdomain') ?></p>

                <!-- REALLY stop The Loop. -->
                <?php endif; ?> 
                
                <?php if (!of_get_option('gg_commentremove')) { ?> 
               <div class="comment-topborder"></div>
               
               <div id="comments" >
                       <?php comments_template(); ?>
               </div><!-- #comments-->
               <?php } ?>                      

        </div><!-- #content-->       

        <div class="clear">
        </div><!-- .clear-->

<?php get_footer(); ?>