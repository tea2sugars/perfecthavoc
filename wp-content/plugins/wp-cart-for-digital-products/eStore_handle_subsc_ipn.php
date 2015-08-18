<?php
//**** This file needs to be included from a file that has access to "wp-load.php" ****

function wl_handle_subsc_signup($ipn_data,$subsc_ref,$unique_ref)
{	
	$postURL = get_option('eStore_wishlist_post_url');// the post URL	
	$secretKey = get_option('eStore_wishlist_secret_word');	// the Secret Key
	if(empty($unique_ref)){$unique_ref = $ipn_data['txn_id'];}
	
	// prepare the data
	$data = array ();
	$data['cmd'] = 'CREATE';
	$data['transaction_id'] = $unique_ref;
	$data['lastname'] = $ipn_data['last_name'];
	$data['firstname'] = $ipn_data['first_name'];
	$data['email'] = $ipn_data['payer_email'];
	$data['level'] = $subsc_ref;
	
	debug_log_subsc("WL Member signup debug data: ".$data['cmd']."|".$data['transaction_id']."|".$data['lastname']."|".$data['firstname']."|".$data['email']."|".$data['level'],true);
	debug_log_subsc("WL Member signup Post URL: ".$postURL,true);
	
	// generate the hash
	$delimiteddata = strtoupper (implode ('|', $data));
	$hash = md5 ($data['cmd'] . '__' . $secretKey . '__' . $delimiteddata);
	
	// include the hash to the data to be sent
	$data['hash'] = $hash;
	
	// send data to post URL
	$ch = curl_init ($postURL);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	$returnValue = curl_exec ($ch);
	debug_log_subsc("WL Member return Value :".$returnValue,true);
	
	// process return value
	list ($cmd, $url) = explode ("\n", $returnValue);
	if ($cmd != 'CREATE'){//Try a different method one more time
		debug_log_subsc("Checking the return value using the split approach.",true);
		$parts = preg_split('/\s+/', $returnValue);
		if(count($parts) > 1){
			$cmd = trim($parts[0]);
			$url = trim($parts[1]);
		}
	}
	// check if the returned command is the same as what we passed
	//if ($cmd == 'CREATE'){
	$pos = strpos($cmd, "CREATE");
	if($pos !== false){
	    $message = $url;
	} 
	else{
	    $message = "Error returned from WishList plugin! Command value:".$cmd.". Error details: ".$url;
	}
	debug_log_subsc("WishList Member signup URL :".$message,true);
	
	//Save the registration URL
	eStore_save_membership_signup_rego_url($data['email'],$message,$ipn_data);
	
	// Send the membership signup email
	$email_subj = "Complete your membership registration";
	$to_address = $ipn_data['payer_email'];
	$from_address = get_option('eStore_download_email_address');
	$email_body = "Dear ".$ipn_data['first_name'].
				  "\n\nPlease Visit the following URL to complete your registration: ".
				  "\n".$message.
				  "\n\nThank You";
	$headers = 'From: '.$from_address . "\r\n";
	    
	if (get_option('eStore_use_wp_mail'))
    {
        wp_mail($to_address,$email_subj,$email_body,$headers);
        debug_log_subsc("Member signup email successfully sent using wp mail system to:".$to_address,true);
    }
    else
    {
		$attachment='';
		if(@eStore_send_mail($to_address,$email_body,$email_subj,$headers))
		{
		    debug_log_subsc("Member signup email successfully sent (using PHP mail) to:".$to_address,true);
		}
		else
		{
		    debug_log_subsc("Member signup email sending failed (using PHP mail) ",false);
		}
    }
}  
function wl_handle_subsc_cancel($ipn_data,$refund=false)
{
	// the post URL
	$postURL = get_option('eStore_wishlist_post_url');
	// the Secret Key
	$secretKey = get_option('eStore_wishlist_secret_word');
	
	// prepare the data
	$data = array ();
	$data['cmd'] = 'DEACTIVATE';
	if($refund){
		$data['transaction_id'] = $ipn_data['parent_txn_id'];
	}
	else{
		$data['transaction_id'] = $ipn_data['subscr_id'];
	}
	
	// generate the hash
	$delimiteddata = strtoupper (implode ('|', $data));
	$hash = md5 ($data['cmd'] . '__' . $secretKey . '__' . $delimiteddata);
	// include the hash to the data to be sent
	$data['hash'] = $hash;
	// send data to post URL
	$ch = curl_init ($postURL);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	$returnValue = curl_exec ($ch);
	// process return value
	list ($cmd, $url) = explode ("\n", $returnValue);
	// check if the returned command is the same as what we passed
	if ($cmd == 'DEACTIVATE') 
	{
		$message = "Membership deactivated successfully";
	} else 
	{
		$message = "Membership deactivation failed";
	}
	debug_log_subsc($message,true);	
}

