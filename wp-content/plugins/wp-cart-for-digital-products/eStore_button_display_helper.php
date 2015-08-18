<?php
function get_thumbnail_image_section_code($ret_product,$thumb_class="thumb-image")
{
	$output = "";
	if(get_option('eStore_enable_smart_thumb'))
	{
		$thumb_img = get_eStore_smart_thumb($ret_product->thumbnail_url);
	}
	else
	{
		$thumb_img = $ret_product->thumbnail_url;
	}	
			
	if(!empty($ret_product->target_thumb_url))
    {
        $output .= '<div class="eStore-thumbnail"><a href="'.$ret_product->target_thumb_url.'" title="'.$ret_product->name.'"><img class="'.$thumb_class.'" src="'.$thumb_img.'" alt="'.$ret_product->name.'" /></a></div>';
    }
    else
    {
        $output .= '<div class="eStore-thumbnail">';
        $output .= '<a href="'.$ret_product->thumbnail_url.'" rel="lightbox['.$ret_product->name.']" title="'.$ret_product->name.'"><img class="'.$thumb_class.'" src="'.$thumb_img.'" alt="'.$ret_product->name.'" /></a>';
        $output .= '</div>';
        if(!empty($ret_product->additional_images))
        {
        	$product_images = explode(',',$ret_product->additional_images);
        	foreach ($product_images as $image)
        	{
        		$output .= '<a href="'.trim($image).'" rel="lightbox['.$ret_product->name.']" title="'.$ret_product->name.'"></a>';
        	}
        }                
    }
    return $output;
}

function get_input_button($button_url)
{
	$replacement = "";
    if (!empty($button_url))
    {
        $replacement .= '<input type="image" src="'.$button_url.'" class="eStore_button eStore_add_to_cart_button" alt="Add to Cart" />';
    }
    else
    {
    	if (preg_match("/http/", WP_ESTORE_ADD_CART_BUTTON)) // Use the image as the 'add to cart' button
    	{
    		$replacement .= '<input type="image" src="'.WP_ESTORE_ADD_CART_BUTTON.'" class="eStore_button eStore_add_to_cart_button" alt="Add to Cart" />';
    	}
    	else
    	{
    		$replacement .= '<input type="submit" value="'.__(WP_ESTORE_ADD_CART_BUTTON).'" class="eStore_button eStore_add_to_cart_button" />';
    	}
    }
    return $replacement;
}

function eStore_get_sold_out_button()
{
	if (preg_match("/http/", WP_ESTORE_SOLD_OUT_IMAGE)){ //Use the image as the 'sold out' button
		$button_code = '<input type="image" src="'.WP_ESTORE_SOLD_OUT_IMAGE.'" class="eStore_sold_out" title="'.ESTORE_ITEM_SOLD_OUT.'" alt="'.ESTORE_SOLD_OUT.'"/>';
	}
	else{
		$button_code = '<input type="submit" value="'.__(WP_ESTORE_SOLD_OUT_IMAGE).'" class="eStore_sold_out eStore_sold_out_text_button" />';
	}
	return $button_code;
}

function eStore_get_buy_now_submit_input($button_img_url,$button_text='')
{
	$replacement = "";
    if (!empty($button_img_url)){
        $replacement .= '<input type="image" src="'.$button_img_url.'" class="eStore_button wppg-eStore-buy-button-submit eStore_buy_now_button" alt="'.WP_ESTORE_BUY_NOW.'" />';
    }
    else{
    	if(empty($button_text)){$button_text = WP_ESTORE_BUY_NOW;}
		$replacement .= '<input type="submit" value="'.__($button_text).'" class="eStore_button wppg-eStore-buy-button-submit eStore_buy_now_button" />';
    }
    return $replacement;
}

