<?php
function eStore_empty_cart_display()
{
	    $empty_cart_text = get_option('wp_cart_empty_text');
	    $products_page = get_option('eStore_products_page_url');
	    
	    $output = "";
	    $output .= '<div class="eStore_empty_cart_block">';
    	if (preg_match("/http/", $empty_cart_text)) // Use the image as the 'Empty Cart Display'
    	{
    		if (!empty($products_page))
    		{
    	    	$output .= '<a rel="nofollow" href="'.$products_page.'"><img src="'.$empty_cart_text.'" class="eStore_empty_cart" alt="Shopping Cart Empty" title="Shopping Cart Empty" /></a>';
    	    	$output .= '<br />'.ESTORE_CART_EMPTY;    			
    		}
    		else
    		{
    	    	$output .= '<img src="'.$empty_cart_text.'" class="eStore_empty_cart" alt="Shopping Cart Empty" title="Shopping Cart Empty" />';
    	    	$output .= '<br />'.ESTORE_CART_EMPTY;
    		}
    	}
    	else
    	{
    		$output .= $empty_cart_text;
    	}
		
		if (!empty($products_page))
		{
			$output .= '<br /><a rel="nofollow" href="'.$products_page.'">'.ESTORE_VISIT_THE_SHOP.'</a>';
		}
		$output .= '</div>';
		return $output;
}

function eStore_get_tax_include_price_by_prod_id($id, $price)
{
    global $wpdb;
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
    $tax_rate = "";
    if(!empty($ret_product->tax)){
        $tax_rate = $ret_product->tax;
    }
    else{
        $tax_rate = get_option('eStore_global_tax_rate');
    }
    $tax_inc_price = $price + (($price * $tax_rate)/100);
    return number_format($tax_inc_price, 2, '.', '');
}

function eStore_calculate_tax_included_price_without_qty($price, $tax_rate)
{
	return $price + (($price * $tax_rate)/100);
}

function eStore_calculate_tax_included_price_with_qty($price, $tax_rate, $qty=1)
{
	return ($price * $qty) + (($price * $qty * $tax_rate)/100);
}

function eStore_get_cart_total()
{
	$total = 0;
	if(empty($_SESSION['eStore_cart']))
	{
		return $total;
	}
	foreach ($_SESSION['eStore_cart'] as $item)
	{
		$total += $item['price'] * $item['quantity'];
	}
	return $total;
}
function eStore_calculate_cart_shipping_if_not_zero()
{
	$postage_cost = 0;
	if(isset($_SESSION['eStore_cart_postage_cost']) && $_SESSION['eStore_cart_postage_cost']== 0 ){//Maybe free shipping discount was applied		
		return $postage_cost;
	}	
	else{
		$postage_cost = eStore_get_cart_shipping();
		return $postage_cost;
	}
}
function eStore_get_cart_shipping()//For a fresh new calculation 
{
	$item_total_shipping = 0;
	$postage_cost = 0;
	if(empty($_SESSION['eStore_cart'])){
		return $postage_cost;
	}
	foreach ($_SESSION['eStore_cart'] as $item)
	{
		$item_total_shipping += $item['shipping'] * $item['quantity'];
	}
	// Base shipping can only be used in conjunction with individual item shipping
	if ($item_total_shipping != 0)
	{
	    $baseShipping = get_option('eStore_base_shipping');
	    $postage_cost = $item_total_shipping + $baseShipping;
	    $postage_cost = $postage_cost + $_SESSION['eStore_selected_shipping_option_cost'];
	}
	else
	{
	    $postage_cost = 0;
	    $postage_cost = $postage_cost + $_SESSION['eStore_selected_shipping_option_cost'];
	}
        $postage_cost = apply_filters('eStore_get_cart_shipping_filter', $postage_cost);
	return $postage_cost;
}
function eStore_get_cart_tax()
{
	$total_tax = 0;
	if(empty($_SESSION['eStore_cart'])){
		return $total_tax;
	}	
	if(get_option('eStore_enable_tax'))
	{
		$total_tax = eStore_calculate_total_cart_tax();
	}
	$_SESSION['eStore_cart_total_tax'] = $total_tax;
	if(isset($_SESSION['eStore_area_specific_total_tax'])){
		$_SESSION['eStore_cart_total_tax'] += $_SESSION['eStore_area_specific_total_tax'];
	}
	return $total_tax;	
}
function eStore_calculate_total_cart_tax()
{
	$total_tax = 0;
	$tax = 0;
	global $wpdb;
	$wp_eStore_config = WP_eStore_Config::getInstance();
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;	
	$global_store_tax_rate = get_option('eStore_global_tax_rate');		
	foreach ($_SESSION['eStore_cart'] as $item)
	{
		$id = $item['item_number'];
		$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
		if($ret_product->tax == "0"){
			$tax = 0;
		}
		else if(empty($ret_product->tax)){
			$tax_rate = $global_store_tax_rate;
			$tax = ($item['price'] * $item['quantity'] * $tax_rate)/100;
		}
		else{
			$tax = ($item['price'] * $item['quantity'] * $ret_product->tax)/100;
		}		
		$total_tax = $total_tax + $tax;
	}

	if ($wp_eStore_config->getValue('eStore_enable_tax_on_shipping')=='1'){//Calculate and add shipping tax		
		$cart_shipping_amt = eStore_calculate_cart_shipping_if_not_zero();
		$shipping_tax = (($cart_shipping_amt * $global_store_tax_rate)/100);
		$total_tax = $total_tax + $shipping_tax;
	}
	return $total_tax;	
}
function eStore_get_total_cart_item_qty()
{
	$total_items = 0;
	if(empty($_SESSION['eStore_cart']))
	{
		return $total_items;
	}
	foreach ($_SESSION['eStore_cart'] as $item)
	{
		$total_items +=  $item['quantity'];
	}
	return $total_items;
}
function eStore_get_current_cart_item_qty_for_product($id)
{
	$total_qty = 0;
	if(empty($_SESSION['eStore_cart']))
	{
		return $total_qty;
	}
	foreach ($_SESSION['eStore_cart'] as $item)
	{
		if($item['item_number'] == $id){
			$total_qty +=  $item['quantity'];
		}
	}
	return $total_qty;	
}
function eStore_get_checkout_url()
{
	if (get_option('eStore_auto_cart_anchor'))
    {
        $checkout_url = get_option('eStore_checkout_page_url')."#wp_cart_anchor";
    }
    else
    {
        $checkout_url = get_option('eStore_checkout_page_url');
    }
    return $checkout_url;
}

function eStore_show_compact_cart()
{
	if ($_SESSION['eStore_cart'] && is_array($_SESSION['eStore_cart']))
	{
		//$num_items = count($_SESSION['eStore_cart']);//to unique item count
		$num_items = eStore_get_total_cart_item_qty();// to show the total quantity count
	}
	if ($num_items > 0)
	{
		if ($num_items == 1)
		{
			$output .= $num_items.ESTORE_ITEM_IN_THE_CART;
		}
		else
		{
			$output .= $num_items.ESTORE_ITEMS_IN_THE_CART;
		}
		$output .= "<br /><a href=".eStore_get_checkout_url().">".ESTORE_VIEW_CART."</a><br />";			
	}
	else
	{
		$output = eStore_empty_cart_display();
	}
	return $output;
}

