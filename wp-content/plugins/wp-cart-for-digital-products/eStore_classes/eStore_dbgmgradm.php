<?php
class eStore_dbgmgradm {
	function settings_menu_html() {
	// Return HTML code for debug manager related settings menu.
	// -- The Assurer, 2010-10-18.
		// Generate HTML menu code...
		$html_code = '<table class="form-table">';
			// First, generate HTML code for "Enable Debug" checkbox...
			$html_code .= '<tr valign="top"><th scope="row">Enable Debug</th>'.
				'<td><input type="checkbox" name="eStore_cart_enable_debug" ';
    			if(get_option('eStore_cart_enable_debug') != '') $html_code .= 'checked="checked" ';
			$html_code .= 'value="1"/><span class="description"> If checked, debug output will be written to log files.  This is useful for troubleshooting post payment failures (for example, if you are not receiving the email after payment).  <a href="http://www.tipsandtricks-hq.com/forum/topic/how-and-when-to-enable-debug-and-what-does-it-do" target="_blank">Read More Here</a><br /><br />You can check all the debug log files by clicking on the links below (The log files can be viewed using any text editor):</span>';
			// Generate links to the log files...
			$html_code .= '<li style="margin-left:15px;"><a href="'.WP_ESTORE_URL.'/'.ESTORE_LOGFILE_IPN.'" target="_blank">'.ESTORE_LOGFILE_IPN.'</a></li>'.
				'<li style="margin-left:15px;"><a href="'.WP_ESTORE_URL.'/'.ESTORE_LOGFILE_POSTPAYMENT.'" target="_blank">'.ESTORE_LOGFILE_POSTPAYMENT.'</a></li>'.
				'<li style="margin-left:15px;"><a href="'.WP_ESTORE_URL.'/'.ESTORE_LOGFILE_SUBSCRIPTION.'" target="_blank">'.ESTORE_LOGFILE_SUBSCRIPTION.'</a></li>'.
				'<li style="margin-left:15px;"><a href="'.WP_ESTORE_URL.'/'.ESTORE_LOGFILE_SQUEEZE_FORM.'" target="_blank">'.ESTORE_LOGFILE_SQUEEZE_FORM.'</a></li>'.
				'<li style="margin-left:15px;"><a href="'.WP_ESTORE_URL.'/'.ESTORE_LOGFILE_DLMGR.'" target="_blank">'.ESTORE_LOGFILE_DLMGR.'</a></li>';
			// A log file "reset" button...
			$html_code .= '<div class="submit"><input type="submit" class="button" name="reset_logfiles" style="font-weight:bold; color:red" value="Reset Debug Log files"/><p class="description">All of the above debug log files will be "reset" and timestamped with a log file reset message.</p></div></td></tr>';
			// And the "Enable Sandbox" checkbox...
			$html_code .= '<tr valign="top"><th scope="row">Enable Sandbox Testing</th>'.
				'<td><input type="checkbox" name="eStore_cart_enable_sandbox" value="1" ';
    			if(get_option('eStore_cart_enable_sandbox') != '') $html_code .= 'checked="checked" ';
			$html_code .= 'value="1"/><span class="description"> If checked the plugin will run in Sandbox/Testing mode (eg. PayPal Sandbox).  Useful for testing <a href="http://tipsandtricks-hq.com/ecommerce/?p=35" target="_blank">Read More Here</a></span></td></tr></table>';
		return $html_code;
	}

	function settings_menu_post($eStore_cart_enable_debug, $eStore_cart_enable_sandbox) {
	// Save debug manager related settings.
	// -- The Assurer, 2010-10-18.
        	update_option('eStore_cart_enable_debug', ($eStore_cart_enable_debug == '1' ? '1' : ''));
        	update_option('eStore_cart_enable_sandbox', ($eStore_cart_enable_sandbox == '1' ? '1' : ''));
	}
}
?>
