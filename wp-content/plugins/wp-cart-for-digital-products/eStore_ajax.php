<?php
add_action('wp_ajax_nopriv_estore_add_cart_submit','estore_add_cart_submit');
add_action('wp_ajax_estore_add_cart_submit','estore_add_cart_submit');

function eStore_is_ajax_capable(){
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest'));
}

function eStore_send_ajax_response($response){
	if(eStore_is_ajax_capable()){
		echo json_encode($response);
	}else{
		header("Location: ".$_SERVER["HTTP_REFERER"]);
	}
	exit;
}

function eStore_ajax_send_error_response($output,$action=''){
	$response = array('status'=> 'error', 'reply_action'=>$action, 'output' => $output);
	eStore_send_ajax_response($response);
	exit;	
}
function estore_add_cart_submit()
{
    $nonce = strip_tags($_REQUEST['nonce']);
    eStore_general_debug("Add to cart ajax handler got fired. Handling add to cart operation... Nonce value: ".$nonce,true);
    //Check to see if the submitted nonce matches
    if(!wp_verify_nonce($nonce,'estore_add_cart_nonce')){
    eStore_general_debug("Ajax add to cart handler. Nonce verification failed! This request will not be processed",false);
    die('Security check failed in ajax add to cart handler.');
    }     	
    //wp_eStore_write_debug_array($_REQUEST,true,false,'eStore_debug.log');
    $action = strip_tags($_REQUEST['action']);
    $prod_data = strip_tags($_REQUEST['prod_data']);
    $prod_data_array = array();
    parse_str($prod_data, $prod_data_array);
    //wp_eStore_write_debug_array($prod_data_array,true,false,'eStore_debug.log');

    $output = eStore_handle_item_addition_to_cart($prod_data_array);
    $product_id = $prod_data_array['item_number'];

    //============================================
    $cart_shortcodes = strip_tags($_REQUEST['cart_shortcodes']);
    //wp_eStore_write_debug_array($cart_shortcodes,true,false,'eStore_debug.log');
    //$cart_shortcodes_in_use = array();
    $cart_shortcodes_in_use = json_decode(stripslashes($cart_shortcodes), true);
    //wp_eStore_write_debug_array($cart_shortcodes_in_use,true,false,'eStore_debug.log');
    $cart_sh_output = create_cart_sh_output($cart_shortcodes_in_use);
    //wp_eStore_write_debug_array($cart_sh_output,true,false,'eStore_debug.log');

    $response = array('status'=> 'success', 'reply_action'=>$action, 'output' => $output, 'prod_id' => $product_id, 'cart_shortcodes' => $cart_sh_output);
    eStore_send_ajax_response($response);
    exit;
}

