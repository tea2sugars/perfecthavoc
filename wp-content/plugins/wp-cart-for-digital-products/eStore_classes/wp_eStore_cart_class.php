<?php
include_once('wp_eStore_item_class.php');
class WP_eStore_Cart
{
	var $cart_id;
	var $item_arr;
	var $currency;
	var $cart_tax;
	var $cart_tax_rate;
	var $cart_shipping;
	var $custom_data;
	var $custom_data2;
	var $post_payment_status = "not_processed";   // check to see if the payment has been already processed once
	var $continue_shopping_url = "";
	var $cart_return_url = "";
	var $cart_cancel_url = "";	
	var $checkout_page_url = "";
	var $discount_applied_once;
	var $auto_discount_applied;

	function WP_eStore_Cart($currency="USD")
	{
		$this->cart_id = uniqid();
		$this->total_cart_item = 0;
		$this->currency = $currency;
		$this->item_arr = array();
		//set other default vars by reading the details from config class
	}
	function SetCartId($cart_id)
	{
		$this->cart_id = $cart_id;
	}
	/* Add an item to the cart */
    function AddItem($item) 
    {
      	$this->item_arr[] = $item;
    }	
    /* Add multimple items to the cart */
    function AddItems($items)
    {
    	$this->item_arr = $items;
    }
    function SetCartShipping($shipping)
    {
    	$this->cart_shipping = $shipping;
    }
    function SetCartCurrency($cart_currency)
    {
    	$this->currency = $cart_currency;
    }
    function SetCartTaxRate($tax_rate)
    {
    	$this->cart_tax_rate = $tax_rate;
    }
    function SetCustomData($custom_data)
    {
    	$this->custom_data = $custom_data;
    }
	function SetCustomData2($custom_data2)
    {
    	$this->custom_data2 = $custom_data2;
    }
    function setPostPaymentStatus($status)
    {
    	$this->post_payment_status = $status;			
    }
    function SetCartReturnUrl($cart_return_url)
    {
    	$this->cart_return_url = $cart_return_url;
    }
    function SetCheckoutPageUrl($checkout_page_url)
    {
    	$this->checkout_page_url = $checkout_page_url;
    }
    function SetDiscountAppliedFlag($flag)
    {
    	$this->discount_applied_once = $flag;
    }
    function SetAutoDiscountAppliedFlag($flag)
    {
    	$this->auto_discount_applied = $flag;
    }

    function GetAutoDiscountAppliedFlag()
    {
    	return $this->auto_discount_applied;
    }
    function GetDiscountAppliedFlag()
    {
    	return $this->discount_applied_once;
    }    
    function GetCartId()
    {
    	return $this->cart_id;
    }
    function GetCustomData()
    {
    	return $this->custom_data;
    }
	function GetCustomData2()
    {
    	return $this->custom_data2;
    }
	function GetPostPaymentStatus()
    {
    	return $this->post_payment_status;			
    }
    function GetCartCurrency()
    {
    	return $this->currency;
    }
    function GetItem($i)
    {
    	return $this->item_arr[$i];
    }
    function GetItemsArray()
    {
    	return $this->item_arr;
    }
    function GetNumberOfCartItems()
    {
    	return count($this->item_arr);
    }
    function GetCartShipping()
    {
    	return $this->cart_shipping;
    }
    function GetCartReturnUrl()
    {
    	return $this->cart_return_url;
    }    
    function CalculateCartSubTotal()
    {
		$sub_total = 0;
		if(count($this->item_arr) < 1)
		{
			return $sub_total;
		}
		for ($i=0; $i < count($this->item_arr); $i++)
		{
			$sub_total += $this->item_arr[$i]->get_item_sub_total();
		}
		return $sub_total;    	
    }
    function CalculateCartTotalTax()
    {
		$tax = 0;
		if(count($this->item_arr) < 1)
		{
			return $tax;
		}
		for ($i=0; $i < count($this->item_arr); $i++)
		{
			if($this->item_arr[$i]->GetItemTax() == '')
			{
				//individual tax is not set (calculate it based on the rate)	
				$tax_rate = $this->cart_tax_rate;			
				$tax += ($this->item_arr[$i]->get_item_sub_total() * $tax_rate)/100;
			}
			else
			{
				$tax += $this->item_arr[$i]->GetItemTax();
			}			
		}
		return $tax;    	
    }    
    function CalculateCartTotalShipping()
    {
    	//For eStore simply use "$this->cart_shipping" value
		$shipping = 0;
		if(count($this->item_arr) < 1)
		{
			return $shipping;
		}
		for ($i=0; $i < count($this->item_arr); $i++)
		{			
			$shipping += $this->item_arr[$i]->GetItemShipping() * $this->item_arr[$i]->GetItemQty();	
		}
		return $shipping;    	
    }     
    function CalculateCartTotal()
    {
    	$sub_total = $this->CalculateCartSubTotal();
    	$tax = $this->CalculateCartTotalTax();
    	$total = $sub_total + $tax + $this->cart_shipping;
    	return $total;
    }
    
