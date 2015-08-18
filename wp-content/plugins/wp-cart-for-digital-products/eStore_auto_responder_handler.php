<?php

//**** This file needs to be included from a file that has access to "wp-load.php" ****
include_once('eStore_db_access.php');
include_once('eStore_debug_handler.php');
include_once('eStore_includes4.php');

function eStore_aweber_new_signup_user($full_target_list_name,$fname,$lname,$email_to_subscribe)
{
	eStore_payment_debug("Attempting to signup the user via AWeber API",true);
	$wp_eStore_config = WP_eStore_Config::getInstance();
	$eStore_aweber_access_keys = $wp_eStore_config->getValue('eStore_aweber_access_keys');
	if(empty($eStore_aweber_access_keys['consumer_key'])){
		eStore_payment_debug("Missing AWeber access keys! You need to first make a conntect before you can use this API",false);
		return;		
	}
	//wp_eStore_write_debug_array($eStore_aweber_access_keys,true);
	if (!class_exists('AWeberAPI')){//TODO - change the class name to "eStore_AWeberAPI" to avoid conflict with others
		include_once('lib/auto-responder/aweber_api/aweber_api.php');
		eStore_payment_debug("AWeber API library inclusion succeeded.",true);
	}else{
		eStore_payment_debug("AWeber API library is already included from another plugin.",true);
	}
	
	$aweber = new AWeberAPI($eStore_aweber_access_keys['consumer_key'], $eStore_aweber_access_keys['consumer_secret']);
	$account = $aweber->getAccount($eStore_aweber_access_keys['access_key'], $eStore_aweber_access_keys['access_secret']);//Get Aweber account
	$account_id = $account->id;
	$mylists = $account->lists;
	eStore_payment_debug("AWeber account retrieved. Account ID: ".$account_id,true);
	
	$target_list_name = str_replace("@aweber.com", "", $full_target_list_name);
	eStore_payment_debug("Attempting to signup the user to the AWeber list: ".$target_list_name,true);
	$list_name_found = false;
	foreach ($mylists as $list) {
		if($list->name == $target_list_name){
			$list_name_found = true;
			try {
			    //Create a subscriber
			    $params = array(
			        'email' => $email_to_subscribe,
			        'name' => $fname.' '.$lname,
			    );
			    $subscribers = $list->subscribers;
			    $new_subscriber = $subscribers->create($params);
			    eStore_payment_debug("User with email address " .$email_to_subscribe. " was added to the AWeber list: ".$target_list_name,true);
			}catch (Exception $exc) {
				eStore_payment_debug("Failed to complete the AWeber signup! Error Details Below.",false);
				wp_eStore_write_debug_array($exc,true);
			}    
		}
	}
	if(!$list_name_found){
		eStore_payment_debug("Error! Could not find the AWeber list (".$full_target_list_name.") in your AWeber Account! Please double check your list name value for typo.",false);
	}
}

function eStore_get_chimp_api_new()
{
	include_once('lib/auto-responder/eStore_MCAPI.class.php');
    $api_key = get_option('eStore_chimp_api_key');
    if(!empty($api_key))
    {
    	eStore_payment_debug("Creating a new API object using the API Key specified in the settings",true);
        $api = new eStore_MCAPI($api_key);
    }
    else
    {
    	$api = "";
    	eStore_payment_debug("Error! You did not specify your MailChimp API key in the autoresponder settings. MailChimp signup will fail.",false);
    }    
    return $api;
}

