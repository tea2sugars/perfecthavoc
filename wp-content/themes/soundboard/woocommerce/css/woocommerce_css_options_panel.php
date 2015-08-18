<?php 
header("Content-type: text/css");

require_once( '../../../../../wp-load.php' );


/* WOOCOMMERCE */
$color = of_get_option('gg_link_color');
if ( of_get_option('gg_link_color') ) {
?>
a.button, 
button.button, 
input.button, 
#respond input#submit, 
#content input.button,
#content div.product .woocommerce_tabs ul.tabs li,
#content div.product .woocommerce-tabs ul.tabs li,
.woocommerce #searchsubmit,
.woocommerce .nav-next a,
.woocommerce .nav-previous a,
.widget_price_filter .ui-slider .ui-slider-range
        {
        background-color: <?php echo $color; ?>;
        }
        
ul.products li.product .price,
div.product span.price,
div.product p.price,
#content div.product span.price,
#content div.product p.price
        {
        color: <?php echo $color; ?>;
        }        
<?php
}

$colorpicker = of_get_option('gg_link_colorpicker');
if ( of_get_option('gg_link_colorpicker') ) {
?>
a.button, 
button.button, 
input.button, 
#respond input#submit, 
#content input.button,
#content div.product .woocommerce_tabs ul.tabs li,
#content div.product .woocommerce-tabs ul.tabs li,
.woocommerce #searchsubmit,
.woocommerce .nav-next a,
.woocommerce .nav-previous a,
.widget_price_filter .ui-slider .ui-slider-range
        {
        background-color: <?php echo $colorpicker; ?>;
        }
        
ul.products li.product .price,
div.product span.price,
div.product p.price,
#content div.product span.price,
#content div.product p.price
        {
        color: <?php echo $colorpicker; ?>;
        } 
<?php
}

$font = of_get_option('gg_font');
$font2 = of_get_option('gg_font2');

if ( of_get_option('gg_font2') ) {
?>
a.button, 
button.button, 
input.button,
.woocommerce #searchsubmit,
#respond input#submit, 
#content input.button
         {
         font-family: "<?php echo $font2; ?>" , "Helvetica Neue", Arial, "sans-serif";
         }
<?php
} elseif ( of_get_option('gg_font') ) {
?>
a.button, 
button.button, 
input.button,
.woocommerce #searchsubmit,
#respond input#submit, 
#content input.button
         {
         font-family: "<?php echo $font; ?>" , "Helvetica Neue", Arial, "sans-serif";
         }
<?php
}

$trans = of_get_option('gg_trans');
if ( of_get_option('gg_trans') ) {
?>
a.button, 
button.button, 
input.button,
.woocommerce #searchsubmit,
#respond input#submit, 
#content input.button,
.woocommerce h1.page-title
         {
         text-transform: <?php echo $trans; ?>;
         }         
<?php
}

?>