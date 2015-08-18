<?php
// Localization opportunities...
define('ESTORE_DLVS_HI1', 'The secure download manager ran into a problem that it couldn&rsquo;t handle, and is unable to process your download request.');
define('ESTORE_DLVS_CP1', 'Please contact the site administrator.');
define('ESTORE_DLVS_TA1', 'Please tell the site administrator the problem was due to the following reason:');
define('ESTORE_DLVS_TU1', 'The problem was due to the following reason:');
define('ESTORE_DLVS_IR1', 'A valid download request (query string) wasn&rsquo;t specified, as part of the link used.&nbsp;&nbsp;Please check the correctness of the download link used, and try again.&nbsp;&nbsp;Sometimes links sent by email get mangled by the email client, or you did not correctly copy the complete link.');
define('ESTORE_DLVS_LCT', 'The download link (see browser address bar) has been used too many times.&nbsp;If you think this reason is in error, please contact the site administrator.');
define('ESTORE_DLVS_LDB', 'The download link (see browser address bar) couldn&rsquo;t be found in the wp_eStore database.');
define('ESTORE_DLVS_LEX', 'The download link (see browser address bar) has expired (it&rsquo;s too old).&nbsp;&nbsp;If you think this reason is in error, please contact the site administrator.');
define('ESTORE_DLVS_PID', 'The product ID contained in the download link (see browser address bar) doesn&rsquo;t seem to be in the wp_eStore database.  You might have a download link for a product that is no longer stocked.&nbsp;&nbsp;If you think this reason is in error, please contact the site administrator.');
define('ESTORE_DLVS_APM', 'The digital product associated with the download link (see browser address bar) contains a malformed Authenticated Page Redirect (APR) URI.');
define('ESTORE_DLVS_APC', 'There is a compatability mismatch between the download script and the APRTP class library.&nbsp;&nbsp;The administrator might&rsquo;ve performed an incomplete upgrade of the wp_eStore plugin.');
define('ESTORE_DLVS_S3M', 'The digital product associated with the download link (see browser address bar) contains a malformed Amazon S3 URI.&nbsp;&nbsp;The administrator should specifically ensure that bucket names are in compliance with Amazon&rsquo;s bucket naming restrictions.i&nbsp;&nbsp;The most common mistake would be if the administrator used any upper case letters or space characters in the bucket name.');
define('ESTORE_DLVS_S3C', 'There is a compatability mismatch between the download script and the AS3TP class library.&nbsp;&nbsp;The administrator might&rsquo;ve performed an incomplete upgrade of the wp_eStore plugin.');
define('ESTORE_DLVS_FNF', 'The download script couldn&rsquo;t locate the file associated with the digital product.  If the administrator thinks this reason is in error, maybe a different URL conversion option might fix the problem.');
define('ESTORE_DLVS_OPN', 'The file (on the server) containing your download couldn&rsquo;t be opened.');
define('ESTORE_DLVS_FQN', 'The cURL library can only process fully qualified URL.&nbsp;&nbsp;Ensure that the URL conversion option is set for "Do Not Convert," and that fully qualified URL are used in the product database.');
define('ESTORE_DLVS_CNI', 'The cURL library isn&rsquo;t installed on this server.');
define('ESTORE_DLVS_CSI', 'A cURL session couldn&rsquo;t be initialized.');
define('ESTORE_DLVS_CPT', 'Unable to set cURL transfer options.');

/********** Please do not change anything below this line **********/

if (!defined('ABSPATH')){include_once ('../../../wp-load.php');}
require_once('eStore_classes/_loader.php');// Activate the class loader.
include_once('eStore_classes.php');
include_once('eStore_db_access.php');
include_once('lib/mimetype.php');

// Ensure that a valid download request query string was specified...
if(!isset($_GET['file']) || empty($_GET['file'])) {// Required "file=" URL query string is missing!
	eStore_dlvs::error(ESTORE_DLVS_IR1, FALSE);
	exit;
}

if (!is_object($eStore_debug_manager)){//Initialize debug mgr if it is not loaded yet
	$eStore_debug_manager = new eStore_dbgmgr(WP_ESTORE_PATH);
}

