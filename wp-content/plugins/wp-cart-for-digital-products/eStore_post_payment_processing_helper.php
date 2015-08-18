<?php
include_once('eStore_debug_handler.php');
include_once('eStore_handle_subsc_ipn.php');

//TODO - refactor these definitions and use the ones from main eStore constants
global $wpdb;
define('WP_ESTORE_CUSTOMERS_TABLE_NAME', $wpdb->prefix . "wp_eStore_customer_tbl");
define('WP_ESTORE_SALES_TABLE_NAME', $wpdb->prefix . "wp_eStore_sales_tbl");
define('WP_ESTORE_PENDING_PAYMENT_TABLE_NAME', $wpdb->prefix . "wp_eStore_pending_payment_tbl");
define('WP_AFFILIATE_TABLE_NAME', $wpdb->prefix . "affiliates_tbl");
define('WP_AFFILIATE_SALES_TABLE_NAME', $wpdb->prefix . "affiliates_sales_tbl");
$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
$sales_table_name = $wpdb->prefix . "wp_eStore_sales_tbl";
$eStore_affiliate_individual_product_commisions = array();

function eStore_apply_post_payment_dynamic_tags($string_to_process,$payment_data,$cart_items='',$additional_data='')
{
    //TODO - refactor and use this function from the post payment processing areas everywhere	
    $wp_eStore_config = WP_eStore_Config::getInstance();
    //Counter for incremental receipt number	    
    $receipt_counter = $wp_eStore_config->getValue('eStore_custom_receipt_counter');
    eStore_payment_debug('Incremental counter value: '.$receipt_counter,true);

    $download_url_life = get_option('eStore_download_url_life');	
    $purchase_date = (date ("Y-m-d")); 
    $total_minus_total_tax = round(($payment_data['mc_gross'] - $payment_data['mc_tax']),2);  
    $payment_data['mc_tax'] = round(($payment_data['mc_tax']),2);
    $txn_id = $payment_data['txn_id'];
    $buyer_shipping_info = $payment_data['address'];
    $buyer_phone = $payment_data['phone'];
    $product_key_data = $payment_data['product_key_data'];    
	//Custom data
    $customvariables = get_custom_var($payment_data['custom']);
    $shipping_option = $customvariables['ship_option'];
    if(empty($shipping_option)){$shipping_option = "Default";}    
    $coupon_code = $customvariables['coupon'];
    $eMember_id = $customvariables['eMember_id'];
    
    //Item specific data
    if(empty($additional_data) && !empty($cart_items)){
        $additional_data = array();
        $payment_currency = get_option('cart_payment_currency');
        $currency_symbol = get_option('cart_currency_symbol');
        foreach ($cart_items as $current_cart_item)
        {		
            $additional_data['constructed_products_name'] .= $current_cart_item['item_name'] .", ";
            $additional_data['constructed_products_price'] .= $current_cart_item['mc_gross'] .", ";
            $additional_data['constructed_products_id'] .= $current_cart_item['item_number'] .", ";
            eStore_payment_debug('Value of current cart item: '.$current_cart_item['subs_product'], true);
            if(isset($current_cart_item['subs_product']) && $current_cart_item['subs_product']=='1'){  // this is a subscription item
                eStore_payment_debug('eStore_apply_post_payment_dynamic_tags() - Constructing product details for a subscription', true);
                $terms = "\n".wp_eStore_get_subscription_summary_string($current_cart_item['item_number'], $current_cart_item['item_name'], $current_cart_item['mc_gross']);
                $additional_data['constructed_products_details'] .= $terms;                            
            }
            else{
                $additional_data['constructed_products_details'] .= "\n".$current_cart_item['item_name']." x ".$current_cart_item['quantity']." - ".$currency_symbol.$current_cart_item['mc_gross']." (".$payment_currency.")";
                $tax_inc_price = eStore_get_tax_include_price_by_prod_id($current_cart_item['item_number'],$current_cart_item['mc_gross']);
                $additional_data['constructed_products_details_tax_inc'] .= "\n".$current_cart_item['item_name']." x ".$current_cart_item['quantity']." - ".$currency_symbol.$tax_inc_price." (".$payment_currency.")";
            }
        }
    }
    
    //The following code will be used for all online gateway checkout because they will pass the $additional_data value
    if(!empty($additional_data)){
        $constructed_products_name = $additional_data['constructed_products_name'];
        $constructed_products_price = $additional_data['constructed_products_price'];
        $constructed_products_id = $additional_data['constructed_products_id'];    
        $constructed_products_details = $additional_data['constructed_products_details'];
        $constructed_products_details_tax_inc = $additional_data['constructed_products_details_tax_inc'];
        $product_specific_instructions = $additional_data['product_specific_instructions'];
        $constructed_download_link = $additional_data['constructed_download_link'];	    
        $constructed_download_link_for_digital_item = $additional_data['constructed_download_link_for_digital_item'];    
        $product_license_data = $additional_data['product_license_data'];//this is the license mgr key (not the normal serial key code)
    }    

    $tags = array("{first_name}","{last_name}","{payer_email}","{product_name}","{product_link}","{product_price}","{product_id}","{download_life}",
        "{product_specific_instructions}","{product_details}","{product_details_tax_inclusive}","{shipping_info}","{license_data}","{purchase_date}",
        "{purchase_amt}","{transaction_id}","{shipping_option_selected}","{product_link_digital_items_only}","{total_tax}","{total_shipping}",
        "{total_minus_total_tax}","{customer_phone}","{counter}","{coupon_code}","{serial_key}","{eMember_id}");
    
    $vals = array($payment_data['first_name'],
    $payment_data['last_name'],
    $payment_data['payer_email'],
    $constructed_products_name,
    $constructed_download_link,
    $constructed_products_price,
    $constructed_products_id,
    $download_url_life,
    $product_specific_instructions,
    $constructed_products_details,
    $constructed_products_details_tax_inc,
    $buyer_shipping_info,
    $product_license_data,
    $purchase_date,
    $payment_data['mc_gross'],
    $txn_id,
    $shipping_option,
    $constructed_download_link_for_digital_item,
    $payment_data['mc_tax'],
    $payment_data['mc_shipping'],
    $total_minus_total_tax,
    $buyer_phone,
    $receipt_counter,
    $coupon_code,
    $product_key_data,
    $eMember_id,
    );

    $string_to_process = str_replace($tags,$vals,$string_to_process);
    return $string_to_process;
}

function eStore_get_product_specific_instructions($retrieved_product)
{
    global $wpdb;
    $product_specific_instructions = "";
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;

    if(!empty($retrieved_product->item_spec_instruction)) // check instructions for the current product
    {
        $product_specific_instructions .= "\n".$retrieved_product->item_spec_instruction;
    }
    
    $package_product = eStore_is_package_product($retrieved_product);
    if($package_product)//This is a package product
    {
        eStore_payment_debug('This is a packaged product. Checking product specific instructions for this package.',true);
        $product_ids = explode(',',$retrieved_product->product_download_url);
        if(sizeof($product_ids)>1){
            foreach($product_ids as $id){
                if(empty($id)){continue;}
                $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
                if(!empty($retrieved_product->item_spec_instruction))  //check instructions of the products included in the package
                {
                    $product_specific_instructions .= "\n".$retrieved_product->item_spec_instruction;
                } 
            }
        }
        else{
            eStore_payment_debug('Error configuring the bundled product: it needs 2 or more product IDs to create a package product',false);
        }
    }
    return $product_specific_instructions;
    
}

function get_custom_var($custom)
{
    $delimiter = "&";
    $customvariables = array();
    $namevaluecombos = explode($delimiter, $custom);
    foreach ($namevaluecombos as $keyval_unparsed)
    {
            $equalsignposition = strpos($keyval_unparsed, '=');
            if ($equalsignposition === false)
            {
                $customvariables[$keyval_unparsed] = '';
                continue;
            }
            $key = substr($keyval_unparsed, 0, $equalsignposition);
            $value = substr($keyval_unparsed, $equalsignposition + 1);
            $customvariables[$key] = $value;
    }
    return $customvariables;
}

