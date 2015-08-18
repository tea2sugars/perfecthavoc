<?php
/*
|-------------------------------------------|
|Property       |   logic  | value          |
|-------------------------------------------|
|Item Quantity  |     >    | 3              |
|Total Quantity |     =    | 15.00          |
|Subtotal Amount| Contains | "Test Product" |
|Total Amount   |          |                |
|Item Name      |          |                |
|Item ID        |
|Free shipping if total|
|Product Category ID|
|-------------------------------------------|
*/
$total_cond_discount = 0;
// if(!empty($value)) -> eStore_apply_cond_discount()
function eStore_apply_cond_discount($ret_coupon)
{
	global $total_cond_discount;
	$total_cond_discount = 0;
    switch($ret_coupon->property)
    {
        case '1':
            eStore_handle_item_quantity_discount($ret_coupon);
            break;
        case '2':
            eStore_handle_total_quantity_discount($ret_coupon);
            break;
        case '3':
            eStore_handle_total_discount($ret_coupon,"subtotal");
            break;
        case '4':
            eStore_handle_total_discount($ret_coupon,"total");
            break;   
        case '5':
            eStore_handle_item_name_discount($ret_coupon);
            break;   
        case '6':
            eStore_handle_item_id_discount($ret_coupon);
            break; 
        case '7':
            eStore_handle_free_shipping_discount($ret_coupon);
            break;  
        case '8':
            eStore_handle_product_category_id_discount($ret_coupon);
            break;                                                                           
        default:
            break;
    }
    return $total_cond_discount;
}

function eStore_handle_product_category_id_discount($ret_coupon)
{
	global $wpdb;
	$cat_prod_rel_table_name = $wpdb->prefix . "wp_eStore_cat_prod_rel_tbl";	
	$products = $_SESSION['eStore_cart'];
	$found_at_least_one_product_in_given_cat = false;
	$eligible_items = array();
        $eligible_items_qty = 0;
	foreach ($products as $key => $item)
	{
		$apply_coupon = false;
		$item_id = $item['item_number'];
		$wp_eStore_cat_db = $wpdb->get_results("SELECT * FROM $cat_prod_rel_table_name where prod_id=$item_id", OBJECT);
		if ($wp_eStore_cat_db)
		{
			foreach ($wp_eStore_cat_db as $wp_eStore_cat_db)
			{
				if($wp_eStore_cat_db->cat_id == $ret_coupon->value)
				{
					$apply_coupon = true;
					$found_at_least_one_product_in_given_cat = true;
					$eligible_items[] = $item_id;
                                        $eligible_items_qty += $item['quantity'];
				}				
			}
			if($apply_coupon && $ret_coupon->discount_type != 1)//Apply %
			{
				$item['price'] = eStore_calc_new_per_item_price($ret_coupon, $item['price'],$item['quantity']);		
				unset($products[$key]);
				array_push($products, $item);				
			}
		}
	}
	if($ret_coupon->discount_type == 1 && $found_at_least_one_product_in_given_cat)
	{
            global $total_cond_discount;
            $total_eligible_items = $eligible_items_qty; //count($eligible_items);
            $total_discount_amount = $ret_coupon->discount_value;
            $total_cond_discount = $total_discount_amount;
            $per_item_discount_amount = $total_discount_amount/$total_eligible_items;
            foreach ($products as $key => $item){
                if(in_array($item['item_number'],$eligible_items)){//Share the per item discount amount
                    //$total_item_price = $item['price'] * $item['quantity'];				
                    //$after_discount_total_item_price = max($total_item_price - $per_item_discount_amount, 0);							
                    //$item['price'] = round($after_discount_total_item_price/$item['quantity'],2);
                    
                    //Spread the discount amt to each item (including qty rather than the full total).
                    $after_discount_item_price = max($item['price'] - $per_item_discount_amount, 0);
                    $item['price'] = round($after_discount_item_price,2);
                    unset($products[$key]);
                    array_push($products, $item);					
                }
            }
	}	
	sort($products);
	$_SESSION['eStore_cart'] = $products;						
}
function eStore_handle_free_shipping_discount($ret_coupon,$flag="total")
{
	global $total_cond_discount;
	$products = $_SESSION['eStore_cart'];
	$total = $_SESSION['eStore_cart_sub_total'];
	$shipping = $_SESSION['eStore_cart_postage_cost'];
	if ($flag == "total")
	{
		$total = $total + $shipping;
	}

	if ($ret_coupon->logic == "1")
	{
		if ($total > $ret_coupon->value)
		{
			foreach ($products as $key => $item)
			{
				$item['shipping'] = 0;
				unset($products[$key]);
				array_push($products, $item);				
			}
		}
	}
	else if ($ret_coupon->logic == "2")
	{
		if ($total == $ret_coupon->value)
		{
			foreach ($products as $key => $item)
			{
				$item['shipping'] = 0;
				unset($products[$key]);
				array_push($products, $item);				
			}
		}
	}	
	$total_cond_discount = -99;//ESTORE_DISCOUNT_FREE_SHIPPING;
	sort($products);
	$_SESSION['eStore_cart'] = $products;
}


