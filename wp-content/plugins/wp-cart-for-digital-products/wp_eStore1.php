<?php

if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
    if (session_status() == PHP_SESSION_NONE) {session_start();}
}
else{
    if(session_id() == '') {session_start();}
}
//if(!isset($_SESSION)){@session_start();}//Old method 
    
define('WP_ESTORE_SITE_HOME_URL', home_url());
define('WP_ESTORE_WP_SITE_URL', site_url());
define('WP_ESTORE_FOLDER', dirname(plugin_basename(__FILE__)));
define('WP_ESTORE_URL', plugins_url('',__FILE__));
define('WP_ESTORE_PATH',plugin_dir_path( __FILE__ ));
define('WP_ESTORE_LIB_URL', WP_ESTORE_URL.'/lib');
define('WP_ESTORE_IMAGE_URL', WP_ESTORE_URL.'/images');
define('WP_ESTORE_GOOG_URLSHRT_API_KEY', 'AIzaSyDtY1Sgq624QngVurGhF6sjDuE7PvRKr4M');

global $wpdb;
define('WP_ESTORE_PRODUCTS_TABLE_NAME', $wpdb->prefix . "wp_eStore_tbl");
define('WP_ESTORE_CUSTOMER_TABLE_NAME', $wpdb->prefix . "wp_eStore_customer_tbl");
define('WP_ESTORE_CATEGORY_TABLE_NAME', $wpdb->prefix . "wp_eStore_cat_tbl");
define('WP_ESTORE_CATEGORY_RELATIONS_TABLE_NAME', $wpdb->prefix . "wp_eStore_cat_prod_rel_tbl");
define('WP_ESTORE_COUPON_TABLE_NAME', $wpdb->prefix . "wp_eStore_coupon_tbl");
define('WP_ESTORE_DB_SALES_TABLE_NAME', $wpdb->prefix . "wp_eStore_sales_tbl");
define('WP_ESTORE_SAVE_CART_TABLE_NAME', $wpdb->prefix . "wp_eStore_save_cart_tbl");
define('WP_ESTORE_PRODUCTS_META_TABLE_NAME', $wpdb->prefix . "wp_eStore_products_meta_tbl");
define('WP_ESTORE_GLOBAL_META_TABLE_NAME', $wpdb->prefix . "wp_eStore_meta_tbl");
define('PAYPAL_LIVE_URL', "https://www.paypal.com/cgi-bin/webscr");
define('PAYPAL_SANDBOX_URL', "https://www.sandbox.paypal.com/cgi-bin/webscr");

$addcart_eStore = get_option('addToCartButtonName');
if (!$addcart_eStore || ($addcart_eStore == '')){$addcart_eStore = 'Add to Cart';}
define('WP_ESTORE_ADD_CART_BUTTON', $addcart_eStore);
define('WP_ESTORE_CURRENCY_SYMBOL', get_option('cart_currency_symbol'));
define('WP_ESTORE_VARIATION_ADD_SYMBOL',get_option('eStore_variation_add_symbol'));

$soldOutImage = get_option('soldOutImage');
if (empty($soldOutImage)) $soldOutImage = WP_ESTORE_URL.'/images/sold_out.png';
define('WP_ESTORE_SOLD_OUT_IMAGE', $soldOutImage);

//includes
$cart_language = get_option('eStore_cart_language');
if (!empty($cart_language))
	$language_file = "languages/".$cart_language;
else
	$language_file = "languages/eng.php";
include_once($language_file);
include_once('eStore_advanced_configs.php'); //maybe conditionally load this?
include_once('eStore_utility_functions.php');
include_once('eStore_utility_functions2.php');
include_once('eStore_db_access.php');
include_once('eStore_classes/wp_eStore_cart_class.php');
include_once('eStore_classes/wp_eStore_customer_class.php');
include_once('eStore_misc_functions.php');
include_once('eStore_cart.php');
include_once('eStore_post_payment_processing_helper.php');
include_once('eStore_includes2.php');
include_once('eStore_includes3.php');
include_once('eStore_email.php');
include_once('eStore_discount_calc.php');
include_once('eStore_classes/_loader.php');
include_once('eStore_manual_gateway_functions.php');
include_once('eStore_serial_key_functions.php');
include_once('eStore_ajax.php');
include_once('eStore_self_action_handler.php');

if(WP_ESTORE_RESET_CART_ACTION_MSG_ON_PAGE_RELOAD === '1' && isset($_SESSION['action_msg_set_time'])){
	$nowtime = time();
	$msg_set_time = $_SESSION['action_msg_set_time'];
	if($nowtime - $msg_set_time > 3){
	unset($_SESSION['eStore_last_action_msg']);
	unset($_SESSION['eStore_last_action_msg_2']);
	unset($_SESSION['eStore_last_action_msg_3']);
	}
}

function wp_eStore_save_retrieve_cart_handler($atts){
	return eStore_cart_save_retrieve_cart_part();
}
function wp_eStore_free_download_squeeze_form_handler($atts){
	//TODO - make this the default shortcode for squeeze form
	extract(shortcode_atts(array(
		'id' => 'no id',
		'button_text' => '',
	), $atts));	
	return eStore_free_download_form($id,'','',$button_text);
}
function wp_eStore_product_details_handler($atts){
	extract(shortcode_atts(array(
		'id' => 'no id',
		'info' => '',
	), $atts));	
	return eStore_show_product_details($id,$info);	
}
function wp_eStore_add_to_cart_handler($atts){
	extract(shortcode_atts(array(
		'id' => 'no id',
		'button_image' => '',
	), $atts));
	return get_button_code_for_product($id,$button_image);
}
function wp_eStore_buy_now_handler($atts){
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));	
	return print_eStore_buy_now_button($id);	
}
function wp_eStore_subscribe_handler($atts){
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return print_eStore_subscribe_button_form($id);
}
function eStore_sale_counter($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return eStore_get_sale_counter($id);
}

function eStore_remaining_copies_counter($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return eStore_get_remaining_copies_counter($id);
}

function eStore_download_now_button($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return eStore_show_download_now_button($id);
}

function eStore_download_now_button_fancy($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return show_product_fancy_style($id,$button_type=4);
}

function eStore_download_now_button_fancy_no_price_handler($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return show_download_now_fancy_no_price($id);
}

function eStore_fancy1($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return show_product_fancy_style($id);
}

function eStore_fancy2($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return show_product_fancy_style2($id);
}

function eStore_buy_now_fancy($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return show_product_fancy_style($id,$button_type=2);
}

function eStore_subscribe_fancy($atts) {
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return show_product_fancy_style($id,$button_type=3);
}

function wp_estore_display_category_fancy($atts)
{
	extract(shortcode_atts(array(
		'id' => 'no id',
	), $atts));
	return show_category_stylish($id);
}

function wp_eStore_buy_now_custom_button_handler($atts)
{
	extract(shortcode_atts(array(
		'id' => 'no id',
		'button' => 'Buy Now',
	), $atts));
	return print_eStore_buy_now_button($id,$button);
}

function wp_eStore_buy_now_for_specific_gateway_handler($atts)
{
	extract(shortcode_atts(array(
		'id' => '',
		'gateway' => 'paypal',
		'button_text' => '',
		'button_image' => '',
	), $atts));
	return eStore_get_gateway_specific_buy_now_button_code($id,$gateway,$button_text,true,'',$button_image);	
}

function wp_eStore_members_purchase_history_handler($atts)
{	
	return eStore_show_members_purchase_history();
}
function wp_eStore_members_purchase_history_with_download_handler($atts)
{
	return eStore_show_members_purchase_history_with_download();
}
function wp_estore_display_categories_fancy()
{
	return show_all_categories_stylish();
}

function wp_digi_cart_always_show()
{
    return eStore_shopping_cart_multiple_gateway();
}
function eStore_shopping_cart_fancy1_when_not_empty()
{
    $output = "";
    if (digi_cart_not_empty()){
        $output = eStore_shopping_cart_fancy1();
    }
    else{
        $output = '<div class="estore-cart-wrapper-1"></div>';
    }
    return $output;
}

function eStore_cart_when_not_empty()
{
    $output = "";
    if (digi_cart_not_empty()){
        $output = print_wp_digi_cart();
    }
    else{
        $output = '<div class="estore-cart-wrapper-0"></div>';
    }
    return $output;
}

