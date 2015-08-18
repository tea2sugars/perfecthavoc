<?php
include_once ('lib/gateway/Paypal.php');
include_once ('lib/gateway/TwoCo.php');
include_once ('lib/gateway/Authorize.php');
if (!defined('ABSPATH')){include_once ('../../../wp-load.php');}

if(WP_ESTORE_SAVE_SESSION_TO_COOKIE){
	wp_eStore_load_session_values_from_cookie();
}

if(get_option('eStore_enable_fancy_redirection_on_checkout')){
	if(isset($_COOKIE['eStore_submit_payment'])){
		if($_COOKIE['eStore_submit_payment'] != "true"){
			exit;
		}		
	}
}

if($_SESSION['eStore_cart_sub_total'] <= 0)
{
	if(get_option('eStore_use_manual_gateway_for_zero_dollar_co') == 1){
		$_REQUEST['eStore_gateway'] = 'manual';
	}
}

eStore_co_member_log_in_requirement_check();
function eStore_co_member_log_in_requirement_check(){
	if(get_option('eStore_eMember_must_be_logged_to_checkout') == 1){
	    if (function_exists('wp_eMember_install')){
		    $emember_auth = Emember_Auth::getInstance();
		    $user_id = $emember_auth->getUserInfo('member_id');
		    if (empty($user_id))//User is not logged in
		    {
		    	$redirection_url = get_option('eStore_eMember_redirection_url_when_not_logged');
		    	if(empty($redirection_url)){
		    		echo "Error Detected! If you want to use the 'Only Allow Logged In Members to Checkout' feature then you must specify a value in the 'Redirection URL for Anonymous Checkout' field also.";
		    		exit;
		    	}
		    	if(get_option('eStore_enable_fancy_redirection_on_checkout')){
					ob_start();	
					wp_eStore_redirector_header();
					wp_eStore_redirector_body();		    		
					$click_text = WP_ESTORE_CLICK_HERE;
					echo "<form id=\"gateway_form\" method=\"POST\" name=\"gateway_form\" action=\"" . $redirection_url . "\">";
					echo "<input type=\"hidden\" name=\"wp_eStore_eMember_redirect\" value=\"1\"/>\n";
					echo "<input type=\"submit\" value=\"$click_text\">";
					echo "</form>";
					wp_eStore_redirector_footer();
					$eStore_redirector_output = ob_get_contents();
					ob_end_clean();    
					echo $eStore_redirector_output;  
					exit;
				}
				eStore_redirect_to_url($redirection_url);//header('Location: ' . $redirection_url);
				exit;
		    }
	    }
	    else{
	    	echo "Error! You don't have the WP eMember plugin installed! You can only use the 'Only Allow Logged In Members to Checkout' feature with the WP eMember plugin.";
	    	exit;
	    }	
	}
}

//Process gateway submission
eStore_co_process_gateway_submission();
function eStore_co_process_gateway_submission()
{
    if(isset($_REQUEST['eStore_gateway'])){
        $gateway_to_submit = $_REQUEST['eStore_gateway'];
        eStore_payment_submission_switch($gateway_to_submit);
    }
    else if(isset($_COOKIE['eStore_gateway'])){
        $gateway_to_submit = $_COOKIE['eStore_gateway'];
        eStore_payment_submission_switch($gateway_to_submit);
    }
    else{
        eStore_show_redirection_message();
        submit_to_paypal();	
    }

    wp_eStore_redirector_footer();
    $eStore_redirector_output = ob_get_contents();
    ob_end_clean();    
    echo $eStore_redirector_output;  
    exit;
}

function eStore_payment_submission_switch($gateway_to_submit)
{
	do_action('eStore_pre_payment_submission_hook',$gateway_to_submit);
    switch ($gateway_to_submit) {
        case 'paypal':   
        	eStore_show_redirection_message();     	
            submit_to_paypal();
            break;
        case 'manual':
        	eStore_show_redirection_message();
        	submit_to_manual();
            break;
        case '2co':
        	eStore_show_redirection_message();
            submit_to_2co();
            break;
        case 'authorize':
        	eStore_show_redirection_message();
            submit_to_authorize();
            break; 
        case 'gco':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=gco&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
            break;   
        case 'pppro':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=pppro&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
            break;    
        case 'sagepay':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=sagepay&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
            break;     
        case 'auth_aim':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=auth_aim&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
            break;
        case 'eway':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=eway&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
            break;  
        case 'epay_dk':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=epay_dk&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
        case 'verotel':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=verotel&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;	        
            break;
        case 'freshbooks':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=freshbooks&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
            break;
        case 'ccbill':
	    	$checkout_url = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/process-checkout.php?wp_pg_gateway=ccbill&is_eStore_co=1&auto_submit=1';
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
            break;
		default:
			do_action('eStore_payment_submission_gateway_hook',$gateway_to_submit);
			eStore_show_redirection_message();
            submit_to_paypal();			
			break;                   
    }	
}