function eStore_show_order_summary()
{
	if (!digi_cart_not_empty()){
        return eStore_empty_cart_display();
    }
    $defaultSymbol = WP_ESTORE_CURRENCY_SYMBOL;
    $output = "";
	$output .= '<div class="eStore_order_summary">';
	$output .= '<div class="eStore_order_summary_header"><span class="eos_left eStore_order_summary_desc">'.WP_ESTORE_DESCRIPTION.'</span><span class="eos_right eStore_order_summary_price">'.ESTORE_PRICE.'</span></div>';
	
	foreach ($_SESSION['eStore_cart'] as $item)
	{	
		$item_price = $item['price']*$item['quantity'];
		$truncated_item_name = substr($item['name'], 0, 28);
		$output .= '<div class="eStore_order_summary_row">';
		$output .= '<span class="eos_left">';
		$output .= $truncated_item_name."...";
		$output .= '<br />'.ESTORE_PRICE.': '.print_digi_cart_payment_currency($item_price,$defaultSymbol);
		$output .= '<br />'.ESTORE_QUANTITY.': '.$item['quantity'];
		$output .= '</span>';
		$output .= '<span class="eos_right">'.print_digi_cart_payment_currency($item_price,$defaultSymbol).'</span>';
		$output .= '</div>';	
	}
	$raw_total = ($_SESSION['eStore_cart_sub_total'] + $_SESSION['eStore_cart_postage_cost'] + $_SESSION['eStore_cart_total_tax']);	
	$output .= '<div class="eStore_order_summary_row"><span class="eos_left">'.ESTORE_SUB_TOTAL.': </span><span class="eos_right">'.print_digi_cart_payment_currency($_SESSION['eStore_cart_sub_total'],$defaultSymbol).'</span></div>';
	$output .= '<div class="eStore_order_summary_row"><span class="eos_left">'.ESTORE_SHIPPING.': </span><span class="eos_right">'.print_digi_cart_payment_currency($_SESSION['eStore_cart_postage_cost'],$defaultSymbol).'</span></div>';
	$output .= '<div class="eStore_order_summary_row"><span class="eos_left">'.WP_ESTORE_TAX.': </span><span class="eos_right">'.print_digi_cart_payment_currency($_SESSION['eStore_cart_total_tax'],$defaultSymbol).'</span></div>';
	$output .= '<div class="eStore_order_summary_row eStore_order_summary_total"><span class="eos_left">'.ESTORE_TOTAL.': </span><span class="eos_right">'.print_digi_cart_payment_currency($raw_total,$defaultSymbol).'</span></div>';

	$conversion_rate = get_option('eStore_secondary_currency_conversion_rate');
	if (!empty($conversion_rate))
	{
		$secondary_total = $raw_total*$conversion_rate;
		$secondary_curr_symbol = get_option('eStore_secondary_currency_symbol');		
		$output .= '<div class="eStore_order_summary_row eStore_order_summary_total_secondary"><span class="eos_left">'.ESTORE_TOTAL.' ('.get_option('eStore_secondary_currency_code').'): </span><span class="eos_right">'.print_digi_cart_payment_currency($secondary_total,$secondary_curr_symbol).'</span></div>';
	}
	$output .= '</div>';
	$output .= '<div class="eStore-clear-float"></div>';
	return $output;
}

function eStore_shopping_cart_fancy1()
{
	$output = "";
	$output .= '<div class="eStore_fancy1_cart_wrapper estore-cart-wrapper-1">';
	if (!digi_cart_not_empty()){
		$output .= eStore_empty_cart_display();
		$output .= "</div>";//end wrapper
        return $output;
    }
    $decimal = '.';
    $defaultSymbol = get_option('cart_currency_symbol');
    if (!empty($defaultSymbol))
        $currency_symbol = $defaultSymbol;
    else
        $currency_symbol = '$';        

    $output .= '<a name="wp_cart_anchor"></a>';    
    $output .= '<div class="eStore_cart_fancy1">';
    
    $title = get_option('wp_cart_title');
    if(!empty($title))
    {
    	$output .= '<div class="eStore_cart_fancy1_header">';
    	$output .= $title;
    	$output .= '</div>';
    }
	
    $output .= eStore_cart_display_quantity_change_warning_part();

        $output .= '<div class="eStore_cart_fancy1_inside">';
	$output .= '<table class="eStore_cart_fancy1_inside_tbl" style="width: 100%;">';
	$output .= eStore_cart_display_items_part($currency_symbol,$decimal);
	$total_tax = eStore_get_cart_tax();
	$output .= eStore_cart_display_total_part($currency_symbol,$decimal,$_SESSION['eStore_cart_sub_total'],$_SESSION['eStore_cart_postage_cost'],$_SESSION['eStore_cart_total_tax']);
	//$output .= eStore_cart_display_continue_shopping_part(); 
	if (get_option('eStore_display_continue_shopping')) 
	{
            $products_page = get_option('eStore_products_page_url');
            $continue_shopping_url = apply_filters('eStore_cart_continue_shopping_url_filter',$products_page);
            $output .= '<tr><td colspan="4"><a href="'.$continue_shopping_url.'"><img src="'.WP_ESTORE_IMAGE_URL.'/cart_fancy1_continue_shopping.png" alt="Continue Shopping" /></a></td></tr>';             
	}
	$below_continue_shopping = "";
	$below_continue_shopping = apply_filters('eStore_below_continue_shopping_filter', $below_continue_shopping);
	if(!empty($below_continue_shopping)){$output .= '<div class="eStore_below_continue_shopping">'.$below_continue_shopping.'</div>';} 
		
	$output .= eStore_cart_save_retrieve_cart_part();
	$output .= eStore_cart_display_action_message_part();
        $output .= eStore_cart_display_store_pickup_part();
	$output .= eStore_cart_display_area_specific_tax_part();
	$output .= eStore_cart_display_shipping_variation_part($_SESSION['eStore_cart_postage_cost']);	
	$output .= eStore_cart_display_enter_affiliate_id_part();
	$output .= eStore_cart_display_enter_coupon_part();
	$output .= "</table>";
        $output .= '</div>';//end of .eStore_cart_fancy1_inside
	
	$output .= '<div class="eStore_cart_fancy1_footer">';
	$output .= eStore_cart_display_checkout_button_form_part();
	$output .= '</div>';
	$output .= "</div>";
	$output .= "</div>";//end wrapper     	  	             
	return $output;
}