function get_download_for_variation($cart_item_name,$ret_product,$script_location,$random_key,$payment_data='')
{	
	$eStore_auto_shorten_url = WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS;
	$variations = eStore_get_all_string_inside("(", ")", $cart_item_name);
	eStore_payment_debug('Generating download link for digital variation...',true);
		
	$pieces = explode('|',$ret_product->variation3);
	for ($i=1;$i<sizeof($pieces); $i++)
	{
		$pieces2 = explode('::',$pieces[$i]);
		$var3 = trim($pieces2[0]);
		if (sizeof($pieces2) > 1)
		{
			eStore_payment_debug('Matching digital variation to: '.$var3,true);					
			if(in_array($var3,$variations))
			{
				eStore_payment_debug('Found a match for digital variation: '.$var3,true);
				if (is_numeric($pieces2[1])){
					if (array_key_exists('2', $pieces2)){
						$url = $pieces2[2];
					}
					else{
						eStore_payment_debug('Download URL was not specified for this variation: '.$var3,true);
						$output = "\n".$cart_item_name . WP_ESTORE_THIS_ITEM_DOES_NOT_HAVE_DOWNLOAD;
						return $output;
					}
				}
				else{
					$url = $pieces2[1];
				}
				$download_key = eStore_check_stamping_flag_and_generate_download_key($ret_product,$ret_product->id,$url,$payment_data,$cart_item_name);
                $encrypted_download_url = eStore_construct_raw_encrypted_dl_url($download_key);
				$output = "\n".stripslashes($cart_item_name)." - ".$encrypted_download_url;
				eStore_register_link_in_db('',$download_key,$output,'','','',0,$payment_data['txn_id']);	
				return $output;										
			}
		}
		else{
			eStore_payment_debug('Download URL or price increment was not specified for this variation: '.$var3,true);
			$output = "\n".$cart_item_name . WP_ESTORE_THIS_ITEM_DOES_NOT_HAVE_DOWNLOAD;
		}			
	}
	return 	$output;
}

function get_download_for_variation_tx_result($cart_item_name,$ret_product,$script_location,$random_key,$payment_data='')
{
	$download_link = get_download_for_variation($cart_item_name,$ret_product,$script_location,$random_key,$payment_data);
	$download_link = nl2br($download_link);
	$download_link = wp_eStore_replace_url_in_string_with_link($download_link);
	return $download_link;
}

function generate_download_link_for_product($id,$name='',$payment_data='')
{
	global $wpdb,$products_table_name;
	$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if(!empty($name))
	{
		$output = generate_download_link($retrieved_product,$name,$payment_data);
	}
	else
	{
		$output = generate_download_link($retrieved_product,$retrieved_product->name,$payment_data);
	}
	return $output;
}

function generate_download_link($retrieved_product,$item_name,$payment_data='')
{		
            $time = time();
		    global $wpdb;
		    global $products_table_name;
		    if(isset($retrieved_product)){
		    	$product_id = $retrieved_product->id;
		    }else{
		    	$product_id = "";
		    }
		    $script_location = get_option('eStore_download_script');
		    $random_key = get_option('eStore_random_code');
		    $eStore_auto_shorten_url = WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS;
		    
		    //Check if nextgen gallery integration is being used
		    $pid_check_value = eStore_is_ngg_pid_present($item_name);
		    if($pid_check_value != -1){
		    	$pictureID = $pid_check_value;
		    	$download_link = eStore_get_ngg_image_url($pictureID,$item_name);//Generate link from Nextgen gallery
		    	return $download_link;
		    }
		    		    
		    //check if it is a digital variation
		    $is_digital_variation = false;
		    if(!empty($retrieved_product->variation3) && eStore_check_if_string_contains_url($retrieved_product->variation3)){
		    	$is_digital_variation = true;
		    }
		    
		    if(empty($retrieved_product->product_download_url) && !$is_digital_variation)
		    {
                $download_link = "\n".$item_name . WP_ESTORE_THIS_ITEM_DOES_NOT_HAVE_DOWNLOAD;
            }
            else
            {
            	if(empty($payment_data['txn_id'])){$payment_data['txn_id'] = "";}
            	if(!empty($retrieved_product->variation3))
            	{
            		$download_link = get_download_for_variation($item_name,$retrieved_product,$script_location,$random_key,$payment_data);
            	}
            	else
            	{         	
	    			$download_url_field = $retrieved_product->product_download_url;
	    			$product_ids = explode(',',$download_url_field);
				    $package_product = eStore_is_package_product($retrieved_product);
                    $multi_parts = false;			
				    if(sizeof($product_ids)>1 && !$package_product){
                        $multi_parts = true;
                    }
				    if($package_product)
				    {
				    	eStore_payment_debug('Generating download link for package product.',true);
				        //$count = 0;
				        foreach($product_ids as $id)
				        {
                            $id = trim($id);
                            $retrieved_product_for_specific_id = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	                        //recursively generate the links
                            $download_link .= generate_download_link($retrieved_product_for_specific_id,$retrieved_product_for_specific_id->name,$payment_data);
				        }
				    }
				    else if($multi_parts)
				    {
				    	eStore_payment_debug('Generating download link for multi part file.',true);
				        $count = 1;
				        $download_link .= "\n".stripslashes($item_name)." - ";
				        foreach($product_ids as $id)
				        {
                            $id = trim($id);
                            if(!empty($id)){
                                //$download = $product_id.'|'.$time.'|'.$id;
                                //$download_key = eStore_generate_download_key($product_id,$id);
                                $download_key = eStore_check_stamping_flag_and_generate_download_key($retrieved_product,$product_id,$id,$payment_data,$item_name);
                                $encrypted_download_url = eStore_construct_raw_encrypted_dl_url($download_key);
                                $download_item = "\n".ESTORE_PART." ".$count." : ".$encrypted_download_url;                                
                                $download_link .= $download_item;
                                eStore_register_link_in_db('',$download_key,$download_item,'','','',0,$payment_data['txn_id']);
                                $count++;
                            }
                        }
                    }
				    else
				    {
				    	eStore_payment_debug('Generating download link for single file.',true);
				        //$download = $product_id.'|'.$time;
				        //$download_key = eStore_generate_download_key($product_id);
				        $download_key = eStore_check_stamping_flag_and_generate_download_key($retrieved_product,$product_id,'',$payment_data,$item_name);
				        $encrypted_download_url = eStore_construct_raw_encrypted_dl_url($download_key);
				        $download_link = "\n".stripslashes($item_name)." - ".$encrypted_download_url;
				        eStore_register_link_in_db('',$download_key,$download_link,'','','',0,$payment_data['txn_id']);
				    }
            	} 
            }
            return $download_link;
}

function eStore_construct_raw_encrypted_dl_url($download_key)//TODO - refactor it everywhere for creating the raw link
{
	$eStore_auto_shorten_url = WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS;
	if(WP_ESTORE_ENABLE_NEW_CHECKOUT_REDIRECTION==='1'){
		$encrypted_download_url = WP_ESTORE_WP_SITE_URL.'/?enc_dl_action=process&file='.$download_key;
	}
	else{
		$script_location = get_option('eStore_download_script');
		$encrypted_download_url = $script_location.'download.php?file='.$download_key;
	}                                
	if($eStore_auto_shorten_url){
		$encrypted_download_url = wp_eStore_shorten_url($encrypted_download_url);
	}
	return $encrypted_download_url;
}

function eStore_generate_download_key($product_id,$url='')
{
	$time = time();
	$random_key = get_option('eStore_random_code');	
	if(empty($url))
	{
		$download = $product_id.'|'.$time;
	}
	else
	{
		$download = $product_id.'|'.$time.'|'.$url;
	}
	$download_key = rawurlencode(base64_encode(RC4Crypt::encrypt($random_key,$download)));
	return $download_key;
}