function filter_eStore_transaction_result($content)
{
        $pattern = '#\[wp_eStore_transaction_result:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
			$replacement = eStore_display_transaction_result();
			$content = str_replace ($match, $replacement, $content);
        }
    	return $content;
}
function eStore_display_all_products_stylish($content)
{
        $pattern = '#\[wp_eStore_all_products_stylish:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
			$replacement = eStore_print_all_products_stylish();
			$content = str_replace ($match, $replacement, $content);
        }
    	return $content;
}

function reset_eStore_cart()
{
    if(isset($_SESSION['eStore_cart']))
    {
        $products = $_SESSION['eStore_cart'];
        foreach ($products as $key => $item)
        {
            if (!empty($item['name']))
                unset($products[$key]);
        }
        unset($_SESSION['discount_applied_once']);
        unset($_SESSION['coupon_code']);
        unset($_SESSION['eStore_coupon_code']);
        unset($_SESSION['auto_discount_applied_once']);
        unset($_SESSION['eStore_discount_total']);
        unset($_SESSION['eStore_last_action_msg']);
        unset($_SESSION['eStore_last_action_msg_2']);
        unset($_SESSION['eStore_last_action_msg_3']);
        $_SESSION['eStore_cart'] = $products;
        unset($_SESSION['eStore_selected_shipping_option_cost']);
        unset($_SESSION['eStore_selected_shipping_option']);
        unset($_SESSION['eStore_shipping_variation_updated_once']);
        unset($_SESSION['eStore_url']);
        unset($_SESSION['eStore_area_specific_total_tax']);
        unset($_SESSION['eStore_store_pickup_checked']);
        do_action('eStore_cart_reset_action');
    }
}

if (WP_ESTORE_RESET_CART_ON_WP_INSTALL_CHANGE == '1'){
	if(isset($_SESSION['eStore_url'])){
		if($_SESSION['eStore_url'] != WP_ESTORE_URL){//reset the cart
			reset_eStore_cart();
		}
	}
}

function eStore_cart_actions_handlers()
{
	if (isset($_POST['addcart_eStore']))
	{
            $wp_eStore_config = WP_eStore_Config::getInstance();
            //$cookie_domain = eStore_get_top_level_domain();	  	
	    setcookie("cart_in_use","true",time()+21600,"/",COOKIE_DOMAIN); 
	    if (function_exists('wp_cache_serve_cache_file')){//WP Super cache workaround
	    	setcookie("comment_author_","eStore",time()+21600,"/",COOKIE_DOMAIN);
	    }
	    
	    unset($_SESSION['eStore_last_action_msg']);
	    unset($_SESSION['eStore_last_action_msg_2']); 
	    unset($_SESSION['eStore_last_action_msg_3']);
	    if (isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1){//Coupon was already applied
			eStore_load_price_from_backed_up_cart();     
	    }
	    
	    $count = 1;
	    isset($_SESSION['eStore_cart'])? $products = $_SESSION['eStore_cart'] : $products='';//$products = $_SESSION['eStore_cart'];    
            //sanitize data
            $product_name = strip_tags($_POST['estore_product_name']);
            if(empty($product_name)){
                $product_name = strip_tags($_POST['product']);//for PHP5.2 use filter_var($_POST['product'], FILTER_SANITIZE_STRING);
            }
	    $_POST['add_qty'] = strip_tags($_POST['add_qty']);
		$_POST['item_number'] = strip_tags($_POST['item_number']);
		if(isset($_POST['custom_price']))$_POST['custom_price'] = strip_tags($_POST['custom_price']);
		if(isset($_POST['price']))$_POST['price'] = strip_tags($_POST['price']);
		isset($_POST['shipping'])?$_POST['shipping'] = strip_tags($_POST['shipping']):$_POST['shipping']='';
		isset($_POST['cartLink'])?$_POST['cartLink'] = strip_tags($_POST['cartLink']):$_POST['cartLink']='';
		isset($_POST['thumbnail_url'])?$_POST['thumbnail_url'] = strip_tags($_POST['thumbnail_url']):$_POST['thumbnail_url']='';	
		isset($_POST['tax'])?$_POST['tax'] = strip_tags($_POST['tax']):$_POST['tax']='';
		if(isset($_POST['digital_flag'])){$_POST['digital_flag'] = strip_tags($_POST['digital_flag']);}else{$_POST['digital_flag']='';}
	    if($_POST['add_qty'] < 1){$_POST['add_qty'] = 1;}
	    
	    if (is_array($products))
	    {
	        foreach ($products as $key => $item)
	        {
	            if ($item['name'] == stripslashes($product_name))
	            {
	            	if($wp_eStore_config->getValue('eStore_do_not_show_qty_in_cart')){
	            		$_SESSION['eStore_last_action_msg'] = '<p class="eStore_error_message">'.ESTORE_ITEM_ALREADY_EXISTS.'</p>';
	            		$_SESSION['action_msg_set_time'] = time();
	            		$count = 2;
	            		continue;
	            	}
	                $req_qty = $item['quantity']+$_POST['add_qty'];
	                $update_quantity = is_quantity_availabe($item['item_number'],$req_qty,$item['name']);
	
	                $count += $item['quantity'];
	                if ($update_quantity)
	                {
	                    $item['quantity'] = $item['quantity'] + $_POST['add_qty'];
	                    unset($products[$key]);
	                    array_push($products, $item);
	                }
	            }
	        }
	    }
	    else
	    {
	        $products = array();
	    }
	
	    if ($count == 1)
	    {
	    	$item_addittion_permitted = true;
	    	$prod_name = stripslashes($product_name);
	    	$quantity_available = is_quantity_availabe($_POST['item_number'],$_POST['add_qty'],$prod_name);
	    	if (!$quantity_available)
	    	{
	    		//Requested qty not available
	    		if(is_numeric(WP_ESTORE_CART_CHECKOUT_ITEM_LIMIT) && WP_ESTORE_CART_CHECKOUT_ITEM_LIMIT > 0)
	    		{
	    			//cart checkout limit apply so cannot add this item
	    			$item_addittion_permitted = false;
	    		}
				$_POST['add_qty'] = 1; //Add one by default
	    	}
	    	
	    	if($item_addittion_permitted)
	    	{
		        if (!empty($_POST[$product_name])){
		            $price = $_POST[$product_name];
		        }
		        else if (isset($_POST['custom_price'])){
		        	global$wpdb;
		           	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
		           	$id = $_POST['item_number'];
		        	$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
		        	if($_POST['custom_price'] < $retrieved_product->price){
		        		$price = $retrieved_product->price;
		        		$currSymbol = get_option('cart_currency_symbol');
		        		$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER.$currSymbol.$retrieved_product->price.'</p>';
		        	}        	
		        	else{                                    
                                    $price = $_POST['custom_price'];
                                    //This price has been entered by the user. So it is already entered in the currently set currency.
                                    //The following will make it compatible with the multi-currency addon.
                                    $price = apply_filters('eStore_convert_to_store_amount', $price);
		        	}
		    	}
		    	else{
		    		$price = $_POST['price'];
		    	}
		        $product = array('name' => stripslashes($product_name), 'price' => $price, 'quantity' => $_POST['add_qty'], 'shipping' => $_POST['shipping'], 'item_number' => $_POST['item_number'], 'cartLink' => $_POST['cartLink'], 'thumbnail_url' => $_POST['thumbnail_url'],'tax' => $_POST['tax'],'digital_flag' => $_POST['digital_flag']);
		        array_push($products, $product);
		        $_SESSION['eStore_last_item_add_url'] = $_POST['cartLink'];
		        //$_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.ESTORE_ITEM_ADDED.'</p>';
	    	}
	    }
	
	    sort($products);
	    $_SESSION['eStore_cart'] = $products;
	    $_SESSION['eStore_url'] = WP_ESTORE_URL;
            $last_added_item_id = $_POST['item_number'];
            
	    if (isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1){//Handle discount if already applied to the cart
	    	if(isset($_SESSION['auto_discount_applied_once']) && $_SESSION['auto_discount_applied_once'] == 1){
				//The auto discount will be taken care of later when the cart loads (it will recalculate)
			}
			else{
				unset($_SESSION['discount_applied_once']);
				eStore_apply_discount($_SESSION['eStore_coupon_code']);	  						
			}
	    }
            
            do_action('eStore_action_item_added_to_cart',$last_added_item_id);
            do_action('eStore_action_cart_data_updated');

	    wp_eStore_check_cookie_flag_and_store_values();
	
	    if (get_option('eStore_auto_checkout_redirection'))
	    {
	    	$checkout_page_settings_value = get_option('eStore_checkout_page_url');
	        if(empty($checkout_page_settings_value))
	    	{
	    		echo '<div class="eStore_error_message">Error in your eStore configuration! You must specify a value in the "Checkout Page" field in the settings menu if you want to use the "Automatic redirection to checkout page" option.</div>';
	    		exit;
	    	}    	
	    	$checkout_url = eStore_get_checkout_url();
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
	    }
	    eStore_redirect_if_using_anchor();
	}
	else if (isset($_POST['eStore_cquantity']))
	{
            unset($_SESSION['eStore_last_action_msg']);
            unset($_SESSION['eStore_last_action_msg_2']);
            unset($_SESSION['eStore_last_action_msg_3']);
            if (isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1)
            {
                //reset_eStore_cart();
                eStore_load_price_from_backed_up_cart();     
            }

            $products = $_SESSION['eStore_cart'];
            //sanitize data
            $product_name = strip_tags($_POST['estore_product_name']);
            if(empty($product_name)){
                $product_name = strip_tags($_POST['product']);
            }
            $_POST['quantity'] = strip_tags($_POST['quantity']);

            foreach ($products as $key => $item)
            {
                if (($item['name'] == stripslashes($product_name)) && $_POST['quantity'])
                {
                    $update_quantity = is_quantity_availabe($item['item_number'],$_POST['quantity'],$item['name']);
                    if ($update_quantity)
                    {
                        $item['quantity'] = $_POST['quantity'];
                        unset($products[$key]);
                        array_push($products, $item);
                    }
                }
                else if (($item['name'] == stripslashes($product_name)) && !$_POST['quantity']){
                        unset($products[$key]);
                }
            }
            sort($products);
            $_SESSION['eStore_cart'] = $products;

            if (isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1)
            {
                if(isset($_SESSION['auto_discount_applied_once']) && $_SESSION['auto_discount_applied_once'] == 1){
                    //The auto discount will be taken care of later when the cart loads (it will recalculate)
                }  
                else{
                    unset($_SESSION['discount_applied_once']);
                    eStore_apply_discount($_SESSION['eStore_coupon_code']);	  						
                }
            }

            do_action('eStore_action_item_qty_changed_in_cart');
            do_action('eStore_action_cart_data_updated');
            wp_eStore_check_cookie_flag_and_store_values();    	
            eStore_redirect_if_using_anchor();   
	}
	else if (isset($_POST['eStore_delcart']))
	{
            unset($_SESSION['eStore_last_action_msg']);
            unset($_SESSION['eStore_last_action_msg_2']);
            unset($_SESSION['eStore_last_action_msg_3']);
	    if (isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1)
	    {
	        //reset_eStore_cart();
	        eStore_load_price_from_backed_up_cart();
	    }

	    $products = $_SESSION['eStore_cart'];
            $product_name = strip_tags($_POST['estore_product_name']);//sanitize data
            if(empty($product_name)){
                $product_name = strip_tags($_POST['product']);
            }
	    foreach ($products as $key => $item)
	    {
	        if ($item['name'] == stripslashes($product_name)){
	            unset($products[$key]);
                }
	    }
	    $_SESSION['eStore_cart'] = $products;
            
	    if (isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1)
	    {	 
			if(isset($_SESSION['auto_discount_applied_once']) && $_SESSION['auto_discount_applied_once'] == 1){
				//The auto discount will be taken care of later when the cart loads
			}  
			else{
				unset($_SESSION['discount_applied_once']);
				eStore_apply_discount($_SESSION['eStore_coupon_code']);	  						
			}
	    }
            
            do_action('eStore_action_item_removed_from_cart');
            do_action('eStore_action_cart_data_updated');
            
	    wp_eStore_check_cookie_flag_and_store_values();
	    eStore_redirect_if_using_anchor();
	}
        
        //Discount application
        if (isset($_POST['eStore_apply_discount']))
        {
            $_POST['coupon_code'] = strip_tags($_POST['coupon_code']);  	
            $coupon = $_POST['coupon_code'];
            eStore_apply_discount($coupon);
        }
        
        //Reset cart action
        if (isset($_REQUEST['reset_eStore_cart']))
        {
            reset_eStore_cart();
            wp_eStore_check_cookie_flag_and_store_values();
        }
        
        if(isset($_POST['eStore_shipping_variation'])){
            $_SESSION['eStore_shipping_variation_updated_once'] = '1';
        }

        if (isset($_POST['eStore_area_tax_submitted'])){
            if (WP_ESTORE_APPLY_TAX_FOR_CERTAIN_AREA !== '0')//Apply sales tax for this customer
            {
                $_SESSION['eStore_area_specific_total_tax'] = eStore_calculate_total_cart_tax();
            }
        }
}//End of cart actions handler function