function eStore_shopping_cart_fancy2()
{
	$output = "";
	$output .= '<div class="eStore_fancy2_cart_wrapper estore-cart-wrapper-2">';
	if (!digi_cart_not_empty()){
		$output .= eStore_empty_cart_display();
		$output .= "</div>";//end wrapper
        return $output;
    }
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $decimal = '.';	
    $currency_symbol = $wp_eStore_config->getValue('cart_currency_symbol');
    
    $output .= '<a name="wp_cart_anchor"></a>';    
    $output .= '<div class="eStore_cart_fancy2">';
    $output .= '<div class="eStore_cart_fancy2_inside">';
    
    $title = get_option('wp_cart_title');
    if(!empty($title)){
    	$output .= '<div class="eStore_cart_fancy2_header">';
    	$output .= $title;
    	$output .= '</div>';
    }
    $output .= eStore_cart_display_quantity_change_warning_part();  

	$output .= '<table style="width: 100%;padding:0px 10px 0px 10px;">';
	$output .= eStore_cart_display_items_part($currency_symbol,$decimal,"2");
	$total_tax = eStore_get_cart_tax();
	$output .= eStore_cart_display_total_part_fancy2($currency_symbol,$decimal,$_SESSION['eStore_cart_sub_total'],$_SESSION['eStore_cart_postage_cost'],$_SESSION['eStore_cart_total_tax']);
	//$output .= eStore_cart_display_continue_shopping_part();
	if (get_option('eStore_display_continue_shopping')){
            $products_page = get_option('eStore_products_page_url');
            $continue_shopping_url = apply_filters('eStore_cart_continue_shopping_url_filter',$products_page);
            $output .= '<tr><td colspan="4"><a href="'.$continue_shopping_url.'" class="eStore_cart_fancy_2_continue_shopping">'.ESTORE_CONTINUE_SHOPPING.'</a></td></tr>';             
	}
	$output .= '<tr><td colspan="4"><div class="eStore_cart_fancy2_divider"></div></td></tr>';
	$below_continue_shopping = "";
	$below_continue_shopping = apply_filters('eStore_below_continue_shopping_filter', $below_continue_shopping);
	if(!empty($below_continue_shopping)){$output .= '<div class="eStore_below_continue_shopping">'.$below_continue_shopping.'</div>';} 
	
	$output .= eStore_cart_save_retrieve_cart_part();
	$output .= eStore_cart_display_action_message_part();
        $output .= eStore_cart_display_store_pickup_part();
	$output .= eStore_cart_display_area_specific_tax_part();
	$output .= eStore_cart_display_shipping_variation_part($_SESSION['eStore_cart_postage_cost']);
	$output .= eStore_cart_display_enter_affiliate_id_part();
	$output .= eStore_cart_display_enter_coupon_part();	
	$output .= "</table>"; 
	
	$output .= eStore_cart_display_checkout_button_form_part();
	
	$output .= '</div>';//end of eStore_cart_fancy2_inside
    $output .= '</div>';//end of eStore_cart_fancy2
    $output .= "</div>";//end wrapper
    return $output;
}

function eStore_shopping_cart_multiple_gateway()
{
	$output = "";
	$output .= '<div class="eStore_classic_cart_wrapper estore-cart-wrapper-0">';
	$title = get_option('wp_cart_title');
	if (!digi_cart_not_empty()){
		$output .= '<div class="shopping_cart" style="padding: 5px;">';    	
        if (!get_option('eStore_shopping_cart_image_hide')){
        	$output .= "<input type='image' src='".WP_ESTORE_URL."/images/shopping_cart_icon.gif' value='Shopping Cart' title='Shopping Cart' />";
        }
       	$output .= '<h2>'.$title.'</h2>';		
		$output .= eStore_empty_cart_display();
		$output .= "</div>";
		$output .= "</div>";//end wrapper
        return $output;
    }
    $decimal = '.';
    $defaultSymbol = get_option('cart_currency_symbol');
    if (!empty($defaultSymbol))
        $currency_symbol = $defaultSymbol;
    else
        $currency_symbol = '$';
	        
    $output .= '<a name="wp_cart_anchor"></a>';
    $output .= '<div class="shopping_cart" style="padding: 5px;">';
    if (!get_option('eStore_shopping_cart_image_hide'))
    {
		$products_page = get_option('eStore_products_page_url');
		$green_cart_img_src = WP_ESTORE_URL."/images/shopping_cart_icon.gif";
		if (!empty($products_page)){
		    $output .= '<a rel="nofollow" href="'.$products_page.'"><img src="'.$green_cart_img_src.'" class="eStore_empty_cart" alt="'.ESTORE_CONTINUE_SHOPPING.'" title="'.ESTORE_CONTINUE_SHOPPING.'" /></a>';
		}
		else{    	
    		$output .= "<img src='".$green_cart_img_src."' alt='' />";
		}
    }
    if(!empty($title))
    {
    	$output .= '<h2>';
    	$output .= $title;
    	$output .= '</h2>';
    }

    $output .= eStore_cart_display_quantity_change_warning_part();

	$output .= '<table style="width: 100%;">';
	$output .= eStore_cart_display_items_part($currency_symbol,$decimal);
	$total_tax = eStore_get_cart_tax();
	$output .= eStore_cart_display_total_part($currency_symbol,$decimal,$_SESSION['eStore_cart_sub_total'],$_SESSION['eStore_cart_postage_cost'],$_SESSION['eStore_cart_total_tax']);
	$output .= eStore_cart_display_continue_shopping_part();
	$below_continue_shopping = "";
	$below_continue_shopping = apply_filters('eStore_below_continue_shopping_filter', $below_continue_shopping);
	if(!empty($below_continue_shopping)){$output .= '<div class="eStore_below_continue_shopping">'.$below_continue_shopping.'</div>';} 	
	
	$output .= eStore_cart_save_retrieve_cart_part();
	$output .= eStore_cart_display_action_message_part();
        $output .= eStore_cart_display_store_pickup_part();
	$output .= eStore_cart_display_area_specific_tax_part();
	$output .= eStore_cart_display_shipping_variation_part($_SESSION['eStore_cart_postage_cost']);
	$output .= eStore_cart_display_enter_affiliate_id_part();
	$output .= eStore_cart_display_enter_coupon_part();            
	$output .= "</table>";  
	
	$output .= eStore_cart_display_checkout_button_form_part();
	$output .= "</div>";
	$output .= "</div>";//end of wrapper   	  	             
	return $output;
}

function eStore_cart_display_quantity_change_warning_part()
{
	$output = "";
	//eStore_load_cart_qty_change_jquery();//in the footer
    if (!isset($_SESSION['discount_applied_once']) || $_SESSION['discount_applied_once'] != 1)
    {
        $output .= '<br /><span class="eStore_qty_change_pinfo" style="display: none; font-weight: bold; color: red;">'.ESTORE_QUANTITY_CHANGE.'</span>';
    }
    else
    {
        $output .= '<br /><span class="eStore_qty_change_pinfo" style="display: none; font-weight: bold; color: red;">'.ESTORE_QUANTITY_CHANGE_NOT_ALLOWED.'</span>';
    }
    return $output;
}