function get_variation_and_input_code($ret_product,$line_break=true,$button_type=1,$nggImage='')
{		
	$var_output = "";
	$variation_add_string = WP_ESTORE_VARIATION_ADD_SYMBOL;
	$curr_sign = WP_ESTORE_CURRENCY_SYMBOL;//get_option('cart_currency_symbol');
	$var_output .= '<div class="eStore_variation_top"></div>';
	if (!empty($ret_product->variation1))
	{
		$pieces = explode('|',$ret_product->variation1);
		$variation1_name = $pieces[0];
		//if ($line_break) $var_output .= '<br />';		
		if(!empty($variation1_name))
			$var_output .= '<span class="eStore_variation_name">'.$variation1_name.' : </span>';
		if($button_type == 1){
			$var_output .= '<select name="variation1" class="eStore_variation" onchange="ReadForm1 (this.form, 1);">';
		}
		else if($button_type == 2){
			$var_output .= '<select name="variation1" class="eStore_variation" onchange="ReadForm1 (this.form, 2);">';
		}
		else if($button_type == 3){
			$var_output .= '<select name="variation1" class="eStore_variation" onchange="ReadForm1 (this.form, 3);">';
		}		
		for ($i=1;$i<sizeof($pieces); $i++)
		{
			$pieces2 = explode(':',$pieces[$i]);
			if (sizeof($pieces2) > 1)				
				$tmp_txt = $pieces2[0].' ['.$variation_add_string.' '.print_digi_cart_payment_currency($pieces2[1],$curr_sign).']';	
				//$tmp_txt = $pieces2[0].' ['.$variation_add_string.' '.$curr_sign.$pieces2[1].']';
			else	
				$tmp_txt = $pieces2[0];
			$var_output .= '<option value="'.htmlspecialchars($tmp_txt).'">'.$tmp_txt.'</option>';
		}
		$var_output .= '</select>';
		if ($line_break) $var_output .= '<br />';	
		else $var_output .= ' ';		
	}

	if (!empty($ret_product->variation2))
	{
		$pieces = explode('|',$ret_product->variation2);
		$variation2_name = $pieces[0];
		if(!empty($variation2_name))
			$var_output .= '<span class="eStore_variation_name">'.$variation2_name.' : </span>';
		
		if($button_type == 1){
			$var_output .= '<select name="variation2" class="eStore_variation" onchange="ReadForm1 (this.form, 1);">';
		}
		else if($button_type == 2){
			$var_output .= '<select name="variation2" class="eStore_variation" onchange="ReadForm1 (this.form, 2);">';
		}
		else if($button_type == 3){
			$var_output .= '<select name="variation2" class="eStore_variation" onchange="ReadForm1 (this.form, 3);">';
		}		
		for ($i=1;$i<sizeof($pieces); $i++)
		{
			$pieces2 = explode(':',$pieces[$i]);
			if (sizeof($pieces2) > 1)
				$tmp_txt = $pieces2[0].' ['.$variation_add_string.' '.print_digi_cart_payment_currency($pieces2[1],$curr_sign).']';
			else	
				$tmp_txt = $pieces2[0];
				
			$var_output .= '<option value="'.htmlspecialchars($tmp_txt).'">'.$tmp_txt.'</option>';			
		}
		$var_output .= '</select>';
		if ($line_break) $var_output .= '<br />';
		else $var_output .= ' ';			
	}
	if (!empty($ret_product->variation4))
	{
		$pieces = explode('|',$ret_product->variation4);
		$variation4_name = $pieces[0];
		if(!empty($variation4_name))
			$var_output .= '<span class="eStore_variation_name">'.$variation4_name.' : </span>';
		
		if($button_type == 1){
			$var_output .= '<select name="variation4" class="eStore_variation" onchange="ReadForm1 (this.form, 1);">';
		}
		else if($button_type == 2){
			$var_output .= '<select name="variation4" class="eStore_variation" onchange="ReadForm1 (this.form, 2);">';
		}
		else if($button_type == 3){
			$var_output .= '<select name="variation4" class="eStore_variation" onchange="ReadForm1 (this.form, 3);">';
		}		
		for ($i=1;$i<sizeof($pieces); $i++)
		{
			$pieces2 = explode(':',$pieces[$i]);
			if (sizeof($pieces2) > 1)
				$tmp_txt = $pieces2[0].' ['.$variation_add_string.' '.print_digi_cart_payment_currency($pieces2[1],$curr_sign).']';
			else	
				$tmp_txt = $pieces2[0];
				
			$var_output .= '<option value="'.htmlspecialchars($tmp_txt).'">'.$tmp_txt.'</option>';			
		}
		$var_output .= '</select>';
		if ($line_break) $var_output .= '<br />';
		else $var_output .= ' ';			
	}
	if (!empty($ret_product->variation3))
	{
		$pieces = explode('|',$ret_product->variation3);
		$variation3_name = $pieces[0];
		if(!empty($variation3_name))
			$var_output .= '<span class="eStore_variation_name">'.$variation3_name.' : </span>';
		
		if($button_type == 1){
			$var_output .= '<select name="variation3" class="eStore_variation" onchange="ReadForm1 (this.form, 1);">';
		}
		else if($button_type == 2){
			$var_output .= '<select name="variation3" class="eStore_variation" onchange="ReadForm1 (this.form, 2);">';
		}
		else if($button_type == 3){
			$var_output .= '<select name="variation3" class="eStore_variation" onchange="ReadForm1 (this.form, 3);">';
		}			
		for ($i=1;$i<sizeof($pieces); $i++)
		{
			$pieces2 = explode('::',$pieces[$i]);
			if (sizeof($pieces2) > 1)
			{
				if (is_numeric($pieces2[1]))
					$tmp_txt = $pieces2[0].' ['.$variation_add_string.' '.print_digi_cart_payment_currency($pieces2[1],$curr_sign).']';
				else
					$tmp_txt = $pieces2[0];
			}
			else	
				$tmp_txt = $pieces2[0];
				
			$var_output .= '<option value="'.htmlspecialchars($tmp_txt).'">'.$tmp_txt.'</option>';
		}
		$var_output .= '</select>';
		if ($line_break) $var_output .= '<br />';
		else $var_output .= ' ';
	}
	if($ret_product->custom_input == '1')
    {
        if(!empty($ret_product->custom_input_label))
            $var_output .= '<span class="eStore_custom_input_name">'.$ret_product->custom_input_label.': </span><input type="text" name="custom_input" value="" class="eStore_text_input eStore_collect_input" />';
        else
            $var_output .= '<span class="eStore_custom_input_name">Instructions: </span><input type="text" name="custom_input" value="" class="eStore_text_input eStore_collect_input" />';
  		if ($line_break) $var_output .= '<br />';
		else $var_output .= ' ';
    }
    if(!empty($nggImage->pid))
    {
    	$var_output .= '<input type="text" name="eStore_ngg_pid" value="[pid:'.$nggImage->pid.']" class="eStore_hidden_textfield" />';
    }
    return $var_output;
}