function eStore_check_stamping_flag_and_generate_download_key($retrieved_product,$product_id,$url='',$payment_data='',$product_name='')
{
	if($retrieved_product->use_pdf_stamper == 1 && !empty($payment_data))
	{
		if(WP_ESTORE_STAMP_PDF_FILE_AT_DOWNLOAD_TIME === '1'){//Check if file sould be stamped at download time option is enabled
			$download_key = eStore_generate_download_key($product_id,$url);
			return $download_key;
		}
		
		if(!empty($url)){
			$src_file = $url;
		}else{
			$src_file = $retrieved_product->product_download_url;
		}		
		eStore_payment_debug('Stamping request for product ID: '.$product_id,true);
		//Check if it is a multiple file URL product
		$multi_file_product = false;
		$product_urls = explode(',',$retrieved_product->product_download_url);
		if(sizeof($product_urls)>1){
			$multi_file_product = true;
		}
		$txn_id = $payment_data['txn_id'];
		
		$force_restamp = false;
		if(isset($payment_data['force_restamp']) && ($payment_data['force_restamp'] == '1')){//Don't lookup cached copy
			eStore_payment_debug('Force restamping is enabled in the request.',true);
			$force_restamp = true;
		}

		if(!empty($txn_id) && !$multi_file_product && !$force_restamp)//check for an already stamped copy of the file to minimize server load
		{				
			eStore_payment_debug('Checking for a reference to the stamped copy of the file for this transaction before invoking another stamp request:'.$txn_id,true);			
			$cond = " txn_id = '$txn_id'";
			$result = WP_eStore_Db_Access::findAll(WP_ESTORE_DOWNLOAD_LINKS_TABLE_NAME, $cond); 
			if($result)
			{
				eStore_payment_debug('Found a reference to the stamped copy of a file. Performing an indepth check...',true);
				$random_key = get_option('eStore_random_code');
				foreach($result as $download_item)
				{
					$download_key = $download_item->download_key;					
					eStore_payment_debug('Decrypting download key: '.$download_key,true);
					$decrypted_data = RC4Crypt::decrypt($random_key,base64_decode(rawurldecode($download_key)));	
					list($product_id_of_stamped_file,$timestamp,$stamped_file_url) = explode('|',$decrypted_data);
					eStore_payment_debug('Decrypted data... Product ID: '.$product_id_of_stamped_file.', Stamped File URL:'.$stamped_file_url,true);
					if($product_id == $product_id_of_stamped_file)
					{
						eStore_payment_debug('Product IDs match. Using the existing stamped copy of the file.',true);
						$new_download_key = eStore_generate_download_key($product_id_of_stamped_file,$stamped_file_url);	
						eStore_payment_debug('New Download Key: '.$new_download_key,true);		
						return $new_download_key;
					}					
				}
				eStore_payment_debug('Product IDs do not match. Need to proceed with a fresh stamping.',true);
			}	
			else{
				eStore_payment_debug('Could not find a reference to the stamped copy of a file',true);
			}
		}
				
		$stamped_file_url = eStore_stamp_pdf_file($payment_data,$src_file,$product_name);
		if($stamped_file_url === 'Error!'){
			eStore_payment_debug('PDF Stamping did not finish correctly!',false);
			$download_key = "Error with PDF stamping (Error code: PDF01). Perform a manual stamping and make sure the PDF stamper is working on your server.";
			return $download_key;
		}		
		$download_key = eStore_generate_download_key($product_id,$stamped_file_url);			
	}
	else
	{
		$download_key = eStore_generate_download_key($product_id,$url);
	}	
	return $download_key;
}

function eStore_stamp_pdf_file($payment_data,$src_file,$product_name='')
{	
	eStore_payment_debug('Stamping the PDF file if WP PDF stamper plugin is installed.',true);
	$src_file = trim($src_file);
	$pdf_stamper_plugin_url = get_option('WP_PDF_STAMP_URL');
	$file_type_pdf = preg_match_all('/^.*\.(pdf)$/i', $src_file, $arr, PREG_PATTERN_ORDER);
	eStore_payment_debug('Source file type check... is file type PDF return value: '.$file_type_pdf,true);
	if($file_type_pdf != 1)
	{		
		eStore_payment_debug('Source file is not a PDF file so no stamping necessary for this file!', true);
		return $src_file;
	}
	if (!empty($pdf_stamper_plugin_url))
	{			
		if(empty($payment_data))
		{
			eStore_payment_debug('Payment data is empty! Cannot stamp a PDF file without customer information in the payment data!',false);
			return "Error!";
		}
		if(empty($src_file))	
		{
			eStore_payment_debug('Source file is empty! Cannot stamp a PDF file without source file data!',false);
			return "Error!";
		}	
		eStore_payment_debug("Source File URL is: ".$src_file,true);
		
	    $postURL = $pdf_stamper_plugin_url."/api/stamp_api.php";
	    // The Secret key
	    $secretKey = get_option('wp_pdf_stamp_secret_key');	
	    // The site URL
	    $domainURL = $_SERVER['SERVER_NAME'];	    
	    	    
	    // prepare the data
	    $data = array ();
	    $data['secret_key'] = $secretKey;
	    $data['requested_domain'] = $domainURL;
	    $data['source_file'] = $src_file;
	    if(empty($payment_data['customer_name']))
	    {
	    	$data['customer_name'] = $payment_data['first_name']." ".$payment_data['last_name'];
	    }
	    else
	    {
	    	$data['customer_name'] = $payment_data['customer_name'];
	    }
	    $data['customer_email'] = $payment_data['payer_email'];
	    $data['customer_phone'] = $payment_data['contact_phone'];
	    if(empty($payment_data['address']))
	    {
	    	$data['customer_address'] = $payment_data['address_street'].", ".$payment_data['address_city'].", ".$payment_data['address_state']." ".$payment_data['address_zip'].", ".$payment_data['address_country'];	    	
	    }	
	    else
	    {
	    	$data['customer_address'] = $payment_data['address'];
	    }    
	    isset($payment_data['payer_business_name'])?$data['customer_business_name'] = $payment_data['payer_business_name']:$data['customer_business_name']='';
	    $data['transaction_id'] = $payment_data['txn_id'];
            $data['product_name'] = $product_name;
	    
	    // use the appropriate stamp API to stamp the file
		if(WP_PDF_STAMP_DO_NOT_USE_CURL == '0')
		{
			eStore_payment_debug("Attempting to stamp the PDF file using CURL API",true);
	    	$returnValue = eStore_post_data_using_curl($postURL,$data);			    						
		}
		else if(function_exists('pdf_stamper_stamp_internal_file'))
		{			
			eStore_payment_debug("Attempting to stamp the PDF file using the internal stamping API. PDF Stamper version: ".WP_PDF_STAMP_VERSION,true);
			$line_distance = "";
			$line_space = "";
			$footer_text = "";
			$additional_params = array();
			$additional_params['transaction_id'] = $payment_data['txn_id'];
                        $additional_params['product_name'] = $product_name;
			
			if (version_compare(WP_PDF_STAMP_VERSION, '4.1.2') >= 0) {//send transaction_id via additional param data				 			
    			$returnValue = pdf_stamper_stamp_internal_file($src_file,$data['customer_name'],$data['customer_email'],$data['customer_phone'],$data['customer_address'],$data['customer_business_name'],$line_distance,$line_space,$footer_text,$additional_params);
			}
			else{
				$returnValue = pdf_stamper_stamp_internal_file($src_file,$data['customer_name'],$data['customer_email'],$data['customer_phone'],$data['customer_address'],$data['customer_business_name'],$line_distance,$line_space,$footer_text);
			}
			
		}
		else{
			eStore_payment_debug("Attempting to stamp the PDF file using CURL API",true);
	    	$returnValue = eStore_post_data_using_curl($postURL,$data);					
		}
		
	    list ($status, $value) = explode ("\n", $returnValue);
        $message = "";
        if(strpos($status,"Success!") !== false)
        {
        	$file_url = trim($value);	        	
        	$message .= "File stamped successfully! Stamped file URL: ".$file_url;
        	eStore_payment_debug($message,true);
        }
        else
        {
        	$message .= "An error occured while trying to stamp the file! Error details: ".$value;
        	eStore_payment_debug($message,false);
        	return "Error!";
        }		    									
	}
	else
	{
		eStore_payment_debug('PDF stamper plugin is not installed!',false);
	}	
	return $file_url;
}