function eStore_cart_display_items_part($currency_symbol,$decimal,$fancy='')
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    if(get_option('eStore_use_auto_discount'))
    {    
            $total = eStore_get_cart_total();
            $total_items = eStore_get_total_cart_item_qty();
            // Auto discount for normal purchase		
            $amt_threshold = get_option('eStore_amount_threshold_auto_coupon');
            if( !empty($amt_threshold) && $total > $amt_threshold)
            {
                    if(isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1){
                            eStore_load_price_from_backed_up_cart();
                            unset($_SESSION['discount_applied_once']);
                    }
                    $coupon = get_option('eStore_amount_threshold_auto_coupon_code');
                    eStore_apply_discount($coupon);
                    $_SESSION['auto_discount_applied_once'] = 1;
            }
            $qty_threshold = get_option('eStore_qty_threshold_auto_coupon');
            if( !empty($qty_threshold) && $total_items > $qty_threshold)
            {		
                    if(isset($_SESSION['discount_applied_once']) && $_SESSION['discount_applied_once'] == 1){
                            eStore_load_price_from_backed_up_cart();
                            unset($_SESSION['discount_applied_once']);
                    }					
                    $coupon = get_option('eStore_qty_threshold_auto_coupon_code');
                    eStore_apply_discount($coupon);		
                    $_SESSION['auto_discount_applied_once'] = 1;
            }	
    }
    $output = "";
    $total_items = 0;
    $total = 0;
    $item_total_shipping = 0;    
    if ($_SESSION['eStore_cart'] && is_array($_SESSION['eStore_cart']))
    {
        $output .= '<tr>
        <th class="eStore_cart_item_name">'.ESTORE_ITEM_NAME.'</th>';
       	if($wp_eStore_config->getValue('eStore_do_not_show_qty_in_cart')){
        	$output .='<th></th>';
        }
        else{        	
        	$output .='<th class="eStore_cart_item_qty">'.ESTORE_QUANTITY.'</th>';
        }
        $output .='<th class="eStore_cart_item_price">'.ESTORE_PRICE.'</th><th></th>
        </tr>';

        foreach ($_SESSION['eStore_cart'] as $item)
        {
        	//Draw the line item
        	if(!empty($fancy)){
        		if($fancy=="2"){
        			$output .= eStore_cart_display_line_item_fancy2($item,$currency_symbol,$decimal);
        		}
        	}else{
        		$output .= eStore_cart_display_line_item($item,$currency_symbol,$decimal);
        	}
        	//End drawing line item
                  
            $total += $item['price'] * $item['quantity'];
    		$item_total_shipping += $item['shipping'] * $item['quantity'];
            $total_items +=  $item['quantity'];
        }
        $_SESSION['eStore_cart_sub_total'] = $total;
        
        //Check the shipping variation price
        if(!isset($_SESSION['eStore_selected_shipping_option_cost'])){
        	$_SESSION['eStore_selected_shipping_option_cost'] = 0;
        }
        if(isset($_POST['eStore_shipping_variation']))
        {
        	$_POST['eStore_shipping_variation'] = strip_tags($_POST['eStore_shipping_variation']);
        	$pieces = explode('|',$_POST['eStore_shipping_variation']);
        	$_SESSION['eStore_selected_shipping_option'] = $pieces[0];
        	if(!empty($pieces[1]))
        		$_SESSION['eStore_selected_shipping_option_cost'] = $pieces[1];  
        	else
        	    $_SESSION['eStore_selected_shipping_option_cost'] = 0;  		
        }
  
        // Base shipping can only be used in conjunction with individual item shipping
        if ($item_total_shipping != 0)
        {
        	$baseShipping = get_option('eStore_base_shipping');
        	$postage_cost = $item_total_shipping + $baseShipping;
        	$_SESSION['eStore_cart_postage_cost'] = $postage_cost + $_SESSION['eStore_selected_shipping_option_cost'];
        }
        else
        {
        	$postage_cost = 0;
            $_SESSION['eStore_cart_postage_cost'] = $postage_cost + $_SESSION['eStore_selected_shipping_option_cost'];
        }
        if(get_option('eStore_use_auto_discount'))
        {			
            // Auto shipping discount
            $amt_threshold_free_shipping = get_option('eStore_amount_free_shipping_threshold');
            $total = apply_filters('eStore_total_before_auto_shipping_discount', $total);
            if( !empty($amt_threshold_free_shipping) && $total > $amt_threshold_free_shipping)
            {
                $postage_cost = 0;
                $_SESSION['eStore_cart_postage_cost'] = $postage_cost;		
                $_SESSION['eStore_last_action_msg_3'] = '<p style="color: green;">'.ESTORE_TOTAL_DISCOUNT.ESTORE_DISCOUNT_FREE_SHIPPING.'</p>';
            }
            $qty_threshold_free_shipping = get_option('eStore_qty_free_shipping_threshold');
            if( !empty($qty_threshold_free_shipping) && $total_items > $qty_threshold_free_shipping)
            {				
                $postage_cost = 0;
                $_SESSION['eStore_cart_postage_cost'] = $postage_cost;	
                $_SESSION['eStore_last_action_msg_3'] = '<p style="color: green;">'.ESTORE_TOTAL_DISCOUNT.ESTORE_DISCOUNT_FREE_SHIPPING.'</p>';
            }
        }
    }
    $below_item_details_section = "";
    $below_item_details_section = apply_filters('eStore_below_item_details_filter', $below_item_details_section);
    if(!empty($below_item_details_section)){$output .= '<div class="eStore_below_item_details">'.$below_item_details_section.'</div>';}
    do_action('eStore_action_after_cart_items_details');
    return $output;
}

