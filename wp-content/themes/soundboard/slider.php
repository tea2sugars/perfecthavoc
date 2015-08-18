<div id="slider" class="nivoSlider">
        
        <?php
        
                if ( of_get_option('gg_slider') && of_get_option('gg_sliderimages') ) { 
                        
                        global $wpdb, $post;
                        
                        $slider = of_get_option('gg_sliderimages');

                        $images = rwmb_meta( 'gxg_slider_images', 'type=image&size=full', $slider );

                        foreach ( $images as $image ) {
   
                                $caption = $image['slidercaption'];
                                $caption = htmlspecialchars($caption, ENT_QUOTES);
                                
                                $cf = $image['sliderurl'];
                                                               
                                if ($cf) {    
                                echo "<a href='$cf'><img src='{$image['url']}' alt='' title='{$caption}' /></a>";
                                } else {    
                                echo "<img src='{$image['url']}' alt='' title='{$caption}' />";
                                }
                        } 

                }        
        ?>
</div><!-- .slider-->
<?php $sliderspeed = of_get_option('gg_sliderspeed'); ?>
<div id="sliderspeed" value="<?php echo $sliderspeed; ?>"></div>