function eStore_misc_loader_handlers()
{
	if(isset($_REQUEST['estore_pagination_go'])){//Pagination go request
		$target_page_no = sanitize_text_field($_REQUEST['estore_pagination_page_no']);
		$parameter_name = sanitize_text_field($_REQUEST['estore_pagination_parameter_name']);
		$page_url = esc_url($_REQUEST['estore_pagination_raw_url']);
		$target_page = eStore_append_http_get_data_to_url($page_url,$parameter_name,$target_page_no);
		eStore_redirect_to_url($target_page);		
	}
        eStore_download_now_button_request_handler();
}

if (isset($_POST['eStore_apply_aff_id']))
{
	if (function_exists('wp_aff_platform_install'))
	{
		$_POST['estore_aff_id'] = strip_tags($_POST['estore_aff_id']);
		record_click_for_eStore_cart($_POST['estore_aff_id']);	
		$_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.ESTORE_AFFILIATE_ID_SET.'</p>';
		
		if (get_option('eStore_aff_link_coupon_aff_id') == 1)
		{
			eStore_apply_discount($_POST['estore_aff_id']);			
		}		
	}
	else
	{
		$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_AFFILIATE_PLUGIN_INACTIVE.'</p>';
	}
}

function eStore_apply_discount($coupon)
{
    if (!isset($_SESSION['discount_applied_once']) || $_SESSION['discount_applied_once'] != 1 || WP_ESTORE_ALLOW_COUPON_STACKING === '1')
    {
    	eStore_backup_estore_cart_before_coupon_application();
        $_SESSION['eStore_coupon_code'] = $coupon;
        global $wpdb;
        $coupon_table_name = $wpdb->prefix . "wp_eStore_coupon_tbl";
        $ret_coupon = $wpdb->get_row("SELECT * FROM $coupon_table_name WHERE coupon_code = '$coupon'", OBJECT);
        if ($ret_coupon)
        {
        	$coupon_error = false;
        	if($ret_coupon->active!='Yes')
        	{
        		$coupon_error = true;
        		$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_COUPON_NOT_ACTIVE.'</p>';
        	}
        	else if(!empty($ret_coupon->redemption_count) && $ret_coupon->redemption_count >= $ret_coupon->redemption_limit)
        	{
        		$coupon_error = true;
        		$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_MAX_COUPON_USE.'</p>';
        	}
            //Check expiry
        	if($ret_coupon->expiry_date != '0000-00-00')
        	{
        		$todaysdate = strtotime(date("Y-m-d"));
        		$expirydate = strtotime($ret_coupon->expiry_date);
        		if($expirydate < $todaysdate)
        		{
        			$coupon_error = true;
        			$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'."Coupon code expired!".'</p>';        			
        		}
        	}
            //Check start date
        	if($ret_coupon->start_date != '0000-00-00')
        	{
        		$todaysdate = strtotime(date("Y-m-d"));
        		$startdate = strtotime($ret_coupon->start_date);
        		if($todaysdate < $startdate)
        		{
        			$coupon_error = true;
        			$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'."This coupon code cannot be used until ".date('F j, Y',$startdate).'</p>';        			
        		}
        	}
			if(!$coupon_error)
			{
				if(empty($ret_coupon->value))
				{
		            $discount_amount = $ret_coupon->discount_value;
		            $discount_type = $ret_coupon->discount_type;
		            $discount_total = 0;
		            $products = $_SESSION['eStore_cart'];
		            if ($discount_type == 0)//% discount
		            {
		            	foreach ($products as $key => $item)
		            	{
		            		if ($item['price'] > 0)
		            		{
		            			$item_discount = (($item['price']*$discount_amount)/100);	            			
		            			$discount_total = $discount_total + $item_discount*$item['quantity'];
		            			$item['price'] = $item['price'] - $item_discount;
		                        unset($products[$key]);
		                        array_push($products, $item);
		            		}
		            		$_SESSION['discount_applied_once'] = 1;
		            	}
		            }
		            else//fixed
		            {
		            	foreach ($products as $key => $item)
		            	{
		            		if (($item['price'] - $discount_amount)> 0){
		            			$discount_total = $discount_total + $discount_amount*$item['quantity'];
		            			$item['price'] = ($item['price'] - $discount_amount);
		            		}else{//Discount amount is bigger or same as item price		            			
		            			$discount_total = $discount_total + $item['price'];
		            			$item['price'] = 0;        			
		            		}
		            		unset($products[$key]);
		            		array_push($products, $item);
		            		$_SESSION['discount_applied_once'] = 1;
		            	}
		            }
		            $discount_total = round($discount_total, 2);
		            $discount_total = number_format($discount_total, 2, '.', '');
		            $_SESSION['eStore_discount_total'] = $discount_total;
		            $_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.ESTORE_TOTAL_DISCOUNT.WP_ESTORE_CURRENCY_SYMBOL.$discount_total.'</p>';
		            sort($products);
		            $_SESSION['eStore_cart'] = $products;
				}
				else
				{
					$discount_total = round(eStore_apply_cond_discount($ret_coupon),2);
					if ($discount_total == -99)//ESTORE_DISCOUNT_FREE_SHIPPING
					{
						$_SESSION['discount_applied_once'] = 1;
						//$_SESSION['eStore_discount_total'] = ESTORE_DISCOUNT_FREE_SHIPPING;
						$_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.ESTORE_TOTAL_DISCOUNT.ESTORE_DISCOUNT_FREE_SHIPPING.'</p>';						
					}					
					else if($discount_total != 0)
					{
						$discount_total = number_format($discount_total, 2, '.', '');
						$_SESSION['eStore_discount_total'] = $discount_total;
						$_SESSION['discount_applied_once'] = 1;
						$_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.ESTORE_TOTAL_DISCOUNT.WP_ESTORE_CURRENCY_SYMBOL.$discount_total.'</p>';
					}
					else
					{
						$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_COUPON_COND_NOT_MET.'</p>';
					}
				}				
			}//end apply discount
        }
        else
        {
        	$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_COUPON_INVALID.'</p>';
        }
    }
    else
    {
    	$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_DISCOUNT_LIMIT.'</p>';
    }   
    $_SESSION['action_msg_set_time'] = time();
    do_action('eStore_action_cart_coupon_applied');
    wp_eStore_check_cookie_flag_and_store_values();
}

