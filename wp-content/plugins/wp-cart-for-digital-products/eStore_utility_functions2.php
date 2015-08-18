<?php
function eStore_txn_prcoessed($txn_id)
{	
	$cond = " txn_id = '$txn_id'";
	$resultset = WP_eStore_Db_Access::findAll(WP_ESTORE_CUSTOMER_TABLE_NAME, $cond); 
	if($resultset){
		return true;
	}	
	return false;
}

function eStore_create_payment_data_from_txn_id($txn_id)
{
	$cond = " txn_id = '$txn_id'";
	$resultset = WP_eStore_Db_Access::find(WP_ESTORE_CUSTOMER_TABLE_NAME, $cond);
	$address = str_replace("\n", " ",$resultset->address);
	$payment_data = array(
	'gateway' => 'none',
	'custom' => '',
	'txn_id' => $txn_id,
	'txn_type' => 'Shopping Cart',
	'transaction_subject' => 'Shopping cart checkout',
	'first_name' => $resultset->first_name,
	'last_name' => $resultset->last_name,
	'payer_email' => $resultset->email_address,
	'num_cart_items' => $resultset->purchase_qty,
	'subscr_id' => $resultset->subscr_id,
	'address' => $address,
	'phone' => $resultset->phone,
	'coupon_used' => $resultset->coupon_code_used,
	'eMember_username' => $resultset->member_username,
	'eMember_userid' => $resultset->member_username,
	'mc_gross' => $resultset->sale_amount,
	'mc_shipping' => '',
	'mc_tax' => '',
    );	
    //wp_eStore_write_debug_array($payment_data,true);
    return $payment_data;
}

function eStore_create_cart_items_data_from_txn_id($txn_id)
{
	$cart_items = array();
	$cond = " txn_id = '$txn_id'";
	$resultset = WP_eStore_Db_Access::findAll(WP_ESTORE_CUSTOMER_TABLE_NAME, $cond);
	if($resultset){
		foreach ($resultset as $item){		
			$current_item = array(
		    'item_number' => $item->purchased_product_id,
			'item_name' => $item->product_name,
			'quantity' => $item->purchase_qty,
			'mc_gross' => $item->sale_amount,
			'mc_currency' => '',
			);
			array_push($cart_items, $current_item);
		}
		//wp_eStore_write_debug_array($cart_items,true);      
	}
	return $cart_items;
}

function eStore_create_item_data($id,$item_name='',$mc_gross='',$quantity='1')
{
	$cart_items = array();
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);	
	if(empty($item_name)){$item_name = $ret_product->name;}
	
	if($mc_gross == '0.00' || $mc_gross == '0'){$mc_gross = $mc_gross;}
	else if(empty($mc_gross)){$mc_gross = $ret_product->price;}
	
	$mc_shipping = $ret_product->shipping_cost;
	$mc_currency = get_option('cart_payment_currency');
	$current_item = array('item_number' => $id,'item_name' => $item_name,'quantity' => $quantity,'mc_gross' => $mc_gross,'mc_currency' => $mc_currency,);
	array_push($cart_items, $current_item);
    return $cart_items;    
}

function eSore_create_payment_data_from_customer_resultset($customer_data_rs)//TODO - refactor using the txn_id one
{	
	$address = str_replace("\n", " ",$customer_data_rs->address);
	$payment_data = array(
	'gateway' => '',
	'custom' => '',
	'txn_id' => $customer_data_rs->txn_id,
	'txn_type' => 'Shopping Cart',
	'transaction_subject' => 'Shopping cart customer data',
	'first_name' => $customer_data_rs->first_name,
	'last_name' => $customer_data_rs->last_name,
	'payer_email' => $customer_data_rs->email_address,
	'num_cart_items' => $customer_data_rs->purchase_qty,
	'subscr_id' => $customer_data_rs->subscr_id,
	'address' => $address,
	'phone' => $customer_data_rs->phone,
	'coupon_used' => $customer_data_rs->coupon_code_used,
	'eMember_username' => $customer_data_rs->member_username,
	'eMember_userid' => $customer_data_rs->member_username,
	'mc_gross' => $customer_data_rs->sale_amount,
	'mc_shipping' => '',
	'mc_tax' => '',
	'address_street' => '',
	'address_city' => '',
	'address_state' => '',
	'address_country' => '',
	);
	return $payment_data;
}