function eMember_handle_subsc_signup($ipn_data,$subsc_ref,$unique_ref,$eMember_id='')
{
    global $wpdb,$emember_config;
    $emember_config = Emember_Config::getInstance();
    $members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
    $membership_level_table = $wpdb->prefix . "wp_eMember_membership_tbl";    
    $email = $ipn_data['payer_email'];
    
    if(empty($eMember_id))
    {	
    	//TODO - query db using subscr_id if the txn_type is recurring payment
    	debug_log_subsc("eMember ID is empty. Checking the database for existing record of this email address..",true);	    
	    $query_db = $wpdb->get_row("SELECT * FROM $members_table_name WHERE email = '$email'", OBJECT);
	    if($query_db){
	    	$eMember_id = $query_db->member_id;
	    	debug_log_subsc("Found record in the members table. The account will be upgraded. Member ID: ".$eMember_id,true);
	    }
	    else{
	    	debug_log_subsc("Did not find record in the members table. A new member account will be created for: ".$email,true);
	    }
    }
	debug_log_subsc("eMember user payment debug data: Unique ID: ".$unique_ref."| Email: ".$email."| Level: ".$subsc_ref,true);
        	
	if (!empty($eMember_id))//Update the existing member account
	{
		// upgrade the member account or assign new membership levels to his/her profile
		debug_log_subsc("Upgrading member account",true);
		$account_state = 'active';
		$membership_level = $subsc_ref;
		$subscription_starts = (date ("Y-m-d"));
		$subscr_id = $unique_ref;
		$resultset = "";
		$resultset = $wpdb->get_row("SELECT * FROM $members_table_name where member_id='$eMember_id'", OBJECT);
		if(!$resultset){
                    debug_log_subsc("Error! Could not find a member account for the given eMember ID: ".$eMember_id, false);
                    return;
                }
                $old_membership_level = $resultset->membership_level;
                            
		if($emember_config->getValue('eMember_enable_secondary_membership'))
		{
			debug_log_subsc("Using secondary membership level feature... adding additional levels to the existing profile of member ID:".$eMember_id,true);
			debug_log_subsc("Quering the table :".$members_table_name.", to retrieve member profile of eMember ID: ".$eMember_id,true);

                        debug_log_subsc("Retrieved member profile. Need to add membership level ID: ".$membership_level.", to this profile",true);
                        $additional_levels = $resultset->more_membership_levels;
                        debug_log_subsc("Current additional levels for this profile: ".$additional_levels,true);
                        if(is_null($additional_levels))
                        {					
                                $additional_levels = $resultset->membership_level; //assign the current primary level to the additional level
                                debug_log_subsc("Current additional levels for this profile is null. Adding level: ".$additional_levels,true);
                        }
                        else if(empty($additional_levels))
                        {					
                                $additional_levels = $resultset->membership_level;	//assign the current primary level to the additional level		
                                debug_log_subsc("Current additional levels for this profile is empty. Adding level: ".$additional_levels,true);		
                        }
                        else
                        {					
                                $additional_levels = $additional_levels.",".$resultset->membership_level; //add the current primary level to the list of additional levels
                                $sec_levels = explode(',', $additional_levels);
                                $additional_levels = implode(',', array_unique($sec_levels));//make sure there is no duplicate entry
                                debug_log_subsc("New additional level set: ".$additional_levels,true);
                        }
                        
                        $membership_level = apply_filters('emember_secondary_before_updating_primary_level', $membership_level, $subsc_ref, $eMember_id);
                        $additional_levels = apply_filters('emember_secondary_before_updating_additional_level', $additional_levels, $subsc_ref, $eMember_id);
                        
                        debug_log_subsc("Updating additional levels column for username: ".$resultset->user_name." with value: ".$additional_levels,true);
                        $updatedb = "UPDATE $members_table_name SET more_membership_levels='$additional_levels' WHERE member_id='$eMember_id'";    	    	
                        $results = $wpdb->query($updatedb);		

                        debug_log_subsc("Upgrading the primary membership level to the recently paid level. New primary membership level ID for this member is: ".$membership_level,true);
                        $updatedb = "UPDATE $members_table_name SET account_state='$account_state',membership_level='$membership_level',subscription_starts='$subscription_starts',subscr_id='$subscr_id' WHERE member_id='$eMember_id'";    	    	
                        $results = $wpdb->query($updatedb);
                        do_action('emember_membership_changed', array('member_id'=>$eMember_id, 'from_level'=>$old_membership_level, 'to_level'=>$membership_level));
		}
		else
		{
			debug_log_subsc("Not using secondary membership level feature... upgrading the current membership level of member ID: ".$eMember_id,true);
			if(function_exists('emember_get_expiry_by_member_id')){
				$current_expiry_date = emember_get_expiry_by_member_id($eMember_id);
				if($current_expiry_date != "noexpire"){
					if (strtotime($current_expiry_date) > strtotime($subscription_starts)){//Expiry time is in the future
						$subscription_starts = $current_expiry_date;//Start at the end of the previous expiry date to make sure he doesn't loose the remaning days from the current level
				    	debug_log_subsc("Updating the subscription start date to the current expiry date value: ".$subscription_starts,true);
					}
				}
			}
			debug_log_subsc("Executing DB update. Debug data: ".$account_state."|".$membership_level."|".$subscription_starts,true);
			$updatedb = "UPDATE $members_table_name SET account_state='$account_state',membership_level='$membership_level',subscription_starts='$subscription_starts',subscr_id='$subscr_id' WHERE member_id='$eMember_id'";    	    	
			$results = $wpdb->query($updatedb);
                        do_action('emember_membership_changed', array('member_id'=>$eMember_id, 'from_level'=>$old_membership_level, 'to_level'=>$membership_level));
		}	
				
    	//If using the WP user integration then update the role on WordPress too
    	if($emember_config->getValue('eMember_create_wp_user'))
    	{
			debug_log_subsc("Updating WordPress user role...",true);
			$resultset = $wpdb->get_row("SELECT * FROM $members_table_name where member_id='$eMember_id'", OBJECT);
    		$membership_level = $resultset->membership_level;
    		$username = $resultset->user_name;    		
	        $membership_level_resultset = $wpdb->get_row("SELECT * FROM $membership_level_table where id='$membership_level'", OBJECT);

		    $user_info = get_user_by('login',$username);
		    $role_name = $membership_level_resultset->role;
		    debug_log_subsc("The member username :".$username." ,WP User ID is: ".$user_info->ID." , Target role name: ".$role_name,true);
			if(!empty($role_name)){
		   		update_wp_user_Role($user_info->ID, $role_name);
	        	debug_log_subsc("Current WP users role updated to: ".$membership_level_resultset->role,true);
			}else{
				debug_log_subsc("You have a configuration error. Could not retrieve role name from the membership level. Level ID: ".$membership_level,false);
			}
    	} 
	    			
		// Set "notify email address" to the member's email address
		$resultset = $wpdb->get_row("SELECT * FROM $members_table_name where member_id='$eMember_id'", OBJECT);
    	$email = $resultset->email;	//$email = $ipn_data['payer_email'];	
    	debug_log_subsc("Setting the TO EMAIL address for membership upgrade notification to: ".$email,true);		
    	    	
	    $subject = $emember_config->getValue('eMember_account_upgrade_email_subject');
	    if (empty($subject))
	    {
	    	$subject = WP_ESTORE_EMEMBER_ACCOUNT_UPGRADE_SUBJECT;
	    }    	
    	$body = $emember_config->getValue('eMember_account_upgrade_email_body');
    	if (empty($body))
    	{
    		$body = WP_ESTORE_EMEMBER_ACCOUNT_UPGRADE_BODY;
    	}
		$from_address = get_option('senders_email_address');
		//$email_body = $body;
		$login_link = $emember_config->getValue('login_page_url');
		$tags1 = array("{first_name}","{last_name}","{user_name}","{login_link}");			
		$vals1 = array($resultset->first_name,$resultset->last_name,$resultset->user_name,$login_link);			
		$email_body = str_replace($tags1,$vals1,$body);			
	    $headers = 'From: '.$from_address . "\r\n";    	
	}//End of existing member account update
	else
	{
		// create fresh new member account
		debug_log_subsc("Creating new member account",true);		
		$user_name ='';
		$password = '';
	
		$first_name = $ipn_data['first_name'];
		$last_name = $ipn_data['last_name'];
		$email = $ipn_data['payer_email'];
		$membership_level = $subsc_ref;
		$subscr_id = $unique_ref;
		
	    $address_street = $ipn_data['address_street'];
	    $address_city = $ipn_data['address_city'];
	    $address_state = $ipn_data['address_state'];
	    $address_zipcode = $ipn_data['address_zip'];
	    $country = $ipn_data['address_country'];
	    $gender = 'not specified';
	
		$date = (date ("Y-m-d"));
		$account_state = 'active';
		$reg_code = uniqid(); //rand(10, 1000);
		$md5_code = md5($reg_code);
	
	    $updatedb = "INSERT INTO $members_table_name (user_name,first_name,last_name,password,member_since,membership_level,account_state,last_accessed,last_accessed_from_ip,email,address_street,address_city,address_state,address_zipcode,country,gender,referrer,extra_info,reg_code,subscription_starts,txn_id,subscr_id) VALUES ('$user_name','$first_name','$last_name','$password', '$date','$membership_level','$account_state','$date','IP','$email','$address_street','$address_city','$address_state','$address_zipcode','$country','$gender','','','$reg_code','$date','','$subscr_id')";
	    $results = $wpdb->query($updatedb);
	
		$results = $wpdb->get_row("SELECT * FROM $members_table_name where subscr_id='$subscr_id' and reg_code='$reg_code'", OBJECT);
	
		$id = $results->member_id;
		
	    $separator='?';
		$url = $emember_config->getValue('eMember_registration_page');
		if(empty($url)){$url=get_option('eMember_registration_page');}
		if(strpos($url,'?')!==false)
		{
			$separator='&';
		}
		$reg_url = $url.$separator.'member_id='.$id.'&code='.$md5_code;
		debug_log_subsc("Member signup URL :".$reg_url,true);
		
		//Save the registration signup URL value
		eStore_save_membership_signup_rego_url($email,$reg_url,$ipn_data);
	
		$subject = get_option('eMember_email_subject');
		$body = get_option('eMember_email_body');
		$from_address = get_option('senders_email_address');
	
	    $tags = array("{first_name}","{last_name}","{reg_link}");
	    $vals = array($first_name,$last_name,$reg_url);
		$email_body    = str_replace($tags,$vals,$body);
	    $headers = 'From: '.$from_address . "\r\n";
	}
    if (get_option('eStore_use_wp_mail'))
    {
        wp_mail($email,$subject,$email_body,$headers);
        debug_log_subsc("Member signup/upgrade completion email successfully sent to:".$email." From email address value used:".$from_address,true);
    }
    else
    {
    	$attachment='';
    	if(@eStore_send_mail($email,$email_body,$subject,$from_address,$attachment))
    	{
    	    debug_log_subsc("Member signup/upgrade completion email successfully sent (using PHP mail) to:".$email." From email address value used:".$from_address,true);
    	}
    	else
    	{
    	    debug_log_subsc("Member signup/upgrade completion email sending failed (using PHP mail) ",false);
    	}
    }
}

