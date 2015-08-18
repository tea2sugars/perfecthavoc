<?php
$tx_result_msg = '';
$tx_result_error_msg = ''; 
function eStore_display_transaction_result()
{	
	global $tx_result_msg,$tx_result_error_msg;    
	$output = "";
	//TODO - replace $tx_result_error_msg with $_SESSION['tx_result_error_msg']
    if(!empty($tx_result_error_msg)){
    	$output .= '<div class="eStore_error_message">'.$tx_result_error_msg.'</div>';
    }
    else if(!empty($_SESSION['tx_result_error_msg']))
    {
    	$output .= '<div class="eStore_error_message">'.$_SESSION['tx_result_error_msg'].'</div>';
    }
    else if(isset($_SESSION['eStore_tx_result']))
    {
    	$output .= '<div STYLE="word-wrap: break-word">';
    	$output .= '<div class="eStore_thank_you_your_order">'.WP_ESTORE_YOUR_ORDER.'</div>';
    	$output .= $_SESSION['eStore_tx_result'];
    	$output .= '</div>';    	
    }
	$output = apply_filters('eStore_display_txn_result_shortcode_output', $output);
    //$output .= '<br />'.$tx_result_msg;
    return $output;
}

/*** PayPal PDT Stuff ***/
function eStore_paypal_pdt_listener()
{
	if(isset($_GET['tx']) && isset($_GET['amt']) && get_option('eStore_display_tx_result'))
	{
		reset_eStore_cart();//Reset the cart if it's not empty yet
		include_once('lib/gateway/paypal_utility.php');
		if(WP_ESTORE_VALIDATE_PAYPAL_PDT_USING_CURL==='1'){
			eStore_paypal_validate_pdt_with_curl();
		}else{
			eStore_paypal_validate_pdt_no_curl();
		}
	}
}

/* Auth.net receipt page processing */
function eStore_auth_net_ipn_processor_listener()
{
	if(isset($_REQUEST['x_invoice_num']) && isset($_REQUEST['x_trans_id']) && isset($_REQUEST['x_amount'])){
            if (get_option('eStore_use_authorize_gateway')){//Auth.net is enabled
                //Process the IPN
                eStore_payment_debug("Authorize.net payment gateway ipn processing... triggered via x_invoice_num and x_trans_id request parameter.",false);
            }else{
                eStore_payment_debug("Authorize.net payment gateway is not enabled in eStore settings. This IPN will not be processed",false);
                return;
            }
	}else{
            return;
	}
	status_header(200);
	include_once ('lib/gateway/Authorize.php');
	include_once ('eStore_process_payment_data.php');	
	
	// Create an instance of the authorize.net library
	$myAuthorize = new Authorize();
	
	// Log the IPN results
	$debug_on = get_option('eStore_cart_enable_debug');
	if ($debug_on){
	    $myAuthorize->ipnLog = TRUE;
	}
	
	// Specify your authorize api and transaction id
	$authorize_login_id = get_option('eStore_authorize_login');
	$authorize_tx_key = get_option('eStore_authorize_tx_key');
	$myAuthorize->setUserInfo($authorize_login_id, $authorize_tx_key);
	
	// Enable test mode if needed
	if(get_option('eStore_cart_enable_sandbox')){
		$myAuthorize->enableTestMode();
	}
	
	// Check validity and process
	if ($myAuthorize->validateIpn()){
		handle_payment_data($myAuthorize->ipnData,"authorize");
	}
	else
	{
            $_SESSION['eStore_tx_result'] = $myAuthorize->lastError;		
	    //Ipn validation failed... redirect to the cancel URL
	    $return = get_option('cart_cancel_from_paypal_url');
	    if(empty($return)){
	    	$return = get_bloginfo ('wpurl');
	    }
	    $redirection_parameter = 'Location: '.$return;
	    header($redirection_parameter);
	    exit;  
	}
        
        //Alternate auth.net IPN listener for when the alternate redirection is enabled.
	if(isset($_REQUEST['estore_auth_ipn']) && $_REQUEST['estore_auth_ipn']=="process"){
            include_once('eStore_auth_ipn.php');
            exit;
	}
        
}

