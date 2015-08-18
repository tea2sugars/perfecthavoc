<?php

define('WP_ESTORE_ENABLE_ADVANCED_NGG_FILE_SERVE', false);
define('WP_ESTORE_CART_CHECKOUT_ITEM_LIMIT', 0);//0 disables any limit (specifying a number here will enable a shopping cart item limit)
define('WP_ESTORE_USE_PURE_JS_FOR_CHECKOUT_AUTO_SUBMISSION', '1');//Set it to 1 to enable this
define('WP_ESTORE_RESET_CART_ON_WP_INSTALL_CHANGE', '0');//Set it to 1 to enable this
define('WP_ESTORE_NO_COMMISSION_FOR_SELF_PURCHASE', '0');//Set it to 1 to enable this
define('WP_ESTORE_CHECK_LEADS_TABLE_FOR_AFFILIATE_REFERRAL_CHECK', '0');//Set it to 1 to enable this
define('WP_ESTORE_CHECK_EMEMBER_REFERRER_FOR_AFFILIATE_ID', '0');//Set it to 1 to enable this
define('WP_ESTORE_DISPLAY_ORIGINAL_ITEM_PRICE_BEFORE_COUPON', '0');//Set it to 1 to enable this
define('WP_ESTORE_APPLY_TAX_FOR_CERTAIN_AREA', '0');//Set it to an area or country name if you want to use it (for example: 'I am from California')
define('WP_ESTORE_SHOW_UPDATE_BUTTON_FOR_QTY_CHANGE', '0');//Set it to 1 to enable this option
define('WP_ESTORE_RESET_CART_ACTION_MSG_ON_PAGE_RELOAD', '0');//Set it to 1 to enable this opton (maybe keep this option enabled by default in the future?)
define('WP_ESTORE_SERIAL_KEY_SEPARATOR', ',');
define('WP_ESTORE_OPEN_IN_NEW_WINDOW_THANKU_DL_LINKS', '0');//Set it to 1 to enable this (opens the download links on the thank you page in new window)
define('WP_ESTORE_DO_NOT_SEND_EMAIL_FROM_SQUEEZE_FORM', '0');//Set it to 1 to enable this
define('WP_ESTORE_DO_NOT_CHECK_URL_VALIDITY', '0');//Set it to 1 to enable this
define('WP_ESTORE_USE_AUTH_NET_ALT_REDIRECTION', '0');//Set it to 1 to enable this
define('WP_ESTORE_ALLOW_COUPON_STACKING', '0');//Set it to 1 to enable this - Warning: enabling this will allow your customers to apply coupons multiple times
define('WP_ESTORE_SHOW_REGO_COMPLETION_LINK_ON_TY_PAGE', '0');//Set it to 1 to enable this
define('WP_ESTORE_MUST_UPDATE_SHIPPING_SELECTION_TO_VIEW_SHIPPING', '0');//Set it to 1 to enable this
define('WP_ESTORE_STAMP_PDF_FILE_AT_DOWNLOAD_TIME', '0');//Set it to 1 to enable this
define('WP_ESTORE_VALIDATE_PAYPAL_PDT_USING_CURL', '1');//Set it to 1 to enable this
define('WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE', '0');//Example value "US"
define('WP_ESTORE_SAVE_SESSION_TO_COOKIE', false);//TODO - deprecate this as the WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION option becomes the new default

/* Simple php mail config */
$email_config['protocol'] = 'mail';

/*** Do not Edit Below This line ***/

/* Options that have been moved to settings menu - set these options from eStore's settings menu */
$wp_eStore_config = WP_eStore_Config::getInstance();
if ($wp_eStore_config->getValue('eStore_show_tax_inclusive_price')=='1'){
	define('WP_ESTORE_DISPLAY_TAX_INCLUSIVE_PRICE', '1');
}else{
	define('WP_ESTORE_DISPLAY_TAX_INCLUSIVE_PRICE', '0');
}
define('WP_ESTORE_REDIRECT_COMMISSION_USING_SATELLITE_AFFILIATE_PLUGIN', false);//The plugin will automatically determine this
if ($wp_eStore_config->getValue('eStore_auto_shorten_dl_links')=='1'){
	define('WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS', true);
}else{
	define('WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS', false);
}

define('WP_ESTORE_SHOW_CURRENCY_SYMBOL_AFTER_AMOUNT', '0');//Check the eStore advanced settings menu for this option

if ($wp_eStore_config->getValue('eStore_use_new_checkout_redirection')=='1'){
    define('WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION', '1');
}else{
    define('WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION', '0');
}