    function IsItemInCart($prod_data_array)
    {
    	$prod_name = $prod_data_array['estore_product_name'];
    	foreach ($this->item_arr as $item){
    		if($item->item_name == stripslashes($prod_name)){
    			return true;
    		}
    	} 
    	return false;   	
    }

    function GetItemIfInCart($prod_data_array)
    {
    	$prod_name = $prod_data_array['estore_product_name'];
    	foreach ($this->item_arr as $item){
    		if($item->item_name == stripslashes($prod_name)){
    			return $item;
    		}
    	} 
    	return "-1";   	
    }
    
    function UpdateItemQty($item,$qty)
    {
		$new_qty = $item->quantity + $qty;
		$item->SetItemQty($new_qty);
    }
        
    function UpdateItemQtyFromDataArray($prod_data_array)
    {
    	$prod_name = $prod_data_array['estore_product_name'];
    	foreach ($this->item_arr as $item){
    		if($item->item_name == stripslashes($prod_name)){
    			$new_qty = $item->quantity + $prod_data_array['add_qty'];
    			$item->SetItemQty($new_qty);
    		}
    	}	
    }
        
    function AddNewItemFromDataArray($prod_data_array)
    {    	
		$eStore_item = new WP_eStore_Item(
		$prod_data_array['item_number'],
		stripslashes($prod_data_array['estore_product_name']),
		$prod_data_array['price'],
		$prod_data_array['add_qty'],
		$prod_data_array['tax'],
		$prod_data_array['shipping'],
		$prod_data_array['cartLink'],
		$prod_data_array['thumbnail_url']			
		);		
		$eStore_item->setThumbUrl($prod_data_array['thumbnail_url']);
		if(isset($prod_data_array['digital_flag']) && $prod_data_array['digital_flag']=="1"){
			$eStore_item->set_digital_item_flag(true);
		}
		$this->AddItem($eStore_item);
    }
    
    function print_eStore_cart_details()
    {
		$output = "";
		$output .= "<br />Cart ID: ".$this->cart_id;
		$output .= "<br />Currency: ".$this->currency;
		$output .= "<br />Cart Shipping: ".$this->cart_shipping;
		$output .= "<br />Cart Tax Rate: ".$this->cart_tax_rate."%";
		$output .= "<br />Cart Total Tax: ".$this->CalculateCartTotalTax();
		$output .= "<br />Cart Sub Total: ".$this->CalculateCartSubTotal();
		$output .= "<br />Cart Total: ".$this->CalculateCartTotal();			
		$output .= "<br />Cart Return URL: ".$this->cart_return_url;
		$output .= "<br />Cart Custom Data: ".$this->custom_data;
		foreach ($this->item_arr as $item)
		{
			$output .= "<br />---------------------";
			$output .= $item->print_item_details();
		}
		return $output;
    }
}

function wp_eStore_save_cart_details_to_db($eStore_cart)
{
	global $wpdb;
	$save_cart_table_name = WP_ESTORE_SAVE_CART_TABLE_NAME;
	
	$cart_id = $eStore_cart->GetCartId();
	$serialized_cart = esc_sql(serialize($eStore_cart));
	
	$wp_eStore_resultset = $wpdb->get_row("SELECT * FROM $save_cart_table_name WHERE cart_id = '$cart_id'", OBJECT);
	if($wp_eStore_resultset)
	{
		//Update the record
		$updatedb = "UPDATE $save_cart_table_name SET serialized_eStore_cart = '$serialized_cart' WHERE cart_id = '$cart_id'";
		$results = $wpdb->query($updatedb);			
	}
	else
	{
		//Insert new record
		$updatedb = "INSERT INTO $save_cart_table_name (cart_id, serialized_eStore_cart) VALUES ('$cart_id','$serialized_cart')";
		$results = $wpdb->query($updatedb);			
	}
//	$wp_eStore_resultset = $wpdb->get_row("SELECT * FROM $save_cart_table_name WHERE cart_id = '$cart_id'", OBJECT);
//	echo "<br />======Retrieved:=====<br />";
//	print_r($wp_eStore_resultset);	
}

function wp_eStore_get_cart_details_from_db($cart_id)
{
	global $wpdb;	
	$save_cart_table_name = WP_ESTORE_SAVE_CART_TABLE_NAME;
	$wp_eStore_resultset = $wpdb->get_row("SELECT * FROM $save_cart_table_name WHERE cart_id = '$cart_id'", OBJECT);
	if($wp_eStore_resultset)
	{
		$eStore_cart = unserialize($wp_eStore_resultset->serialized_eStore_cart);
		return $eStore_cart;
	}
	return "-1";//could not retrieve data			
}