function eMember_handle_subsc_cancel($ipn_data,$refund=false)
{
	if (!function_exists('wp_eMember_install')){
		debug_log_subsc("WP eMember plugin is not active so no action is necessary for this subscription cancellation notification.",true);
		return;
	}
		
	if($refund){		
		$subscr_id = $ipn_data['parent_txn_id'];
		debug_log_subsc("Refund notification check for eMember - check if a member account needs to be deactivated... subscr ID: ".$subscr_id,true); 
	}
	else{		
		$subscr_id = $ipn_data['subscr_id']; 
		debug_log_subsc("Subscription cancellation notification check for eMember - check if a member account needs to be deactivated... subscr ID: ".$subscr_id,true);
	}	
    //$subscr_id = $ipn_data['subscr_id'];    

    global $wpdb;
    $members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
    $membership_level_table   = $wpdb->prefix . "wp_eMember_membership_tbl";
    
    debug_log_subsc("Retrieving member account from the database...",true);
    $resultset = $wpdb->get_row("SELECT * FROM $members_table_name where subscr_id='$subscr_id'", OBJECT);
    if($resultset)
    {
    	$membership_level = $resultset->membership_level;
    	$level_query = $wpdb->get_row("SELECT * FROM $membership_level_table where id='$membership_level'", OBJECT);
    	if (empty($level_query->subscription_period) && empty($level_query->subscription_unit)){
    		//subscription duration is set to no expiry or until canceled so deactivate the account now
    		$account_state = 'inactive';
		    $updatedb = "UPDATE $members_table_name SET account_state='$account_state' WHERE subscr_id='$subscr_id'";
		    $results = $wpdb->query($updatedb);    		
		    debug_log_subsc("Subscription cancellation received! Member account deactivated.",true);
		}
		else if (empty($level_query->subscription_period ) && !empty($level_query->subscription_unit)){//Fixed expiry
			//Subscription duration is set to fixed expiry. Don't do anything.
			debug_log_subsc("Subscription cancellation received! Level is using fixed expiry date so account will not be deactivated now.",true);
		}
		else{
    		//Subscription duration is set to duration. Set the account to unsubscribed and it will be set to inactive when the "Subscription duration" is over	
    		$account_state = 'unsubscribed';    
		    $updatedb = "UPDATE $members_table_name SET account_state='$account_state' WHERE subscr_id='$subscr_id'";
		    $results = $wpdb->query($updatedb);    		
		    debug_log_subsc("Subscription cancellation received! Member account set to unsubscribed.",true);
    	}
        
        $new_rego_code = '';//Invalidate the user's special rego code
        $updatedb = "UPDATE $members_table_name SET reg_code='$new_rego_code' WHERE subscr_id='$subscr_id'";
	$results = $wpdb->query($updatedb);           
        debug_log_subsc("Subscription cancellation - Member account rego code has been invalidated.",true);
        
        do_action('emember_membership_cancelled', array('member_id' => $resultset->member_id, 'level' => $membership_level));
    }
    else
    {
    	debug_log_subsc("No member found for the given subscriber ID:".$subscr_id,false);
    	return;
    }      
}