function eStore_redirect_if_using_anchor()
{	
    if (get_option('eStore_auto_cart_anchor'))
    {
        $anchor_name = digi_cart_current_page_url()."#wp_cart_anchor";
        $redirection_parameter = 'Location: '.$anchor_name;
        header($redirection_parameter);        
    }	    
}
function is_quantity_availabe($id,$requested_quantity,$prod_name="")
{
	if(!is_numeric($requested_quantity)){
		$_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_ENTER_A_NUMBER_FOR_QTY.'</p>';
		return false;		
	}
	
    global $wpdb;
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if(!$ret_product){//Invalid item ID
		echo "<br />Error! Invalid item ID passed to the is_quantity_availabe() function";
		return false;	
	}
	
	$args = array('id' => $id, 'requested_quantity' => $requested_quantity, 'prod_name' => $prod_name);
	$qty_available_chk_result = "";
	$qty_available_chk_result = apply_filters('eStore_qty_available_filter', $qty_available_chk_result, $args);
	if(!empty($qty_available_chk_result)){
		$_SESSION['eStore_last_action_msg_2'] = '<p style="color: red;">'.$qty_available_chk_result.'</p>';
		return false;
	}
	
    //Check per customer quantity limit
    if(is_numeric($ret_product->per_customer_qty_limit))
    {    	
    	$client_ip = $_SERVER['REMOTE_ADDR'];
	    $purchased_qty = 0;	    
	    $customer_table_name = WP_ESTORE_CUSTOMER_TABLE_NAME;
	    $resultset = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE ipaddress = '$client_ip' and purchased_product_id = '$id'", OBJECT);
	    foreach ($resultset as $row){
	    	$purchased_qty += $row->purchase_qty;
	    }
	    
	    if (isset($_REQUEST['eStore_cquantity'])){//This is a change quantity request in the cart
                $total_qty_for_this_item = $purchased_qty + $requested_quantity;
	    }else{//Add to cart request
                $total_qty_for_this_item = $purchased_qty + $requested_quantity;
                //Alt logic below
                //$new_qty_to_add = strip_tags($_POST['add_qty']);
	    	//$current_item_qty_in_cart = eStore_get_current_cart_item_qty_for_product($id);
	    	//$total_qty_for_this_item = $current_item_qty_in_cart + $purchased_qty + $new_qty_to_add;
	    }
	    
	    if($total_qty_for_this_item > $ret_product->per_customer_qty_limit)
	    {    	
	    	$_SESSION['eStore_last_action_msg_2'] = '<p style="color: red;">'.ESTORE_ITEM_LIMIT_EXCEEDED.'</p>';
	    	return false;
	    }  
    }
    //Check cart item limit
    if(is_numeric(WP_ESTORE_CART_CHECKOUT_ITEM_LIMIT) && WP_ESTORE_CART_CHECKOUT_ITEM_LIMIT > 0){
    	$current_item_qty_in_cart = eStore_get_total_cart_item_qty();
    	if($current_item_qty_in_cart >= WP_ESTORE_CART_CHECKOUT_ITEM_LIMIT){
    		$_SESSION['eStore_last_action_msg_2'] = '<p style="color: red;">'.ESTORE_CART_QTY_LIMIT_EXCEEDED.WP_ESTORE_CART_CHECKOUT_ITEM_LIMIT.'</p>';
    		return false;
    	}
    }
    
    if (is_numeric($ret_product->available_copies))
    {
        if ($ret_product->available_copies >= $requested_quantity)
        {
        	//$_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.ESTORE_QTY_UPDATED.'</p>';
            return true;
        }
        else
        {
            $_SESSION['eStore_last_action_msg'] = '<p style="color: red;">'.ESTORE_QUANTITY_LIMIT_EXCEEDED.' '.ESTORE_AVAILABLE_QTY.': '.$ret_product->available_copies.'</p>';            
            $_SESSION['action_msg_set_time'] = time();
            return false;
        }
    }
    else
    {
    	//$_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.ESTORE_QTY_UPDATED.'</p>';
    	return true;
    }
}

function print_wp_digi_cart()
{
    return eStore_shopping_cart_multiple_gateway();
}

function aff_add_custom_field()
{
    $custom_field_val = eStore_get_custom_field_value();
    $output = '<input type="hidden" name="custom" value="'.$custom_field_val.'" id="eStore_custom_values" />';
	return 	$output;
}
function eStore_get_custom_field_value()
{
	$output = '';
	$_SESSION['eStore_custom_values']='';
	if (!empty($_SESSION['ap_id'])){
        $name = 'ap_id';
        $value = $_SESSION['ap_id'];
        $custom_field_val = append_values_to_custom_field($name,$value);
	}
	else if (isset($_COOKIE['ap_id'])){
        $name = 'ap_id';
        $value = $_COOKIE['ap_id'];
        $custom_field_val = append_values_to_custom_field($name,$value);
	}
	if(isset($_COOKIE['c_id'])){
        $name = 'c_id';
        $value = $_COOKIE['c_id'];
        $custom_field_val = append_values_to_custom_field($name,$value);
	}
	if (!empty($_SESSION['eStore_coupon_code'])&& $_SESSION['discount_applied_once'] == 1){
        $name = 'coupon';
        $value = $_SESSION['eStore_coupon_code'];
        $custom_field_val = append_values_to_custom_field($name,$value);
    }
    if (function_exists('wp_eMember_install')){  
        $emember_auth = Emember_Auth::getInstance();
        $user_id = $emember_auth->getUserInfo('member_id');
        if (!empty($user_id))
        {
            $name = 'eMember_id';
            $custom_field_val = append_values_to_custom_field($name,$user_id);
        } 
    }
    $clientip = $_SERVER['REMOTE_ADDR'];
	if (!empty($clientip)){
        $name = 'ip';
        $value = $clientip;
        $custom_field_val = append_values_to_custom_field($name,$value);
    }
    if(!empty($_SESSION['eStore_selected_shipping_option'])){
    	$name = 'ship_option';
        $value = $_SESSION['eStore_selected_shipping_option'];
    	$custom_field_val = append_values_to_custom_field($name,$value);
    }
    if(isset($_SESSION['eStore_store_pickup_checked']) && $_SESSION['eStore_store_pickup_checked'] == '1'){
    	$name = 'store_pickup';
        $value = 'yes';
    	$custom_field_val = append_values_to_custom_field($name,$value);
    }
    $custom_field_val = apply_filters('eStore_custom_field_value_filter', $custom_field_val);
    return $custom_field_val;
}