$time = time();// Time download script was invoked.
global $wpdb;
$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
$product_meta_table_name = WP_ESTORE_PRODUCTS_META_TABLE_NAME;
$data = $_GET['file'];
$file_key = $data;
$current_access_count = -1;
$random_key = get_option('eStore_random_code');
$download_url_life = get_option('eStore_download_url_life');
$download_url_limit_count = get_option('eStore_download_url_limit_count');
$id_time = RC4Crypt::decrypt($random_key,base64_decode(rawurldecode($data)));
$product_id="";$timestamp="";$url="";
$encrypted_args_array = explode('|',$id_time);
if(count($encrypted_args_array)>2){
	list($product_id,$timestamp,$url) = $encrypted_args_array;
}else{
	list($product_id,$timestamp) = $encrypted_args_array;
}

$theid = strip_tags($product_id);
$retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$theid'", OBJECT);
if($retrieved_product->id != $product_id) {
	eStore_dlvs::error(ESTORE_DLVS_PID, FALSE);
	exit;
}
$dl_query_cond = " download_key = '$file_key'";
$dl_query_result = WP_eStore_Db_Access::find(WP_ESTORE_DOWNLOAD_LINKS_TABLE_NAME, $dl_query_cond);
if($dl_query_result) {
	$eStore_debug_manager->downloads("Link details retrieved using file key. Link ID: ".$dl_query_result->id, ESTORE_LEVEL_STATUS);
	$current_access_count = $dl_query_result->access_count;
}
else {
	eStore_dlvs::error(ESTORE_DLVS_LDB);
	exit;
}

/*** Link validation check ***/
$psdc_checked = false;
$pset_checked = false;

// Check product specific link expiry settings (download count)
$download_limit_count = "";
$product_meta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$theid' AND meta_key='download_limit_count'", OBJECT);
if($product_meta){
    $download_limit_count = $product_meta->meta_value;
    if(!empty($download_limit_count)){
        $psdc_checked = true;
	$eStore_debug_manager->downloads("Download limit counting is enabled for this product. Product ID: ".$theid.". Count limit: ".$download_limit_count.". Current access count: ".$current_access_count, ESTORE_LEVEL_STATUS);
	if($current_access_count >= $download_limit_count) {
            //This product has been downloaded too many times using this encrypted link.
            eStore_dlvs::error(ESTORE_DLVS_LCT, FALSE);
            exit;
	}
    }
}
// Check product specific link expiry settings (download time limitation)
$download_limit_time = "";
$product_meta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$theid' AND meta_key='download_limit_time'", OBJECT);
if($product_meta){
    $download_limit_time = $product_meta->meta_value;
    if(!empty($download_limit_time)){
        $pset_checked = true;
        if ($timestamp < $time - ($download_limit_time * 60 * 60 )) {
            $eStore_debug_manager->downloads("Download limit time is enabled for this product. Product ID: ".$theid.". This link has expired.", ESTORE_LEVEL_STATUS);
            eStore_dlvs::error(ESTORE_DLVS_LEX, FALSE);
            exit;
        }
    }
}

//Check global link expiry settings (download count)
if(!$psdc_checked){
    if($download_url_limit_count && $download_url_limit_count!= "999") {// Download limit counting is enabled
            $eStore_debug_manager->downloads("Global download limit counting is enabled. Count limit: ".$download_url_limit_count." Access count: ".$current_access_count, ESTORE_LEVEL_STATUS);
            if($current_access_count >= $download_url_limit_count) {
                    // Download script invoked too many times for this file key...
                    eStore_dlvs::error(ESTORE_DLVS_LCT, FALSE);
                    exit;
            }
    }
}
//Check global link expiry settings (time limit)
if(!$pset_checked){
    $eStore_debug_manager->downloads("Checking global download expiry time.", ESTORE_LEVEL_STATUS);
    if ($timestamp < $time - ($download_url_life * 60 * 60 )) {
            eStore_dlvs::error(ESTORE_DLVS_LEX, FALSE);
            exit;
    }
}
/*** End link validation check ***/