function eStore_paypal_ipn_listener()//The alternative PP IPN listener - TODO - Make this the default
{
	if(isset($_REQUEST['estore_pp_ipn']) && $_REQUEST['estore_pp_ipn']=="process"){
		include_once('paypal.php');
		exit;
	}
}

function eStore_process_PDT_payment_data($keyarray)
{
	setcookie("cart_in_use","true",time()+21600,"/");
	
	//TODO - refactor using eStore_do_thank_you_page_display_tasks()
	//TODO - do a multi-submission check
	eStore_payment_debug("Processing PayPal PDT...",true);
	global $tx_result_msg,$tx_result_error_msg;
   	if (get_option('eStore_strict_email_check') != '')
   	{
    	$seller_paypal_email = get_option('cart_paypal_email');
    	if ($seller_paypal_email != $keyarray['receiver_email'])
    	{
    		$tx_result_error_msg .= 'Invalid Seller Paypal Email Address Detected: '.$keyarray['receiver_email'];
    		eStore_payment_debug('Invalid Seller Paypal Email Address Detected: '.$keyarray['receiver_email'],fales);
    		return false;
    	}
    }
   	$payment_status = $keyarray['payment_status'];
    if ($payment_status != "Completed" && $payment_status != "Processed")
    {
        $tx_result_error_msg .= ESTORE_PENDING_PAYMENT_EMAIL_BODY;//'The Fund have not been cleared yet. Product will be delivered when the fund clears!';
        eStore_payment_debug("The fund did not clear. Product will be delivered via email when the fund clears! Payment Status: ".$payment_status,false);
    	return false;
    }

    $custom = $keyarray['custom'];
    $delimiter = "&";
    $customvariables = array();
    $namevaluecombos = explode($delimiter, $custom);
    foreach ($namevaluecombos as $keyval_unparsed)
    {
        $equalsignposition = strpos($keyval_unparsed, '=');
        if ($equalsignposition === false)
        {
            $customvariables[$keyval_unparsed] = '';
            continue;
        }
        $key = substr($keyval_unparsed, 0, $equalsignposition);
        $value = substr($keyval_unparsed, $equalsignposition + 1);
        $customvariables[$key] = $value;
    }	
    isset($customvariables['ngg_pid'])?$pictureID = $customvariables['ngg_pid']:$pictureID = '';//$pictureID = $customvariables['ngg_pid'];    
    
	//Fire the begin processing hook
	do_action('eStore_begin_paypal_pdt_processing',$keyarray['payer_email'],$customvariables['ip']);
	    
    $transaction_type = $keyarray['txn_type'];
    $transaction_id = $keyarray['txn_id'];
    $transaction_subject = $keyarray['transaction_subject'];
    $gross_total = $keyarray['mc_gross'];
		if ($transaction_type == "cart")
		{
			// Cart Items
			$num_cart_items = $keyarray['num_cart_items'];
			$tx_result_msg .= 'Number of Cart Items: '.$num_cart_items;

			$i = 1;
			$cart_items = array();
			while($i < $num_cart_items+1)
			{
				$item_number = $keyarray['item_number' . $i];
				$item_name = $keyarray['item_name' . $i];
				$quantity = $keyarray['quantity' . $i];
				$mc_gross = $keyarray['mc_gross_' . $i];
				$mc_currency = $keyarray['mc_currency'];

				$current_item = array(
									   'item_number' => $item_number,
									   'item_name' => $item_name,
									   'quantity' => $quantity,
									   'mc_gross' => $mc_gross,
									   'mc_currency' => $mc_currency,
									  );

				array_push($cart_items, $current_item);
				$i++;
			}
		}
		else
		{
			$cart_items = array();
			$tx_result_msg .= 'Transaction Type: Buy Now/Subscribe';
			$item_number = $keyarray['item_number'];
			$item_name = $keyarray['item_name'];
			$quantity = $keyarray['quantity'];
			$mc_gross = $keyarray['mc_gross'];
			$mc_currency = $keyarray['mc_currency'];

			$current_item = array(
									   'item_number' => $item_number,
									   'item_name' => $item_name,
									   'quantity' => $quantity,
									   'mc_gross' => $mc_gross,
									   'mc_currency' => $mc_currency,
									  );

			array_push($cart_items, $current_item);
		}
		$script_location = get_option('eStore_download_script');
		$random_key = get_option('eStore_random_code');

		global $wpdb;
		$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
		$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
		$sales_table_name = $wpdb->prefix . "wp_eStore_sales_tbl";
		$payment_currency = get_option('cart_payment_currency');

	    $product_id_array = Array();
	    $product_name_array = Array();
	    $product_price_array = Array();
	    $product_qty_array = Array();
	    $download_link_array = Array();
        $counter = 0;
               
		foreach ($cart_items as $current_cart_item)
		{
			$cart_item_data_num = $current_cart_item['item_number'];
			$key=$cart_item_data_num;
			$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$key'", OBJECT);						
			$cart_item_data_name = trim($current_cart_item['item_name']); //$retrieved_product->name;
			//$cart_item_data_name = mb_convert_encoding($cart_item_data_name, "UTF-8");
			$cart_item_data_quantity = $current_cart_item['quantity'];
			$cart_item_data_total = $current_cart_item['mc_gross'];
			$cart_item_data_currency = $current_cart_item['mc_currency'];

			$tx_result_msg .= '<br />Item Number: '.$cart_item_data_num;
			$tx_result_msg .= '<br />Item Name: '.$cart_item_data_name;
			$tx_result_msg .= '<br />Item Quantity: '.$cart_item_data_quantity;
			$tx_result_msg .= '<br />Item Total: '.$cart_item_data_total;
			$tx_result_msg .= '<br />Item Currency: '.$cart_item_data_currency;

			// Compare the values with the values stored in the database
			isset($customvariables['coupon'])?$coupon_code = $customvariables['coupon']:$coupon_code='';//$coupon_code = $customvariables['coupon'];			
			if(!empty($coupon_code))
			{
                $tx_result_msg .= 'Coupon Code Used : '.$coupon_code;
                $coupon_table_name = $wpdb->prefix . "wp_eStore_coupon_tbl";
                $ret_coupon = $wpdb->get_row("SELECT * FROM $coupon_table_name WHERE coupon_code = '$coupon_code'", OBJECT);
                if ($ret_coupon)
                {
                    $discount_amount = $ret_coupon->discount_value;
                    $discount_type = $ret_coupon->discount_type;
                    if ($discount_type == 0)
                    {
                        //apply % discount
                        $discount = ($retrieved_product->price*$discount_amount)/100;
                        $true_product_price = $retrieved_product->price - $discount;
                    }
                    else
                    {
                        // apply value discount
                        $true_product_price = $retrieved_product->price - $discount_amount;
                    }
                }
            }           
            else
            {
                $true_product_price = $retrieved_product->price*$cart_item_data_quantity;
            }
            
			$check_price = true;
			$msg = "";
			$msg = apply_filters('eStore_before_checking_price_filter', $msg, $current_cart_item);
			if(!empty($msg) && $msg == "currency-check-override"){
				$check_price = false;
				$tx_result_msg .= '<br />PDT check - Price and currency check override enabled by filter eStore_before_checking_price_filter';
			}
			if($check_price){
	            $true_product_price = round($true_product_price, 2);
				if ($cart_item_data_total < $true_product_price)
				{
			    	$tx_result_error_msg .= 'Wrong Product Price Detected. Actual Product Price : '.$true_product_price;
			    	$tx_result_error_msg .= 'Paid Product Price : '.$cart_item_data_total;
	         		return false;
				}
				
				if(!empty($retrieved_product->currency_code))
				    $payment_currency = $retrieved_product->currency_code;			
				if ($payment_currency != $cart_item_data_currency)
				{
			    	$tx_result_error_msg .= 'Invalid Product Currency : '.$cart_item_data_currency;
	         		return false;
				}
			}
			
			//Check if nextgen gallery integration is being used
		    $pid_check_value = eStore_is_ngg_pid_present($cart_item_data_name);
		    if($pid_check_value != -1)
		    {
		    	$pictureID = $pid_check_value;
		    }			
			if(!empty($pictureID))
			{
				$download_link = eStore_get_ngg_image_url_html($pictureID,$cart_item_data_name);
				$pictureID = "";
			}
			else
			{
				$eStore_auto_shorten_url = WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS;
				$dl_link_target = 'target="_self"';
				if(WP_ESTORE_OPEN_IN_NEW_WINDOW_THANKU_DL_LINKS == '1'){$dl_link_target = 'target="_blank"';}
			    $product_id = $retrieved_product->id;
				//check if it is a digital variation
			    $is_digital_variation = false;
			    if(!empty($retrieved_product->variation3) && eStore_check_if_string_contains_url($retrieved_product->variation3)){
			    	$is_digital_variation = true;
			    }			    
			    if(empty($retrieved_product->product_download_url) && !$is_digital_variation)
			    {
	                $download_link = "<br /><strong>".$cart_item_data_name."</strong>". WP_ESTORE_THIS_ITEM_DOES_NOT_HAVE_DOWNLOAD;
	            }
	            else
	            {   
	            	$payment_data = array();
	            	$payment_data['customer_name'] = $keyarray['first_name']." ".$keyarray['last_name'];
	            	$payment_data['payer_email'] = $keyarray['payer_email'];
	            	isset($keyarray['contact_phone'])?$payment_data['contact_phone'] = $keyarray['contact_phone']:$payment_data['contact_phone']='';
	            	$payment_data['address'] = $keyarray['address_street'].", ".$keyarray['address_city'].", ".$keyarray['address_state']." ".$keyarray['address_zip'].", ".$keyarray['address_country'];
	            	$payment_data['txn_id'] = $keyarray['txn_id'];
	            	
	            	if(!empty($retrieved_product->variation3))
	            	{
	            		$download_link = get_download_for_variation_tx_result($cart_item_data_name,$retrieved_product,$script_location,$random_key,$payment_data);
	            	}
	            	else
	            	{
		    			$download_url_field = $retrieved_product->product_download_url;
		    			$product_ids = explode(',',$download_url_field);
					    $package_product = true;
	                    $multi_parts = false;
					    foreach($product_ids as $id)
					    {
					        if(!is_numeric($id))
					        {
					            $package_product = false;
					        }
					    }
					    if(sizeof($product_ids)>1 && !$package_product){
	                        $multi_parts = true;
	                    }
					    if($package_product)
					    {
					        $tx_result_msg .= 'The product is a package product.';
					        foreach($product_ids as $id)
					        {
	                            $id = trim($id);
		                        $retrieved_product_for_id = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
		                        $download_key =  eStore_check_stamping_flag_and_generate_download_key($retrieved_product_for_id,$retrieved_product_for_id->id,'',$payment_data,$cart_item_data_name);
	                            $download_url = eStore_construct_raw_encrypted_dl_url($download_key);
								if(WP_ESTORE_USE_ANCHOR_FOR_THANKU_DL_LINKS==='1'){
									$raw_download = '<a href="'.$download_url.'" '.$dl_link_target.'>'.WP_ESTORE_CLICK_HERE_TO_DOWNLOAD.'</a>';
								}else{
					        		$raw_download = '<a href="'.$download_url.'" '.$dl_link_target.'>'.$download_url.'</a>';
								}
	                            $download_link .= "<br /><strong>".$retrieved_product_for_id->name."</strong> - ".$raw_download.'<br />';
	                            eStore_register_link_in_db('',$download_key,$download_url,'','','',0,$payment_data['txn_id']);
					        }
					    }
					    else if($multi_parts)
					    {
	                        $tx_result_msg .= 'The product has multiple downloadable files.';
					        $count = 1;
					        $download_link .= "<br /><strong>".$cart_item_data_name."</strong> - ";
					        foreach($product_ids as $id)
					        {
	                            $id = trim($id);
	                            if(!empty($id)){
	                                $download_key =  eStore_check_stamping_flag_and_generate_download_key($retrieved_product,$product_id,$id,$payment_data,$cart_item_data_name);
	                                $download_url = eStore_construct_raw_encrypted_dl_url($download_key);
	                            	if(WP_ESTORE_USE_ANCHOR_FOR_THANKU_DL_LINKS==='1'){
										$raw_download = '<a href="'.$download_url.'" '.$dl_link_target.'>'.WP_ESTORE_CLICK_HERE_TO_DOWNLOAD.'</a>';
									}else{
					        			$raw_download = '<a href="'.$download_url.'" '.$dl_link_target.'>'.$download_url.'</a>';
									}
	                                $download_link .= "<br />".ESTORE_PART." ".$count." : ".$raw_download;
	                                eStore_register_link_in_db('',$download_key,$download_url,'','','',0,$payment_data['txn_id']);
	                                $count++;
	                            }
	                        }
	                    }
					    else
					    {
					        $download_key =  eStore_check_stamping_flag_and_generate_download_key($retrieved_product,$product_id,'',$payment_data,$cart_item_data_name);
					        $download_url = eStore_construct_raw_encrypted_dl_url($download_key);
					    	if(WP_ESTORE_USE_ANCHOR_FOR_THANKU_DL_LINKS==='1'){
								$raw_download = '<a href="'.$download_url.'" '.$dl_link_target.'>'.WP_ESTORE_CLICK_HERE_TO_DOWNLOAD.'</a>';
							}else{
					        	$raw_download = '<a href="'.$download_url.'" '.$dl_link_target.'>'.$download_url.'</a>';
							}
					        $download_link = "<br /><strong>".stripslashes($cart_item_data_name)."</strong> - ".$raw_download;		
					        eStore_register_link_in_db('',$download_key,$download_url,'','','',0,$payment_data['txn_id']);	         
					    } 
	            	} 
	            }
			}
		    $tx_result_msg .= 'Download Link : '.$download_link;

            array_push($product_name_array, $cart_item_data_name);
            array_push($product_id_array, $product_id);
            array_push($product_price_array, $cart_item_data_total);
            array_push($product_qty_array, $cart_item_data_quantity);            
            //array_push($attachments_array, $retrieved_product->product_download_url);
            array_push($download_link_array, $download_link);
            $counter++;
            $download_link = '';
		}
		// How long the download link remain valid (hours)
		$download_url_life = get_option('eStore_download_url_life');
		$email_body = get_option('eStore_buyer_email_body');

		// Send the product
		$constructed_products_name = "";
		$constructed_products_price = "";
		$constructed_products_id = "";
		$constructed_download_link = "";
        for ($i=0; $i < sizeof($product_name_array); $i++)
        {
            $constructed_products_name .= $product_name_array[$i];
            $constructed_products_name .= ", ";

            $constructed_products_price .= $product_price_array[$i];
            $constructed_products_price .= ", ";

            $constructed_products_id .= $product_id_array[$i];
            $constructed_products_id .= ", ";

            $constructed_download_link .= "<br />";
            if (is_array($download_link_array[$i]))
            {
            	$package_downloads = $download_link_array[$i];
            	for ($j=0; $j < sizeof($package_downloads); $j++)
            	{
            		$constructed_download_link .= $package_downloads[$j];
            		$constructed_download_link .= "<br />";
            	}
            }
            else
            {
            	$constructed_download_link .= $download_link_array[$i];
            }
        }
        
        //Save transaction result for thank you page display
        eStore_payment_debug("Saving transaction data for thank you page display.",true);
        eStore_save_trans_result_for_thank_you_page_display($keyarray,$constructed_download_link,$cart_items);

        //Google Analytics e-commerce tracking (only do it if set in settings menu)
        if(get_option('eStore_enable_analytics_tracking'))
        {
	        $mc_shipping = $keyarray['mc_shipping'];
	        $mc_tax = $keyarray['tax'];
	        $city = $keyarray['address_city'];
	        $state = $keyarray['address_state'];
	        $country = $keyarray['address_country'];
	        $eStore_analytics_code = array();	        
	        $eStore_analytics_code[] = "'_addTrans',"."'".$transaction_id."','".get_bloginfo('name')."','".$gross_total."','".$mc_tax."','".$mc_shipping."','".$city."','".$state."','".$country."'";
	        
			for ($j=0; $j < sizeof($product_name_array); $j++)  
			{				
				$eStore_analytics_code[] = "'_addItem',"."'".$transaction_id."','".$product_id_array[$j]."','".$product_name_array[$j]."','','".$product_price_array[$j]."','".$product_qty_array[$j]."'";
			}  			
			$eStore_analytics_code[] = "'_trackTrans'";
			$_SESSION['eStore_ga_code'] = $eStore_analytics_code;
			
			add_filter('yoast-ga-push-after-pageview','eStore_add_trans_to_ga_tracking');
        }	
}


