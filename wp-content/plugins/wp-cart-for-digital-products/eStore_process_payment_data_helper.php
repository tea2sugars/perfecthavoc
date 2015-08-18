<?php
//**** This file needs to be included from a file that has access to "wp-load.php" ****
include_once('eStore_handle_subsc_ipn.php');
include_once('eStore_auto_responder_handler.php');
include_once('eStore_post_payment_processing_helper.php');
include_once('eStore_email.php');

function eStore_do_post_payment_tasks($payment_data,$cart_items)
{
	eStore_payment_debug('Performing post payment processing tasks.',true);
	// Add a check to see if the transaction ID for that email address entry already exists in the customer's database meaning the sale has already been processed?
	$txn_id = $payment_data['txn_id'];
	$emailaddress = $payment_data['payer_email'];
	$sandbox = get_option('eStore_cart_enable_sandbox');
	if(!$sandbox){
		//Check for already processed transaction if running in live mode		
		if(eStore_is_txn_already_processed($payment_data)){
			eStore_payment_debug('The transaction ID and the email address already exists in the database. So the payment have already been processed. No need to do anything for this. This could happen from a server glitch.',false);
			return false;			
		}	
	}
	$product_verified = process_payment_data($payment_data,$cart_items); //verify payment data, create download links, send notification
	if ($product_verified)
	{				
		//GA tracking
		if($payment_data['background_post'] != 'yes'){
			eStore_payment_debug('GA tracking if being used...',true);
        	eStore_track_ga_ecommerce($payment_data,$cart_items);
		}else{
			eStore_payment_debug('This is a background post so GA tracking will not be performed',true);
		}
        
        //Autoresponder signups
        $firstname = $payment_data['first_name'];
        $lastname = $payment_data['last_name'];
        $emailaddress = $payment_data['payer_email'];
        eStore_item_specific_autoresponder_signup($cart_items,$firstname,$lastname,$emailaddress);
		eStore_global_autoresponder_signup($firstname,$lastname,$emailaddress);
		
		eStore_payment_debug('Recording transaction details...',true);
		record_sales_data($payment_data,$cart_items); // Record sales data in customers database			
		eStore_aff_award_commission($payment_data,$cart_items);  // Award affiliate commission			
		eStore_award_author_commission($payment_data,$cart_items); // Revenue sharing				
		eStore_handle_auto_affiliate_account_creation($payment_data); // Auto affiliate account creation  	
		eStore_POST_IPN_data_to_url($payment_data,'',$cart_items);	//Post IPN data to external site if needed
	}
	eStore_payment_debug('End of post payment processing tasks.',true);	
}