function eStore_get_ngg_image_url($pictureID,$item_name)
{
	$eStore_auto_shorten_url = WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS;
	$script_location = get_option('eStore_download_script');
	$image  = nggdb::find_image($pictureID);
	$imageUrl = $image->imageURL;
	eStore_payment_debug('Nextgen gallery raw image URL for picture ID: '.$pictureID.' is: '.$imageUrl,true);
    $product_id = get_option('eStore_ngg_template_product_id');
    eStore_payment_debug('Generating download key for Nextgen gallery image using product ID: '.$product_id,true);
    $download_key = eStore_generate_download_key($product_id,$imageUrl);
    $encrypted_download_url = eStore_construct_raw_encrypted_dl_url($download_key);
    $download_link .= "\n".stripslashes($item_name)." - ".$encrypted_download_url;
    eStore_register_link_in_db('',$download_key,$download_link,'','','',0,'');
    return $download_link;
}
function eStore_get_ngg_image_url_html($pictureID,$item_name)
{
	$eStore_auto_shorten_url = WP_ESTORE_AUTO_SHORTEN_DOWNLOAD_LINKS;
	$script_location = get_option('eStore_download_script');
	$image  = nggdb::find_image($pictureID);
	$imageUrl = $image->imageURL;
	$product_id = get_option('eStore_ngg_template_product_id');
	eStore_payment_debug('Generating download key for Nextgen gallery image using product ID: '.$product_id,true);
	$download_key = eStore_generate_download_key($product_id,$imageUrl);
	$encrypted_download_url = eStore_construct_raw_encrypted_dl_url($download_key);
	$raw_download = '<a href="'.$encrypted_download_url.'">'.$encrypted_download_url.'</a>';
	$download_link = "<br /><strong>".$item_name."</strong> - ".$raw_download;	
	eStore_register_link_in_db('',$download_key,$encrypted_download_url,'','','',0,'');
					        	       
    return $download_link;
}

function eStore_handle_refund($payment_data)
{
    eStore_payment_debug('Handling refund request...',true);
    do_action('eStore_transaction_refund',$payment_data);
    global $wpdb,$sales_table_name,$customer_table_name;
    $parent_txn_id = $payment_data['parent_txn_id'];    
    $emailaddress = $payment_data['payer_email'];    
    $clientdate = (date ("Y-m-d"));
    $clienttime	= (date ("H:i:s"));
    $product_id = $payment_data['item_number'];
    $sale_price = $payment_data['mc_gross'];
    
    //Check if this txn exists
    $resultsets = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE txn_id = '$parent_txn_id'", OBJECT);
    if(!$resultsets){
        eStore_payment_debug('Could not find the record associated with this transaction in the database.', false);
        return;
    }
    
    if (function_exists('wp_aff_platform_install'))
    {
    	if (function_exists('wp_aff_handle_refund'))
    	{
            eStore_payment_debug('Reverse the commmission for this payment. Parent Txn ID: '.$parent_txn_id,true);
            wp_aff_handle_refund($parent_txn_id);
    	}        
    }
    if (get_option('eStore_enable_wishlist_int'))
    {
    	// Deactivate wishlist member's account
    	wl_handle_subsc_cancel($payment_data,true);    	
    }
    if (function_exists('wp_eMember_install'))
    {
    	// deactivate eMember account if applicable
    	eMember_handle_subsc_cancel($payment_data,true);
    }
    
    $total_price = 0;
    foreach($resultsets as $resultset)
    {
        $total_price = $total_price + $resultset->sale_amount;
    }
    if($total_price > 0)
    {
        $refunded_amt = $sale_price * -1;
        if($total_price < $refunded_amt){
            $sale_price = $total_price * -1;
            $sale_price = number_format($sale_price, 2, '.', '');
        }
    }
    eStore_payment_debug('Updating sales database table with the refund amount: '.$sale_price,true);	
    $updatedb2 = "INSERT INTO $sales_table_name (cust_email, date, time, item_id, sale_price) VALUES ('$emailaddress','$clientdate','$clienttime','$product_id','$sale_price')";
    $results = $wpdb->query($updatedb2);
    if(get_option('eStore_auto_customer_removal'))
    {    	
    	//$editingcustomer = $wpdb->get_row("SELECT * FROM $customer_table_name WHERE txn_id = '$parent_txn_id'", OBJECT);
        $updatedb = "DELETE FROM $customer_table_name WHERE txn_id='$parent_txn_id'";
        $results = $wpdb->query($updatedb);
    }
}

function is_paypal_recurring_payment($payment_data)
{
    $recurring_payment = false;
    global $wpdb;
    $customer_table_name = WP_ESTORE_CUSTOMER_TABLE_NAME;

    $transaction_type = $payment_data['txn_type'];
    if ($transaction_type == "recurring_payment")
    {
        $recurring_payment = true;
    }
    else if ($transaction_type == "subscr_payment")
    {
        $email = $payment_data['payer_email'];
        $item_number = $payment_data['item_number'];
        $subscr_id = $payment_data['subscr_id'];
        eStore_payment_debug('Is recurring payment check debug data: '.$email."|".$item_number."|".$subscr_id, true);
        
        $result = $wpdb->get_row("SELECT * FROM $customer_table_name WHERE subscr_id = '$subscr_id'", OBJECT);
        if(isset($result)){
            eStore_payment_debug('This subscr_id exists in the database. Recurring payment check flag value is true.', true);
            $recurring_payment = true;
            return $recurring_payment;
        }
        
        $customer_exists = $wpdb->get_row("SELECT * FROM $customer_table_name WHERE email_address = '$email' and purchased_product_id = '$item_number' ORDER by id DESC", OBJECT);
        eStore_payment_debug('Is recurring payment check customer data: '.$customer_exists->email_address."|".$customer_exists->subscr_id, true);
        if($customer_exists)
        {
            if(empty($customer_exists->subscr_id))
            {
                $recurring_payment = true;
            }
            else if($customer_exists->subscr_id == $subscr_id)
            {
                $recurring_payment = true;
            }
        }
    }
    if($recurring_payment){
        eStore_payment_debug('Recurring payment check flag value is true.', true);
    }
    return $recurring_payment;
}
function eStore_is_package_product($retrieved_product)
{
	$download_url_field = $retrieved_product->product_download_url;
	$product_ids = explode(',',$download_url_field);
	$package_product = false;		
	foreach($product_ids as $id)
	{
		if(is_numeric($id))
		{
			$package_product = true;
		}
	}
	return $package_product;		
}
function eStore_is_ngg_pid_present($name)
{
	$raw_pid_string = eStore_get_string_between($name,'[',']');
	if(!empty($raw_pid_string))
	{
		$pid_values = explode(":",$raw_pid_string);
		if($pid_values[0]=="pid")
			return $pid_values[1];//return the PID			
	}
	return -1;//no PID present
}