function eStore_mailchimp_subscribe($api,$target_list_name,$fname,$lname,$email_to_subscribe)
{	
	eStore_payment_debug("MailChimp target list name: ".$target_list_name,true);
	//Check if interest group data is present
	$pieces = explode("|", $target_list_name);
	if(count($pieces)>2){
		$target_list_name = trim($pieces[0]);
		$interest_group_name = trim($pieces[1]);
		$interest_groups = trim($pieces[2]);
		eStore_payment_debug("MailChimp list name: ".$target_list_name,true);
		eStore_payment_debug("Interest Group Name: ".$interest_group_name,true);
		eStore_payment_debug("Groups: ".$interest_groups,true);
	}
	
	$list_filter = array();
	$list_filter['list_name'] = $target_list_name;
	$all_lists = $api->lists($list_filter);
	$lists_data = $all_lists['data'];
	$found_match = false;
    foreach ($lists_data AS $list) 
    {
    	eStore_payment_debug("Checking list name : ".$list['name'],true);	    	
        if (strtolower($list['name']) == strtolower($target_list_name))
        {
        	$found_match = true;
            $list_id = $list['id'];
            eStore_payment_debug("Found a match for the list name on MailChimp. List ID :".$list_id,true);
        }
    }
    if(!$found_match){
    	eStore_payment_debug("Could not find a list name in your MailChimp account that matches with the target list name: ".$target_list_name,false);
    	return;
    }
    eStore_payment_debug("List ID to subscribe to:".$list_id,true);

	//Create the merge_vars data
	$merge_vars = array('FNAME'=>$fname, 'LNAME'=>$lname, 'INTERESTS'=>'');
    $signup_date_field_name = get_option('eStore_signup_date_field_name');
    if(!empty($signup_date_field_name)){//Add the signup date
    	$todays_date = date ("Y-m-d");
    	$merge_vars[$signup_date_field_name] = $todays_date;
    }
    if(count($pieces)>2){//Add the interest groups data to the merge_vars
    	$group_data = array(array('name'=>$interest_group_name, 'groups'=>$interest_groups));
    	$merge_vars['GROUPINGS'] = $group_data;
    }
    //wp_eStore_write_debug_array($merge_vars,true);
    
    //Subscribe to the list
    if(get_option('eStore_mailchimp_disable_double_optin')!='')
    {
    	eStore_payment_debug("Subscribing to MailChimp without double opt-in... Name: ".$fname." ".$lname." Email: ".$email_to_subscribe,true);
    	$send_welcome = true;
    	if(get_option('eStore_mailchimp_disable_final_welcome_email')!=''){
    		$send_welcome = false;
    		eStore_payment_debug ("Send welcome email option is disabled. Setting the send welcome flag to false.",true);
    	}
    	$retval = $api->listSubscribe($list_id, $email_to_subscribe, $merge_vars, "html", false, false, true, $send_welcome);
    }
    else//do the default subscription with basic values
    {
    	eStore_payment_debug("Subscribing to MailChimp... Name: ".$fname." ".$lname." Email: ".$email_to_subscribe,true); 
    	$retval = $api->listSubscribe($list_id, $email_to_subscribe, $merge_vars );
    }
	if ($api->errorCode){
		eStore_payment_debug ("Unable to load listSubscribe()!",false);
		eStore_payment_debug ("\tCode=".$api->errorCode,false);
		eStore_payment_debug ("\tMsg=".$api->errorMessage,false);
	} 
	else
	{
		eStore_payment_debug("MailChimp Signup was successful.",true);
	}
    return $retval;
}

function eStore_getResponse_subscribe($campaign_name,$fname,$lname,$email_to_subscribe)
{
	eStore_payment_debug('Attempting to call GetResponse API for list signup...',true);	 
	// your API key available at http://www.getresponse.com/my_api_key.html
	$api_key = get_option('eStore_getResponse_api_key');	
	// API 2.x URL
	$api_url = 'http://api2.getresponse.com';	
	$customer_name = $fname." ".$lname;	
	eStore_payment_debug('API Key:'.$api_key.', Customer name:'.$customer_name,true);	 
	
	if(!function_exists('curl_init')){
		eStore_payment_debug('Your server does not have the popular PHP CURL library installed! GetResponse API does not work without this library. Contact your hosting provider and request them to install CURL on your server.',false);
		return false;
	}	
	include_once('lib/auto-responder/eStore_jsonRPCClient.php');
	// initialize JSON-RPC client
	$client = new eStore_jsonRPCClient($api_url);
	eStore_payment_debug('created the eStore_jsonRPCClient object',true);
	$result = NULL;
	
	eStore_payment_debug('Attempting to retrieve campaigns for '.$campaign_name,true);
	// get CAMPAIGN_ID of the specified campaign (e.g. 'sample_marketing')
	try {
	    $result = $client->get_campaigns(
	        $api_key,
	        array (
	            # find by name literally
	            'name' => array ( 'EQUALS' => $campaign_name )
	        )
	    );
	}
	catch (Exception $e) {
	    # check for communication and response errors
            $error_msg = $e->getMessage();
            estore_write_debug_msg_or_array($error_msg, false);
	}

	eStore_payment_debug('Retrieved campaigns for: '.$campaign_name,true);
	# uncomment this line to preview data structure
	# print_r($result);
	
	# since there can be only one campaign of this name
	# first key is the CAMPAIGN_ID you need
	$CAMPAIGN_ID = array_pop(array_keys($result));	
	eStore_payment_debug("Attempting GetResponse add contact operation for campaign ID: ".$CAMPAIGN_ID." Customer Name: ".$customer_name." Email: ".$email_to_subscribe,true);
	
	if(empty($CAMPAIGN_ID))
	{
            eStore_payment_debug("Could not retrieve campaign ID. Please double check your GetResponse Campaign Name:".$campaign_name,false);
            return false;
	}
	else
	{
            //Add contact to campaign
            try {
                $result = $client->add_contact(
                    $api_key,
                    array (
                        'campaign'  => $CAMPAIGN_ID,
                        'name'      => $customer_name,
                        'email'     => $email_to_subscribe,
                            'cycle_day' => '0'
                    )
                );
            }
            catch (Exception $e) {
                //Check for communication and response errors
                $error_msg = $e->getMessage();
                estore_write_debug_msg_or_array($error_msg, false);
            }
	}
        estore_write_debug_msg_or_array($result, true);
	return true;
}