function eStore_update_member_subscription_start_date_if_applicable($ipn_data,$subscr_id)
{
	if (!function_exists('wp_eMember_install')){
		debug_log_subsc("WP eMember plugin is not active so no action is necessary for this recurring payment.",true);
		return;
	}
    global $wpdb;
    $emember_config = Emember_Config::getInstance();
    $members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
    $membership_level_table = $wpdb->prefix . "wp_eMember_membership_tbl";    
    $email = $ipn_data['payer_email'];
	debug_log_subsc("Updating subscription start date if applicable for this subscription payment. Subscriber ID: ".$subscr_id." Email: ".$email,true);
	
	//We can also query using the email address
	$query_db = $wpdb->get_row("SELECT * FROM $members_table_name WHERE subscr_id = '$subscr_id'", OBJECT);
	if($query_db){
		$eMember_id = $query_db->member_id;
		$current_primary_level = $query_db->membership_level;
		debug_log_subsc("Found a record in the member table. The eMember ID of the account to check is: ".$eMember_id." Membership Level: ".$current_primary_level,true);
		
		$level_query = $wpdb->get_row("SELECT * FROM $membership_level_table where id='$current_primary_level'", OBJECT);
    	if(!empty($level_query->subscription_period) && !empty($level_query->subscription_unit)){//Duration value is used		
			$account_state = "active";
			$subscription_starts = (date ("Y-m-d"));
		
			$updatedb = "UPDATE $members_table_name SET account_state='$account_state',subscription_starts='$subscription_starts' WHERE member_id='$eMember_id'";    	    	
			$results = $wpdb->query($updatedb);
			debug_log_subsc("Updated the member profile with current date as the subscription start date.",true);
    	}else{
    		debug_log_subsc("This membership level is not using a duration/interval value as the subscription duration.",true);
    	}			
	}else{
		debug_log_subsc("Did not find a record in the members table for subscriber ID: ".$subscr_id,true);
	}		
}

