<?php
function wp_eStore_on_page_manual_gateway_form_handler()
{
	include_once('eStore_auto_responder_handler.php');
	include_once('eStore_handle_subsc_ipn.php');	
	if (!digi_cart_not_empty()){//If cart is empty then do not show the checkout form
	    return eStore_empty_cart_display();
	}
	eStore_manual_gateway_form_processing_code();
	$output = "";
	ob_start();	
	echo '<link type="text/css" rel="stylesheet" href="'.WP_ESTORE_URL.'/view/eStore_on_page_manual_form_css.css" />';
	eStore_manual_gateway_collect_details_form();
	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}

function eStore_manual_gateway_api()
{
    //## TODO - remove this function after the new method is finalized (the new one uses the store action page)
	include_once('eStore_post_payment_processing_helper.php');
	include_once('eStore_auto_responder_handler.php');
	include_once('eStore_handle_subsc_ipn.php');	
	
	eStore_manual_gateway_form_processing_code();
	eStore_manual_gateway_display_form();
}

function eStore_manual_gateway_form_processing_code()
{
	if (isset($_POST['submit_shipping']))
        {
            if(eStore_get_total_cart_item_qty() < 1){//Cart does not have any item
                    echo '<div class="eStore_error_message">Error! Your shopping cart is empty. Please add items to your cart before checking out.</div>';
                    return;
            }
            $input_verified = false;
            global $wpdb;
            $wp_eStore_config = WP_eStore_Config::getInstance();			

            $err_msg = eStore_check_address_details();
            if (!empty($err_msg))
            {
            	$msg = '<div id="error">';
            	$msg .= ESTORE_REQUIRED_FIELDS_MISSING;
                $msg .= $err_msg;
                $msg .= '</div>';
                echo $msg;
            }
            else
            {
                    //Fire the begin processing hook
                    $clientip = $_SERVER['REMOTE_ADDR'];
                    $clientemail = $_POST['email'];
                    do_action('eStore_begin_manual_co_processing',$clientemail,$clientip);
                    $last_records_id = $wp_eStore_config->getValue('eStore_custom_receipt_counter');//get_option('eStore_custom_receipt_counter');
                    if (empty($last_records_id)){$last_records_id = 0;}
                    $receipt_counter = $last_records_id + 1;                    
                    $wp_eStore_config->setValue('eStore_custom_receipt_counter',$receipt_counter);
                    $wp_eStore_config->saveConfig();
                
                    $address = $_POST['address'].", ".$_POST['city'].", ".$_POST['state']." ".$_POST['postcode']." ".$_POST['country'];
                    $payment_data = extract_manaul_co_general_payment_data($_POST['firstname'],$_POST['lastname'],$_POST['email'],$address,$_POST['phone']);
                    $cart_items = extract_manual_item_data();
										
    	            $cust_direction = get_option('eStore_manual_co_cust_direction');
    	            $curr_symbol = get_option('cart_currency_symbol');
    	            if(!empty($cust_direction))
    	            {
			$cust_direction_mod = eStore_apply_post_payment_dynamic_tags($cust_direction,$payment_data,$cart_items);				    	            	
    	            	$body .= "\n-------------------------------\n";
    	            	$body .= $cust_direction_mod;	
    	            	$body .= "\n-------------------------------\n";
    	            }	            
    	            $count = 1;
    	            $constructed_download_link .= "<br />";
                    $product_key_data = "";
                    $show_tax_inc_price = $wp_eStore_config->getValue('eStore_show_tax_inclusive_price');
    	            foreach ($_SESSION['eStore_cart'] as $item)
    	            {
	    	        $products_table_name = $wpdb->prefix . "wp_eStore_tbl";
	    	        $key = $item['item_number'];
			$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$key'", OBJECT);    	            	
    	           		
			$rounded_price = round($item['price'], 2);
    	           	$body .= "\n".WP_ESTORE_DETAILS_OF_ORDERED_PRODUCT.": ".$count;
    	                $body .= "\n-------------------------";
    	                $body .= "\n".ESTORE_PRODUCT_ID.": ".$item['item_number'];
    	                $body .= "\n".ESTORE_PRODUCT_NAME.": ".$item['name'];
                        if($show_tax_inc_price == '1'){
                            $rounded_price = eStore_get_tax_include_price_by_prod_id($item['item_number'], $rounded_price);
                        }
                        $formatted_price = print_digi_cart_payment_currency($rounded_price,$curr_symbol);
    	                $body .= "\n".ESTORE_PRICE.": ".$formatted_price;
    	                $body .= "\n".ESTORE_QUANTITY.": ".$item['quantity']."\n";
    	                if(get_option('eStore_manual_co_give_download_links')!='')
    	                {
    	                	$download_link = generate_download_link_for_product($item['item_number'], $item['name'], $payment_data);
    	                	$constructed_download_link .= $download_link."<br />";
    	                	$body .= $download_link."\n";
    	                	$product_key = eStore_post_sale_retrieve_serial_key_and_update($retrieved_product,$item['name'],$item['quantity']);
                                $product_key_data .= $product_key;
                                $body .= $product_key;
                                $product_specific_instructions = eStore_get_product_specific_instructions($retrieved_product); 
                                $product_specific_instructions = eStore_apply_post_payment_dynamic_tags($product_specific_instructions, $payment_data, $cart_items);
                                $body .= $product_specific_instructions;                                        
    	                }
    	                $count++;
    	                
    	                //Check and signup WishList or WP eMember user if needed
    	                //if(get_option('eStore_manual_co_auto_update_db')=='1')
    	                if($wp_eStore_config->getValue('eStore_manual_co_auto_create_membership')=='1')
    	                {
	    	            $member_ref = $retrieved_product->ref_text;
                            eStore_payment_debug('Checking if membership inegration is being used. Reference Text Value: '.$member_ref,true);
                            if (!empty($member_ref))
                            {		    	  	
                                if (get_option('eStore_enable_wishlist_int'))
                                {
                                    eStore_payment_debug('WishList integration is being used... doing member account creation/upgrade task... see the "subscription_handle_debug.log" file for details',true);
                                    wl_handle_subsc_signup($payment_data,$member_ref,$payment_data['txn_id']);
                                }
                                else
                                {
                                    if (function_exists('wp_eMember_install'))
                                    {
                                        $eMember_id = $payment_data['eMember_userid'];
                                        eStore_payment_debug('eMember integration is being used... doing member account creation/upgrade task... see the "subscription_handle_debug.log" file for details',true);
                                        eMember_handle_subsc_signup($payment_data,$member_ref,$payment_data['txn_id'],$eMember_id);
                                    }
                                }
                            }
    	                }
    	                //=== End of membership handling code ===
    	            }
    	            $body .= "\n-------------------------------\n";
                    if($show_tax_inc_price != '1'){
                        $body .= ESTORE_SUB_TOTAL.": ".print_digi_cart_payment_currency($_SESSION['eStore_cart_sub_total'],$curr_symbol);
                    }
    	            if(!empty($_SESSION['eStore_cart_postage_cost'])){
    	            	$body .= "\n".ESTORE_SHIPPING.": ".print_digi_cart_payment_currency($_SESSION['eStore_cart_postage_cost'],$curr_symbol);
    	            }
    	            if(!empty($_SESSION['eStore_cart_total_tax'])){
    	            	$body .= "\n".WP_ESTORE_TAX.": ".print_digi_cart_payment_currency($_SESSION['eStore_cart_total_tax'],$curr_symbol);
    	            }
    	            $total = $_SESSION['eStore_cart_sub_total'] + $_SESSION['eStore_cart_postage_cost']+$_SESSION['eStore_cart_total_tax'];
    	            $body .= "\n".ESTORE_TOTAL.": ".print_digi_cart_payment_currency($total,$curr_symbol);
                    $conversion_rate = get_option('eStore_secondary_currency_conversion_rate');
                    if (!empty($conversion_rate))
                    {
                        $secondary_curr_symbol = get_option('eStore_secondary_currency_symbol');
                        $body .= "\n".ESTORE_TOTAL.' ('.get_option('eStore_secondary_currency_code').'): '.print_digi_cart_payment_currency($total*$conversion_rate,$secondary_curr_symbol);
                    }    	            
    	            
                    if(isset($_SESSION['eStore_store_pickup_checked']) && $_SESSION['eStore_store_pickup_checked'] == '1'){
                        $body .= "\nStore Pickup: Yes";
                    }
                    
    	            $total_items = $count-1;
    	            $body .= "\n".WP_ESTORE_TOTAL_ITEMS_ORDERED.": ".$total_items;
    	            $body .= "\n".ESTORE_TRANSACTION_ID.": ".$payment_data['txn_id'];
    	            
    	            $body .= "\n\n".WP_ESTORE_CUSTOMER_DETAILS;
    	            $body .= "\n-------------------------";
    	            $body .= "\n".WP_ESTORE_NAME.": ".$_POST['firstname']." ".$_POST['lastname'];
    	            $body .= "\n".ESTORE_EMAIL.": ".$_POST['email'];
    	            $body .= "\n".ESTORE_PHONE.": ".$_POST['phone'];
    	            $body .= "\n".ESTORE_ADDRESS.": ".$_POST['address'];
                    $body .= "\n".ESTORE_CITY.": ".$_POST['city'];
                    $body .= "\n".ESTORE_STATE.": ".$_POST['state'];
                    $body .= "\n".ESTORE_POSTCODE.": ".$_POST['postcode'];
                    $body .= "\n".ESTORE_COUNTRY.": ".$_POST['country'];
    	            $body .= "\n".WP_ESTORE_ADDITIONAL_COMMENT.": ".$_POST['additional_comment'];

    	            $notify_email = get_option('eStore_manual_notify_email');
    	            $buyer_email = $_POST['email'];

    	            if(empty($notify_email)){
    	                $notify_email = get_bloginfo('admin_email');
    	            }
    	            // Get referrer
    	            if (!empty($_SESSION['ap_id'])){
                            $referrer = $_SESSION['ap_id'];
                    }
                    else if (isset($_COOKIE['ap_id'])){
                            $referrer = $_COOKIE['ap_id'];
                    }

                    //Call the filter for email notification body
                    eStore_payment_debug('Applying filter - eStore_notification_email_body_filter',true);
                    $body = apply_filters('eStore_notification_email_body_filter', $body, $payment_data, $cart_items);

                    $seller_email_body = $body."\n\n".WP_ESTORE_REFERRER.": ".$referrer;
                    $from_email_address = get_option('eStore_download_email_address');
                    $headers = 'From: '.$from_email_address . "\r\n";
                    
    	            // Notify Seller
    	            $n_subject = $wp_eStore_config->getValue('seller_email_subject_manual_co');
    	            if(empty($n_subject)){
    	            	$n_subject = get_option('eStore_seller_email_subj');
    	            }
    	            wp_mail($notify_email, $n_subject, $seller_email_body, $headers);
    	            
    	            // Notify Buyer
    	            $buyer_email_subj = $wp_eStore_config->getValue('buyer_email_subject_manual_co');
    	            if(empty($buyer_email_subj)){
    	            	$buyer_email_subj = get_option('eStore_buyer_email_subj');
    	            }
    	            wp_mail($buyer_email,$buyer_email_subj,$body,$headers);

                    if(!empty($product_key_data)){//Lets add any serial key info to the data
                        $payment_data['product_key_data'] = $product_key_data;
                    }                            
    	            //Fire the manual checkout hook
    	            do_action('eStore_manual_checkout_form_data',$payment_data,$cart_items);
    	            
                    //Add to the customer database if the option is enabled
                    if(get_option('eStore_manual_co_auto_update_db')=='1'){						
                            record_sales_data($payment_data,$cart_items);						
                    }

                    //Perform autoresponder signup
                    if(get_option('eStore_manual_co_do_autoresponder_signup') == '1')
                    {
                            eStore_item_specific_autoresponder_signup($cart_items,$_POST['firstname'],$_POST['lastname'],$_POST['email']);
                            eStore_global_autoresponder_signup($_POST['firstname'],$_POST['lastname'],$_POST['email']);
                    }

                    //Award Affiliate Commission
                    eStore_award_commission_manual_co($payment_data,$cart_items);

                    // Revenue sharing
                    eStore_award_author_commission_manual_co($payment_data,$cart_items); 	

                    //Create affiliate account if needed
                    eStore_handle_auto_affiliate_account_creation($payment_data);

                    //Post IPN data to external site if needed
                    eStore_POST_IPN_data_to_url($payment_data,'',$cart_items);					

                    //Save transaction result for thank you page display
                    if(get_option('eStore_manual_co_give_download_links')!=''){
                            $constructed_download_link = wp_eStore_replace_url_in_string_with_link($constructed_download_link);
                            eStore_save_trans_result_for_thank_you_page_display($payment_data,$constructed_download_link,$cart_items);
                    }													

    	            $return_url = get_option('eStore_manual_return_url');
    	            if(empty($return_url))
    	            {
    	                $return_url = get_bloginfo('wpurl');
    	            }
					
    	            //Google analytics tracking
    	            if(get_option('eStore_enable_analytics_tracking') && get_option('eStore_manual_co_give_download_links')!='')
                    {
                            eStore_track_ga_ecommerce($payment_data,$cart_items);
                            $return_url = eStore_append_http_get_data_to_url($return_url,"eStore_manual_co_track_ga","1");
                    }

                    //Reset cart and redirect to Thank you page
    	            reset_eStore_cart();   
    	            					
    	            eStore_redirect_to_url($return_url);    	            
		}
        }	
}

