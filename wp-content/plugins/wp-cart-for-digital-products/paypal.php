<?php
if (!defined('ABSPATH')){include_once ('../../../wp-load.php');}
include_once('wp_cart_for_digital_products.php');
include_once('eStore_email.php');
include_once('eStore_classes.php');
include_once('eStore_handle_subsc_ipn.php');
include_once('eStore_post_payment_processing_helper.php');
include_once('eStore_includes4.php');
include_once('eStore_auto_responder_handler.php');

status_header(200);

$error_msg='';

class paypal_ipn_handler {

   var $last_error;                 // holds the last error encountered
   var $ipn_log;                    // bool: log IPN results to text file?
   var $ipn_log_file;               // filename of the IPN log
   var $ipn_response;               // holds the IPN response from paypal
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   var $sandbox_mode = false;

   	function paypal_ipn_handler()
   	{
        $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
      	$this->last_error = '';
      	$this->ipn_log_file = WP_ESTORE_PATH.'ipn_handle_debug.log';
      	$this->ipn_response = '';
    }

   	function validate_and_dispatch_product()
	{
		// Check Product Name , Price , Currency , Receivers email ,
		global $error_msg;
		global $wpdb;
                $wp_eStore_config = WP_eStore_Config::getInstance();
                $clientdate = (date ("Y-m-d"));
		$clienttime	= (date ("H:i:s"));
		$product_specific_instructions = "";
		$currency_symbol = get_option('cart_currency_symbol');
		
		//Post/Forward IPN data to external URL if needed
		eStore_POST_IPN_data_to_url($this->ipn_data);
		
   		// Read the IPN and validate
   		if (get_option('eStore_strict_email_check') != '')
   		{
    		$seller_paypal_email = get_option('cart_paypal_email');
    		if ($seller_paypal_email != $this->ipn_data['receiver_email'])
    		{
                $error_msg .= 'Invalid Seller Paypal Email Address : '.$this->ipn_data['receiver_email'];
    			$this->debug_log($error_msg,false);
    			return false;
    		}
    		else
    		{
                $this->debug_log('Seller Paypal Email Address is Valid: '.$this->ipn_data['receiver_email'],true);
            }
    	}
    	
    	$payment_status = $this->ipn_data['payment_status'];
    	if (!empty($payment_status))
    	{
    		if ($payment_status == "Denied")
    		{
    			$error_msg .= 'Payment status for this transaction is DENIED. You denied the transaction... most likely a cancellation of an eCheque.';
    			$this->debug_log("You denied the transaction. Most likely a cancellation of an eCheque. Nothing to do here.",false);
    			return false;
    		}
    		if ($payment_status == "Canceled_Reversal")
    		{
    			$this->debug_log("This is a dispute closed notification in your favour. The plugin will not do anyting.",false);
    			return true;    			
    		}
	        if ($payment_status != "Completed" && $payment_status != "Processed" && $payment_status != "Refunded" && $payment_status != "Reversed")
	        {
                $error_msg .= 'Funds have not been cleared yet. Product(s) will be delivered when the funds clear!';
	  			$this->debug_log($error_msg,false);

	  			$to_address = $this->ipn_data['payer_email'];
                $subject = ESTORE_PENDING_PAYMENT_EMAIL_SUBJECT;
                $body = ESTORE_PENDING_PAYMENT_EMAIL_BODY;
                $from_address = get_option('eStore_download_email_address');
                eStore_send_notification_email($to_address, $subject, $body, $from_address);
	    		return false;
	        }
    	}   	

		$transaction_type = $this->ipn_data['txn_type'];
		if($transaction_type == "new_case")
		{
			$this->debug_log('This is a dispute case',true);
			return true;
		}
		
		$transaction_id = $this->ipn_data['txn_id'];
		$transaction_subject = $this->ipn_data['transaction_subject'];
				
        $custom = $this->ipn_data['custom'];
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
        $eMember_id = $customvariables['eMember_id'];
        $pictureID = $customvariables['ngg_pid'];
		        
		//Check for refund payment
		$gross_total = $this->ipn_data['mc_gross'];
		if ($gross_total < 0)
		{
			// This is a refund or reversal so handle the refund
			eStore_handle_refund($this->ipn_data);
			$this->debug_log('This is a refund/reversal. Refund amount: '.$gross_total,true);
			return true;
		}
		
		//Check for duplicate notification due to server setup issue
		if(eStore_is_txn_already_processed($this->ipn_data)){
			$error_msg .= 'The transaction ID and the email address already exists in the database. So this seems to be a duplicate transaction notification. This usually happens with bad server setup.';
			$this->debug_log('The transaction ID and the email address already exists in the database. So this seems to be a duplicate transaction notification. This usually happens with bad server setup.',false);
			return true; //No need to be alarmed			
		}
		//=== End of duplicate notification check ===	
		
		//Fire the begin processing hook
		do_action('eStore_begin_payment_processing',$this->ipn_data['payer_email'],$customvariables['ip']);
		
		$time = time();
		global $wpdb;
		$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
		$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
		$sales_table_name = $wpdb->prefix . "wp_eStore_sales_tbl";
				
		if ($transaction_type == "cart")
		{
			$this->debug_log('Transaction Type: Shopping Cart',true);
			// Cart Items
			$num_cart_items = $this->ipn_data['num_cart_items'];
			$this->debug_log('Number of Cart Items: '.$num_cart_items,true);

			$i = 1;
			$cart_items = array();
			while($i < $num_cart_items+1)
			{
				$item_number = $this->ipn_data['item_number' . $i];
				$item_name = $this->ipn_data['item_name' . $i];
				//$item_name = mb_convert_encoding($item_name, "UTF-8");
				$quantity = $this->ipn_data['quantity' . $i];
				$mc_gross = $this->ipn_data['mc_gross_' . $i];
				$mc_shipping = $this->ipn_data['mc_shipping' . $i];
				$mc_currency = $this->ipn_data['mc_currency'];

				$current_item = array(
									   'item_number' => $item_number,
									   'item_name' => $item_name,
									   'quantity' => $quantity,
									   'mc_gross' => $mc_gross,
									   'mc_shipping' => $mc_shipping,
									   'mc_currency' => $mc_currency,
									  );

				array_push($cart_items, $current_item);
				$i++;
			}
		}
		else if (($transaction_type == "subscr_signup"))
		{
			$this->debug_log('Subscription signup IPN received... (handled by the subscription IPN handler). Check the "subscription_handle_debug.log" file more details.',true);
			if(eStore_chk_and_record_cust_data_for_free_trial_signup($this->ipn_data)){//Check and record customer data for free trial
				return true;
			}
			$subsc_prod_id = $this->ipn_data['item_number'];
			$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$subsc_prod_id'", OBJECT);
			if(!$ret_product){
                $error_msg .= 'Request received for subscription product ID: '.$subsc_prod_id.'. Could not find this Product ID in the product database (please check the manage products menu and verify that you have specified the correct product ID).';
		    	$this->debug_log($error_msg,false);
         		return false;				
			}
			$subsc_ref = $ret_product->ref_text;
			if (!empty($subsc_ref)){//Do membership signup task
				$this->debug_log('Reference Text field value: '.$subsc_ref,true);
				if (get_option('eStore_enable_wishlist_int')){
                	$this->debug_log('WishList integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);
        			wl_handle_subsc_signup($this->ipn_data,$subsc_ref,$this->ipn_data['subscr_id']);
				}
				else if (function_exists('wp_eMember_install')){
                	$this->debug_log('eMember integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);                                    
                	eMember_handle_subsc_signup($this->ipn_data,$subsc_ref,$this->ipn_data['subscr_id'],$eMember_id);
                }
			}
			return true;
		}
		else if (($transaction_type == "subscr_cancel") || ($transaction_type == "subscr_eot") || ($transaction_type == "subscr_failed"))
		{
			if (get_option('eStore_enable_wishlist_int'))
			{
			    wl_handle_subsc_cancel($this->ipn_data);
                        }
                        else
                        {
                            // Code to handle the IPN for subscription cancellation
                            if (function_exists('wp_eMember_install'))
                            {
                                eMember_handle_subsc_cancel($this->ipn_data);
                            }
                        }
			$this->debug_log('Subscription cancellation IPN received... nothing to do here(handled by the subscription IPN handler)',true);
			return true;
		}
		else
		{
			$cart_items = array();
			$this->debug_log('Transaction Type (Buy Now/Subscribe): '.$transaction_type,true);
			$item_number = $this->ipn_data['item_number'];
			$item_name = $this->ipn_data['item_name'];
			//$item_name = mb_convert_encoding($item_name, "UTF-8");
			$quantity = $this->ipn_data['quantity'];
			if(empty($quantity)){$quantity = 1;}
			
			$mc_tax = $this->ipn_data['tax'];
			if(!empty($mc_tax)){//For "web_accept" txn, the total tax is included in the "mc_gross" amt.
				$mc_gross = $this->ipn_data['mc_gross'] - $mc_tax;
				$this->debug_log('Deducting tax amount ('.$mc_tax.') from mc_gross amt',true);
			}else{
				$mc_gross = $this->ipn_data['mc_gross'];
			}
			
			$mc_shipping = $this->ipn_data['mc_shipping'];
			$mc_currency = $this->ipn_data['mc_currency'];

			$current_item = array(
									   'item_number' => $item_number,
									   'item_name' => $item_name,
									   'quantity' => $quantity,
									   'mc_gross' => $mc_gross,
									   'mc_shipping' => $mc_shipping,
									   'mc_currency' => $mc_currency,
									  );

			array_push($cart_items, $current_item);
		}
		
        // URL of directory where script is stored ( include trailing slash )
		$script_location = get_option('eStore_download_script');
		$random_key = get_option('eStore_random_code');
		$payment_currency = get_option('cart_payment_currency');

	    $product_id_array = Array();
	    $product_name_array = Array();
	    $product_price_array = Array();
	    $product_qty_array = Array();
	    $download_link_array = Array();
	    $download_link_for_digital_item = Array();
	    
	    $product_key_data = "";
        $counter = 0;
		foreach ($cart_items as $current_cart_item)
		{
			$cart_item_data_num = $current_cart_item['item_number'];
			$key=$cart_item_data_num;
			$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$key'", OBJECT);
			if(!$retrieved_product)
			{
                $error_msg .= 'Request received for product ID: '.$cart_item_data_num.'. Could not find this Product ID in the product database (please check the manage products menu and verify that you have specified the correct product ID).';
		    	$this->debug_log($error_msg,false);
         		return false;				
			}
						
			$cart_item_data_name = trim($current_cart_item['item_name']); //$retrieved_product->name;	
			$cart_item_data_quantity = $current_cart_item['quantity'];
			$cart_item_data_total = $current_cart_item['mc_gross'];
			$cart_item_shipping = $current_cart_item['mc_shipping'];
			$cart_item_data_currency = $current_cart_item['mc_currency'];
			if(empty($cart_item_data_quantity))
			{
				$cart_item_data_quantity = 1;
			}
			$this->debug_log('Item Number: '.$cart_item_data_num,true);
			$this->debug_log('Item Name: '.$cart_item_data_name,true);
			$this->debug_log('Item Quantity: '.$cart_item_data_quantity,true);
			$this->debug_log('Item Price: '.$cart_item_data_total,true);
			$this->debug_log('Item Shipping: '.$cart_item_shipping,true);
			$this->debug_log('Item Currency: '.$cart_item_data_currency,true);

			// Compare the values with the values stored in the database
			$coupon_code = $customvariables['coupon'];
			if(!empty($coupon_code))
			{
                $this->debug_log('Coupon Code Used : '.$coupon_code,true);
                $coupon_table_name = $wpdb->prefix . "wp_eStore_coupon_tbl";
                $ret_coupon = $wpdb->get_row("SELECT * FROM $coupon_table_name WHERE coupon_code = '$coupon_code'", OBJECT);
                if ($ret_coupon)
                {
                    $discount_amount = $ret_coupon->discount_value;
                    $discount_type = $ret_coupon->discount_type;
                    if ($discount_type == 0){//apply % discount
                        $discount = ($retrieved_product->price*$discount_amount)/100;
                        $true_product_price = $retrieved_product->price - $discount;
                    }
                    else{// apply value discount
                        $true_product_price = $retrieved_product->price - $discount_amount;
                    }
                }
			    else{
                	eStore_payment_debug('Could not find the coupon in the database: '.$coupon_code,false);
                }
                if($transaction_type == "subscr_payment"){
                	$true_product_price = 0;//Used coupon on subscription product
                	eStore_payment_debug('Coupon discount was used on a subscription product',true);
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
				$this->debug_log('Price and currency check override enabled by filter eStore_before_checking_price_filter',true);
			}
			if($check_price){
	            $true_product_price = round($true_product_price, 2);
				if ($cart_item_data_total < $true_product_price)
				{
	                $error_msg .= 'Wrong Product Price Detected! Actual Product Price : '.$true_product_price.' Amount Paid: '.$cart_item_data_total;
			    	$this->debug_log($error_msg,false);
	         		return false;
				}
				
				if(!empty($retrieved_product->currency_code))
				    $payment_currency = $retrieved_product->currency_code;
				if ($payment_currency != $cart_item_data_currency)
				{
	                $error_msg .= 'Invalid Product Currency Detected! The payment was made in currency: '.$cart_item_data_currency;                
			    	$this->debug_log($error_msg,false);
			    	$this->debug_log('You specified to receive payment in: '.$payment_currency.' for this product. Check eStore settings or this product\'s Buy Now/Subscription section and correct the currency code to fix this issue.',false);
	         		return false;
				}
			}

		    //*** Handle Membership Payment ***
		    $member_ref = $retrieved_product->ref_text;
		    $this->debug_log('Value of the reference text field for this product: '.$member_ref,true);
		    if (!empty($member_ref))
		    {	    	
		    	if ($transaction_type == "web_accept")
		    	{
			    	if (get_option('eStore_enable_wishlist_int')){
						$this->debug_log('WishList integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);
						wl_handle_subsc_signup($this->ipn_data,$member_ref,$this->ipn_data['txn_id']);
					}
					else{
						if (function_exists('wp_eMember_install')){
							$this->debug_log('eMember integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);
							eMember_handle_subsc_signup($this->ipn_data,$member_ref,$this->ipn_data['txn_id'],$eMember_id);
						}
					}
		    	}
		    	else if($transaction_type == "cart")
		    	{
			    	if (get_option('eStore_enable_wishlist_int')){
                    	$this->debug_log('WishList integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);
						wl_handle_subsc_signup($this->ipn_data,$member_ref,$this->ipn_data['txn_id']);
					}
                	else{
    		        	if (function_exists('wp_eMember_install')){
                        	$this->debug_log('eMember integration is being used... creating member account... see the "subscription_handle_debug.log" file for details',true);
    		                eMember_handle_subsc_signup($this->ipn_data,$member_ref,$this->ipn_data['txn_id'],$eMember_id);
                        }
                    }
		    	}
		    	else if($transaction_type == "subscr_payment")
		    	{
		    		$subscr_id=$this->ipn_data['subscr_id'];
		    		eStore_update_member_subscription_start_date_if_applicable($this->ipn_data,$subscr_id);
		    	}		    	
		    }
		    //== End of Membership payment handling ==

		    $product_id = $retrieved_product->id;
		    
		    //Check if nextgen gallery integration is being used
		    $pid_check_value = eStore_is_ngg_pid_present($cart_item_data_name);
		    if($pid_check_value != -1)
		    {
		    	$pictureID = $pid_check_value;
		    }
		    //Generate link from Nextgen gallery if PID is present.
		    if(!empty($pictureID))
		    {
		    	$download_link = eStore_get_ngg_image_url($pictureID,$cart_item_data_name);
		    	$pictureID = "";
		    }
		    else
		    {
                        $this->debug_log('Generating encrypted download link for this product.',true);
                        $download_link = generate_download_link($retrieved_product,$cart_item_data_name,$this->ipn_data);
		    }
		    $this->debug_log('Download Link: [hidden]',true);//$download_link 

            $product_specific_instructions .= eStore_get_product_specific_instructions($retrieved_product);  
            
            //Product license key generation if using the license manager
            if (function_exists('wp_lic_manager_install'))
            {
	            $product_license_data .= eStore_check_and_generate_license_key($retrieved_product,$this->ipn_data);
	            $this->debug_log('License Data: [hidden]',true);//$product_license_data
            }
            
            //Issue serial key if this feature is being used            
            $product_key_data .= eStore_post_sale_retrieve_serial_key_and_update($retrieved_product,$cart_item_data_name,$cart_item_data_quantity);
		    
            array_push($product_name_array, $cart_item_data_name);
            array_push($product_id_array, $product_id);
            array_push($product_price_array, $cart_item_data_total);
            array_push($product_qty_array, $cart_item_data_quantity);            
            array_push($download_link_array, $download_link);
            if(eStore_check_if_string_contains_url($download_link)){
            	array_push($download_link_for_digital_item, $download_link);  
            }          
           
            $counter++;
            $download_link = '';
		}

		if(!empty($product_key_data)){
			$this->ipn_data['product_key_data'] = $product_key_data;
		}
		
		// How long the download link remain valid (hours)
		$download_url_life = get_option('eStore_download_url_life');

		// Email settings data
		$notify_email = get_option('eStore_notify_email_address');  // Email which will receive notification of sale (sellers email)
		$download_email = get_option('eStore_download_email_address'); // Email from which the mail wil be sent from
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

        $purchase_date = (date ("Y-m-d"));
        $total_purchase_amt = $this->ipn_data['mc_gross'];
        $txn_id = $this->ipn_data['txn_id'];
        $total_tax = $this->ipn_data['tax'];
        $total_shipping = round(($this->ipn_data['mc_handling'] + $this->ipn_data['mc_shipping']),2);
        $total_minus_total_tax = round(($total_purchase_amt - $total_tax),2);

        $this->ipn_data['mc_tax'] = $total_tax;
        $this->ipn_data['mc_shipping'] = $total_shipping;
        
        //Counter for incremental receipt number	    
		$last_records_id = $wp_eStore_config->getValue('eStore_custom_receipt_counter');//get_option('eStore_custom_receipt_counter');
		if (empty($last_records_id))
		{
			$last_records_id = 0;
		}
		$receipt_counter = $last_records_id + 1;
		$this->debug_log('Incremental counter value for PayPal checkout: '.$receipt_counter,true);
		$wp_eStore_config->setValue('eStore_custom_receipt_counter',$receipt_counter);
		$wp_eStore_config->saveConfig();	 
        
        $buyer_shipping_info = "\n".$this->ipn_data['address_name'];
        $buyer_shipping_info .= "\n".$this->ipn_data['address_street'];
        $buyer_shipping_info .= "\n".$this->ipn_data['address_city'];
        $buyer_shipping_info .= "\n".$this->ipn_data['address_state']." ".$this->ipn_data['address_zip'];
        $buyer_shipping_info .= "\n".$this->ipn_data['address_country'];      
        $buyer_shipping_info .= "\n".$this->ipn_data['contact_phone'];        
        $this->ipn_data['address'] = $buyer_shipping_info;
        
        $buyer_phone = $this->ipn_data['contact_phone'];
        
        $shipping_option = $customvariables['ship_option'];
        if(empty($shipping_option)){$shipping_option = "Default";}
        
        $product_specific_instructions = eStore_apply_post_payment_dynamic_tags($product_specific_instructions, $this->ipn_data, $cart_items );
       
        $tags = array("{first_name}","{last_name}","{payer_email}","{product_name}","{product_link}","{product_price}","{product_id}","{download_life}",
            "{product_specific_instructions}","{product_details}","{product_details_tax_inclusive}","{shipping_info}","{license_data}","{purchase_date}",
            "{purchase_amt}","{transaction_id}","{shipping_option_selected}","{product_link_digital_items_only}","{total_tax}","{total_shipping}",
            "{total_minus_total_tax}","{customer_phone}","{counter}","{coupon_code}","{serial_key}","{eMember_id}");
        
        $vals = array($this->ipn_data['first_name'],$this->ipn_data['last_name'],$this->ipn_data['payer_email'],$constructed_products_name,$constructed_download_link,
            $constructed_products_price,$constructed_products_id,$download_url_life,$product_specific_instructions,$constructed_products_details,
            $constructed_products_details_tax_inc,$buyer_shipping_info,$product_license_data,$purchase_date,$total_purchase_amt,$txn_id,$shipping_option,
            $constructed_download_link_for_digital_item,$total_tax,$total_shipping,$total_minus_total_tax,$buyer_phone,$receipt_counter,$coupon_code,
            $product_key_data,$eMember_id);

        $subject = str_replace($tags,$vals,$email_subject);
        $body = stripslashes(str_replace($tags,$vals,$email_body));
        $headers = 'From: '.$download_email . "\r\n";
        $attachment = '';

        //Call the filter for email notification body
        $this->debug_log('Applying filter - eStore_notification_email_body_filter',true);
        $body = apply_filters('eStore_notification_email_body_filter', $body, $this->ipn_data, $cart_items);
        
        // Determine if it's a recurring payment
        $recurring_payment = is_paypal_recurring_payment($this->ipn_data);

        if (!$recurring_payment) //Don't send emails for recurring payments
        {
        	if (get_option('eStore_send_buyer_email'))
        	{
	            if (get_option('eStore_use_wp_mail'))
	            {
                        wp_eStore_send_wp_mail($this->ipn_data['payer_email'], $subject, $body, $headers);
	                //wp_mail($this->ipn_data['payer_email'], $subject, $body, $headers);
	                $this->debug_log('Product Email successfully sent to '.$this->ipn_data['payer_email'].'.',true);
	            }
	            else
	            {
	            	if(@eStore_send_mail($this->ipn_data['payer_email'],$body,$subject,$download_email,$attachment))
	            	{
	            	   	$this->debug_log('Product Email successfully sent (using PHP mail) to '.$this->ipn_data['payer_email'].'.',true);
	            	}
	            	else
	            	{
	                    $this->debug_log('Error sending product Email (using PHP mail) to '.$this->ipn_data['payer_email'].'.',false);
	            	}
	            }
        	}
        }
	    // Notify seller
		$n_subject = str_replace($tags,$vals,$notify_subject);
		$n_body = str_replace($tags,$vals,$notify_body);
		if ($wp_eStore_config->getValue('eStore_add_payment_parameters_to_admin_email') == '1')
		{
			$n_body .= "\n\n------- User Email ----------\n".
	                  $body.
	                  "\n\n------- Paypal Parameters (Only admin will receive this) -----\n".
	                  $this->post_string;
		}	                  
        $n_body = stripslashes($n_body);
        
        if (!$recurring_payment) //Don't send emails for recurring payments
        {
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
                        $this->debug_log('Notify Email successfully sent to '.$recipient_email_address.'.',true);
                    }
                    else
                    {
                        if(@eStore_send_mail($recipient_email_address,$n_body,$n_subject,$download_email))
                        {
                            $this->debug_log('Notify Email successfully sent (using PHP mail) to '.$recipient_email_address.'.',true);
                        }
                        else
                        {
                            $this->debug_log('Error sending notify Email (using PHP mail) to '.$recipient_email_address.'.',false);
                        }
                    }					
                }
            }        	
        }
        // Do Post operations
        if (!$recurring_payment)
        {
            $this->debug_log('Updating Products, Customers, Coupons, Sales Database Tables with Sales Data.',true);
    			
            $firstname = $this->ipn_data['first_name'];
            $lastname = $this->ipn_data['last_name'];
            $emailaddress = $this->ipn_data['payer_email'];            
            $address = esc_sql(stripslashes($buyer_shipping_info));
            $phone = $this->ipn_data['contact_phone'];
            $subscr_id = $this->ipn_data['subscr_id'];            
            $customer_ip = $customvariables['ip'];
            if(empty($customer_ip)){$customer_ip = "No information";}
            $product_key_data = $this->ipn_data['product_key_data'];
            if(empty($product_key_data)){$product_key_data = "";}
            $notes = "";			
            $status = "Paid";

            if (function_exists('wp_eMember_install') && empty($eMember_id)){//eMember purchase history additional check
                $this->debug_log('No eMember ID was passed so the user was not logged in. Quering member database to see if a user account exists for: '.$emailaddress,true);
                $members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
                $query_emem_db = $wpdb->get_row("SELECT member_id FROM $members_table_name WHERE email = '$emailaddress'", OBJECT);
                if($query_emem_db){
                        $eMember_id = $query_emem_db->member_id;
                        $this->debug_log('Found a user account with the purchaser email address. adding this purchase to account ID: '.$eMember_id,true);					
                }
            }
            
            $counter = 0;
            foreach ($cart_items as $current_cart_item)
            {
                $cart_item_data_num = $current_cart_item['item_number'];
                $cart_item_data_name = $current_cart_item['item_name'];    			
                $key=$cart_item_data_num;
                $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$key'", OBJECT);
                $current_product_id = $cart_item_data_num;
                $cart_item_qty = $current_cart_item['quantity'];
                $sale_price = $current_cart_item['mc_gross'];

                if(empty($cart_item_qty)){
                        $cart_item_qty = 1;
                }
                $this->debug_log('Product ID: '.$cart_item_data_num.'. Current available copies value: '.$retrieved_product->available_copies.' Sales count value: '.$retrieved_product->sales_count,true);

                $new_available_copies = "";
                if (is_numeric($retrieved_product->available_copies))
                {                	                	
                    $new_available_copies = ($retrieved_product->available_copies - $cart_item_qty);
                }
                $new_sales_count = ($retrieved_product->sales_count + $cart_item_qty);
                $this->debug_log('New available copies value: '.$new_available_copies.' New sales count value: ' .$new_sales_count,true);

                $updatedb = "UPDATE $products_table_name SET available_copies = '$new_available_copies', sales_count = '$new_sales_count' WHERE id='$current_product_id'";
                $results = $wpdb->query($updatedb);

                // Update the Customer table
                $product_name = esc_sql(stripslashes($cart_item_data_name));
                $eMember_username =  $eMember_id;			             			                    
                $updatedb = "INSERT INTO $customer_table_name (first_name, last_name, email_address, purchased_product_id,txn_id,date,sale_amount,coupon_code_used,member_username,product_name,address,phone,subscr_id,purchase_qty,ipaddress,status,serial_number,notes) VALUES ('$firstname', '$lastname','$emailaddress','$current_product_id','$transaction_id','$clientdate','$sale_price','$coupon_code','$eMember_username','$product_name','$address','$phone','$subscr_id','$cart_item_qty','$customer_ip','$status','$product_key_data','$notes')";
                $results = $wpdb->query($updatedb);

                $updatedb2 = "INSERT INTO $sales_table_name (cust_email, date, time, item_id, sale_price) VALUES ('$emailaddress','$clientdate','$clienttime','$current_product_id','$sale_price')";
                $results = $wpdb->query($updatedb2);    			
            }
            if(!empty($coupon_code))
            {
            	$coupon_table_name = $wpdb->prefix . "wp_eStore_coupon_tbl";
                $ret_coupon = $wpdb->get_row("SELECT * FROM $coupon_table_name WHERE coupon_code = '$coupon_code'", OBJECT);
                if ($ret_coupon)
                {
                	$redemption_count = $ret_coupon->redemption_count + 1;
    	            $updatedb = "UPDATE $coupon_table_name SET redemption_count = '$redemption_count' WHERE coupon_code='$coupon_code'";
    				$results = $wpdb->query($updatedb);            	
                }        	
            }
            $this->debug_log('Products, Customers, Coupons, Sales Database Tables Updated.',true);

            //Autoresponder signups
            eStore_item_specific_autoresponder_signup($cart_items,$firstname,$lastname,$emailaddress);
			eStore_global_autoresponder_signup($firstname,$lastname,$emailaddress);
			
			$this->ipn_data['eMember_userid'] = $eMember_id;//need to add the member ID to the IPN data
			do_action('eStore_product_database_updated_after_payment',$this->ipn_data,$cart_items);//eStore's action after post payment product database is update
        }

        $this->debug_log('Updating Affiliate Database Table with Sales Data if Using the WP Affiliate Platform Plugin.',true);
        if (eStore_affiliate_capability_exists())
        {
        	//$this->debug_log('WP Affiliate Platform is installed, checking referral details...',true);      	        	
        	$award_commission = true;
        	if(get_option('eStore_aff_one_time_commission'))
        	{
        		if($recurring_payment)
        		{
        			$award_commission = false;
        			$this->debug_log('One time commission option is being used, This is a recurring payment and will not generate affiliate commission.',true);
        		}
        	}        	
        	if($award_commission)
        	{        		
				$this->debug_log('Affiliate Commission may need to be tracked. See the "eStore_post_payment_debug.log" file for more details on commission calculation',true);
				eStore_aff_award_commission($this->ipn_data,$cart_items);
        	}	
        	
        	//Handle auto affiliate account creation if this feature is used
        	eStore_handle_auto_affiliate_account_creation($this->ipn_data);		
        }
		else
		{
			$this->debug_log('Not Using the WP Affiliate Platform Plugin.',true);
		}  

		//Fire Recurring payment action hook
		if($recurring_payment){
			$this->debug_log('Firing the PayPal recurring payment action hook.',true);
			do_action('eStore_paypal_recurring_payment_received',$this->ipn_data,$cart_items);
		}
		
		//Revenue sharing
		$share_revenue = get_option('eStore_aff_enable_revenue_sharing');
		if(!empty($share_revenue))
		{
			eStore_award_author_commission($this->ipn_data,$cart_items);
		}
		
		//POST IPN Data to memberwing script if specified in the settings
		$memberwing_external_post_url = get_option('eStore_memberwing_ipn_post_url');
		if(!empty($memberwing_external_post_url))
		{
			$this->debug_log('Posting IPN data to Memberwing plugin :'.$memberwing_external_post_url,true);
			eStore_POST_IPN_data_to_url($this->ipn_data,$memberwing_external_post_url);
		}
		
	    return true;
   	}

    function validate_ipn()
    {
      // parse the paypal URL
      $url_parsed=parse_url($this->paypal_url);

      // generate the post string from the _POST vars aswell as load the _POST vars into an arry
      $post_string = '';
      foreach ($_POST as $field=>$value) {
         $this->ipn_data["$field"] = $value;
         $post_string .= $field.'='.urlencode(stripslashes($value)).'&';
      }

      $this->post_string = $post_string;
      $this->debug_log('Post string : '. $this->post_string,true);

      $post_string.="cmd=_notify-validate"; // append ipn command

      if(!function_exists('fsockopen')){
          $this->debug_log('This site does not have fsockopen() enabled. Trying curl to verify IPN.', true);
          return $this->validate_ipn_using_curl($post_string);
      }

      // open the connection to paypal
      if($this->sandbox_mode){//connect to PayPal sandbox
	      $uri = 'ssl://'.$url_parsed['host'];
	      $port = '443';         	
	      $fp = fsockopen($uri,$port,$err_num,$err_str,30);
      }
      else{//connect to live PayPal site using standard approach
      	$fp = fsockopen($url_parsed['host'],"80",$err_num,$err_str,30);
      }
      
      if(!$fp)
      {
         // could not open the connection.  If loggin is on, the error message
         // will be in the log.
         $this->debug_log('Connection to '.$url_parsed['host']." failed. fsockopen error no. $errnum: $errstr",false);
         return false;

      }
      else
      {
         // Post the data back to paypal
         fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n");
         fputs($fp, "Host: $url_parsed[host]\r\n");
         fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
         fputs($fp, "Content-length: ".strlen($post_string)."\r\n");
         fputs($fp, "Connection: close\r\n\r\n");
         fputs($fp, $post_string . "\r\n\r\n");

         // loop through the response from the server and append to variable
         while(!feof($fp)) {
            $this->ipn_response .= fgets($fp, 1024);
         }

         fclose($fp); // close connection

         $this->debug_log('Connection to '.$url_parsed['host'].' successfuly completed.',true);
      }

      //if (eregi("VERIFIED",$this->ipn_response))
      if (strpos($this->ipn_response, "VERIFIED") !== false)// Valid IPN transaction.
      {
         $this->debug_log('IPN successfully verified.',true);
         return true;
      }
      else
      {
         // Invalid IPN transaction. Check the log for details.
         $this->debug_log('IPN validation failed.',false);
         return false;
      }
   }
   
   function validate_ipn_using_curl($post_string)
   {
        $notify_url = PAYPAL_LIVE_URL;
        if($this->sandbox_mode){//Override to PayPal sandbox
            $notify_url = PAYPAL_SANDBOX_URL;
        }
        $this->debug_log('Starting PayPal IPN verification via CURL. Connecting to: '.$notify_url, false);
        $ch = curl_init($notify_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        
        if(!$res){
            $this->debug_log('HTTP ERROR... could not establish a connection to PayPal for IPN verification!', false);
            return false;
        }
        
        if (strcmp ($res, "VERIFIED") == 0) {
            $this->debug_log('PayPal IPN successfully verified using CURL.', false);
            return true;
        } 
        else if (strcmp ($res, "INVALID") == 0) {// IPN invalid
            return false;
        }
        return false;
   }

   function debug_log($message,$success,$end=false)
   {
   	  if (!$this->ipn_log) return;  // is logging turned off?

      // Timestamp
      $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :').$message. "\n";

      if ($end) {
      	$text .= "\n------------------------------------------------------------------\n\n";
      }
      // Write to log
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text );
      fclose($fp);
   }
}

// Start of IPN handling (script execution)
$ipn_handler_instance = new paypal_ipn_handler();

$debug_enabled = get_option('eStore_cart_enable_debug');
if ($debug_enabled)
{
	echo 'Debug is enabled. Check the ipn_handle_debug.log file for debug output.';
	$ipn_handler_instance->ipn_log = true;
	if(empty($_POST))
	{
		$ipn_handler_instance->debug_log('This debug line was generated because you entered the URL of the ipn handling script in the browser.',true,true);
		exit;
	}
}

$sandbox = get_option('eStore_cart_enable_sandbox');
if ($sandbox) // Enable sandbox testing
{
	$ipn_handler_instance->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	$ipn_handler_instance->sandbox_mode = true;
}

$ipn_handler_instance->debug_log('Paypal Class Initiated by '.$_SERVER['REMOTE_ADDR'].' eStore Version: '.WP_ESTORE_VERSION,true);

if ($ipn_handler_instance->validate_ipn())
{
	$ipn_handler_instance->debug_log('Creating product Information to send.',true);

      if(!$ipn_handler_instance->validate_and_dispatch_product())
      {
          $ipn_handler_instance->debug_log('IPN product validation failed.',false);

          $to_address = get_option('eStore_notify_email_address');
          $subject = "WP eStore - Payment Verification Failed!";
          $body = "This is a notification email from the eStore plugin letting you know that a payment verification failed.".
          		  "\n\nPlease fully read this email as it will explain everything you need to know to address any issue(s) that maybe occuring (if there are any)!".
          		  "\n\nThe post payment verification for a payment notification failed. This could happen for one of the following reasons:".
                  "\n\n 1. The fund for the payment have not cleared in PayPal yet (no need to do anything as the digital product will be delivered once the fund clears).".
                  "\n 2. Someone maybe trying to scam a purchase! (no worries... WP eStore got your back :)".
          		  "\n 3. You have made a mistake somewhere (please see the failure reason section below to find out more details about this).".
          		  "\n\nThe following section has more details on why this failure occurred.".
          		  "\n\n===== Exact Reason for This Failure =====".
          		  "\n".
                  "\nThe transaction failed for the following reason...\n".$error_msg.
          		  "\n\n===== How to solve the 'Funds have not cleared' or 'Pending payment received' notification =====".
          		  "\n".
          		  "\nIf you have received a pending payment meaning the funds have not cleared for this payment and you don't know the reason then this link should help".
          		  "\nhttp://www.tipsandtricks-hq.com/forum/topic/reasons-for-a-pending-paypal-payment".
          		  "\n\n===== PayPal parameters for this transaction =====".
          		  "\n\n".
          		  $ipn_handler_instance->post_string;

          $from_address = get_option('eStore_download_email_address');
          eStore_send_notification_email($to_address, $subject, $body, $from_address);
      }
}
$ipn_handler_instance->debug_log('Paypal class finished.',true,true);
