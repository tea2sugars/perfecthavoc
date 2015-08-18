<?php

function wp_eStore_addon_settings()
{
	global $wp_eStore_config;
	$wp_eStore_config = WP_eStore_Config::getInstance();
	
    if (isset($_POST['info_update']))
    {
	$retVal = '';
        update_option('eStore_aff_allow_aff_id', isset($_POST['eStore_aff_allow_aff_id']) ? '1':'' );
        update_option('eStore_aff_link_coupon_aff_id', isset($_POST['eStore_aff_link_coupon_aff_id']) ? '1':'' ); 
        update_option('eStore_aff_one_time_commission', isset($_POST['eStore_aff_one_time_commission']) ? '1':'' ); 
        update_option('eStore_aff_enable_revenue_sharing', isset($_POST['eStore_aff_enable_revenue_sharing']) ? '1':'' );
        $wp_eStore_config->setValue('eStore_enable_stats_for_author_role', isset($_POST['eStore_enable_stats_for_author_role']) ? '1':'' );
        update_option('eStore_aff_enable_lead_capture_for_sqeeze_form', isset($_POST['eStore_aff_enable_lead_capture_for_sqeeze_form']) ? '1':'' );
        update_option('eStore_create_auto_affiliate_account', isset($_POST['eStore_create_auto_affiliate_account']) ? '1':'' );
        update_option('eStore_aff_enable_commission_per_transaction', isset($_POST['eStore_aff_enable_commission_per_transaction']) ? '1':'' ); 
        update_option('eStore_aff_no_commission_if_coupon_used', isset($_POST['eStore_aff_no_commission_if_coupon_used']) ? '1':'' );

        update_option('eStore_eMember_must_be_logged_to_checkout', isset($_POST['eStore_eMember_must_be_logged_to_checkout']) ? '1':'' );
        update_option('eStore_eMember_redirection_url_when_not_logged', (string)$_POST["eStore_eMember_redirection_url_when_not_logged"]);
        
	eStore_dlmgradm::settings_menu_post($_POST['eStore_auto_convert_to_relative_url'], $_POST['eStore_download_method']);
	$retVal .= eStore_as3tpadm::settings_menu_post($_POST['eStore_as3tp_aws_acckey'], $_POST['eStore_as3tp_aws_seckey'], $_POST['eStore_as3tp_expiry']);
	if($retVal != TRUE) $retVal .= '<br />';
        update_option('eStore_lic_mgr_post_url', isset($_POST["eStore_lic_mgr_post_url"])? $_POST["eStore_lic_mgr_post_url"]:'');
        update_option('eStore_lic_mgr_secret_word', isset($_POST["eStore_lic_mgr_secret_word"])? $_POST["eStore_lic_mgr_secret_word"]:'');

        update_option('wp_eStore_use_recaptcha', isset($_POST['wp_eStore_use_recaptcha']) ? '1':'' );
        update_option('wp_eStore_captcha_public_key', (string)$_POST["wp_eStore_captcha_public_key"]);
        update_option('wp_eStore_captcha_private_key', (string)$_POST["wp_eStore_captcha_private_key"]);
        
        $wp_eStore_config->saveConfig();
        
        echo '<div id="message" class="updated fade"><p><strong>';        
        echo 'Options Updated!';
        echo '<br />Return Value: '.$retVal;
        echo '</strong></p></div>';
    }
    $defaultEmail = get_option('cart_paypal_email');
    if (empty($defaultEmail)) $defaultEmail = get_bloginfo('admin_email');
    
	?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />

	<div class="postbox">
	<h3><label for="title">WP Affiliate Platform Plugin Specific Settings</label></h3>
	<div class="inside">

    <br /><strong>Only use these options if you are using the <a href="http://www.tipsandtricks-hq.com/?p=1474" target="_blank">WP Affiliate Platform Plugin</a></strong>
    <br /><br />

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
      
    <tr valign="top"><td width="25%" align="left">
    Allow Affiliate ID Entry 
    </td><td align="left">
    <input name="eStore_aff_allow_aff_id" type="checkbox"<?php if(get_option('eStore_aff_allow_aff_id')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will give the customer an option to enter an "Affiliate ID" in the shopping cart to give reward to an affiliate</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Link Affiliate ID with Coupons Table 
    </td><td align="left">
    <input name="eStore_aff_link_coupon_aff_id" type="checkbox"<?php if(get_option('eStore_aff_link_coupon_aff_id')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the customer entering an "Affiliate ID" in the shopping cart will also be able to receive a discount if there is a coupon code in the coupons table that matches with the Affiliate ID. Useful when trying to promote a special deal with an affiliate</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    One Time Commission for Subscription Payment 
    </td><td align="left">
    <input name="eStore_aff_one_time_commission" type="checkbox"<?php if(get_option('eStore_aff_one_time_commission')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will award commission only once for the subscription payment. Recurring payments will not award affiliate commission.</p>
    </td></tr>            
    
    <tr valign="top"><td width="25%" align="left">
    Enable Revenue Sharing
    </td><td align="left">
    <input name="eStore_aff_enable_revenue_sharing" type="checkbox"<?php if(get_option('eStore_aff_enable_revenue_sharing')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will enable the revenue sharing feature. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=930" target="_blnak">Read More Here</a></p>
    </td></tr>  

    <tr valign="top"><td width="25%" align="left">
    Add Another Stats Menu for Users with Author Role
    </td><td align="left">
    <input name="eStore_enable_stats_for_author_role" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_enable_stats_for_author_role')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">If you want your WordPress users with a role of Author or above to be able to see the eStore stats menu then check this option. This is helpful when you want another user (example: revenue sharing partner) to be able to see the stats without giving them admin privilege.</p>
    </td></tr>  
    
    <tr valign="top"><td width="25%" align="left">
    Capture Lead on Squeeze Form Submission
    </td><td align="left">
    <input name="eStore_aff_enable_lead_capture_for_sqeeze_form" type="checkbox"<?php if(get_option('eStore_aff_enable_lead_capture_for_sqeeze_form')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will capture the lead on <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=126" target="_blnak">sqeeze form</a> submission if this user was referred by an affiliate.</p>
    </td></tr>  
       
    <?php if(function_exists('wp_aff_check_if_account_exists')){ ?>            
    <tr valign="top"><td width="25%" align="left">
    Automatically Create Affiliate Account After Purchase
    </td><td align="left">
    <input name="eStore_create_auto_affiliate_account" type="checkbox"<?php if(get_option('eStore_create_auto_affiliate_account')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will automatically create an affiliate account with a unique affiliate ID for the buyer after the purchase. If the email address used to make the purchase already exists in the affiliate database then a new account will not be created.</p>
    </td></tr>     
    <?php } ?>

    <tr valign="top"><td width="25%" align="left">
    Award Fixed Commission Per Transaction
    </td><td align="left">
    <input name="eStore_aff_enable_commission_per_transaction" type="checkbox"<?php if(get_option('eStore_aff_enable_commission_per_transaction')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">By default the commission is awarded for every item purchased from your store. When you check this option the plugin will award the commission once per transaction. This can be useful when you want to give out a fixed commission per transaction rather than for every item purchased in that transaction.</p>
    </td></tr>  

    <tr valign="top"><td width="25%" align="left">
    Do Not Award Commission if Coupon Used
    </td><td align="left">
    <input name="eStore_aff_no_commission_if_coupon_used" type="checkbox"<?php if(get_option('eStore_aff_no_commission_if_coupon_used')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">If you do not want to award commission for transactions where a coupon code is used then check this option.</p>
    </td></tr>  
                        
    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">WP eMember Plugin Specific Settings</label></h3>
	<div class="inside">

    <br /><strong>Only use these options if you are using the <a href="http://www.tipsandtricks-hq.com/?p=1706" target="_blank">WP eMember Plugin</a></strong>
    <br /><br />

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    Only Logged In Members Can Checkout 
    </td><td align="left">
    <input name="eStore_eMember_must_be_logged_to_checkout" type="checkbox"<?php if(get_option('eStore_eMember_must_be_logged_to_checkout')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will not allow an anonymous user to continue shopping cart checkout unless the user logs in or registers for an account in eMember.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Redirection URL for Anonymous Checkout
    </td><td align="left">
    <input name="eStore_eMember_redirection_url_when_not_logged" type="text" size="100" value="<?php echo get_option('eStore_eMember_redirection_url_when_not_logged'); ?>"/>
    <br /><p class="description">When an anonymous user clicks the "Checkout" button in the shopping cart, the user will be reditected to this URL requesting him/her to log in or register for an account.</p>
    </td></tr>        
    
    </table>
    </div></div>
    
<?php if (function_exists('wp_lic_manager_install')){ ?>
	<div class="postbox">
	<h3><label for="title">WP License Manager Plugin Specific Settings</label></h3>
	<div class="inside">

    <br /><strong>Only use these options if you are using the <a href="http://www.tipsandtricks-hq.com/" target="_blank">WP License Manager Plugin</a></strong>
    <br /><br />

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    License Creation POST URL 
    </td><td align="left">
    <input name="eStore_lic_mgr_post_url" type="text" size="100" value="<?php echo get_option('eStore_lic_mgr_post_url'); ?>"/>
    <br /><p class="description">Get this value from the WP License Manager plugin's settings tab</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    License Creation Secret Word
    </td><td align="left">
    <input name="eStore_lic_mgr_secret_word" type="text" size="50" value="<?php echo get_option('eStore_lic_mgr_secret_word'); ?>"/>
    <br /><p class="description">Get this value from the WP License Manager plugin's settings tab</p>
    </td></tr>        
    
    </table>
    </div></div>
<?php } ?>
    
	<div class="postbox">
	<h3><label for="title">WP eStore Download Manager Related</label></h3>
	<div class="inside">
	<?php echo eStore_dlmgradm::settings_menu_html(); ?>
	</div></div>
        
	<div class="postbox">
	<h3><label for="title">Amazon Web Services (AWS) Simple Storage Service (S3) Related (<a href="http://www.tipsandtricks-hq.com/ecommerce/?p=1101" target="_blank">Read Integration Details</a>)</label></h3>
	<div class="inside">
	<?php echo eStore_as3tpadm::settings_menu_html(); ?>
	</div></div>
        
	<div class="postbox">
	<h3><label for="title">reCAPTCHA Settings (If you want to use <a href="http://recaptcha.net/learnmore.html" target="_blank">reCAPTCHA</a> then you need to get reCAPTCHA API keys from <a href="http://recaptcha.net/whyrecaptcha.html" target="_blank">here</a> and use in the settings below)</label></h3>
	<div class="inside">
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    Use reCAPTCHA
    </td><td align="left">
    <input name="wp_eStore_use_recaptcha" type="checkbox"<?php if(get_option('wp_eStore_use_recaptcha')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this box if you want to use <a href="http://recaptcha.net/learnmore.html" target="_blank">reCAPTCHA</a>.</p>
    </td></tr>
    <tr valign="top"><td width="25%" align="left">
    Public Key
    </td><td align="left">
    <input name="wp_eStore_captcha_public_key" type="text" size="100" value="<?php echo get_option('wp_eStore_captcha_public_key'); ?>"/>
    <br /><p class="description">The public key for the reCAPTCHA API</p>
    </td></tr>  
    <tr valign="top"><td width="25%" align="left">
    Private Key
    </td><td align="left">
    <input name="wp_eStore_captcha_private_key" type="text" size="100" value="<?php echo get_option('wp_eStore_captcha_private_key'); ?>"/>
    <br /><p class="description">The private key for the reCAPTCHA API</p>
    </td></tr>
    </table>
    </div></div>
            
    <div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="<?php _e('Update'); ?> &raquo;" />
    </div>
    </form>
    <?php        
}

?>