function append_values_to_custom_field($name,$value)
{
    $custom_field_val = $_SESSION['eStore_custom_values'];
    $new_val = $name.'='.$value;
    if (empty($custom_field_val))
    {
        $custom_field_val = $new_val;
    }
    else
    {
        $custom_field_val = $custom_field_val.'&'.$new_val;
    }
    $_SESSION['eStore_custom_values'] = $custom_field_val;
    return $custom_field_val;
}

function print_wp_digi_cart_button($content)
{
        $pattern = '#\[wp_eStore:product_id:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
            $pattern = '[wp_eStore:product_id:';
            $m = str_replace ($pattern, '', $match);

            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode('|',$m);
    		$key = $pieces[0];

			if (sizeof($pieces) == 1)
			{
				$replacement = get_button_code_for_product($key);
				$content = str_replace ($match, $replacement, $content);
			}
        }
    	return $content;
}

function print_wp_digi_cart_button_for_product($id)
{
	// This function has been deprecated. Use the "get_button_code_for_product" function instead
	$replacement = get_button_code_for_product($id);
    return $replacement;
}

function eStore_print_products_from_category($content)
{
        $pattern = '#\[wp_eStore_category_products:category_id:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
            $pattern = '[wp_eStore_category_products:category_id:';
            $m = str_replace ($pattern, '', $match);

            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode('|',$m);
    		$key = $pieces[0];

			if (sizeof($pieces) == 1)
			{
				$replacement = show_products_from_category($key);
				$content = str_replace ($match, $replacement, $content);
			}
        }
    	return $content;
}

function filter_eStore_buy_now_button($content)
{	
        $pattern = '#\[wp_eStore_buy_now:product_id:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
            $pattern = '[wp_eStore_buy_now:product_id:';
            $m = str_replace ($pattern, '', $match);

            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode('|',$m);
    		$key = $pieces[0];
    		
			if (sizeof($pieces) == 1)
			{
				$replacement = print_eStore_buy_now_button($key);
				$content = str_replace ($match, $replacement, $content);
			}
        }
    	return $content;
}

function filter_eStore_subscribe_button($content)
{
        $pattern = '#\[wp_eStore_subscribe:product_id:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
            $pattern = '[wp_eStore_subscribe:product_id:';
            $m = str_replace ($pattern, '', $match);

            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode('|',$m);
    		$key = $pieces[0];

			if (sizeof($pieces) == 1)
			{
				$replacement = print_eStore_subscribe_button_form($key);
				$content = str_replace ($match, $replacement, $content);
			}
        }
    	return $content;
}

function filter_eStore_free_download_form($content)
{
        $pattern = '#\[wp_eStore_free_download:product_id:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
            $pattern = '[wp_eStore_free_download:product_id:';
            $m = str_replace ($pattern, '', $match);

            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode('|',$m);
    		$key = $pieces[0];

			if (sizeof($pieces) == 1)
			{
				$replacement = eStore_free_download_form($key);
				$content = str_replace ($match, $replacement, $content);
			}
        }
    	return $content;
}

function filter_eStore_free_download_form_ajax($content)
{
        $pattern = '#\[wp_eStore_free_download_ajax:product_id:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {
            $pattern = '[wp_eStore_free_download_ajax:product_id:';
            $m = str_replace ($pattern, '', $match);

            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode('|',$m);
    		$key = $pieces[0];

			if (sizeof($pieces) == 1)
			{
				$replacement = eStore_free_download_form_ajax($key);
				$content = str_replace ($match, $replacement, $content);
			}
        }
    	return $content;
}

function digi_cart_not_empty()
{
        $count = 0;
        if (isset($_SESSION['eStore_cart']) && is_array($_SESSION['eStore_cart']))
        {
            foreach ($_SESSION['eStore_cart'] as $item)
                $count++;
            return $count;
        }
        else
            return 0;
}

function wp_eStore_is_digital_product($ret_product)
{
	//return true if the product is a digital product
	if(!empty($ret_product->product_download_url) && empty($ret_product->shipping_cost) && empty($ret_product->weight)){
		return true;
	}	
	else{
		return false;
	}
}

function get_eStore_currency_price_format() {
	$wp_eStore_config = WP_eStore_Config::getInstance();
	$currency_pos = $wp_eStore_config->getValue('eStore_price_currency_position');
	switch ($currency_pos) 
	{
		case 'left' :
			$format = '%1$s%2$s';
		break;
		case 'right' :
			$format = '%2$s%1$s';
		break;
		case 'left_space' :
			$format = '%1$s&nbsp;%2$s';
		break;
		case 'right_space' :
			$format = '%2$s&nbsp;%1$s';
		break;
	}
	return apply_filters('eStore_currency_price_format_filter', $format, $currency_pos);
}

function format_eStore_price_amount($price, $symbol, $decimal='.', $thousands_sep=',')
{
	$wp_eStore_config = WP_eStore_Config::getInstance();
	$decimal = $wp_eStore_config->getValue('eStore_price_decimal_separator');
	if(empty($decimal)){$decimal = '.';}
	$thousands_sep = $wp_eStore_config->getValue('eStore_price_thousand_separator');
	if(empty($thousands_sep)){$thousands_sep = ',';}
	$num_of_decimals = $wp_eStore_config->getValue('eStore_price_num_decimals');
	$num_of_decimals = intval($num_of_decimals);
	$n_formatted_price = number_format($price, $num_of_decimals, $decimal, $thousands_sep);
	$format_template = get_eStore_currency_price_format();
	$full_formatted_price = sprintf($format_template, $symbol, $n_formatted_price);
	return $full_formatted_price;
}

function print_digi_cart_payment_currency($price, $symbol, $decimal='.', $thousands_sep=',')
{
	//$price = apply_filters('eStore_just_before_price_display_raw_price_amt', $price);
	$output_str = "";
	$args = array ('price'=>$price, 'symbol' => $symbol);
	$output_str = apply_filters('eStore_just_before_price_display', $output_str, $args);
	if(!empty($output_str)){return $output_str;}

	if(is_numeric($price)){//format the price amount
		$full_formatted_price = format_eStore_price_amount($price, $symbol, $decimal, $thousands_sep);
		return $full_formatted_price;
	}
	return $price;
}

function print_digi_cart_payment_currency_with_tax($price, $symbol, $tax_rate='', $decimal='.', $thousands_sep=',')
{
	if(empty($tax_rate))
	{
		$tax_rate = get_option('eStore_global_tax_rate');
	}
	if(is_numeric($price)){//format the price amount
		$tax_included_price = eStore_calculate_tax_included_price_without_qty($price, $tax_rate);
		return print_digi_cart_payment_currency($tax_included_price, $symbol, $decimal, $thousands_sep);
	}
	return $price;
}

function print_tax_inclusive_payment_currency_if_enabled($price, $symbol, $tax_rate='', $ret_product='', $decimal='.', $thousands_sep=',')
{
	if(WP_ESTORE_DISPLAY_TAX_INCLUSIVE_PRICE == '1')
	{
		if(empty($tax_rate) && !empty($ret_product)){//Lets check the product specific tax
			if(!empty($ret_product->tax)){
				$tax_rate = $ret_product->tax;
			}
		}
		$payment_currency = print_digi_cart_payment_currency_with_tax($price,$symbol,$tax_rate,$decimal,$thousands_sep);
		return $payment_currency;
	}
	else
	{
		return print_digi_cart_payment_currency($price, $symbol, $decimal, $thousands_sep);
	}
}