// If the encrypted link did not contain a file URL, use the product download URL instead.
$file_path = (!empty($url) ? $url : $retrieved_product->product_download_url);

//Update the access count if this feature is enabled
if($current_access_count!= -1)
{   	
  	$cond = " download_key = '$file_key'";
   	$fields = array();
   	$fields['access_count'] = $current_access_count+1;
        
        if (function_exists('wp_eMember_install')){
            $emember_auth = Emember_Auth::getInstance();
            $user_id = $emember_auth->getUserInfo('member_id');
            if (!empty($user_id)){//User is logged in so track the user ID against this download  
                $fields['user_id'] = $user_id;
            }
        }

	$result = WP_eStore_Db_Access::update(WP_ESTORE_DOWNLOAD_LINKS_TABLE_NAME, $cond, $fields);
}

$clientip = $_SERVER['REMOTE_ADDR']; 
$eStore_debug_manager->downloads("Handing download request from IP Address: ".$clientip, ESTORE_LEVEL_STATUS);

if(eStore_aprtp::is_aprtp_scheme($file_path)) {
// Authenticated Page Redirect (APR) URL request...
	$eStore_debug_manager->downloads("APRTP URI request = $file_path", ESTORE_LEVEL_STATUS);
	$retVal = eStore_aprtp::generate_url_request($file_path, $apr_file_path);
	switch($retVal) {
		case -1:	// Authenticated Page Redirect (APR) URL request...
				$eStore_debug_manager->downloads("Dispatching APRTP URL = $apr_file_path", ESTORE_LEVEL_STATUS);
				header("Location: $apr_file_path");
				exit;
		case FALSE:	// Malformed URI request...
				$eStore_debug_manager->downloads("Malformed or misconfigured APRTP URI request!", ESTORE_LEVEL_FALURE);
				eStore_dlvs::error(ESTORE_DLVS_APM);
				exit;
		default:	// Unrecognized URI request...
				$eStore_debug_manager->downloads("Unrecognized eStore_aprtp::generate_url_request() return = $retVal", ESTORE_LEVEL_FALURE);
				eStore_dlvs::error(ESTORE_DLVS_APC);
				exit;
	}
}
    
if(eStore_as3tp::is_as3tp_scheme($file_path)) {
// Amazon Web Services (AWS) Simple Storage Service (S3) URL request...
	$eStore_debug_manager->downloads("AS3TP URI request = $file_path", ESTORE_LEVEL_STATUS);
	$retVal = eStore_as3tp::generate_url_request($file_path, $aws_file_path);
	switch($retVal) {
		case 1:		// Unsigned (public) URL request...
		case -1:	// Signed (private) URL request...
				$eStore_debug_manager->downloads("Dispatching AS3TP URL = $aws_file_path", ESTORE_LEVEL_STATUS);
				header("Location: $aws_file_path");
				exit;
		case FALSE:	// Malformed URI request...
				$eStore_debug_manager->downloads("Malformed or misconfigured AS3TP URI request!", ESTORE_LEVEL_FALURE);
				eStore_dlvs::error(ESTORE_DLVS_S3M);
				exit;
		default:	// Unrecognized URI request...
				$eStore_debug_manager->downloads("Unrecognized eStore_as3tp::generate_url_request() return = $retVal", ESTORE_LEVEL_FALURE);
				eStore_dlvs::error(ESTORE_DLVS_S3C);
				exit;
	}
}