function eStore_cart_display_line_item($item,$currency_symbol,$decimal)
{
	//When changing functionaly of this function, you need to change the functionality in the corresponding fancy functions too
	$wp_eStore_config = WP_eStore_Config::getInstance();
	$output = "";
	$output .= "<tr><td style='overflow: hidden;'><a href='".$item['cartLink']."'>".$item['name']."</a></td>";
	if($wp_eStore_config->getValue('eStore_do_not_show_qty_in_cart')){
		$output .='<td></td>';            	
	}
	else{
		$output .= "<td class='eStore_cart_item_qty_value'><form method=\"post\"  action=\"\" name='peStore_cquantity' style='display: inline'>
		<input type=\"hidden\" name=\"estore_product_name\" value=\"".htmlspecialchars($item['name'])."\" />    
		<input type='hidden' name='eStore_cquantity' value='1' /><input type='text' name='quantity' value='".$item['quantity']."' size='1' class='eStore_cart_item_qty' />";
		if(WP_ESTORE_SHOW_UPDATE_BUTTON_FOR_QTY_CHANGE === '1'){	            	 
			$output .= '<input type="submit" value="'.ESTORE_UPDATE.'" class="eStore_update_qty" />';
		}
		$output .= "</form></td>";
	}
        	
	if(WP_ESTORE_DISPLAY_TAX_INCLUSIVE_PRICE === '1')
	{
		if(!empty($item['tax'])){
			$tax_rate = $item['tax'];
    	}
    	else{
    		$global_store_tax_rate = get_option('eStore_global_tax_rate');
    		$tax_rate = $global_store_tax_rate;
		}
    	$tax_included_price = eStore_calculate_tax_included_price_without_qty($item['price'], $tax_rate);
    	$item_display_price_amt = $tax_included_price;     		
	}       	
    else{
		$item_display_price_amt = $item['price'];
	}
	//Display Price
	$output .= "<td class='eStore_cart_item_price_value'>".print_digi_cart_payment_currency(($item_display_price_amt * $item['quantity']), $currency_symbol, $decimal);
	if(WP_ESTORE_DISPLAY_ORIGINAL_ITEM_PRICE_BEFORE_COUPON === '1' && isset($_SESSION['discount_applied_once'])){
        $price_before_coupon = eStore_get_original_item_price_from_backed_up_cart($item['name']);        		
        if(!empty($price_before_coupon)){
        	if(WP_ESTORE_DISPLAY_TAX_INCLUSIVE_PRICE === '1'){
        		$price_before_coupon = eStore_calculate_tax_included_price_without_qty($price_before_coupon, $tax_rate);
        	}
        	$output .= ' (<span class="eStore_price_before_coupon">'.print_digi_cart_payment_currency(($price_before_coupon * $item['quantity']), $currency_symbol, $decimal).'</span>)';
        }
	}
	$output .= "</td>";
        	            
	$output .= "<td style='text-align: left'><form method=\"post\"  action=\"\">
	<input type=\"hidden\" name=\"estore_product_name\" value=\"".htmlspecialchars($item['name'])."\" />
	<input type='hidden' name='eStore_delcart' value='1' />
	<input type='image' src='".WP_ESTORE_URL."/images/Shoppingcart_delete.gif' class='eStore_remove_item_button' value='Remove' title='".ESTORE_REMOVE_ITEM."' /></form></td></tr>";  
	return $output;
}
function eStore_cart_display_line_item_fancy2($item,$currency_symbol,$decimal)
{
	$wp_eStore_config = WP_eStore_Config::getInstance();
	$currency_symbol = $wp_eStore_config->getValue('cart_currency_symbol');
	$output = "";
	$output .= "<tr>";
	//Display Item name
	$output .= "<td class='eStore_cart_item_name_value'><span class='eStore_cart_fancy_2_item_name'>".$item['name']."</span>";
	$output .= "<span class='eStore_cart_fancy_2_remove'><form method=\"post\"  action=\"\">
	<input type=\"hidden\" name=\"estore_product_name\" value=\"".htmlspecialchars($item['name'])."\" />
	<input type='hidden' name='eStore_delcart' value='1' />
	<input type='submit' value='Remove' title='".ESTORE_REMOVE_ITEM."' class='eStore_cart_fancy_2_remove' />
	</form></span>";	
	$output .= "</td>";
	
	//Display qty
	if($wp_eStore_config->getValue('eStore_do_not_show_qty_in_cart')){
		$output .='<td></td>';            	
	}
	else{
		$output .= "<td class='eStore_cart_item_qty_value'><form method=\"post\"  action=\"\" name='peStore_cquantity' style='display: inline'>
		<input type=\"hidden\" name=\"estore_product_name\" value=\"".htmlspecialchars($item['name'])."\" />    
		<input type='hidden' name='eStore_cquantity' value='1' /><input type='text' name='quantity' value='".$item['quantity']."' size='1' class='eStore_cart_item_qty' />";
		if(WP_ESTORE_SHOW_UPDATE_BUTTON_FOR_QTY_CHANGE === '1'){	            	 
			$output .= '<input type="submit" value="'.ESTORE_UPDATE.'" class="eStore_update_qty" />';
		}
		$output .= "</form></td>";
	}
        	
	if(WP_ESTORE_DISPLAY_TAX_INCLUSIVE_PRICE === '1')
	{
		if(!empty($item['tax'])){
			$tax_rate = $item['tax'];
    	}
    	else{
    		$global_store_tax_rate = get_option('eStore_global_tax_rate');
    		$tax_rate = $global_store_tax_rate;
		}    			
    	$tax_included_price = eStore_calculate_tax_included_price_without_qty($item['price'], $tax_rate);
    	$item_display_price_amt = $tax_included_price;     		
	}       	
    else{
		$item_display_price_amt = $item['price'];
	}
	
	//Display Price
	$output .= "<td class='eStore_cart_item_price_value'><span class='eStore_cart_fancy_2_price'>".print_digi_cart_payment_currency(($item_display_price_amt * $item['quantity']), $currency_symbol, $decimal)."</span>";
	if(WP_ESTORE_DISPLAY_ORIGINAL_ITEM_PRICE_BEFORE_COUPON === '1' && isset($_SESSION['discount_applied_once'])){
        $price_before_coupon = eStore_get_original_item_price_from_backed_up_cart($item['name']);        		
        if(!empty($price_before_coupon)){
        	if(WP_ESTORE_DISPLAY_TAX_INCLUSIVE_PRICE === '1'){
        		$price_before_coupon = eStore_calculate_tax_included_price_without_qty($price_before_coupon, $tax_rate);
        	}
        	$output .= ' (<span class="eStore_price_before_coupon">'.print_digi_cart_payment_currency(($price_before_coupon * $item['quantity']), $currency_symbol, $decimal).'</span>)';
        }
	}
	$output .= "</td>";
	$output .= "<td></td>";
	$output .= "</tr>";//End of line item row    	            
	return $output;	
}

function eStore_cart_display_shipping_cost_part($currency_symbol,$decimal,$postage_cost,$shipping_price_class="eStore_cart_shipping_price")
{
	$output = "";	
    if(WP_ESTORE_MUST_UPDATE_SHIPPING_SELECTION_TO_VIEW_SHIPPING==='1')//apply the shipping variation needs to be updated logic
    {
		$update_check_needed = false;
		$always_display_shipping_var = get_option('eStore_always_display_shipping_variation');
		if($always_display_shipping_var != ''){$update_check_needed = true;}
		$shippping_var_txt = get_option('eStore_shipping_variation');
		if($postage_cost != 0 && !empty($shippping_var_txt)){$update_check_needed = true;}

		if($update_check_needed && !isset($_SESSION['eStore_selected_shipping_option'])){
    		$output .= "<tr><td colspan='3' style='font-weight: bold; text-align: right;'><span class='eStore_cart_select_shipping_msg'>".WP_ESTORE_SELECT_SHIPPING_OPTION_TO_CALC_SHIPPING."</span></td><td></td></tr>";
    		return $output;
		}
    }
	
    if($postage_cost != 0){
		$output .= "<tr class='estore-cart-shipping'><td colspan='2' style='font-weight: bold; text-align: right;'>".ESTORE_SHIPPING.": </td><td style='text-align: left'><span class='".$shipping_price_class."'>".print_digi_cart_payment_currency($postage_cost, $currency_symbol, $decimal)."</span></td><td></td></tr>";
    }
    return $output;
}

function eStore_cart_display_total_part($currency_symbol,$decimal,$total,$postage_cost,$total_tax=0)
{
	$output = "";
	if($postage_cost != 0 || $total_tax != 0){
		$output .= "<tr class='estore-cart-subtotal'><td colspan='2' style='font-weight: bold; text-align: right;'>".ESTORE_SUB_TOTAL.": </td><td style='text-align: left'>".print_digi_cart_payment_currency($total, $currency_symbol, $decimal)."</td><td></td></tr>";
	}
	
	$output .= eStore_cart_display_shipping_cost_part($currency_symbol,$decimal,$postage_cost);
	
    if($total_tax != 0){
    	$output .= "<tr class='estore-cart-tax'><td colspan='2' style='font-weight: bold; text-align: right;'>".WP_ESTORE_TAX.": </td><td style='text-align: left'>".print_digi_cart_payment_currency($total_tax, $currency_symbol, $decimal)."</td><td></td></tr>";
    }
    $empty_cart_code = '<form  method="post" action="" >';
    $empty_cart_code .= "<input type='image' src='".WP_ESTORE_URL."/images/empty-shopping-cart-icon.png' class='eStore_empty_cart_button' value='Empty' title='".ESTORE_EMPTY_CART."' />";
    $empty_cart_code .= '<input type="hidden" name="reset_eStore_cart" value="1" /></form>';

    $output .= "<tr class='estore-cart-total'><td colspan='2' style='font-weight: bold; text-align: right;'>".ESTORE_TOTAL.": </td><td style='text-align: left'>".print_digi_cart_payment_currency(($total+$postage_cost+$total_tax), $currency_symbol, $decimal)."</td>";
    $output .= "<td>".$empty_cart_code."</td></tr>";	
    return $output;
}