function eStore_get_default_purchase_qty_input_data($name='add_qty',$value='1')
{
    $value = apply_filters('eStore_filter_default_purchase_qty_value', $value);
    $data = '<span class="eStore_item_default_qty_data">';
    $data .= '<span class="eStore_item_default_qty_data_label">'.ESTORE_QUANTITY.': </span><span class="eStore_item_default_qty_data_input"><input type="text" name="'.$name.'" size="2" value="'.$value.'" />&nbsp;</span>';
    $data .= '</span>';
    return $data;
}

function eStore_get_gateway_specific_buy_now_button_code($id,$gateway='paypal',$button_text='',$line_break=true,$nggImage='',$buttonImage='')
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$id = strip_tags($id);
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if(!$ret_product){
		return eStore_wrong_product_id_error_msg($id);
	}

	$replacement = "";
	if (is_numeric ($ret_product->available_copies)){
		if ($ret_product->available_copies < 1){// No more copies left
			return eStore_get_sold_out_button();
		}
	}
	if (!empty($ret_product->target_button_url)){
        $replacement = '<form method="post" action="'.$ret_product->target_button_url.'">';
        $replacement .= eStore_get_buy_now_submit_input($ret_product->button_image_url);
        $replacement .= '</form>';
        return $replacement;
    }
    
    if(isset($_SESSION['eStore_gs_bn_co_error_msg']) && $_REQUEST['item_number'] == $id){
    	$replacement .= $_SESSION['eStore_gs_bn_co_error_msg'];
    }
    
	//$replacement .= '<object class="eStore_button_object">';
	$replacement .= '<form method="post" class="wppg-eStore-buy-button-form" action="" style="display:inline" onsubmit="return ReadForm1(this, 1);">';
	
    $var_output = get_variation_and_input_code($ret_product,$line_break,1,$nggImage);
    if (!empty($var_output)){
         $replacement .= $var_output;
    }

	//If custom price option is set
	if($ret_product->custom_price_option == '1'){
		$currSymbol = WP_ESTORE_CURRENCY_SYMBOL;//get_option('cart_currency_symbol');
		$replacement .= '<div class="wppg-eStore-buy-button-custom-price">'.WP_ESTORE_YOUR_PRICE.': '.$currSymbol.'<input type="text" name="custom_price" size="3" value="" /></div>';
	}
		    
    if($ret_product->show_qty=='1'){
        $replacement .= '<div class="wppg-eStore-buy-button-qty">'.eStore_get_default_purchase_qty_input_data().'</div>';			
    }
    else{
    	$replacement .= '<div class="wppg-eStore-buy-button-qty"><input type="hidden" name="add_qty" value="1" /></div>';
    }

	if(!empty($nggImage->alttext))
	{			
		$replacement .= '<input type="hidden" name="product" value="'.htmlspecialchars($nggImage->alttext).'" /><input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($nggImage->alttext).'" />';
	}
	else
	{
    	$replacement .= '<input type="hidden" name="product" value="'.htmlspecialchars($ret_product->name).'" /><input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($ret_product->name).'" />';
	}
	
	if(!empty($nggImage->thumbURL)){//$nggImage->imageURL
		$replacement .= '<input type="hidden" name="thumbnail_url" value="'.$nggImage->thumbURL.'" />';
	}
	else{
		$replacement .= '<input type="hidden" name="thumbnail_url" value="'.$ret_product->thumbnail_url.'" />';
	}	
	$replacement .= '<input type="hidden" name="price" value="'.$ret_product->price.'" /><input type="hidden" name="price_tmp1" value="'.$ret_product->price.'" />';
	$replacement .= '<input type="hidden" name="item_number" value="'.$ret_product->id.'" /><input type="hidden" name="shipping" value="'.$ret_product->shipping_cost.'" /><input type="hidden" name="tax" value="'.$ret_product->tax.'" />';
	if(!empty($ret_product->product_url)){
		$replacement .= '<input type="hidden" name="cartLink" value="'.$ret_product->product_url.'" />';
	}
	else{
		$replacement .= '<input type="hidden" name="cartLink" value="'.digi_cart_current_page_url().'" />';
	}
	if(wp_eStore_is_digital_product($ret_product)){//flag this as a digital product			
            $replacement .= '<input type="hidden" name="digital_flag" value="1" />';
	}
        if(!empty($ret_product->currency_code)){
            $replacement .= '<input type="hidden" name="eStore_buy_now_currency_override" value="'.$ret_product->currency_code.'" />';
        }
        
	$replacement .= '<input type="hidden" name="eStore_gsbn_gateway" value="'.$gateway.'" />';
	$replacement .= '<input type="hidden" name="eStore_gs_buy_now_submit" value="1" />';
	
        if (get_option('eStore_show_t_c_for_buy_now')){
            $replacement .= eStore_show_terms_and_cond();
        }

	if(empty($buttonImage)){$buttonImage = $ret_product->button_image_url;}
	if(!empty($buttonImage)){
    	$replacement .= eStore_get_buy_now_submit_input($buttonImage);
    }
    else {
    	$replacement .= eStore_get_buy_now_submit_input("",$button_text);
    }
	$replacement .= '</form>';
	//$replacement .= '</object>';
	return $replacement;
}