function eStore_show_redirection_message()
{
	ob_start();	
	wp_eStore_redirector_header();
	wp_eStore_redirector_body();	
}

function wp_eStore_redirector_header()
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head>';
	echo "<title>".WP_ESTORE_PROCESSING_ORDER."</title>";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';	
	?>	
	<style type="text/css">
	#container{	max-width:450px;margin-left: auto;margin-right: auto;}
	#redirection_canvas{padding:20px;border: 1px solid #CCCCCC;-moz-border-radius:5px; -khtml-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; margin:-3px;}
	#redirection_body_content {background-color:#fff; color: #504945; height:267px; margin:5px; padding:20px; line-height:22px;}
	.redirection_header{color:#227ABE; text-align:center; font:14px Georgia,Arial; font-weight:bold; line-height:22px;}
	.eStore_checkout_click_here_button{background-color:#EFF1F2; color:#4f4f4f;	-webkit-border-radius:15px; -moz-border-radius:15px; -khtml-border-radius:15px;  border-radius:15px; border:1px solid #ccc; box-shadow: 1px 1px 1px #DDDDDD; -moz-box-shadow:1px 1px 1px #DDDDDD; -webkit-box-shadow:1px 1px 1px #DDDDDD; padding: 4px 12px; cursor:pointer;}	
	</style>
	<?php	
	echo "</head>";	
}
function wp_eStore_redirector_body()
{
	echo '<body>';
	echo '<div id="container"><div id="redirection_canvas"><div id="redirection_body_content">';
	
	//Check if session is working for eStore or if the cart is empty
	if(!isset($_SESSION['eStore_cart']) || empty($_SESSION['eStore_cart']))
	{
		echo '<br /><strong>Your shopping cart is empty. Please add an item to your cart. You can load the checkout page and make sure the shopping cart is not empty.</strong><br />';
		$products_page = get_option('eStore_products_page_url');
		if(!empty($products_page)){
			echo '<br /><a href="'.$products_page.'">Go to Products Page</a><br />';
		}
		echo '<br /><strong>If you are the admin of this site and you are certain that the shopping cart is not empty then the PHP Session on your server is not working correctly. Please check this <a href="http://www.tipsandtricks-hq.com/forum/topic/php-session-not-working-correctly" target="_blank">article</a></strong><br /><br />';
		echo '</div></div></div>';
		echo "</body></html>";
		exit;
	}
	
	$loader_img_src = WP_ESTORE_URL.'/images/ajax-loader-2.gif';
	echo "<div class=\"redirection_header\">".WP_ESTORE_ORDER_BEING_PROCESSED."</div>";
	echo '<br /><p style="text-align:center;"><img src="'.$loader_img_src.'" id="eStore_spinner" alt="'.WP_ESTORE_PROCESSING_ORDER.'" /></p>';
	echo "<p style=\"text-align:center;\"><br/>".WP_ESTORE_NOT_AUTO_REDIRECT."<br/></p>\n<br />";		
	echo "<div style=\"text-align:center;\">";
}
function wp_eStore_redirector_footer()
{
	echo '</div>';//end of the main body secion (gateway form button goes in this section)
	echo '</div></div></div>';
	wp_eStore_load_auto_form_submitter();
	echo "</body>";		
	echo "</html>";	
}
function wp_eStore_load_auto_form_submitter()
{	
	if(WP_ESTORE_USE_PURE_JS_FOR_CHECKOUT_AUTO_SUBMISSION == '1')
	{
		?>
		<script type="text/javascript">				
		var user_agt = navigator.userAgent.toLowerCase();
		if (user_agt.indexOf("msie") != -1 || user_agt.indexOf("firefox") != -1)
		{
			document.forms['gateway_form'].submit();
			var spinner_img_src = document.getElementById('eStore_spinner').src;
			document.getElementById('eStore_spinner').src = spinner_img_src;
		}	
		else
		{
			setTimeout("document.forms['gateway_form'].submit()",500);
		}		
		</script>	
		<?php 	
	}
	else
	{
		if ($_SERVER['HTTPS'] == 'on') {
			echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>';
		}
		else{
			echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>';
		}	
		?>	
		<script type="text/javascript">
		jQuery(document).ready(function($) {
		$(function() {			
			setTimeout("$('#gateway_form').submit()", 800);
		 });
		 });
		</script>
		<?php	
	}
}

function submit_to_authorize()
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $eStore_default_currency = get_option('cart_payment_currency');
    $eStore_return_url = get_option('cart_return_from_paypal_url');
    $eStore_sandbox_enabled = get_option('eStore_cart_enable_sandbox');
	
    $myAuthorize = new Authorize();
    
    $authorize_login_id = get_option('eStore_authorize_login');
    $authorize_tx_key = get_option('eStore_authorize_tx_key');
    $myAuthorize->setUserInfo($authorize_login_id, $authorize_tx_key);

    if (get_option('eStore_auto_product_delivery') != '')
    {
        $notify = WP_ESTORE_URL.'/eStore_auth_ipn.php';
        if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
            $notify = WP_ESTORE_SITE_HOME_URL.'/?estore_auth_ipn=process';
    	}
        
        $myAuthorize->addField('x_Relay_URL', $notify);
    }

	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;    
    $x_Description = "Cart Checkout: ";
    $count = 1;
    foreach ($_SESSION['eStore_cart'] as $item)
    {
        $id = $item['item_number'];
        $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
            	
    	if($count > 1){$x_Description .= ", ";}
    	$x_Description .= $item['item_number'];
    	
		$rounded_price = round($item['price'], 2);
        $item_name = substr(htmlspecialchars($item['name']), 0, 30);
		$line_item_val = $item['item_number']."<|>".$item_name."<|><|>".$item['quantity']."<|>".$rounded_price."<|>"."N";
        $myAuthorize->addField('x_line_item-'.$count, $line_item_val);
        $count++;
    }
    
    //Specify the url where auth.net will return the customer after payment
    $total_items_in_cart = count($_SESSION['eStore_cart']);
    if($total_items_in_cart == 1 && !empty($ret_product->return_url)){    	
    	//$myAuthorize->addField('x_receipt_link_URL', $ret_product->return_url);
    	$eStore_return_url = $ret_product->return_url;
    }
    if (!empty($eStore_return_url)){//Set the return URL
    	$myAuthorize->addField('x_receipt_link_URL', $eStore_return_url);
    }

	//TODO - new auth.net redirection method
	if(WP_ESTORE_USE_AUTH_NET_ALT_REDIRECTION==='1'){
		$myAuthorize->addField('x_receipt_link_method', "GET");
		$myAuthorize->addField('x_receipt_link_text', "Click Here to Complete Transaction");
		$myAuthorize->addField('x_Relay_Response', "FALSE");
	}

    // Shipping
    $myAuthorize->addField('x_freight', $_SESSION['eStore_cart_postage_cost']);
    // Tax
    if(!empty($_SESSION['eStore_cart_total_tax'])){
    	$myAuthorize->addField('x_tax', round($_SESSION['eStore_cart_total_tax'], 2));
    }  
    else{  
    	$myAuthorize->addField('x_tax', 0);
    }
    // Duty
    $myAuthorize->addField('x_duty', 0);    
    // Total
    $total = round(($_SESSION['eStore_cart_sub_total'] + $_SESSION['eStore_cart_postage_cost'] + $_SESSION['eStore_cart_total_tax']),2);
    $myAuthorize->addField('x_Amount', $total);
    //If currency code is set then the "x_fp_hash" algorithm need to be updated to include this field.
	//$myAuthorize->addField('x_currency_code', $eStore_default_currency);

	// Description
    $myAuthorize->addField('x_Description', $x_Description);

    //$val = "item2<|>cricket bag<|>Wilson cricket carry bag, red<|>1<|>39.99<|>N";
    $myAuthorize->addField('x_Invoice_num', rand(1, 100));
    
    //Generate Customer ID
    $custId = uniqid();
    $myAuthorize->addField('x_Cust_ID', $custId);
	
    // Enable test mode if needed
    if($eStore_sandbox_enabled)
        $myAuthorize->enableTestMode();

    // Save the order details
    eStore_save_order_details($custId);
  
    // Lets clear the cart
    reset_eStore_cart();

    // Let's start the train!
    $myAuthorize->submitPayment2(WP_ESTORE_CLICK_HERE);  
}

