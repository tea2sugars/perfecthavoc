<?php
include_once('eStore_classes.php');
include_once('eStore_includes.php');
include_once('eStore_button_display_helper.php');

function get_eStore_price_for_product($id)
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if(!$ret_product){		
		return eStore_wrong_product_id_error_msg($id);
	}
	return $ret_product->price;
}

function get_eStore_desc_for_product($id)
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if(!$ret_product){		
		return eStore_wrong_product_id_error_msg($id);
	}
	return html_entity_decode($ret_product->description, ENT_COMPAT,"UTF-8");
}

function eStore_wrong_product_id_error_msg($id)
{
	$output = "<div style='color:red;'>Looks like you have entered a product ID (".$id.") that doesn't exist in the product database. Please check your product ID value again!</div><br />";
	return $output;	
}

function eStore_free_download_form($id,$post_url='',$success_msg='',$button_text='') {
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	$button_image = $ret_product->button_image_url;
	if(empty($button_image)){
		$button_image = WP_ESTORE_URL."/images/download_icon.png";
	}
	if (is_numeric($ret_product->available_copies))	{
		if ($ret_product->available_copies < 1){// No more copies left
			$output = WP_ESTORE_NO_COPIES_LEFT;
			return $output;
		}
	}
		
	$form_unique_identifier = "eStore_free_download".$id;
	$form_unique_identifier2 = eStore_get_static_count();
	if($form_unique_identifier2 === 0){
		unset($_SESSION['eStore_squeeze_form_processed']);
	}
		
	$output = "";
	$email_sent = false;
	$use_recaptcha = false;
	$resp = "";
	if(get_option('wp_eStore_use_recaptcha')){
		$use_recaptcha = true;
		$publickey = get_option('wp_eStore_captcha_public_key');
		$privatekey = get_option('wp_eStore_captcha_private_key');
	}
	
	if (isset($_POST[$form_unique_identifier])) 
	{
		// Submit button was clicked...
                $process_form = true;
		if($use_recaptcha)
		{			
			if (!function_exists('_recaptcha_qsencode')){require_once('lib/recaptchalib.php');}
			$resp =	recaptcha_check_answer ($privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]);

		   	if (!$resp->is_valid) {
				$output .= '<p class="eStore_error_message">'.WP_ESTORE_IMAGE_VERIFICATION_FAILED.'</p>';
                                $process_form = false;
			}
		}
		if($process_form)
		{
			if(empty($success_msg)){$success_msg = WP_ESTORE_EMAIL_SENT;}
			if (empty($_POST['cust_name']) || empty($_POST['cust_email'])) {
				$output .= WP_ESTORE_NAME_OR_EMAIL_MISSING;
			}
			else if ( !is_email($_POST['cust_email']) ){
				$output .= '<p class="eStore_error_message">'.WP_ESTORE_EMAIL_INVALID.'</p>';
			}
			else if ($_SESSION['eStore_squeeze_form_processed'] !== "Processed"){
				if(!empty($post_url)){
					$postURL = $post_url;
				}else{
					$postURL = WP_ESTORE_URL."/ajax_process_download.php";
				}			
				// prepare the data
				$data = array ();
				$data['name'] = strip_tags($_POST['cust_name']);
				$data['email'] = strip_tags($_POST['cust_email']);
				$data['prod_id'] = strip_tags($_POST['free_download_product_id']);
				$data['ap_id'] = strip_tags($_POST['free_download_ap_id']);	
				$data['clientip'] = strip_tags($_POST['free_download_clientip']);	
				
				//Process the squeeze form submission
				if(empty($post_url)){//This is not a custom http post so process it internally
					include_once('eStore_squeeze_form_functions.php');
					eStore_process_squeeze_form_submission($data['name'],$data['email'],$data['prod_id'],$data['ap_id'],$data['clientip']);
					$retVal = "Success";
				}else{
					$retVal = eStore_post_data_using_wp_remote_post($postURL,$data);
				}
				
				if($retVal == "Success") {
					$output .= $success_msg;//WP_ESTORE_EMAIL_SENT;					
				} 
				else {
					$output .= 'Could not POST the squeeze form processing request to the server!';
				}
				$email_sent = true;
				$_SESSION['eStore_squeeze_form_processed'] = "Processed";
			}
			else{
				//Squeeze form for this product has already been processed on this page. No need to process multiple of them.
				$output .= $success_msg;//WP_ESTORE_EMAIL_SENT;
				$email_sent = true;
			}
			if($email_sent && !empty($ret_product->return_url)){//Post form submit redirect to page
				eStore_redirect_to_url($ret_product->return_url);
			}
		}
	}
	if(!$email_sent) {
		// E-mail has not yet been sent.  Output should be the input form.
		isset($_COOKIE['ap_id'])? $cookie_value = $_COOKIE['ap_id'] : $cookie_value = '';
		
		$output .= '<div class="free_download_form_old">';
		$output .= '<form method="post"  action=""  style="display:inline">';
		$output .= '<div class="eStore_sf_name_label eStore_sf_element">'.WP_ESTORE_NAME.': </div>';
		$output .= '<div class="eStore_sf_name_field eStore_sf_element"><input name="cust_name" type="text" class="eStore_text_input" /></div>';
		$output .= '<div class="eStore_sf_email_label eStore_sf_element">'.ESTORE_EMAIL.': </div>';
		$output .= '<div class="eStore_sf_email_field eStore_sf_element"><input name="cust_email" type="text" class="eStore_text_input" /></div>';
		
		$args = array('id' => $id);
		$output = apply_filters('eStore_squeeze_form_below_email_filter', $output, $args);
		
		if (get_option('eStore_show_t_c_for_squeeze_form')){
			$output .= eStore_show_terms_and_cond();
		}
		$output .= '<input type="hidden" name="eStore_free_download" value="1" />';
		$output .= '<input type="hidden" name="'.$form_unique_identifier.'" value="1" />';
		$output .= '<input type="hidden" name="free_download_product_id" value="'.base64_encode($id).'" />';
		$output .= '<input type="hidden" name="free_download_ap_id" id="free_download_ap_id" value="'.$cookie_value.'" />';
		$output .= '<input type="hidden" name="free_download_clientip" id="free_download_clientip" value="'.$_SERVER['REMOTE_ADDR'].'" />';
		if($use_recaptcha) {// Show the Re-Captcha challenge...
			if (!function_exists('_recaptcha_qsencode')) require_once('lib/recaptchalib.php');
	        	$output .= recaptcha_get_html($publickey)."<br />";
		}
		
		if(empty($button_text)){//render the download button using image
			$output .= '<input type="image" name="submit" class="free_download_submit" alt="'.ESTORE_DOWNLOAD_TEXT.'" src="'.$button_image.'" />';
		}else{//render the download button using the specified text
			$output .= '<input type="submit" name="submit" class="free_download_submit" value="'.__($button_text).'" />';//ESTORE_DOWNLOAD_TEXT
		}
		$output .= '</form>';
		$output .= '</div>';
		$output .= '<div class="eStore-clear-float"></div>';
	}
	return $output;
}

function eStore_free_download_form_ajax($id)
{
    eStore_load_free_download_ajax();

	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	$button_image = $ret_product->button_image_url;
	if(empty($button_image))
	{
		$button_image = WP_ESTORE_URL."/images/download_icon.png";
	}
	
	if (is_numeric($ret_product->available_copies))
	{
		if ($ret_product->available_copies < 1)// No more copies left
		{
			$output = WP_ESTORE_NO_COPIES_LEFT;
			return $output;
		}
	}
		
    $output .= '<div class="free_download_form">';
	$output .= '<form name="free_download" method="post" action="">';
    $output .= '<label for="name" id="name_label">'.WP_ESTORE_NAME.': </label><br />
      <input type="text" name="name" id="name" value="" class="eStore_text_input" />
      <br /><label class="error" for="name" id="name_error">'.WP_ESTORE_REQUIRED_FIELD.'<br /></label>
      <label for="eStore_ajax_email" id="email_label">'.ESTORE_EMAIL.': </label><br />
      <input type="text" name="eStore_ajax_email" id="eStore_ajax_email" value="" class="eStore_text_input" />
      <br /><label class="error" for="eStore_ajax_email" id="email_error">'.WP_ESTORE_REQUIRED_FIELD.'<br /></label>';
	if (get_option('eStore_show_t_c_for_squeeze_form')) 
	{
		$output .= eStore_show_terms_and_cond();  
      	$output .= '<input type="hidden" name="ajax_force_t_c" id="ajax_force_t_c" value="1" />';
	} 
	else
	{
    	$output .= '<input type="hidden" name="ajax_force_t_c" id="ajax_force_t_c" value="0" />';
	}
    $output .= '<input type="hidden" name="free_download_product_id" id="free_download_product_id" value="'.base64_encode($id).'" />
      <input type="hidden" name="free_download_ap_id" id="free_download_ap_id" value="'.$_COOKIE['ap_id'].'" />
      <input type="hidden" name="free_download_clientip" id="free_download_clientip" value="'.$_SERVER['REMOTE_ADDR'].'" />
      <input type="image" name="submit" class="button" id="submit_btn" alt ="'.ESTORE_DOWNLOAD_TEXT.'" src="'.$button_image.'" />';
    $output .=  '</form>';
	$output .= '</div>';

	return $output;
}