function process_payment_data(&$payment_data,$cart_items)
{
    global $wpdb,$wp_eStore_config;
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;

    $script_location = get_option('eStore_download_script');
    $random_key = get_option('eStore_random_code');
    $payment_currency = get_option('cart_payment_currency');
    $customvariables = get_custom_var($payment_data['custom']);
    $product_specific_instructions = "";
    $currency_symbol = get_option('cart_currency_symbol');

    //Fire the begin processing hook
    do_action('eStore_begin_payment_processing',$payment_data['payer_email'],$customvariables['ip']);

    $product_id_array = Array();
    $product_name_array = Array();
    $product_price_array = Array();
    $product_qty_array = Array();
    $download_link_array = Array();
    $download_link_for_digital_item = Array();
    $counter = 0;
    $product_key_data = "";
    foreach ($cart_items as $current_cart_item)
    {
        $cart_item_data_num = $current_cart_item['item_number'];
        $cart_item_data_name = $current_cart_item['item_name'];
        $cart_item_data_quantity = $current_cart_item['quantity'];
        $cart_item_data_total = $current_cart_item['mc_gross'];
        $cart_item_data_currency = $current_cart_item['mc_currency'];

        eStore_payment_debug('Item Number: '.$cart_item_data_num,true);
        eStore_payment_debug('Item Name: '.$cart_item_data_name,true);
        eStore_payment_debug('Item Quantity: '.$cart_item_data_quantity,true);
        eStore_payment_debug('Item Total: '.$cart_item_data_total,true);
        eStore_payment_debug('Item Currency: '.$cart_item_data_currency,true);

        if ($cart_item_data_num != "SHIPPING")
        {
            // Compare the values with the values stored in the database
            $key=$cart_item_data_num;

            $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$key'", OBJECT);
            if(!$retrieved_product)
            {
                eStore_payment_debug('No Item found for the Item ID: '.$cart_item_data_num,false);
                return false;
            }
            $coupon_code = $customvariables['coupon'];
            if(!empty($coupon_code))
            {
                eStore_payment_debug('Coupon Code Used : '.$coupon_code,true);
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
                        eStore_payment_debug('Product Price after applying % discount: '.$true_product_price,true);
                    }
                    else
                    {
                        // apply value discount
                        $true_product_price = $retrieved_product->price - $discount_amount;
                        eStore_payment_debug('Product Price after applying fixed amount discount: '.$true_product_price,true);
                    }
                }
                else{
                	eStore_payment_debug('Could not find the coupon in the database: '.$coupon_code,false);
                }
            }
            else
            {
            	if(is_numeric($retrieved_product->a3)){
            		$true_product_price = 0;//subscription product
            	}
            	else if(is_numeric($retrieved_product->price)){
                	$true_product_price = $retrieved_product->price*$cart_item_data_quantity;
            	}
                else{
                 	$true_product_price = 0;//most likely a subscription
                }
            }
            
            $check_price = true;
            $msg = "";
            $msg = apply_filters('eStore_before_checking_price_filter', $msg, $current_cart_item);
            if(!empty($msg) && $msg == "currency-check-override"){
                $check_price = false;
                eStore_payment_debug('Price and currency check override enabled by filter eStore_before_checking_price_filter',true);
            }
            if($check_price){
                $true_product_price = round($true_product_price, 2);
                if ($cart_item_data_total < $true_product_price)
                {
                    eStore_payment_debug('Wrong Product Price Detected. Actual Product Price : '.$true_product_price,false);
                    eStore_payment_debug('Paid Product Price : '.$cart_item_data_total,false);
                    return false;
                }

                if(!empty($retrieved_product->currency_code)){
                    $payment_currency = $retrieved_product->currency_code;
                }

                if ($payment_currency != $cart_item_data_currency)
                {
                    eStore_payment_debug('Invalid Product Currency. Expected currency: '.$payment_currency.', Received Currency: '.$cart_item_data_currency,false);
                    return false;
                }
            }

            //*** Handle Membership Payment ***	
            eStore_payment_debug('Checking if membership inegration is being used.',true);	    
            $member_ref = $retrieved_product->ref_text;
            if (!empty($member_ref))
            {		    	  	
                if (get_option('eStore_enable_wishlist_int'))
                {
                    eStore_payment_debug('WishList integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);
                    wl_handle_subsc_signup($payment_data,$member_ref,$payment_data['txn_id']);
                }
                else
                {
                    if (function_exists('wp_eMember_install'))
                    {
                        $eMember_id = $customvariables['eMember_id'];
                        eStore_payment_debug('eMember integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);
                        eMember_handle_subsc_signup($payment_data,$member_ref,$payment_data['txn_id'],$eMember_id);
                    }
                }	    	
            }
            //== End of Membership payment handling ==		    			

            $item_name = $cart_item_data_name;//$retrieved_product->name;
            $download_link = generate_download_link($retrieved_product,$item_name,$payment_data);
            eStore_payment_debug('Download Link: [hidden]',true);//$download_link

            $product_specific_instructions .= eStore_get_product_specific_instructions($retrieved_product);          

            if($retrieved_product->create_license == 1)
            {
                $license_key = eStore_generate_license_key($payment_data);
                $product_license_data .= "\n".$cart_item_data_name." License Key: ".$license_key;
                eStore_payment_debug('License Key: [hidden]',true);//$license_key
            }	
		    
            //Issue serial key if this feature is being used
            $product_key_data .= eStore_post_sale_retrieve_serial_key_and_update($retrieved_product,$cart_item_data_name,$cart_item_data_quantity);

            array_push($product_name_array, $cart_item_data_name);
            array_push($product_id_array, $cart_item_data_num);
            if(empty($cart_item_data_total)){$cart_item_data_total = $retrieved_product->price;}
            array_push($product_price_array, $cart_item_data_total);
            array_push($product_qty_array, $cart_item_data_quantity);
            array_push($download_link_array, $download_link);
            if(eStore_check_if_string_contains_url($download_link)){
            	array_push($download_link_for_digital_item, $download_link);  
            }
	}            
        $counter++;
    }

	if(!empty($product_key_data)){
		$payment_data['product_key_data'] = $product_key_data;	
	}    
	
	// How long the download link remain valid (hours)
	$download_url_life = get_option('eStore_download_url_life');
	// Emails
	$notify_email = get_option('eStore_notify_email_address');  // Email which will recive notification of sale (sellers email)
	$download_email = get_option('eStore_download_email_address'); // Email from which the mail wil be sent
	$email_subject = get_option('eStore_buyer_email_subj');
	$email_body = get_option('eStore_buyer_email_body');
	$notify_subject = get_option('eStore_seller_email_subj');
	$notify_body =  get_option('eStore_seller_email_body');

	// Send the product
        for ($i=0; $i < sizeof($product_name_array); $i++)
        {
            $constructed_products_name .= $product_name_array[$i];
            $constructed_products_name .= ", ";

            $constructed_products_price .= $product_price_array[$i];
            $constructed_products_price .= ", ";

            $constructed_products_id .= $product_id_array[$i];
            $constructed_products_id .= ", ";
            
            $constructed_products_details .= "\n".$product_name_array[$i]." x ".$product_qty_array[$i]." - ".$currency_symbol.$product_price_array[$i]." (".$payment_currency.")";
            $tax_inc_price = eStore_get_tax_include_price_by_prod_id($product_id_array[$i], $product_price_array[$i]);
            $constructed_products_details_tax_inc .= "\n".$product_name_array[$i]." x ".$product_qty_array[$i]." - ".$currency_symbol.$tax_inc_price." (".$payment_currency.")";
            
            //Download links list for all items in the cart
            $constructed_download_link .= "\n";
            if (is_array($download_link_array[$i]))
            {
	            $package_downloads = $download_link_array[$i];
	            for ($j=0; $j < sizeof($package_downloads); $j++)
	            {
	            	$constructed_download_link .= $package_downloads[$j];
	            	$constructed_download_link .= "\n";
	            }
            }
            else
            {
            	$constructed_download_link .= $download_link_array[$i];
            }
            
            //Download links for only digital items in the cart
            $constructed_download_link_for_digital_item .= "\n";
            if (is_array($download_link_for_digital_item[$i]))
            {
            	$package_downloads2 = $download_link_for_digital_item[$i];
            	for ($j=0; $j < sizeof($package_downloads2); $j++)
            	{
            		$constructed_download_link_for_digital_item .= $package_downloads2[$j];
            		$constructed_download_link_for_digital_item .= "\n";
            	}
            }
            else
            {
            	$constructed_download_link_for_digital_item .= $download_link_for_digital_item[$i];
            }                          
        }

	//Counter for incremental receipt number	    
	$last_records_id = $wp_eStore_config->getValue('eStore_custom_receipt_counter');//get_option('eStore_custom_receipt_counter');
	if (empty($last_records_id)){
		$last_records_id = 0;
	}
	$receipt_counter = $last_records_id + 1;
	eStore_payment_debug('Incremental counter value: '.$receipt_counter,true);
	$wp_eStore_config->setValue('eStore_custom_receipt_counter',$receipt_counter);
	$wp_eStore_config->saveConfig();	
		        
        $purchase_date = (date ("Y-m-d"));
        //$total_purchase_amt = $payment_data['mc_gross'];    
        $total_minus_total_tax = number_format(($payment_data['mc_gross'] - $payment_data['mc_tax']),2);    
        $txn_id = $payment_data['txn_id'];        
        $buyer_shipping_info = $payment_data['address'];
        $buyer_phone = $payment_data['phone'];
        $shipping_option = $customvariables['ship_option'];
        if(empty($shipping_option)){$shipping_option = "Default";}

        $product_specific_instructions = eStore_apply_post_payment_dynamic_tags($product_specific_instructions, $payment_data, $cart_items);

        $additional_data = array();
        $additional_data['constructed_products_name'] = $constructed_products_name;
        $additional_data['constructed_products_price'] = $constructed_products_price;
        $additional_data['constructed_products_id'] = $constructed_products_id;    
        $additional_data['constructed_products_details'] = $constructed_products_details;
        $additional_data['constructed_products_details_tax_inc'] = $constructed_products_details_tax_inc;
        $additional_data['product_specific_instructions'] = $product_specific_instructions;
        $additional_data['constructed_download_link'] = $constructed_download_link;	    
        $additional_data['constructed_download_link_for_digital_item'] = $constructed_download_link_for_digital_item;
        $additional_data['product_license_data'] = $product_license_data;//this is the license mgr key (not the normal serial key code)
        
	$subject = eStore_apply_post_payment_dynamic_tags($email_subject, $payment_data, $cart_items, $additional_data);//str_replace($tags,$vals,$email_subject);
	$body    = eStore_apply_post_payment_dynamic_tags($email_body, $payment_data, $cart_items, $additional_data);//str_replace($tags,$vals,$email_body);
	$headers = 'From: '.$download_email . "\r\n";
	$attachment = '';
        
	//Call the filter for email notification body
	eStore_payment_debug('Applying filter - eStore_notification_email_body_filter',true);
	$body = apply_filters('eStore_notification_email_body_filter', $body, $payment_data, $cart_items);
                
        eStore_payment_debug('Sending product email to : '.$payment_data["payer_email"],true);
        if (get_option('eStore_use_wp_mail'))
        {
            wp_eStore_send_wp_mail($payment_data['payer_email'], $subject, $body, $headers);
            //wp_mail($payment_data['payer_email'], $subject, $body, $headers);
            eStore_payment_debug('Product Email successfully sent to '.$payment_data['payer_email'].'.',true);
        }
        else
        {
	        if(@eStore_send_mail($payment_data['payer_email'],$body,$subject,$download_email,$attachment))
	        {
	           	eStore_payment_debug('Product Email successfully sent (using PHP mail) to '.$payment_data['payer_email'].'.',true);
	        }
	        else
	        {
	        	eStore_payment_debug('Error sending product Email (using PHP mail) to '.$payment_data['payer_email'].'.',false);
	        }
        }

    	// Notify seller
        foreach ($payment_data as $key=>$value)
        {
            $post_string .= "$key=$value, ";
        }
        $n_subject = eStore_apply_post_payment_dynamic_tags($notify_subject, $payment_data, $cart_items, $additional_data);//str_replace($tags,$vals,$notify_subject);
        $n_body = eStore_apply_post_payment_dynamic_tags($notify_body, $payment_data, $cart_items, $additional_data);//str_replace($tags,$vals,$notify_body);
        if ($wp_eStore_config->getValue('eStore_add_payment_parameters_to_admin_email') == '1')
        {
                $n_body .= "\n\n------- User Email ----------\n".
                  $body.
                  "\n\n------- Paypal Parameters (Only admin will receive this) -----\n".
                  $post_string;
        }	                  
        $n_body = stripslashes($n_body);
        	                  
        $notify_emails_array = explode(",",$notify_email);
        foreach ($notify_emails_array as $notify_email_address)
        {
                if(!empty($notify_email_address))
                {
                        $recipient_email_address = trim($notify_email_address);
                    if (get_option('eStore_use_wp_mail'))
                {
                    wp_eStore_send_wp_mail($recipient_email_address, $n_subject, $n_body, $headers);
                    //wp_mail($recipient_email_address, $n_subject, $n_body, $headers);
                    eStore_payment_debug('Notify Email successfully sent to '.$recipient_email_address.'.',true);
                }
                else
                {
                        if(@eStore_send_mail($recipient_email_address,$n_body,$n_subject,$download_email))
                        {
                             eStore_payment_debug('Notify Email successfully sent (using PHP mail) to '.$recipient_email_address.'.',true);
                        }
                        else
                        {
                            eStore_payment_debug('Error sending notify Email (using PHP mail) to '.$recipient_email_address.'.',false);
                        }
                }				
                }
        }

        //Record details for the Thank You page display
        eStore_payment_debug('Creating transaction result display value',true);
        //Save transaction result for thank you page display
        $constructed_download_link = nl2br($constructed_download_link);
        $constructed_download_link = wp_eStore_replace_url_in_string_with_link($constructed_download_link);        
        eStore_save_trans_result_for_thank_you_page_display($payment_data,$constructed_download_link,$cart_items);
	              
        global $wp_eStore_transaction_result_display_content;
        $wp_eStore_transaction_result_display_content = $_SESSION['eStore_tx_result'];
        eStore_payment_debug('Transaction result display value set',true);   
        
        return true;
}