function eStore_cart_display_total_part_fancy2($currency_symbol,$decimal,$total,$postage_cost,$total_tax=0)
{
	$output = "";
	if($postage_cost != 0 || $total_tax != 0){
		$output .= "<tr class='estore-cart-subtotal'><td colspan='2' style='font-weight: bold; text-align: right;'>".ESTORE_SUB_TOTAL.": </td><td style='text-align: left'><span class='eStore_cart_fancy_2_price'>".print_digi_cart_payment_currency($total, $currency_symbol, $decimal)."</span></td><td></td></tr>";
	}
	
	$output .= eStore_cart_display_shipping_cost_part($currency_symbol,$decimal,$postage_cost,"eStore_cart_fancy_2_price");

    if($total_tax != 0){
    	$output .= "<tr class='estore-cart-tax'><td colspan='2' style='font-weight: bold; text-align: right;'>".WP_ESTORE_TAX.": </td><td style='text-align: left'><span class='eStore_cart_fancy_2_price'>".print_digi_cart_payment_currency($total_tax, $currency_symbol, $decimal)."</span></td><td></td></tr>";
    }
    $output .= "<tr class='estore-cart-total'><td colspan='2' style='font-weight: bold; text-align: right;'>".ESTORE_TOTAL.": </td><td style='text-align: left'><span class='eStore_cart_fancy_2_price'>".print_digi_cart_payment_currency(($total+$postage_cost+$total_tax), $currency_symbol, $decimal)."</span></td>";
    $output .= "<td></td></tr>";	
    return $output;
}