function get_button_code_for_product($id,$buttonImage='')
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$id = strip_tags($id);
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if($ret_product){
    	$output = get_button_code_for_element($ret_product,true,'',$buttonImage);
	}
	else{
		$output = eStore_wrong_product_id_error_msg($id);
	}
    return $output;
}

function get_button_code_for_element($ret_product,$line_break=true,$nggImage='',$buttonImage='')
{
	$replacement = "";
	if($ret_product)
	{
            if (is_numeric ($ret_product->available_copies))
            {
                if ($ret_product->available_copies < 1)// No more copies left
                {
                    return eStore_get_sold_out_button();
                }
            }
            if (!empty($ret_product->target_button_url))
            {
	        $replacement = '<form method="post" action="'.$ret_product->target_button_url.'">';
	        $replacement .= get_input_button($ret_product->button_image_url);
	        $replacement .= '</form>';
	        return $replacement;
	    }
            
            $replacement .= '<form method="post" action="" class="eStore-button-form" style="display:inline" onsubmit="return ReadForm1(this, 1);">';
		
	    $var_output = get_variation_and_input_code($ret_product,$line_break,1,$nggImage);
	    if (!empty($var_output))
	    {
	         $replacement .= $var_output;
	    }
	
            //If custom price option is set
            if($ret_product->custom_price_option=='1')
            {
                $currSymbol = get_option('cart_currency_symbol');
                $currSymbol = apply_filters('eStore_change_curr_symbol_filter', $currSymbol);
                $replacement .= WP_ESTORE_YOUR_PRICE.': '.$currSymbol.'<input type="text" name="custom_price" size="3" value="" />&nbsp;';
                if ($line_break) $replacement .= '<br />';
            }
			    
	    if($ret_product->show_qty=='1')
	    {
                $replacement .= eStore_get_default_purchase_qty_input_data();
                if ($line_break) $replacement .= '<br />';
	    }
	    else
	    {
	    	$replacement .= '<input type="hidden" name="add_qty" value="1" />';
	    }    
		    
	    if(!empty($buttonImage)){
	    	$replacement .= get_input_button($buttonImage);
	    }
	    else{
	    	$replacement .= get_input_button($ret_product->button_image_url);
	    }
	    
            if(!empty($nggImage->alttext))
            {			
                $replacement .= '<input type="hidden" name="estore_product_name" value="'.htmlspecialchars($nggImage->alttext).'" /><input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($nggImage->alttext).'" />';
            }
            else
            {
                $replacement .= '<input type="hidden" name="estore_product_name" value="'.htmlspecialchars($ret_product->name).'" /><input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($ret_product->name).'" />';
            }	
            if(!empty($nggImage->thumbURL)){//$nggImage->imageURL
                    $replacement .= '<input type="hidden" name="thumbnail_url" value="'.$nggImage->thumbURL.'" />';
            }
            else{
                    $replacement .= '<input type="hidden" name="thumbnail_url" value="'.$ret_product->thumbnail_url.'" />';
            }	
            $replacement .= '<input type="hidden" name="price" value="'.$ret_product->price.'" /><input type="hidden" name="price_tmp1" value="'.$ret_product->price.'" />';
            if(!empty($ret_product->old_price)){
                $replacement .= '<input type="hidden" name="price_tmp1_old" value="'.$ret_product->old_price.'" />';
            }
            $replacement .= '<input type="hidden" name="item_number" value="'.$ret_product->id.'" /><input type="hidden" name="shipping" value="'.$ret_product->shipping_cost.'" /><input type="hidden" name="tax" value="'.$ret_product->tax.'" /><input type="hidden" name="addcart_eStore" value="1" />';
            if(!empty($ret_product->product_url)){
                    $replacement .= '<input type="hidden" name="cartLink" value="'.$ret_product->product_url.'" />';
            }
            else{
                    $replacement .= '<input type="hidden" name="cartLink" value="'.digi_cart_current_page_url().'" />';
            }		
            if(wp_eStore_is_digital_product($ret_product)){//flag this as a digital product			
                    $replacement .= '<input type="hidden" name="digital_flag" value="1" />';
            }
            $replacement .= '</form>';
            //$replacement .= '</object>';
            $replacement .= '<div class="eStore_item_added_msg eStore_item_added_msg-'.$ret_product->id.'"></div>';
            return $replacement;
	}
	else //could not retrieve product from database
	{		
            return eStore_wrong_product_id_error_msg($ret_product->id);
	}
}

function get_button_code_fancy2_for_element($ret_product,$line_break=true)
{
	if($ret_product)
	{
		$replacement = "";
		if (is_numeric ($ret_product->available_copies))
		{
			if ($ret_product->available_copies < 1)// No more copies left
			{
				return eStore_get_sold_out_button();
			}
		}
		if (!empty($ret_product->target_button_url))
		{
	        $replacement = '<form method="post" action="'.$ret_product->target_button_url.'" style="display:inline">';
	        $replacement .= get_input_button($ret_product->button_image_url);
	        $replacement .= " ";
	        $replacement .= '</form>';
	        return $replacement;
	    }
	
            $replacement .= '<form method="post" action="" class="eStore-button-form" style="display:inline" onsubmit="return ReadForm1(this, 1);">';
	    
	    $replacement .= get_input_button($ret_product->button_image_url);
	    $replacement .= " ";
	
            //If custom price option is set
            if($ret_product->custom_price_option=='1')
            {
                    $replacement .= WP_ESTORE_YOUR_PRICE.': <input type="text" name="custom_price" size="3" value="" />&nbsp;';
                    if ($line_break) $replacement .= '<br />';
            }    
	    if($ret_product->show_qty=='1')
	    {
			$replacement .= eStore_get_default_purchase_qty_input_data();
			if ($line_break) $replacement .= '<br />';
	    }
	    else
	    {
	    	$replacement .= '<input type="hidden" name="add_qty" value="1" />';
	    }  
	        
	    $var_output = get_variation_and_input_code($ret_product,$line_break);
	    if (!empty($var_output))
	    {
	    	$replacement .= "  ";
	        $replacement .= $var_output;
	    }    
	    $replacement .= '<input type="hidden" name="thumbnail_url" value="'.$ret_product->thumbnail_url.'" />';
		$replacement .= '<input type="hidden" name="estore_product_name" value="'.htmlspecialchars($ret_product->name).'" /><input type="hidden" name="price" value="'.$ret_product->price.'" />';
		$replacement .= '<input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($ret_product->name).'" /><input type="hidden" name="price_tmp1" value="'.$ret_product->price.'" />';
                if(!empty($ret_product->old_price)){
                    $replacement .= '<input type="hidden" name="price_tmp1_old" value="'.$ret_product->old_price.'" />';
                }
		$replacement .= '<input type="hidden" name="item_number" value="'.$ret_product->id.'" /><input type="hidden" name="shipping" value="'.$ret_product->shipping_cost.'" /><input type="hidden" name="tax" value="'.$ret_product->tax.'" /><input type="hidden" name="addcart_eStore" value="1" />';
		if(!empty($ret_product->product_url)){
			$replacement .= '<input type="hidden" name="cartLink" value="'.$ret_product->product_url.'" />';
		}
		else{
			$replacement .= '<input type="hidden" name="cartLink" value="'.digi_cart_current_page_url().'" />';
		}			
		if(wp_eStore_is_digital_product($ret_product)){//flag this as a digital product			
			$replacement .= '<input type="hidden" name="digital_flag" value="1" />';
		}		
		$replacement .= '</form>';
		//$replacement .= '</object>';
		return $replacement;
	}
	else //could not retrieve product from database
	{		
		return eStore_wrong_product_id_error_msg($ret_product->id);
	}	
}

function print_eStore_ngg_add_to_cart($image,$product_id='')
{
	$output = "";
	if(empty($product_id))
	{
		//try to get it from the settings
		$product_id = get_option('eStore_ngg_template_product_id');
	}
	if(empty($product_id))//we have no way of rendering a button
	{
		$output .= "You need to specify a product ID in the 3rd Party Settings of eStore";
	}
	else
	{
		global $wpdb;
		$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
		$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$product_id'", OBJECT);		
		
		$output .= get_button_code_for_element($ret_product,true,$image);
	}
	return $output;
}