if ($retrieved_product->downloadable == 'no') {
// File is not downloadable through the eStore download manager...
	if($retrieved_product->ppv_content == 1) {
	// File is PPV content...
		$_SESSION['eStore_vod_authorized'] = true;
		$_SESSION['eStore_vod_url'] = $file_path;
		header('Location: '.WP_ESTORE_URL.'/mask.php');
	} else {
	// File is downloadable, using the browser, from a non-obfuscated source...
		header('Location: '.$file_path);
	}
} 
else {
	// Offer the file, using the eStore download manager to obfuscate the original file source...

	/* Generate the "Save As" file name. */
	$file_name = basename($file_path);	
	
	/* NextGen Gallery Advanced Integration */
	if(WP_ESTORE_ENABLE_ADVANCED_NGG_FILE_SERVE){		
		//Searh for a backup version of this file.
		$file_to_find = $file_path . "_backup";
		$eStore_debug_manager->downloads("Using advanced NextGen Gallery file serve option. File to look for: ".$file_to_find, ESTORE_LEVEL_STATUS);
		$search_result = eStore_is_valid_url_if_not_empty($file_to_find);
		if($search_result){
			$eStore_debug_manager->downloads("Found a backup copy of the file. Preparing to serve this copy of the file ...", ESTORE_LEVEL_STATUS);
			$file_path = $file_to_find;
			$file_name = basename($file_path);
		}
		$file_name = eStore_perform_save_as_file_name_massaging($file_name);
		$eStore_debug_manager->downloads("Save as file name: ".$file_name, ESTORE_LEVEL_STATUS);
	}
	if(WP_ESTORE_STAMP_PDF_FILE_AT_DOWNLOAD_TIME === '1'){//Check if file should be stamped at download time		
		$eStore_debug_manager->downloads('PDF File stamping at download time feature is enabled. Checking if file need to be stamped. File Key: '.$file_key, ESTORE_LEVEL_SUCCESS);
		if($retrieved_product->use_pdf_stamper == 1){			
			$eStore_debug_manager->downloads('PDF stamping is enabled for this product. Preparing to stamp the file.', ESTORE_LEVEL_SUCCESS);
			$cond = " download_key = '$file_key'";
			$resultset = WP_eStore_Db_Access::find(WP_ESTORE_DOWNLOAD_LINKS_TABLE_NAME, $cond);
			if($resultset){
				$transaction_id = $resultset->txn_id;
				$eStore_debug_manager->downloads('Retrieved the transaction ID of the corresponding transaction. Txn ID: '.$transaction_id, ESTORE_LEVEL_SUCCESS);
				$customer_table_name = WP_ESTORE_CUSTOMER_TABLE_NAME;
				$customer_data_rs = $wpdb->get_row("SELECT * FROM $customer_table_name WHERE txn_id = '$transaction_id'", OBJECT);
				if($customer_data_rs){
					$eStore_debug_manager->downloads('Retrieved the customer data for this transaction. Customer email '.$customer_data->email_address, ESTORE_LEVEL_SUCCESS);
					$payment_data = eSore_create_payment_data_from_customer_resultset($customer_data_rs);
					$src_file = $file_path;//$retrieved_product->product_download_url;
					$stamped_file_url = eStore_stamp_pdf_file($payment_data,$src_file);
					if($stamped_file_url === 'Error!'){
						$stamping_error = 'PDF Stamping did not finish correctly! Perform a manual stamping from PDF stamper admin menu and make sure the PDF stamper is working on your server.';
						$eStore_debug_manager->downloads($stamping_error,ESTORE_LEVEL_FAILURE);
						eStore_dlvs::error($stamping_error);
						exit;
					}
					$file_path = $stamped_file_url;
					$eStore_debug_manager->downloads('File stamped successfully. Stamped file URL: '.$file_path, ESTORE_LEVEL_SUCCESS);
				}else{
					$stamping_error = "Failed to retrieve customer data for the given transaction ID! Make sure you made a live transaction before exercising the download option with PDF stamping. PDF file stamping does not work unless real customer details is present from the gateway transaction.";
					$eStore_debug_manager->downloads($stamping_error,ESTORE_LEVEL_FAILURE);
					eStore_dlvs::error($stamping_error);
					exit;
				}
			}else{
				$eStore_debug_manager->downloads("Failed to retrieve download link details for the given file key!",ESTORE_LEVEL_FAILURE);
			}
		}
	}
		
	// Attempt to convert $file_path from a URL into a relative or absolute file path.
	$eStore_debug_manager->downloads("Unresolved DL file path = $file_path", ESTORE_LEVEL_STATUS);
	$file_path = eStore_dlfilepath::url_to_path_converter($file_path);
	$eStore_debug_manager->downloads("Resolved DL file path = $file_path", ESTORE_LEVEL_STATUS);
	// Issue a debugger warning, if PHP Safe Mode is on...
	if(ini_get('safe_mode') == '1') $eStore_debug_manager->downloads('PHP safe mode ON.', ESTORE_LEVEL_WARNING);
	if(preg_match("/^http/i", $file_path) == 1) {
	// Sigh, we are still "stuck" with a URL...
		$eStore_debug_manager->downloads('No DL file path conversion performed on URL.', ESTORE_LEVEL_ADVISORY);
		if(ini_get('allow_url_fopen') != '1') {
		// Grrr, URL aware fopen are disabled...
			$eStore_debug_manager->downloads('URL aware fropen() disabled, forcing cURL...', ESTORE_LEVEL_ADVISORY);
			$retVal = download_dispatch($file_path, $file_name, 7);	// Force use of cURL method.
		} else
		// Use configured download method on a URL...
			$retVal = download_dispatch($file_path, $file_name);
	} else {
	// Use configured download method on an absolute or relative file path...
		if(file_exists($file_path))
			$retVal = download_dispatch($file_path, $file_name);	// Download local file.
		else {
		// Local file does not exist...
			eStore_dlvs::error(ESTORE_DLVS_FNF);
			$retVal = "Error on file_exists('$file_path')";
		}
	}
	// Log the results...
	if($retVal === TRUE)
		$eStore_debug_manager->downloads('DL completed with no server-side errors detected.', ESTORE_LEVEL_SUCCESS);
	else {
		if($retVal === FALSE) $retVal = "DL failure: $file_path";
		$eStore_debug_manager->downloads($retVal, ESTORE_LEVEL_FAILURE, FALSE, TRUE);
	}
}
exit;