function eStore_generic_autoresponder_signup($firstname, $lastname, $emailaddress, $list_email_address)
{
	eStore_payment_debug('Preparing to send signup request email for generic autoresponder integration.',true);
	//$from_address = $emailaddress;//Use customer email address as the from address for this email
	$from_address = $firstname." ".$lastname." <".$emailaddress.">";

    $subject = "Autoresponder Automatic Sign up email";
    $body    = "\n\nThis is an automatic email that is sent to the autoresponder for user signup purpose\n".
               "\nEmail: ".$emailaddress.
               "\nName: ".$firstname." ".$lastname;

    if (get_option('eStore_use_wp_mail'))
    {
    	eStore_payment_debug('Sending signup request email via WordPress mailing system. From email address: '.$from_address,true);
		$headers = 'From: '.$from_address . "\r\n";
    	wp_mail($list_email_address, $subject, $body, $headers);
    	eStore_payment_debug('Signup email request successfully sent to:'.$list_email_address,true);
    	return 1;
    }
    else
    {
    	eStore_payment_debug('Sending signup request email via eStore\'s generic mail script.',true);
    	$attachment = '';
       	if(@eStore_send_mail($list_email_address,$body,$subject,$from_address,$attachment))
       	{
       		eStore_payment_debug('Signup email request successfully sent to:'.$list_email_address,true);
            return 1;
       	}
       	else
       	{
            return 0;
       	} 
    }	
}

