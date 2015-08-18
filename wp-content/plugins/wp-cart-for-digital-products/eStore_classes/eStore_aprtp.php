<?php
// Authenticated Page Redirect Transfer Protocol (APRTP) class module, WP eStore edition.
// -- The Assurer, 2011-07-16.
class eStore_aprtp {
	function shortcode_api($attributes, $content='') {
	// WordPress shortcode API.
	// This is the API for the [wp_eStore_APR] shortcode.
	// Usage:
	//	[wp_eStore_APR expiry=<minutes> status=<expiration-status>] the_content [/wp_eStore_APR]
	// Notes:
	//	[wp_eStore_APR status=help] will be replaced with the shortcode's help message.
	// -- The Assurer
		// Parse attributes...
		extract(shortcode_atts(array(	'expiry' => 0, 'status' => 'unexpired'), $attributes));
		if(!is_numeric($expiry)) return eStore_aprtp::man_page();		// $expiry must be a numeric value.
		switch($status) {
			case 'unexpired'	:	// APR is unexpired...
							if(eStore_aprtp::cookie_test($expiry))
								return do_shortcode($content);
							else
								return '';
			case 'expired'		:	// APR is expired...
							if(!eStore_aprtp::cookie_test($expiry))
								return do_shortcode($content);
							else
								return '';
			case 'help'		:	// Display help message.
			default			:	// Invalid $status value...
							return eStore_aprtp::man_page();
		}
	}

	function cookie_set($cookie_name='APRcookie') {
	// Sets the APR cookie.
	// -- The Assurer, 2011-07-17.
		global $eStore_debug_manager;						// Need access to debug manager.
		$random_key = get_option('eStore_random_code');
		$cookie_time = (string)time();
		$cookie_flavor = md5(RC4Crypt::encrypt($random_key, $cookie_name));
		$cookie_dough = rawurlencode(base64_encode(RC4Crypt::encrypt($random_key, $cookie_time)));
		if(setcookie($cookie_flavor, $cookie_dough, 0, '/', COOKIE_DOMAIN))
			$eStore_debug_manager->downloads("\$_COOKIE[$cookie_flavor] => $cookie_dough", ESTORE_LEVEL_SUCCESS);
		else
			$eStore_debug_manager->downloads("\$_COOKIE[$cookie_flavor] => $cookie_dough", ESTORE_LEVEL_FAILURE);
		return $retVal;
	}

	function cookie_test($cookie_timeout=0, $cookie_name='') {
	// Test for valid APR cookie.
	// Returns TRUE if a valid APR cookie, derived from $cookie_name exists.  If $cookie_name is an empty string, then
	// $cookie_name will be assigned the URL of the current browser page.  Returns FALSE if the named APR cookie does not
	// exist.  If $cookie_timeout is greater than zero, then FALSE will be returned if the APR cookie is older than
	// $cookie_timeout minutes.
	// -- The Assurer, 2012-04-30.
		global $eStore_debug_manager;						// Need access to debug manager.
		if($cookie_name == '') $cookie_name = eStore_aprtp::curPageURL();	// Use URL of current browser page.
		$eStore_debug_manager->downloads("Authenticating APR request for: $cookie_name", ESTORE_LEVEL_STATUS);
		$random_key = get_option('eStore_random_code');
		$cookie_flavor = md5(RC4Crypt::encrypt($random_key, $cookie_name));	// Derive the APR cookie name.
		if(!isset($_COOKIE["$cookie_flavor"]))  {
			$eStore_debug_manager->downloads("\$_COOKIE[$cookie_flavor] not found.", ESTORE_LEVEL_STATUS);
			return FALSE;							// No cookie for you!
		}
		if($cookie_timeout > 0) {
		// Test for age of APR cookie, if $cookie_timeout is at least 1 minute...
			$cookie_time = (int)RC4Crypt::decrypt($random_key, base64_decode(rawurldecode($_COOKIE[$cookie_flavor])));
			$cookie_timeout = (int)(($cookie_timeout*60)+$cookie_time);
			if($cookie_timeout <= (int)time()) {
				$eStore_debug_manager->downloads("\$_COOKIE[$cookie_flavor] expired.", ESTORE_LEVEL_STATUS);
				return FALSE;						// APR cookie has expired.
			}
		}
		return TRUE;
	}

	function curPageURL() {
	// Returns the URL of the current browser page.
	// -- The Assurer, 2011-07-17.
		$retVal = 'http';							// Default scheme is HTTP.
		if($_SERVER['HTTPS'] == 'on') {$retVal .= 's';}				// Make HTTPS if necessary.
		$retVal .= '://'.$_SERVER['SERVER_NAME'];				// Add server name.
// This code is only here for hystorical purposes.  Using it will break the shortcode.
//		if($_SERVER['SERVER_PORT'] != '80')					// Test for default HTTP port number.
//			$retVal .= ':'.$_SERVER['SERVER_PORT'];				// Specify port number if needed.
		$retVal .= $_SERVER['REQUEST_URI'];					// Add resource path.
		return $retVal;
	}