function print_eStore_ngg_buy_now($image,$product_id='')
{
	$output = "";
	if(empty($product_id))
	{
		//try to get it from the settings
		$product_id = get_option('eStore_ngg_template_product_id');
	}
	if(empty($product_id))//we have no way of rendering a button
	{
		$output .= "You need to specify a product ID in the 3rd Party Settings of eStore";
	}
	else
	{
		$output .= print_eStore_buy_now_button($product_id,'',$image);
	}
	return $output;
}
function print_eStore_buy_now_button($id,$button='',$nggImage='')
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    global $wpdb;
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
    if(!$ret_product){		
        return eStore_wrong_product_id_error_msg($id);
    }

    if (is_numeric ($ret_product->available_copies)){
        if ($ret_product->available_copies < 1){// No more copies left
            return eStore_get_sold_out_button();
        }
    }
    $paypal_email = get_option('cart_paypal_email');
	
    if(!empty($ret_product->currency_code))
        $paypal_currency = $ret_product->currency_code;
    else
        $paypal_currency = get_option('cart_payment_currency');
	
    $return = get_option('cart_return_from_paypal_url');

    // Find out if the product should be delivered automatically through a notify email
    if (get_option('eStore_auto_product_delivery') == ''){
            $notify = '';
    }
    else{
        if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
                $notify = WP_ESTORE_WP_SITE_URL.'/?estore_pp_ipn=process';
        }else{
                $notify = WP_ESTORE_URL.'/paypal.php';
        }
    }

    if (!empty($notify))
        $urls .= '<input type="hidden" name="notify_url" value="'.$notify.'" />';

    if (!empty($ret_product->return_url))
    {
        $urls .= '<input type="hidden" name="return" value="'.$ret_product->return_url.'" />';
    }
    else
    {
    if (!empty($return))
        $urls .= '<input type="hidden" name="return" value="'.$return.'" />';
    }
    $cancel_url =  get_option('cart_cancel_from_paypal_url');
    if(!empty($cancel_url))
    {
        $urls .= '<input type="hidden" name="cancel_return" value="'.$cancel_url.'" />';
    }
		
    if (empty($button))
    {
	$button = $ret_product->button_image_url;
    }
    if (!empty($button))
    {
        $button_type .= '<input type="image" src="'.$button.'" class="eStore_buy_now_button" alt="'.WP_ESTORE_BUY_NOW.'"/>';
    }
    else
    {
        $button_type .= '<input type="submit" class="eStore_buy_now_button" value="'.WP_ESTORE_BUY_NOW.'" />';
    }
    $sandbox_enabled = get_option('eStore_cart_enable_sandbox');

    /* === PayPal Buy Now Button Form === */
    $output = "";
    $output .= '<div class="eStore_button_wrapper eStore_pp_buy_now_wrapper">';
    if($sandbox_enabled){
        $output .= '<form action="'.PAYPAL_SANDBOX_URL.'" method="post" onsubmit="return ReadForm1(this, 2);">';
    }
    else{
        $output .= '<form action="'.PAYPAL_LIVE_URL.'" method="post" onsubmit="return ReadForm1(this, 2);">';
    }		    
	
    $line_break = true;
    
    //Variation code
    $output .= get_variation_and_input_code($ret_product,$line_break,2);
    
    //Custom price
    if($ret_product->custom_price_option=='1')
    {
        $currSymbol = get_option('cart_currency_symbol');
        $currSymbol = apply_filters('eStore_change_curr_symbol_filter', $currSymbol);
        $output .= WP_ESTORE_YOUR_PRICE.': '.$currSymbol.'<input type="text" name="custom_price" size="3" value="" />&nbsp;';
        if ($line_break) $output .= '<br />';
    }
	
    //Show qty field if set in the product
    if($ret_product->show_qty=='1')
    {
        $output .= eStore_get_default_purchase_qty_input_data("quantity","1");
        if ($line_break) $output .= '<br />';
    }		
	    
    if(!empty($nggImage->alttext))
    {
        $price_tmp1 = apply_filters('eStore_change_price_before_payment_filter', $ret_product->price);
    	$output .= '<input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($nggImage->alttext).'" /><input type="hidden" name="price_tmp1" value="'.$price_tmp1.'" />';
    }
    else
    {
        $price_tmp1 = apply_filters('eStore_change_price_before_payment_filter', $ret_product->price);
    	$output .= '<input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($ret_product->name).'" /><input type="hidden" name="price_tmp1" value="'.$price_tmp1.'" />';
    }

    if(defined('WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE') && WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE !== '0'){
        //Set the country/region preference by force.
        $output .= '<input type="hidden" name="lc" value="'.WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE.'" />';
    }
    $output .= '<input type="hidden" name="cmd" value="_xclick" />';
    $output .= '<input type="hidden" name="charset" value="utf-8" />';
 
    if (!empty($ret_product->paypal_email)){
    	$output .= "<input type=\"hidden\" name=\"business\" value=\"$ret_product->paypal_email\" />";
    }
    else{
    	$output .= "<input type=\"hidden\" name=\"business\" value=\"$paypal_email\" />";
    }

    if(!empty($nggImage->alttext)){
    	$output .= '<input type="hidden" name="item_name" value="'.htmlspecialchars($nggImage->alttext).'" />';
    }
    else{
    	$output .= '<input type="hidden" name="item_name" value="'.htmlspecialchars($ret_product->name).'" />';
    }
    $price_amt = apply_filters('eStore_change_price_before_payment_filter', $ret_product->price);
    $output .= "<input type=\"hidden\" name=\"amount\" value=\"$price_amt\" />";
    $output .= "<input type=\"hidden\" name=\"currency_code\" value=\"$paypal_currency\" />";
    $output .= "<input type=\"hidden\" name=\"item_number\" value=\"$id\" />";
    
    if (!get_option('eStore_paypal_profile_shipping'))
    {
        if (!empty($ret_product->shipping_cost))
        {
            $baseShipping = get_option('eStore_base_shipping');
            $postage_cost = round($ret_product->shipping_cost + $baseShipping,2);
            $output .= "<input type=\"hidden\" name=\"shipping\" value='".$postage_cost."' />";
            $output .= "<input type=\"hidden\" name=\"no_shipping\" value='2' />";
        }
        else
        {
            $output .= "<input type=\"hidden\" name=\"no_shipping\" value='1' />";
        }  
    }
    else if(!empty($ret_product->weight))//include weight for profile based shipping
    {
    	$output .= "<input type=\"hidden\" name=\"weight\" value='".$ret_product->weight."' />";
    	$output .= "<input type=\"hidden\" name=\"weight_unit\" value=\"lbs\" />";
    }   
    if(get_option('eStore_enable_tax'))
    {
    	if(!empty($ret_product->tax))
    	{
            $tax = round(($ret_product->price * $ret_product->tax)/100,2);
            $output .= "<input type=\"hidden\" name=\"tax\" value='".$tax."' />";
    	}
    	else
        {
            $tax_rate = get_option('eStore_global_tax_rate');
            $tax = round(($ret_product->price * $tax_rate)/100,2);
            $output .= "<input type=\"hidden\" name=\"tax\" value='".$tax."' />";
    	}    	
    }
    else{
        if($ret_product->tax == '0'){
            $output .= "<input type=\"hidden\" name=\"tax\" value='0' />";
        }
    }
    
    $output .= $urls;
    $output .= '<input type="hidden" name="mrb" value="3FWGC6LFTMTUG" />';
    $returnButtonText = get_option('eStore_paypal_return_button_text');
    $output .= '<input type="hidden" name="cbt" value="'.$returnButtonText.'" />';
    
    $page_style_name = get_option('eStore_paypal_co_page_style');
    $output .= '<input type="hidden" name="page_style" value="'.$page_style_name.'" />';

    if($wp_eStore_config->getValue('eStore_pp_collect_instruction_enabled')=='1'){    
        $instruction_f_label = $wp_eStore_config->getValue('eStore_pp_instruction_label');
        if(empty($instruction_f_label)){
            $instruction_f_label = 'Add special instructions to the seller';
        }
        $output .= '<input type="hidden" name="no_note" value="0" />';
        $output .= '<input type="hidden" name="cn" value="'.$instruction_f_label.'" />';
    }
    
    if (get_option('eStore_display_tx_result')){
        $output .= '<input type="hidden" name="rm" value="1" />';
    }    
    if(empty($ret_product->ref_text))
    {
        if(!empty($nggImage->pid))
        {
            $custom_field_val = eStore_get_custom_field_value();
            $custom_field_val = append_values_to_custom_field('ngg_pid',$nggImage->pid);
            $output .= '<input type="hidden" name="custom" value="'.$custom_field_val.'" id="eStore_custom_values" />';
        }
        else
        {     	
            $output .= aff_add_custom_field();
        }
    }   
    else
    {
        $custom_field_val = eStore_get_custom_field_value();
        $custom_field_val = append_values_to_custom_field('subsc_ref',$ret_product->ref_text);
        $output .= '<input type="hidden" name="custom" value="'.$custom_field_val.'" id="eStore_custom_values" />';
    }
    if (get_option('eStore_show_t_c_for_buy_now')){
        $output .= eStore_show_terms_and_cond();  
    }
		
    $output .= $button_type;    
    $output .= '</form>';
    $output .= '</div>';
    return $output;
}