function eStore_chk_and_record_cust_data_for_free_trial_signup($ipn_data)
{
	$txn_type = $ipn_data['txn_type'];
	if(array_key_exists("mc_amount1",$ipn_data)){
		$amount1=$ipn_data['mc_amount1'];
	}else{
		$amount1=$ipn_data['amount1'];
	}
	debug_log_subsc("Transaction type and amount1 value of this subscription ipn : ".$txn_type."|".$amount1,true);
	if (($txn_type == "subscr_signup") && $amount1 == "0.00"){
		debug_log_subsc("Recording customer data for free trial subscription signup.",true);
		$txn_id = $ipn_data['subscr_id'];
		$ipn_data['txn_id'] = $txn_id;
		$ipn_data['status'] = "Free Trial";
		$id = $ipn_data['item_number'];
		$item_name = $ipn_data['item_name'];
		$cart_items = eStore_create_item_data($id,$item_name,"0");
		record_sales_data($ipn_data,$cart_items);
		include_once('eStore_process_payment_data_helper.php');
		process_payment_data($ipn_data,$cart_items);//Process and send notification email
		//Autoresponder signups
		$firstname = $ipn_data['first_name'];
		$lastname = $ipn_data['last_name'];
		$emailaddress = $ipn_data['payer_email'];
		eStore_item_specific_autoresponder_signup($cart_items,$firstname,$lastname,$emailaddress);
		eStore_global_autoresponder_signup($firstname,$lastname,$emailaddress);
		return true;
	}
	return false;//Not a free trial
}