function download_dispatch($file_path, $file_name, $force_method=FALSE) {
// Dispatch the configured download method...
// Optionally "force" selection of a particular download method.
// Returns TRUE if no server-side errors detected, FALSE or an error string, if errors were detected.
// -- The Assurer, 2010-10-08.
	global $eStore_debug_manager;					// Need access to debug manager.
	// Attempt to disable the PHP script maximum execution timer...
	$old_max_time = ini_get('max_execution_time');			// Get current maximum execution time.
	@set_time_limit(0);						// Attempt to disable the timer.
	$new_max_time = ini_get('max_execution_time');			// Now get current maximum execution time.
	if($new_max_time > 0) $eStore_debug_manager->downloads("Unable to affect maximum PHP script execution time.  Download method will terminate after $new_max_time seconds have elasped.  Downloads not completed within that time period will be invalid.", ESTORE_LEVEL_ADVISORY);
	// Determine which download method to use...
	$eStore_dl_method = ($force_method === FALSE ? get_option('eStore_download_method') : $force_method);
	$eStore_debug_manager->downloads("Dispatching DL method = $eStore_dl_method", ESTORE_LEVEL_STATUS);
	switch($eStore_dl_method) {
		case 1:		// (Default) Method 1, Fopen-8K.
				return download_using_fopen($file_path, $file_name);
		case 2:		// Method 2, Fopen-1M (New Default Alpha).
				return download_using_fopen($file_path, $file_name, 1024);
		case 3:		// Method 3, Fpassthru (Depreciated).
				return custom_read2($file_path, $file_name);
		case 4:		// Method 4, Readfile-1M-SessionWriteClose.
				return download_using_fopen($file_path, $file_name, 1024, TRUE);
		case 5:		// Method 5, Fopen-8K-SessionWriteClose.
				return download_using_fopen($file_path, $file_name, 8, TRUE);
		case 6:		// Method 6, Fopen-1M-Closed-NoZip.
				return custom_read4($file_path, $file_name);
		case 7:		// Method 7, cURL.
       			return download_using_curl($file_path, $file_name);
		case 8:		// Method 8, Mod X-Sendfile (only if your server have this library installed)
       			return eStore_download_using_xsend_file($file_path, $file_name);       			
		default:	return 'Configuration Error, $eStore_dl_method = '.$eStore_dl_method;
	}
}