function print_eStore_subscribe_button_form($id)
{
    global $wpdb;
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
    $output = "";
    if (is_numeric ($ret_product->available_copies)){
        if ($ret_product->available_copies < 1){// No more copies left
            return eStore_get_sold_out_button();
        }
    }
    if (!empty($ret_product->paypal_email))
        $paypal_email = $ret_product->paypal_email;
    else
        $paypal_email = get_option('cart_paypal_email');
		
    if(!empty($ret_product->currency_code))
        $paypal_currency = $ret_product->currency_code;
    else
        $paypal_currency = get_option('cart_payment_currency');	

    $return = get_option('cart_return_from_paypal_url');

    // Find out if the product should be delivered automatically through a notify email
    if (get_option('eStore_auto_product_delivery') == ''){
            $notify = '';
    }
    else{
        if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
                $notify = WP_ESTORE_WP_SITE_URL.'/?estore_pp_ipn=process';
        }else{
                $notify = WP_ESTORE_URL.'/paypal.php';
        }
    }

    $urls = "";
    if (!empty($notify))
        $urls .= '<input type="hidden" name="notify_url" value="'.$notify.'" />';

    if (!empty($ret_product->return_url))
    {
        $urls .= '<input type="hidden" name="return" value="'.$ret_product->return_url.'" />';
    }
    else
    {
    if (!empty($return))
        $urls .= '<input type="hidden" name="return" value="'.$return.'" />';
    }
    $cancel_url =  get_option('cart_cancel_from_paypal_url');
    if(!empty($cancel_url))
    {
        $urls .= '<input type="hidden" name="cancel_return" value="'.$cancel_url.'" />';
    }

    $button = $ret_product->button_image_url;
    $button_type = "";
    if (!empty($button))
    {
        $button_type .= '<input type="image" src="'.$button.'" class="eStore_subscribe_button" alt="'.WP_ESTORE_SUBSCRIBE.'"/>';
    }
    else
    {
        $button_type .= '<input type="submit" class="eStore_subscribe_button" value="'.WP_ESTORE_SUBSCRIBE.'" />';
    }
    
    $sandbox_enabled = get_option('eStore_cart_enable_sandbox');
    if($sandbox_enabled){
    	$form_submit_url = PAYPAL_SANDBOX_URL;
    }
    else{
    	$form_submit_url = PAYPAL_LIVE_URL;
    }
    $form_submit_url = apply_filters('eStore_pp_subscribe_form_submit_url', $form_submit_url);
    //Subscribe button form
    $output = "";
    $output .= '<div class="eStore_button_wrapper eStore_pp_subscribe_wrapper">';
    $output .= '<form action="'.$form_submit_url.'" method="post" onsubmit="return ReadForm1(this, 3);">';
            
    $line_break = true;
  
    //variation code
    $output .= get_variation_and_input_code($ret_product,$line_break,3);
    
    //Custom price
    if($ret_product->custom_price_option=='1')
    {
        $currSymbol = get_option('cart_currency_symbol');
        $currSymbol = apply_filters('eStore_change_curr_symbol_filter', $currSymbol);
        $output .= WP_ESTORE_YOUR_PRICE.': '.$currSymbol.'<input type="text" name="custom_price" size="3" value="" />&nbsp;';
        if ($line_break) $output .= '<br />';
    }  
	    
    $output .= '<input type="hidden" name="product_name_tmp1" value="'.htmlspecialchars($ret_product->name).'" /><input type="hidden" name="price_tmp1" value="'.$ret_product->a3.'" />';
    
    if(defined('WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE') && WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE !== '0'){
        //Set the country/region preference by force.
        $output .= '<input type="hidden" name="lc" value="'.WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE.'" />';
    }
    $output .= '<input type="hidden" name="cmd" value="_xclick-subscriptions" />';
    $output .= '<input type="hidden" name="charset" value="utf-8" />';
    $output .= "<input type=\"hidden\" name=\"business\" value=\"$paypal_email\" />";
    $output .= '<input type="hidden" name="item_name" value="'.htmlspecialchars($ret_product->name).'" />';
    $output .= "<input type=\"hidden\" name=\"currency_code\" value=\"$paypal_currency\" />";
    $output .= "<input type=\"hidden\" name=\"item_number\" value=\"$id\" />";    
    $output .= '<input type="hidden" name="rm" value="2" /><input type="hidden" name="no_note" value="1" />';
    if(!empty($ret_product->p1))
    {
    	$output .= "<input type=\"hidden\" name=\"a1\" value=\"$ret_product->a1\" /><input type=\"hidden\" name=\"p1\" value=\"$ret_product->p1\" /><input type=\"hidden\" name=\"t1\" value=\"$ret_product->t1\" />";
    }
    if(!empty($ret_product->p3))
    {
    	if(empty($ret_product->a3))
    	{
    		$output .= "<div style='color:red;'>Looks like you did not specify a recurring amount in the subscription details. Please note that you must specify a value for the Recurring Billing Amount to create a working subscription payment button!</div>";
    	}
    	else
    	{    		    	
    		$output .= "<input type=\"hidden\" name=\"a3\" value=\"$ret_product->a3\" /><input type=\"hidden\" name=\"p3\" value=\"$ret_product->p3\" /><input type=\"hidden\" name=\"t3\" value=\"$ret_product->t3\" />";
    	}
    }   
    if($ret_product->sra == '1')
    {
    	$output .= '<input type="hidden" name="sra" value="1" />';
    }

    if($ret_product->srt>1) //do not include srt value if set to 1 or a negetive number.
    {
    	$output .= "<input type=\"hidden\" name=\"src\" value=\"1\" /><input type=\"hidden\" name=\"srt\" value=\"$ret_product->srt\" />";
    }
    else if($ret_product->srt == '0')
    {
        $output .= "<input type=\"hidden\" name=\"src\" value=\"1\" />";
    }

    if(is_numeric($ret_product->shipping_cost) && $ret_product->shipping_cost == 0){
        $output .= "<input type=\"hidden\" name=\"no_shipping\" value='1' />";
    }

    $output .= $urls;
    $output .= '<input type="hidden" name="mrb" value="3FWGC6LFTMTUG" />';    
    $returnButtonText = get_option('eStore_paypal_return_button_text');
    $output .= '<input type="hidden" name="cbt" value="'.$returnButtonText.'" />';

    $page_style_name = get_option('eStore_paypal_co_page_style');
    $output .= '<input type="hidden" name="page_style" value="'.$page_style_name.'" />';
    
    if(empty($ret_product->ref_text))
    {
        $output .= aff_add_custom_field();
    }
    else
    {
        $custom_field_val = eStore_get_custom_field_value();
        $subsc_ref_val = 'subsc_ref='.$ret_product->ref_text;
        if (empty($custom_field_val)){
            $custom_field_val = $subsc_ref_val;
        }
        else{
            $custom_field_val = $custom_field_val.'&'.$subsc_ref_val;
        }
        $output .= '<input type="hidden" name="custom" value="'.$custom_field_val.'" id="eStore_custom_values" />';
    }
    if (get_option('eStore_show_t_c_for_buy_now'))
        $output .= eStore_show_terms_and_cond();  
		    
    $output .= $button_type;
    $output .= '</form>';
    $output .= '</div>';
    return $output;		
}