function eStore_POST_IPN_data_to_url($data,$post_url='',$cart_items='')
{
	//POST IPN Data to corresponding scripts if specified in the settings
	if(empty($post_url)){
		//See if the external URL is specified in the settings
		$ipn_external_post_url = get_option('eStore_third_party_ipn_post_url');
	}
	else{
		$ipn_external_post_url = $post_url;
	}
	
	if(!empty($ipn_external_post_url))
	{
            global $wpdb;
            $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;

            if(!empty($cart_items)){
                    //add the cart item details to the post data array
                    $num_cart_items = count($cart_items);
                    if($num_cart_items>1){
                            $i = 1;
                            foreach ($cart_items as $current_cart_item){
                                    $data_key = 'item_name'.$i;
                                    $data[$data_key] = trim($current_cart_item['item_name']);

                                    $data_key = 'item_number'.$i;
                                    $data[$data_key] = trim($current_cart_item['item_number']);					
                                    $i++;
                            }
                    }
                    else{
                            $data_key = 'item_name';
                            $data[$data_key] = trim($cart_items[0]['item_name']);
                            $data_key = 'item_number';
                            $data[$data_key] = trim($cart_items[0]['item_number']);
                    }
            }
                       
            if(empty($data['txn_type'])){
                $data['txn_type'] = "subscr_payment";
            }
            
            $prod_id = $data['item_number'];
            if(empty($prod_id)){//Lets try one more time to add the item_number from IPN data (if any)
                $prod_id = $data['item_number1'];
            }
            if(!empty($prod_id)){//Add some extra product specific data for single item checkout
                $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$prod_id'", OBJECT);
                $data['ref_text'] = $ret_product->ref_text;
            }
            
            $extra_data = "";
            $data = apply_filters('eStore_before_posting_ipn_data_filter', $data, $extra_data);
            eStore_payment_debug('Posting IPN data to :'.$ipn_external_post_url,true);
            $retVal = eStore_post_data_using_curl($ipn_external_post_url, $data);
            if($retVal == "NO CURL"){
                    eStore_payment_debug('Could not post IPN. CURL library is not installed on this server!', false);
            }
            else{
                    eStore_payment_debug('IPN values posted successfully. Return value: '.$retVal, true);
            }
	}	
}
function eStore_check_and_generate_license_key($retrieved_product,$payment_data='')
{
	global $wpdb,$products_table_name;
	$product_license_data = "";
	$package_product = eStore_is_package_product($retrieved_product);
	if($package_product)
	{
		eStore_payment_debug('Checking license key generation for package product.',true);
		$product_ids = explode(',',$retrieved_product->product_download_url);
        foreach($product_ids as $id)
		{
	        $id = trim($id);
	        $retrieved_product_for_specific_id = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	        $product_license_data .= eStore_check_licese_key_flag_and_generate_key($retrieved_product_for_specific_id,$payment_data);   
		}		
	}
	else 
	{
		eStore_payment_debug('Checking license key generation for single product.',true);
		$product_license_data .= eStore_check_licese_key_flag_and_generate_key($retrieved_product,$payment_data);
	}
	return $product_license_data;
}
function eStore_check_licese_key_flag_and_generate_key($retrieved_product,$payment_data)
{
	if($retrieved_product->create_license == 1)
	{
		$license_key = eStore_generate_license_key($payment_data);
		$product_license_data .= "\n".$retrieved_product->name." License Key: ".$license_key;
	}	
	return $product_license_data;
}
function eStore_generate_license_key($payment_data='')
{
	if(function_exists('wp_lic_manager_install'))
	{
	    //Post URL
	    $postURL = get_option('eStore_lic_mgr_post_url');
	    // the Secret Key
	    $secretKey = get_option('eStore_lic_mgr_secret_word');
	    // prepare the data
	    $data = array ();
	    $data['secret_key'] = $secretKey;
	    $data['first_name'] = $payment_data['first_name'];
	    $data['last_name'] = $payment_data['last_name'];
	    $data['email'] = $payment_data['payer_email'];
	    $data['company_name'] = $payment_data['payer_business_name'];
	    $data['txn_id'] = $payment_data['txn_id'];
	    $returnValue = eStore_post_data_using_curl($postURL,$data);
		
	    list ($status, $msg, $additionalMsg) = explode ("\n", $returnValue);
        $message = "";
        if(strpos($status,"Success") !== false)
        {
        	$license_key = trim($additionalMsg);	        	
        	$message .= "License Key created successfully! License Key: ".$license_key;
        	eStore_payment_debug($message,true); 
        	return $license_key;       	
        }
        else
        {
        	$message .= "An error occured while trying to create the license key! Error details: ".$msg;
        	eStore_payment_debug($message,false);
        	return "Error";
        }	    
	}
    else
    {
    	eStore_payment_debug('WP License Manager plugin is not installed!',false);
    	return "Error";
    }	    
}
function eStore_register_link_in_db($creation_time='',$download_key='',$download_item='',$download_limit_count,$download_limit_time,$download_limit_ip='',$access_count=0,$txn_id='')
{
	if(empty($creation_time))
	{
		$creation_time = date ("Y-m-d H:i:s");//current_time('mysql');
	}
	if(empty($download_key))
	{
		//Download key cannot be empty
		return false;	
	}		
	if(empty($download_limit_count))
	{
		$download_limit_count = get_option('eStore_download_url_limit_count');
		if(empty($download_limit_count))
		{
			$download_limit_count = 999;
		}
	}
	if(empty($download_limit_time))
	{
		$download_limit_time = get_option('eStore_download_url_life');
	}
	if(empty($download_limit_ip))
	{
		$download_limit_ip = $_SERVER['REMOTE_ADDR'];
	}
    //Add to the download link to the database   
    global $wpdb;
    $download_key = rawurldecode($download_key);//str_replace('%2B','+', $download_key ); 
    $fields = array();
    $fields['creation_time'] = $creation_time;
    $fields['download_key'] = $download_key;
    $fields['download_item'] =  esc_sql($download_item);
    $fields['download_limit_count'] = $download_limit_count;
    $fields['download_limit_time'] = $download_limit_time;
    $fields['download_limit_ip'] = $download_limit_ip;
    $fields['access_count'] = $access_count;
    $fields['txn_id'] = $txn_id;
    $updated = WP_eStore_Db_Access::insert(WP_ESTORE_DOWNLOAD_LINKS_TABLE_NAME, $fields); 	
    return true;	
}

