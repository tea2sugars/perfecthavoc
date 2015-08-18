<?php
function wp_eStore_payment_gateway_settings()
{
    global $wp_eStore_config;
    $wp_eStore_config = WP_eStore_Config::getInstance();
	
    if (isset($_POST['info_update']))
    {
        update_option('eStore_use_multiple_gateways', isset($_POST["eStore_use_multiple_gateways"])?'1':'');
        update_option('eStore_use_manual_gateway_for_zero_dollar_co', isset($_POST["eStore_use_manual_gateway_for_zero_dollar_co"])?'1':'');
        
        update_option('eStore_use_paypal_gateway', isset($_POST["eStore_use_paypal_gateway"])?'1':'');
        update_option('cart_paypal_email', trim($_POST["cart_paypal_email"]));
        update_option('eStore_paypal_profile_shipping', isset($_POST["eStore_paypal_profile_shipping"])?'1':'');
        update_option('eStore_paypal_return_button_text', stripslashes((string)$_POST["eStore_paypal_return_button_text"]));
        update_option('eStore_paypal_co_page_style', trim($_POST["eStore_paypal_co_page_style"]));
        update_option('eStore_paypal_pdt_token', trim($_POST["eStore_paypal_pdt_token"]));
        $wp_eStore_config->setValue('eStore_pp_collect_instruction_enabled', isset($_POST["eStore_pp_collect_instruction_enabled"])?'1':'');
        $wp_eStore_config->setValue('eStore_pp_instruction_label', ($_POST["eStore_pp_instruction_label"]));        
        
        update_option('eStore_use_manual_gateway', isset($_POST["eStore_use_manual_gateway"])?'1':'');
        update_option('eStore_manual_notify_email', trim($_POST["eStore_manual_notify_email"]));
        $wp_eStore_config->setValue('buyer_email_subject_manual_co', trim($_POST["buyer_email_subject_manual_co"]));
        $wp_eStore_config->setValue('seller_email_subject_manual_co', trim($_POST["seller_email_subject_manual_co"]));
        update_option('eStore_manual_co_cust_direction', stripslashes((string)$_POST["eStore_manual_co_cust_direction"]));
        update_option('eStore_manual_return_url', trim($_POST["eStore_manual_return_url"]));
        update_option('eStore_manual_co_do_not_collect_shipping_charge', isset($_POST["eStore_manual_co_do_not_collect_shipping_charge"])?'1':'');
        update_option('eStore_manual_co_give_aff_commission', isset($_POST["eStore_manual_co_give_aff_commission"])?'1':'');        
        update_option('eStore_manual_co_auto_update_db', isset($_POST["eStore_manual_co_auto_update_db"])?'1':'');
        $wp_eStore_config->setValue('eStore_manual_co_auto_create_membership', isset($_POST["eStore_manual_co_auto_create_membership"])?'1':'');
        update_option('eStore_manual_co_do_autoresponder_signup', isset($_POST["eStore_manual_co_do_autoresponder_signup"])?'1':'');          
        update_option('eStore_manual_co_give_download_links', isset($_POST["eStore_manual_co_give_download_links"])?'1':'');		
        $wp_eStore_config->setValue('eStore_on_page_manual_checkout_page_url', trim($_POST["eStore_on_page_manual_checkout_page_url"])); 
		
        update_option('eStore_use_2co_gateway', isset($_POST["eStore_use_2co_gateway"])?'1':'');
        update_option('eStore_2co_vendor_id', trim($_POST["eStore_2co_vendor_id"]));
        update_option('eStore_2co_secret_word', trim($_POST["eStore_2co_secret_word"]));

        update_option('eStore_use_authorize_gateway', isset($_POST["eStore_use_authorize_gateway"])?'1':'');
        update_option('eStore_authorize_login', trim($_POST["eStore_authorize_login"]));
        update_option('eStore_authorize_tx_key', trim($_POST["eStore_authorize_tx_key"]));
        
        $wp_eStore_config->saveConfig();
        
        echo '<div id="message" class="updated fade"><p><strong>';
        echo 'Options Updated!';
        echo '</strong></p></div>';
    }
    $defaultEmail = get_option('cart_paypal_email');
    if (empty($defaultEmail)) $defaultEmail = get_bloginfo('admin_email');
    
	?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />

	<div class="postbox">
	<h3><label for="title">General Payment Gateway Settings</label></h3>
	<div class="inside">

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    Use Multiple Payment Gateways
    </td><td align="left">
    <input name="eStore_use_multiple_gateways" type="checkbox"<?php if(get_option('eStore_use_multiple_gateways')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the plugin will give the customer an option to select a payment method (Example: PayPal, 2Checkout, Manual).</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Redirect to Manual Checkout For Zero Amount Purchase
    </td><td align="left">
    <input name="eStore_use_manual_gateway_for_zero_dollar_co" type="checkbox"<?php if(get_option('eStore_use_manual_gateway_for_zero_dollar_co')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Payment gateways like PayPal do not allow customers to checkout if an item has zero amount. If you want to give your customers item(s) for free then you can use this option together with the manual checkout feature. Ideally you should be using a <a href="http://tipsandtricks-hq.com/ecommerce/?p=126" target="_blank">squeeze page type form</a> to give away free items.</p>
    </td></tr>    

    </table>
    </div></div>

    <div class="postbox">
    <h3><label for="title">PayPal Settings</label></h3>
    <div class="inside">

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    Use PayPal Payment Gateway
    </td><td align="left">
    <input name="eStore_use_paypal_gateway" type="checkbox"<?php if(get_option('eStore_use_paypal_gateway')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the customers will be able to checkout through PayPal.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Paypal Email Address/Secure Merchant ID
    </td><td align="left">
    <input name="cart_paypal_email" type="text" size="40" value="<?php echo $defaultEmail; ?>"/>
    <br /><p class="description">Your PayPal email address (this is the account where the payments will go to)</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Use PayPal Profile Based Shipping
    </td><td align="left">
    <input name="eStore_paypal_profile_shipping" type="checkbox"<?php if(get_option('eStore_paypal_profile_shipping')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this if you want to use PayPal profile based shipping that you have configured in your PayPal account. Using this will ignore any other shipping options that you have specified in this plugin.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Customize the Return Button Text
    </td><td align="left">
    <input name="eStore_paypal_return_button_text" type="text" size="40" value="<?php echo get_option('eStore_paypal_return_button_text'); ?>"/>
    <br /><p class="description">Use this if you want to customize the return button text that is shown to your customers on the payment confirmation page on PayPal. The button text is "Return To Merchant" by default</p>
    </td></tr>
 
    <tr valign="top"><td width="25%" align="left">
    Collect Instructions on PayPal Checkout Page
    </td><td align="left">
    Enable Instruction Collection 
    <input name="eStore_pp_collect_instruction_enabled" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_pp_collect_instruction_enabled')!='') echo ' checked="checked"'; ?> value="1"/>
    <br />Instruction Field Label 
    <input name="eStore_pp_instruction_label" type="text" size="40" value="<?php echo $wp_eStore_config->getValue('eStore_pp_instruction_label'); ?>"/>
    <p class="description">Use this option if you want to collect instructions from your customers on the PayPal checkout page.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Custom Checkout Page Style Name
    </td><td align="left">
    <input name="eStore_paypal_co_page_style" type="text" size="40" value="<?php echo get_option('eStore_paypal_co_page_style'); ?>"/>
    <br /><p class="description">Specify the page style name here if you want to customize the paypal checkout page with custom page style otherwise leave this field empty.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    PDT Identity Token
    </td><td align="left">
    <input name="eStore_paypal_pdt_token" type="text" size="100" value="<?php echo get_option('eStore_paypal_pdt_token'); ?>"/>
    <br /><p class="description">Specify your identity token in the text field above if you want to <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=499" target="_blank">display the transaction result on the Thank You page</a>. If you need help finding your token then <a href="http://www.tipsandtricks-hq.com/forum/topic/how-do-i-setup-paypal-pdt-and-get-my-paypal-pdt-token-id" target="_blank">click here</a>.</p>
    </td></tr>
        
    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">Manual / Off-line Checkout Settings</label></h3>
	<div class="inside">

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    Use Manual Payment Option
    </td><td align="left">
    <input name="eStore_use_manual_gateway" type="checkbox"<?php if(get_option('eStore_use_manual_gateway')!='') echo ' checked="checked"'; ?> value="1"/>
     Read the various <a href="http://www.tipsandtricks-hq.com/forum/topic/wp-estores-manualoffline-checkout-methods-how-manual-checkout-works" target="_blank">manual checkout setup options</a> available in the eStore plugin.
    <br /><p class="description">When checked the customers will be able to checkout using a Manual process.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Notification Email Address for Seller
    </td><td align="left">
    <input name="eStore_manual_notify_email" type="text" size="40" value="<?php echo get_option('eStore_manual_notify_email'); ?>"/>
    <br /><p class="description">This is where the email containg the products and customer details will be sent to when a customer checks out using the manual checkout method.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Buyer Email Subject for Manual Checkout
    </td><td align="left">
    <input name="buyer_email_subject_manual_co" type="text" size="60" value="<?php echo $wp_eStore_config->getValue('buyer_email_subject_manual_co'); ?>"/>
    <br /><p class="description">This is the email subject that will be used to notify the buyer when a customer checks out using the manual checkout method.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Seller Email Subject for Manual Checkout
    </td><td align="left">
    <input name="seller_email_subject_manual_co" type="text" size="60" value="<?php echo $wp_eStore_config->getValue('seller_email_subject_manual_co'); ?>"/>
    <br /><p class="description">This is the email subject that will be used to notify the seller when a customer checks out using the manual checkout method.</p>
    </td></tr>
                
	<tr valign="top"><td width="25%" align="left">
	Directions for the Customer
	</td><td align="left">
	<textarea name="eStore_manual_co_cust_direction" rows="6" cols="80"><?php echo get_option('eStore_manual_co_cust_direction'); ?></textarea>
	<br /><p class="description">You can put direction for payment here. The customer will receive this in the order confirmation email. The followiong tags can be used in this field {first_name}, {last_name}, {payer_email}, {transaction_id}, {counter}.</p>
	</td></tr>

    <tr valign="top"><td width="25%" align="left">
    Return URL
    </td><td align="left">
    <input name="eStore_manual_return_url" type="text" size="100" value="<?php echo get_option('eStore_manual_return_url'); ?>"/>
    <br /><p class="description">This is where the customers will be redirected to after they complete the manual checkout.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Do Not Add Shipping Charge
    </td><td align="left">
    <input name="eStore_manual_co_do_not_collect_shipping_charge" type="checkbox"<?php if(get_option('eStore_manual_co_do_not_collect_shipping_charge')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">If you do not want to charge shipping to customers who checkout via manual checkout (for example: if you are using store pickup) then check this option.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Automatically Update Customer & Products Database
    </td><td align="left">
    <input name="eStore_manual_co_auto_update_db" type="checkbox"<?php if(get_option('eStore_manual_co_auto_update_db')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Usually the customers and products database are updated after a confirmed payment. In the event of manual payment the plugin has no way of verifying the payment. Checking this option will update the customers and products database after the manual checkout submission. Alternatively, you can uncheck this option and manually enter the data after you receive the money from the customer.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Create Membership Account (<a href="http://www.tipsandtricks-hq.com/wordpress-emember-easy-to-use-wordpress-membership-plugin-1706" target="_blank">WP eMember Plugin</a>)
    </td><td align="left">
    <input name="eStore_manual_co_auto_create_membership" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_manual_co_auto_create_membership')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Usually the membership account is created after a confirmed payment. Checking this option will create any necessary membership account after the manual checkout submission.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Perform Autoresponder Signup
    </td><td align="left">
    <input name="eStore_manual_co_do_autoresponder_signup" type="checkbox"<?php if(get_option('eStore_manual_co_do_autoresponder_signup')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Usually autoresponder signup is performed for a confirmed payment. Check this option if you want to perform autoresponder signup as soon as the customer submits the manual checkout form.</p>
    </td></tr>
        
    <tr valign="top"><td width="25%" align="left">
    Automatically Award Affiliate Commission
    </td><td align="left">
    <input name="eStore_manual_co_give_aff_commission" type="checkbox"<?php if(get_option('eStore_manual_co_give_aff_commission')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When checked the affiliate commission will be automatically awarded to the appropriate affiliate after the manual checkout submission.</p>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Send Product Download Links in the Email
    </td><td align="left">
    <input name="eStore_manual_co_give_download_links" type="checkbox"<?php if(get_option('eStore_manual_co_give_download_links')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Usually the product download links are given to the customer after a confirmed payment. In the event of manual payment the plugin has no way of verifying the payment so it is preferred that you manually give out the links after you receive the payment. Checking this option will force the plugin to send out download links in the manual checkout email that the customer receives after checkout.</p>
    </td></tr>    

    <tr valign="top"><td width="25%" align="left">
    Embed Manual Checkout Form on a WordPress Page (optional)
    </td><td align="left">
    <input name="eStore_on_page_manual_checkout_page_url" type="text" size="100" value="<?php echo $wp_eStore_config->getValue('eStore_on_page_manual_checkout_page_url'); ?>"/>    
    <br /><p class="description">If you want to place the manual checkout form on a WordPress page then create a page and use the <strong>[wp_eStore_on_page_manual_gateway_form]</strong> shortcode on that page. Specify the URL of that page in the above field so that eStore can send your customers to this page when they choose to pay using manual method.</p>
    </td></tr> 
        
    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">2Checkout Settings</label></h3>
	<div class="inside">

	<strong><i>(Please make sure to setup your 2Checkout merchant account by following <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=1075" target="_blank">this instruction</a> first)</i></strong>
	<br /><br />
	
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    Use 2Checkout Payment Gateway
    </td><td align="left">
    <input name="eStore_use_2co_gateway" type="checkbox"<?php if(get_option('eStore_use_2co_gateway')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When enabled, your customers will be able to checkout using <a href="https://www.2checkout.com/referral?r=tips2co" target="_blank">2Checkout</a> payment gateway.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    2Checkout Vendor ID
    </td><td align="left">
    <input name="eStore_2co_vendor_id" type="text" size="20" value="<?php echo get_option('eStore_2co_vendor_id'); ?>"/>
    <br /><p class="description">Your 2Checkout vendor ID.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    2Checkout Secret Word
    </td><td align="left">
    <input name="eStore_2co_secret_word" type="text" size="20" value="<?php echo get_option('eStore_2co_secret_word'); ?>"/>
    <br /><p class="description">Your 2Checkout Secret Word. <a href="http://help.2checkout.com/articles/FAQ/Where-do-I-set-up-the-Secret-Word/" target="_blank">How to find the secret word?</a></p>
    </td></tr>

    </table>
    </div></div>

	<div class="postbox">
	<h3><label for="title">Authorize.net Settings</label></h3>
	<div class="inside">

    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    Use Authorize.net Payment Gateway
    </td><td align="left">
    <input name="eStore_use_authorize_gateway" type="checkbox"<?php if(get_option('eStore_use_authorize_gateway')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">When enabled, your customers will be able to checkout using <a href="http://www.authorize.net/" target="_blank">Authorize.net</a> payment gateway.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Authorize.net Login
    </td><td align="left">
    <input name="eStore_authorize_login" type="text" size="20" value="<?php echo get_option('eStore_authorize_login'); ?>"/>
    <br /><p class="description">API login ID for the payment gateway account.</p>
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    Authorize.net Transaction Key
    </td><td align="left">
    <input name="eStore_authorize_tx_key" type="text" size="20" value="<?php echo get_option('eStore_authorize_tx_key'); ?>"/>
    <br /><p class="description">Transaction key obtained from the Authorize.net Merchant Interface.</p>
    </td></tr>

    </table>
    </div></div>
    
<?php if(defined('WP_PAYMENT_GATEWAY_BUNDLE_VERSION')){ ?>

	<div class="postbox">
	<h3><label for="title">Additional Payment Gateways From Payment Gateway Bundle</label></h3>
	<div class="inside">
	<ul>	
	<li>If you want to use Google Checkout then please configure Google Checkout Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>	

	<li>If you want to use PayPal Payments Pro then please configure PayPal Payments Pro Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>	
		
	<li>If you want to use SagePay payment gateway then please configure SagePay Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>	
		
	<li>If you want to use Authorize.net AIM (Advanced Integration Method) or ARB then please configure Authorize.net AIM Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>	
		
	<li>If you want to use eWAY then please configure the eWAY gateway Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>	
	
	<li>If you want to use ePay.dk then please configure the ePay.dk gateway Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>	
	
	<li>If you want to use Verotel (adult payment gateway) then please configure the Verotel gateway Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>
	
	<li>If you want to use ClickBank then please configure the ClickBank Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>
	
	<li>If you want to use FreshBooks then please configure the FreshBooks Settings from the <a href="admin.php?page=wp-payment-gateway&action=gateway" target="_blank">WP Payment Gateway Bundle Settings Menu</a></li>
	</ul>			
    </div></div>
                      
<?php } ?>
    
    <div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="<?php _e('Update'); ?> &raquo;" />
    </div>
    </form>
    <br />
    <?php
}
?>