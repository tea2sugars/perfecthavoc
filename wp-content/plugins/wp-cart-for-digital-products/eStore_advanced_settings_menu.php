<?php 

function wp_eStore_advanced_settings()
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
	
    if (isset($_POST['advanced_settings_update']))
    {
    	$wp_eStore_config->setValue('eStore_price_currency_position', trim($_POST["eStore_price_currency_position"]));
    	$wp_eStore_config->setValue('eStore_price_decimal_separator', trim($_POST["eStore_price_decimal_separator"]));
    	$wp_eStore_config->setValue('eStore_price_thousand_separator', trim($_POST["eStore_price_thousand_separator"]));
    	$wp_eStore_config->setValue('eStore_price_num_decimals', trim($_POST["eStore_price_num_decimals"]));
    	//$wp_eStore_config->setValue('eStore_price_thousand_separator', isset($_POST["eStore_use_2co_gateway"])?'1':'');
        
        update_option('eStore_ppv_verification_failed_url', trim($_POST["eStore_ppv_verification_failed_url"])); 
        
        /* Miscellaneous Advanced Settings */
        $wp_eStore_config->setValue('eStore_use_new_checkout_redirection',($_POST["eStore_use_new_checkout_redirection"]=='1')?1:'');
        $wp_eStore_config->setValue('eStore_use_custom_text_for_thank_you_page_dl',($_POST["eStore_use_custom_text_for_thank_you_page_dl"]=='1')?1:'');
        $wp_eStore_config->setValue('eStore_use_ajax_on_add_to_cart_buttons',($_POST["eStore_use_ajax_on_add_to_cart_buttons"]=='1')?1:'');
        /* end of Miscellaneous Advanced Settings */
        
	$wp_eStore_config->saveConfig();
        
        echo '<div id="message" class="updated fade"><p><strong>';
        echo 'Options Updated!';
        echo '</strong></p></div>';
    }
    
    $curr_position = $wp_eStore_config->getValue('eStore_price_currency_position');
    if(empty($curr_position)){$curr_position = "left";}
    
	?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

	<div class="postbox">
	<h3><label for="title">Price Display Settings</label></h3>
	<div class="inside">

	<p>The following options affect how prices are displayed on the frontend.</p>
	
    <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
    
	<tr valign="top">
	<th scope="row">Currency Position</th>
	<td>
	<select name="eStore_price_currency_position">
	<option value="left" <?php if($curr_position=="left")echo 'selected="selected"';?>>Left</option>
	<option value="right" <?php if($curr_position=="right")echo 'selected="selected"';?>>Right</option>
	</select>
	<p class="description">This controls the position of the currency symbol.</p>
	</td></tr>

	<tr valign="top">
	<th scope="row">Decimal Separator</th>
	<td>
	<input name="eStore_price_decimal_separator" type="text" size="5" value="<?php echo $wp_eStore_config->getValue('eStore_price_decimal_separator'); ?>"/>
	<p class="description">This sets the decimal separator of the displayed price.</p>
	</td></tr>
	
	<tr valign="top">
	<th scope="row">Thousand Separator</th>
	<td>
	<input name="eStore_price_thousand_separator" type="text" size="5" value="<?php echo $wp_eStore_config->getValue('eStore_price_thousand_separator'); ?>"/>
	<p class="description">This sets the thousand separator of the displayed price.</p>
	</td></tr>

	<tr valign="top">
	<th scope="row">Number of Decimals</th>
	<td>
	<input name="eStore_price_num_decimals" type="text" size="5" value="<?php echo $wp_eStore_config->getValue('eStore_price_num_decimals'); ?>"/>
	<p class="description">This sets the number of decimal points shown in the displayed price.</p>
	</td></tr>
	
    </table>
    </div></div>
    
    <div class="postbox">
    <h3><label for="title">Pay Per View Content Settings</label></h3>
    <div class="inside">
    <table class="form-table">
    <tr valign="top">
    <th scope="row">Redirection Page for Unauthorized Access</th>
    <td><input type="text" name="eStore_ppv_verification_failed_url" value="<?php echo get_option('eStore_ppv_verification_failed_url'); ?>" size="70" />
    <br /><p class="description">Visitors will be redirected to this page when trying to access the Pay Per View URL without clicking on a valid link. Only use this settings if you are selling Pay Per View content.</p></td>
    </tr>
    </table>
    </div></div>
        
    <div class="postbox">
    <h3><label for="title">Miscellaneous Advanced Settings</label></h3>
    <div class="inside">
    <table class="form-table">
        
    <tr valign="top">
    <th scope="row">Enable Alternate Redirection Method</th>        
    <td align="left">    
    <input name="eStore_use_new_checkout_redirection" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_use_new_checkout_redirection')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this option if you want to enable the alternate redirection method. Use it if we have advised you to do so.</p>
    </td></tr>
    
    <tr valign="top">
    <th scope="row">Use Custom Anchor Text for the Thank You page Download Link</th>        
    <td align="left">    
    <input name="eStore_use_custom_text_for_thank_you_page_dl" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_use_custom_text_for_thank_you_page_dl')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this option if you want to show a more user-friendly clickable text for the corresponding download link on the Thank You page. By default the link itself is displayed as the clickable anchor text.</p>
    </td></tr>
    
    <tr valign="top">
    <th scope="row">Enable Ajax on Add to Cart Buttons</th>        
    <td align="left">    
    <input name="eStore_use_ajax_on_add_to_cart_buttons" type="checkbox"<?php if($wp_eStore_config->getValue('eStore_use_ajax_on_add_to_cart_buttons')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><p class="description">Check this option if you want to enable ajax effect on your Add to Cart buttons. This will allow you to add products to the shopping cart without refreshing the page.</p>
    </td></tr>
    
    </table>
    </div></div>
        
    <div class="submit">
    	<input type="submit" class="button-primary" name=advanced_settings_update value="<?php _e('Update'); ?> &raquo;" />
    </div>
    </form>
    <?php     
}