function eStore_handle_item_addition_to_cart($prod_data_array)
{
    $output = "";
    $wp_eStore_config = WP_eStore_Config::getInstance();

    //Need to drop cookie?
    unset($_SESSION['eStore_last_action_msg']);
    unset($_SESSION['eStore_last_action_msg_2']); 
    unset($_SESSION['eStore_last_action_msg_3']);
    
    if(isset($_SESSION['eStore_cart'])){//Load data from standard cart items
    	$estore_cart = wp_eStore_load_eStore_cart_class();
    }else{
    	isset($_SESSION['eStore_cart_class'])? $estore_cart = unserialize($_SESSION['eStore_cart_class']) : $estore_cart = new WP_eStore_Cart();
    }
    
    $product_id = $prod_data_array['item_number'];
    $add_qty = $prod_data_array['add_qty'];
    if($add_qty < 1){$add_qty = 1;$prod_data_array['add_qty'] = 1;}
    eStore_general_debug("Checking if item already exists in cart",true);
    $existing_item = $estore_cart->GetItemIfInCart($prod_data_array); 
    if($existing_item !== "-1"){//Found an item
    	eStore_general_debug("This item already exists in the cart. Updating item ...",true);
    	if($wp_eStore_config->getValue('eStore_do_not_show_qty_in_cart')){
    		$output = '<p class="eStore_error_message">'.ESTORE_ITEM_ALREADY_EXISTS.'</p>';
    		eStore_ajax_send_error_response($output);
    	}
    	$new_qty = $existing_item->quantity + $add_qty;
        if(!is_quantity_availabe($product_id,$new_qty)){//Check if the requested qty is available
    		eStore_general_debug("Requested quantity is not available! Product ID: ".$product_id." Requested qty: ".$new_qty,false);
    		if(isset($_SESSION['eStore_last_action_msg'])){$output = $_SESSION['eStore_last_action_msg'];}
    		if(isset($_SESSION['eStore_last_action_msg_2'])){$output = $_SESSION['eStore_last_action_msg_2'];}
    		eStore_ajax_send_error_response($output);
    	}
    	//Update the quantity of this item
    	$estore_cart->UpdateItemQty($existing_item,$prod_data_array['add_qty']);	
    }else{//New item
    	eStore_general_debug("Adding a brand new item to the cart",true);
    	if(!is_quantity_availabe($product_id,$add_qty)){//Check if the requested qty is available
    		eStore_general_debug("Requested quantity is not available! Product ID: ".$product_id." Requested qty: ".$add_qty,false);
    		if(isset($_SESSION['eStore_last_action_msg'])){$output = $_SESSION['eStore_last_action_msg'];}
    		if(isset($_SESSION['eStore_last_action_msg_2'])){$output = $_SESSION['eStore_last_action_msg_2'];}
    		eStore_ajax_send_error_response($output);
    	}
    	if(isset($prod_data_array['custom_price'])){//Check if it is a custom price amount
    		if($prod_data_array['custom_price'] < $prod_data_array['price']){
    			eStore_general_debug("Custom price value is less than the minimum amount!",false);
    			$output = '<p class="eStore_error_message">'.WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER . WP_ESTORE_CURRENCY_SYMBOL . $prod_data_array['price'].'</p>';
    			eStore_ajax_send_error_response($output);
    		}
    		$prod_data_array['price'] = $prod_data_array['custom_price'];
    	}
    	//Add the item
    	$estore_cart->AddNewItemFromDataArray($prod_data_array);
    }   
    $_SESSION['eStore_cart_class'] = serialize($estore_cart);        
    //$db_data_cart = $estore_cart->print_eStore_cart_details();
    //eStore_general_debug("Cart details: ".$db_data_cart,true);
    
    //Load to the legacy cart session
    wp_eStore_load_cart_class_to_session($estore_cart);
    do_action('eStore_action_item_added_to_cart',$product_id);
    
    $output = eStore_shopping_cart_multiple_gateway();
    return $output;
}

function create_cart_sh_output($cart_shortcodes_in_use)
{
    $cart_sh_output = array();
    if($cart_shortcodes_in_use[0] == '1'){
        $cart_sh_output[0] = eStore_shopping_cart_multiple_gateway();  //call the classic cart function
    }else{
        $cart_sh_output[0] = "";
    }
    
    if($cart_shortcodes_in_use[1] == '1'){
        $cart_sh_output[1] = eStore_shopping_cart_fancy1();  //call the fancy cart1 function
    }else{
        $cart_sh_output[1] = "";
    }
    
    if($cart_shortcodes_in_use[2] == '1'){
        $cart_sh_output[2] = eStore_shopping_cart_fancy2();  //call the fancy cart2 function
    }else{
        $cart_sh_output[2] = "";
    }
    
    if($cart_shortcodes_in_use[3] == '1'){
        $cart_sh_output[3] = show_wp_eStore_cart_with_thumbnail();  //call the classic cart with thumb function
    }else{
        $cart_sh_output[3] = "";
    }
    
    if($cart_shortcodes_in_use[4] == '1'){
        $cart_sh_output[4] = eStore_display_compact_cart();   //call the compact cart function
    }else{
        $cart_sh_output[4] = "";
    }
    
    if($cart_shortcodes_in_use[5] == '1'){
        $cart_sh_output[5] = eStore_display_compact_cart2();    //call the compact cart2 function
    }else{
        $cart_sh_output[5] = "";
    }
    
    if($cart_shortcodes_in_use[6] == '1'){
        $cart_sh_output[6] = eStore_display_compact_cart3();    //call the compact cart3 function
    }else{
        $cart_sh_output[6] = "";
    }
    
    if($cart_shortcodes_in_use[7] == '1'){
        $cart_sh_output[7] = eStore_display_compact_cart4();    //call the compact cart4 function
    }else{
        $cart_sh_output[7] = "";
    }
    
    return $cart_sh_output;
}