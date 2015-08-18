<?php
// Amazon Simple Storage Service Transfer Protocol (AS3TP) class module, WP eStore edition.
// -- The Assurer, 2010-12-01.
class eStore_as3tp {
	function as3key_activate() {
	// Called during plugin activation, to add the AWS S3 Access Key ID, Secret Key and expiry to the WP database.
	// -- The Assurer, 2010-12-02.
		add_option('eStore_as3tp_as3key_data','::');					// Initialize stored keypair.
		return;
	}

	function as3key_fetch(&$aws_acckey, &$aws_seckey, &$expiry) {
	// Fetches the stored AWS S3 Access Key ID, Secret Key and expiry from the WP database.  The fetched information is
	// returned in the $aws_acckey, $aws_seckey and $expiry variables.
	// Returns TRUE if no errors occurred during retrieval, otherwise FALSE.
		$as3key_data = get_option('eStore_as3tp_as3key_data','::');			// Fetch the stored key data.
		$key_regex = '/^(.*):(.*):([0-9]*)$/';
		if(preg_match_all($key_regex, $as3key_data, $key_matches) != 1) return FALSE;	// Parsing error.
		$aws_acckey = trim(rawurldecode($key_matches[1][0]));				// Normalize Access Key ID.
		$aws_seckey = trim(rawurldecode($key_matches[2][0]));				// Normalize Secret Key.
		$expiry = trim(rawurldecode($key_matches[3][0]));				// Normalize expiry.
		if($expiry == '') $expiry = '3600';
		return TRUE;
	}

	function as3key_uninstall() {
	// Can be used, during plugin uninstallation, to remove all AS3TP information from the WP database.
	// Returns TRUE if no errors occurred, otherwise FALSE.
	// -- The Assurer, 2010-12-08.
		return delete_option('eStore_as3tp_as3key_data');				// Delete AS3TP information.
}

	function as3key_update($aws_acckey, $aws_seckey, $expiry='300') {
	// Updates the stored AWS S3 Access Key ID, Secret Key and expiry in the WP database.
	// Returns FALSE if the Access Key ID is not 20 characters in length, if the Secret Access Key is not 40
	// characters in length, or if any DB update errors occur.  Otherwise, a copy of the updated key data is returned.
	// -- The Assurer, 2010-12-02.
		// Normalize key data...
		$aws_acckey = trim($aws_acckey);
		$aws_seckey = trim($aws_seckey);
		$expiry = trim($expiry);
		// Perform error checking...
		if(($aws_acckey != '') && (strlen($aws_acckey) != 20)) return FALSE;	// Access Key ID must be 20 chars.
		if(($aws_seckey != '') && (strlen($aws_seckey) != 40)) return FALSE;	// Secret Key must be 40 chars.
		if(preg_match('/^[0-9]+$/', $expiry) != 1) return FALSE;		// Expiry must be a decimal integer.
		// Assemble the key data, using URL safe versions of the data, update the database, and return...
		$as3key_data = rawurlencode($aws_acckey).':'.rawurlencode($aws_seckey).':'.rawurlencode($expiry);
		update_option('eStore_as3tp_as3key_data',$as3key_data);
		return $as3key_data;
	}