function eStore_handle_item_id_discount($ret_coupon)
{
	global $total_cond_discount;
	$products = $_SESSION['eStore_cart'];
	foreach ($products as $key => $item)
	{
		if ($item['item_number'] == $ret_coupon->value)
		{
			//$item['price'] = eStore_calc_new_per_item_price($ret_coupon, $item['price'],$item['quantity']);
			$new_per_item_price = 0;
			if ($ret_coupon->discount_type == 1)// value discount
			{				
				$total_discount_amount = $ret_coupon->discount_value;
				$new_per_item_price = $item['price'] - $total_discount_amount;
				$total_cond_discount = $total_cond_discount + $total_discount_amount*$item['quantity'];
			}
			else //% value
			{
				$new_per_item_price = (eStore_calc_new_item_total($ret_coupon, $item['price'],$item['quantity'])/$item['quantity']);
			}			
			$item['price'] = round($new_per_item_price,2);
			if($item['price']<0)
			{
				$item['price'] = 0;
			}			
			unset($products[$key]);
			array_push($products, $item);
		}
	}
	sort($products);
	$_SESSION['eStore_cart'] = $products;	
}

function eStore_handle_item_name_discount($ret_coupon)
{
	$products = $_SESSION['eStore_cart'];
	foreach ($products as $key => $item)
	{
		$pos = strpos($item['name'],$ret_coupon->value);
		if ($pos !== false)
		{
			$item['price'] = eStore_calc_new_per_item_price($ret_coupon, $item['price'],$item['quantity']);
			unset($products[$key]);
			array_push($products, $item);
		}
	}
	sort($products);
	$_SESSION['eStore_cart'] = $products;
}

function eStore_handle_total_discount($ret_coupon,$flag="total")
{
	$products = $_SESSION['eStore_cart'];
	$total = eStore_get_cart_total();//$_SESSION['eStore_cart_sub_total'];
	$shipping = eStore_get_cart_shipping();//$_SESSION['eStore_cart_postage_cost'];

	if ($flag == "total")
	{
			$total = $total + $shipping;
	}

	if ($ret_coupon->logic == "1")
	{
		if ($total > $ret_coupon->value)
		{
			$products = eStore_apply_discount_on_products($ret_coupon,$products);
		}
	}
	else if ($ret_coupon->logic == "2")
	{
		if ($total == $ret_coupon->value)
		{
			$products = eStore_apply_discount_on_products($ret_coupon,$products);
		}
	}
	sort($products);
	$_SESSION['eStore_cart'] = $products;
}

function eStore_handle_total_quantity_discount($ret_coupon)
{
	$products = $_SESSION['eStore_cart'];
	$total_quantity = 0;
	foreach ($products as $key => $item)
	{
		$total_quantity = $total_quantity + $item['quantity'];
	}
	if ($ret_coupon->logic == "1")
	{
		if ($total_quantity > $ret_coupon->value)
		{
			$products = eStore_apply_discount_on_products($ret_coupon,$products);
		}
	}
	else if ($ret_coupon->logic == "2")
	{
		if ($total_quantity == $ret_coupon->value)
		{
			$products = eStore_apply_discount_on_products($ret_coupon,$products);
		}
	}
	sort($products);
	$_SESSION['eStore_cart'] = $products;
}