function record_sales_data($payment_data,$cart_items)
{
    eStore_payment_debug('Updating Products and Customers Database Tables with Sales Data.',true); 
    global $wpdb;
    $products_table_name = WP_ESTORE_DB_PRODUCTS_TABLE_NAME;
    $customer_table_name = WP_ESTORE_DB_CUSTOMERS_TABLE_NAME;
    $sales_table_name = WP_ESTORE_DB_SALES_TABLE_NAME;
    
	$firstname = $payment_data['first_name'];
	$lastname = $payment_data['last_name'];
	$emailaddress = $payment_data['payer_email'];            
    $transaction_id = $payment_data['txn_id'];
    $clientdate = (date ("Y-m-d"));
    $clienttime	= (date ("H:i:s"));		    
    $address = esc_sql(stripslashes($payment_data['address']));
    $phone = $payment_data['phone'];
    $coupon_code_used = esc_sql($payment_data['coupon_used']);
    $eMember_id = esc_sql($payment_data['eMember_userid']);
	if (function_exists('wp_eMember_install') && empty($eMember_id)){//eMember purchase history additional check
    	eStore_payment_debug('No eMember ID was passed so the user was not logged in. Quering member database to see if a user account exists for: '.$emailaddress,true);
    	$members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
    	$query_emem_db = $wpdb->get_row("SELECT member_id FROM $members_table_name WHERE email = '$emailaddress'", OBJECT);
		if($query_emem_db){
			$eMember_id = $query_emem_db->member_id;
			eStore_payment_debug('Found a user account with the purchaser email address. adding this purchase to account ID: '.$eMember_id,true);			
		}
    }  
    $eMember_username = $eMember_id;
      
    $subscr_id = $payment_data['subscr_id'];
    $customvariables = get_custom_var($payment_data['custom']);
	$customer_ip = $customvariables['ip'];
	if(empty($customer_ip)){$customer_ip = "No information";}
	$product_key_data = $payment_data['product_key_data'];
	if(empty($product_key_data)){$product_key_data = "";}
	$notes = "";
    
    if(!empty($payment_data['status'])){$status = $payment_data['status'];}
    else if($payment_data['gateway']=='manual'){$status = "Unpaid";}	
    else{$status = "Paid";}
    		        
    $counter = 0;
	foreach ($cart_items as $current_cart_item)
	{		
			$cart_item_data_num = $current_cart_item['item_number'];
			$cart_item_data_name = $current_cart_item['item_name'];
			$cart_item_qty = $current_cart_item['quantity'];
			$key=$cart_item_data_num;
			$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$key'", OBJECT);
            $current_product_id = $cart_item_data_num;

            eStore_payment_debug('Product ID: '.$cart_item_data_num.'.Current available copies value: '.$retrieved_product->available_copies.' Sales count value: '.$retrieved_product->sales_count,true);
            $new_available_copies = "";
            if (is_numeric($retrieved_product->available_copies))
            {
                $new_available_copies = ($retrieved_product->available_copies - $cart_item_qty);
            }
            $new_sales_count = ($retrieved_product->sales_count + $cart_item_qty);
			eStore_payment_debug('New available copies value: '.$new_available_copies.' New sales count value: ' .$new_sales_count,true);
            
            $updatedb = "UPDATE $products_table_name SET available_copies = '$new_available_copies', sales_count = '$new_sales_count' WHERE id='$current_product_id'";
			$results = $wpdb->query($updatedb);
			
			// Update the Customer table	        
		    $product_name = esc_sql(stripslashes($cart_item_data_name));
		    $sale_price = $current_cart_item['mc_gross'];
	                
            $updatedb = "INSERT INTO $customer_table_name (first_name, last_name, email_address, purchased_product_id,txn_id,date,sale_amount,coupon_code_used,member_username,product_name,address,phone,subscr_id,purchase_qty,ipaddress,status,serial_number,notes) VALUES ('$firstname', '$lastname','$emailaddress','$current_product_id','$transaction_id','$clientdate','$sale_price','$coupon_code_used','$eMember_username','$product_name','$address','$phone','$subscr_id','$cart_item_qty','$customer_ip','$status','$product_key_data','$notes')";
            $results = $wpdb->query($updatedb);

			$updatedb2 = "INSERT INTO $sales_table_name (cust_email, date, time, item_id, sale_price) VALUES ('$emailaddress','$clientdate','$clienttime','$current_product_id','$sale_price')";
			$results = $wpdb->query($updatedb2);
    }
    //Update the coupons table if coupon was used
    $coupon_code = $customvariables['coupon'];
	if(!empty($coupon_code))
	{
		$coupon_table_name = WP_ESTORE_COUPON_TABLE_NAME;
		$ret_coupon = $wpdb->get_row("SELECT * FROM $coupon_table_name WHERE coupon_code = '$coupon_code'", OBJECT);
		if ($ret_coupon)
		{
			$redemption_count = $ret_coupon->redemption_count + 1;
			$updatedb = "UPDATE $coupon_table_name SET redemption_count = '$redemption_count' WHERE coupon_code='$coupon_code'";
			$results = $wpdb->query($updatedb);            	
		}        	
	}   
    eStore_payment_debug('Products, Customers, Sales and Coupons Database Tables Updated.',true);
    do_action('eStore_product_database_updated_after_payment',$payment_data,$cart_items);//eStore's action after post payment product database is update
}