function wp_eStore_load_eStore_cart_class()
{
	$eStore_cart = new WP_eStore_Cart();
	$eStore_items = wp_eStore_load_items_to_eStore_item_class();
	$eStore_cart->AddItems($eStore_items);
	$shipping = round($_SESSION['eStore_cart_postage_cost'], 2);
	$eStore_cart->SetCartShipping($shipping);
	if(get_option('eStore_enable_tax') != ''){
		$eStore_cart->SetCartTaxRate(get_option('eStore_global_tax_rate'));
	}
	$return_url = get_option('cart_return_from_paypal_url');
	$eStore_currency = get_option('cart_payment_currency');
	$eStore_cart->SetCartCurrency($eStore_currency);
	$eStore_cart->SetCartReturnUrl($return_url);
	$custom_field_val = eStore_get_custom_field_value();
	$eStore_cart->SetCustomData($custom_field_val); //($_SESSION['eStore_custom_values']);
	if (isset($_SESSION['discount_applied_once'])){
		$eStore_cart->SetDiscountAppliedFlag($_SESSION['discount_applied_once']);
	}
	if (isset($_SESSION['auto_discount_applied_once'])){
		$eStore_cart->SetAutoDiscountAppliedFlag($_SESSION['auto_discount_applied_once']);
	}
	return $eStore_cart;	
}

function wp_eStore_load_items_to_eStore_item_class()
{	
	//global $wpdb;
	//$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$eStore_items = array();
	foreach ($_SESSION['eStore_cart'] as $item)
	{		
		$eStore_item = new WP_eStore_Item($item['item_number'],$item['name'],$item['price'],$item['quantity'],$item['tax'],$item['shipping'],$item['cartLink']);
		if(isset($item['digital_flag']) && $item['digital_flag']=="1")
		{
			$eStore_item->set_digital_item_flag(true);
		}
		// TODO - Need to check if item weigth is configured for this product (Use this if needed)		
		//$id = $item['item_number'];
        //$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
        //if(!empty($ret_product->weight))
        //{
        //	$eStore_item->SetItemWeight($ret_product->weight);
        //}
        if(!empty($item['thumbnail_url'])){
        	$eStore_item->setThumbUrl($item['thumbnail_url']);
        }
		$eStore_items[] = $eStore_item;
	}
	return $eStore_items;
}

function wp_eStore_load_cart_class_to_session($eStore_cart)
{
	$cart_array = wp_eStore_load_items_to_cart_array($eStore_cart->GetItemsArray());
    $_SESSION['eStore_cart'] = $cart_array;
    $_SESSION['eStore_url'] = WP_ESTORE_URL;    
    $_SESSION['eStore_cart_postage_cost'] = $eStore_cart->GetCartShipping();
    $discount_applied_flag = $eStore_cart->GetDiscountAppliedFlag();
    if(!empty($discount_applied_flag)){
    	$_SESSION['discount_applied_once'] = $discount_applied_flag;
    }
    $auto_discount_applied_flag = $eStore_cart->GetAutoDiscountAppliedFlag();
    if(!empty($auto_discount_applied_flag)){
    	$_SESSION['auto_discount_applied_once'] = $auto_discount_applied_flag;
    }    
	//$eStore_cart->GetCustomData($custom_field_val); //Need to use this custom data (maybe individually set the AP ID and other session data from the array
}
function wp_eStore_load_items_to_cart_array($items_object_array)
{	
    $products = array();
    foreach ($items_object_array as $eStore_item)
    {
        $digital_flag = '';
        if($eStore_item->is_digital()){
            $digital_flag = '1';
        }
        $product = array(
        'name' => $eStore_item->item_name, 
        'price' => $eStore_item->item_price, 
        'quantity' => $eStore_item->quantity, 
        'shipping' => $eStore_item->item_shipping, 
        'item_number' => $eStore_item->item_id, 
        'cartLink' => $eStore_item->item_url, 
        'thumbnail_url' => '',
        'tax' => $eStore_item->item_tax,
        'digital_flag' => $digital_flag,
        'thumbnail_url' => $eStore_item->thumb_url
        );
        array_push($products, $product);		
    }
    sort($products);
    return $products;
}
function wp_eStore_convert_cart_class_to_items_array($eStore_cart)
{
    $cart_items = array();
    $eStore_cart_items = $eStore_cart->GetItemsArray();
    $num_eStore_cart_items =  $eStore_cart->GetNumberOfCartItems();
    $eStore_cart_currency = $eStore_cart->GetCartCurrency();

    for ($i=0; $i<$num_eStore_cart_items; $i++)
    {
        $item_number = $eStore_cart_items[$i]->GetItemID();
        $item_name = $eStore_cart_items[$i]->GetItemName();
        $quantity = $eStore_cart_items[$i]->GetItemQty();
        $mc_gross = $eStore_cart_items[$i]->GetItemPrice() * $quantity;
        $mc_gross = number_format($mc_gross,2,'.','');
        $mc_shipping = $eStore_cart_items[$i]->GetItemShipping();
        $mc_currency = $eStore_cart_items[$i]->GetItemCurrency();
        if(empty($mc_currency))
        {
                $mc_currency = $eStore_cart_currency;
        }				
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
    return $cart_items;
}