function download_using_fopen($file_path, $file_name, $chunk_blocks=8, $session_close=FALSE) {	
// Download methods #1, #2, #4 and #5.
// -- The Assurer, 2010-10-22.
	$chunk_size = 1024*$chunk_blocks;		// Number of bytes per chunk.
	$fp = @fopen($file_path,"rb");			// Open source file.
	if ($fp === FALSE) {
	// File could not be opened...
		eStore_dlvs::error(ESTORE_DLVS_OPN);
		return "Error on fopen('$file_path')";	// Catch any fopen() problems.
	}
	if($session_close) @session_write_close();	// Close current session, if requested.
	// Write headers to browser...
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	$mimetype_class_object = new eStore_mimetype();
	$mimetype = $mimetype_class_object->getType($file_path);	
	header("Content-Type: ".$mimetype);
	header("Content-Disposition: attachment; filename=\"$file_name\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".eStore_dlfilepath::dl_filesize($file_path));
	$chunks_transferred = 0;			// Reset chunks transferred counter.
	while(!feof($fp)) {
	// Process source file in $chunk_size byte chunks...
		$chunk = @fread($fp, $chunk_size);	// Read one chunk from the source file.
		if($chunk === FALSE) {
		// A read error occurred...
			@fclose($fp);
			return 'Error on fread() after '.number_format($chunks_transferred).' chunks transferred.';
		}
		// Chunk was successfully read...
		print($chunk);				// Send the chunk on its way.
		flush();				// Flush the PHP output buffers.
		$chunks_transferred += 1;		// Increment the transferred chunk counter.
		// Check connection status...
		// Note: it is a known problem that, more often than not, connection_status() will always return a 0...  8(
		$constat = connection_status();
		if($constat != 0) {
		// Something happened to the browser connection...
			@fclose($fp);
			switch($constat) {
				case 	1:	return 'Connection aborted by client.';
				case	2:	return 'Connection timeout.';
				default:	return "Unrecognized connection_status() = $constat";
			}
		}
	}
	// Well, we finally made it without detecting any server-side errors!
	@fclose($fp);					// Close the source file.
	return TRUE;					// Success!
}

function download_using_curl($file_path,$file_name) {
// Download method #7, cURL.
// -- The Assurer, 2010-11-09.
	if(preg_match("/^http/i", $file_path) != 1) {
	// Not a fully qualified URL...
		eStore_dlvs::error(ESTORE_DLVS_FQN);
		return 'cURL can only process fully qualified URL.';
	}
	if(!function_exists('curl_init')) {
	// cURL library not detected...
		eStore_dlvs::error(ESTORE_DLVS_CNI);
		return 'Error on function_exists(\'curl_init\')x';
	}
	$ch = curl_init();				// Initialize a new cURL session.
	if ($ch === FALSE) {
	// cURL could not be initialized...
		eStore_dlvs::error(ESTORE_DLVS_CSI);
		return 'Error on curl_init()';		// Catch any curl_init() problems.
	}
	// Set cURL options...
	if((curl_setopt($ch, CURLOPT_URL, $file_path) &&
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE) &&
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE) &&
		curl_setopt($ch, CURLOPT_HEADER, FALSE) &&
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE) &&
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0') &&
		curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'download_using_curl_callback')) === FALSE) {
	// Transfer options could not be set...
		eStore_dlvs::error(ESTORE_DLVS_CPT);
		return 'Error on curl_setopt()';	// Catch any setup problems.
	}
	// Write headers to browser...
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	$mimetype_class_object = new eStore_mimetype();
	$mimetype = $mimetype_class_object->getType($file_path);
	header("Content-Type: ".$mimetype);
	header("Content-Disposition: attachment; filename=\"$file_name\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".eStore_dlfilepath::dl_filesize($file_path));
	// Let the transfer begin...
	$retVal = curl_exec($ch);			// Perform the transfer.
	curl_close($ch);				// Close the cURL session.
	return $retVal;					// Return the completion status.
}