	function generate_url_request($uri_in, &$url_out) {
	// If $uri_in is a valid Authenticated Page Redirect (APRTP) URI, $url_out is filled in with the HTTP,
	// or HTTPS, URL request for the page specified in the URI.
	// Returns -1 if $url_out is an authenticated URL request, or FALSE if a parsing or syntax error occurred.
	// -- The Assurer, 2012-04-30.
		global $eStore_debug_manager;						// Need access to debug manager.
		if(!eStore_aprtp::uri_parse($uri_in, $scheme, $apr_resource))		// Parse the URI.
			return FALSE;							// URI parsing error.
		$url_out = "$scheme://$apr_resource";					// Assemble the APR URL.
		$eStore_debug_manager->downloads("Authorizing APR request for: $url_out", ESTORE_LEVEL_STATUS);
		eStore_aprtp::cookie_set($url_out);					// Set page specific APR cookie.
		return -1;
	}

	function is_aprtp_scheme($uri_in) {
	// Returns TRUE if $uri_in is a qualified URI that matches the APRTP URL scheme.
	// To qualify, a URI must have "aprtp://" or "aprtps://" at the begining of the $uri_in string.
	// -- The Assurer, 2011-07-16.
		return (preg_match('/^aprtps?:\/\//i', $uri_in) == 1 ? TRUE : FALSE);
	}

	function man_page() {
		$retVal = '<div><hr /><p style="text-align: center"><b>Authenticated Page Redirect (APR)</b></p><p>APR allows WP eStore to use loadable (WordPress) browser pages (called "APR Targets"), instead of downloadable files, that contain protected content as digital products.&nbsp;&nbsp;This is useful in cases where you want to grant secure, one-off, access to page content that would otherwise require the services of a membership plugin.&nbsp;&nbsp;Before redirecting to an APR target, the WP eStore download manager issues an "APR Cookie" that is valid for the remainder of the current browser session.&nbsp;&nbsp;Each APR cookie is uniquely keyed and encrypted to a specific APR target.&nbsp;&nbsp;Using wp_eStore_APR shortcodes, APR targets can control the display of content; based upon the existence, non-existence, or age of APR cookies.</p><p><b>Usage:</b></p><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[[wp_eStore_APR expiry=<span style="text-decoration: underline">minutes</span> status=<span style="text-decoration: underline">expiration-status</span>] the_content [/wp_eStore_APR]]</p><p><b>Where:</b></p><ul><li><span style="text-decoration: underline">minutes</span> is a number, greater than zero, used to test the age in minutes of the APR cookie specific to the current browser page.&nbsp;&nbsp;If <span style="text-decoration: underline">minutes</span> is less than or equal to zero, then only the existence of a page specific APR cookie will be tested.&nbsp;&nbsp;The default value is: 0</li><li><span style="text-decoration: underline">expiration-status</span> determines the condition under which the_content will be displayed.&nbsp;&nbsp;If set to \'unexpired\' the_content will only be displayed if the page specific APR cookie exists, or if its age is less than the specified number of minutes.&nbsp;&nbsp;If set to \'expired\' the_content will only be displayed if the page specific APR cookie is either missing or is older than the specified number of minutes.&nbsp;&nbsp;The default value is: unexpired</li></ul><p><b>Notes:</b></p><ul><li>The shortcode [[wp_eStore_APR status=help]] will be replaced with this help message.</li><li>APR cookies are created by the WP eStore download manager, when it detects the URI of a digital product that has been specified using the APRTP scheme.&nbsp;&nbsp;Under the APRTP scheme, the URI of a digital product "page" (instead of a file) is specified using the format <span style="text-decoration: underline">scheme</span>://domain/resource where <span style="text-decoration: underline">scheme</span> is either "aprtp" (for http) or "aprtps" (for https).</li><li>APR cookies are only valid on the first page of multi-page posts.&nbsp;&nbsp;This is because the "nextpage" (WordPress) tag modifies the URL of second and subsequent pages, making them unrecognizable by the APR cookie testing code.</li></ul><hr /></div>';
		return $retVal;
	}

	function uri_parse($uri_in, &$scheme, &$apr_resource) {
	// Returns TRUE if $uri_in is a parsable APRTP URI.
	// A parsable URI is of the form described in RFC 3986: Scheme://Resource
	// Supported scheme names are: "aprtp" or "aprtps"
	// Resource identifies a loadable browser page.
	// If $uri_in is parsable, the following variables are filled in:
	// $scheme = Transliterated APRTP URI scheme that is supported: "aprtp" --> "http" or "aprtps" --> "https"
	// $apr_resource = The target browser page.
	// -- The Assurer, 2011-07-16.
		if(!eStore_aprtp::is_aprtp_scheme($uri_in)) return FALSE;			// Qualify the scheme.
		// Parse the scheme and resource names...
		$uri_regex = '/^apr(.+):\/\/(.+)/i';
		if(preg_match_all($uri_regex, $uri_in, $uri_matches) != 1) return FALSE;	// Parse URI elements.
		$scheme = 'ht'.strtolower($uri_matches[1][0]);					// URI scheme.
		$apr_resource = str_replace('%2F', '/', rawurlencode($uri_matches[2][0]));	// Resource name.
		// Undo URL encoding for query strings, in case "Ugly" permalinks are being used...
		$apr_resource = str_replace('%3F', '?', $apr_resource);
		$apr_resource = str_replace('%3D', '=', $apr_resource);
		return TRUE;									// No parsing errors occurred.
	}
}
