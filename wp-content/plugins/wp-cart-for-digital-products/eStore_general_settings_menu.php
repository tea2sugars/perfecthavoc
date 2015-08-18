<?php
function show_wp_digi_cart_settings_page ()
{	
	global $eStore_debug_manager;
	$wp_eStore_config = WP_eStore_Config::getInstance();
	if(isset($_POST['reset_logfiles'])) {
	// Reset the debug log files...
		$eStore_debug_manager->reset_logfiles();
        	echo '<div id="message" class="updated fade"><p><strong>Debug log files have been reset!</strong></p></div>';
	}
    if (isset($_POST['info_update']))
    {
    	$error_msg = "";
        update_option('eStore_cart_language', (string)$_POST["eStore_cart_language"]);
        
        if(trim($_POST["cart_payment_currency"]) == "CAN"){$_POST["cart_payment_currency"] = "CAD";} 
        update_option('cart_payment_currency', trim($_POST["cart_payment_currency"]));
        update_option('cart_currency_symbol', (string)$_POST["cart_currency_symbol"]);
        $wp_eStore_config->setValue('cart_currency_symbol', (string)$_POST["cart_currency_symbol"]);
        
        update_option('eStore_variation_add_symbol', trim(stripslashes($_POST["eStore_variation_add_symbol"])));
        //update_option('eStore_paypal_profile_shipping', ($_POST['eStore_paypal_profile_shipping']!='') ? 'checked="checked"':'' );        
        update_option('eStore_products_per_page', (string)$_POST["eStore_products_per_page"]);
        //update_option('cart_paypal_email', (string)$_POST["cart_paypal_email"]);
        update_option('addToCartButtonName', trim($_POST["addToCartButtonName"]));
        update_option('soldOutImage', trim($_POST["soldOutImage"]));
        update_option('wp_eStore_widget_title', stripslashes((string)$_POST["wp_eStore_widget_title"]));
        update_option('wp_cart_title', stripslashes((string)$_POST["wp_cart_title"]));
        update_option('wp_cart_empty_text', (string)$_POST["wp_cart_empty_text"]);
        update_option('cart_return_from_paypal_url', trim($_POST["cart_return_from_paypal_url"]));
        update_option('cart_cancel_from_paypal_url', trim($_POST["cart_cancel_from_paypal_url"]));
        update_option('eStore_products_page_url', trim($_POST["eStore_products_page_url"]));
        update_option('eStore_display_continue_shopping', isset($_POST["eStore_display_continue_shopping"])?'checked="checked"':'');
        $wp_eStore_config->setValue('eStore_do_not_show_qty_in_cart', isset($_POST["eStore_do_not_show_qty_in_cart"])?'checked="checked"':'');        
        
        update_option('eStore_checkout_page_url', trim($_POST["eStore_checkout_page_url"]));
        update_option('eStore_auto_checkout_redirection', isset($_POST["eStore_auto_checkout_redirection"])?'checked="checked"':'');
        update_option('eStore_auto_cart_anchor', isset($_POST["eStore_auto_cart_anchor"])?'checked="checked"':'');
        update_option('eStore_shopping_cart_image_hide', isset($_POST["eStore_shopping_cart_image_hide"])?'checked="checked"':'');
        update_option('eStore_show_t_c', isset($_POST["eStore_show_t_c"])?'checked="checked"':'');
        update_option('eStore_show_t_c_for_buy_now', isset($_POST["eStore_show_t_c_for_buy_now"])?'checked="checked"':'');
        update_option('eStore_show_t_c_for_squeeze_form', isset($_POST["eStore_show_t_c_for_squeeze_form"])?'checked="checked"':'');
        update_option('eStore_t_c_url', trim($_POST["eStore_t_c_url"])); 
        update_option('eStore_show_compact_cart', isset($_POST["eStore_show_compact_cart"])?'checked="checked"':''); 
        update_option('eStore_enable_fancy_redirection_on_checkout', isset($_POST["eStore_enable_fancy_redirection_on_checkout"])?'checked="checked"':'');
        $wp_eStore_config->setValue('eStore_enable_fancy_redirection_on_checkout', isset($_POST["eStore_enable_fancy_redirection_on_checkout"])?'checked="checked"':'');
        $wp_eStore_config->setValue('eStore_enable_save_retrieve_cart', isset($_POST["eStore_enable_save_retrieve_cart"])?'1':'');
        $wp_eStore_config->setValue('eStore_enable_checkout_amt_limit', isset($_POST["eStore_enable_checkout_amt_limit"])?'1':'');
        
        $min_cart_co_limit_amt = trim($_POST["eStore_checkout_amt_limit_minimum"]);
        if(!empty($min_cart_co_limit_amt) && !is_numeric($min_cart_co_limit_amt)){
        	$error_msg .= '<br />The Minimum Checkout Amount limitation value must be a numeric number! Please check the value of this field again.';
        }
        $wp_eStore_config->setValue('eStore_checkout_amt_limit_minimum', $min_cart_co_limit_amt); 
        $max_cart_co_limit_amt = trim($_POST["eStore_checkout_amt_limit_maximum"]);
        if(!empty($max_cart_co_limit_amt) && !is_numeric($max_cart_co_limit_amt)){
        	$error_msg .= '<br />The Maximum Checkout Amount limitation value must be a numeric number! Please check the value of this field again.';
        }
        $wp_eStore_config->setValue('eStore_checkout_amt_limit_maximum', $max_cart_co_limit_amt);        
        
        update_option('eStore_enable_lightbox_effect', isset($_POST["eStore_enable_lightbox_effect"])?'checked="checked"':'');
        update_option('eStore_enable_smart_thumb', isset($_POST["eStore_enable_smart_thumb"])?'1':'');
                                           
        update_option('eStore_base_shipping', trim($_POST["eStore_base_shipping"]));
        update_option('eStore_shipping_variation', trim($_POST["eStore_shipping_variation"]));
        update_option('eStore_always_display_shipping_variation', isset($_POST["eStore_always_display_shipping_variation"])?'1':'');        
        $wp_eStore_config->setValue('eStore_enable_store_pickup', isset($_POST["eStore_enable_store_pickup"])?'1':'');
        update_option('eStore_enable_tax', isset($_POST["eStore_enable_tax"])?'checked="checked"':'');
        update_option('eStore_global_tax_rate', (string)$_POST["eStore_global_tax_rate"]);
        $wp_eStore_config->setValue('eStore_enable_tax_on_shipping', isset($_POST["eStore_enable_tax_on_shipping"])?'1':'');
        $wp_eStore_config->setValue('eStore_show_tax_inclusive_price', isset($_POST["eStore_show_tax_inclusive_price"])?'1':'');
        
        update_option('eStore_secondary_currency_code', trim($_POST["eStore_secondary_currency_code"]));
        update_option('eStore_secondary_currency_symbol', (string)$_POST["eStore_secondary_currency_symbol"]);
        update_option('eStore_secondary_currency_conversion_rate', (string)$_POST["eStore_secondary_currency_conversion_rate"]);
        
        update_option('eStore_random_code', trim(stripslashes($_POST["eStore_random_code"])));
        update_option('eStore_download_url_life', trim($_POST["eStore_download_url_life"]));
        update_option('eStore_download_url_limit_count', trim($_POST["eStore_download_url_limit_count"]));
        //update_option('eStore_download_enable_ip_address_lock', ($_POST['eStore_download_enable_ip_address_lock']=='1') ? '1':'' );
        update_option('eStore_download_script', trim($_POST["eStore_validation_sc_url"]));
        $wp_eStore_config->setValue('eStore_auto_shorten_dl_links', isset($_POST["eStore_auto_shorten_dl_links"])?'1':'');
        $wp_eStore_config->setValue('eStore_product_price_must_be_zero_for_free_download', isset($_POST["eStore_product_price_must_be_zero_for_free_download"])?'1':''); 
        
        update_option('eStore_auto_product_delivery', isset($_POST["eStore_auto_product_delivery"])?'checked="checked"':'');
        update_option('eStore_display_tx_result', isset($_POST["eStore_display_tx_result"])?'checked="checked"':'');       
        update_option('eStore_strict_email_check', isset($_POST["eStore_strict_email_check"])?'checked="checked"':'');
        update_option('eStore_auto_customer_removal', isset($_POST["eStore_auto_customer_removal"])?'checked="checked"':'');             
        
		// Update debug manager related settings...
		eStore_dbgmgradm::settings_menu_post($_POST['eStore_cart_enable_debug'], $_POST['eStore_cart_enable_sandbox']);

		$wp_eStore_config->saveConfig();
        wp_eStore_clear_cache();
        
        if(!empty($error_msg)){
        	echo '<div id="message" class="error">';
        	echo $error_msg;
        	echo '<br /><br /></div>';
        }
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>Options Updated!</strong></p></div>';
    }
    $cart_language = get_option('eStore_cart_language');
    if (empty($cart_language)) $cart_language = 'eng.php';

    $defaultCurrency = get_option('cart_payment_currency');
    if (empty($defaultCurrency)) $defaultCurrency = 'USD';

    $eStore_variation_add_symbol = get_option('eStore_variation_add_symbol');
    if (empty($eStore_variation_add_symbol)) $eStore_variation_add_symbol = '+';

    $defaultSymbol = get_option('cart_currency_symbol');
    if (empty($defaultSymbol)) $defaultSymbol = '$';
    
	$eStore_products_per_page = get_option('eStore_products_per_page');
	if (empty($eStore_products_per_page)) $eStore_products_per_page = '20';
	
    //$notify_url =  get_option('eStore_notify_url');
    $return_url =  get_option('cart_return_from_paypal_url');
    $cancel_url =  get_option('cart_cancel_from_paypal_url');
    
    $eStore_products_page_url =  get_option('eStore_products_page_url');
    $eStore_checkout_page_url = get_option('eStore_checkout_page_url');

    $addcart = get_option('addToCartButtonName');
    if (empty($addcart)) $addcart = 'Add to Cart';

    $soldOut = get_option('soldOutImage');
    if (empty($soldOut)) $soldOut = WP_ESTORE_URL.'/images/sold_out.png';

	$title = get_option('wp_cart_title');
	//if (empty($title)) $title = 'Your Shopping Cart';
	$widget_title = get_option('wp_eStore_widget_title');

	$emptyCartText = get_option('wp_cart_empty_text');

    if (get_option('eStore_display_continue_shopping'))
        $eStore_display_continue_shopping = 'checked="checked"';
    else
        $eStore_display_continue_shopping = '';

	if ($wp_eStore_config->getValue('eStore_do_not_show_qty_in_cart'))
		$eStore_do_not_show_qty_in_cart = 'checked="checked"';
	else
		$eStore_do_not_show_qty_in_cart = '';
        
    if (get_option('eStore_auto_checkout_redirection'))
        $eStore_auto_checkout_redirection = 'checked="checked"';
    else
        $eStore_auto_checkout_redirection = '';
        
    if (get_option('eStore_auto_cart_anchor'))
        $eStore_cart_anchor = 'checked="checked"';
    else
        $eStore_cart_anchor = '';

    if (get_option('eStore_shopping_cart_image_hide'))
        $eStore_cart_image_hide = 'checked="checked"';
    else
        $eStore_cart_image_hide = '';
        
    if (get_option('eStore_show_t_c'))
        $eStore_show_t_c = 'checked="checked"';
    else
        $eStore_show_t_c = '';   
        
    if (get_option('eStore_show_t_c_for_buy_now'))
        $eStore_show_t_c_for_buy_now = 'checked="checked"';
    else
        $eStore_show_t_c_for_buy_now = '';          
        
    if (get_option('eStore_show_t_c_for_squeeze_form'))
        $eStore_show_t_c_for_squeeze_form = 'checked="checked"';
    else
        $eStore_show_t_c_for_squeeze_form = '';   
        
    $eStore_t_c_url = get_option('eStore_t_c_url');    

    if (get_option('eStore_show_compact_cart'))
        $eStore_show_compact_cart = 'checked="checked"';
    else
        $eStore_show_compact_cart = '';

    if (get_option('eStore_enable_fancy_redirection_on_checkout'))
        $eStore_enable_fancy_redirection_on_checkout = 'checked="checked"';
    else
        $eStore_enable_fancy_redirection_on_checkout = '';

    if ($wp_eStore_config->getValue('eStore_enable_save_retrieve_cart')=='1')
        $eStore_enable_save_retrieve_cart = 'checked="checked"';
    else
        $eStore_enable_save_retrieve_cart = '';   
                
    if ($wp_eStore_config->getValue('eStore_enable_checkout_amt_limit')=='1')
        $eStore_enable_checkout_amt_limit = 'checked="checked"';
    else
        $eStore_enable_checkout_amt_limit = '';            
        
    if (get_option('eStore_enable_lightbox_effect'))
        $eStore_enable_lightbox_effect = 'checked="checked"';
    else
        $eStore_enable_lightbox_effect = '';
        
    if (get_option('eStore_enable_smart_thumb'))
        $eStore_enable_smart_thumb = 'checked="checked"';
    else
        $eStore_enable_smart_thumb = '';
        
	$baseShipping = get_option('eStore_base_shipping');
	if (empty($baseShipping)) $baseShipping = '0';
	
	$shippingVar = get_option('eStore_shipping_variation');
	if(get_option('eStore_always_display_shipping_variation')!='') 
		$always_display_shipping_var = ' checked="checked"';
	else
		$always_display_shipping_var = '';
	
    $enable_store_pickup = $wp_eStore_config->getValue('eStore_enable_store_pickup');
    if($enable_store_pickup){ 
        $enable_store_pickup = ' checked="checked"';
    }
    else{
        $enable_store_pickup = '';    
    }
    if (get_option('eStore_enable_tax'))
        $eStore_enable_tax = 'checked="checked"';
    else
        $eStore_enable_tax = '';	
        
    if ($wp_eStore_config->getValue('eStore_enable_tax_on_shipping')=='1')
        $eStore_enable_tax_on_shipping = 'checked="checked"';
    else
        $eStore_enable_tax_on_shipping = '';
        
    if ($wp_eStore_config->getValue('eStore_show_tax_inclusive_price')=='1')
        $eStore_show_tax_inclusive_price = 'checked="checked"';
    else
        $eStore_show_tax_inclusive_price = '';
        
    if (get_option('eStore_auto_product_delivery'))
        $eStore_auto_delivery = 'checked="checked"';
    else
        $eStore_auto_delivery = '';

    $eStore_random_code = get_option('eStore_random_code');
    if (empty($eStore_random_code)) $eStore_random_code = 'AZ#Ui$335UBSD)AminOc32j90';

    $eStore_download_url_limit_count = get_option('eStore_download_url_limit_count');
    //if (empty($eStore_download_url_limit_count)) $eStore_download_url_limit_count = '3';
          
    $eStore_download_url_life = get_option('eStore_download_url_life');
    if (empty($eStore_download_url_life)) $eStore_download_url_life = '24';
    
    $eStore_download_script = get_option('eStore_download_script');
    if (empty($eStore_download_script)) 
    {
    	$eStore_download_script = WP_ESTORE_URL."/";
    	update_option('eStore_download_script', $eStore_download_script);
    }

    if ($wp_eStore_config->getValue('eStore_auto_shorten_dl_links')=='1')
        $eStore_auto_shorten_dl_links = 'checked="checked"';
    else
        $eStore_auto_shorten_dl_links = '';    
            
    if ($wp_eStore_config->getValue('eStore_product_price_must_be_zero_for_free_download')=='1')
        $eStore_product_price_must_be_zero_for_free_download = 'checked="checked"';
    else
        $eStore_product_price_must_be_zero_for_free_download = '';    

    if (get_option('eStore_strict_email_check'))
        $eStore_strict_email_check = 'checked="checked"';
    else
        $eStore_strict_email_check = '';

    if (get_option('eStore_auto_customer_removal'))
        $eStore_auto_customer_removal = 'checked="checked"';
    else
        $eStore_auto_customer_removal = '';

    if (get_option('eStore_display_tx_result'))
        $eStore_display_tx_result = 'checked="checked"';
    else
        $eStore_display_tx_result = '';

	?> 	

	<div class="eStore_grey_box">
 	<p>For information, updates and detailed documentation, please visit the <a href="http://www.tipsandtricks-hq.com/ecommerce/wp-estore-documentation" target="_blank">WP eStore Documentation</a> or
    the main plugin page <a href="http://www.tipsandtricks-hq.com/?p=1059" target="_blank">WP eStore</a></p>
    </div>

	<div class="postbox">
	<h3><label for="title">Quick Usage Guide</label></h3>
	<div class="inside">

	<p><strong>1.</strong> WP eStore has a lot of setting options but don't get intimidated by it. Most of the default settings are good to get you started (keep things simple at first). Remember to watch the video tutorials from our documentation.</p>
	<p><strong>2.</strong> First add products to the database through the 'Add/Edit Products' interface. Products can be modified through the 'Manage Products' interface.</p>
    <p><strong>3.</strong> To add an 'Add to Cart' button simply add the shortcode <strong>[wp_eStore_add_to_cart id=PRODUCT-ID]</strong> on your landing page of a product. Replace PRODUCT-ID with the actual product id (example: [wp_eStore_add_to_cart id=1] ). Product IDs for all your products can be found in the 'Manage Products' section</p>
	<p><strong>4.</strong> To add the shopping cart to a post or page (example: a checkout page) simply add the shortcode <strong>[wp_eStore_cart]</strong> to the post or page. You can also use the shortcode in a text widget to add the shopping cart to the sidebar.</p>
	<p>Check out the <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=460" target="_blank">shortcodes and function reference page</a> for a full list of usable shortcodes.</p>
    <p>Like the plugin? Give us a <a href="http://www.tipsandtricks-hq.com/?p=1059#gfts_share" target="_blank">thumbs up here</a> by clicking on a share button.</p>
	
    </div></div>
    
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

 	<?php
	// Compute the $wp_home_dir configuration hint.
	// -- The Assurer
	$wp_home_dir = '';
	$current_eStore_script_filepath_parts = explode('/', $_SERVER['PHP_SELF']);
	while(($current_eStore_script_filepath_parts[0] <> 'wp-admin') AND (count($current_eStore_script_filepath_parts) > 1)) {
		$wp_home_dir .= '/'.array_shift($current_eStore_script_filepath_parts);
	}
	$wp_home_dir = trim($wp_home_dir, '/');
 	
echo '
	<div class="postbox">
	<h3><label for="title">General eStore Settings</label></h3>
	<div class="inside">';

echo '
<table class="form-table">';

?>
<tr valign="top">
<th scope="row">Language</th>
<td>
<select name="eStore_cart_language">
<option value="eng.php" <?php if($cart_language=="eng.php")echo 'selected="selected"';?>><?php echo "English" ?></option>
<option value="i18n.php" <?php if($cart_language=="i18n.php")echo 'selected="selected"';?>><?php echo "i18n" ?></option>
<option value="ita.php" <?php if($cart_language=="ita.php")echo 'selected="selected"';?>><?php echo "Italian" ?></option>
<option value="spa.php" <?php if($cart_language=="spa.php")echo 'selected="selected"';?>><?php echo "Spanish" ?></option>
<option value="cat.php" <?php if($cart_language=="cat.php")echo 'selected="selected"';?>><?php echo "Catalan" ?></option>
<option value="ger.php" <?php if($cart_language=="ger.php")echo 'selected="selected"';?>><?php echo "German" ?></option>
<option value="nld.php" <?php if($cart_language=="nld.php")echo 'selected="selected"';?>><?php echo "Dutch" ?></option>
<option value="fr.php" <?php if($cart_language=="fr.php")echo 'selected="selected"';?>><?php echo "French" ?></option>
<option value="heb.php" <?php if($cart_language=="heb.php")echo 'selected="selected"';?>><?php echo "Hebrew" ?></option>
<option value="swe.php" <?php if($cart_language=="swe.php")echo 'selected="selected"';?>><?php echo "Swedish" ?></option>
<option value="pl.php" <?php if($cart_language=="pl.php")echo 'selected="selected"';?>><?php echo "Polish" ?></option>
</select>
<p class="description">Select a language that you want your shopping cart to be displayed in (Contact us for other language options).</p>
</td></tr>
<?php

echo '
<tr valign="top">
<th scope="row">Currency Code</th>
<td><input type="text" name="cart_payment_currency" value="'.$defaultCurrency.'" size="4" /><span class="description"> (example: USD, CAD, EUR, GBP, AUD)</span></td>
</tr>

<tr valign="top">
<th scope="row">Currency Symbol</th>
<td><input type="text" name="cart_currency_symbol" value="'.$defaultSymbol.'" size="4" /><span class="description"> (example: $, &#163;, &#8364;)</span>
</td>
</tr>

<tr valign="top">
<th scope="row">Variation Addition Symbol</th>
<td><input type="text" name="eStore_variation_add_symbol" value="'.$eStore_variation_add_symbol.'" size="4" /><span class="description"> (example: +, add) </span>
<br /><p class="description">This only applies to variation control display. By default, the plus (+) sign is used as the add symbol for your variations. You can however customize it to use any character or word for display purpose (for example: you can use the word "add"). 
<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=345" target="_blank">Read More on Variation Control</a></p>
</td>
</tr>

<tr valign="top">
<th scope="row">Terms & Conditions Page URL</th>
<td><input type="text" name="eStore_t_c_url" value="'.$eStore_t_c_url.'" size="70" /><br />
<p class="description">The URL of your Terms and Conditions page if you have one.</p>
</td>
</tr>

<tr valign="top">
<th scope="row">Show Terms & Conditions Checkbox</th>
<td>
<input type="checkbox" name="eStore_show_t_c" value="1" '.$eStore_show_t_c.' /> For Shopping Cart (Add to Cart Type Buttons)
<br /><input type="checkbox" name="eStore_show_t_c_for_buy_now" value="1" '.$eStore_show_t_c_for_buy_now.' /> For Buy Now and Subscription Type Buttons
<br /><input type="checkbox" name="eStore_show_t_c_for_squeeze_form" value="1" '.$eStore_show_t_c_for_squeeze_form.' /> For Free Download Squeeze Forms
<br /><p class="description">If checked the customers will have to agree to the Terms and Conditions before they can make a purchase.</p></td>
</tr>

<tr valign="top">
<th scope="row">Enable Lightbox effect on Images</th>
<td><input type="checkbox" name="eStore_enable_lightbox_effect" value="1" '.$eStore_enable_lightbox_effect.' />
<br /><p class="description">Check this if you want lightbox effect on the product thumbnail images.</p></td>
</tr>

<tr valign="top">
<th scope="row">Enable Smart Thumbnail Option</th>
<td><input type="checkbox" name="eStore_enable_smart_thumb" value="1" '.$eStore_enable_smart_thumb.' />
<br /><p class="description">If your product thumbnail images look a little bit squashed then check this option which will take a snapshot of a square section (fit for the thumbnail area) from the image. The thumbnail images must be stored on the same server that this eStore plugin is installed on (not an external server) for this option to work.</p></td>
</tr>

<tr valign="top">
<th scope="row">Products Per Page Limit</th>
<td><input type="text" name="eStore_products_per_page" value="'.$eStore_products_per_page.'" size="4" /> (example: 20) 
<br /><p class="description">This number is used for pagination purpose. This is the number of products that will be displayed per page when displaying the full list of products on a page.</p></td>
</tr>

</table>
</div></div>

<div class="postbox">
<h3><label for="title">General Image and Page URL Settings</label></h3>
<div class="inside">
<table class="form-table">

<tr valign="top">
<th scope="row">Add to Cart Button Text or Image</th>
<td><input type="text" name="addToCartButtonName" value="'.$addcart.'" size="100" />
<br /><p class="description">To use a customized image as the button, simply enter the URL of the image file in the above field. For example, <code>http://example.com/my-images/add-button.jpg</code>
<br />You can specify a customized button image for a particular product from the Add/Edit products menu.</p>
</td>
</tr>

<tr valign="top">
<th scope="row">Sold Out Button Text or Image URL</th>
<td><input type="text" name="soldOutImage" value="'.$soldOut.'" size="100" />
<br /><p class="description">When selling limited copies of a product, this image will be shown instead of the payment button when the product is sold out. You can also use a text in the above field (example text: Sold Out).</p></td>
</tr>

<tr valign="top">
<th scope="row">Products/Store Page URL</th>
<td><input type="text" name="eStore_products_page_url" value="'.$eStore_products_page_url.'" size="100" />
<br /><p class="description">If used, the shopping cart widget will display a link to this page when the cart is empty. Leave empty if you do not have a store or products page.</p></td>
</tr>

<tr valign="top">
<th scope="row">Return URL</th>
<td><input type="text" name="cart_return_from_paypal_url" value="'.$return_url.'" size="100" />
<br /><p class="description">This is the URL the customer will be redirected to after a successful payment. Enter the URL of your Thank You page here.</p></td>
</tr>

<tr valign="top">
<th scope="row">Cancel URL</th>
<td><input type="text" name="cart_cancel_from_paypal_url" value="'.$cancel_url.'" size="100" />
<br /><p class="description">The customer will be redirected to the above page if the payment is cancelled.</p></td>
</tr>

</table>
</div></div>

<div class="postbox">
<h3><label for="title">Shopping Cart Specific Settings</label></h3>
<div class="inside">
<table class="form-table">

<tr valign="top">
<th scope="row">Shopping Cart Widget Title</th>
<td><input type="text" name="wp_eStore_widget_title" value="'.$widget_title.'" size="50" /></td>
</tr>

<tr valign="top">
<th scope="row">Shopping Cart Header</th>
<td><input type="text" name="wp_cart_title" value="'.$title.'" size="50" />
<br /><p class="description">Leave empty if you do not want the shopping cart header to appear</p></td>
</tr>

<tr valign="top">
<th scope="row">Text or Image to Show When the Cart is Empty</th>
<td><input type="text" name="wp_cart_empty_text" value="'.$emptyCartText.'" size="100" />
<br /><p class="description">To use a customized image for the empty cart display instead of a plain text, simply enter the URL of the image file in the above field.</p></td>
</tr>

<tr valign="top">
<th scope="row">Display Continue Shopping Link</th>
<td><input type="checkbox" name="eStore_display_continue_shopping" value="1" '.$eStore_display_continue_shopping.' />
<br /><p class="description">If checked the shopping cart will display a continue shopping link. There must be a URL in the "Products Page" field for this to work.</p></td>
</tr>

<tr valign="top">
<th scope="row">Do Not Show Quantity in Shopping Cart</th>
<td><input type="checkbox" name="eStore_do_not_show_qty_in_cart" value="1" '.$eStore_do_not_show_qty_in_cart.' />
<br /><p class="description">Check this if you do not want the shopping cart to display the product quantity. Your customer can only add one copy of the product to the shopping cart. Can be helpful if you are only selling digital products and you do not want your customers to buy multiple copies of a product.</p></td>
</tr>

<tr valign="top">
<th scope="row">Checkout Page</th>
<td><input type="text" name="eStore_checkout_page_url" value="'.$eStore_checkout_page_url.'" size="100" />
<p class="description">You can optionally create a specific checkout page for eStore if you want. <a href="http://www.tipsandtricks-hq.com/forum/topic/how-to-create-a-specific-checkout-page-for-estore" target="_blank">More Details Here</a></p>
</td>
</tr>

<tr valign="top">
<th scope="row">Automatic redirection to checkout page</th>
<td><input type="checkbox" name="eStore_auto_checkout_redirection" value="1" '.$eStore_auto_checkout_redirection.' />
<br /><p class="description">If checked the visitor will be redirected to the Checkout page after a product is added to the cart. You must enter a URL in the Checkout Page field for this to work.</p></td>
</tr>

<tr valign="top">
<th scope="row">Allow Shopping Cart Anchor</th>
<td><input type="checkbox" name="eStore_auto_cart_anchor" value="1" '.$eStore_cart_anchor.' />
<br /><p class="description">If checked the visitor will be taken to the Shopping cart anchor point within the page after a product Add, Delete or Quantity Change.</p></td>
</tr>

<tr valign="top">
<th scope="row">Hide Shopping Cart Image</th>
<td><input type="checkbox" name="eStore_shopping_cart_image_hide" value="1" '.$eStore_cart_image_hide.' />
<br /><p class="description">If checked the shopping cart image in the title will not be shown.</p></td>
</tr>

<tr valign="top">
<th scope="row">Show Compact Cart in Widget</th>
<td><input type="checkbox" name="eStore_show_compact_cart" value="1" '.$eStore_show_compact_cart.' /> 
<br /><p class="description">If checked the shopping cart displayed in the sidebar widget will display only the number of items in the cart instead of the full item listing. Useful for sites with narrow sidebar.</p></td>
</tr>

<tr valign="top">
<th scope="row">Enable Fancy Redirection On Checkout</th>
<td><input type="checkbox" name="eStore_enable_fancy_redirection_on_checkout" value="1" '.$eStore_enable_fancy_redirection_on_checkout.' /> 
<br /><p class="description">This feature requires WordPress 3.0, if this option conflicts with other plugin(s) then simply uncheck this option. If checked the redirection to the payment page will be displayed using a fancy JQuery effect. If unchecked it will redirect using the standard method.</p></td>
</tr>

<tr valign="top">
<th scope="row">Enable Save and Retrieve Cart Feature</th>
<td><input type="checkbox" name="eStore_enable_save_retrieve_cart" value="1" '.$eStore_enable_save_retrieve_cart.' /> 
<br /><p class="description">If enabled your customers will be able to save the content of their cart and retrieve it at a later time and continue shopping.</p></td>
</tr>

<tr valign="top">
<th scope="row">Enable Checkout Amount Limitations (optional)</th>
<td>
<input type="checkbox" name="eStore_enable_checkout_amt_limit" value="1" '.$eStore_enable_checkout_amt_limit.' />
<span class="description">Only enable this option if you want to impose a checkout amount limitation in the shopping cart. For example: only allow a customer to checkout if the minimum amount in the cart is more than $20.00</span>
<br />'.$defaultSymbol.' <input type="text" name="eStore_checkout_amt_limit_minimum" value="'.$wp_eStore_config->getValue('eStore_checkout_amt_limit_minimum').'" size="5" /> Minimum Checkout Amount (Example: 20.00)
<br />'.$defaultSymbol.' <input type="text" name="eStore_checkout_amt_limit_maximum" value="'.$wp_eStore_config->getValue('eStore_checkout_amt_limit_maximum').'" size="5" /> Maximum Checkout Amount (Example: 99.00)
<br /><p class="description">You can enter a value in one of the above fields or both</p>
</td>
</tr>

</table>
</div></div>

	<div class="postbox">
	<h3><label for="title">Shipping & Tax Related Settings</label></h3>
	<div class="inside">
	
<table class="form-table">	
<tr valign="top">
<th scope="row">Base Shipping Cost</th>
<td><input type="text" name="eStore_base_shipping" value="'.$baseShipping.'" size="2" /> 
<span class="description">(Example: 5.00) Used for non digital Products. This amount is added to the total of the individual products shipping cost. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=50" target="_blank">Read More Here</a></span></td>
</tr>

<tr valign="top">
<th scope="row">Shipping Variation Option</th>
<td><textarea name="eStore_shipping_variation" cols="83" rows="2">'.$shippingVar.'</textarea>
<br /><p class="description">Can be used to offer shipping variation option to customers. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=1056" target="_blank">Read More Here</a></p></td>
</tr>

<tr valign="top">
<th scope="row">Always Display Shipping Variation</th>
<td><input type="checkbox" name="eStore_always_display_shipping_variation" value="1" '.$always_display_shipping_var.' />
<br /><p class="description">If you want to display the shipping variation in the cart all the time then check this field. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=1056#when_to_use_always_display_shipping_var" target="_blank">Read More Here</a></p></td>
</tr>

<tr valign="top">
<th scope="row">Enable Store Pickup</th>
<td><input type="checkbox" name="eStore_enable_store_pickup" value="1" '.$enable_store_pickup.' />
<br /><p class="description">Check this option if you want to allow your users to be able to pick up from your store. When this is enabled, the users will be able to choose to pickup the items from the store. In that case the cart will not charge any shipping.</p></td>
</tr>

<tr valign="top">
<th scope="row">Calculate Tax</th>
<td><input type="checkbox" name="eStore_enable_tax" value="1" '.$eStore_enable_tax.' /> Enables tax calculation
<br />Tax Rate <input type="text" name="eStore_global_tax_rate" value="'.get_option('eStore_global_tax_rate').'" size="2" />% 
<p class="description">Enter the tax rate in the above field (Example: 10). You can override the tax rate of an individual item by editing the product. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=916" target="_blank">Read More Here</a></p>
<input type="checkbox" name="eStore_enable_tax_on_shipping" value="1" '.$eStore_enable_tax_on_shipping.' /> Calculate Tax on Shipping Amount
<p class="description">If you want to add tax on the shipping amount then check this option.</p>
<input type="checkbox" name="eStore_show_tax_inclusive_price" value="1" '.$eStore_show_tax_inclusive_price.' /> Show Tax Inclusive Amount
<p class="description">The tax amount is shown separately in the shopping cart by default (this is the popular choice). However, If you want to show tax inclusive amount for each product then check this option.</p>
</td>
</tr>

</table>
</div></div>

	<div class="postbox">
	<h3><label for="title">Secondary Currency Settings (This is for Display Purpose Only)</label></h3>
	<div class="inside">
<i>&nbsp;&nbsp;If you want to display the price of your products in a secondary currency side by side with your primary currency then use this section otherwise leave the fields empty.</i>	

<table class="form-table">	
<tr valign="top">
<th scope="row">Secondary Currency Code </th>
<td><input type="text" name="eStore_secondary_currency_code" value="'.get_option('eStore_secondary_currency_code').'" size="3" /> 
<span class="description"> (e.g. USD, EUR, GBP, AUD)</span</td>
</tr>

<tr valign="top">
<th scope="row">Secondary Currency Symbol </th>
<td><input type="text" name="eStore_secondary_currency_symbol" value="'.get_option('eStore_secondary_currency_symbol').'" size="3" /> 
<span class="description"> (e.g. $, &#163;, &#8364;)</span></td>
</tr>

<tr valign="top">
<th scope="row">Conversion Rate</th>
<td><input type="text" name="eStore_secondary_currency_conversion_rate" value="'.get_option('eStore_secondary_currency_conversion_rate').'" size="3" /> 
<br /><p class="description">Conversion rate for the primary to secondary currency. For example if your primary currency is USD and the secondary currency is EUR then a rough conversion rate would be .775</p></td>
</tr>

</table>
</div></div>

	<div class="postbox">
	<h3><label for="title">Digital Product Delivery Settings</label></h3>
	<div class="inside">		
		
<table class="form-table">
<tr valign="top">
<th scope="row">Random Code</th>
<td><input type="text" name="eStore_random_code" value="'.$eStore_random_code.'" size="70" />
<br /><p class="description">This Random code is used as a key to generate the encrypted download link for your downloadable products. Change it to something random.</p></td>
</tr>

<tr valign="top">
<th scope="row">Duration of Download Link</th>
<td><input type="text" name="eStore_download_url_life" value="'.$eStore_download_url_life.'" size="3" /> Hours
<br /><p class="description">This is the duration of time the encrypted links will remain active. After this amount of time the link will expire.</p></td>
</tr>
	
<tr valign="top">
<th scope="row">Download Limit Count</th>
<td><input type="text" name="eStore_download_url_limit_count" value="'.$eStore_download_url_limit_count.'" size="3" /> Times
<br /><p class="description">Number of times an item can be downloaded before the link expires. Leave empty or set a high value (e.g. 999) if you do not want to limit downloads by download count.</p></td>
</tr>

<tr valign="top">
<th scope="row">Download Validation Script Location</th>
<td><input type="text" name="eStore_validation_sc_url" value="'.$eStore_download_script.'" size="100" />
<br /><p class="description">You do not need to change this value unless you want to customize this. Can be used to customize the download URL <a href="http://tipsandtricks-hq.com/ecommerce/?p=224" target="_blank">Read More Here</a>.<br>Configuration hint for the custom_download.php file is --> <font style="background-color:#ffff66">$wp_home_dir = \''.$wp_home_dir.'\';</font></p>';
$dl_script_file_path = $eStore_download_script.'download.php';
if(!file_exists(eStore_dlfilepath::absolute_from_url($dl_script_file_path))) {
	echo '<br><strong><font color=#ff0000>ADVISORY: If you changed the download validation script location, please remember to install a customized version of download.php as per the above instructions.</font></strong>';
	echo '  Note: If you are using a subdomain for the download validation script location, that is different from that of the eStore plugin, this message may be a false positive.  But just to be sure, please double check your work, before ignoring this message.';
}
if(!file_exists('../wp-content/plugins/wp-cart-for-digital-products/download.php')) {
	echo '<br>i<strong><font color=#ff0000>WARNING: Cannot locate file download.php in the eStore plugin directory.</font></strong>';
}
echo '</td>
</tr>

<tr valign="top">
<th scope="row">Shorten Encrypted Download Links</th>
<td><input type="checkbox" name="eStore_auto_shorten_dl_links" value="1" '.$eStore_auto_shorten_dl_links.' />
<br /><p class="description">Use this option if you want to automatically shorten the encrypted download links/URLs using Google URL shortener service. The encrypted download links will look like the following when you use this feature.
<br /><code>http://goo.gl/bV9Z1</code>
</p></td>
</tr>

<tr valign="top">
<th scope="row">Enforce Zero Price Checking on Free Product Download</th>
<td><input type="checkbox" name="eStore_product_price_must_be_zero_for_free_download" value="1" '.$eStore_product_price_must_be_zero_for_free_download.' />
<br /><p class="description">Enabling this option will add an extra security check in eStore before free downloads are given to the users (example: via a squeeze form). It will make sure that the product has a price value of zero (0.00) before offering the download.</p></td>
</tr>

</table>
</div></div>

	<div class="postbox">
	<h3><label for="title">Post Payment Processing Settings</label></h3>
	<div class="inside">	
<table class="form-table">
<tr valign="top">
<th scope="row">Use Automatic Post Payment Processing</th>
<td><input type="checkbox" name="eStore_auto_product_delivery" value="1" '.$eStore_auto_delivery.' />
<br /><p class="description">If checked the plugin will perform the post payment processing after every purchase. This option must be checked if you want certain functionality eg. automatically send emails after purchase, award affiliate commission automatically if you are using the affiliate platform plugin etc.</p></td>
</tr>

<tr valign="top">
<th scope="row">Use Strict PayPal Email Address Checking</th>
<td><input type="checkbox" name="eStore_strict_email_check" value="1" '.$eStore_strict_email_check.' />
<br /><p class="description">If checked the script will check to make sure that the PayPal email address specified is the same as the account where the payment was deposited (Usage of PayPal Email Alias will fail too).</p></td>
</tr>

<tr valign="top">
<th scope="row">Use Automatic Customer Record Removal</th>
<td><input type="checkbox" name="eStore_auto_customer_removal" value="1" '.$eStore_auto_customer_removal.' />
<br /><p class="description">If checked the plugin will delete a customer record from the customer database when a refund is issued.</p></td>
</tr>

<tr valign="top">
<th scope="row">Enable Transaction Result Display</th>
<td><input type="checkbox" name="eStore_display_tx_result" value="1" '.$eStore_display_tx_result.' />
<br /><p class="description">Check this if you want to display the transaction result containing the product delivery message on a post-payment return page (eg. a Thank You page). This allows the customer to be able to download the Digital goods via an encrypted link from this page instantly. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=499" target="_blank">Learn How To</a></p></td>
</tr>
</table>
		
</div></div>
		
<div class="postbox">
<h3><label for="title">Email Settings</label></h3>
<div class="inside">					
<p>The email settings fields have been <a href="admin.php?page=wp_eStore_settings&settings_action=email" target="_blank">moved here</a></p>
</div></div>
		
	<div class="postbox">
	<h3><label for="title">Testing and Debugging Settings</label></h3>
	<div class="inside">'.
	eStore_dbgmgradm::settings_menu_html().
	'</div></div>
    
    <div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="Update Options &raquo;" />
    </div>

 </form><p></p> 
 ';

}