function submit_to_2co()
{
	$eStore_default_currency = get_option('cart_payment_currency');
	$eStore_return_url = get_option('cart_return_from_paypal_url');
	$eStore_sandbox_enabled = get_option('eStore_cart_enable_sandbox');    
    $my2CO = new TwoCo();

    // Specify your 2CheckOut vendor id
    $vendor_id = get_option('eStore_2co_vendor_id');
    $my2CO->addField('sid', $vendor_id);

    if (!empty($eStore_default_currency)){
        $tco_currency = $eStore_default_currency;
    }
    else{
        $tco_currency = 'USD';
    }
    $tco_currency = apply_filters('eStore_change_curr_code_before_payment_filter', $tco_currency);
    $my2CO->addField('tco_currency', $tco_currency);//use list_currency 
    
    // Save the order details
    $uniqueId = uniqid();        
    eStore_save_order_details($uniqueId);
    
    // Specify the order information
    $my2CO->addField('id_type', '1');
    $my2CO->addField('cart_order_id', $uniqueId);

    // =======================
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;    
    $count = 1;
    $total = 0;
    foreach ($_SESSION['eStore_cart'] as $item)
    {
        $id = $item['item_number'];
        $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
            	
   		$rounded_price = round($item['price'], 2);   		  		
        $my2CO->addField("c_name_$count", htmlspecialchars($item['name']));
        $my2CO->addField("c_description_$count", htmlspecialchars($item['name']));
        $my2CO->addField("c_price_$count", $rounded_price);
        //$my2CO->addField("quantity_$count", $item['quantity']);
        //$my2CO->addField("c_prod_$count", $item['item_number']);
        $my2CO->addField("c_prod_$count", $item['item_number'].','.$item['quantity']);
        if(empty($item['shipping']))
        {
            $my2CO->addField("c_tangible_$count", 'N');
        }
        else
        {
            $my2CO->addField("c_tangible_$count", 'Y');
        }
        $total += $rounded_price * $item['quantity'];
        $count++;
    }
    
    //Specify the url where 2CO will return the custoemr after payment
    $total_items_in_cart = count($_SESSION['eStore_cart']);
    if($total_items_in_cart == 1 && !empty($ret_product->return_url)){    	
    	$my2CO->addField('return_url', $ret_product->return_url);
    }
    else if (!empty($eStore_return_url)){
    	$my2CO->addField('return_url', $eStore_return_url);
    }    
        
    if ($_SESSION['eStore_cart_postage_cost'] > 0)
    {
   		$rounded_shipping = round($_SESSION['eStore_cart_postage_cost'], 2);
        $my2CO->addField("c_name_$count", "Shipping");
        $my2CO->addField("c_description_$count", "Shipping");
        $my2CO->addField("c_price_$count", $rounded_shipping);
        $my2CO->addField("c_prod_$count", "SHIPPING".','."1");    
    }
    $grand_total = $total+$rounded_shipping+$_SESSION['eStore_cart_total_tax'];
    $grand_total = round($grand_total,2);
    $my2CO->addField('total', $grand_total);
    //$my2CO->addField('sh_cost', $_SESSION['eStore_cart_postage_cost']);

    //========================
    //$my2CO->addField('pay_method', "CC");
    //$my2CO->addField('skip_landing', "1");
    //========================

    if (get_option('eStore_auto_product_delivery') != '')
    {
        $notify = WP_ESTORE_URL.'/eStore_2co_ipn.php';
        $my2CO->addField('x_Receipt_Link_URL', $notify);
    }

    $custom_field_val = eStore_get_custom_field_value();
    $my2CO->addField('custom', $custom_field_val);

    // Enable test mode if needed
    if($eStore_sandbox_enabled)
    {
        $my2CO->enableTestMode();
    }

    // Lets clear the cart
    reset_eStore_cart();
    
    $my2CO->submitPayment2(WP_ESTORE_CLICK_HERE);
}