function eStore_handle_item_quantity_discount($ret_coupon)
{
	$products = $_SESSION['eStore_cart'];
    if ($ret_coupon->logic == "1") //handle greater than
    {
		foreach ($products as $key => $item)
		{
			if ($item['quantity'] > $ret_coupon->value)
			{
				$item['price'] = eStore_calc_new_per_item_price($ret_coupon, $item['price'],$item['quantity']);
				unset($products[$key]);
				array_push($products, $item);
			}
		}
    }
    else if ($ret_coupon->logic == "2") //handle equal to
    {
		foreach ($products as $key => $item)
		{
			if ($item['quantity'] == $ret_coupon->value)
			{
				$item['price'] = eStore_calc_new_per_item_price($ret_coupon, $item['price'],$item['quantity']);
				unset($products[$key]);
				array_push($products, $item);
			}
		}
    }
	sort($products);
	$_SESSION['eStore_cart'] = $products;
}

function eStore_apply_discount_on_products($ret_coupon,$products)
{
	foreach ($products as $key => $item)
	{
		$item['price'] = eStore_calc_new_per_item_price($ret_coupon, $item['price'],$item['quantity']);
		if($item['price']<0)
		{
			$item['price'] = 0;
		}
		unset($products[$key]);
		array_push($products, $item);
	}

	return $products;
}
function eStore_calc_new_per_item_price($ret_coupon, $amount,$quantity)
{
	if ($ret_coupon->discount_type == 1)// value discount
	{
		global $total_cond_discount;
		$total_discount_amount = $ret_coupon->discount_value;
		$total_items_in_cart = eStore_get_total_cart_item_qty();
		$per_item_discount_amount = $total_discount_amount/$total_items_in_cart;
		$new_per_item_price = max($amount - $per_item_discount_amount, 0);
		$total_cond_discount = $total_cond_discount + $per_item_discount_amount*$quantity;
	}
	else //%discount
	{
		$new_per_item_price = (eStore_calc_new_item_total($ret_coupon, $amount,$quantity)/$quantity);
	}
	return round($new_per_item_price,2);
}
function eStore_calc_new_item_total($ret_coupon, $amount,$quantity)
{
	$new_total = eStore_calc_value_after_discount($ret_coupon, $amount*$quantity,$quantity);
	return $new_total;
}
function eStore_calc_value_after_discount($ret_coupon, $amount,$quantity)
{
	$discount_val = eStore_calc_discount_amount($ret_coupon, $amount,$quantity);
	return (max($amount - $discount_val, 0));
}
function eStore_calc_discount_amount($ret_coupon, $amount,$quantity)
{
	global $total_cond_discount;
	if ($ret_coupon->discount_type == 0) // %discount
	{
		$discount_amount = (($amount * $ret_coupon->discount_value)/100);
	}
	else if ($ret_coupon->discount_type == 1)// value discount
	{
		$discount_amount = $ret_coupon->discount_value*$quantity;
	}
	$total_cond_discount = $total_cond_discount + $discount_amount;
	return $discount_amount;
}

/* Coupon application related helpful functions */
function eStore_backup_estore_cart_before_coupon_application()
{
    if(isset($_SESSION['eStore_cart']))
    {
        $_SESSION['eStore_cart_backup'] = $_SESSION['eStore_cart'];
    }	
}
function eStore_load_price_from_backed_up_cart()
{
	if(isset($_SESSION['eStore_cart_backup']) && !empty($_SESSION['eStore_cart_backup']) && !empty($_SESSION['eStore_cart']))
	{
		$products = $_SESSION['eStore_cart'];
	    foreach ($products as $key => $item)
	    {
	    	//check if the product exists in the backup cart then update the price    	
	    	$item_updated = false;
	    	foreach ($_SESSION['eStore_cart_backup'] as $backup_item)
	    	{
	    		if($item['name'] == $backup_item['name']){
	    			$item['price'] = $backup_item['price'];//load price from backup
	    			$item_updated = true;
	    			continue;
	    		}
	    	}
	    	if($item_updated){
	            unset($products[$key]);
	            array_push($products, $item);
	    	}	    	
	    }	
    	sort($products);
    	$_SESSION['eStore_cart'] = $products;	    
	}
}
function eStore_get_original_item_price_from_backed_up_cart($item_name)
{
	if(isset($_SESSION['eStore_cart_backup']) && !empty($_SESSION['eStore_cart_backup'])){
	    foreach ($_SESSION['eStore_cart_backup'] as $backup_item)
	    {
	    	if($item_name == $backup_item['name']){
	    		return $backup_item['price'];
	    	}
	    }
	}
    return 0;
}