//Google Analytics ecommerce tracking for manual checkout if needed
if(isset($_REQUEST['eStore_manual_co_track_ga'])){
	add_filter('yoast-ga-push-after-pageview','eStore_add_trans_to_ga_tracking');
}
function eStore_add_trans_to_ga_tracking($push) 
{
	if(isset($_SESSION['eStore_ga_code']))
	{
		for ($j=0; $j < sizeof($_SESSION['eStore_ga_code']); $j++) 
		{
			$push[] = $_SESSION['eStore_ga_code'][$j];
		}
		return $push;
	}
}

function eStore_save_trans_result_for_thank_you_page_display($payment_data,$constructed_download_link,$cart_items)
{
	//$constructed_download_link - must have the values in HTML links	
	$email_info = WP_ESTORE_YOU_WILL_SOON_RECEIVE_EMAIL."(<strong>".$payment_data['payer_email']."</strong>)<br />";        
	$offer_text = html_entity_decode(get_option('eStore_special_offer_text'), ENT_COMPAT,"UTF-8");	
	$additional_action_data = eStore_check_and_retrieve_action_data($payment_data,$cart_items);
	
	$_SESSION['eStore_tx_result'] = '<div class="eStore_thank_you_download_links">'.$constructed_download_link.'</div>';
	if(!empty($additional_action_data)){
		$_SESSION['eStore_tx_result'] .= '<div class="eStore_thank_you_additional_action_data">'.$additional_action_data.'</div>';
	}
	$_SESSION['eStore_tx_result'] .= '<div class="eStore_thank_you_total_cost">'.WP_ESTORE_TOTAL_COST.': '.print_tax_inclusive_payment_currency_if_enabled($payment_data['mc_gross'], WP_ESTORE_CURRENCY_SYMBOL).'</div>';
	$_SESSION['eStore_tx_result'] .= '<div class="eStore_thank_you_txn_id">'.ESTORE_TRANSACTION_ID.': '.$payment_data['txn_id'].'</div>';
	$_SESSION['eStore_tx_result'] .= '<div class="eStore_thank_you_email">'.$email_info.'</div>';
	if(!empty($offer_text)){
		$_SESSION['eStore_tx_result'] .= '<div class="eStore_thank_you_offer">'.$offer_text.'</div>';
	}
	$output = $_SESSION['eStore_tx_result'];
	$_SESSION['eStore_tx_result'] = apply_filters('eStore_tx_result_session_data', $output, $payment_data, $cart_items);//Gets fired before init action
}

