<?php

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);
 
function my_theme_wrapper_start() {
  echo '<div id="content" class="grid_9">';
}
 
function my_theme_wrapper_end() {
  echo '</div><!-- #content-->';
}

/* number or products per row *************************************************/
add_filter('loop_shop_columns', 'loop_columns');
if (!function_exists('loop_columns')) {
function loop_columns() {
return 4;
}
}

/* Change number or products per page *****************************************/
add_filter('loop_shop_per_page', create_function('$cols', 'return 8;'));


/* number or related products per row *****************************************/
if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {    
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
        function custom_output_related_products() {
        // Display 4 products in 4 columns
        woocommerce_related_products( 4, 4 );
        }
        add_action( 'woocommerce_after_single_product_summary', 'custom_output_related_products', 20 );                
} 





/* Define image sizes *********************************************************/
global $pagenow;
if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) add_action('init', 'yourtheme_woocommerce_image_dimensions', 1);

function yourtheme_woocommerce_image_dimensions() {
// Image sizes
update_option( 'woocommerce_thumbnail_image_width', '160' ); // Image gallery thumbs
update_option( 'woocommerce_single_image_width', '300' ); // Featured product image
}


?>