function eStore_donate_button_code($args){
	extract(shortcode_atts(array(
		'id' => 'no id',
		'button_text' => '',
	), $args));

	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if(!$ret_product){		
		return eStore_wrong_product_id_error_msg($id);
	}

	if (!empty($ret_product->paypal_email)){$paypal_email = $ret_product->paypal_email;}
	else{$paypal_email = get_option('cart_paypal_email');}
		
    if(!empty($ret_product->currency_code)){$paypal_currency = $ret_product->currency_code;}
	else{$paypal_currency = get_option('cart_payment_currency');}	

	$return = $ret_product->return_url;
	if (empty($return)){
		$return = get_option('cart_return_from_paypal_url');
	}

	// Find out if the product should be delivered automatically through a notify email
	if (get_option('eStore_auto_product_delivery') == ''){$notify = '';}
	else{
            if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
                    $notify = WP_ESTORE_WP_SITE_URL.'/?estore_pp_ipn=process';
            }else{
                    $notify = WP_ESTORE_URL.'/paypal.php';
            }            
        }
	
    $sandbox_enabled = get_option('eStore_cart_enable_sandbox');
    if($sandbox_enabled){$form_submit_url = PAYPAL_SANDBOX_URL;}
    else{$form_submit_url = PAYPAL_LIVE_URL;}
    
    $output = "";
    $output .= '<form action="'.$form_submit_url.'" method="post">';
    $output .= '<input type="hidden" name="charset" value="utf-8" />';
    $output .= '<input type="hidden" name="business" value="'.$paypal_email.'">';
    $output .= '<input type="hidden" name="cmd" value="_donations">';
    $output .= '<input type="hidden" name="item_name" value="'.$ret_product->name.'">';
    $output .= '<input type="hidden" name="item_number" value="'.$id.'">';
    $price_amt = (float)$ret_product->price;
    if($price_amt > 0){//Fixed price donation
        $price_amt = apply_filters('eStore_change_price_before_payment_filter', $price_amt);
    	$output .= '<input type="hidden" name="amount" value="'.$price_amt.'">';
    }
    $paypal_currency = apply_filters('eStore_change_curr_code_before_payment_filter', $paypal_currency);
    $output .= '<input type="hidden" name="currency_code" value="'.$paypal_currency.'">';
    if(!empty($notify)){
    	$output .= '<input type="hidden" name="notify_url" value="'.$notify.'">';
    }
    $output .= '<input type="hidden" name="return" value="'.$return.'">';
    $page_style_name = get_option('eStore_paypal_co_page_style');
    if(!empty($page_style_name)){
        $output .= '<input type="hidden" name="page_style" value="'.$page_style_name.'" />';
    }
    if(defined('WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE') && WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE !== '0'){
            //Set the country/region preference by force.
            $output .= '<input type="hidden" name="lc" value="'.WP_ESTORE_FORCE_LANGUAGE_OF_PAYPAL_PAGE.'" />';
    }

    $output .= aff_add_custom_field();
	
    if (!empty($button_text)){
    	$sbmt_button_code = '<input type="submit" class="eStore_donate_button" value="'.__($button_text).'" />';
    }
    else if(!empty($ret_product->button_image_url)){
    	$sbmt_button_code = '<input type="image" src="'.$ret_product->button_image_url.'" class="eStore_donate_button" alt="Donate"/>';
    }
    else{
   		$sbmt_button_code = '<input type="submit" class="eStore_donate_button" value="'.WP_ESTORE_DONATE.'" />';
    }
    $output .= $sbmt_button_code;
    $output .= '</form>';
	return $output;
}

function show_product_fancy_style($id,$button_type=1,$show_price=1,$restriction='',$args=array())
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);

    $output = '<div class="eStore-product eStore-fancy-wrapper">';
    $output .= get_thumbnail_image_section_code($ret_product);
    if(!empty($ret_product->product_url))
    {
    	$output .= '<div class="eStore-product-description"><div class="eStore-product-name"><a href="'.$ret_product->product_url.'">'.$ret_product->name.'</a></div>';
    }
    else
    {
    	$output .= '<div class="eStore-product-description"><div class="eStore-product-name">'.$ret_product->name.'</div>';
    }
    $description = html_entity_decode($ret_product->description, ENT_COMPAT,"UTF-8");
    $output .= do_shortcode($description);
    if (!empty($ret_product->available_copies))
        $output .= '<br />'.ESTORE_AVAILABLE_QTY.': '.$ret_product->available_copies;
    
	if($show_price==1){        
	    if(!empty($ret_product->old_price)){
	    	$output .= '<div class="eStore_oldprice"><strong>'.ESTORE_OLD_PRICE.': </strong><span class="eStore_price_value_old">'.print_tax_inclusive_payment_currency_if_enabled($ret_product->old_price, WP_ESTORE_CURRENCY_SYMBOL,'',$ret_product).'</span></div>';
	    }
    	$output .= '<div class="eStore_price"><span class="eStore_price_label"><strong>'.ESTORE_PRICE.': </strong></span><span class="eStore_price_value">'.print_tax_inclusive_payment_currency_if_enabled($ret_product->price,WP_ESTORE_CURRENCY_SYMBOL,'', $ret_product).'</span></div>';
	    $conversion_rate = get_option('eStore_secondary_currency_conversion_rate');
	    if (!empty($conversion_rate))
	    {
	    	$secondary_curr_symbol = get_option('eStore_secondary_currency_symbol');
	    	$secondary_curr_amt = number_format($ret_product->price*$conversion_rate,2);
	    	$output .= get_option('eStore_secondary_currency_code').' '.print_tax_inclusive_payment_currency_if_enabled($secondary_curr_amt, $secondary_curr_symbol,'', $ret_product).'<br />';
	    }
    }
    $output .= eStore_show_button_based_on_condition($id,$ret_product,$button_type,$restriction,$args);
    $output .= '</div></div>';
    
    return $output;
}
function show_product_fancy_style2($id,$button_type=1,$show_price=1,$restriction='',$args=array())
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);

    $output = '<div class="eStore-product-fancy2">';
    $output .= get_thumbnail_image_section_code($ret_product,"eStore-product-fancy2-thumb-image");
    if(!empty($ret_product->product_url))
    {
    	$output .= '<div class="eStore-product-description"><div class="eStore-fancy2-product-name"><a href="'.$ret_product->product_url.'">'.$ret_product->name.'</a></div>';
    }
    else
    {
    	$output .= '<div class="eStore-product-description"><div class="eStore-fancy2-product-name">'.$ret_product->name.'</div>';
    }      
    //$output .= '<div class="eStore-product-description"><div class="eStore-product-name">'.$ret_product->name.'</div>';
    $description = html_entity_decode($ret_product->description, ENT_COMPAT,"UTF-8");
    $output .= do_shortcode($description);
    if (!empty($ret_product->available_copies))
        $output .= '<br /><strong>'.ESTORE_AVAILABLE_QTY.': </strong>'.$ret_product->available_copies;    
    $output .= '</div></div>';

    $output .= '<div class="eStore-product-fancy2-footer eStore-fancy-wrapper">';
    $output .= '<div class="footer-left"><div class="footer-left-content">';
	$output .= eStore_show_button_based_on_condition($id,$ret_product,$button_type,$restriction,'2',$args);
    $output .= '</div></div>';
    
    if($show_price==1){ 
	    $conversion_rate = get_option('eStore_secondary_currency_conversion_rate');
	    if (!empty($conversion_rate))
	    {
	    	$secondary_curr_symbol = get_option('eStore_secondary_currency_symbol');
	    	$secondary_curr_code = get_option('eStore_secondary_currency_code');
	    	$secondary_curr_amt = number_format($ret_product->price*$conversion_rate,2);
	    	$output .= '<div class="footer-right"><span class="eStore_fancy2_price">';
	    	$output .= '<span class="eStore_price_label">'.ESTORE_PRICE.': </span><span class="eStore_price_value">'.print_tax_inclusive_payment_currency_if_enabled($ret_product->price, WP_ESTORE_CURRENCY_SYMBOL,'',$ret_product).'</span>';
	    	$output .= ' ('.$secondary_curr_code.' '.print_tax_inclusive_payment_currency_if_enabled($secondary_curr_amt, $secondary_curr_symbol,'',$ret_product).')';
	    	$output .= '</span>';
	    	if(!empty($ret_product->old_price)){
	    		$output .= '<span class="eStore_oldprice">'.ESTORE_OLD_PRICE.': <span class="eStore_price_value_old">'.print_tax_inclusive_payment_currency_if_enabled($ret_product->old_price, WP_ESTORE_CURRENCY_SYMBOL,'',$ret_product).'</span>&nbsp;</span>';
	    	}
	    	$output .= '</div>';
	    }
	    else
	    {
			$output .= '<div class="footer-right"><span class="eStore_fancy2_price"><span class="eStore_price_label">'.ESTORE_PRICE.': </span><span class="eStore_price_value">'.print_tax_inclusive_payment_currency_if_enabled($ret_product->price, WP_ESTORE_CURRENCY_SYMBOL,'',$ret_product).'</span></span>';
	    	if(!empty($ret_product->old_price)){
	    		$output .= '<span class="eStore_oldprice">'.ESTORE_OLD_PRICE.': <span class="eStore_price_value_old">'.print_tax_inclusive_payment_currency_if_enabled($ret_product->old_price, WP_ESTORE_CURRENCY_SYMBOL,'',$ret_product).'</span>&nbsp;</span>';
	    	}
	    	$output .= '</div>';			
	    }
    }
    $output .= '</div>';
    $output .= '<div class="eStore-clear-float"></div>';

    return $output;
}