function eStore_do_thank_you_page_display_tasks_with_txn_id($txn_id)
{
	eStore_payment_debug('Executing eStore_do_thank_you_page_display_tasks_with_txn_id()',true);			
	if(eStore_txn_prcoessed($txn_id)){
		$payment_data = eStore_create_payment_data_from_txn_id($txn_id);
		$cart_items = eStore_create_cart_items_data_from_txn_id($txn_id);
		eStore_do_thank_you_page_display_tasks($payment_data,$cart_items);
		return true;
	}
	eStore_payment_debug('eStore_do_thank_you_page_display_tasks_with_txn_id() - the given transaction has not been processed yet.',true);  
	return false;
}

function eStore_do_thank_you_page_display_tasks($payment_data,$cart_items)
{
	eStore_payment_debug('Executing eStore_do_thank_you_page_display_tasks()',true);  
	$constructed_download_link = eStore_generate_download_links_for_cart_items($payment_data,$cart_items);
	$constructed_download_link = eStore_convert_text_download_links_into_html($constructed_download_link);
    eStore_save_trans_result_for_thank_you_page_display($payment_data,$constructed_download_link,$cart_items);
	        
    global $wp_eStore_transaction_result_display_content;
    $wp_eStore_transaction_result_display_content = $_SESSION['eStore_tx_result'];
    eStore_payment_debug('Transaction result display value set',true);   	
}

