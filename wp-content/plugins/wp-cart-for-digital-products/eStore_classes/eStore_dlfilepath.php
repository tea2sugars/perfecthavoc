<?php
class eStore_dlfilepath {
	function absolute_from_url($src_file_url) {
	// Converts $src_file_url into an absolute file path, starting at the server's root directory.
	// Warning: Assumes $src_file_url is at, or below, the server's document root directory.  If the
	// $src_file_url is outside the scope of the server's document root directory, a FALSE value will be returned.
	// FALSE is also returned if $src_file_url is not a qualified URL.
	// -- The Assurer, 2010-10-06.
		global $eStore_debug_manager;
		if (preg_match("/^http/i", $src_file_url) != 1) return FALSE; // Not a qualified URL.
		$domain_url = $_SERVER['SERVER_NAME']; // Get domain name.
		$domain_url_no_www = str_replace("www.","",$domain_url);
		$absolute_path_root = $_SERVER['DOCUMENT_ROOT']; // Get absolute document root path.

		// Calculate position in $src_file_url just after the domain name.
		$domain_name_pos = stripos($src_file_url, $domain_url);
		if($domain_name_pos === FALSE)
		{
			$eStore_debug_manager->downloads("Didn't find a direct match for the domain URL in the src file.", ESTORE_LEVEL_WARNING);
			$file_on_this_domain = stripos($src_file_url, $domain_url_no_www);
			if($file_on_this_domain === FALSE){
				$eStore_debug_manager->downloads("The src file is not stored in this domain. This is an external file link.", ESTORE_LEVEL_WARNING);
				return false;
			}
			//Lets try another method of conversion
			$eStore_debug_manager->downloads("Trying a secondary URL conversion method.", ESTORE_LEVEL_WARNING);
			$path = parse_url($src_file_url, PHP_URL_PATH);
			$abs_path = $absolute_path_root.$path;
			//$abs_path = ABSPATH.$path;//another option
			$abs_path = str_replace('//','/',$abs_path);
			return $abs_path;
		}
		$domain_name_length = strlen($domain_url);
		$total_length = $domain_name_pos+$domain_name_length;
		// Replace http*://SERVER_NAME in $src_file_url with the absolute document root path.
		return substr_replace($src_file_url, $absolute_path_root, 0, $total_length);	
	}

	function dl_file_exists($file_path) {
	// Returns file_exists($file_path) and if necessary, writes appropriate ADVISORY and WARNING messages to the
	// debugger log file.
	// -- The Assurer, 2010-11-20.
		global $eStore_debug_manager;			// Need access to debug manager.
		$retVal = file_exists($file_path);		// Check if $file_path is a valid target.
		if($retVal === TRUE) return TRUE;		// Target $file_path is valid.
		// Target $local_file_path is invalid (does not exist)...
		$eStore_debug_manager->downloads("Invalid URL conversion target = $file_path", ESTORE_LEVEL_WARNING);
		$eStore_debug_manager->downloads('Forcing "Do Not Convert" option.', ESTORE_LEVEL_ADVISORY);
		return FALSE;					// Return failed file_exists() status.
	}

	function dl_filesize($uri, $user='', $pw='') {
	// Returns the size, in bytes, of a file whose path is specified by a URI.  If the URI is a qualified URL and cURL is not
	// installed on the server, a string of "unknown" is returned.  Note: We use "URI" instead of "URL" because this is not
	// necessarily an HTTP request.
	// -- The Assurer, 2010-10-07.
		if(preg_match("/^http/i", $uri) != 1) {
		// Not a qualified URL...
			$retVal = @filesize($uri);			// Get file size.
			if($retVal === FALSE) $retVal = 'unknown';	// Whitewash any stat() errors.
			return $retVal;					// Return local file size.
		}
		if(!function_exists('curl_init')) return 'unknown';	// If cURL not installed, size is "unknown."
		$ch = curl_init($uri);					// Initialize cURL for this URI.
		if($ch === FALSE) return 'unknown';			// Return "unknown" if initialization fails.
		curl_setopt($ch, CURLOPT_HEADER, TRUE);			// Request header in output.
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);			// Exclude body from output).
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);		// Return transfer as string on curl_exec().
		// if auth is needed, do it here
		if(!empty($user) && !empty($pw)) {			// Set optional authentication headers...
			$headers = array('Authorization: Basic '.base64_encode($user.':'.$pw));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$header = curl_exec($ch);				// Retrieve the remote file header.
		if($header === FALSE) return 'unknown';			// Return "unknown" if header could not be retrieved.
		// Parse the remote file header for the content length...
		if(preg_match('/Content-Length:\s([0-9].+?)\s/', $header, $matches) == 1) {
			return $matches[1];				// Return remote file size.
		} else {
			return 'unknown';				// Return "unknown" if no information available.
		}
	}

	function relative_to_eStore_from_url($src_file_url) {
	// Converts $src_file_url into a file path, that is relative to the main eStore plugin directory.
	// Warning: Assumes that $src_file_url is at, or below, the WordPress root directory.  A value of FALSE is returned
	// under the following conditions:
	// 1. $src_file_url is not a qualified URL.
	// 2. $src_file_url is outside the scope of the WordPress root directory.
	// 3. $src_file_url and get_bloginfo('wpurl') are different, because one uses HTTP and the other uses HTTPS.
	//    This could happen if the product is stored in the DB with an HTTPS and the WP root uses HTTP, or vice versa.
	// -- The Assurer, 2010-10-15.
		if (preg_match("/^http/i", $src_file_url) != 1) return FALSE;	// Not a qualified URL.
		$wpurl = get_bloginfo('wpurl');					// iGet WP root URL1 and directory.
		// Calculate position in $src_file_url just after the WP root URL and directory.
		$wp_root_pos = stripos($src_file_url, $wpurl);
		if($wp_root_pos === FALSE) return FALSE;			// Rats!  URL is not under WP root directory.
		$wp_root_length = strlen($wpurl);
		$total_length = $wp_root_pos+$wp_root_length;
		$relative_path_to_wpurl = '../../..';				// Relative path to WP root directory.
		// Replace $wpurl with $relative_path_to_wpurl in $src_file_url and return the result.
		return substr_replace($src_file_url, $relative_path_to_wpurl, 0, $total_length);	
	}

	function url_to_path_converter($src_file_url) {
	// Attempts to convert $src_file_url into either a relative path, or an absolute path, if possible.  If no
	// conversion is possible, returns $src_file_url.  Reasons for why no conversion takes place are that
	// $src_file_url is not a qualified URL, or it is outside the scope of either the WP root directory or the
	// SERVER_NAME document root directory.  Also, trying to pass the path of a file, instead of its URL will
	// also result in a non-conversion.
	// -- The Assurer, 2010-10-07.
		// Attempt to convert into both relative and absolute paths...
		$relative = eStore_dlfilepath::relative_to_eStore_from_url($src_file_url);
		$absolute = eStore_dlfilepath::absolute_from_url($src_file_url);
		switch(get_option('eStore_auto_convert_to_relative_url')) {
		// Return conversion, based on user preference...
			case 0: // Absolute path...
				if(($absolute != FALSE) && eStore_dlfilepath::dl_file_exists($absolute)) return $absolute;
				break;
			case 1:	// Relative path...
				if(($relative != FALSE) && eStore_dlfilepath::dl_file_exists($relative)) return $relative;
				break;
			case 2:	// Do not convert...
				break;
		}
		//If the preferred URL conversions failed, or if "No Conversion" was choosen, then return the original URL.
		return $src_file_url;
	}
}
?>