function eStore_check_and_retrieve_action_data($payment_data,$cart_items)
{
	eStore_payment_debug('Checking if additional action data needs to be displayed on the Thank You page.',true);	
	$output = "";
	$customer_email = $payment_data['payer_email'];
	$txn_id = $payment_data['txn_id'];
	$member_rego_data_needed = false;
	$product_key_data_needed = false;
	
	foreach ($cart_items as $current_cart_item)
	{
		$id = $current_cart_item['item_number'];			
		$retrieved_product = WP_eStore_Db_Access::find(WP_ESTORE_PRODUCTS_TABLE_NAME, " id = '$id'");
		if(!empty($retrieved_product->ref_text)){
			$member_rego_data_needed = true;
		}
		
		$retrieved_meta = WP_eStore_Db_Access::find(WP_ESTORE_PRODUCTS_META_TABLE_NAME, " prod_id = '$id' AND meta_key='available_key_codes'");
		if(!empty($retrieved_meta->meta_value)){			
			$product_key_data_needed = true;
		}		
	}

	if($member_rego_data_needed && WP_ESTORE_SHOW_REGO_COMPLETION_LINK_ON_TY_PAGE==='1'){
		$cond = " meta_key2='txn_id' AND meta_value2='$txn_id' ORDER by meta_id DESC";//TODO - add condition to find "reg_url" column so it doesn't get record from other rows with txn_id column
		$result = WP_eStore_Db_Access::find(WP_ESTORE_GLOBAL_META_TABLE_NAME, $cond);
//		if(!$result){//TODO - Failsafe: try with the email address and find the last entry and then check with members DB to see if incomplete rego exists
//			$cond = " meta_key1='member_email' AND meta_value1='$customer_email' ORDER by meta_id DESC";
//			$result = WP_eStore_Db_Access::find(WP_ESTORE_GLOBAL_META_TABLE_NAME, $cond);
//		}
		if($result){//We have a result
			$rego_completion_link = '<a href="'.$result->meta_value3.'" target="_blank">'.WP_ESTORE_CLICK_HERE_TO_COMPLETE_REGO.'</a>';
			$output .= '<div class="eStore_thank_you_rego_link">'.$rego_completion_link.'</div>';
		}else{
			$output .= WP_ESTORE_REGO_URL_IS_YET_TO_BE_CREATED;
		}
	}
	
	$product_key_data_found = false;
	if($product_key_data_needed){
		if(!empty($payment_data['product_key_data'])){//product key data is already available			
			$output .= $payment_data['product_key_data'];
			$product_key_data_found = true;
		}else{
			$retrieved_cust = WP_eStore_Db_Access::find(WP_ESTORE_CUSTOMER_TABLE_NAME, " txn_id = '$txn_id'");
			//$retrieved_cust = WP_eStore_Db_Access::findAllArray(WP_ESTORE_CUSTOMER_TABLE_NAME, " email_address = '$customer_email' AND txn_id = '$txn_id'", "date ASC");
			if($retrieved_cust){
				$output .= '<div class="eStore_thank_you_serial_key">'.$retrieved_cust->serial_number.'</div>';
				$product_key_data_found = true;		
			}
		}
		
		if(!$product_key_data_found){
			//TODO - IPN hasn't been processed yet. Add a marker to do the ajax query later maybe
			$output .= WP_ESTORE_SERIAL_KEY_IS_YET_TO_BE_CREATED;
		}
	}
	return $output;
}