function eStore_manual_gateway_display_form()
{
	include_once ('view/eStore_shipping_details_view.php');
	echo show_shipping_details_form_new("manual");	
}

function eStore_manual_gateway_collect_details_form($gateway="manual")
{
	if (function_exists('wp_eMember_install'))
	{
		$emember_auth = Emember_Auth::getInstance();
		$user_id = $emember_auth->getUserInfo('member_id');
		if (!empty($user_id))
		{
			//eMember user is logged in... load member's details into the fields.
			if(empty($_POST['email'])){
				$_POST['email'] = $emember_auth->getUserInfo('email');
				$_POST['firstname'] = $emember_auth->getUserInfo('first_name');
				$_POST['lastname'] = $emember_auth->getUserInfo('last_name');
				$_POST['address'] = $emember_auth->getUserInfo('address_street');
				$_POST['city'] = $emember_auth->getUserInfo('address_city');
				$_POST['state'] = $emember_auth->getUserInfo('address_state');
				$_POST['postcode'] = $emember_auth->getUserInfo('address_zipcode');
				$_POST['country'] = $emember_auth->getUserInfo('country');
				$_POST['phone'] = $emember_auth->getUserInfo('phone');
			}
		}
	}	
?>
<form id="payment" action="" method="post">

<h3><?php echo ESTORE_FILL_IN_SHIPPING_DETAILS; ?></h3>

<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="<?php echo $gateway; ?>" />
<input type="hidden" name="submit_shipping" id="submit_shipping" value="true" />

	<fieldset>
		<ol>
			<li>
				<label for=firstname><?php echo ESTORE_FIRST_NAME; ?> *</label>
				<input id="firstname" name="firstname" type="text" value="<?php echo isset($_POST['firstname'])?$_POST['firstname']:''; ?>" required autofocus>
			</li>
			<li>
				<label for=lastname><?php echo ESTORE_LAST_NAME; ?> *</label>
				<input id="lastname" name="lastname" type="text" value="<?php echo isset($_POST['lastname'])?$_POST['lastname']:''; ?>" required>
			</li>
			<li>
				<label for=address><?php echo ESTORE_ADDRESS; ?> *</label>
				<textarea id="address" name="address" rows=5 required><?php echo isset($_POST['address'])?$_POST['address']:''; ?></textarea>
			</li>
			<li>
				<label for=city><?php echo ESTORE_CITY; ?> *</label>
				<input id="city" name="city" type="text" value="<?php echo isset($_POST['city'])?$_POST['city']:''; ?>" required>
			</li>
			<li>
				<label for=state><?php echo ESTORE_STATE; ?> *</label>
				<input id="state" name="state" type="text" value="<?php echo isset($_POST['state'])?$_POST['state']:''; ?>" required>
			</li>
			<li>
				<label for=postcode><?php echo ESTORE_POSTCODE; ?> *</label>
				<input id="postcode" name="postcode" type="text" value="<?php echo isset($_POST['postcode'])?$_POST['postcode']:''; ?>" required>
			</li>
			<li>
				<label for=country><?php echo ESTORE_COUNTRY; ?> *</label>
				<input id="country" name="country" type="text" value="<?php echo isset($_POST['country'])?$_POST['country']:''; ?>" required>
			</li>
			<li>
				<label for=phone><?php echo ESTORE_PHONE; ?></label>
				<input id="phone" name="phone" type="text" value="<?php echo isset($_POST['phone'])?$_POST['phone']:''; ?>">
			</li>
			<li>
				<label for=email><?php echo ESTORE_EMAIL; ?> *</label>
				<input id="email" name="email" type="email" value="<?php echo isset($_POST['email'])?$_POST['email']:''; ?>" required>
			</li>
			<?php if ($gateway == "manual"){ ?>
			    <li>
				<label for=additional_comment><?php echo ESTORE_ADDITIONAL_COMMENT; ?></label>
				<textarea id="additional_comment" name="additional_comment" rows=5><?php echo isset($_POST['additional_comment'])?$_POST['additional_comment']:''; ?></textarea>
			    </li>
                        <?php } ?>
		</ol>
	</fieldset>
	<fieldset>
	    <input type="hidden" name="eStore_manaul_gateway" id="eStore_manaul_gateway" value="process" />
		<button type="submit" name="confirm"><?php echo ESTORE_CONFIRM_ORDER; ?></button>
	</fieldset>
</form>	
<?php 
}