function eStore_save_membership_signup_rego_url($email,$rego_url,$ipn_data)
{
	debug_log_subsc("Saving membership signup rego URL data for: ".$email,true);
    $fields = array();
    $fields['date_time'] = date ("Y-m-d H:i:s");//current_time('mysql');    
    $fields['meta_key1'] = "member_email";//Email address key
    $fields['meta_value1'] = $email;//Email address value  
    $fields['meta_key2'] = "txn_id";//Txn ID key
    $fields['meta_value2'] = $ipn_data['txn_id'];//Txn ID value       
    $fields['meta_key3'] = "rego_url";//Rego url key
    $fields['meta_value3'] = $rego_url;//Rego url value
    //Save ip address too maybe    
    $updated = WP_eStore_Db_Access::insert(WP_ESTORE_GLOBAL_META_TABLE_NAME, $fields);
    debug_log_subsc("Membership signup rego URL data saved.",true);
}

function debug_log_subsc($message,$success,$end=false)
{
    // Timestamp
    $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :').$message. "\n";
    if ($end) {
    	$text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log
    $debug_log_file_name = realpath(dirname(__FILE__))."/subscription_handle_debug.log";//TODO - replace with WP_ESTORE_PATH
    $fp=fopen($debug_log_file_name,'a');
    fwrite($fp, $text );
    fclose($fp);  // close file
}
?>