	function generate_url_request($uri_in, &$url_out) {
	// If $uri_in is a valid Amazon Web Services (AWS) Simple Storage Service (S3) URI, $url_out is filled in with the HTTP,
	// or HTTPS, URL request for the S3 object specified in the URI.
	// Returns 1 if $url_out is an unsigned URL request, -1 if $url_out is a presigned URL request, or FALSE if a parsing
	// or syntax error occurred.
	// -- The Assurer, 2010-11-25.
		// Parse the URI...
		if(!eStore_as3tp::uri_parse($uri_in, $scheme, $aws_acckey, $as3_host, $as3_bucket, $as3_resource))
			return FALSE;							// URI parsing error.
		// Determine the URI authority...
		$retVal = eStore_as3tp::uri_authority($aws_acckey, $aws_seckey, $expiry);
		if($retVal === FALSE) return FALSE;					// URI authority syntax error.
		if($retVal == 1) {
		// Generate an unsigned URL request...
			$url_out = "$scheme://$as3_host/$as3_resource";
		} else {
		// Generate a presigned URL request...
			$expiry += time();						// Compute expiry relative to ***NOW***.
			$signature = urlencode(
				base64_encode(
					eStore_as3tp::hash_hmac_sha1(
						utf8_encode("GET\n\n\n$expiry\n/$as3_bucket/$as3_resource"),
						$aws_seckey, TRUE
					)
				)
			);								// Create authorization signature.
			// Create authentiction query string...
			$authentication = "AWSAccessKeyId=$aws_acckey&Expires=$expiry&Signature=$signature";
			// Assemble presigned URL request...
			$url_out = "$scheme://$as3_host/$as3_resource?$authentication";
		}
		return $retVal;
	}

	function hash_hmac_sha1($data, $key, $raw_output=FALSE) {
	// Calls the PHP hash_hmac('sha1', $data, $key, $raw_output) function.  If the hash_hmac() function is not
	// defined, usually because PHP_VERSION is less than  5.1.2, the computational equivalent is used instead.
	// -- The Assurer, 2010-11-24.
		if(function_exists('hash_hmac')) return hash_hmac('sha1', $data, $key, $raw_output);
		// Initialize the input/output boxes...
		$pad_in = str_repeat(chr(0x36), 64);
		$pad_out = str_repeat(chr(0x5C), 64);
		// Format the key...
		$key = str_pad((strlen($key) > 64 ? pack('H40', sha1($key)) : $key), 64, chr(0x00));
		// Fill in the boxes...
		for($i = 0; $i < 63; $i++) {
			$pad_in[$i] = $pad_in[$i]^$key[$i];
			$pad_out[$i] = $pad_out[$i]^$key[$i];
		}
		// Compute hash...
		$retVal = sha1($pad_out.pack('H40', sha1($pad_in.$data)));
		// Return output!
		return ($raw_output ? pack('H40', $retVal) : $retVal);
	}

	function is_as3_bucket_name_valid($as3_bucket) {
	// Returns TRUE if $as3_bucket conforms to ***ALL*** AWS S3 bucket naming restrictions as described in:
	// http://docs.amazonwebservices.com/AmazonS3/latest/index.html?BucketRestrictions.html
	// Non-comforming bucket names are the #1 reason for "The request signature we calculated does not match..." errors.
	// -- The Assurer, 2010-11-25.
		// 1. Only lower case letters, numbers, periods and dashes are permitted.
		// 2. Names must begin with a number or letter.
		// 3. Names must not end with a dash.
		// 4. Names must be between 3 and 63 characters in length.
		if(preg_match('/^[a-z0-9][a-z0-9\.\-]{1,61}[a-z0-9]$/', $as3_bucket) != 1) return FALSE;
		// 5. Names must not resemble IP addresses.
		if(preg_match('/^[0-9]+[\.[0-9]+]*$/', $as3_bucket) == 1) return FALSE;
		// 6. Names must not contain adjacent periods.
		if(preg_match('/\.\./', $as3_bucket) == 1) return FALSE;
		// 7. Names must not contain dashes that are adjacent to periods.
		if(preg_match('/\-\.|\.\-/', $as3_bucket) == 1) return FALSE;
		return TRUE;
	}

	function is_as3tp_scheme($uri_in) {
	// Returns TRUE if $uri_in is a qualified URI that matches the AWS S3 URL scheme.
	// To qualify, a URI must have "as3tp://" or "as3tps://" at the begining of the $uri_in string.
	// -- The Assurer, 2010-11-23.
		return (preg_match('/^as3tps?:\/\//i', $uri_in) == 1 ? TRUE : FALSE);
	}