function submit_to_paypal()
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $eStore_default_currency = get_option('cart_payment_currency');
    $eStore_return_url = get_option('cart_return_from_paypal_url');
    $eStore_sandbox_enabled = get_option('eStore_cart_enable_sandbox');
	
    if (!empty($eStore_default_currency))
        $paypal_currency = $eStore_default_currency;
    else
        $paypal_currency = 'USD';

    $email = get_option('cart_paypal_email');

    $myPaypal = new Paypal();
    $myPaypal->gatewayUrl = 'https://www.paypal.com/cgi-bin/webscr';//PAYPAL_LIVE_URL
    
    $myPaypal->addField('charset', "utf-8");
    $myPaypal->addField('business', $email);
    $paypal_currency = apply_filters('eStore_change_curr_code_before_payment_filter', $paypal_currency);
    $myPaypal->addField('currency_code', $paypal_currency);    
    
    $cancel_url =  get_option('cart_cancel_from_paypal_url');
    if(!empty($cancel_url)){$myPaypal->addField('cancel_return', $cancel_url);}
	
    if (get_option('eStore_auto_product_delivery') != ''){
    	if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
    		$notify = WP_ESTORE_SITE_HOME_URL.'/?estore_pp_ipn=process';
    	}else{
        	$notify = WP_ESTORE_URL.'/paypal.php';
    	}
        $myPaypal->addField('notify_url', $notify);
    }

    // =======================
    global $wpdb;
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $weight = 0;    
    $count = 1;
    $all_items_digital = true;
    foreach ($_SESSION['eStore_cart'] as $item)
    {
        $rounded_price = round($item['price'], 2);
        $rounded_price = apply_filters('eStore_change_price_before_payment_filter', $rounded_price);
        $myPaypal->addField("item_name_$count", htmlspecialchars($item['name']));
        $myPaypal->addField("amount_$count", $rounded_price);
        $myPaypal->addField("quantity_$count", $item['quantity']);
        $myPaypal->addField("item_number_$count", $item['item_number']);
        
        //Check to see if this is a tax free item and set the tax accordingly so that the profile based PayPal tax can work nicely
        if($item['tax'] == "0"){
            $myPaypal->addField("tax_$count", $item['tax']);
        }
        
        $id = $item['item_number'];
        $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
        if(!empty($ret_product->weight)){
            $weight += ($ret_product->weight * $item['quantity']);
        }
        if(empty($item['digital_flag'])){$all_items_digital = false;}
        
        $count++;
    }

    $total_items_in_cart = count($_SESSION['eStore_cart']);
    if($total_items_in_cart == 1 && !empty($ret_product->return_url)){    	
    	$myPaypal->addField('return', $ret_product->return_url);
    }
    else if (!empty($eStore_return_url)){
    	$myPaypal->addField('return', $eStore_return_url);
    }

    if (!get_option('eStore_paypal_profile_shipping'))
    {
        //Not Using paypal's profile based shipping so include shipping otherwise ignore shipping here as it will be calculated on paypal's site
        $shipping = round($_SESSION['eStore_cart_postage_cost'], 2);
        if(!empty($shipping))//This condition is true for even a vlaue of $0.01
        {
   	    	$shipping = apply_filters('eStore_change_shipping_before_payment_filter', $shipping); //change tax amount before submitting if converting currency to another type
        	$myPaypal->addField('no_shipping', '2');
        	$myPaypal->addField('handling_cart', $shipping);  
        	//$myPaypal->addField('shipping_1', $shipping);   	
        }
        else //This checkout has no shipping cost
        {
        	//If you do not want to collect address for checkout that has no shipping cost then uncomment the following line of code.
        	//$myPaypal->addField('no_shipping', '1');
        }
    }
    else//Profile based shipping is enabled
    {
    	//Include the weight for profile based shipping calc
    	$myPaypal->addField('weight_cart', round($weight, 2));
    	$myPaypal->addField('weight_unit', 'lbs');
    	if($all_items_digital){//All the items in the cart are digital items so set the shipping flag to 0 so no shipping is charged
            $total_items = count($_SESSION['eStore_cart']);
            for ($i = 1; $i <= $total_items; $i++){
                $myPaypal->addField('shipping_'.$i, '0');
            }
    	}
    	else if(isset($_SESSION['eStore_cart_postage_cost']) && $_SESSION['eStore_cart_postage_cost']== 0 ){//Free shipping discount applied. send 0 shipping to override profile based shipping
            if(empty($weight)){//Add $0 shipping override
		$myPaypal->addField('shipping_1', '0');
            }
	}
    }
    if(!empty($_SESSION['eStore_cart_total_tax']))
    {
    	$cart_total_tax = round($_SESSION['eStore_cart_total_tax'], 2);
    	$cart_total_tax = apply_filters('eStore_change_tax_before_payment_filter', $cart_total_tax); //change tax amount before submitting if converting currency to another type
    	$myPaypal->addField('tax_cart', $cart_total_tax);
    }

    if (get_option('eStore_display_tx_result'))
    {
        $myPaypal->addField('rm', '1');
    }

    if(defined('WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE') && WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE !== '0'){
            //Set the country/region preference by force.
            $myPaypal->addField('lc', WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE);
    }
    $myPaypal->addField('cmd', '_cart');
    $myPaypal->addField('upload', '1');
    $myPaypal->addField('mrb', '3FWGC6LFTMTUG');

    $custom_field_val = eStore_get_custom_field_value();
    $myPaypal->addField('custom', $custom_field_val);    
    
    $page_style_name = get_option('eStore_paypal_co_page_style');
    if(!empty($page_style_name)){
    	$myPaypal->addField('page_style', $page_style_name);
    }
    
    $returnButtonText = get_option('eStore_paypal_return_button_text');
    if (!empty($returnButtonText)){
        $myPaypal->addField('cbt', $returnButtonText);
    }
    
    if($wp_eStore_config->getValue('eStore_pp_collect_instruction_enabled')=='1'){    
        $instruction_f_label = $wp_eStore_config->getValue('eStore_pp_instruction_label');
        if(empty($instruction_f_label)){
            $instruction_f_label = 'Add special instructions to the seller';
        }
        $myPaypal->addField('no_note', '0');
        $myPaypal->addField('cn', $instruction_f_label);
    }
    
    
    // Enable sandbox mode if needed
    if($eStore_sandbox_enabled){
        $myPaypal->enableTestMode();
    }

    // Lets clear the cart if automatic redirection is not being used otherwise we will empty the cart after the redirection
    $PDT_auth_token = get_option('eStore_paypal_pdt_token');	
    if(empty($PDT_auth_token)){
        reset_eStore_cart();
    }

    // submit the payment!
    $myPaypal->submitPayment2(WP_ESTORE_CLICK_HERE);    
} 

