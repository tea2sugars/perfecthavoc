<?php

if (!defined('ABSPATH')){include_once ('../../../wp-load.php');}
include_once('eStore_process_payment_data_helper.php');
if(!isset($_SESSION)){
	session_start();
}

$custom_data="";
$wp_eStore_transaction_result_display_content = "";

function handle_payment_data($raw_data,$gateway)
{	    	
	eStore_payment_debug("Handling payment data from: ".$gateway,true);
	if($gateway == "2co")
	{
		$mc_currency = $raw_data['list_currency'];
		if(empty($mc_currency)){
			$mc_currency = $raw_data['currency_code'];
		}
		$uniqueOrderId = $raw_data['item_id_1'];
		if(empty($uniqueOrderId)){//This is a tank you page post (not a background IPN post)
			$uniqueOrderId = $raw_data['cart_order_id'];
		}
		$cart_items = eStore_retrieve_order_details_from_db($uniqueOrderId, $gateway, $mc_currency);

		if(empty($raw_data['message_type'])){//Normal Thank You page post 
			$payment_data = extract_2co_general_payment_data_secondary($raw_data,$gateway,$cart_items);
		}
		else{//proper INS post
	    	$payment_data = extract_2co_general_payment_data($raw_data,$gateway,$cart_items);	    
		}   
	    if ($payment_data['txn_type'] == "ORDER_CREATED")
	    {
	    	eStore_payment_debug("Order Received... verifying payment data.",true);	
		    eStore_do_post_payment_tasks($payment_data,$cart_items);    	
	    }
	
	    //Log the payment and cart data to the debug file    
	    foreach ($payment_data as $key=>$value)
	    {
	        $text .= "$key=$value, ";
	    }
		foreach ($cart_items as $key=>$value)
	    {
	        $text .= "$key=$value, ";
	    }	    
	    eStore_payment_debug($text,true,true);
	}
	else if($gateway == "authorize")
	{
		$cart_items = eStore_retrieve_order_details_from_db($raw_data['x_cust_id'], $gateway);
		$payment_data = extract_authorize_general_payment_data($raw_data,$gateway,$cart_items);		
		
		//print_r($cart_items);
		if(!empty($cart_items))
		{
			eStore_payment_debug("Order Received... verifying payment data.",true);	
			eStore_do_post_payment_tasks($payment_data,$cart_items); 
		}
		else
		{
			eStore_payment_debug("Cart items empty! Could not retrieve items from the database.",false);
		}    	
	    //Log the payment data to the debug file    	    		
	    foreach ($payment_data as $key=>$value)
	    {
	        $text .= "$key=$value, ";
	    }
		foreach ($cart_items as $key=>$value)
	    {
	        $text .= "$key=$value, ";
	    }	    
	    eStore_payment_debug($text,true,true);
	    
	    //The pending payment data can be deleted at this stage	    	
	}
    //file_put_contents('2co_process.txt', $text);
    
	reset_eStore_cart();
    $post_payment_return_url = get_option('cart_return_from_paypal_url');
    $post_payment_return_url = eStore_append_http_get_data_to_url($post_payment_return_url,"reset_eStore_cart","1");
    eStore_redirect_to_url($post_payment_return_url);
}

function eStore_retrieve_order_details_from_db($uniqueOrderId, $gateway, $mc_currency='USD')
{
	eStore_payment_debug("Retrieving order details... Key: ".$uniqueOrderId." Gateway: ".$gateway,true);
	global $wpdb,$custom_data;
	$cart_items = array();
		
	$pending_payment_table_name = WP_ESTORE_PENDING_PAYMENT_TABLE_NAME;
	$wp_eStore_db = $wpdb->get_results("SELECT * FROM $pending_payment_table_name WHERE customer_id = '$uniqueOrderId'", OBJECT);
	if ($wp_eStore_db)
	{
		foreach ($wp_eStore_db as $wp_eStore_db)
		{
			$item_number = $wp_eStore_db->item_number;
			$item_name = $wp_eStore_db->name;
			$quantity = $wp_eStore_db->quantity;
			$mc_gross = $wp_eStore_db->price*$quantity;
			$mc_shipping = $wp_eStore_db->shipping;
			$custom = $wp_eStore_db->custom;
			$cart_total_shipping = $wp_eStore_db->total_shipping;		
			$cart_total_tax = $wp_eStore_db->total_tax;	
			$sub_total = $wp_eStore_db->subtotal;
			$current_item = array(
	          					   'item_number' => $item_number,
								   'item_name' => $item_name,
								   'quantity' => $quantity,
								   'mc_gross' => $mc_gross,
								   'mc_shipping' => $mc_shipping,
								   'mc_currency' => $mc_currency,
								   'custom' => $custom,
								   'total_shipping' => $cart_total_shipping,
								   'total_tax' => $cart_total_tax,
								   'subtotal' => $sub_total,
								  );
	
			array_push($cart_items, $current_item);	
			$ret_item_details = $item_number."|".$item_name."|".$quantity."|".$mc_gross."|".$mc_shipping."|".$custom;	
			eStore_payment_debug("Retrieved Item Details: ".$ret_item_details,true);
		}
		//Clean up
		eStore_payment_debug("Deleting the temporary order details data",true);
		$delete_query = "DELETE FROM $pending_payment_table_name WHERE customer_id='$uniqueOrderId'";
		$result = $wpdb->query($delete_query);
	}	
	$custom_data = $cart_items[0]['custom'];
    return $cart_items;	
}