function eStore_cart_display_continue_shopping_part()
{
    if (get_option('eStore_display_continue_shopping')) 
    {
    	$products_page = get_option('eStore_products_page_url');
        $continue_shopping_url = apply_filters('eStore_cart_continue_shopping_url_filter',$products_page);
    	$output = '<tr><td colspan="4">&laquo;<a href="'.$continue_shopping_url.'"><strong>'.ESTORE_CONTINUE_SHOPPING.'</strong></a></td></tr>';    
    	return $output;            
    }    
}
function eStore_cart_save_retrieve_cart_part()
{
	$wp_eStore_config = WP_eStore_Config::getInstance();
	if ($wp_eStore_config->getValue('eStore_enable_save_retrieve_cart')=='1')
	{
		$output = "";
		$output .= '<tr><td colspan="4">';
		$output .= '<div class="eStore_save_retrieve_cart_section">';
		$output .= '<div class="eStore_save_retrieve_cart_body">';
		$output .= '<span class="eStore_save_cart_section">';
		$output .= '<a href="#" class="eStore_save_cart_button" title="'.ESTORE_SAVE.'">'.ESTORE_SAVE.'</a>';
		$output .= '</span>';//eStore_save_cart_section
		$output .= '<span class="eStore_retrieve_cart_section">';		
		$output .= '<a href="#" class="eStore_retrieve_cart_button" title="'.ESTORE_RETRIEVE.'">'.ESTORE_RETRIEVE.'</a>';
		$output .= '</span>';//eStore_retrieve_cart_section
		$output .= '</div>';//end of eStore_save_retrieve_cart_body
		$output .= '<div class="eStore_save_retrieve_cart_message"></div>';
		$output .= '</div>';//end of eStore_save_retrieve_cart_section
		$output .= '</td></tr>';
		return $output;
	}
}
function eStore_cart_display_action_message_part()
{
	$output = "";
	if (!empty($_SESSION['eStore_last_action_msg']) || !empty($_SESSION['eStore_last_action_msg_2']))	
    {
        if(isset($_SESSION['eStore_last_action_msg_2'])){
    		$output .= '<tr><td colspan="4"><strong><i>'.$_SESSION['eStore_last_action_msg_2'].'</i></strong></td></tr>';
    	} 
    	if(isset($_SESSION['eStore_last_action_msg'])){   	
    		$output .= '<tr><td colspan="4"><strong><i>'.$_SESSION['eStore_last_action_msg'].'</i></strong></td></tr>';
    	}     	            
    }
    if(isset($_SESSION['eStore_last_action_msg_3']))
    {
    	$output .= '<tr><td colspan="4"><strong><i>'.$_SESSION['eStore_last_action_msg_3'].'</i></strong></td></tr>';
    }
    return $output;
}
function eStore_cart_display_area_specific_tax_part()
{
	if (WP_ESTORE_APPLY_TAX_FOR_CERTAIN_AREA !== '0')
    {
    	//$output .= eStore_load_area_specific_tax_jquery(); - this now gets included in the footer
    	$output .= '<tr class="eStore_regional_tax_choice_section"><td colspan="4">
    	    		<form method="post" action="" class="eStore_area_tax_form" >    			    
    			    <input type="checkbox" name="eStore_area_tax_chkbox" class="eStore_area_tax_chkbox" value="" />
    			    <label for="eStore_area_tax_chkbox">'.WP_ESTORE_APPLY_TAX_FOR_CERTAIN_AREA.'</label>
    			    <input type="submit" class="eStore_area_tax_submit" value="'.ESTORE_APPLY.'" />
    			    <input type="hidden" name="eStore_area_tax_submitted" value="1" />
    			    </form>
                    </td></tr>';
    	return $output;
    }
}
function eStore_cart_display_store_pickup_part()
{
    if (WP_ESTORE_DO_NOT_APPLY_SHIPPING_FOR_STORE_PICKUP != '0')
    {
        if(isset($_SESSION['eStore_store_pickup_checked']) && $_SESSION['eStore_store_pickup_checked'] == '1'){ 
            $store_pickup = ' checked="checked"';
        }
        else{
            $store_pickup = '';    
        }
        $output .= '<tr class="eStore_store_pickup_choice_section"><td colspan="4">';
        $output .= '<form method="post" action="" class="eStore_store_pickup_form">
                    <input type="checkbox" name="eStore_store_pickup_chkbox" class="eStore_store_pickup_chkbox" value="1" '.$store_pickup.' />
                    <label for="eStore_store_pickup_chkbox">'.WP_ESTORE_DO_NOT_APPLY_SHIPPING_FOR_STORE_PICKUP.'</label>
                    <input type="submit" name="eStore_store_pickup_submit" class="eStore_store_pickup_submit_btn" value="'.ESTORE_APPLY.'" />
                    <input type="hidden" name="eStore_store_pickup_data_submitted" value="1" />';
        $output .= '<input type="hidden" name="eStore_store_pickup_chkbx_data" value="'.$store_pickup.'" />';
        $output .= '</form>';
        $output .= '</td></tr>';
        return $output;
    }
}
function eStore_cart_display_shipping_variation_part($postage_cost,$button_type=1)
{
	$output = "";
	$always_display_shipping_var = get_option('eStore_always_display_shipping_variation');	
	if ($postage_cost != 0 || $always_display_shipping_var != '')
	{
		$shippping_var_txt = get_option('eStore_shipping_variation');		
		if (!empty($shippping_var_txt))
		{
			$var_output = "";
			//eStore_load_shipping_var_change_warning_jquery();//in the footer
			$variation_add_string = WP_ESTORE_VARIATION_ADD_SYMBOL;//"+";
			$curr_sign = WP_ESTORE_CURRENCY_SYMBOL;
			$pieces = explode('|',$shippping_var_txt);
			$variation1_name = $pieces[0];
	
			$var_output .= $variation1_name." : ";
			$var_output .= '<select name="eStore_shipping_variation" class="shipping_variation">';
			for ($i=1;$i<sizeof($pieces); $i++)
			{
				$pieces2 = explode(':',$pieces[$i]);
				if (sizeof($pieces2) > 1)
					$tmp_txt = $pieces2[0].' ['.$variation_add_string.' '.print_digi_cart_payment_currency($pieces2[1],$curr_sign).']';
				else	
					$tmp_txt = $pieces2[0];
				if(!isset($pieces2[1])){$pieces2[1] = '';}
				$var_output .= '<option value="'.htmlspecialchars($pieces2[0]."|".$pieces2[1]).'" '.eStore_is_shipping_option_selected($pieces2[0]).'>'.$tmp_txt.'</option>';
			}
			$var_output .= '</select>';
			
			$output .= '<tr><td colspan="4">';//Used to show shipping variations
		    $output .= '<strong>'.ESTORE_SHIPPING_VARIATION.'</strong>
		    		    <form method="post" action="" >
		    		    '.$var_output.'
		    		    <input type="submit" class="eStore_shipping_update_button" value="'.ESTORE_UPDATE.'" />
		    		    </form>';
		    if(!isset($_SESSION['eStore_shipping_variation_updated_once']) || $_SESSION['eStore_shipping_variation_updated_once'] != '1'){
		    	$output .= '<div class="shipping_var_changed_default eStore_warning">'.ESTORE_CLICK_UPDATE_BUTTON.'</div>';
		    }
		    $output .= '<div class="shipping_var_changed eStore_warning">'.ESTORE_CLICK_UPDATE_BUTTON.'</div>';//used from javascript
		    $output .= '</td></tr>';
		}
	}	
    return $output;	
}
function eStore_is_shipping_option_selected($option_to_match)
{
	if(isset($_SESSION['eStore_selected_shipping_option']) && $_SESSION['eStore_selected_shipping_option'] == $option_to_match)
		return 'selected="selected"';		
}
function eStore_cart_display_enter_affiliate_id_part()
{
	if (get_option('eStore_aff_allow_aff_id') == 1)
    {
    	$output .= '<tr><td colspan="4"><strong>'.ESTORE_ENTER_AFFILIATE_ID.'</strong>
    			    <form  method="post" action="" >
    			    <input type="text" name="estore_aff_id" id="estore_aff_id" value="" size="10" />
    			    <input type="submit" value="'.ESTORE_APPLY.'" />
    			    <input type="hidden" name="eStore_apply_aff_id" value="1" />
    			    </form>
    				</td></tr>';
    	return $output;
    }            

}
function eStore_cart_display_enter_coupon_part()
{
	if (get_option('eStore_use_coupon_system') == 1)
    {
    	$output = '<tr><td colspan="4">
    				<div class="eStore_coupon_section">
    				<strong>'.ESTORE_ENTER_COUPON_CODE.'</strong>    			    
    			    <form  method="post" action="" >
    			    <input type="text" name="coupon_code" id="coupon_code" value="" size="10" />
    			    <input type="submit" class="eStore_apply_coupon" value="'.ESTORE_APPLY.'" />
    			    <input type="hidden" name="eStore_apply_discount" value="1" />
    			    </form>
    			    </div>
    				</td></tr>';
    	return $output;
	}	
}
function eStore_cart_display_checkout_button_form_part()
{
	$output = "";
	global $wp_eStore_config;	
	if(defined('WP_PAYMENT_GATEWAY_BUNDLE_VERSION')){// Load payment gateway bundle config		
		$wp_pg_bundle_config = WP_Payment_Gateway_Bundle_Config::getInstance();
	}	
	
	//Check if minimum and maximum cart checkout amount restriction apply
	if ($wp_eStore_config->getValue('eStore_enable_checkout_amt_limit')=='1')//shopping cart checkout limitation feature is enabled
	{
		$minimum_cart_co_amount = $wp_eStore_config->getValue('eStore_checkout_amt_limit_minimum');
		if(!empty($minimum_cart_co_amount)){
			$minimum_cart_co_amount = number_format((double)$minimum_cart_co_amount,2);
		}
		$maximum_cart_co_amount = $wp_eStore_config->getValue('eStore_checkout_amt_limit_maximum');
		if(!empty($maximum_cart_co_amount)){
			$maximum_cart_co_amount = number_format((double)$maximum_cart_co_amount,2);
		}
		$cart_sub_total = eStore_get_cart_total();	
		if(is_numeric($minimum_cart_co_amount) && $cart_sub_total < $minimum_cart_co_amount){
			$output .= '<p class="eStore_error_message">';
			$output .= ESTORE_CART_DOES_NOT_MEET_MIN_REQUIREMENT;
			$output .= ESTORE_CART_MINIMUM_CHECKOUT_AMOUNT_REQUIRED . WP_ESTORE_CURRENCY_SYMBOL . $minimum_cart_co_amount;
			$output .= '</p>';
			return $output;
		}
		if(is_numeric($maximum_cart_co_amount) && $cart_sub_total > $maximum_cart_co_amount){
			$output .= '<p class="eStore_error_message">';
			$output .= ESTORE_CART_DOES_NOT_MEET_MIN_REQUIREMENT;
			$output .= ESTORE_CART_MAXIMUM_CHECKOUT_AMOUNT_REQUIRED . WP_ESTORE_CURRENCY_SYMBOL . $maximum_cart_co_amount;
			$output .= '</p>';
			return $output;
		}		
	}
	
	//Create the checkout button form
            $output .= '<div class="eStore_cart_checkout_button">';
			if (get_option('eStore_show_t_c')){$output .= eStore_show_terms_and_cond();}
			
			if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){//TODO - make the new checkout the default
				$output .= '<form action="'.WP_ESTORE_WP_SITE_URL.'?eStore_checkout=process" method="post">';
			}else{
            	$output .= '<form action="'.WP_ESTORE_URL.'/eStore_payment_submission.php" method="post">';
			}
            $checkout_button = WP_ESTORE_URL.'/images/checkout_paypal.png';

            if (get_option('eStore_use_multiple_gateways'))
            {
                $output .= ESTORE_PAYMENT_METHOD;
                $output .= '<select class="eStore_gateway" name="eStore_gateway">';
                if (get_option('eStore_use_paypal_gateway'))
               	{
               		if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "paypal"){
               			$output .= '<option value="paypal" selected="selected">'.ESTORE_PAYPAL.'</option>';
               			$checkout_button = WP_ESTORE_URL.'/images/checkout_paypal.png';
               		}
               		else{
                    	$output .= '<option value="paypal">'.ESTORE_PAYPAL.'</option>';
               		}
                }
                if (get_option('eStore_use_manual_gateway'))
                {
                	if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "manual"){
               			$output .= '<option value="manual" selected="selected">'.ESTORE_MANUAL.'</option>';
               			$checkout_button = WP_ESTORE_URL.'/images/checkout_manual.png';
               		}
               		else{                	
               	    	$output .= '<option value="manual">'.ESTORE_MANUAL.'</option>';
               		}
               	}
                if (get_option('eStore_use_2co_gateway'))
                {
                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "2co"){
               			$output .= '<option value="2co" selected="selected">'.ESTORE_TWO_CO.'</option>';
               			$checkout_button = WP_ESTORE_URL.'/images/checkout_2co.png';
               		}
               		else{                	
               	    	$output .= '<option value="2co">'.ESTORE_TWO_CO.'</option>';
               		}                	
               	}
               	if (get_option('eStore_use_authorize_gateway'))
               	{
                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "authorize"){
               			$output .= '<option value="authorize" selected="selected">'.ESTORE_AUTHORIZE.'</option>';
               			$checkout_button = WP_ESTORE_URL.'/images/checkout_authorize.gif';
               		}
               		else{                	
               	    	$output .= '<option value="authorize">'.ESTORE_AUTHORIZE.'</option>';
               		}                		
               	}     
               	//Add the payment gateway bundle checkout options    
               	if(defined('WP_PAYMENT_GATEWAY_BUNDLE_VERSION'))
               	{
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_gco_gateway'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "gco"){
	               			$output .= '<option value="gco" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_gco_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_gco.gif';
	               		}
	               		else{                	
	               	    	$output .= '<option value="gco">'.$wp_pg_bundle_config->getValue('wp_pg_gco_selector_label').'</option>';
	               		}                		
	               	}    
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_pppro_gateway'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "pppro"){
	               			$output .= '<option value="pppro" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_pppro_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_pppro.gif';
	               		}
	               		else{                	
	               	    	$output .= '<option value="pppro">'.$wp_pg_bundle_config->getValue('wp_pg_pppro_selector_label').'</option>';
	               		}                		
	               	}	
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_sagepay_gateway'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "sagepay"){
	               			$output .= '<option value="sagepay" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_sagepay_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_sagepay.gif';
	               		}
	               		else{                	
	               	    	$output .= '<option value="sagepay">'.$wp_pg_bundle_config->getValue('wp_pg_sagepay_selector_label').'</option>';
	               		}  	               		         		
	               	} 
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_auth_aim_gateway'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "auth_aim"){
	               			$output .= '<option value="auth_aim" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_auth_aim_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_auth_aim.gif';
	               		}
	               		else{                	
	               	    	$output .= '<option value="auth_aim">'.$wp_pg_bundle_config->getValue('wp_pg_auth_aim_selector_label').'</option>';
	               		}           		
	               	}
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_eway_gateway'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "eway"){
	               			$output .= '<option value="eway" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_eway_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_eway.gif';
	               		}
	               		else{                	
	               	    	$output .= '<option value="eway">'.$wp_pg_bundle_config->getValue('wp_pg_eway_selector_label').'</option>';
	               		}           		
	               	}
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_epay_dk_gateway'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "epay_dk"){
	               			$output .= '<option value="epay_dk" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_epay_dk_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_epay_dk.gif';
	               		}
	               		else{             	
	               	    	$output .= '<option value="epay_dk">'.$wp_pg_bundle_config->getValue('wp_pg_epay_dk_selector_label').'</option>';
	               		}           		
	               	}	
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_verotel_flexpay_gateway'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "verotel"){
	               			$output .= '<option value="verotel" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_verotel_flexpay_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_verotel.gif';
	               		}
	               		else{             	
	               	    	$output .= '<option value="verotel">'.$wp_pg_bundle_config->getValue('wp_pg_verotel_flexpay_selector_label').'</option>';
	               		}           		
	               	}	
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_freshbooks'))
	               	{
	                    if(isset($_COOKIE['eStore_gateway']) && $_COOKIE['eStore_gateway'] == "freshbooks"){
	               			$output .= '<option value="freshbooks" selected="selected">'.$wp_pg_bundle_config->getValue('wp_pg_freshbooks_selector_label').'</option>';
	               			$checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_freshbooks.gif';
	               		}
	               		else{             	
	               	    	$output .= '<option value="freshbooks">'.$wp_pg_bundle_config->getValue('wp_pg_freshbooks_selector_label').'</option>';
	               		}           		
	               	}	               	               	               			               			               		               	               	           		
               	}
               	$output = apply_filters('eStore_cart_checkout_option_mc_filter',$output);
                $output .= '</select><br />';
            }
            else//A single gateway is being used
            {
                if (get_option('eStore_use_paypal_gateway'))
                {
                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="paypal" />';
                    $checkout_button = WP_ESTORE_URL.'/images/checkout_paypal.png';
                }
                else if (get_option('eStore_use_manual_gateway'))
                {
                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="manual" />';
                    $checkout_button = WP_ESTORE_URL.'/images/checkout_manual.png';
                }
                else if (get_option('eStore_use_2co_gateway'))
                {
                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="2co" />';
                    $checkout_button = WP_ESTORE_URL.'/images/checkout_2co.png';
                }
                else if (get_option('eStore_use_authorize_gateway'))
               	{
                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="authorize" />';
                    $checkout_button = WP_ESTORE_URL.'/images/checkout_authorize.gif';               		
               	}                 	
               	else if(defined('WP_PAYMENT_GATEWAY_BUNDLE_VERSION')) //Add the payment gateway bundle checkout options    
               	{
               	    if ($wp_pg_bundle_config->getValue('wp_pg_use_gco_gateway'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="gco" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_gco.gif';               		
	               	}       
               	    else if ($wp_pg_bundle_config->getValue('wp_pg_use_pppro_gateway'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="pppro" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_pppro.gif';               		
	               	}  	
               	    else if ($wp_pg_bundle_config->getValue('wp_pg_use_sagepay_gateway'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="sagepay" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_sagepay.gif';               		
	               	} 
               	    else if ($wp_pg_bundle_config->getValue('wp_pg_use_auth_aim_gateway'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="auth_aim" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_auth_aim.gif';               		
	               	} 	  
               	    else if ($wp_pg_bundle_config->getValue('wp_pg_use_eway_gateway'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="eway" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_eway.gif';               		
	               	}
               	    else if ($wp_pg_bundle_config->getValue('wp_pg_use_epay_dk_gateway'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="epay_dk" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_epay_dk.gif';               		
	               	}	
               	    else if ($wp_pg_bundle_config->getValue('wp_pg_use_verotel_flexpay_gateway'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="verotel" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_verotel.gif';               		
	               	}
               	    else if ($wp_pg_bundle_config->getValue('wp_pg_use_freshbooks'))
	               	{
	                    $output .= '<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="freshbooks" />';
	                    $checkout_button = WP_PAYMENT_GATEWAY_BUNDLE_PLUGIN_URL.'/images/checkout_freshbooks.gif';               		
	               	}
               	}
                $output = apply_filters('eStore_cart_checkout_option_single_filter',$output);
            }
            $checkout_button = apply_filters('eStore_cart_checkout_button_img_url_filter',$checkout_button);
            //$output .= '<input type="hidden" name="eStore_url" id="eStore_url" value="'.WP_ESTORE_URL.'" />';

            if(get_option('eStore_enable_fancy_redirection_on_checkout'))
            {
            	if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
					$output .= '<a href="'.WP_ESTORE_WP_SITE_URL.'?eStore_checkout=process" class="redirect_trigger" rel="#overlay">';
				}else{
            		$output .= '<a href="'.WP_ESTORE_URL.'/eStore_payment_submission.php" class="redirect_trigger" rel="#overlay">';
				}       
	            $output .= '<input type="image" src="'.$checkout_button.'" name="submit" class="eStore_paypal_checkout_button" alt="Checkout" />';
	           	$output .= '</a>';		     	           	
            }
            else
            { 	 	
            	$output .= '<input type="image" src="'.$checkout_button.'" name="submit" class="eStore_paypal_checkout_button" alt="Checkout" />';
            }
            
            $output .= '</form>';
			$output .= '</div>';
			
			$output = apply_filters('eStore_cart_checkout_button_form_filter', $output);
			
			$below_cart_co_button = "";
			$below_cart_co_button = apply_filters('eStore_below_cart_checkout_filter', $below_cart_co_button);
			if(!empty($below_cart_co_button)){$output .= '<div class="eStore_below_cart_checkout">'.$below_cart_co_button.'</div>';}
			return $output;
}