function eStore_aff_award_commission($payment_data,$cart_items,$customReferrer='')
{
	eStore_payment_debug('===> Start of Affiliate Commission Calculation <===',true);
	eStore_payment_debug('Checking if the WP Affiliate Platform Plugin is installed.',true);
	if (eStore_affiliate_capability_exists())
	{
		global $wpdb;
		$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
		$affiliates_table_name = WP_AFFILIATE_TABLE_NAME;
		$aff_sales_table = WP_AFFILIATE_SALES_TABLE_NAME;
					
		eStore_payment_debug('WP Affiliate Platform is installed, checking commission related details...',true);	
		$customvariables = get_custom_var($payment_data['custom']);	
		if(!empty($customReferrer))//Maybe the author profit sharing feature is being used
		{
			$referrer = $customReferrer;
			eStore_payment_debug('Revenue sharing feature is being used',true);
		}
		else
		{			
			$referrer = $customvariables['ap_id'];
		}
		
		// Check affiliate leads table for referrer if enabled
		if(WP_ESTORE_CHECK_LEADS_TABLE_FOR_AFFILIATE_REFERRAL_CHECK === '1')
		{
			if(function_exists('wp_aff_get_referrer_from_leads_table_for_buyer')){
				$buyer_email = $payment_data['payer_email'];
				$referrer = wp_aff_get_referrer_from_leads_table_for_buyer($buyer_email);
				eStore_payment_debug('Referrer value returned from the leads table check is:'.$referrer,true);
			}
			else{
				eStore_payment_debug('You need to update the affiliate plugin to use this feature',false);
			}
		}		
		// Check if an eMember user with a referrer has purchased
		$eMember_member_id = $customvariables['eMember_id'];
		if(WP_ESTORE_CHECK_EMEMBER_REFERRER_FOR_AFFILIATE_ID === '1' && !empty($eMember_member_id)){
			eStore_payment_debug('This purchase was made by a member with eMember ID: '.$eMember_member_id.' Looking to see if a referrer value exists in this member profile...',true);
			$eMember_resultset = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id=' . esc_sql($eMember_member_id));
			$member_referrer = trim($eMember_resultset->referrer);
			eStore_payment_debug('Attached referrer value with this member profile is: '.$member_referrer,true);
			if(!empty($member_referrer)){
				$referrer = $member_referrer;
				eStore_payment_debug('Setting the referrer value of this sale to Affiliate ID: '.$referrer,true);
			}			
		}
		
		if (!empty($referrer))
		{
			eStore_payment_debug('The referrer for this sale is:'.$referrer,true);
			$c_id = $customvariables['c_id'];//campaign id (if any)
			$txn_id = $payment_data['txn_id'];
			$buyer_email = $payment_data['payer_email'];
			$clientip = $customvariables['ip'];
			eStore_payment_debug('Additional debug data. Txn ID: '.$txn_id.' Campign ID: '.$c_id.' Buyer Email: '.$buyer_email,true);

			//Check if no commission is to be awarded for self purchase
			if(WP_ESTORE_NO_COMMISSION_FOR_SELF_PURCHASE == '1')
			{
				//check if the referrer is the buyer
				if(function_exists('wp_aff_check_if_buyer_is_referrer')){
					if(wp_aff_check_if_buyer_is_referrer($referrer,$buyer_email)){
						eStore_payment_debug('The buyer ('.$buyer_email.') is the referrer ('.$referrer.') so this sale is NOT ELIGIBLE for generating any commission.',true);
						return true;
					}
					else{
						eStore_payment_debug('The buyer is not the referrer so this sale is eligible for generating commission.',true);
					}
				}
				else{
					eStore_payment_debug('You need to update your affiliate plugin before you can use the No commission on self purchase feature.',false);
				}
			}
								
			$resultset = $wpdb->get_results("SELECT * FROM $aff_sales_table WHERE txn_id = '$txn_id'", OBJECT);
			if($resultset)
			{
				//Commission for this transaction has already been awarded so no need to do anything.
				eStore_payment_debug('The database record shows that the commission for this transaction has already been awarded so no need to do anything.',true);
				eStore_payment_debug('===> End Affiliate Commission Check <===',true);
				return;
			}	

			//Check if the "DO not award commission if coupon is used" feature is in use
			if(get_option('eStore_aff_no_commission_if_coupon_used')!='')
			{
				$coupon = $customvariables['coupon'];
				if(!empty($coupon)){
					eStore_payment_debug('Do Not Award Commission if Coupon Used feature is enabled. Commission will not be awarded for this transaction since a coupon code has been used. Coupon: '.$coupon,true);
					eStore_payment_debug('===> End Affiliate Commission Check <===',true);
					return;
				}
				eStore_payment_debug('No coupon used for this transaction',true);
			}

			$wp_aff_affiliates_db = $wpdb->get_row("SELECT * FROM $affiliates_table_name WHERE refid = '$referrer'", OBJECT);
			$commission_level = $wp_aff_affiliates_db->commissionlevel;
			$second_tier_referrer = $wp_aff_affiliates_db->referrer;
			$second_tier_commission_level = 0;
			if(!empty($second_tier_referrer)){//This affiliate has a 2nd tier referrer
				eStore_payment_debug('Retrieving the 2nd tier affiliate profile.',true);
				$second_tier_aff = $wpdb->get_row("SELECT * FROM $affiliates_table_name WHERE refid = '$second_tier_referrer'", OBJECT);
				if(!empty($second_tier_aff->sec_tier_commissionlevel)){
					$second_tier_commission_level = $second_tier_aff->sec_tier_commissionlevel;
					eStore_payment_debug('The 2nd tier affiliate ('.$second_tier_referrer.') has a profile specific 2nd tier commission level. Commission level is: '.$second_tier_commission_level,true);
				}else{
					$second_tier_commission_level = get_option('wp_aff_2nd_tier_commission_level');
				}
			}
			
			$counter = 1;
			$commission_amount = 0;
			$product_comm_amount = 0;
			$second_tier_commission_amount = 0;
			$purchased_items = '';	
			global $eStore_affiliate_individual_product_commisions;		
		    foreach ($cart_items as $current_cart_item)
		    {
		    	eStore_payment_debug('Processing Commission for : '.$current_cart_item['item_name'],true);
		    	
       			$cart_item_number = $current_cart_item['item_number'];
       			//The total item price includes the (individual item price * quantity)
       			$total_item_price = $current_cart_item['mc_gross'] - $current_cart_item['mc_shipping'];
       			$item_qty = $current_cart_item['quantity'];
       			eStore_payment_debug('Total Price of the currently processing item : '.$total_item_price,true);
                $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$cart_item_number'", OBJECT);
                
                if (!empty($retrieved_product->commission))
                {
                	eStore_payment_debug('Using product specific commission specified in eStore',true);
                	if (get_option('wp_aff_use_fixed_commission'))
	                {
	                	eStore_payment_debug('Using fixed commission rate for this product specific commission',true);
	                	//Give fixed commission from the product's specified level
	                    $product_comm_amount = $item_qty * $retrieved_product->commission;
	                    
	                    //Award fixed commission for 2nd tier from the product's specified level
	                    if (!empty($retrieved_product->tier2_commission)){
	                    	$product_second_tier_comm_amt = $item_qty * $retrieved_product->tier2_commission;
	                    }
	                }
		            else
		            {
		            	eStore_payment_debug('Using % based commission rate for this product specific commission',true);
		            	//Award % commission from the product's specified level
		            	//The total item price includes the (individual item price * quantity)
		            	$product_comm_amount = ($total_item_price*$retrieved_product->commission/100);
		            	//Award % commission for 2nd tier from the product's specified level
	                    if (!empty($retrieved_product->tier2_commission)){
	                    	$product_second_tier_comm_amt = $total_item_price * ($retrieved_product->tier2_commission)/100;
	                    }		            	
		            }                	
                }
                else
                {                	
                	if ($retrieved_product->commission == "0")
	                {
	                	$product_comm_amount = 0;
	                	$product_second_tier_comm_amt = 0;
	                	eStore_payment_debug('This product will not generate any commission as the product specific commission for this product has been specified as 0',true);
	                }
	                else
	                {
	                	eStore_payment_debug('Using commission rate from affiliate profile',true);
	                	if (get_option('wp_aff_use_fixed_commission'))
	                    {
	                    	eStore_payment_debug('Using fixed commission rate for this commission. Qty:'.$item_qty.', Fixed commission level:'.$commission_level,true);
	                    	//Give fixed commission from the affiliate's specified level
	                        $product_comm_amount = $item_qty * $commission_level;
	                    	//Award fixed commission for 2nd tier from the affiliate's specified level
	                    	$product_second_tier_comm_amt = $item_qty * $second_tier_commission_level;                        
	                    }
	                    else
	                    {
	                    	eStore_payment_debug('Using % based commission rate for this commission. Qty:'.$item_qty.', Total price:'.$total_item_price.', Commission level:'.$commission_level,true);
	                    	//The total item price includes the (individual item price * quantity)
	                    	$product_comm_amount = $total_item_price * ($commission_level/100);
	                    	//Award fixed commission for 2nd tier from the affiliate's specified level
	                    	$product_second_tier_comm_amt =  $total_item_price * (($second_tier_commission_level)/100);   	                    	
	                    }
	                }
                }
                $commission_amount = $commission_amount + $product_comm_amount;
                $second_tier_commission_amount = $second_tier_commission_amount + $product_second_tier_comm_amt;
                
                //Save the individual product commission details for later use
                $current_cart_item['product_commission'] = $product_comm_amount;  
                $current_cart_item['product_commission_2nd_tier'] = $product_second_tier_comm_amt;    
                $current_cart_item['product_commission_total']  = $product_comm_amount + $product_second_tier_comm_amt;        
                array_push($eStore_affiliate_individual_product_commisions, $current_cart_item);
		        
                if($counter>1){
                	$purchased_items .= ", ";
                }                
                $purchased_items .= $cart_item_number;
                $counter++;
            }
            
            $commission_amount = round($commission_amount,2);
            $second_tier_commission_amount = round($second_tier_commission_amount,2);
            $sale_amount = $payment_data['mc_gross'];
            $clientdate = (date ("Y-m-d"));
            $clienttime	= (date ("H:i:s"));            
            $txn_id = $payment_data['txn_id'];
            $item_id = $purchased_items;
            $buyer_name = $payment_data['first_name']." ".$payment_data['last_name'];
            $aff_version = get_option('wp_aff_platform_version');
            
			//Check if using the satellite affiliate plugin is being used then direct commision there
			if(defined('SATELLITE_WP_AFFILIATE_PLATFORM_VERSION')){//WP_ESTORE_REDIRECT_COMMISSION_USING_SATELLITE_AFFILIATE_PLUGIN
				eStore_payment_debug('Satellite affiliate plugin is installed. Redirecting commission using the satellite affiliate plugin.',true);
				if(function_exists('satellite_aff_perform_remote_sale_tracking_eStore')){
					eStore_payment_debug('Redirecting commission using the direct commission awarding method. Commission amount: '.$commission_amount,true);
					satellite_aff_perform_remote_sale_tracking_eStore($commission_amount,$sale_amount,$referrer,$txn_id,$item_id,$buyer_email,$clientip,$buyer_name);
				}
				else if(function_exists('satellite_aff_perform_remote_sale_tracking')){									
					satellite_aff_perform_remote_sale_tracking($sale_amount,$referrer,$txn_id,'',$buyer_email,$clientip);	
				}		
				return true;
			}
            
                        eStore_payment_debug("WP Affiliate plugin version is: ".$aff_version,true);
                        
			// Check if the commission per transaction option is enabled
			if(get_option('eStore_aff_enable_commission_per_transaction')!='')
			{
                            eStore_payment_debug('Commission per transaction option is enabled so the commission will be awarded for the full transaction rather than on a per item basis',true);
                            if (get_option('wp_aff_use_fixed_commission'))
                            {
                                eStore_payment_debug('Using fixed commission model... Awarding fixed affiliate commission',true);	            	
                                $updatedb = "INSERT INTO $aff_sales_table (refid,date,time,browser,ipaddress,payment,sale_amount,txn_id,item_id,buyer_email,campaign_id,buyer_name) VALUES ('$referrer','$clientdate','$clienttime','','$clientip','$commission_amount','$sale_amount','$txn_id','$item_id','$buyer_email','$c_id','$buyer_name')";
                                $results = $wpdb->query($updatedb);
                                eStore_payment_debug('===> End Affiliate Commission Check <===',true);	
                                return;					
                            }
                            else{
                                //For percentage based commission there is no difference between per transaction commission amount and the per item commission amount
                            }
			}
		    
                        //% based commission
                        $updatedb = "INSERT INTO $aff_sales_table (refid,date,time,browser,ipaddress,payment,sale_amount,txn_id,item_id,buyer_email,campaign_id,buyer_name) VALUES ('$referrer','$clientdate','$clienttime','','$clientip','$commission_amount','$sale_amount','$txn_id','$item_id','$buyer_email','$c_id','$buyer_name')";
			$results = $wpdb->query($updatedb);	
			
			//Send commission notification email if enabled
			if(function_exists('wp_aff_send_commission_notification')){
				if($commission_amount>0)
				{
                                        eStore_payment_debug("Sending commission email notification request to the affiliate plugin",true);
					wp_aff_send_commission_notification($wp_aff_affiliates_db->email, $txn_id);
					eStore_payment_debug("Commission email notification request sending complete.",true);
				}
				else
				{
					eStore_payment_debug("The commission amount is 0. No need to notify the affiliate",true);
				}
			}			
		
			$message = 'The sale has been registered in the WP Affiliate Platform Database for referrer: '.$referrer.' with amount: '.$commission_amount;
			eStore_payment_debug($message,true);	 

			//2nd tier affiliate commission
                        eStore_payment_debug('Awarding 2nd tier commission if applicable',true);	
                        //$result = wp_aff_award_second_tier_commission($wp_aff_affiliates_db,$sale_amount,$txn_id,$item_id,$buyer_email);
                        if (get_option('wp_aff_use_2tier') && !empty($wp_aff_affiliates_db->referrer))
                        {
                                $award_tier_commission = true;	
                                $duration = get_option('wp_aff_2nd_tier_duration');		
                                if(!empty($duration))
                                {
                                        $join_date = $wp_aff_affiliates_db->date;
                                        $days_since_joined = round((strtotime(date("Y-m-d")) - strtotime($join_date) ) / (60 * 60 * 24));

                                        if ($days_since_joined > $duration)
                                        {
                                                eStore_payment_debug('Tier commission award duration expried',true);
                                                $award_tier_commission = false;
                                        }
                                }				
                                if ($award_tier_commission)
                                {							
                                        $updatedb = "INSERT INTO $aff_sales_table (refid,date,time,browser,ipaddress,payment,sale_amount,txn_id,item_id,buyer_email) VALUES ('$wp_aff_affiliates_db->referrer','$clientdate','$clienttime','','','$second_tier_commission_amount','$sale_amount','$txn_id','$item_id','$buyer_email')";
                                        //$updatedb = "INSERT INTO $aff_sales_table VALUES ('$wp_aff_affiliates_db->referrer','$clientdate','$clienttime','','','$second_tier_commission_amount','$sale_amount','$txn_id','$item_id','$buyer_email')";
                                        $results = $wpdb->query($updatedb);	
                                        eStore_payment_debug('Tier commission awarded to: '.$wp_aff_affiliates_db->referrer.'. Commission amount: '.$second_tier_commission_amount,true);	
                                }			
                        }															
                        eStore_payment_debug('End of tier commission check',true);
		}
		else
		{
			eStore_payment_debug('No Referrer Found. This is not an affiliate referred sale.',true);
		}
	}	
	else
	{
		eStore_payment_debug('WP Affiliate Platform capability is not present.',true);
	}
	eStore_payment_debug('===> End Affiliate Commission Check <===',true);	
}
function eStore_award_author_commission($payment_data,$cart_items)
{			
	eStore_payment_debug('Checking if the Author ID field has been used in any of the purchased product for revenue sharing purpose.',true);
	$share_revenue = get_option('eStore_aff_enable_revenue_sharing');
	if($share_revenue != '1')
	{
		eStore_payment_debug('Revenue sharing feature is not in use.',true);
		return;
	}		
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$buyer_email = $payment_data['payer_email'];
	$buyer_name = $payment_data['first_name']." ".$payment_data['last_name'];
    $txn_id = $payment_data['txn_id'];
            	
    global $eStore_affiliate_individual_product_commisions;
	if(!empty($eStore_affiliate_individual_product_commisions)){
		$cart_items = $eStore_affiliate_individual_product_commisions;
	}
	    
	foreach ($cart_items as $current_cart_item)
    {
		$cart_item_number = $current_cart_item['item_number'];
     	$total_item_price = $current_cart_item['mc_gross'] - $current_cart_item['mc_shipping'];
       	$item_qty = $current_cart_item['quantity'];       	
        $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$cart_item_number'", OBJECT);	
		if(!empty($retrieved_product->author_id))
		{
			//award commission for the author of this book
			eStore_payment_debug('Sharing Revenue.... Total Price of the currently processing item : '.$total_item_price,true);
			$referrer = $retrieved_product->author_id;
			$rev_share_commission = $retrieved_product->rev_share_commission;
			$sale_amount = $total_item_price; //The total item price includes the (individual item price * quantity)
			if(!empty($current_cart_item['product_commission'])){
				//lets subtract the affiliate commission before calculating the author's share
				$sale_amount = $sale_amount - $current_cart_item['product_commission'];
			}
			eStore_payment_debug('Sale amount that will be used (this amount has taken the affiliate commission into consideration if it applies): '.$sale_amount,true);
			
			if(empty($rev_share_commission)){$rev_share_commission = "";}
			eStore_payment_debug('Sharing revenue with: '.$referrer.' Commission override value (if any): '.$rev_share_commission,true);
			eStore_payment_debug('WP Affiliate platform plugin version: '.WP_AFFILIATE_PLATFORM_VERSION,true);
			$result = wp_aff_award_commission($referrer,$sale_amount,$txn_id,$cart_item_number,$buyer_email,'',$rev_share_commission,$buyer_name);			
			eStore_payment_debug(eStore_br2nl($result),true);
		}        	        
	}
}