function eStore_track_ga_ecommerce($payment_data,$cart_items)
{
        //Google Analytics e-commerce tracking (only do it if set in settings menu)
        if(get_option('eStore_enable_analytics_tracking'))
        {
			//The $payment_data arrray must have - city, state and country fields
			if(empty($payment_data['address_city']) || empty($payment_data['address_state']) || empty($payment_data['address_country']))
			{
				eStore_payment_debug('Ecommerce analytics tracking failure. City, State or Country data is missing.',false);
				echo '<div class="">Error! City, State, Country data is missing! Cannot track Google Analytics without these data.</div>';
				exit;				
			}	
	        	
        	$transaction_id = $payment_data['txn_id'];
        	$gross_total = $payment_data['mc_gross'];
	        $mc_shipping = $payment_data['mc_shipping'];
	        $mc_tax = $payment_data['mc_tax'];
	        $city = $payment_data['address_city'];
	        $state = $payment_data['address_state'];
	        $country = $payment_data['address_country'];
	        $eStore_analytics_code = array();	        
	        $eStore_analytics_code[] = "'_addTrans',"."'".$transaction_id."','".get_bloginfo('name')."','".$gross_total."','".$mc_tax."','".$mc_shipping."','".$city."','".$state."','".$country."'";
	        
			foreach ($cart_items as $key => $item)  
			{
				$eStore_analytics_code[] = "'_addItem',"."'".$transaction_id."','".$item['item_number']."','".$item['item_name']."','','".$item['mc_gross']."','".$item['quantity']."'";
			}  			
			$eStore_analytics_code[] = "'_trackTrans'";
			$_SESSION['eStore_ga_code'] = $eStore_analytics_code;
			//print_r($_SESSION['eStore_ga_code'] );
			add_filter('yoast-ga-push-after-pageview','eStore_add_trans_to_ga_tracking');
			eStore_payment_debug('Ecommerce analytics tracking data has been pushed successfully.',true);
        }	
}