function eStore_gateway_specific_buy_now_submit_listener(){
	if (isset($_REQUEST['eStore_gs_buy_now_submit']))
	{
		$wp_eStore_config = WP_eStore_Config::getInstance();
		$cookie_domain = eStore_get_top_level_domain();	  	
	    setcookie("cart_in_use","true",time()+21600,"/",$cookie_domain); 
	    if (function_exists('wp_cache_serve_cache_file')){//WP Super cache workaround
	    	setcookie("comment_author_","eStore",time()+21600,"/",$cookie_domain);
	    }
	    
	    unset($_SESSION['eStore_last_action_msg']);
		unset($_SESSION['eStore_last_action_msg_2']);
		unset($_SESSION['eStore_last_action_msg_3']);    
	    unset($_SESSION['eStore_gs_bn_co_error_msg']);
	    if(isset($_SESSION['eStore_cart'])){unset($_SESSION['eStore_cart']);}
	    
		//sanitize data
		$_REQUEST['product'] = strip_tags($_REQUEST['product']);//for PHP5.2 use filter_var($_REQUEST['product'], FILTER_SANITIZE_STRING);
	    $_REQUEST['add_qty'] = strip_tags($_REQUEST['add_qty']);
		$_REQUEST['item_number'] = strip_tags($_REQUEST['item_number']);
		if(isset($_REQUEST['custom_price']))$_REQUEST['custom_price'] = strip_tags($_REQUEST['custom_price']);
		if(isset($_REQUEST['price']))$_REQUEST['price'] = strip_tags($_REQUEST['price']);
		isset($_REQUEST['shipping'])?$_REQUEST['shipping'] = strip_tags($_REQUEST['shipping']):$_REQUEST['shipping']='';
		isset($_REQUEST['cartLink'])?$_REQUEST['cartLink'] = strip_tags($_REQUEST['cartLink']):$_REQUEST['cartLink']='';
		isset($_REQUEST['thumbnail_url'])?$_REQUEST['thumbnail_url'] = strip_tags($_REQUEST['thumbnail_url']):$_REQUEST['thumbnail_url']='';	
		isset($_REQUEST['tax'])?$_REQUEST['tax'] = strip_tags($_REQUEST['tax']):$_REQUEST['tax']='';
		if(isset($_REQUEST['digital_flag'])){$_REQUEST['digital_flag'] = strip_tags($_REQUEST['digital_flag']);}else{$_REQUEST['digital_flag']='';}
		$gateway = strip_tags($_REQUEST['eStore_gsbn_gateway']);
	
		$products = array();
		$eStore_gs_buy_now_checkout_error = false;
		$count = 1;
		
	    if ($count == 1)
	    {    	
	    	$item_addittion_permitted = true;
	    	$quantity_available = is_quantity_availabe($_REQUEST['item_number'],$_REQUEST['add_qty']);
	    	if (!$quantity_available){
	    		//Requested qty not available
				$_REQUEST['add_qty'] = 1; //Add one by default
				$eStore_gs_buy_now_checkout_error = true;
	    	}
	    	if(isset($_SESSION['eStore_last_action_msg'])){
	    		$_SESSION['eStore_gs_bn_co_error_msg'] = $_SESSION['eStore_last_action_msg'];
	    	}
	    	
	    	if($item_addittion_permitted)
	    	{
		        if (!empty($_REQUEST[$_REQUEST['product']])){
		            $price = $_REQUEST[$_REQUEST['product']];
		        }
		        else if (isset($_REQUEST['custom_price'])){
		        	global$wpdb;
		           	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
		           	$id = $_REQUEST['item_number'];
		        	$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
		        	if($_REQUEST['custom_price'] < $retrieved_product->price){
		        		$price = $retrieved_product->price;
		        		$currSymbol = get_option('cart_currency_symbol');
		        		$eStore_gs_buy_now_checkout_error = true;
		        		$_SESSION['eStore_gs_bn_co_error_msg'] = '<p style="color: red;">'.WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER.$currSymbol.$retrieved_product->price.'</p>';
		        	}
		        	else{
		    			$price = $_REQUEST['custom_price'];
		        	}
		    	}
		    	else{
		    		$price = $_REQUEST['price'];
		    	}
		        $product = array('name' => stripslashes($_REQUEST['product']), 'price' => $price, 'quantity' => $_REQUEST['add_qty'], 'shipping' => $_REQUEST['shipping'], 'item_number' => $_REQUEST['item_number'], 'cartLink' => $_REQUEST['cartLink'], 'thumbnail_url' => $_REQUEST['thumbnail_url'],'tax' => $_REQUEST['tax'],'digital_flag' => $_REQUEST['digital_flag']);
		        array_push($products, $product);
	    	}
	    }
	    
            if(!$eStore_gs_buy_now_checkout_error)
            {
                $_SESSION['eStore_cart'] = $products;
                $_SESSION['eStore_url'] = WP_ESTORE_URL;
                $_SESSION['eStore_cart_sub_total'] = eStore_get_cart_total();
                $_SESSION['eStore_cart_postage_cost'] = eStore_get_cart_shipping();
                $_SESSION['eStore_cart_total_tax'] = eStore_calculate_total_cart_tax();
                if(isset($_REQUEST['eStore_buy_now_currency_override']))
                {
                    $buy_now_currency = strip_tags($_REQUEST['eStore_buy_now_currency_override']);
                    $_SESSION['eStore_buy_now_currency_override'] = $buy_now_currency;
                }
                wp_eStore_check_cookie_flag_and_store_values();
                if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
                    $redirect_page = WP_ESTORE_WP_SITE_URL.'/?eStore_checkout=process&eStore_gateway='.$gateway;
                }else{
                    $redirect_page = WP_ESTORE_URL.'/eStore_payment_submission.php?eStore_gateway='.$gateway;
                }
                eStore_redirect_to_url($redirect_page);
            }
	}
}