function eStore_handle_auto_affiliate_account_creation($payment_data)
{
	if (function_exists('wp_aff_platform_install') || function_exists('wp_aff_create_affilate'))
	{
		eStore_payment_debug('Checking if auto affiliate account creation feature is active.',true);
		$create_auto_affiliate_account = get_option('eStore_create_auto_affiliate_account');
		if($create_auto_affiliate_account)
		{
			eStore_payment_debug('Checking if an affiliate account already exists...',true);
			if(function_exists('wp_aff_check_if_account_exists')){
				$account_exists = wp_aff_check_if_account_exists($payment_data['payer_email']);
			}
			else{
				eStore_payment_debug('Error! You need to update the affiliate platform plugin to use the auto upgrade feature!',false);
			}
			if(!$account_exists)
			{
				$aff_id = uniqid();
				$pwd = $aff_id;//use the affiliate ID as the password to create the account
				$commission_level = get_option('wp_aff_commission_level');
				$date = (date ("Y-m-d"));
				eStore_payment_debug('Creating affiliate account with Affiliate ID:'.$aff_id,true);
				wp_aff_create_affilate($aff_id,$pwd,$payment_data['payer_business_name'],"",$payment_data['first_name'],$payment_data['last_name'],"",$payment_data['payer_email'],"","","","","",$payment_data['address_country'],"","",$date,$payment_data['payer_email'],$commission_level,"");
				wp_aff_send_sign_up_email($aff_id,$pwd,$payment_data['payer_email']);
			}
			else
			{
				eStore_payment_debug('Affiliate account already exists with this email address. No new account will be created.',true);
			}
		}		
	}
	else
	{
		eStore_payment_debug('WP Affiliate Platform capability is not present.',true);
	}
}

function wp_eStore_handle_recurring_payment_charged_action($payment_data,$cart_items)
{
	//eMember related tasks
	$subscr_id = $payment_data['subscr_id'];
	eStore_update_member_subscription_start_date_if_applicable($payment_data,$subscr_id);
	
	//Affiilate plugin related tasks
	$award_commission = true;
	if(get_option('eStore_aff_one_time_commission')){
		$award_commission = false;
		eStore_payment_debug('One time commission option is being used, This is a recurring payment and will not generate affiliate commission.',true);
	}
	       	
	if($award_commission){
		eStore_payment_debug('Affiliate Commission may need to be tracked for this recurring payment. Invoking the commission checker function.',true);
		eStore_aff_award_commission($payment_data,$cart_items);
	}	
}

function wp_eStore_handle_subscription_eot_action($payment_data,$cart_items)
{	
	eMember_handle_subsc_cancel($payment_data);//eMember task
	if (get_option('eStore_enable_wishlist_int')){//WishList member task
		wl_handle_subsc_cancel($payment_data);
	}
}
