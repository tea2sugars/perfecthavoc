<?php
require_once('eStore_classes/_loader.php');
include_once('admin_includes.php');
include_once('eStore_post_payment_processing_helper.php');
include_once('eStore_general_settings_menu.php');
include_once('eStore_payment_gateway_settings_menu.php');
include_once('eStore_addon_settings_menu.php');
include_once('eStore_advanced_settings_menu.php');
include_once('eStore_email_settings_menu.php');

function wp_estore_settings_menu()
{
	if(isset($_GET['estore_hide_sc_msg'])){//Turn off the super cache warning display
		$wp_eStore_config = WP_eStore_Config::getInstance();
		$wp_eStore_config->setValue('eStore_do_not_show_sc_warning', '1');
		$wp_eStore_config->saveConfig();		
	}
	
	echo '<div class="wrap">';
	echo '<div id="poststuff"><div id="post-body">'; 
	 
	echo eStore_admin_css();
	echo eStore_admin_submenu_css();
	$current_tab = (isset($_GET['settings_action']))? $_GET['settings_action']:'';
   ?>
   <h2>WP eStore Settings v <?php echo WP_ESTORE_VERSION; ?></h2>
   <div class="eStoreSubMenu">
   <div class="eStoreSubMenuItem <?php echo ($current_tab=='')?'current':''; ?>"><a href="admin.php?page=wp_eStore_settings">General Settings</a></div>
   <div class="eStoreSubMenuItem <?php echo ($current_tab=='gateway')?'current':''; ?>"><a href="admin.php?page=wp_eStore_settings&settings_action=gateway">Payment Gateway Settings</a></div>  
   <div class="eStoreSubMenuItem <?php echo ($current_tab=='email')?'current':''; ?>"><a href="admin.php?page=wp_eStore_settings&settings_action=email">Email Settings</a></div>  
   <div class="eStoreSubMenuItem <?php echo ($current_tab=='aweber')?'current':''; ?>"><a href="admin.php?page=wp_eStore_settings&settings_action=aweber">Autoresponder Settings</a></div>
   <div class="eStoreSubMenuItem <?php echo ($current_tab=='advanced')?'current':''; ?>"><a href="admin.php?page=wp_eStore_settings&settings_action=advanced">Advanced Settings</a></div>
   <div class="eStoreSubMenuItem <?php echo ($current_tab=='addon')?'current':''; ?>"><a href="admin.php?page=wp_eStore_settings&settings_action=addon">Addon Settings</a></div>
   <div class="eStoreSubMenuItem <?php echo ($current_tab=='thrid_party')?'current':''; ?>"><a href="admin.php?page=wp_eStore_settings&settings_action=thrid_party">3rd Party Integration</a></div>
   <div class="eStore-admin-clear-float"></div>
   </div>
   <?php

   switch ($current_tab)
   {
       case 'aweber':
           wp_eStore_autoresponder_settings();
           break;
       case 'gateway':
           wp_eStore_payment_gateway_settings();
           break;
       case 'email':
           wp_eStore_email_settings();
           break;       
       case 'advanced':
           wp_eStore_advanced_settings();
           break;
       case 'addon':
           wp_eStore_addon_settings();
           break;
       case 'thrid_party':
           wp_eStore_third_party_settings();
           break;
       default:
           show_wp_digi_cart_settings_page();
           break;
   }

     echo '</div></div>';
     echo '</div>';
}