function eStore_item_specific_autoresponder_signup($cart_items,$firstname,$lastname,$emailaddress)
{
	eStore_payment_debug('Performing item specific autoresponder signup if specified.',true);
	global $wp_eStore_config;
	$wp_eStore_config = WP_eStore_Config::getInstance();

	foreach ($cart_items as $current_cart_item)
	{
		$cart_item_data_num = $current_cart_item['item_number'];
		$item_name = $current_cart_item['item_name'];
		$cond = " id = '$cart_item_data_num'";
		$retrieved_product = WP_eStore_Db_Access::find(WP_ESTORE_DB_PRODUCTS_TABLE_NAME, $cond);		
	
		$list_name = $retrieved_product->aweber_list;
		// Autoresponder Sign up
	    if (!empty($retrieved_product->aweber_list))
	    {
	    	if(get_option('eStore_use_mailchimp'))//Using Mailchimp
	        {
	        	eStore_payment_debug('MailChimp integration is being used.',true);	 
	        	$api = eStore_get_chimp_api_new();
	            $retval = eStore_mailchimp_subscribe($api,$list_name,$firstname,$lastname,$emailaddress);
	            eStore_payment_debug('MailChimp item specific signup operation performed. Return value is: '.$retval,true);
	        }
	        else if(get_option('eStore_use_getResponse'))//Using GetResponse
	        {
	        	eStore_payment_debug('GetResponse integration is being used.',true);	 
	            $campaign_name = $retrieved_product->aweber_list;
	            eStore_payment_debug('GetResponse campaign to signup to:'.$campaign_name,true);
	            $retval = eStore_getResponse_subscribe($campaign_name,$firstname,$lastname,$emailaddress);	            
	            eStore_payment_debug('GetResponse item specific signup operation performed. Return value is: '.$retval,true);	        	
	        }
	        else if($wp_eStore_config->getValue('eStore_use_generic_autoresponder_integration')=='1')
	        {
	        	eStore_payment_debug('Generic autoresponder integration is being used.',true);	
		        $list_email_address = $retrieved_product->aweber_list;
		        $result = eStore_generic_autoresponder_signup($firstname, $lastname, $emailaddress, $list_email_address);
		        eStore_payment_debug('Generic autoresponder signup result: '.$result,true);	        	
	        }
	        else if($wp_eStore_config->getValue('eStore_use_new_aweber_integration') == '1'){//AWeber integration is enabled
        		eStore_payment_debug('Using AWeber integraiton option to signup to the list: '.$list_name,true);
        		eStore_aweber_new_signup_user($list_name,$firstname,$lastname,$emailaddress);
        	}
	        
	        // API call for plugins extending the item specific autoresponder signup
	        $signup_data = Array('firstname'=>$firstname, 'lastname'=>$lastname, 'email'=>$emailaddress, 'list_name'=>$retrieved_product->aweber_list, 'item_id' => $cart_item_data_num, 'item_name' => $item_name);
	        do_action('eStore_item_specific_autoresponder_signup',$signup_data);
	    }
	}
}

function eStore_global_autoresponder_signup($firstname,$lastname,$emailaddress)
{
	global $wp_eStore_config;
	$wp_eStore_config = WP_eStore_Config::getInstance();

	if($wp_eStore_config->getValue('eStore_use_new_aweber_integration') == '1')//AWeber integration is enabled
    {
    	if (get_option('eStore_enable_aweber_int') == 1){
    		eStore_payment_debug('Global AWeber list signup option is enabled.',true);
    		$aweber_list = get_option('eStore_aweber_list_name');
        	eStore_aweber_new_signup_user($aweber_list,$firstname,$lastname,$emailaddress);
    	}
    	else{
    		eStore_payment_debug('Global AWeber list signup option is disabled. No global list signup will be performed.',true);
    	}
    }
    if (get_option('eStore_enable_global_chimp_int') == 1)
    {
    	eStore_payment_debug('Mailchimp integration is being used.',true);	 
        $api = eStore_get_chimp_api_new();
        $target_list_name = get_option('eStore_chimp_list_name');
        $retval = eStore_mailchimp_subscribe($api,$target_list_name,$firstname,$lastname,$emailaddress);
        eStore_payment_debug('MailChimp global list signup operation performed. Return value is: '.$retval,true);
    }
    if(get_option('eStore_enable_global_getResponse_int') == 1)
    {
    	eStore_payment_debug('GetResponse integration is being used.',true);	 
	    $campaign_name = get_option('eStore_getResponse_campaign_name');
	    eStore_payment_debug('GetResponse campaign to signup to:'.$campaign_name,true);
	    $retval = eStore_getResponse_subscribe($campaign_name,$firstname,$lastname,$emailaddress);	    
	    eStore_payment_debug('GetResponse global list signup operation performed. Return value is: '.$retval,true);	      	
    }	
    if($wp_eStore_config->getValue('eStore_use_global_generic_autoresponder_integration')=='1')
    {
    	 eStore_payment_debug('Generic global autoresponder integration is being used.',true);	
         $list_email_address = $wp_eStore_config->getValue('eStore_generic_autoresponder_target_list_email');
         $result = eStore_generic_autoresponder_signup($firstname, $lastname, $emailaddress, $list_email_address);
         eStore_payment_debug('Generic autoresponder signup result: '.$result,true);
    } 

	// API call for plugins extending the global autoresponder signup
	$signup_data = Array('firstname'=>$firstname, 'lastname'=>$lastname, 'email'=>$emailaddress);
	do_action('eStore_global_autoresponder_signup',$signup_data);    
}