	function uri_authority(&$aws_acckey, &$aws_seckey, &$expiry) {
	// Performs URI authority syntax error checking and, as necessary, fills in the following variables:
	// $aws_acckey = 20 character AWS access key ID.
	// $aws_seckey = 40 character AWS secret access key.
	// $Expiry = Number of seconds before AWS S3 resource access expires.
	// Returns 1 if the URI authority is "public," or -1 if the URI authority is "private."
	// Returns FALSE if any AWSAccessKeyId:AWSSecretKey:Expiry syntax errors occur.
	// Permitted calling values for AWSAccessKeyId:AWSSecretKey:Expiry are:
	//	AWSAccessKeyId		AWSSecretKey		Expiry		Resulting Return Value(s)
	//	---------------		---------------		----------	------------------------------------------------
	//	"public"		Null			Null		Returns 1.
	//	Null			Null			Null		Returns -1.  Fills in access key, secret key and
	//									expiry values from the WordPress options.
	// All other calling values for AWSAccessKeyId:AWSSecretKey:Expiry will cause a FALSE to be returned.
	// -- The Assurer, 2010-11-27.
		if(($aws_seckey != '') || ($expiry != '')) return FALSE;	// $aws_seckey & $expiry must be null.
		if(strtolower($aws_acckey) == 'public') {
		// Access is public...
			$aws_acckey = 'public';					// Normalize $aws_acckey.
			return 1;
		}
		// Access is private...
		if($aws_acckey != '') return FALSE;				// No other authorities are supported.
		// Get preconfigured AWS Access Key ID, Secret Key and expiry...
		if(!eStore_as3tp::as3key_fetch($aws_acckey, $aws_seckey, $expiry)) return FALSE;
		// Perform error checking...
		if(strlen($aws_acckey) != 20) return FALSE;				// Access Key ID must be 20 chars.
		if(strlen($aws_seckey) != 40) return FALSE;				// Secret Key must be 40 chars.
		if(preg_match('/^[0-9]+$/', $expiry) != 1) return FALSE;		// Expiry must be a decimal integer.
		return -1;
	}

	function uri_parse($uri_in, &$scheme, &$authority, &$as3_host, &$as3_bucket, &$as3_resource) {
	// Returns TRUE if $uri_in is a parsable AWS S3 URI.
	// A parsable URI is of the form described in RFC 3986: Scheme://[Authority@]Path
	// Supported scheme names are: "as3tp" or "as3tps"
	// Supported authorities are: Null (for "private") and "public"
	// Path is: Bucket/Resource path that identifies a unique AWS S3 object.
	// If $uri_in is parsable, the following variables are filled in:
	// $scheme = Transliterated AWS S3 URI scheme that is supported: "as3tp" --> "http" or "as3tps" --> "https"
	// $authority = Optional URI authority.
	// $as3_host = DNS host path to the bucket containing the AWS S3 resource.
	// $as3_bucket = AWS S3 bucket name containing the resource.
	// $as3_resource = The key (folder) & object (file) name contained in the AWS S3 bucket.
	// -- The Assurer, 2010-11-23.
		if(!eStore_as3tp::is_as3tp_scheme($uri_in)) return FALSE;			// Qualify the scheme.
		// Parse the scheme, access key, secret key, expiry, host, bucket and resource names...
		$uri_regex = '/^as3(.+):\/\/(([^@]*)@)?([^\/]+)\/(.+)/i';
		if(preg_match_all($uri_regex, $uri_in, $uri_matches) != 1) return FALSE;	// Parse URI elements.
		$scheme = 'ht'.strtolower($uri_matches[1][0]);					// URI scheme.
		$authority = $uri_matches[3][0];						// Optional URI authority.
		$as3_host = $uri_matches[4][0];							// DNS host path.
		$as3_bucket = preg_replace('/\.s3\.amazonaws\.com$/i','',$as3_host);		// Bucket name.
		$as3_resource = str_replace('%2F', '/', rawurlencode($uri_matches[5][0]));	// Resource name.
		if(!eStore_as3tp::is_as3_bucket_name_valid($as3_bucket)) return FALSE;		// Validate bucket name.
		return TRUE;									// No parsing errors occurred.
	}
}
?>