function extract_manual_item_data()
{
	$cart_items = array();
	foreach ($_SESSION['eStore_cart'] as $item)
	{
		$item_number = $item['item_number'];
		$item_name = $item['name'];
		$quantity = $item['quantity'];
		$mc_gross = round($item['price'], 2);
		//$mc_shipping = $item['item_number'];
		$mc_currency = get_option('cart_payment_currency');

                $current_item = array(
                'item_number' => $item_number,
                'item_name' => $item_name,
                'quantity' => $quantity,
                'mc_gross' => $mc_gross,
                'mc_currency' => $mc_currency,
                );
		array_push($cart_items, $current_item);
    }
    return $cart_items;    
}

function extract_manaul_co_general_payment_data($fname,$lname,$email,$address,$phone)
{
$custom = eStore_get_custom_field_value();	
$unique_id = uniqid();
$num_cart_items = count($_SESSION['eStore_cart']);

$coupon_code_used = '';
if (!empty($_SESSION['eStore_coupon_code']))
{
	$coupon_code_used = $_SESSION['eStore_coupon_code'];
}

$eMember_id = '';
if (function_exists('wp_eMember_install'))
{
	$emember_auth = Emember_Auth::getInstance();
	$user_id = $emember_auth->getUserInfo('member_id');
	if (!empty($user_id))
	{
		$eMember_id = $user_id;
	}
}
$total = $_SESSION['eStore_cart_sub_total'] + $_SESSION['eStore_cart_postage_cost']+$_SESSION['eStore_cart_total_tax'];

$payment_data = array(
'gateway' => 'manual',
'custom' => $custom,
'txn_id' => $unique_id,
'txn_type' => 'Shopping Cart',
'transaction_subject' => 'Shopping cart manual checkout',
'first_name' => $fname,
'last_name' => $lname,
'payer_email' => $email,
'num_cart_items' => $num_cart_items,
'subscr_id' => $unique_id,
'address' => $address,
'phone' => $phone,
'coupon_used' => $coupon_code_used,
'eMember_username' => $eMember_id,
'eMember_userid' => $eMember_id,
'mc_gross' => $total,
'mc_shipping' => $_SESSION['eStore_cart_postage_cost'],
'mc_tax' => $_SESSION['eStore_cart_total_tax'],
'address_street' => $_POST['address'],
'address_city' => $_POST['city'],
'address_state' => $_POST['state'],
'address_country' => $_POST['country'],
);
return $payment_data;
}