function wp_eStore_autoresponder_settings()
{
	global $wp_eStore_config;
	$wp_eStore_config = WP_eStore_Config::getInstance();
	
    if (isset($_POST['info_update']))
    {
    	$errors = "";
		if (isset($_POST['aweber_make_connection']))
		{
			if($wp_eStore_config->getValue('eStore_aweber_authorize_status') != 'authorized'){
		        $authorization_code = trim($_POST['aweber_auth_code']);	        
		        if (!class_exists('AWeberAPI')){
					include_once('lib/auto-responder/aweber_api/aweber_api.php');
		        }        
				$auth = AWeberAPI::getDataFromAweberID($authorization_code);
				list($consumerKey, $consumerSecret, $accessKey, $accessSecret) = $auth;
		        $eStore_aweber_access_keys = array(
		            'consumer_key'    => $consumerKey,
		            'consumer_secret' => $consumerSecret,
		            'access_key'      => $accessKey,
		            'access_secret'   => $accessSecret,
		        );	
		        $wp_eStore_config->setValue('eStore_aweber_access_keys', $eStore_aweber_access_keys);		        	
				//var_dump($eStore_aweber_access_keys);
				
				if ($eStore_aweber_access_keys['access_key']){
					try {
		            	$aweber = new AWeberAPI($consumerKey, $consumerSecret);
		            	$account = $aweber->getAccount($accessKey, $accessSecret);
		        	} catch (AWeberException $e) {
		            	$account = null;
		        	}
		        	if (!$account){
		            	//$this->deauthorize();//TODO - remove the keys
						$errors = 'AWeber authentication failed! Please try connecting again.';            	
		        	}
		        	else{
		        		$wp_eStore_config->setValue('eStore_aweber_authorize_status', 'authorized');
		        		$_POST['eStore_use_new_aweber_integration'] = '1';//Set the eStore_use_new_aweber_integration flag to enabled
				        echo '<div id="message" class="updated fade"><p><strong>';
				        echo 'AWeber authorization success!';
				        echo '</strong></p></div>';      	        		
		        	}			
				}
			}
			else{//Remove existing connection
		        $eStore_aweber_access_keys = array(
		            'consumer_key' => '',
		            'consumer_secret' => '',
		            'access_key' => '',
		            'access_secret' => '',
		        );		
		        $wp_eStore_config->setValue('eStore_aweber_access_keys', $eStore_aweber_access_keys);
		        $wp_eStore_config->setValue('eStore_aweber_authorize_status', '');
		        $_POST['eStore_use_new_aweber_integration'] = '';//Set the eStore_use_new_aweber_integration flag to disabled
				echo '<div id="message" class="updated fade"><p><strong>';
				echo 'AWeber connection removed!';
				echo '</strong></p></div>';  		        	
			}			
		}
        update_option('eStore_enable_aweber_int', isset($_POST["eStore_enable_aweber_int"])?'1':'');
        update_option('eStore_aweber_list_name', trim($_POST["eStore_aweber_list_name"]));        
        $wp_eStore_config->setValue('eStore_use_new_aweber_integration', isset($_POST["eStore_use_new_aweber_integration"])?'1':'');
        
        
        update_option('eStore_use_mailchimp', isset($_POST["eStore_use_mailchimp"])?'1':'');
        update_option('eStore_enable_global_chimp_int', isset($_POST["eStore_enable_global_chimp_int"])?'1':'');
        update_option('eStore_chimp_list_name', trim(stripslashes($_POST["eStore_chimp_list_name"])));
        update_option('eStore_chimp_api_key', trim($_POST["eStore_chimp_api_key"]));
        update_option('eStore_mailchimp_disable_double_optin', isset($_POST["eStore_mailchimp_disable_double_optin"])?'1':'');
        update_option('eStore_mailchimp_disable_final_welcome_email', isset($_POST["eStore_mailchimp_disable_final_welcome_email"])?'1':'');
        update_option('eStore_signup_date_field_name', trim($_POST["eStore_signup_date_field_name"]));

        update_option('eStore_use_getResponse', isset($_POST["eStore_use_getResponse"])?'1':'');
        update_option('eStore_enable_global_getResponse_int', isset($_POST["eStore_enable_global_getResponse_int"])?'1':'');
        update_option('eStore_getResponse_campaign_name', trim($_POST["eStore_getResponse_campaign_name"]));
        update_option('eStore_getResponse_api_key', trim($_POST["eStore_getResponse_api_key"]));
        
        //update_option('eStore_enable_infusionsoft_int', ($_POST['eStore_enable_infusionsoft_int']=='1') ? '1':'' );
        //update_option('eStore_infusionsoft_group_number', (string)$_POST["eStore_infusionsoft_group_number"]);
        
        $wp_eStore_config->setValue('eStore_use_generic_autoresponder_integration', isset($_POST["eStore_use_generic_autoresponder_integration"])?'1':'');
        $wp_eStore_config->setValue('eStore_use_global_generic_autoresponder_integration', isset($_POST["eStore_use_global_generic_autoresponder_integration"])?'1':'');        
        $wp_eStore_config->setValue('eStore_generic_autoresponder_target_list_email', trim($_POST["eStore_generic_autoresponder_target_list_email"]));
        $wp_eStore_config->saveConfig();
        
        if(!empty($errors)){
	        echo '<div id="message" class="error"><p>';
	        echo $errors;
	        echo '</p></div>';         	
        }
        else{
	        echo '<div id="message" class="updated fade"><p><strong>';
	        echo 'Autoresponder Options Updated!';
	        echo '</strong></p></div>';        	
        }
    }

	?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />

	<div class="postbox">
	<h3><label for="title">AWeber Settings (<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=615" target="_blank">AWeber Integration Instructions</a>)</label></h3>
	<div class="inside">

    <table class="form-table">
    
    <tr valign="top"><td width="25%" align="left">
    Enable AWeber Integration:
    </td><td align="left">    
    <input name="eStore_use_new_aweber_integration" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_use_new_aweber_integration')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this option if you want to enable AWeber integration and signup your customers to your AWeber list after purchase.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Global AWeber List Signup (optional):
    </td><td align="left">
    <input name="eStore_enable_aweber_int" type="checkbox"<?php if(get_option('eStore_enable_aweber_int')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Use this option if you want to sign up the customers of every transaction to one big AWeber List (specify the list name below). If you want to selectively signup customers to a different list on a per product basis then configure it in the Autoresponder settings section of the product in question.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Global AWeber List Name (optional):
    </td><td align="left">
    <input name="eStore_aweber_list_name" type="text" size="30" value="<?php echo get_option('eStore_aweber_list_name'); ?>"/>
    <br /><p class="description">The name of the AWeber list where the customers will be signed up to (example, listname@aweber.com)</p>
    </td></tr>
    </table>
    
    <div style="border-bottom: 1px solid #dedede; height: 10px"></div>
    <p><strong>Configure AWeber API</strong></p>
    <table class="form-table">
    
    <tr valign="top"><td width="25%" align="left">
    Step 1: Get Your AWeber Authorization Code:
    </td><td align="left">    
    <a href="https://auth.aweber.com/1.0/oauth/authorize_app/999d6172" target="_blank">Click here to get your authorization code</a>
    <br /><p class="description">Clicking on the above link will take you to the AWeber site where you will need to log in using your AWeber username and password. Then give access to the Tips and Tricks HQ AWeber app.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Step 2: Paste in Your Authorization Code:
    </td><td align="left">
    <input name="aweber_auth_code" type="text" size="140" value=""/>
    <br /><p class="description">Paste the long authorization code that you got from AWeber in the above field.</p>
    </td></tr>    

	<tr valign="top"><td colspan="2" align="left">
	Step 3: Hit the following "Make Connection" button
	</td></tr>

	<tr valign="top"><td colspan="2" align="left">
	<?php 
	if($wp_eStore_config->getValue('eStore_aweber_authorize_status') == 'authorized'){
		echo '<input type="submit" name="aweber_make_connection" value="Remove Connection" class= "button button" />';
	}else{
		echo '<input type="submit" name="aweber_make_connection" value="Make Connection" class= "button-primary" />';
	}
	?>
	</td></tr>	

    </table>
    </div></div>
 
	<div class="postbox">
	<h3><label for="title">MailChimp Settings (<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=753" target="_blank">MailChimp Integration Instructions</a>)</label></h3>
	<div class="inside">

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Enable MailChimp Integration:
    </td><td align="left">
    <input name="eStore_use_mailchimp" type="checkbox"<?php if(get_option('eStore_use_mailchimp')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this if you want to signup your customers to your MailChimp list.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Global MailChimp Integration:
    </td><td align="left">
    <input name="eStore_enable_global_chimp_int" type="checkbox"<?php if(get_option('eStore_enable_global_chimp_int')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will automatically sign up the customer of every transaction to your MailChimp List specified below. If you want to selectively signup customers on a per product basis then use the Autoresponder settings of that product.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    MailChimp List Name:
    </td><td align="left">
    <input name="eStore_chimp_list_name" type="text" size="30" value="<?php echo get_option('eStore_chimp_list_name'); ?>"/>
    <br /><p class="description">The name of the MailChimp list where the customers will be signed up to when using the global signup option (example: Customer List)</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    MailChimp API Key:
    </td><td align="left">
    <input name="eStore_chimp_api_key" type="text" size="50" value="<?php echo get_option('eStore_chimp_api_key'); ?>"/>
    <br /><p class="description">The API Key of your MailChimp account (can be found under the "Account" tab). By default the API Key is not active so make sure you activate it in your Mailchimp account.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Disable Double Opt-In:
    </td><td align="left">
    <input name="eStore_mailchimp_disable_double_optin" type="checkbox"<?php if(get_option('eStore_mailchimp_disable_double_optin')!='') echo ' checked="checked"'; ?> value="1"/>
    Do not send double opt-in confirmation email  
    <p class="description">Use this checkbox if you do not wish to use the double opt-in option. Please note that abusing this option may cause your MailChimp account to be suspended.</p>
    
    <input name="eStore_mailchimp_disable_final_welcome_email" type="checkbox"<?php if(get_option('eStore_mailchimp_disable_final_welcome_email')!='') echo ' checked="checked"'; ?> value="1"/>
    Do not send welcome email  
    <p class="description">Use this checkbox if you do not wish to send the welcome email sent by MailChimp when a user subscribes to your list. This will only work if you disable the double opt-in option above.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Signup Date Field Name (optional):
    </td><td align="left">
    <input name="eStore_signup_date_field_name" type="text" size="30" value="<?php echo get_option('eStore_signup_date_field_name'); ?>"/>
    <br /><p class="description">If you have configured a signup date field for your mailchimp list then specify the name of the field here (example: SIGNUPDATE). <a href="http://kb.mailchimp.com/article/how-do-i-create-a-date-field-in-my-signup-form" target="_blank">More Info</a></p>
    </td></tr>
            
    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">GetResponse Settings (<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=898" target="_blank">GetResponse Integration Instructions</a>)</label></h3>
	<div class="inside">

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Enable GetResponse Integration:
    </td><td align="left">
    <input name="eStore_use_getResponse" type="checkbox"<?php if(get_option('eStore_use_getResponse')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this if you want to signup your customers to your GetResponse list.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Global GetResponse Integration:
    </td><td align="left">
    <input name="eStore_enable_global_getResponse_int" type="checkbox"<?php if(get_option('eStore_enable_global_getResponse_int')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will automatically sign up the customer of every transaction to your GetResponse campaign specified below. If you want to selectively signup customers on a per product basis then use the Autoresponder settings of that product.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    GetResponse Campaign Name:
    </td><td align="left">
    <input name="eStore_getResponse_campaign_name" type="text" size="30" value="<?php echo get_option('eStore_getResponse_campaign_name'); ?>"/>
    <br /><p class="description">The name of the GetResponse campaign where the customers will be signed up to (e.g. marketing)</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    GetResponse API Key:
    </td><td align="left">
    <input name="eStore_getResponse_api_key" type="text" size="50" value="<?php echo get_option('eStore_getResponse_api_key'); ?>"/>
    <br /><p class="description">The API Key of your GetResponse account (can be found inside your GetResponse Account).</p>
    </td></tr>

    </table>
    </div></div>
    
	<div class="postbox">
	<h3><label for="title">Generic Autoresponder Integration Settings</label></h3>
	<div class="inside">

	<br /><strong>&nbsp; &nbsp; If your autoresponder provider allows you to signup users just by sending an email to the list email address with the user's email as the from address then you can use this method of integration</strong>
	
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Use Generic Autoresponder Integration:
    </td><td align="left">
    <input name="eStore_use_generic_autoresponder_integration" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_use_generic_autoresponder_integration')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Use this option if you want to use the generic auotoresponder integration option.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Enbale Global Integration:
    </td><td align="left">
    <input name="eStore_use_global_generic_autoresponder_integration" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_use_global_generic_autoresponder_integration')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will automatically sign up the customer of every transaction to your list/campaign specified below. If you want to selectively signup customers on a per product basis then use the Autoresponder settings of that product.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Global List/Campaign Email Address:
    </td><td align="left">
    <input name="eStore_generic_autoresponder_target_list_email" type="text" size="40" value="<?php echo $wp_eStore_config->getValue('eStore_generic_autoresponder_target_list_email'); ?>"/>
    <br /><p class="description">The email address of the list where the customer will be signed up to. The plugin will send the autoresponder signup email to this address.</p>
    </td></tr>

    </table>
    </div></div>
    
    <div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="<?php _e('Update'); ?> &raquo;" />
    </div>
    </form>
    <?php
}

function wp_eStore_third_party_settings()
{
    if (isset($_POST['info_update']))
    {
        update_option('eStore_enable_wishlist_int', isset($_POST["eStore_enable_wishlist_int"])?'1':'');
        update_option('eStore_wishlist_post_url', trim($_POST["eStore_wishlist_post_url"]));
        update_option('eStore_wishlist_secret_word', trim($_POST["eStore_wishlist_secret_word"]));
        
        update_option('eStore_ngg_template_product_id', trim($_POST["eStore_ngg_template_product_id"]));   

        update_option('eStore_enable_analytics_tracking', isset($_POST["eStore_enable_analytics_tracking"])?'1':'');
        
        update_option('eStore_memberwing_ipn_post_url', trim($_POST["eStore_memberwing_ipn_post_url"])); 
        
        update_option('eStore_third_party_ipn_post_url', trim($_POST["eStore_third_party_ipn_post_url"]));         

        update_option('eStore_api_access_key', trim($_POST["eStore_api_access_key"]));   
        
        echo '<div id="message" class="updated fade"><p><strong>';
        echo 'Options Updated!';
        echo '</strong></p></div>';
    }
    
	$api_access_key = get_option('eStore_api_access_key');
    if(empty($api_access_key)){
    	$api_access_key = uniqid('',true);
		update_option("eStore_api_access_key", $api_access_key);
    }
	?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />

	<div class="postbox">
	<h3><label for="title">WishList Integration Settings (<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=448" target="_blank">WishList Integration Instructions</a>)</label></h3>
	<div class="inside">

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Enable Wishlist Members Integration
    </td><td align="left">
    <input name="eStore_enable_wishlist_int" type="checkbox"<?php if(get_option('eStore_enable_wishlist_int')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this checkbox if you want to integrate with the WishList Members plugin.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    WishList Members POST URL
    </td><td align="left">
    <input name="eStore_wishlist_post_url" type="text" size="100" value="<?php echo get_option('eStore_wishlist_post_url'); ?>"/>
    <br /><p class="description">Get this value from your WishList Members plugin's Integration tab (Select Generic Integration)</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    WishList Members Secret Word
    </td><td align="left">
    <input name="eStore_wishlist_secret_word" type="text" size="50" value="<?php echo get_option('eStore_wishlist_secret_word'); ?>"/>
    <br /><p class="description">Get this value from your WishList Members plugin's Integration tab (Select Generic Integration)</p><br />
    </td></tr>
    </table>
    </div></div>
    
	<div class="postbox">
	<h3><label for="title">NextGen Gallery Settings (<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=805" target="_blank">NextGen Gallery Integration Instructions</a>)</label></h3>
	<div class="inside">
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Product ID to be Used as a Template
    </td><td align="left">
    <input name="eStore_ngg_template_product_id" type="text" size="3" value="<?php echo get_option('eStore_ngg_template_product_id'); ?>"/>
    <br /><p class="description">Configure one product with the price, variation, shipping etc options that you prefer then use the ID of that product here. The eStore will use the information from this product when placing "Buy" button under the gallery images.</p>
    </td></tr>
    
    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">Google Analytics Tracking (<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=850" target="_blank">Analytics E-Commerce Tracking Overview</a>)</label></h3>
	<div class="inside">
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Enable Google Analytics Tracking
    </td><td align="left">
    <input name="eStore_enable_analytics_tracking" type="checkbox"<?php if(get_option('eStore_enable_analytics_tracking')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this checkbox if you want to integrate Google Analytics e-commerce tracking for products sold through WP eStore.</p>
    </td></tr>        
    
    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">Memberwing Integration Settings</label></h3>
	<div class="inside">
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    PayPal IPN Post URL
    </td><td align="left">
    <input name="eStore_memberwing_ipn_post_url" type="text" size="100" value="<?php echo get_option('eStore_memberwing_ipn_post_url'); ?>"/>
    <br /><p class="description">If you want to integrate the Memberwing plugin with eStore then get the IPN Post URL from your Memberwing plugin and specify it here.</p>
    </td></tr>
        
    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">POST IPN to a 3rd Party Application</label></h3>
	<div class="inside">
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Instant Payment Notification (IPN) Post URL
    </td><td align="left">
    <input name="eStore_third_party_ipn_post_url" type="text" size="100" value="<?php echo get_option('eStore_third_party_ipn_post_url'); ?>"/>
    <br /><p class="description">If you want eStore to post the IPN(instant payment notification) to another URL for 3rd party integration then specify the IPN Post URL here.</p>
    </td></tr>
    </table>
    </div></div>    
    
	<div class="postbox">
	<h3><label for="title">WP eStore API Access</label></h3>
	<div class="inside">
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    WP eStore API Access Key
    </td><td align="left">
    <input name="eStore_api_access_key" type="text" size="100" value="<?php echo get_option('eStore_api_access_key'); ?>"/>
    <br /><p class="description">The API Access key for WP eStore</p>
    </td></tr>
    </table>
    </div></div>    
        
    <div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="<?php _e('Update'); ?> &raquo;" />
    </div>
    </form>
    <?php
}



function wp_estore_admin_menu()
{
    echo '<div class="wrap">';
    echo '<h2>Admin Functions</h2>';
	echo '<div id="poststuff"><div id="post-body">';
	
	echo eStore_admin_css();
	global $wpdb;
	
	echo '<div class="eStore_yellow_box">These helpful admin functions allow you to do various manual admin stuff from time to time like generating an encrypted download link for any product, sending email to any customer etc.</div>';
    
	$message = "";
	$eStore_product_id = "";
	$wp_eStore_variation_name = "";
	$eStore_download_link = "";	
	if (isset($_POST['generate_download_link']))
    {
    	$eStore_product_id = trim($_POST["wp_eStore_product_id"]);
    	$wp_eStore_variation_name = stripslashes($_POST["wp_eStore_variation_name"]);
    		    
		$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
        $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$eStore_product_id'", OBJECT);
        
        if (!empty($wp_eStore_variation_name))
        {
        	$item_name = $retrieved_product->name." (".$wp_eStore_variation_name.")";
        }
        else
        {
        	$item_name = $retrieved_product->name;
        }
        
        $eStore_download_link = generate_download_link($retrieved_product,$item_name);
        if (empty($eStore_download_link))
        {
        	$eStore_download_link = "Download link generation failed! Make sure the digital product variation name is correct if this product uses digital product variation.";
        }
        $message .= 'Download Link Generated!';
    }

    if (isset($_POST['send_email']))
    {
    	update_option('eStore_from_email',stripslashes($_POST["wp_eStore_from_email"]));
		update_option('eStore_to_email',stripslashes($_POST["wp_eStore_to_email"]));
		update_option('eStore_email_subject',stripslashes($_POST["wp_eStore_email_subject"]));
		update_option('eStore_admin_email_body',stripslashes($_POST["wp_eStore_email_body"]));

        $attachment = '';
        if (get_option('eStore_use_wp_mail'))
        {
            $from = get_option('eStore_from_email');
            $headers = 'From: '.$from . "\r\n";
            wp_mail(get_option('eStore_to_email'), get_option('eStore_email_subject'), get_option('eStore_admin_email_body'),$headers);
            $message .= "Email sent successfully!";
        }
        else
        {
        	if(@eStore_send_mail(get_option('eStore_to_email'),get_option('eStore_admin_email_body'),get_option('eStore_email_subject'),get_option('eStore_from_email'),$attachment))
        	{
        	   	$message .= "Email sent successfully!";
        	}
        	else
        	{
        		$message .= "Email Sending failed!";
        	}
        }
    }
	if(isset($_POST['bulk_delete']))
	{
		$interval_val = $_POST['bulk_delete_hours'];
		$interval_unit = 'HOUR';//MINUTE
		$cur_time = current_time('mysql');

		$download_links_table_name = $wpdb->prefix . "wp_eStore_download_links_tbl";
		$cond = " DATE_SUB('$cur_time',INTERVAL '$interval_val' $interval_unit) > creation_time";
        $result = $wpdb->query("DELETE FROM $download_links_table_name WHERE $cond", OBJECT);        

        if($result)	
		{			
			$message .= "The download links have been deleted! The current timestamp value used was: ".$cur_time;
		}
		else
		{
			$message .= "Nothing to delete!";
		}
	}
	if(isset($_POST['reset_settings_to_default']))
	{
		wp_eStore_reset_settings_to_default();
		$message .= "Settings options have been reset to default!";			
	}	
	if(isset($_POST['reset_sales_data']))
	{
		wp_eStore_reset_sales_data();
		$message .= "Sales data have been reset!";		
	}
	if(isset($_POST['reset_product_sales_counter_data']))
	{
		wp_eStore_reset_product_sales_counter();
		$message .= "All product sales counter have been reset to 0";		
	}
        if(isset($_POST['reset_receipt_counter_data'])){
                wp_eStore_reset_receipt_counter();
		$message .= "Counter tag value has been reset to 0";
        }
	if(isset($_POST['refresh_prod_cat_relations_tbl']))
	{
		wp_eStore_refresh_product_category_relation_tbl();
		$message .= "The products and category relations table has been resynced.";
	}
	if(isset($_POST['remove_all_db_tables']))
	{
		wp_eStore_remove_all_db_tables();
		$message .= "All Database tables have been removed! Remember, eStore will not work unless you deactivate and reactivate it again!";		
	}
	
	//Display message to the user
	if(!empty($message)){		
		echo '<div id="message" class="updated fade"><p><strong>';
	    echo $message;
	    echo '</strong></p></div>';  
	}

    ?>
	<div class="postbox">
	<h3><label for="title">Generate an Encrypted download link for a Product</label></h3>
	<div class="inside">

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="generate_download_link" id="generate_download_link" value="true" />
    
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td align="left">
    Product ID: 
    </td><td align="left">
    <input name="wp_eStore_product_id" type="text" size="10" value="<?php echo $eStore_product_id; ?>" />
    <br /><i>(i) Enter the product id of the product that you want to generate an encrypted download link for.</i>
    </td></tr>

    <tr valign="top"><td align="left">
    Digital Product Variation Name: 
    </td><td align="left">
    <input name="wp_eStore_variation_name" type="text" size="30" value="<?php echo $wp_eStore_variation_name; ?>" />
    <br /><i>(ii) Enter the digital product variation name if you are using it. Leave empty if this product does not use the digital product variation field.</i>
    </td></tr>
    
    <tr valign="top"><td align="left">
    </td><td align="left">
    <input type="submit" class="button" name="generate_download_link" value="Generate Link &raquo;" />
    <br /><i>(iii) Hit the "Generate Link" button.</i><br /><br />
    </td></tr>
    
    <tr valign="top"><td align="left">
    Download Link: 
    </td><td align="left">
    <textarea name="wp_eStore_download_link" rows="6" cols="70"><?php echo $eStore_download_link; ?></textarea>
    <br /><i>The encrypted download link for a product will be shown in the above area when you hit the Generate Link button.</i><br />
    </td></tr>
    </table>
    </form>
	</div></div>
	
	<div class="postbox">
	<h3><label for="title">Send Email to Customers</label></h3>
	<div class="inside">
	
	<div class="eStore_yellow_box">You can use this section to send a quick email to your customer. 
	If you want to send a download link for a product then first generate the link from the above section then copy and paste 
	the encrypted link in the body section below.
	</div>
	
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="send_email" id="send_email" value="true" />

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td align="left">
    From Email Address:
    </td><td align="left">
    <input name="wp_eStore_from_email" type="text" size="50" value="<?php echo get_option('eStore_from_email'); ?>" />
    <br /><i>This email address will appear in the from field of the email.</i>
    </td></tr>

    <tr valign="top"><td align="left">
    To Email Address:
    </td><td align="left">
    <input name="wp_eStore_to_email" type="text" size="50" value="<?php echo get_option('eStore_to_email'); ?>" />
    <br /><i>This is the email address where the email will be sent to.</i>
    </td></tr>

    <tr valign="top"><td align="left">
    Email Subject: 
    </td><td align="left">
    <input name="wp_eStore_email_subject" type="text" size="50" value="<?php echo get_option('eStore_email_subject'); ?>" />
    <br /><i>This is the email subject.</i>
    </td></tr>

    <tr valign="top"><td align="left">
    Email Body: 
    </td><td align="left">
    <textarea name="wp_eStore_email_body" rows="10" cols="70"><?php echo get_option('eStore_admin_email_body'); ?></textarea>
    <br /><i>Type your email and hit Send Email button below.</i><br /><br />
    <input type="submit" class="button" name="send_email" value="<?php _e('Send Email'); ?> &raquo;" />
    </td></tr>

	</table>
	</form>
	</div></div>
	
    <div class="postbox">
    <h3><label for="title">Clean The Encrypted Download Links Table</label></h3>
    <div class="inside">
    <br />
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	 Delete All Links Older Than
    <input name="bulk_delete_hours" type="text" size="3" value=""/> Hours
    <div class="submit">
        <input type="submit" class="button" name="bulk_delete" value="Bulk Delete &raquo;" />
    </div>
    </form>
    </div></div>

    <div class="postbox">
    <h3><label for="title">The Almighty Reset Buttons</label></h3>
    <div class="inside">
    
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onSubmit="return confirm('Are you sure you want to reset all the settings options to default?');" >    
    <div class="submit">
        <input type="submit" class="button" name="reset_settings_to_default" value="Reset eStore Settings to Default" />
    </div>    
    </form> 
       
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onSubmit="return confirm('Are you sure you want to reset all sales related data? Useful if you are trying to reset all the test transactions before going live.');" >    
    <div class="submit">
        <input type="submit" class="button" name="reset_sales_data" value="Reset All Sales Data" />
    </div>    
    </form> 

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onSubmit="return confirm('Are you sure you want to reset all product sales counter to 0?');" >    
    <div class="submit">
        <input type="submit" class="button" name="reset_product_sales_counter_data" value="Reset Product Sales Counter" />
    </div>    
    </form> 
    
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onSubmit="return confirm('Are you sure you want to reset the receipt counter value to 0?');" >    
    <div class="submit">
        <input type="submit" class="button" name="reset_receipt_counter_data" value="Reset Counter Tag Value" />
    </div>    
    </form>
        
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onSubmit="return confirm('Are you sure you want to run this operation?');" >    
    <div class="submit">
        <input type="submit" class="button" name="refresh_prod_cat_relations_tbl" value="Resync Products and Category Relations Table" />
    </div>    
    </form> 
        
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onSubmit="return confirm('Are you sure you want to remove all eStore database tables? Useful if you are trying to do a clean reinstall of the plugin.');" >    
    <div class="submit">
        <input type="submit" class="button" name="remove_all_db_tables" value="Remove All eStore Database Tables" />
    </div>    
    </form> 
               
    </div></div>

    <div class="postbox">
    <h3><label for="title">Site Diagnostics</label></h3>
    <div class="inside">
	<strong>WP eStore Version: </strong><code><?php echo WP_ESTORE_VERSION;?></code><br />
	<strong>WP Version: </strong><code><?php echo get_bloginfo("version"); ?></code><br />
	<strong>WPMU: </strong><code><?php echo (!defined('MULTISITE') || !MULTISITE) ? "No" : "Yes";  ?></code><br />
	<strong>MySQL Version: </strong><code><?php echo $wpdb->db_version();?></code><br />
	<strong>WP Table Prefix: </strong><code><?php echo $wpdb->prefix; ?></code><br />
	<strong>PHP Version: </strong><code><?php echo phpversion(); ?></code><br />
	<strong>Session Save Path: </strong><code><?php echo ini_get("session.save_path"); ?></code><br />
	<strong>WP URL: </strong><code><?php echo get_bloginfo('wpurl'); ?></code><br />
	<strong>Server Name: </strong><code><?php echo $_SERVER['SERVER_NAME']; ?></code><br />
	<strong>Cookie Domain: </strong><code><?php $cookieDomain = parse_url( strtolower( get_bloginfo('wpurl') ) ); echo $cookieDomain['host']; ?></code><br />
	<strong>CURL Library Present: </strong><code><?php echo (function_exists('curl_init')) ? "Yes" : "No"; ?></code><br />
	<strong>Debug File Write Permissions: </strong><code><?php echo (is_writable(WP_ESTORE_PATH)) ? "Writable" : "Not Writable"; ?></code><br />		
    </div></div>
    <?php
    echo '</div></div>';
    echo '</div>';
}
?>