function digi_cart_current_page_url() 
{
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
	    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} 
	else 
	{
	    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}
function get_eStore_smart_thumb($img_url)
{
	$src = WP_ESTORE_LIB_URL.'/timthumb.php?src='.$img_url.'&h=125&w=125&zc=1&q=100';
	return $src;
}

function wp_eStore_clear_cache(){
	if(function_exists('w3tc_pgcache_flush')){
		w3tc_pgcache_flush();
	} 
	else if(function_exists('wp_cache_clear_cache')){
		wp_cache_clear_cache();
	}
}

// Display The Options Page
function wp_digi_cart_admin_menu ()
{
     add_options_page('WP Sell Digital Products', 'WP Sell Digital Products', 'manage_options', __FILE__, 'wp_digi_cart_options');
}

function show_wp_digi_cart_widget($args)
{
	extract($args);

	$cart_title = get_option('wp_eStore_widget_title');
	if (empty($cart_title)) $cart_title = 'Shopping Cart';

	echo $before_widget;
	echo $before_title . $cart_title . $after_title;
	if (get_option('eStore_show_compact_cart'))
	{
		echo eStore_show_compact_cart();
	}
	else
	{
		echo print_wp_digi_cart();
	}
    echo $after_widget;
}

function wp_digi_cart_widget_control()
{
    ?>
    <p>You can create more widgets by adding a standard Text Widget to your sidebar then using one of the shopping cart <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=460" target="_blank">shortcodes</a> in it.</p>
    <?php
}

function widget_wp_digi_cart_init()
{   
    $widget_options = array('classname' => 'widget_wp_digi_cart', 'description' => __( "Display WP Cart For Digital Products.") );
    wp_register_sidebar_widget('wp_digi_cart_widgets', __('WP Cart for Digital Products'), 'show_wp_digi_cart_widget', $widget_options);
    wp_register_widget_control('wp_digi_cart_widgets', __('WP Cart for Digital Products'), 'wp_digi_cart_widget_control' );
}

 
//if(get_option('eStore_enable_fancy_redirection_on_checkout')){
//add_action('wp_head', wp_eStore_load_current_jquery('1.4.2'));
//}
function wp_eStore_load_current_jquery($version) {
        global $wp_scripts;
        if ( ( version_compare($version, $wp_scripts -> registered[jquery] -> ver) == 1 ) && !is_admin() ) {
                wp_deregister_script('jquery');  
                wp_register_script('jquery',
                        'http://ajax.googleapis.com/ajax/libs/jquery/'.$version.'/jquery.min.js',
                        false, $version);
        }
}

function wp_eStore_load_libraries()
{
    global $wp_version;
    wp_enqueue_script('jquery');
    if(WP_ESTORE_ENABLE_AJAX_ON_ADD_TO_CART_BUTTONS === '1'){//add ajax handling scripts
	    wp_enqueue_script('estore-plugins-js', WP_ESTORE_LIB_URL.'/eStore_plugins.js',array(),WP_ESTORE_VERSION);
	    wp_enqueue_script('estore-ajax-js', WP_ESTORE_LIB_URL.'/eStore-ajax.js',array(),WP_ESTORE_VERSION);
	    wp_localize_script('estore-ajax-js', 'eStore_JS', array(
	    'ajaxurl'=>admin_url('admin-ajax.php'), 
	    'add_cart_nonce'=>wp_create_nonce('estore_add_cart_nonce'), 
	    'estore_url'=>WP_ESTORE_URL)
	    );    
    }
    
    if(!is_admin()){   
        wp_enqueue_script('jquery.external.lib.js',WP_ESTORE_LIB_URL.'/jquery.external.lib.js'); 
        wp_enqueue_script('jquery.lightbox',WP_ESTORE_LIB_URL.'/jquery.lightbox-0.5.pack.js');
        if(get_option('eStore_enable_fancy_redirection_on_checkout')){//TODO - use getconfig here
            if(version_compare($wp_version, '3.5', '<')){//fix until jquerytools releases version compatible with jquery 1.8.3
                    wp_enqueue_script('jquery.tools',WP_ESTORE_LIB_URL.'/jquery.tools.min.js');
            }
            else{
                    wp_enqueue_script('jquery.tools',WP_ESTORE_LIB_URL.'/jquery.tools18.min.js');
            }
        }		
    }
    else{ //admin dashboard
    	wp_enqueue_script('estore-admin-js', WP_ESTORE_LIB_URL.'/estore-admin.js');//Admin js code
    	if (isset($_GET['page']))
    	{
		    if ($_GET['page'] == 'wp_eStore_addedit'){//wp eStore add edit page
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
				wp_register_script('eStore-uploader', WP_ESTORE_LIB_URL.'/eStore-uploader-script.js', array('jquery','media-upload','thickbox'));
				wp_enqueue_script('eStore-uploader');	    	
				wp_enqueue_style('thickbox');			
			}
			if ($_GET['page'] == 'wp_eStore_stats' || $_GET['page'] == 'wp_eStore_discounts'){//wp eStore stats or discounts page
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
			}
    	}
    }     	
}

function wp_eStore_head_content()
{	
	echo '<link type="text/css" rel="stylesheet" href="'.WP_ESTORE_URL.'/wp_eStore_style.css?ver='.WP_ESTORE_VERSION.'" />'."\n";
	echo '<link type="text/css" rel="stylesheet" href="'.WP_ESTORE_URL.'/wp_eStore_custom_style.css" />'."\n";
	
	// Add javascript needed variable values in the page
	$debug_marker = "<!-- WP eStore plugin v" . WP_ESTORE_VERSION . " - http://www.tipsandtricks-hq.com/wordpress-estore-plugin-complete-solution-to-sell-digital-products-from-your-wordpress-blog-securely-1059/ -->";
	echo "\n${debug_marker}\n";
        
        $wp_eStore_config = WP_eStore_Config::getInstance();
        $variation_decimal_separator = $wp_eStore_config->getValue('eStore_price_decimal_separator');
        if(!isset($variation_decimal_separator)){$variation_decimal_separator = ".";}
        $variation_thousand_separator = $wp_eStore_config->getValue('eStore_price_thousand_separator');
        if(!isset($variation_thousand_separator)){$variation_thousand_separator = ",";}
        $currency_position = $wp_eStore_config->getValue('eStore_price_currency_position');
        if(!isset($currency_position)){$currency_position = "left";}
        $num_of_decimals = $wp_eStore_config->getValue('eStore_price_num_decimals');
        if(!is_numeric($num_of_decimals)){$num_of_decimals = "2";}
	$js_constants = '<script type="text/javascript">
	JS_WP_ESTORE_CURRENCY_SYMBOL = "'.WP_ESTORE_CURRENCY_SYMBOL.'";
	JS_WP_ESTORE_VARIATION_ADD_STRING = "'.WP_ESTORE_VARIATION_ADD_SYMBOL.'";
        JS_WP_ESTORE_VARIATION_DECIMAL_SEPERATOR = "'.$variation_decimal_separator.'";
	JS_WP_ESTORE_VARIATION_THOUSAND_SEPERATOR = "'.$variation_thousand_separator.'";
        JS_WP_ESTORE_VARIATION_CURRENCY_POS = "'.$currency_position.'";
        JS_WP_ESTORE_VARIATION_NUM_OF_DECIMALS = "'.$num_of_decimals.'";    
	JS_WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER = "'.WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER.'";
        JS_WP_ESTORE_URL = "'.WP_ESTORE_URL.'";';
        if(defined('WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL')){$js_constants .= 'JS_WP_ESTORE_PG_BUNDLE_URL = "'.WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'";';}
	$js_constants .= '</script>';
        echo $js_constants;
	do_action('eStore_before_including_read_form_js');
	echo '<script type="text/javascript" src="'.WP_ESTORE_URL.'/lib/eStore_read_form.js?ver='.WP_ESTORE_VERSION.'"></script>';
}

