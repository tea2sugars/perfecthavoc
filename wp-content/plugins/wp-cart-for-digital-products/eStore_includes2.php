<?php
include_once('eStore_post_payment_processing_helper.php');

function estore_exclude_page_handler($excludes)
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $store_action_page_id = $wp_eStore_config->getValue('eStore_template_store_action_page_id');
    if(!isset($store_action_page_id)){
        $store_action = get_page_by_path('estore-action');
        $store_action_page_id = $store_action->ID;
    }
    $excludes[] = $store_action_page_id;
    
    if(is_array(get_option('exclude_pages'))){
        $excludes = array_merge(get_option('exclude_pages'), $excludes );
    }
    sort($excludes);

    return $excludes;
}

function eStore_send_free_download1($name, $to_email_address, $download, $payment_data='', $cart_items='')
{
    if(WP_ESTORE_DO_NOT_SEND_EMAIL_FROM_SQUEEZE_FORM==='1'){//Don't send the email for the squeeze form submission
            return true;
    }
    global $wpdb;
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $attachment = '';
    $from_email_address = get_option('eStore_download_email_address');
    $headers = 'From: '.$from_email_address . "\r\n";
    $email_subj = $wp_eStore_config->getValue('eStore_squeeze_form_email_subject');
    if(empty($email_subj)){
        $email_subj = ESTORE_FREE_DOWNLOAD_SUBJECT;
    }
    $email_body = $wp_eStore_config->getValue('eStore_squeeze_form_email_body');
    if(empty($email_body)){
        $email_body = ESTORE_DEAR.' '.$name.
                              "\n\n".ESTORE_FREE_DOWNLOAD_EMAIL_BODY.
                              "\n".$download.
                              "\n\n".ESTORE_THANK_YOU;
    }else{//Apply the email tag filtering
        $prod_id = $cart_items[0]['item_number'];
	$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$prod_id'", OBJECT);
        
        $product_specific_instructions = eStore_get_product_specific_instructions($retrieved_product);
        $product_specific_instructions = eStore_apply_post_payment_dynamic_tags($product_specific_instructions, $payment_data, $cart_items );
        $additional_data = array();
        $additional_data['product_specific_instructions'] = $product_specific_instructions;
        
        $email_body = str_replace("{product_link}",$download,$email_body);
        $email_body = eStore_apply_post_payment_dynamic_tags($email_body,$payment_data,$cart_items,$additional_data);
    }
    
    if (get_option('eStore_use_wp_mail'))
    {
        wp_eStore_send_wp_mail($to_email_address, $email_subj, $email_body, $headers);
        return true;
    }
    else
    {
        if(@eStore_send_mail($to_email_address,$email_body,$email_subj,$from_email_address,$attachment))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function free_download_pseudo_payment_data($cust_name, $cust_email)
{
	// This function returns pseudo payment_data that can be passed to the PDF Stamper addon.  It is called by both the Ajax
	$unique_id = "Free-Download-".uniqid();
	list($firstname,$lastname) = explode(' ',$cust_name,2);
	$payment_data = array(
		'customer_name' => $cust_name,
		'payer_email' => $cust_email,
		'first_name' => $firstname,
		'last_name' => $lastname,
		'contact_phone' => 'N/A or Not Provided',
		'address' => $cust_email,
		'payer_business_name' => $cust_name,
		'txn_id' => $unique_id,
	);
	return $payment_data;
}
