<?php

class WP_eStore_Config
{
    var $configs;
    static $_this;

    function loadConfig()
    {
        $this->configs = get_option('wp_eStore_plugin_configs_v2');
        if(empty($this->configs)){//TODO - remove this check after june 2014
            $eStore_raw_configs = get_option('wp_eStore_plugin_configs');
            if(is_string($eStore_raw_configs)){
                    $this->configs = unserialize($eStore_raw_configs);
            }
            else
            {
                    $this->configs = unserialize((string)$eStore_raw_configs);
            }
        }
        
        if(empty($this->configs)){$this->configs = array();}//This a brand new install site with no config data so initilize with a new array
    }
	
    function getValue($key){
    	return isset($this->configs[$key])?$this->configs[$key] : '';    	
    }
    
    function setValue($key, $value){
    	$this->configs[$key] = $value;
    }
    function saveConfig(){
    	update_option('wp_eStore_plugin_configs', serialize($this->configs) );
    	update_option('wp_eStore_plugin_configs_v2', $this->configs);
    }
    function addValue($key, $value)
    {
    	if (array_key_exists($key, $this->configs))
    	{
            //Don't update the value for this key
    	}
    	else
    	{
            //It is save to update the value for this key
            $this->configs[$key] = $value;
    	}    	
    }
    static function getInstance()
    {
    	if(empty(self::$_this)){
    		self::$_this = new WP_eStore_Config();
    		self::$_this->loadConfig();
    		return self::$_this;
    	}
    	return self::$_this;
    }
}

//Helper class for eStore config
class WP_eStore_Config_Helper
{
    function __construct(){
        //NOP
    }
    
    static function add_options_config_values()
    {
	add_option("cart_paypal_email", get_bloginfo('admin_email'));
	add_option("eStore_use_paypal_gateway", 1);
	add_option("cart_payment_currency", 'USD');
	add_option("cart_currency_symbol", '$');
	add_option("wp_cart_title", 'Items in Your Cart');
	add_option('wp_cart_empty_text', 'Your cart is empty');
	add_option('cart_return_from_paypal_url', get_bloginfo('wpurl'));
	add_option("eStore_auto_product_delivery", 1);
	add_option("eStore_enable_lightbox_effect", 1);
	add_option("eStore_manage_products_limit2", 50);
	add_option("eStore_auto_convert_to_relative_url", 0);
	add_option("eStore_variation_add_symbol", '+');
        add_option("eStore_download_method", '1');
        add_option('eStore_as3tp_as3key_data','::');//AS3TP
	$api_access_key = uniqid('',true);
	add_option("eStore_api_access_key", $api_access_key);
	
        //------------------------
	$wp_eStore_config = WP_eStore_Config::getInstance();	

        //Email related
        add_option("eStore_use_wp_mail", 1);
	add_option("eStore_send_buyer_email", 1); 
        $from_email_address = get_bloginfo('name')." <sales@your-domain.com>";
        add_option('eStore_download_email_address', $from_email_address);
 
        $buyer_email_subj = "Thank you for the purchase";
        add_option('eStore_buyer_email_subj', $buyer_email_subj);
        $buyer_email_body = "Dear {first_name} {last_name}".
			  "\n\nThank you for your purchase!".
			  "\n{product_details}".
			  "\n\nAny item(s) to be shipped will be processed as soon as possible, any digital item(s) can be downloaded using the encrypted links below.".
			  "\n{product_link}".
			  "\n\nThanks";
        add_option('eStore_buyer_email_body', $buyer_email_body);
        
        $notify_email_address = get_bloginfo('admin_email');
        add_option('eStore_notify_email_address', $notify_email_address);
        $seller_email_subj = "Notification of product sale";
        add_option('eStore_seller_email_subj', $seller_email_subj);
        $seller_email_body = "Dear Seller".
                            "\n\nThis mail is to notify you of a product sale. Product Name: {product_name} Product ID: {product_id}".
                            "\nThe sale was made to {first_name} {last_name} ({payer_email})".
                            "\n\nThanks";
        add_option('eStore_seller_email_body', $seller_email_body);

        //Create a generic template page
       	// Setup the author, slug, and title for the post
        $slug = 'estore-action';
        $page_title = 'Store Action';
	// If the page doesn't already exist, then create it
	if( null == get_page_by_title($page_title)) {
            $slug = 'estore-action';
            $page_title = 'Store Action';
            $page_content = 'This page has been created by WP eStore plugin. Please do not delete it. You can hide this page from your navigation menu.';
            $page_data = array(
            'post_status' 		=> 'publish',
            'post_type' 		=> 'page',
            'post_author' 		=> 1,
            'post_name' 		=> $slug,
            'post_title' 		=> $page_title,
            'post_content' 		=> $page_content,
            'comment_status' 	=> 'closed'
            );
            $page_id = wp_insert_post($page_data);     
            $wp_eStore_config->addValue('eStore_template_store_action_page_id', $page_id);
	}
        
        //Advanced settings
	$wp_eStore_config->addValue('eStore_price_currency_position', 'left');
	$wp_eStore_config->addValue('eStore_price_decimal_separator', '.');
	$wp_eStore_config->addValue('eStore_price_thousand_separator', ',');
	$wp_eStore_config->addValue('eStore_price_num_decimals', 2);
	
	$wp_eStore_config->setValue('eStore_do_not_show_sc_warning', '');
        
        $wp_eStore_config->setValue('eStore_use_new_checkout_redirection', '1');
	
        //Save the config updates
        $wp_eStore_config->saveConfig();
        
        //Trigger the after plugin activate check	
	update_option('eStore_plugin_activation_check_flag','1');
    }    
}