function show_download_now_fancy_no_price($id)
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);

    $output = '<div class="eStore-product">';
    $output .= get_thumbnail_image_section_code($ret_product);
    if(!empty($ret_product->product_url))
    {
    	$output .= '<div class="eStore-product-description"><a href="'.$ret_product->product_url.'"><strong>'.$ret_product->name.'</strong></a>';
    }
    else
    {
    	$output .= '<div class="eStore-product-description"><strong>'.$ret_product->name.'</strong>';
    }    
    $output .= '<br />'.html_entity_decode($ret_product->description, ENT_COMPAT,"UTF-8");
    //if (!empty($ret_product->available_copies))
    //    $output .= '<br />'.ESTORE_AVAILABLE_QTY.': '.$ret_product->available_copies;
    
    $output .= eStore_show_download_now_button($id);
    $output .= '</div></div>';
    
    return $output;
}

function show_all_categories_stylish()
{
	global $wpdb;
	$cat_table_name = WP_ESTORE_CATEGORY_TABLE_NAME;
	$wp_eStore_cat_db = $wpdb->get_results("SELECT * FROM $cat_table_name ORDER BY cat_id ASC", OBJECT);
	if ($wp_eStore_cat_db)
	{
   		foreach ($wp_eStore_cat_db as $wp_eStore_cat_db)
   		{
            $output .= show_category_fancy_style($wp_eStore_cat_db);
   		}
	}	
	return $output;		
}

function show_category_stylish($id)
{
	global $wpdb;
	$cat_table_name = WP_ESTORE_CATEGORY_TABLE_NAME;
	$wp_eStore_cat_db = $wpdb->get_row("SELECT * FROM $cat_table_name WHERE cat_id = '$id'", OBJECT);
	if ($wp_eStore_cat_db)
	{
            $output = show_category_fancy_style($wp_eStore_cat_db);
        }
        return $output;
}

function show_category_fancy_style($wp_eStore_cat_db)
{
    $output = '<div class="eStore-category-fancy">';
    $output .= '<div class="eStore-category-fancy-thumbnail"><a href="'.$wp_eStore_cat_db->cat_url.'" title="'.$wp_eStore_cat_db->cat_name.'"><img class="thumb-image" src="'.$wp_eStore_cat_db->cat_image.'" alt="'.$wp_eStore_cat_db->cat_name.'" /></a></div>';
    $output .= '<div class="eStore-category-fancy-name">';
    $output .= '<a href="'.$wp_eStore_cat_db->cat_url.'">'.'<strong>'.$wp_eStore_cat_db->cat_name.'</strong></a>';
    $output .= '</div>';
    $output .= '<div class="eStore-category-fancy-description">';	
    //$output .= html_entity_decode($wp_eStore_cat_db->cat_desc, ENT_COMPAT);
    $output .= html_entity_decode($wp_eStore_cat_db->cat_desc, ENT_COMPAT,"UTF-8");
    $output .= '</div></div>';
    
    return $output;	
}
function show_products_from_category($id,$style=1)
{
	$i = 0;
    //set pages to include $limit records per page
	$limit = get_option('eStore_products_per_page');
	if(empty($limit))
		$limit = 25;	
    if (isset($_GET['product_page']))
    {
        $page = strip_tags($_GET['product_page']);
    }
    else
    {
        $page = 1;
    }
    $start = ($page - 1) * $limit;

	global $wpdb;
	$cat_prod_rel_table_name = $wpdb->prefix . "wp_eStore_cat_prod_rel_tbl";
	//$wp_eStore_db = $wpdb->get_results("SELECT * FROM $cat_prod_rel_table_name where cat_id=$id", OBJECT);
	$wp_eStore_db = $wpdb->get_results("SELECT * FROM $cat_prod_rel_table_name where cat_id=$id ORDER BY prod_id DESC LIMIT $start, $limit", OBJECT);
    $totalrows = $wpdb->get_var("SELECT COUNT(*) FROM $cat_prod_rel_table_name where cat_id=$id;");

	if ($wp_eStore_db)
	{
   		foreach ($wp_eStore_db as $wp_eStore_db)
   		{
            if ($style==1)
            {
                $output .= show_product_fancy_style($wp_eStore_db->prod_id);
            }
            else if ($style==2)
            {
                $output .= show_product_fancy_style2($wp_eStore_db->prod_id);
            }          
   		}
	}
	else
	{
		$output .= "No products in this category yet!";
	}

    $output .= wp_eStore_generate_pagination_section($totalrows,$limit);   
	return $output;	
}
function eStore_print_all_products_stylish($style=1)
{
	$i = 0;
    //set pages to include $limit records per page
	$limit = get_option('eStore_products_per_page');
	if(empty($limit))
		$limit = 25;		
    if (isset($_GET['product_page']))
    {
        $page = strip_tags($_GET['product_page']);
    }
    else
    {
        $page = 1;
    }
    $start = ($page - 1) * $limit;

	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$wp_eStore_db = $wpdb->get_results("SELECT * FROM $products_table_name ORDER BY id DESC LIMIT $start, $limit", OBJECT);
    //get total rows
    $totalrows = $wpdb->get_var("SELECT COUNT(*) FROM $products_table_name;");

	if ($wp_eStore_db)
	{
   		foreach ($wp_eStore_db as $wp_eStore_db)
   		{
            if ($style==1)
            {
                $output .= show_product_fancy_style($wp_eStore_db->id);
            }
            else if ($style==2)
            {
                $output .= show_product_fancy_style2($wp_eStore_db->id);
            }
   		}
	}
	$output .= wp_eStore_generate_pagination_section($totalrows,$limit);
	return $output;
}

function wp_estore_products_table()
{
	$i = 0;
	$output .= '
	<table class="widefat">
	<thead><tr>
	<th scope="col">'.__('Products', 'wp_eStore').'</th>
	</tr></thead>
	<tbody>';
    
    //set pages to include $limit records per page
	$limit = get_option('eStore_products_per_page');
    if (isset($_GET['product_page']))
    {
        $page = strip_tags($_GET['product_page']);
    }
    else
    {
        $page = 1;
    }
    $start = ($page - 1) * $limit;

	global $wpdb;
	$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
	//$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$wp_eStore_db = $wpdb->get_results("SELECT * FROM $products_table_name ORDER BY id DESC LIMIT $start, $limit", OBJECT);
    //get total rows
    $totalrows = $wpdb->get_var("SELECT COUNT(*) FROM $products_table_name;");

	if ($wp_eStore_db)
	{
		foreach ($wp_eStore_db as $wp_eStore_db)
		{
			if($i%2 == 0)
              {
				$output .= "<tr bgcolor='#F4F6FA'>";
				$i++;
			  }
			else
			  {
				$output .= "<tr bgcolor='#E9EDF5'>";
				$i++;
			  }
			$output .= '<td><strong>'.$wp_eStore_db->name.'</strong><br> '.WP_ESTORE_CURRENCY_SYMBOL.$wp_eStore_db->price.'<br>';
			$output .= get_button_code_for_product($wp_eStore_db->id);
			$output .= '<br></td></tr>';
		}
	}

	$output .= '</tbody>
	</table>';

	$output .= wp_eStore_generate_pagination_section($totalrows,$limit);
	return $output;
}

function eStore_show_terms_and_cond()
{
    //eStore_load_t_and_c_jquery(); It now gets loaded in the footer
    $terms_url = get_option('eStore_t_c_url');
    $terms = "<a href=\"$terms_url\" target=\"_blank\"><u>".ESTORE_TERMS_AND_CONDITIONS."</u></a>";
    $output = '<input type="checkbox" name="t-and-c" class="t-and-c" value="" /><label for="t-and-c">'.ESTORE_TERMS_AGREE.$terms.'</label><br />';
    $output .= '<label class="t_and_c_error" for="t-and-c" id="t_and_c_error">'.ESTORE_TERMS_ERROR.'<br /></label>';
    return $output;
}

function eStore_get_sale_counter($id)
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);

    $output = $ret_product->sales_count;
    return $output;
}

function eStore_get_remaining_copies_counter($id)
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);

    $output = $ret_product->available_copies;
    return $output;
}

function eStore_show_download_now_button($id,$line_break=true,$nggImage='')
{
	$output .= '<div class="download_now_button">';
	$output .= '<form method="post"  action="" style="display:inline" onsubmit="return ReadForm1(this, 1);">';
	$output .= '<input type="hidden" name="eStore_download_now_button" value="1" />';
	$output .= '<input type="hidden" name="download_now_product_id" value="'.base64_encode($id).'" />';
	$output .= '<input type="hidden" name="eStore_form_time_value" value="'.strtotime("now").'" />';
	
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;		
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	
	if(!empty($nggImage->alttext))
	{			
		$replacement .= '<input type="hidden" name="product" value="'.$nggImage->alttext.'" /><input type="hidden" name="product_name_tmp1" value="'.$nggImage->alttext.'" />';
	}
	else
	{
    	$replacement .= '<input type="hidden" name="product" value="'.$ret_product->name.'" /><input type="hidden" name="product_name_tmp1" value="'.$ret_product->name.'" />';
	}	
	$var_output = get_variation_and_input_code($ret_product,$line_break,1,$nggImage);
	$output .= $replacement.$var_output; 
	
	$button = $ret_product->button_image_url;
    if (!empty($button))
    {
        $button_type .= '<input type="image" src="'.$button.'" class="download_now_button_submit" alt="'.ESTORE_DOWNLOAD_TEXT.'"/>';
    }
    else
    {
   		$button_type .= '<input type="submit" name="submit" class="download_now_button_submit" value="'.ESTORE_DOWNLOAD_TEXT.'" />';
    }
    	
	$output .= $button_type;
	$output .= '</form>';
	$output .= '</div>';
	return $output;
}