if ($wp_eStore_config->getValue('eStore_use_custom_text_for_thank_you_page_dl')=='1'){
    define('WP_ESTORE_USE_ANCHOR_FOR_THANKU_DL_LINKS', '1');
}else{
    define('WP_ESTORE_USE_ANCHOR_FOR_THANKU_DL_LINKS', '0');
}

if ($wp_eStore_config->getValue('eStore_use_ajax_on_add_to_cart_buttons')=='1'){
    define('WP_ESTORE_ENABLE_AJAX_ON_ADD_TO_CART_BUTTONS', '1');
}else{
    define('WP_ESTORE_ENABLE_AJAX_ON_ADD_TO_CART_BUTTONS', '0');
}

if ($wp_eStore_config->getValue('eStore_enable_store_pickup')=='1'){
    define('WP_ESTORE_DO_NOT_APPLY_SHIPPING_FOR_STORE_PICKUP', WP_ESTORE_STORE_PICKUP_LABEL);
}else{
    define('WP_ESTORE_DO_NOT_APPLY_SHIPPING_FOR_STORE_PICKUP', '0');
}

/* If you want to set one gateway as the default selected gateway then uncomment the following line 
* and use one of "authorize", "manual" or "2co" values to make that gateway as the default selected one in the cart */
//$_COOKIE['eStore_gateway'] = "authorize";

if (function_exists('is_multisite') && is_multisite()) 
{		
	$blog_id = $wpdb->blogid;
	if(isset($_COOKIE['eStore_cart_blog_id'])){
		if($_COOKIE['eStore_cart_blog_id'] !=$blog_id){
			$cookie_domain = COOKIE_DOMAIN;//eStore_get_top_level_domain();	
	    	setcookie("eStore_cart_blog_id",$blog_id,time()+7200,"/",$cookie_domain);						
			reset_eStore_cart();			
		}
	}
	else{
		$cookie_domain = COOKIE_DOMAIN;//eStore_get_top_level_domain();	
	    setcookie("eStore_cart_blog_id",$blog_id,time()+7200,"/",$cookie_domain);  
	}
}

function wp_eStore_check_cookie_flag_and_store_values()
{
	if(WP_ESTORE_SAVE_SESSION_TO_COOKIE){
		wp_eStore_save_session_values_to_cookie();
	}
}
function wp_eStore_save_session_values_to_cookie()
{
	$domain_url = $_SERVER['SERVER_NAME'];
	$cookie_domain = str_replace("www","",$domain_url);  
	$cookie_life_time = time() + 86400;  
	$serialized_string = base64_encode(serialize( $_SESSION['eStore_cart'])) ;
	setcookie('eStore_cart',$serialized_string,$cookie_life_time,"/",$cookie_domain);  
	$_SESSION['eStore_cart_sub_total'] = eStore_get_cart_total();
    setcookie('eStore_cart_sub_total',$_SESSION['eStore_cart_sub_total'],$cookie_life_time,"/",$cookie_domain);
    $_SESSION['eStore_cart_postage_cost'] = eStore_get_cart_shipping();
    setcookie('eStore_cart_postage_cost',$_SESSION['eStore_cart_postage_cost'],$cookie_life_time,"/",$cookie_domain);  
    $_SESSION['eStore_cart_total_tax'] = eStore_get_cart_tax();   
    setcookie('eStore_cart_total_tax',$_SESSION['eStore_cart_total_tax'],$cookie_life_time,"/",$cookie_domain);
    setcookie('eStore_custom_values',$_SESSION['eStore_custom_values'],$cookie_life_time,"/",$cookie_domain);
    setcookie('eStore_coupon_code',$_SESSION['eStore_coupon_code'],$cookie_life_time,"/",$cookie_domain);
    setcookie('eStore_selected_shipping_option',$_SESSION['eStore_selected_shipping_option'],$cookie_life_time,"/",$cookie_domain);
}
function wp_eStore_load_session_values_from_cookie()
{
	$_SESSION['eStore_cart'] = unserialize(base64_decode($_COOKIE['eStore_cart']));
	$_SESSION['eStore_cart_sub_total'] = $_COOKIE['eStore_cart_sub_total'];
	$_SESSION['eStore_cart_postage_cost'] = $_COOKIE['eStore_cart_postage_cost'];
	$_SESSION['eStore_cart_total_tax'] = $_COOKIE['eStore_cart_total_tax'];
	$_SESSION['eStore_custom_values'] = $_COOKIE['eStore_custom_values'];
	$_SESSION['eStore_coupon_code'] = $_COOKIE['eStore_coupon_code'];
	$_SESSION['eStore_selected_shipping_option'] = $_COOKIE['eStore_selected_shipping_option'];	
}