function download_using_curl_callback($ch, $chunk) {
// Callback for cUrl Write Function.
// -- The Assurer, 2010-11-09.
	print($chunk);				// Send the chunk on its way.
	flush();				// Flush the PHP output buffers.
	return strlen($chunk);			// Return chunk size.
}

function custom_read2($file_path, $file_name) {
// Download method #3.
// -- The Assurer, 2010-10-26.
	$retVal = TRUE;						// Default return value.
	$fp = @fopen($file_path, "rb");				// Open source file.
	if ($fp === FALSE) {
	// File could not be opened...
		eStore_dlvs::error(ESTORE_DLVS_OPN);
		$retVal = "Error on fopen('$file_path')";	// Catch any fopen() problems.
	} else {
		// Write headers to browser...
		header("Cache-Control: ");			// leave blank to avoid IE errors.
		header("Pragma: ");				// leave blank to avoid IE errors.
		$mimetype_class_object = new eStore_mimetype();
		$mimetype = $mimetype_class_object->getType($file_path);
		header("Content-Type: ".$mimetype);
		header("Content-Disposition: attachment; filename=\"".$file_name."\"");
		header("Content-length:".(string)(filesize($file_path)));
		sleep(1);					// Take a 1 second break...
		if(@fpassthru($fp) === FALSE) $retVal = 'Error on fpassthru()';
		@fclose($fp);
	}
	return $retVal;
}

function custom_read4($file_path,$file_name)
{
	// Close the session (helpful for long downloads
	@session_write_close();
	// set output compression
    if (function_exists('apache_setenv')) @apache_setenv('no-gzip', 1);
    @ini_set('zlib.output_compression', 0);		
	ob_end_clean();		// End automatic output buffering
	
    	$size = eStore_dlfilepath::dl_filesize($file_path);
			
	header("Pragma: public");
	header("Cache-Control: maxage=1");
	header("Content-type: application/octet-stream"); 
	header("Content-Disposition: attachment; filename=\"".$file_name."\""); 
	header("Content-Description: eStore Download");	

	// Handle resumable downloads
	if (isset($_SERVER['HTTP_RANGE'])) {
		list($units, $reqrange) = explode('=', $_SERVER['HTTP_RANGE'], 2);
		if ($units == 'bytes') {
			// Use first range - http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
			list($range, $extra) = explode(',', $reqrange, 2);
		} else $range = '';
	} else $range = '';
	
	// Determine download chunk to grab
    list($start, $end) = explode('-', $range, 2);
	
    // Set start and end based on range (if set), or set defaults
    // also check for invalid ranges.
    $end = (empty($end)) ? ($size - 1) : min(abs(intval($end)),($size - 1));
    $start = (empty($start) || $end < abs(intval($start))) ? 0 : max(abs(intval($start)),0);

    // Only send partial content header if downloading a piece of the file (IE workaround)
    if ($start > 0 || $end < ($size - 1)) header('HTTP/1.1 206 Partial Content');

    header('Accept-Ranges: bytes');
    header('Content-Range: bytes '.$start.'-'.$end.'/'.$size);
    header('Content-length: '.($end-$start+1)); 
	
	$file = fopen($file_path, 'rb');
	fseek($file, $start);
	$packet = 1024*1024;
	while(!feof($file)) {
		if (connection_status() !== 0) return false;
		$buffer = fread($file,$packet);
		if (!empty($buffer)) echo $buffer;
		ob_flush(); flush();
	}
	fclose($file);
	return true;		
}

function eStore_download_using_xsend_file($file_path,$file_name)
{
	// Write headers to browser and send file using X-sendfile
	header('X-Sendfile: '.$file_path);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$file_name\"");
}

function eStore_perform_save_as_file_name_massaging($file_name)
{
	// Perform any save as file name modification tweak here
	// The following changes the nextgen gallery x.jpg_backup fiels to x.jpg_backup.jpg name
	$extension_to_look_for = "jpg_backup";
	$pos = strpos($file_name, $extension_to_look_for);
	if($pos !== false)
	{
		$file_name = $file_name.".jpg";
	}	
	return $file_name;
}