function eStore_download_now_button_request_handler()
{
    if (isset($_POST['eStore_download_now_button']))
    {
        //sanitize data
        $_POST['product'] = strip_tags($_POST['product']);  
        $_POST['download_now_product_id'] = strip_tags($_POST['download_now_product_id']);  

        $product_id = base64_decode($_POST['download_now_product_id']);
        $product_name_with_var_text = $_POST['product'];    

        //update the inventory count of this product
        global $wpdb,$wp_eStore_config;
        $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
        $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$product_id'", OBJECT);
        if ($wp_eStore_config->getValue('eStore_product_price_must_be_zero_for_free_download')=='1')
        {
            if(!is_numeric($retrieved_product->price) || $retrieved_product->price > 0){
                    echo '<div class="eStore_error_message">Error! The admin of this site requires the product price to be set to 0.00 before it can be given as a free download!</div>';
                    exit;
            }    	
        }
            $cart_item_qty = 1;
        if (is_numeric($retrieved_product->available_copies))
        {
            $new_available_copies = ($retrieved_product->available_copies - $cart_item_qty);
        }
        $new_sales_count = ($retrieved_product->sales_count + $cart_item_qty);
        $current_product_id = $retrieved_product->id;
        $updatedb = "UPDATE $products_table_name SET available_copies = '$new_available_copies', sales_count = '$new_sales_count' WHERE id='$current_product_id'";
        $results = $wpdb->query($updatedb);

        //generate download link
        $download_link = generate_download_link_for_product($product_id,$product_name_with_var_text);
        $pieces = explode("http", $download_link);    
        $full_encrypted_url = 'http'.$pieces[1];
        eStore_redirect_to_url($full_encrypted_url);
    }
}

function eStore_show_members_purchase_history()
{
    if (function_exists('wp_eMember_install'))
    {
	    $emember_auth = Emember_Auth::getInstance();
	    //$user_name = $auth->getUserInfo('user_name');
	    $user_id = $emember_auth->getUserInfo('member_id');
	    if (!empty($user_id))
	    {
			$output .= eStore_display_members_purchase_history($user_id);		
	    }
	    else
	    {
	    	$output .= ESTORE_YOU_MUST_BE_LOGGED;
	    }
    }
    else
    {
    	$output .= "<br />You need to have the WP eMember plugin installed to be able to use this feature";
    }
	return $output;
}
function eStore_display_members_purchase_history($user_id,$show_download=false)
{
	global $wpdb;
	$customer_table_name = WP_ESTORE_CUSTOMER_TABLE_NAME;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;	
	$ret_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE member_username = '$user_id'", OBJECT);
	if(!$show_download)	
	{
		$output .= '
		<table class="widefat">
		<thead><tr>
		<th scope="col">'.ESTORE_PRODUCT_NAME.'</th>	
	    <th scope="col">'.ESTORE_DATE.'</th>
	    <th scope="col">'.ESTORE_PRICE_PAID.'</th>
		</tr></thead>
		<tbody>';
	
		if ($ret_customer_db)
		{
			foreach ($ret_customer_db as $ret_customer_db)
			{
				$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$ret_customer_db->purchased_product_id'", OBJECT);				
				$product_details = $ret_customer_db->product_name;
				if(!empty($ret_product->product_url)){
					$product_details = '<a href="'.$ret_product->product_url.'" target="_blank">'.$ret_customer_db->product_name.'</a>';
				}
				$output .= '<tr>';				
				$output .= '<td align="left">'.$product_details.'</td>';
				$output .= '<td align="center">'.$ret_customer_db->date.'</td>';
                                $sale_amt = $ret_customer_db->sale_amount;
                                if(is_numeric($sale_amt)){
                                    $sale_amt = print_digi_cart_payment_currency($sale_amt,WP_ESTORE_CURRENCY_SYMBOL);
                                }                                
				$output .= '<td align="center">'.$sale_amt.'</td>';
				$output .= '</tr>';
			}
		}
		else
		{
			$output .= '<tr><td colspan="4">'.ESTORE_NO_PURCHASE_FOUND.'</td></tr>';
		}
		$output .= '</tbody></table>';
	}
	else //purchase history with download
	{
		if(isset($_POST['eStore_purchase_history_generate_download_now_button']))
		{
			//Show the generated download links
			$id = strip_tags($_POST['download_now_product_id']);
			$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
			//$payment_data = free_download_pseudo_payment_data($cust_name, $cust_email);
			$item_name = strip_tags($_POST['download_now_item_name']);
			$download_link = generate_download_link($retrieved_product,$item_name);
			$download_link = nl2br($download_link);
        	$download_link = wp_eStore_replace_url_in_string_with_link($download_link);
        	$output .=  '<p style="border:1px solid #ccc; padding:10px;">'.$download_link.'</p>';
		}
		$output .= '
		<table class="widefat">
		<thead><tr>
		<th scope="col">'.ESTORE_PRODUCT_NAME.'</th>	
	    <th scope="col">'.ESTORE_DATE.'</th>
	    <th scope="col">'.ESTORE_PRICE_PAID.'</th>
	    <th scope="col">'.ESTORE_GENERATE_DOWNLOAD.'</th>
		</tr></thead>
		<tbody>';
	
		if ($ret_customer_db)
		{
			foreach ($ret_customer_db as $ret_customer_db)
			{
				$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$ret_customer_db->purchased_product_id'", OBJECT);				
				if($ret_product){
					$product_details = $ret_customer_db->product_name;
					if(!empty($ret_product->product_url)){
						$product_details = '<a href="'.$ret_product->product_url.'" target="_blank">'.$ret_customer_db->product_name.'</a>';
					}			
					$output .= '<tr>';
					$output .= '<td align="left">'.$product_details.'</td>';
					$output .= '<td align="center">'.$ret_customer_db->date.'</td>';
                                        
                                        $sale_amt = $ret_customer_db->sale_amount;
                                        if(is_numeric($sale_amt)){
                                            $sale_amt = print_digi_cart_payment_currency($sale_amt,WP_ESTORE_CURRENCY_SYMBOL);
                                        }
					$output .= '<td align="center">'.$sale_amt.'</td>';	
	
					$output .= '<td align="center">';
					$output .= '<form method="post" action="" style="display:inline">';
					$output .= '<input type="hidden" name="eStore_purchase_history_generate_download_now_button" value="1" />';
					$output .= '<input type="hidden" name="download_now_product_id" value="'.$ret_product->id.'" />';
					$output .= '<input type="hidden" name="download_now_item_name" value="'.$ret_customer_db->product_name.'" />';
					$output .= '<input type="submit" name="submit" class="generate_download_now_button" value="'.ESTORE_GENERATE_DOWNLOAD.'" />';
					$output .= '</form>';
					$output .= '</td>';
	
					$output .= '</tr>';
				}
				else
				{
					$output .= "<br />Error - Could not find the requested product ID in the products database!";
				}
			}
		}
		else
		{
			$output .= '<tr><td colspan="4">'.ESTORE_NO_PURCHASE_FOUND.'</td></tr>';
		}
		$output .= '</tbody></table>';		
	}
	return $output;		
}
function eStore_show_members_purchase_history_with_download()
{
    if (function_exists('wp_eMember_install'))
    {
	    $emember_auth = Emember_Auth::getInstance();
	    $user_id = $emember_auth->getUserInfo('member_id');
	    if (!empty($user_id))
	    {
			//get purchase history with download option	
	    	$output .= eStore_display_members_purchase_history($user_id,true);	
	    }
	    else
	    {
	    	$output .= ESTORE_YOU_MUST_BE_LOGGED;
	    }
    }
    else
    {
    	$output .= "<br />You need to have the WP eMember plugin installed to be able to use this feature";
    }
	return $output;	
}