function wp_eStore_add_footer_code()
{
	if(get_option('eStore_enable_fancy_redirection_on_checkout')){
            //Overlayed element
            $output .= '<div class="eStore_apple_overlay">';
            $output .= '<div class="cart_redirection_contentWrap"></div>';
            $output .= '</div>';	
            echo $output;					
            eStore_load_fancy_overlay_jquery2();
	}	
	if (get_option('eStore_enable_lightbox_effect') != ''){
		eStore_load_lightbox();
	}
	$wp_eStore_config = WP_eStore_Config::getInstance();
	if ($wp_eStore_config->getValue('eStore_enable_save_retrieve_cart')=='1'){
		eStore_load_save_retrieve_cart_jquery();
	}	
	if (WP_ESTORE_APPLY_TAX_FOR_CERTAIN_AREA !== '0'){
		eStore_load_area_specific_tax_jquery();
	}
        if (WP_ESTORE_DO_NOT_APPLY_SHIPPING_FOR_STORE_PICKUP !== '0'){
		eStore_load_store_pickup_jquery();
	}
	if (get_option('eStore_use_multiple_gateways')){
            echo '<script type="text/javascript" src="'.WP_ESTORE_URL.'/lib/estore-multi-gateway-co.js?ver='.WP_ESTORE_VERSION.'"></script>';
	}
	eStore_load_t_and_c_jquery();
	eStore_load_shipping_var_change_warning_jquery();
	eStore_load_cart_qty_change_jquery();	
}

function eStore_tinyMCE_addbutton() 
{
	// check user permission
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
	return;	
	// Add only in Rich Editor mode
	if (get_user_option('rich_editing') == 'true') 
	{
		add_filter("mce_external_plugins", "eStore_tinyMCE_load");
		add_filter('mce_buttons', 'eStore_tinyMCE_register_button');
	}
}
function eStore_tinyMCE_load($plugin_array) 
{
	$plug = WP_ESTORE_LIB_URL . '/editor_plugin.js';
	$plugin_array['wpEstore'] = $plug;
	return $plugin_array;
}
function eStore_tinyMCE_register_button($buttons) 
{
   array_push($buttons, "separator", "wpEstoreButton");
   return $buttons;	
}