function submit_to_manual()
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $eStore_on_page_manual_checkout_page_url = $wp_eStore_config->getValue('eStore_on_page_manual_checkout_page_url');
    if(empty($eStore_on_page_manual_checkout_page_url))//Use the stand alone manual checkout form
    {
        $sp_obj = eStore_get_store_action_page_obj();
        $sa_page_url = get_permalink($sp_obj->ID);
        $full_sa_page_url = add_query_arg(array('manual_checkout'=>'1'), $sa_page_url);
    }
    else{
        $full_sa_page_url = $eStore_on_page_manual_checkout_page_url;
        //eStore_redirect_to_url($eStore_on_page_manual_checkout_page_url);
    }

    $click_text = WP_ESTORE_CLICK_HERE;
    $output = "";
    $output .= "<div style=\"text-align:center;\">";
    $output .= '<form id="gateway_form" action="'.$full_sa_page_url.'" method="post">';
    $output .= '<input type="hidden" name="eStore_manaul_gateway" id="eStore_manaul_gateway" value="process" />';	
    $output .= "<input type=\"submit\" value=\"$click_text\">";
    $output .= "</form>";
    $output .= "</div>";
    echo $output;	
}

function eStore_save_order_details($custId)
{
	global $wpdb;
	$pending_payment_table_name = $wpdb->prefix . "wp_eStore_pending_payment_tbl";
	
	$custom_field_val = eStore_get_custom_field_value();
	$referrer = eStore_get_referrer();
		
	$total_cart_shipping = $_SESSION['eStore_cart_postage_cost'];
	if(is_numeric($total_cart_shipping)){
		$total_cart_shipping = number_format($total_cart_shipping,2);
	}	
	$total_cart_tax = number_format($_SESSION['eStore_cart_total_tax'], 2);
	$sub_total = $_SESSION['eStore_cart_sub_total'];
	foreach ($_SESSION['eStore_cart'] as $item)
	{
		$item_number = $item['item_number'];
        $name = esc_sql(htmlspecialchars($item['name']));		
		$price = round($item['price'], 2);
		$quantity = $item['quantity'];
		$shipping = $item['shipping'];						
		
		$updatedb = "INSERT INTO $pending_payment_table_name (customer_id, item_number, name, price, quantity, shipping, custom, total_shipping, total_tax, subtotal) VALUES ('$custId', '$item_number', '$name','$price','$quantity','$shipping','$custom_field_val','$total_cart_shipping','$total_cart_tax','$sub_total')";
		$results = $wpdb->query($updatedb);	
	}
}
function eStore_get_referrer()
{
	if (!empty($_SESSION['ap_id'])){
        $value = $_SESSION['ap_id'];
	}
	else if (isset($_COOKIE['ap_id'])){
        $value = $_COOKIE['ap_id'];
	}	
	return $value;
}
?>