function eStore_show_button_based_on_condition($id,$ret_product,$button_type,$restriction,$style='',$args=array())
{
	$output = "";
	$show_button = false;
	if(!empty($restriction)){		
		if(function_exists('wp_eMember_install')){
			$restriction_args = explode('|',$restriction);
			$args_size = count($restriction_args);
			if($args_size == 1){//Only level restriction restriction="1-2-3"
				$permitted_levels = explode('-', $restriction_args[0]);
				$show_button = eStore_member_belongs_to_specified_levels($permitted_levels);
			}
			else if($args_size>1){//Level restriction with conditional buttons restriction="1-2|4|2"
				if($args_size == 3){
					if(eStore_is_product_using_custom_button_image($id)){//Check if button image is being used
						$output .= '<div class="eStore_error_message">Error! You have specified a custom button image for this product in the product configuration. This shortcode cannot work with a custom button image. Edit this product and remove the URL value from the "Button Image URL" field.</div>';
						return $output;
					}
					$permitted_levels = explode('-', $restriction_args[0]);
					if(eStore_member_belongs_to_specified_levels($permitted_levels)){
						$button_type = $restriction_args[1];
					}else{
						$button_type = $restriction_args[2];
					}
					$show_button = true;
				}
				else{
					$output .= '<div class="eStore_error_message">Error! You have an error in the "restriction" parameter. Please check the documentation and update the shortcode accordingly.</div>';
				}
			}
		}
		else{
			$output .= '<strong><i>The restriction option can be only be used if you are using the <a href="http://www.tipsandtricks-hq.com/?p=1706" target="_blank">WP eMember</a> plugin!</i></strong>';
		}				
	}  
	if(empty($restriction) || $show_button){
	    if($button_type==1){
	    	if($style == '2')
	    		$output .= get_button_code_fancy2_for_element($ret_product,false);
	    	else
	    		$output .= get_button_code_for_element($ret_product);
	    }	        
	    else if ($button_type==2){
	        $output .= print_eStore_buy_now_button($id);
	    }
	    else if ($button_type==3){
                if(isset($args['gateway']))
                {
                    if(function_exists('print_wp_pg_eStore_subscription_button_form')){
                        $output .= print_wp_pg_eStore_subscription_button_form($id, $args['gateway']);
                    }
                }
                else{
                    $output .= print_eStore_subscribe_button_form($id);
                }
	    }
	    else if ($button_type==4){
	        $output .= eStore_show_download_now_button($id);  
	    }
	    else if($button_type==5){//download now button with PDF Stamping
	    	if(function_exists('eStore_show_download_now_button_with_stamping')){
	    		$output .= eStore_show_download_now_button_with_stamping($id);
	    	}
	    	else{
	    		$output .= '<div class="eStore_error_message">Error! You need to have the eStore extra shortocdes plugin installed for this type of button.</div>';
	    	}
	    }
            else if($button_type==6){//Donate now button
                $args = array('id' => $id);
                $output .= eStore_donate_button_code($args);
            }
	}
	else{
		$output .= '<div class="eStore_eMember_restricted_message">'.EMEMBER_CONTENT_RESTRICTED.'</div>';
	}
	return $output;	
}

function wp_eStore_generate_pagination_section($totalrows,$limit,$url='',$get_parameter_string='',$show_pagination_div='1')
{
	$output = "";
    //Number of pages setup
    if($totalrows <= $limit){//No pagination needed
    	return $output;
    }
    
	if(empty($get_parameter_string)){
		$get_parameter_string = "product_page";
	}
	    
    //Create page URL with separator
    if(empty($url)){
    	$url=get_permalink();
    }
	$separator='?';
	$search_string = "?".$get_parameter_string."=";
	if(strpos($url,$search_string)){
		$separator='?';
	}
	else if(strpos($url,'?')!==false){
	    $separator='&';
	} 
	
	$url_with_separator = $url.$separator.$get_parameter_string."=";
	    
    if (isset($_GET[$get_parameter_string]))
    {
        $page = strip_tags($_GET[$get_parameter_string]);
    }
    else
    {
        $page = 1;
    }
	$pages = ceil($totalrows / $limit);
	
	if($show_pagination_div=='1'){
		$output .= '<div class="eStore_pagination">';
	}
	if($page==1){
		$output .= "<a href='".$url_with_separator.$page."' class=\"pagination_page current_pagination_page\">".$page."</a>";
		$output .= "<a href='".$url_with_separator.($page+1)."' class=\"pagination_page\">".($page+1)."</a>";
		if($pages>2){
			$output .= "<a href='".$url_with_separator.($page+2)."' class=\"pagination_page\">".($page+2)."</a>";		
		}
		$output .= " ... ";
		$output .= "<a href='".$url_with_separator.$pages."' class=\"pagination_page\">".ESTORE_LAST."</a>";		
	}
	else if($page==$pages){
		$output .= "<a href='".$url_with_separator."1"."' class=\"pagination_page\">".ESTORE_FIRST."</a>";	
		$output .= " ... ";
		if($pages>2){
			$output .= "<a href='".$url_with_separator.($page-2)."' class=\"pagination_page\">".($page-2)."</a>";
		}
		$output .= "<a href='".$url_with_separator.($page-1)."' class=\"pagination_page\">".($page-1)."</a>";
		$output .= "<a href='".$url_with_separator.$page."' class=\"pagination_page current_pagination_page\">".$page."</a>";			
	}
	else{
		$output .= "<a href='".$url_with_separator."1"."' class=\"pagination_page\">".ESTORE_FIRST."</a>";	
		$output .= " ... ";		
		$output .= "<a href='".$url_with_separator.($page-1)."' class=\"pagination_page\">".($page-1)."</a>";
		$output .= "<a href='".$url_with_separator.$page."' class=\"pagination_page current_pagination_page\">".$page."</a>";
		if($pages>2){
			$output .= "<a href='".$url_with_separator.($page+1)."' class=\"pagination_page\">".($page+1)."</a>";
		}
		$output .= " ... ";
		$output .= "<a href='".$url_with_separator.$pages."' class=\"pagination_page\">".ESTORE_LAST."</a>";			
	}
	
	$output .= '<span class="eStore_pagination_go_to_area"> '.WP_ESTORE_GO_TO_PAGE;
	$output .= '<form method="post" action="" style="display:inline">';
	$output .= '<input type="hidden" name="estore_pagination_parameter_name" value="'.$get_parameter_string.'" />';
	$output .= '<input type="hidden" name="estore_pagination_raw_url" value="'.$url.'" />';
	$output .= '<input type="text" name="estore_pagination_page_no" value="" size="3" class="estore_pagination_page_no" />';
	$output .= '<input type="submit" name="estore_pagination_go" value="Go" class="estore_pagination_go_button" />';
	$output .= '</form>';
	$output .= '</span>';
	
	if($show_pagination_div=='1'){
		$output .= '</div>';
	}
    return $output;	
}

function eStore_show_product_details($id,$info)
{
	if($id == 'no id'){
		return '<div class="eStore_error_message">You did not specify a Product ID. Please enter a Product ID with this shortcode</div>';
	}	
	if(empty($info)){
		return '<div class="eStore_error_message">You did not specify which information of the product you want to show (the "info" parameter is empty). Please check the shortcode documentation to learn the usage of this shortcode.</div>';
	}
	$condition = " id='".$id."'";
	$product_details = WP_eStore_Db_Access::find(WP_ESTORE_PRODUCTS_TABLE_NAME, $condition);
	//var_dump($product_details);
	if($info == 'all'){
		var_dump($product_details);
		return "";
	}
	if($info == "description"){
		$description = html_entity_decode($product_details->$info, ENT_COMPAT,"UTF-8");
		$description = do_shortcode($description);
		return $description;
	}
	if(isset($product_details->$info)){
		return $product_details->$info;
	}
        else if($info === "price_formatted"){
            $item_price = $product_details->price;
            $defaultSymbol = WP_ESTORE_CURRENCY_SYMBOL;
            $price_formatted = print_digi_cart_payment_currency($item_price,$defaultSymbol);
            return $price_formatted;
        }
	else if($info === "price_tax_inclusive"){
            $tax_inc_price = eStore_get_tax_include_price_by_prod_id($id, $product_details->price);
            return $tax_inc_price;
	}
	else if($info === "price_tax_inclusive_formatted"){
            $tax_inc_price = eStore_get_tax_include_price_by_prod_id($id, $product_details->price);
            $defaultSymbol = WP_ESTORE_CURRENCY_SYMBOL;
            $tax_inc_price_formatted = print_digi_cart_payment_currency($tax_inc_price,$defaultSymbol);            
            return $tax_inc_price_formatted;
	}
        else if($info === "old_price_formatted"){
            $old_item_price = $product_details->old_price;
            $defaultSymbol = WP_ESTORE_CURRENCY_SYMBOL;
            $old_price_formatted = print_digi_cart_payment_currency($old_item_price,$defaultSymbol);
            return $old_price_formatted;
        }
	else{
            return '<div class="eStore_error_message">The value you specified for the "info" parameter does not exist in the eStore product database. Please check the shortcode documentation to learn the usage of this shortcode.</div>';
	}
}
