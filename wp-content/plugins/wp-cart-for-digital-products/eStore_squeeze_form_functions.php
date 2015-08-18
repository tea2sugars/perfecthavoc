<?php
require_once('eStore_classes/_loader.php');
include_once('eStore_email.php');
include_once('eStore_includes2.php');
include_once('eStore_auto_responder_handler.php');
function eStore_process_squeeze_form_submission($name,$email,$prod_id,$ap_id,$clientip)
{
	global $eStore_debug_manager;
	$eStore_debug_manager->squeeze_form("Processing free download request for squeeze form submission...", ESTORE_LEVEL_SUCCESS);
	if(empty($email) || empty($prod_id)){
		$eStore_debug_manager->squeeze_form("Error! Email or Product ID value is missing. Cannot process this request.", ESTORE_LEVEL_FAILURE);
	}
	
	if(!is_numeric($prod_id)){
		$eStore_debug_manager->squeeze_form("Decrypting product ID value: ".$prod_id, ESTORE_LEVEL_SUCCESS);
		$prod_id = base64_decode($prod_id);
	}
	$eStore_debug_manager->squeeze_form("Received Data...[Name:".$name."][Email:".$email."][Product ID:".$prod_id."][ap_id:".$ap_id."][IP Address:".$clientip."]", ESTORE_LEVEL_SUCCESS);
	
	//Check the email address validity
	if ( !is_email($email) ){
		$eStore_debug_manager->squeeze_form("Email address (".$email.") is not valid. This request will not be processed.", ESTORE_LEVEL_FAILURE,true);
		exit;
	}
	
	global $wpdb;
	$wp_eStore_config = WP_eStore_Config::getInstance();
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
	        
	$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$prod_id'", OBJECT);
	//Perform verification if the the "Enforce Zero Price Checking on Free Product Download" option is enabled.
	if ($wp_eStore_config->getValue('eStore_product_price_must_be_zero_for_free_download')=='1')
	{
	    if(!is_numeric($retrieved_product->price) || $retrieved_product->price > 0){    	
	    	$error_msg = "Error! The admin of this site requires the product price to be set to 0.00 before it can be given as a free download!";
	    	eStore_send_free_download1($name, $email, $error_msg);
	    	$eStore_debug_manager->squeeze_form($error_msg, ESTORE_LEVEL_FAILURE,true);
	    	exit;
	    }    	
	}
	
	// These 2 lines of code ensure the Ajax version of the "squeeze form" now passes its data through to the PDF Stamper addon.
	// -- The Assurer, 2010-09-12.
	$payment_data = free_download_pseudo_payment_data($name, $email);// Populate the pseudo payment data.
	$cart_items = eStore_create_item_data($prod_id);// Populate the pseudo cart data.        
	$download = generate_download_link_for_product($prod_id, '', $payment_data);	// Generate the download link.
	//$download = generate_download_link_for_product($prod_id);
	
	if (eStore_send_free_download1($name, $email, $download, $payment_data, $cart_items))
	{
            $eStore_debug_manager->squeeze_form("Email with the download link sent to: ".$email, ESTORE_LEVEL_SUCCESS);
	    //$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$prod_id'", OBJECT);
	    
	    $download_email = get_option('eStore_download_email_address');
            $name_pieces = explode(' ', $name,2);
            $firstname = $name_pieces[0];
            if (!empty($name_pieces[1])){$lastname = $name_pieces[1];}   
	
            $eStore_debug_manager->squeeze_form("Performing autoresponder signup if specified in the settings...", ESTORE_LEVEL_SUCCESS);
	    eStore_item_specific_autoresponder_signup($cart_items,$firstname,$lastname,$email);    
	    eStore_global_autoresponder_signup($firstname,$lastname,$email);             
	                
	    $eStore_debug_manager->squeeze_form("Updating the customers database with the visitor details...", ESTORE_LEVEL_SUCCESS);
	    // Update the Customer and products table
            $cart_item_qty = 1;
            $new_available_copies = '';
	    if (is_numeric($retrieved_product->available_copies))
	    {
	        $new_available_copies = ($retrieved_product->available_copies - $cart_item_qty);
	    }
	    $new_sales_count = ($retrieved_product->sales_count + $cart_item_qty);
	    $current_product_id = $retrieved_product->id;
	    $updatedb = "UPDATE $products_table_name SET available_copies = '$new_available_copies', sales_count = '$new_sales_count' WHERE id='$current_product_id'";
	    $results = $wpdb->query($updatedb);    
	     
	    $emailaddress = $email;            
	    $clientdate = (date ("Y-m-d"));
	    $txn_id = $payment_data['txn_id'];//"Free Download";
	    $sale_price = '0';

            $coupon_code_used = "";
            $eMember_username = "";
            $product_name = $retrieved_product->name;
            $address = "";
            $phone = "";
            $subscr_id = "";
            $cart_item_qty = "1";
            $customer_ip = $clientip;
            $status = "FREE_DOWNLOAD";
            $product_key_data = "";
            $notes = "";
				
	    $ret_customer_db = $wpdb->get_row("SELECT email_address FROM $customer_table_name WHERE purchased_product_id = '$prod_id' and email_address='$emailaddress'", OBJECT);
	    if (!$ret_customer_db)
		{
			$updatedb = "INSERT INTO $customer_table_name (first_name, last_name, email_address, purchased_product_id,txn_id,date,sale_amount,coupon_code_used,member_username,product_name,address,phone,subscr_id,purchase_qty,ipaddress,status,serial_number,notes) VALUES ('$firstname', '$lastname','$emailaddress','$prod_id','$txn_id','$clientdate','$sale_price','$coupon_code_used','$eMember_username','$product_name','$address','$phone','$subscr_id','$cart_item_qty','$customer_ip','$status','$product_key_data','$notes')";
			$results = $wpdb->query($updatedb);
		} 
		
		if(!empty($ap_id))
		{		
			$eStore_debug_manager->squeeze_form("Affiliate Referrer ID Value:".$ap_id, ESTORE_LEVEL_SUCCESS,true);
			if(get_option('eStore_aff_enable_lead_capture_for_sqeeze_form')!='')
			{
				if(function_exists('wp_aff_record_remote_lead')){
					if(empty($clientip)){
						$clientip = "";
					}
					wp_aff_record_remote_lead($ap_id,$email,$prod_id,$clientip);
					$eStore_debug_manager->squeeze_form("Affiliate lead captured", ESTORE_LEVEL_SUCCESS,true);
				}
				else{
					$eStore_debug_manager->squeeze_form("Affiliate platform plugin is not installed or it needs to be updated to use this feature!", ESTORE_LEVEL_FAILURE,true);
				}
			}
		}
		do_action('eStore_squeeze_form_processed',$payment_data,$cart_items);
		$eStore_debug_manager->squeeze_form("Squeeze form task complete.", ESTORE_LEVEL_SUCCESS,true);
	}	
}