//Add the Admin Menus
if (is_admin())
{
	if (get_bloginfo('version') >= 3.0) {
	     define("ESTORE_MANAGEMENT_PERMISSION", "add_users");
	}
	else{
		define("ESTORE_MANAGEMENT_PERMISSION", "edit_themes");
	}	
	function wp_digi_cart_add_admin_menu()
	{
		add_menu_page(__("WP eStore", 'wp_eStore'), __("WP eStore", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, __FILE__, "wp_estore_product_management_menu");
		add_submenu_page(__FILE__, __("Manage WP eStore", 'wp_eStore'), __("Manage Products", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, __FILE__, "wp_estore_product_management_menu");		
		add_submenu_page(__FILE__, __("Add/Edit WP eStore", 'wp_eStore'), __("Add/Edit Products", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_eStore_addedit', "wp_estore_add_product_menu");
		add_submenu_page(__FILE__, __("WP eStore Categories", 'wp_eStore'), __("Categories", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_eStore_categories', "wp_eStore_manage_categories_menu");
		add_submenu_page(__FILE__, __("WP eStore Stats", 'wp_eStore'), __("Stats", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_eStore_stats', "wp_estore_stats_menu");
        add_submenu_page(__FILE__, __("WP eStore Settings", 'wp_eStore'), __("Settings", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_eStore_settings', "wp_estore_settings_menu");
		add_submenu_page(__FILE__, __("WP eStore Admin", 'wp_eStore'), __("Admin Functions", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_eStore_admin', "wp_estore_admin_menu");
		add_submenu_page(__FILE__, __("WP eStore Coupons", 'wp_eStore'), __("Coupons/Discounts", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_eStore_discounts', "wp_estore_discounts_menu");
		add_submenu_page(__FILE__, __("eStore Manage Customers", 'wp_eStore'), __("Manage Customers", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_estore_customer_management', "wp_estore_customer_management_menu");
		add_submenu_page(__FILE__, __("Add/Edit Customers", 'wp_eStore'), __("Add/Edit Customers", 'wp_eStore'), ESTORE_MANAGEMENT_PERMISSION, 'wp_eStore_customer_addedit', "wp_estore_add_customer_menu");
		
		$wp_eStore_config = WP_eStore_Config::getInstance();
		if($wp_eStore_config->getValue('eStore_enable_stats_for_author_role') == '1')
		{
			//Add a stats menu for WP users with author role
			define('ESTORE_STATS_MENU_PERMISSION', "edit_published_posts");
			add_menu_page(__("WP eStore Stats", 'wp_eStore'), __("WP eStore Stats", 'wp_eStore'), ESTORE_STATS_MENU_PERMISSION, 'wp_eStore_stats', "wp_estore_stats_menu");
		}
	}
	//Include menus
	require_once(dirname(__FILE__).'/wp_digi_cart_admin_menu.php');
	require_once(dirname(__FILE__).'/eStore_product_management.php');
	require_once(dirname(__FILE__).'/eStore_categories_menu.php');
	require_once(dirname(__FILE__).'/eStore_discounts_menu.php');
	require_once(dirname(__FILE__).'/eStore_stats_menu.php');
	require_once(dirname(__FILE__).'/eStore_customers_menu.php');
}

// Insert the options page to the admin menu
if (is_admin())
{
	add_action('admin_menu','wp_digi_cart_add_admin_menu');
}

//This is to make sure the WordPress do not try to update eStore from wordpress.org as it is a private plugin
function wp_eStore_restrict_auto_update_check( $r, $url ) {
    if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
            return $r; // Not a plugin update request. Bail immediately.
    $plugins = unserialize( $r['body']['plugins'] );

    if(!is_array($plugins->plugins) || !is_array($plugins->active)){//Bail out
        return $r;
    }
    unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
    unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
    $r['body']['plugins'] = serialize( $plugins );
    return $r;
}
add_filter( 'http_request_args', 'wp_eStore_restrict_auto_update_check', 5, 2 );

function wp_eStore_plugin_conflict_check()
{
	$msg = "";    
	//Check schemea
	$installed_schema_version = get_option("wp_eStore_db_version");
	if($installed_schema_version != WP_ESTORE_DB_VERSION){
		$msg .= '<p>It looks like you did not follow the <a href="http://www.tipsandtricks-hq.com/ecommerce/wordpress-estore-installation-and-usage-guide-31#upgrade" target="_blank">WP eStore upgrade instruction</a> to update the plugin. The database schema is out of sync and need to be updated. Please deactivate the plugin and follow the <a href="http://www.tipsandtricks-hq.com/ecommerce/wordpress-estore-installation-and-usage-guide-31#upgrade" target="_blank">upgrade instruction from here</a> to upgrade the plugin and correct this.</p>';
	}
	    
	$activation_flag_value = get_option('eStore_plugin_activation_check_flag');
    if($activation_flag_value != '1' && empty($msg)){//no need check for conflict	    	
        return;
    }
    		
    $wp_eStore_config = WP_eStore_Config::getInstance();
	if(function_exists('bb2_install')){
		$msg .= '<p>You have the Bad Behavior plugin active! This plugin is known to block PayPal\'s payment notification (IPN). Please see <a href="http://www.tipsandtricks-hq.com/forum/topic/list-of-plugins-that-dont-play-nice-conflicting-plugins" target="_blank">this post</a> for more details.</p>';
	}

	// WP Super cache plugin check
	if (function_exists('wp_cache_serve_cache_file') && $wp_eStore_config->getValue('eStore_do_not_show_sc_warning') != '1'){
		$sc_integration_incomplete = false;
		global $wp_super_cache_late_init;
		if ( false == isset( $wp_super_cache_late_init ) || ( isset( $wp_super_cache_late_init ) && $wp_super_cache_late_init == 0 ) ){
			$sc_integration_incomplete = true;
		}	
		
		if(defined('TIPS_AND_TRICKS_SUPER_CACHE_OVERRIDE')){$sc_integration_incomplete=false;}
		if ($sc_integration_incomplete){		
			$msg .= '<p>You have the WP Super Cache plugin active. Please make sure to follow <a href="http://www.tipsandtricks-hq.com/forum/topic/using-the-plugins-together-with-wp-super-cache-plugin" target="_blank">this instruction</a> to make it work with the WP eStore plugin. You can ignore this message if you have already applied the recommended changes. ';
			$msg .= '<input class="button " type="button" onclick="document.location.href=\'admin.php?page=wp_eStore_settings&estore_hide_sc_msg=1\';" value="Hide this Message">';
			$msg .= '</p>';
		}	
	}	
	if (function_exists('w3tc_pgcache_flush') && class_exists('W3_PgCache')){				
		$integration_in_place = false;
		$w3_pgcache = & W3_PgCache::instance();
	    foreach ($w3_pgcache->_config->get_array('pgcache.reject.cookie') as $reject_cookie) {
	    	if (strstr($reject_cookie,"cart_in_use") !== false){
	    		$integration_in_place = true;
	    	}   	
        }	
        if(!$integration_in_place){
        	$msg .= '<p>You have the W3 Total Cache plugin active. Please make sure to follow <a href="http://www.tipsandtricks-hq.com/forum/topic/using-the-plugins-with-w3-total-cache-plugin" target="_blank">these instructions</a> to make it work with the WP eStore plugin.</p>';
        }	
	}
	
	//Check for duplicate copies of the eStore plugin
	$plugins_list = get_plugins();
	$plugin_names_arrray = array();
	foreach ($plugins_list as $plugin){
		$plugin_names_arrray[] = $plugin['Name'];
	}
	$plugin_unqiue_count = array_count_values($plugin_names_arrray);
	if($plugin_unqiue_count['WP eStore']>1){
		$msg .= '<br />It looks like you have two copies (potentially different versions) of the WP eStore plugin in your plugins directory. This can be the source of many problems. Please delete every copy of the eStore plugin from your plugins directory to clean it out then upload one fresh copy. <a href="http://www.tipsandtricks-hq.com/ecommerce/wordpress-estore-installation-and-usage-guide-31#upgrade" target="_blank">More Info</a><br /><br />';
	}
		
	if(!empty($msg)){
		echo '<div class="updated fade">'.$msg.'</div>';	
	}else{
		//Set this flag so it does not do the conflict check on every admin page load
		update_option('eStore_plugin_activation_check_flag','');
	}
}

function eStore_pre_update_option_active_plugins($new_value)
{
        $old_value = (array) get_option('active_plugins');
        if ($new_value !== $old_value)
        {
            //a plugin was activated or deactivated - do something based on the value of this flag
            update_option('eStore_plugin_activation_check_flag','1');
        }
        return $new_value;
}

function eStore_plugins_loaded_handler()
{
    eStore_misc_loader_handlers();
    eStore_cart_actions_handlers();
    eStore_paypal_ipn_listener();
    eStore_auth_net_ipn_processor_listener();
    eStore_paypal_pdt_listener();
    eStore_gateway_specific_buy_now_submit_listener();
    if(defined('WP_PAYMENT_GATEWAY_BUNDLE_VERSION')){//payment gateway bundle plugin hooks
        add_action('wp_pg_recurring_payment_charged','wp_eStore_handle_recurring_payment_charged_action',10,2);
        add_action('wp_pg_last_recurring_payment_charged','wp_eStore_handle_recurring_payment_charged_action',10,2);
        add_action('wp_pg_subscription_eot','wp_eStore_handle_subscription_eot_action',10,2);
    }
    add_action('eStore_process_refund','eStore_handle_refund');
}

function eStore_wp_loaded_handler()
{
    if(isset($_REQUEST['eStore_checkout']) && $_REQUEST['eStore_checkout']=="process")//Process checkout request
    {
        include_once('eStore_payment_submission.php');
        exit;
    }
    if(isset($_REQUEST['enc_dl_action']) && $_REQUEST['enc_dl_action']=="process"){//Process download
        include_once('download.php');
        exit;
    }
}

add_action('plugins_loaded','eStore_plugins_loaded_handler');
add_action('wp_loaded','eStore_wp_loaded_handler');

add_action('init', 'wp_eStore_load_libraries');
add_action('init', 'eStore_tinyMCE_addbutton');
add_action('init', 'widget_wp_digi_cart_init');

add_filter('wp_list_pages_excludes', 'estore_exclude_page_handler');
add_filter('pre_update_option_active_plugins', 'eStore_pre_update_option_active_plugins' );//This function will get fired when a plugin activation or deactivation occurs
add_filter('the_content', 'print_wp_digi_cart_button',11);//Deprecated
//add_filter('the_content', 'eStore_fancy_product_display',11);//Deprecated
//add_filter('the_content', 'eStore_fancy_product_display2');//Deprecated
add_filter('the_content', 'eStore_display_all_products_stylish',11);
add_filter('the_content', 'eStore_print_products_from_category',11);
add_filter('the_content', 'filter_eStore_buy_now_button',11);//Deprecated
add_filter('the_content', 'filter_eStore_free_download_form',11);
add_filter('the_content', 'filter_eStore_free_download_form_ajax',11);
add_filter('the_content', 'filter_eStore_subscribe_button',11);//Deprecated
//add_filter('the_content', 'wp_digi_cart_show');//Deprecated
add_filter('the_content', 'filter_eStore_transaction_result');//Deprecated
add_filter('the_content', 'do_shortcode',11);
if (!is_admin())
{add_filter('widget_text', 'do_shortcode');}
add_filter('the_excerpt', 'do_shortcode',11);

add_shortcode('wp_eStore_add_to_cart', 'wp_eStore_add_to_cart_handler');
add_shortcode('wp_eStore_buy_now_button', 'wp_eStore_buy_now_handler');
add_shortcode('wp_eStore_subscribe_button', 'wp_eStore_subscribe_handler');
add_shortcode('wp_eStore_cart', 'wp_digi_cart_always_show');
add_shortcode('wp_eStore_cart_fancy1', 'eStore_shopping_cart_fancy1');
add_shortcode('wp_eStore_cart_fancy1_when_not_empty', 'eStore_shopping_cart_fancy1_when_not_empty');
add_shortcode('wp_eStore_cart_when_not_empty', 'eStore_cart_when_not_empty');
add_shortcode('wp_eStore_cart_fancy2', 'eStore_shopping_cart_fancy2');
add_shortcode('wp_eStore_list_products', 'wp_estore_products_table');
add_shortcode('wp_eStore_list_categories_fancy', 'wp_estore_display_categories_fancy');
add_shortcode('wp_eStore_category_fancy', 'wp_estore_display_category_fancy');
add_shortcode('wp_eStore_fancy1', 'eStore_fancy1');
add_shortcode('wp_eStore_fancy2', 'eStore_fancy2');
add_shortcode('wp_eStore_buy_now_fancy', 'eStore_buy_now_fancy');
add_shortcode('wp_eStore_subscribe_fancy', 'eStore_subscribe_fancy');
add_shortcode('wp_eStore_sale_counter', 'eStore_sale_counter');
add_shortcode('wp_eStore_remaining_copies_counter', 'eStore_remaining_copies_counter');
add_shortcode('wp_eStore_download_now_button', 'eStore_download_now_button');
add_shortcode('wp_eStore_download_now_button_fancy', 'eStore_download_now_button_fancy');
add_shortcode('wp_eStore_download_now_button_fancy_no_price', 'eStore_download_now_button_fancy_no_price_handler');
add_shortcode('wp_eStore_buy_now_custom_button', 'wp_eStore_buy_now_custom_button_handler');
add_shortcode('wp_eStore_members_purchase_history', 'wp_eStore_members_purchase_history_handler');
add_shortcode('wp_eStore_members_purchase_history_with_download', 'wp_eStore_members_purchase_history_with_download_handler');
add_shortcode('wp_eStore_APR', array('eStore_aprtp', 'shortcode_api'));
add_shortcode('wp_eStore_on_page_manual_gateway_form', 'wp_eStore_on_page_manual_gateway_form_handler');
add_shortcode('wp_eStore_product_details', 'wp_eStore_product_details_handler');
add_shortcode('wp_eStore_free_download_squeeze_form', 'wp_eStore_free_download_squeeze_form_handler');
add_shortcode('wp_eStore_save_retrieve_cart', 'wp_eStore_save_retrieve_cart_handler');
add_shortcode('wp_eStore_display_transaction_result', 'eStore_display_transaction_result');//TODO - the doc needs to be updated
add_shortcode('wp_eStore_order_summary', 'eStore_show_order_summary');//TODO - update doc (shortcode list)
add_shortcode('wp_eStore_buy_now_for_specific_gateway', 'wp_eStore_buy_now_for_specific_gateway_handler');//TODO - update doc (shortcode list)
add_shortcode('wp_eStore_donate', 'eStore_donate_button_code');//TODO - update doc (shortcode list)

if (is_admin()){
	add_action('admin_notices', 'wp_eStore_plugin_conflict_check');
}
add_action('wp_head', 'wp_eStore_head_content');
add_action('wp_footer', 'wp_eStore_add_footer_code');
