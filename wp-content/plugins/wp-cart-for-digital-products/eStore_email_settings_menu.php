<?php 

function wp_eStore_email_settings()
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    
    if (isset($_POST['estore_email_settings_update']))
    {
        update_option('eStore_use_wp_mail', isset($_POST["eStore_use_wp_mail"])?'checked="checked"':'');
        $wp_eStore_config->setValue('eStore_email_content_type', trim($_POST["eStore_email_content_type"]));
        update_option('eStore_download_email_address', (string)$_POST["eStore_download_email_address"]);

        update_option('eStore_send_buyer_email', isset($_POST["eStore_send_buyer_email"])?'checked="checked"':'');
        update_option('eStore_buyer_email_subj', stripslashes((string)$_POST["eStore_buyer_email_subj"]));
        update_option('eStore_buyer_email_body', stripslashes((string)$_POST["eStore_buyer_email_body"]));
        update_option('eStore_notify_email_address', (string)$_POST["eStore_notify_email_address"]);
        update_option('eStore_seller_email_subj', stripslashes((string)$_POST["eStore_seller_email_subj"]));
        update_option('eStore_seller_email_body', stripslashes((string)$_POST["eStore_seller_email_body"]));
        $wp_eStore_config->setValue('eStore_add_payment_parameters_to_admin_email', isset($_POST["eStore_add_payment_parameters_to_admin_email"])?'1':'');  
    	//$wp_eStore_config->setValue('eStore_price_currency_position', trim($_POST["eStore_price_currency_position"]));

        $wp_eStore_config->setValue('eStore_squeeze_form_email_subject', stripslashes($_POST["eStore_squeeze_form_email_subject"]));
        $wp_eStore_config->setValue('eStore_squeeze_form_email_body', stripslashes($_POST["eStore_squeeze_form_email_body"]));
        
	$wp_eStore_config->saveConfig();
        
        echo '<div id="message" class="updated fade"><p><strong>';
        echo 'Email Settings Updated!';
        echo '</strong></p></div>';
    }
    
    if (get_option('eStore_use_wp_mail'))
        $eStore_use_wp_mail = 'checked="checked"';
    else
        $eStore_use_wp_mail = '';
    
    $eStore_email_content_type = $wp_eStore_config->getValue('eStore_email_content_type');
    if (empty($eStore_email_content_type)) $eStore_email_content_type = 'text';
    //--------------

    if (get_option('eStore_send_buyer_email'))
        $eStore_send_buyer_email = 'checked="checked"';
    else
        $eStore_send_buyer_email = '';

    $eStore_download_email_address = get_option('eStore_download_email_address');
    if (empty($eStore_download_email_address))
    {
    	$eStore_download_email_address = get_bloginfo('name')." <sales@your-domain.com>";//'sales@your-domain.com';
    }

    $eStore_buyer_email_subj = get_option('eStore_buyer_email_subj');
    if (empty($eStore_buyer_email_subj)) $eStore_buyer_email_subj = "Thank you for the purchase";

    $eStore_buyer_email_body = get_option('eStore_buyer_email_body');
    if (empty($eStore_buyer_email_body))
    {
        $eStore_buyer_email_body = "Dear {first_name} {last_name}".
			  "\n\nThank you for your purchase!".
			  "\n{product_details}".
			  "\n\nAny item(s) to be shipped will be processed as soon as possible, any digital item(s) can be downloaded using the encrypted links below.".
			  "\n{product_link}".
			  "\n\nThanks";
    }

    
    $eStore_notify_email_address = get_option('eStore_notify_email_address');
    //To allow admins to turn off admin notification email we are going to allow them to have an empty value here
    //if (empty($eStore_notify_email_address)){ $eStore_notify_email_address = get_bloginfo('admin_email'); }

    $eStore_seller_email_subj = get_option('eStore_seller_email_subj');
    if (empty($eStore_seller_email_subj)) $eStore_seller_email_subj = "Notification of product sale";

    $eStore_seller_email_body = get_option('eStore_seller_email_body');
    if (empty($eStore_seller_email_body))
    {
        $eStore_seller_email_body = "Dear Seller".
				"\n\nThis mail is to notify you of a product sale. Product Name: {product_name} Product ID: {product_id}".
				"\nThe sale was made to {first_name} {last_name} ({payer_email})".
				"\n\nThanks";
    }

    if ($wp_eStore_config->getValue('eStore_add_payment_parameters_to_admin_email') == '1')
        $eStore_add_payment_parameters_to_admin_email = 'checked="checked"';
    else
        $eStore_add_payment_parameters_to_admin_email = '';

    $eStore_squeeze_form_email_subject = $wp_eStore_config->getValue('eStore_squeeze_form_email_subject');
    if(empty($eStore_squeeze_form_email_subject)){
        $eStore_squeeze_form_email_subject = "Your Free Download Link";
    }
    $eStore_squeeze_form_email_body = $wp_eStore_config->getValue('eStore_squeeze_form_email_body');
    if(empty($eStore_squeeze_form_email_body)){
        $eStore_squeeze_form_email_body = 'Dear {first_name} {last_name}'.
                              "\n\nBelow is your download link:".
                              "\n{product_link}".
                              "\n\nThank You";
    }
    
    ?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

    <div class="postbox">
    <h3><label for="title">General Email Settings</label></h3>
    <div class="inside">
    
    <table class="form-table">
    <tr valign="top">
    <th scope="row">Use WordPress Mailing System</th>
    <td><input type="checkbox" name="eStore_use_wp_mail" value="1" <?php echo $eStore_use_wp_mail; ?> /><span class="description"> If checked the plugin will use the WordPress mail function to send emails (this is recommended). Otherwise it will use a simple PHP mail script that comes with this plugin.</span></td>
    </tr>    

    <tr valign="top">
    <th scope="row">Email Content Type</th>
    <td>
    <select name="eStore_email_content_type">
    <option value="text" <?php if($eStore_email_content_type=="text")echo 'selected="selected"';?>><?php echo "Plain Text" ?></option>
    <option value="html" <?php if($eStore_email_content_type=="html")echo 'selected="selected"';?>><?php echo "HTML" ?></option>
    </select>
    <p class="description">Choose which format of email to send. We recommend using plain text format as it has better email delivery rate. <a href="http://www.tipsandtricks-hq.com/forum/topic/why-use-plain-text-email-instead-of-html-email" target="_blank">Read More Here</a></p>
    </td></tr>    
    
    <tr valign="top">
    <th scope="row">From Email Address*</th>
    <td><input type="text" name="eStore_download_email_address" value="<?php echo $eStore_download_email_address; ?>" size="50" />
    <br /><p class="description">Example: Your Name &lt;sales@your-domain.com&gt; This is the email address that will be used to send the email to the buyer. This name and email address will appear in the from field of the email.</p></td>
    </tr>
    
    </table>
    </div></div>
        
    <div class="postbox">
    <h3><label for="title">Purchase Confirmation Email Settings</label></h3>
    <div class="inside">

    <p><i>The following options affect the emails that gets sent to your buyers after a purchase.</i></p>

    <table class="form-table">

    <tr valign="top">
    <th scope="row">Send Emails to Buyer After Purchase</th>
    <td><input type="checkbox" name="eStore_send_buyer_email" value="1" <?php echo $eStore_send_buyer_email; ?> /><span class="description"> If checked the plugin will send an email to the buyer with the sale details. If digital goods are purchased then the email will contain encrypted download links for the downloadable products.</a></span></td>
    </tr>

    <tr valign="top">
    <th scope="row">Buyer Email Subject*</th>
    <td><input type="text" name="eStore_buyer_email_subj" value="<?php echo $eStore_buyer_email_subj; ?>" size="50" />
    <br /><p class="description">This is the subject of the email that will be sent to the buyer.</p></td>
    </tr>

    <tr valign="top">
    <th scope="row">Buyer Email Body*</th>
    <td>
    <?php 
    $buyer_body_settings = array('textarea_name' => 'eStore_buyer_email_body');
    wp_editor($eStore_buyer_email_body, "eStore_buyer_email_body_content", $buyer_body_settings);
    ?>
    <br /><p class="description">This is the body of the email that will be sent to the buyer. Do not change the email tags (text within the braces {}). All the available <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=460#email_tags" target="_blank">email tags are listed here</a>. Please note that the manual checkout does not use this email settings (Check the "Directions for the Customer" field in the manual checkout settings section).</p></td>
    </tr>

    <tr valign="top">
    <th scope="row">Notification Email Address*</th>
    <td><input type="text" name="eStore_notify_email_address" value="<?php echo $eStore_notify_email_address; ?>" size="50" />
    <br /><p class="description">This is the email address where the seller will be notified of product sales. You can put multiple email addresses separated by comma (,) in the above field to send the notification to multiple email addresses.</p></td>
    </tr>

    <tr valign="top">
    <th scope="row">Seller Email Subject*</th>
    <td><input type="text" name="eStore_seller_email_subj" value="<?php echo $eStore_seller_email_subj; ?>" size="50" />
    <br /><p class="description">This is the subject of the email that will be sent to the seller for record.</p></td>
    </tr>

    <tr valign="top">
    <th scope="row">Seller Email Body*</th>
    <td>
    <?php 
    $seller_email_body_settings = array('textarea_name' => 'eStore_seller_email_body');
    wp_editor($eStore_seller_email_body, "eStore_seller_email_body_content", $seller_email_body_settings);
    ?>
    <br /><p class="description">This is the body of the email that will be sent to the seller for record. Do not change the text within the braces {}. All the available <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=460#email_tags" target="_blank">email tags are listed here</a></p></td>
    </tr>

    <tr valign="top">
    <th scope="row">Append Buyer Email and Payment Parameters to Admin Email</th>
    <td><input type="checkbox" name="eStore_add_payment_parameters_to_admin_email" value="1" <?php echo $eStore_add_payment_parameters_to_admin_email; ?> />
    <br /><p class="description">Check this if you want to append the buyer email body and other important payment parameters to the admin notification email body.</p></td>
    </tr>

    </table>    

    </div></div>

    <div class="postbox">
    <h3><label for="title">Squeeze Form Email Settings</label></h3>
    <div class="inside">
    
    <p><i>The following options affect the emails that gets sent to your users after a they submit a squeeze form.</i></p>
        
    <table class="form-table">
   
    <tr valign="top">
    <th scope="row">Squeeze Form Email Subject</th>
    <td><input type="text" name="eStore_squeeze_form_email_subject" value="<?php echo $eStore_squeeze_form_email_subject; ?>" size="50" />
    <p class="description">Specify the subject of the email that will be sent to user when a squeeze form is submitted.</p></td>
    </tr>
    
    <tr valign="top">
    <th scope="row">Squeeze Form Email Body</th>
    <td>
    <?php 
    $squeeze_form_email_body_settings = array('textarea_name' => 'eStore_squeeze_form_email_body');
    wp_editor($eStore_squeeze_form_email_body, "eStore_squeeze_form_email_body_content", $squeeze_form_email_body_settings);
    ?>
    <p class="description">Specify the email body that will be sent to the user when a squeeze form is submitted. Do not change the text within the braces {}. You can use the following email tags in this email:
    <br />{first_name} – First name of the user
    <br />{last_name} – Last name of the user
    <br />{product_link} – Download link of the product the squeeze form is giving.
    </p></td>
    </tr>
    
    </table>
    </div></div>

    <div class="submit">
    	<input type="submit" class="button-primary" name=estore_email_settings_update value="Update &raquo;" />
    </div>
    </form>
    <?php     
}
