<?php get_header(); ?>

                <h1 class="pagetitle"> <?php the_title(); ?> </h1>
                
        <div id="content" class="grid_9">

                <div class="clear">
                </div><!-- .clear-->                 
                
                <ul class="video">
                <?php  
              
                        $jwversion = of_get_option('gg_jwversion');
                        
                        $video_title = $post->post_title;
                        $video = get_post_meta($post->ID, 'gxg_videoembedcode', true);
                        $video2 = get_post_meta($post->ID, 'gxg_videofile', true);
                        $video_id = get_the_ID();
                        
                        //video thumb
                        $thumb_path = wp_get_attachment_image_src(get_post_thumbnail_id(), 'video');
                        $video_thumb = get_the_post_thumbnail($post->ID, 'video', array('title' => '')) ;
                        $video_ID = str_replace("http://youtu.be/", "", $video);
                        if (get_the_post_thumbnail($post->ID, 'video', array('title' => '')) == false and strpos($video,'youtu') !== false){
                                $video_thumb= '<img width="220" height="140" src="http://img.youtube.com/vi/' . $video_ID . '/0.jpg" class="attachment-video wp-post-image" alt="b" title="" />';
                        }  
                        
                        //open videos on mobile device in browser and not in youtube app
                        $embed = $video;
                        $repl = "http://youtu.be/";
                        $emb = "http://www.youtube.com/embed/";                        
                        $mobile_embed = str_replace($repl, $emb, $embed);

                        $embed2 = $video;
                        $repl2 = "http://vimeo.com/";
                        $emb2 = "http://player.vimeo.com/video/";                        
                        $mobile_embed2 = str_replace($repl2, $emb2, $embed2);                                            
                    
                        //test if mobile device
                        $detect = new Mobile_Detect();
                        
                        if ($detect->isMobile() and $video and strpos($video,'youtu') !== false) {
                        ?>  
                                <li>   
                                        <a href="<?php echo $mobile_embed; ?>" title="">                                              
                                                
                                                <div class="video_item mosaic-block-v bar">                                	
                                                        <div class="video-icon">
                                                                        <div class="details mosaic-overlay vid-size">
                                                                                <?php echo $video_title; ?>
                                                                        </div>
                                                                                                        
                                                        </div>                                                
                                                        <div class="mosaic-backdrop">                                                
                                                                <?php echo $video_thumb; ?>  
                                                        </div>
                                                </div><!-- .video_item-->
                                        </a>                                      
                                </li>
                                
                        <?php
                        }        

                        elseif ($detect->isMobile() and $video and strpos($video,'vimeo') !== false) {
                        ?>  
                                <li>   
                                        <a href="<?php echo $mobile_embed2; ?>" title="">                                              
                                                
                                                <div class="video_item mosaic-block-v bar">                                	
                                                        <div class="video-icon">
                                                                        <div class="details mosaic-overlay vid-size">
                                                                                <?php echo $video_title; ?>
                                                                        </div>
                                                                                                        
                                                        </div>                                                
                                                        <div class="mosaic-backdrop">                                                
                                                                <?php echo $video_thumb; ?>  
                                                        </div>
                                                </div><!-- .video_item-->
                                        </a>                                      
                                </li>
                                
                        <?php
                        }
                        
                        
                        
                        elseif ($detect->isMobile() and $video2 and ( $jwversion == 'version6' ) ) {
                        ?>                        
                                <li>
                                        <div class="video_item"> 
                                                <div id='vb_<?php echo $video_id; ?>'> Loading Video... </div>
                                        </div>
                                        
                                        <script type='text/javascript'>                                
                                                jwplayer('vb_<?php echo $video_id; ?>').setup({                                                       
                                                        'file': '<?php echo $video2; ?>',
                                                        'id': 'id_<?php echo $video_id; ?>',
                                                        'autostart': 'false',
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer6/sixVideo.xml',
                                                        'image': '<?php print $thumb_path[0]; ?>',
                                                        'width': '220',
                                                        'height': '140'
                                                });
                                        </script>             
                                        
                                </li>
                                
                        <?php
                        }
                        
                                                     
                        elseif ($detect->isMobile() and $video2) {
                        ?>                        
                                <li>
                                        <div class="video_item"> 
                                                <div id='vb_<?php echo $video_id; ?>'> Loading Video... </div>
                                        </div>
                                        
                                        <script type='text/javascript'>                                
                                                jwplayer('vb_<?php echo $video_id; ?>').setup({
                                                        'flashplayer': '<?php bloginfo('template_directory'); ?>/jwplayer/player.swf',
                                                        'file': '<?php echo $video2; ?>',
                                                        'id': 'id_<?php echo $video_id; ?>',
                                                        'autostart': 'false',
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer5/soundboard.zip',
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer5/soundboard/soundboard.xml',
                                                        'image': '<?php print $thumb_path[0]; ?>',
                                                        'controlbar': 'none',
                                                        'width': '220',
                                                        'height': '140'
                                                });
                                        </script>             
                                        
                                </li>
                                
                        <?php
                        }
                        
                        elseif ($video){
                        ?>                        
                                <li>
                                        <a href="<?php echo $video; ?>" data-rel="prettyPhoto" target="_blank" title="">                                              
                                                
                                                <div class="video_item mosaic-block-v bar">                                	
                                                        <div class="video-icon">
                                                                        <div class="details mosaic-overlay vid-size">
                                                                                <?php echo $video_title; ?>
                                                                        </div>
                                                                                                        
                                                        </div>                                                
                                                        <div class="mosaic-backdrop">                                                
                                                                <?php echo $video_thumb; ?>  
                                                        </div>
                                                </div><!-- .video_item-->
                                        </a>
                                </li>
                                
                        <?php
                        }
                        
                        
                        elseif ($jwversion == 'version6') {
                        ?>                        
                                <li>
                                        <a href="#<?php echo $video_id; ?>" data-rel="prettyPhoto" target="_blank"  title="" >
                                                       
                                                <div class="video_item mosaic-block-v bar">                                             
                                                        <div class="video-icon">
                                                                <div class="details mosaic-overlay vid-size">
                                                                        <?php echo $video_title; ?>
                                                                </div>                                                                
                                                                 
                                                        </div>   
                                                        <div class="mosaic-backdrop">                                                
                                                                <?php echo $video_thumb; ?>     
                                                        </div>                                                       
                                                        
                                                        <div id="<?php echo $video_id; ?>" class="hidden">
                                                                <div id='vb_<?php echo $video_id; ?>'> Loading Video... </div>
                                                        </div>
                                                                
                                                </div><!-- .video_item-->
                                        </a>
        
                                         <script type='text/javascript'>                                
                                                jwplayer('vb_<?php echo $video_id; ?>').setup({                                                        
                                                        file: '<?php echo $video2; ?>',
                                                        id: 'id_<?php echo $video_id; ?>',
                                                        autostart: 'true',
                                                        skin: '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer6/sixVideo.xml',

                                                        primary: 'flash',
                                                        width: '540',
                                                        height: '380'
                                                });
                                        </script>             
                                        
                                </li>                      
                        <?php
                        }

                        
                        else {
                        ?>                        
                                <li>
                                        <a href="#<?php echo $video_id; ?>" data-rel="prettyPhoto" target="_blank"  title="" >
                                                       
                                                <div class="video_item mosaic-block-v bar">                                             
                                                        <div class="video-icon">
                                                                <div class="details mosaic-overlay vid-size">
                                                                        <?php echo $video_title; ?>
                                                                </div>                                                                
                                                                 
                                                        </div>   
                                                        <div class="mosaic-backdrop">                                                
                                                                <?php echo $video_thumb; ?>     
                                                        </div>                                                       
                                                        
                                                        <div id="<?php echo $video_id; ?>" class="hidden">
                                                                <div id='vb_<?php echo $video_id; ?>'> Loading Video... </div>
                                                        </div>
                                                                
                                                </div><!-- .video_item-->
                                        </a>
        
                                        <script type='text/javascript'>                                
                                                jwplayer('vb_<?php echo $video_id; ?>').setup({
                                                        'flashplayer': '<?php bloginfo('template_directory'); ?>/jwplayer/player.swf',
                                                        'file': '<?php echo $video2; ?>',
                                                        'id': 'id_<?php echo $video_id; ?>',
                                                        'autostart': 'true',
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer5/soundboard.zip',
                                                        'skin': '<?php bloginfo('template_directory'); ?>/jwplayer/JWPlayer5/soundboard/soundboard.xml',
                                                        //'image': '<?php print $thumb_path[0]; ?>',
                                                        'controlbar': 'bottom',
                                                        'width': '540',
                                                        'height': '380'
                                                });
                                        </script>             
                                        
                                </li>                      
                        <?php
                        }
       

                ?>
                </ul>
                
      

        </div><!-- #content-->
        
        <div id="sidebar" class="grid_3">
                <?php get_sidebar(); ?>
        </div><!-- #sidebar-->

        <div class="clear">
        </div><!-- .clear-->

<?php get_footer(); ?>