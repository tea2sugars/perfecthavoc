<?php
class eStore_dlmgradm {
	function settings_menu_html() {
	// Return HTML code for download manager related settings menu.
		// Check various download method prerequisites...
		$can_remote_fopen = ((ini_get('allow_url_fopen') == '1') ? TRUE : FALSE);
		$can_curl = (function_exists('curl_init') ? TRUE : FALSE);
		// If cURL method selected, but cURL not installed, force default method selection...
		$dl_method = get_option('eStore_download_method');
		if(($dl_method == '7') && (!$can_curl)) {
			$dl_method = '1';
			update_option('eStore_download_method', $dl_method);
		}
		// Get current auto conversion preference...
		$auto_convert = get_option('eStore_auto_convert_to_relative_url');
		if($auto_convert == '') {
		// Deal with old-style preference...
			$auto_convert = '0';
			update_option('eStore_auto_convert_to_relative_url', $auto_convert);
		}
		// Generate HTML menu code...
		$html_code = 'You shouldn\'t have to change any of these settings, unless instructed.<br /><br />'.
			'<table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">';
			// First, generate the Auto-Convert URL checkbox...
			$html_code .= '<tr valign="top">'.
				'<td width="25%" align="left">Automatic URL Conversion Preference</td>'.
				'<td align="left">'.
				'<select name="eStore_auto_convert_to_relative_url">'.
				'<option value="0"' . ($auto_convert == '0' ? ' selected="selected"' : '') . '>(Default) Absolute</option>'.
				'<option value="1"' . ($auto_convert == '1' ? ' selected="selected"' : '') . '>Relative</option>'.
				'<option value="2"' . ($auto_convert == '2' ? ' selected="selected"' : '') . '>Do Not Convert</option>';
			$html_code .= '</select><br /><i>By default, eStore tries to convert product download file URL into absolute file paths.  If you receive file_exists() errors in the download manager debug log file, you can try the "Relative" or "Do Not Convert" preferences.  This can be helpful on some servers, on which URL conversion to absolute file paths for product download files is not possible.  WARNING: The "Relative" option will not work, if the file being downloaded is stored outside (above) the WordPress root directory level, and the "Do Not Convert" option may fail if PHP Safe Mode is enabled, if URL aware fopen() are disabled, or if the cURL library is not installed on your server.</i></td></tr>';       
			// Then, generate the Download Method selection menu...
			$html_code .= '<tr valign="top"><td width="25%" align="left">Download Method</td><td align="left">'.
				'<select name="eStore_download_method">'.
				'<option value="1"' . ($dl_method == '1' ? ' selected="selected"' : '') . '>(Default) Method 1, Fopen-8K</option>'.
				'<option value="2"' . ($dl_method == '2' ? ' selected="selected"' : '') . '>Method 2, Fopen-1M (New Default Alpha)</option>'.
				'<option value="3"' . ($dl_method == '3' ? ' selected="selected"' : '') . '>Method 3, Fpassthru (Depreciated)</option>'.
				'<option value="4"' . ($dl_method == '4' ? ' selected="selected"' : '') . '>Method 4, Readfile-1M-SessionWriteClose</option>'.
				'<option value="5"' . ($dl_method == '5' ? ' selected="selected"' : '') . '>Method 5, Fopen-8K-SessionWriteClose</option>'.
				'<option value="6"' . ($dl_method == '6' ? ' selected="selected"' : '') . '>Method 6, Fopen-1M-Closed-NoZip</option>';
				if($can_curl) $html_code .= '<option value="7"' . ($dl_method == '7' ? ' selected="selected"' : '') . '>Method 7, cURL</option>';
				$html_code .= '<option value="8"' . ($dl_method == '8' ? ' selected="selected"' : '') . '>Method 8, Mod X-Sendfile</option>';
				$html_code .= '</select><br /><i>If the default download method does not work on your server, please try one of the other available methods.  WARNING: Method 7, cURL requires the "Do Not Convert" Automatic URL Conversion Preference, and that ALL product download files be specified as fully qualified URL (not absolute or relative file names).  If the cURL library is not installed on your server, Method 7 will not appear in the drop down menu.</i></td></tr></table>';
		return $html_code;
	}

	function settings_menu_post($eStore_auto_convert_to_relative_url, $eStore_download_method) {
	// Save download manager related settings.
		update_option('eStore_auto_convert_to_relative_url', $eStore_auto_convert_to_relative_url);
		update_option('eStore_download_method', (string)$eStore_download_method);
	}
}
?>