function eStore_award_commission_manual_co($payment_data,$cart_items)
{	
	if (eStore_affiliate_capability_exists() && get_option('eStore_manual_co_give_aff_commission') == 1)
	{   
		eStore_aff_award_commission($payment_data,$cart_items);
	}
}
function eStore_award_author_commission_manual_co($payment_data,$cart_items)
{	
	if (function_exists('wp_aff_platform_install') && get_option('eStore_manual_co_give_aff_commission') == 1)
	{   
		eStore_award_author_commission($payment_data,$cart_items);
	}
}

function eStore_check_address_details()
{
    if (empty($_POST['firstname']))
    {
	$error_msg .= "<br />".ESTORE_FIRST_NAME;
    }
    if (empty($_POST['lastname']))
    {
	$error_msg .= "<br />".ESTORE_LAST_NAME;
    }
    if (empty($_POST['address']))
    {
	$error_msg .= "<br />".ESTORE_ADDRESS;
    }
    if (empty($_POST['city']))
    {
	$error_msg .= "<br />".ESTORE_CITY;
    }
    if (empty($_POST['postcode']))
    {
	$error_msg .= "<br />".ESTORE_POSTCODE;
    }
    if (empty($_POST['state']))
    {
	$error_msg .= "<br />".ESTORE_STATE;
    }
    if (empty($_POST['country']))
    {
	$error_msg .= "<br />".ESTORE_COUNTRY;
    }        
    if (empty($_POST['email']))
    {
	$error_msg .= "<br />".ESTORE_EMAIL;
    }

    return $error_msg;
}

add_filter('the_title', 'eStore_manual_co_filter_store_action_page_title');
function eStore_manual_co_filter_store_action_page_title($title)
{
    if (!in_the_loop()) {
        return $title;
    }
    global $post;
    $sp_obj = eStore_get_store_action_page_obj();
    if ($post->ID == $sp_obj->ID)
    {
        if(isset($_REQUEST['manual_checkout']) && $_REQUEST['manual_checkout'] == '1')
        {
            $title = '';
            return $title; 
        }
    }
    return $title;
}
add_filter('the_content', 'eStore_manual_co_filter_store_action_page_content');
function eStore_manual_co_filter_store_action_page_content($content)
{
    global $post;
    $sp_obj = eStore_get_store_action_page_obj();
    if ($post->ID == $sp_obj->ID)
    {
        if(isset($_REQUEST['manual_checkout']) && $_REQUEST['manual_checkout'] == '1')
        {
            $content = wp_eStore_on_page_manual_gateway_form_handler();
        }
    }
    return $content;
}
