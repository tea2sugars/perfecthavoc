<?php
//TODO - refactor this functionality to the estore ajax system
if ((isset($_REQUEST['eStore_cart_action'])) && (strlen(trim($_REQUEST['eStore_cart_action'])) > 0)) {
	include_once('../../../wp-load.php');
	include_once('eStore_debug_handler.php');	
	include_once('eStore_classes/wp_eStore_cart_class.php');
	
	eStore_payment_debug('eStore_process_cart_requests invoked... checking request data.',true);
	$action = "";
	$action = strip_tags($_REQUEST['eStore_cart_action']);
	eStore_payment_debug('eStore_process_cart_requests action: '.$action,true);
		
	switch ($action)
	{
		case 'eStore_save_cart':
			wp_eStore_handle_save_cart_action($action);
			break;
		case 'eStore_retrieve_cart':
			wp_eStore_handle_retrieve_cart_action($action);
			break;			
		default:
			//DO Nothing
			break;
	}	
}
else
{
	exit;//DO Nothing
}

function wp_eStore_handle_save_cart_action($action)
{
	//TODO - do an IP addresss check and potentially limit request based on that if needed.
	eStore_payment_debug('Processing save cart action...',true);
	if(!isset($_SESSION['eStore_cart']) || empty($_SESSION['eStore_cart']))
	{
		eStore_payment_debug('Error! Shopping cart is empty... nothing to save!',false);
		echo json_encode(array('reply_action'=>$action, 'status'=> 'error', 'details'=>ESTORE_CART_EMPTY));
		exit;
	}
	$eStore_cart = wp_eStore_load_eStore_cart_class();
	wp_eStore_write_debug_array($eStore_cart,true);
	
	$cart_id = $eStore_cart->GetCartId();
	eStore_payment_debug('Saving cart to the database... Cart ID: '. $cart_id,true);
	wp_eStore_save_cart_details_to_db($eStore_cart);
	eStore_payment_debug('Cart saved successfully!',true);
	
	echo json_encode(array('reply_action'=>$action, 'status'=> 'success', 'ID'=>$cart_id));
	exit;
}

function wp_eStore_handle_retrieve_cart_action($action)
{
	eStore_payment_debug('Processing retrieve cart action...',true);
	$cart_id = strip_tags($_REQUEST['cart_id']);

	if(empty($cart_id)){
		eStore_payment_debug('Error! Cart ID is empty! Cannot process this request',false);
		echo json_encode(array('reply_action'=>$action, 'status'=> 'error', 'code'=>'ESTORE_AJAX_01', 'details'=>'Cart ID is empty!'));
		exit;
	}
	eStore_payment_debug('Retrieving previously saved cart... Cart ID: '.$cart_id,true);
	$eStore_cart = wp_eStore_get_cart_details_from_db($cart_id);
	if($eStore_cart === "-1"){
		eStore_payment_debug('Error! Failed to retrieve cart for the given cart ID!',false);
		echo json_encode(array('reply_action'=>$action, 'status'=> 'error', 'code'=>'ESTORE_AJAX_02', 'details'=>'Failed to retrieve cart for the given cart ID!'));
		exit;
	}
	eStore_payment_debug('Loading cart into session...',true);
	wp_eStore_load_cart_class_to_session($eStore_cart);
	wp_eStore_write_debug_array($_SESSION['eStore_cart'],true);
	echo json_encode(array('reply_action'=>$action, 'status'=> 'success', 'ID'=>$cart_id));
	exit;
}
?>