function eStore_generate_download_links_for_cart_items($payment_data,$cart_items)
{
	eStore_payment_debug('Executing eStore_generate_download_links_for_cart_items()',true);
    global $wpdb;
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    
	$product_name_array = Array();
	$download_link_array = Array();

	foreach ($cart_items as $current_cart_item)
	{
		$cart_item_data_num = $current_cart_item['item_number'];
		$cart_item_data_name = $current_cart_item['item_name'];
		$cart_item_data_quantity = $current_cart_item['quantity'];
		$cart_item_data_total = $current_cart_item['mc_gross'];
		$cart_item_data_currency = $current_cart_item['mc_currency'];

		eStore_payment_debug('Item Number: '.$cart_item_data_num,true);
		eStore_payment_debug('Item Name: '.$cart_item_data_name,true);
		eStore_payment_debug('Item Quantity: '.$cart_item_data_quantity,true);
		eStore_payment_debug('Item Total: '.$cart_item_data_total,true);
		eStore_payment_debug('Item Currency: '.$cart_item_data_currency,true);
		
		if ($cart_item_data_num != "SHIPPING")
		{
			// Compare the values with the values stored in the database
			$key=$cart_item_data_num;

			$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$key'", OBJECT);
            if(!$retrieved_product)
            {
                eStore_payment_debug('No Item found for the Item ID: '.$cart_item_data_num,false);
                return false;
            }    			

		    $item_name = $cart_item_data_name;
            $download_link = generate_download_link($retrieved_product,$item_name,$payment_data);
		    eStore_payment_debug('Download Link : '.$download_link,true);		               

            array_push($product_name_array, $cart_item_data_name);
            array_push($download_link_array, $download_link);            
		}
    }
    
    for ($i=0; $i < sizeof($product_name_array); $i++)
    {
    	//Download links list for all items in the cart
        $constructed_download_link .= "\n";
        if (is_array($download_link_array[$i]))
        {
	    	$package_downloads = $download_link_array[$i];
	        for ($j=0; $j < sizeof($package_downloads); $j++)
	        {
	        	$constructed_download_link .= $package_downloads[$j];
	        	$constructed_download_link .= "\n";
	        }
        }
        else
        {
        	$constructed_download_link .= $download_link_array[$i];
        }    	
    }
    	
    return $constructed_download_link;
}