function extract_authorize_general_payment_data($raw_data,$gateway,$cart_items)
{
//global $custom_data;
$custom_data = $cart_items[0]['custom'];
$customvariables = get_custom_var($custom_data);
$eMember_id = $customvariables['eMember_id'];
$coupon = $customvariables['coupon'];
$total_shipping = $cart_items[0]['total_shipping'];
$total_tax = $cart_items[0]['total_tax'];

$gross_total = $raw_data['x_amount'];
if(empty($raw_data['x_ship_to_address'])){//Get the billing address at least.
	$raw_data['x_ship_to_address'] = $raw_data['x_address'];
	$raw_data['x_ship_to_city'] = $raw_data['x_city'];
	$raw_data['x_ship_to_state'] = $raw_data['x_state'];
	$raw_data['x_ship_to_zip'] = $raw_data['x_zip'];
	$raw_data['x_ship_to_country'] = $raw_data['x_country'];
}
$address = $raw_data['x_ship_to_first_name']." ".$raw_data['x_ship_to_last_name'].", ".
		$raw_data['x_ship_to_address'].", ".$raw_data['x_ship_to_city'].", ".
		$raw_data['x_ship_to_state']." ".$raw_data['x_ship_to_zip'].", ".$raw_data['x_ship_to_country'];

$payment_data = array(
'gateway' => $gateway,
'custom' => $custom_data,
'txn_id' => $raw_data['x_trans_id'],
'txn_type' => $raw_data['x_type'],
'transaction_subject' => $raw_data['x_response_reason_text'],
'first_name' => $raw_data['x_first_name'],
'last_name' => $raw_data['x_last_name'],
'payer_email' => $raw_data['x_email'],
'num_cart_items' => $raw_data['item_count'],
'subscr_id' => $raw_data['x_cust_id'],
'address' => $address,
'phone' => $raw_data['x_phone'],
'coupon_used' => $coupon,
'eMember_username' => $eMember_id,
'eMember_userid' => $eMember_id,
'mc_gross' => $gross_total,
'mc_shipping' => $total_shipping,
'mc_tax' => $total_tax,
'address_street' => $raw_data['x_ship_to_address'],
'address_city' => $raw_data['x_ship_to_city'],
'address_state' => $raw_data['x_ship_to_state'],
'address_country' => $raw_data['x_ship_to_country'],
'payer_business_name' => $raw_data['x_company'],
);
return $payment_data;
}

function extract_2co_general_payment_data($raw_data,$gateway,$cart_items)
{
eStore_payment_debug("2CO IPN Processing - Creating payment data using INS post data.",true);
$custom_data = $cart_items[0]['custom'];
$customvariables = get_custom_var($custom_data);
$eMember_id = $customvariables['eMember_id'];
$coupon = $customvariables['coupon'];
$total_shipping = $cart_items[0]['total_shipping'];
$total_tax = $cart_items[0]['total_tax'];

$gross_total = $raw_data['invoice_list_amount'];
$address = $raw_data['bill_street_address']." ".$raw_data['bill_street_address2'].", ".$raw_data['bill_city'].", ".$raw_data['bill_state']." ".$raw_data['bill_postal_code'].", ".$raw_data['bill_country'];	
//item_type_# = bill or refund
$payment_data = array(
'gateway' => $gateway,
'custom' => $custom_data,
'txn_id' => $raw_data['invoice_id'],
'txn_type' => $raw_data['message_type'],
'transaction_subject' => $raw_data['message_description'],
'first_name' => $raw_data['customer_first_name'],
'last_name' => $raw_data['customer_last_name'],
'payer_email' => $raw_data['customer_email'],
'num_cart_items' => $raw_data['item_count'],
'subscr_id' => $raw_data['invoice_id'],
'address' => $address,
'phone' => $raw_data['customer_phone'],
'coupon_used' => $coupon,
'eMember_username' => $eMember_id,
'eMember_userid' => $eMember_id,
'mc_gross' => $gross_total,
'mc_shipping' => $total_shipping,
'mc_tax' => $total_tax,
'address_street' => $raw_data['bill_street_address'],
'address_city' => $raw_data['bill_city'],
'address_state' => $raw_data['bill_state'],
'address_country' => $raw_data['bill_country'],
                     );
return $payment_data;
}

function extract_2co_general_payment_data_secondary($raw_data,$gateway,$cart_items){
eStore_payment_debug("2CO IPN Processing - Creating payment data using thank you page post data.",true);
$custom_data = $cart_items[0]['custom'];
$customvariables = get_custom_var($custom_data);
$eMember_id = $customvariables['eMember_id'];
$coupon = $customvariables['coupon'];
$total_shipping = $cart_items[0]['total_shipping'];
$total_tax = $cart_items[0]['total_tax'];
$sub_total = $cart_items[0]['subtotal'];
$gross_total = $sub_total + $total_shipping + $total_tax;

$address = $raw_data['street_address']." ".$raw_data['street_address2'].", ".$raw_data['city'].", ".$raw_data['state']." ".$raw_data['zip'].", ".$raw_data['country'];	
//item_type_# = bill or refund
$payment_data = array(
'gateway' => $gateway,
'custom' => $custom_data,
'txn_id' => $raw_data['invoice_id'],
'txn_type' => "ORDER_CREATED",
'transaction_subject' => $raw_data['message_description'],
'first_name' => $raw_data['first_name'],
'last_name' => $raw_data['last_name'],
'payer_email' => $raw_data['email'],
'num_cart_items' => count($cart_items),
'subscr_id' => $raw_data['invoice_id'],
'address' => $address,
'phone' => $raw_data['phone'],
'coupon_used' => $coupon,
'eMember_username' => $eMember_id,
'eMember_userid' => $eMember_id,
'mc_gross' => $gross_total,
'mc_shipping' => $total_shipping,
'mc_tax' => $total_tax,
'address_street' => $raw_data['street_address'],
'address_city' => $raw_data['city'],
'address_state' => $raw_data['state'],
'address_country' => $raw_data['country'],
                     );
return $payment_data;
}
