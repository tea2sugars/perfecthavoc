<?php
function eStore_strip_tags_array($data, $tags = null)
{
	//This can be used on single or multi-dimensional array
    $stripped_data = array();
    foreach ($data as $value)
    {
        if (is_array($value))
        {
            $stripped_data[] = eStore_strip_tags_array($value, $tags);
        }
        else
        {
            $stripped_data[] = strip_tags($value, $tags);
        }
    }
    return $stripped_data;
}

function eStore_strip_tags_recursive( $str, $allowable_tags = NULL )
{
	//use it to recursively strip tags from user input
    if ( is_array( $str ) )
    {
        $str = array_map( 'eStore_strip_tags_recursive', $str, array_fill( 0, count( $str ), $allowable_tags ) );
    }
    else
    {
        $str = strip_tags( $str, $allowable_tags );
    }
    return $str;
} 

function eStore_get_top_level_domain(){
	$full_domain = $_SERVER['SERVER_NAME'];
	$tld = preg_replace("/^(.*\.)?([^.]*\..*)$/", "$2", $_SERVER['HTTP_HOST']);
	return $tld;
}

function eStore_append_http_get_data_to_url($url,$name,$value)
{
    $separator='?';
	if(strpos($url,'?')!==false)
	{
	    $separator='&';
	} 	
	$full_url = $url.$separator.$name.'='.$value;
	return $full_url;
}

function eStore_redirect_to_url($url,$delay='0',$exit='1')
{
	if(empty($url))
	{
		echo "<br /><strong>Error! The URL value is empty. Please specify a correct URL value to redirect to!</strong>";
		exit;
	}
	if (!headers_sent())
	{
		header('Location: ' . $url);
	}
	else
	{
		echo '<meta http-equiv="refresh" content="'.$delay.';url='.$url.'" />';
	}
	if($exit == '1')//exit
	{
		exit;
	}
}

function eStore_is_valid_url_if_not_empty($url)
{
	if(empty($url)){
		return true;
	}else{
		return eStore_is_valid_url($url);
	}
}
function eStore_is_valid_url($url)
{
	if(WP_ESTORE_DO_NOT_CHECK_URL_VALIDITY==='1'){
		return true;
	}
		$orig_url = $url;		
        $url = @parse_url($url);
        if ( ! $url) {
            return false;
        }
        $url = array_map('trim', $url);
        $scheme = $url['scheme'];
        if($scheme == "https"){
        	$url['port'] = 443;
        }
        $url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
        
        $path = (isset($url['path'])) ? $url['path'] : '';
        if ($path == '')
        {
            $path = '/';
        }
        $path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';
        if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) )
        {
            if ( PHP_VERSION >= 5 ) //Primary checking method
            {
		        if(ini_get('allow_url_fopen') != '1'){ 	
		        	//do nothing... it will fall back to the 2nd second checking method
		        }
		        else{		                 
                	$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
		            $headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
		            return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );                	
		        }
            }
            
            if(function_exists('fsockopen')) //Alternate checking method using fsockopen
            {       	
                $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
                if ( ! $fp )
                {
                    return false;
                }
                fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
                $headers = fread ( $fp, 128 );
                fclose ( $fp );
	            $headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
	            return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );                
            }
            
            
            if(function_exists('curl_init'))//Alternate checking method using CURL
            {
		        $ch = curl_init($orig_url);  
		        curl_setopt($ch, CURLOPT_TIMEOUT, 5);  
		        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
		        $data = curl_exec($ch);  
		        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
		        curl_close($ch);  
		        if($httpcode>=200 && $httpcode<300){  
		            return true;  
		        } 
		        else{  
		            return false;  
		        }
            }
            else
            {
            	return true;//Could not validate... just return true anyway.
            }

        }
        return false;
}
function wp_eStore_is_date_valid($date)
{
    	$arr=split("-",$date); // splitting the array
		$yy=($arr[0]); // first element of the array is year
		$mm=($arr[1]); // second element is month
		$dd=($arr[2]); // third element is day
		If(!checkdate($mm,$dd,$yy)){
			return false;
		}	
		else{
			return true;
		}
}
function wp_eStore_replace_url_in_string_with_link($input_string)
{
	$url_pattern = '/# Rev:20100913_0900 github.com\/jmrware\/LinkifyURL
	# Match http & ftp URL that is not already linkified.
	  # Alternative 1: URL delimited by (parentheses).
	  (\()                     # $1  "(" start delimiter.
	  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $2: URL.
	  (\))                     # $3: ")" end delimiter.
	| # Alternative 2: URL delimited by [square brackets].
	  (\[)                     # $4: "[" start delimiter.
	  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $5: URL.
	  (\])                     # $6: "]" end delimiter.
	| # Alternative 3: URL delimited by {curly braces}.
	  (\{)                     # $7: "{" start delimiter.
	  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $8: URL.
	  (\})                     # $9: "}" end delimiter.
	| # Alternative 4: URL delimited by <angle brackets>.
	  (<|&(?:lt|\#60|\#x3c);)  # $10: "<" start delimiter (or HTML entity).
	  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $11: URL.
	  (>|&(?:gt|\#62|\#x3e);)  # $12: ">" end delimiter (or HTML entity).
	| # Alternative 5: URL not delimited by (), [], {} or <>.
	  (                        # $13: Prefix proving URL not already linked.
	    (?: ^                  # Can be a beginning of line or string, or
	    | [^=\s\'"\]]          # a non-"=", non-quote, non-"]", followed by
	    ) \s*[\'"]?            # optional whitespace and optional quote;
	  | [^=\s]\s+              # or... a non-equals sign followed by whitespace.
	  )                        # End $13. Non-prelinkified-proof prefix.
	  ( \b                     # $14: Other non-delimited URL.
	    (?:ht|f)tps?:\/\/      # Required literal http, https, ftp or ftps prefix.
	    [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]+ # All URI chars except "&" (normal*).
	    (?:                    # Either on a "&" or at the end of URI.
	      (?!                  # Allow a "&" char only if not start of an...
	        &(?:gt|\#0*62|\#x0*3e);                  # HTML ">" entity, or
	      | &(?:amp|apos|quot|\#0*3[49]|\#x0*2[27]); # a [&\'"] entity if
	        [.!&\',:?;]?        # followed by optional punctuation then
	        (?:[^a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]|$)  # a non-URI char or EOS.
	      ) &                  # If neg-assertion true, match "&" (special).
	      [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]* # More non-& URI chars (normal*).
	    )*                     # Unroll-the-loop (special normal*)*.
	    [a-z0-9\-_~$()*+=\/#[\]@%]  # Last char can\'t be [.!&\',;:?]
	  )                        # End $14. Other non-delimited URL.
	/imx';
	
	$dl_link_target = 'target="_self"';
	if(WP_ESTORE_OPEN_IN_NEW_WINDOW_THANKU_DL_LINKS == '1'){$dl_link_target = 'target="_blank"';}
	if(WP_ESTORE_USE_ANCHOR_FOR_THANKU_DL_LINKS==='1'){
		$url_replace = '$1$4$7$10$13<a href="$2$5$8$11$14" '.$dl_link_target.'>'.WP_ESTORE_CLICK_HERE_TO_DOWNLOAD.'</a>$3$6$9$12';
	}else{
		$url_replace = '$1$4$7$10$13<a href="$2$5$8$11$14" '.$dl_link_target.'>$2$5$8$11$14</a>$3$6$9$12';
	}
	$output = preg_replace($url_pattern,$url_replace,$input_string);
	return $output;	
}

function wp_eStore_shorten_url($url, $qr=NULL){
	if(function_exists('curl_init')){
		eStore_payment_debug('Shortening the encrypted download URL using Google URL shortener API...',true);
		$apiKey = WP_ESTORE_GOOG_URLSHRT_API_KEY;		 
		$postData = array('longUrl' => $url, 'key' => $apiKey);
		$jsonData = json_encode($postData);
		 
		$curlObj = curl_init();
		curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url');
		curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curlObj, CURLOPT_HEADER, 0);
		curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
		curl_setopt($curlObj, CURLOPT_POST, 1);
		curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
		$response = curl_exec($curlObj);		
		$json = json_decode($response);//Convert the response json string to object
		curl_close($curlObj);
		$short_url = $json->id;
		eStore_payment_debug('Shortened URL value: '.$short_url,true);
		return $short_url;
	}
	else{
		eStore_payment_debug('Error! cURL library is required to be installed on your server to use the shorten URL feature',false);
	}
	return false;
}

function eStore_escape_csv_value($value) {
	$value = str_replace('"', '""', $value); // First off escape all " and make them ""
	if(preg_match('/,/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value)) { // Check if I have any commas or new lines
		return '"'.$value.'"'; // If I have new lines or commas escape them
	} else {
		return $value; // If no new lines or commas just return the value
	}
}

function eStore_br2nl($input) 
{
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
}

function eStore_convert_text_download_links_into_html($constructed_download_link)
{
	$constructed_download_link = nl2br($constructed_download_link);
	$constructed_download_link = wp_eStore_replace_url_in_string_with_link($constructed_download_link);
	return $constructed_download_link;
}

function eStore_is_product_using_custom_button_image($id)
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
	if(!empty($ret_product->button_image_url)){
		return true;	
	}else{
		return false;
	}
}

function eStore_check_if_string_contains_url($string_to_check)
{
	if (preg_match("/http/", $string_to_check)){
		return true;
	}
	return false;
}

function eStore_get_all_string_inside($start, $end, $string) 
{
	$pattern = '/' . preg_quote($start, '/') . '(.*?)'. preg_quote($end, '/').'/i';
	preg_match_all($pattern, $string, $m);
	return $m[1];
}

function eStore_get_string_between($string, $start, $end)
{
	$string = " ". $string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

function eStoreMyInArray($array, $value, $key)
{
    //loop through the array
    foreach ($array as $val) {
      //if $val is an array cal myInArray again with $val as array input
      if(is_array($val)){
        if(eStoreMyInArray($val,$value,$key))
          return true;
      }
      //else check if the given key has $value as value
      else{
        if($array[$key]==$value)
          return true;
      }
    }
    return false;
}

function eStore_remove_duplicate_array_value_based_on_key($src_array,$column_name)
{
	$newArr = array();
	foreach ($src_array as $val) {
	    $newArr[$val[$column_name]] = $val;    
	}
	$array = array_values($newArr);
	return $array;	
}

function eStore_sort_multidimensional_array_based_on_column($array_to_sort,$column_name)
{
	foreach ($array_to_sort as $key => $row) 
	{
	    $items[$key]  = $row[$column_name];
	}
	array_multisort($items, SORT_DESC, $array_to_sort);	
	return $array_to_sort;
}

function eStore_post_data_using_wp_remote_post($postURL,$data)
{
	$response = wp_remote_post( $postURL, array(
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => $data,
		'cookies' => array()
	    )
	);	
	if( is_wp_error( $response ) ) 
	{
		return 'Something went wrong when trying to do remote post!';
		//Uncomment the following for more details on the error
		//echo 'Response:<pre>';
		//print_r( $response );
		//echo '</pre>';   
	} 
	else 
	{
		return 'Success';
	}	
}

function eStore_member_belongs_to_specified_levels($permitted_levels)
{
	$emember_config = Emember_Config::getInstance();	
	$emember_auth = Emember_Auth::getInstance(); 	
	$level = $emember_auth->getUserInfo('membership_level');			
	if (in_array($level, $permitted_levels)){
		return true;
	}                
	else if ($emember_config->getValue('eMember_enable_secondary_membership')){            	
		$sec_levels = $emember_auth->getUserInfo('more_membership_levels');
		if($sec_levels){
                    if (is_string($sec_levels)){
			$sec_levels = explode(',',$sec_levels);
                    }
                    foreach($sec_levels as $sec_level){
                        if(in_array($sec_level, $permitted_levels))
                            return true;
                    }
		}
	}
	return false;
}

function eStore_post_data_using_curl($postURL,$data)
{
	if(!function_exists('curl_init'))
	{
		eStore_payment_debug('CURL library is not installed!',false);
		return "NO CURL";
	}
    // send data to post URL
    $ch = curl_init ($postURL);
    curl_setopt ($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    $returnValue = curl_exec ($ch);	
    curl_close ($ch);
    return $returnValue;
}

function eStore_multi_submit_check($salt_string='') {//Returns true if not a duplicate submission
    if(empty($salt_string)){
		$string = $_REQUEST['eStore_form_time_value'];
    }else{
    	$string = $salt_string;
    }
	if (isset($_SESSION['eStore_multi_submission_check'])) {
		if ($_SESSION['eStore_multi_submission_check'] === md5($string)) {
			return false;
		} else {
			$_SESSION['eStore_multi_submission_check'] = md5($string);
			return true;
        }
	} else {
		$_SESSION['eStore_multi_submission_check'] = md5($string);
		return true;
	}
}

function eStore_get_static_count()
{
    static $count = 0; // "inner" count = 0 only the first run
    return $count++; // Everytime you call this function the value of count will go up by 1 until its a new page load
}

function eStore_is_txn_already_processed($payment_data)
{
	global $wpdb;
	$customer_table_name = WP_ESTORE_CUSTOMER_TABLE_NAME;		
	$txn_id = $payment_data['txn_id'];
	$emailaddress = $payment_data['payer_email'];		
	$resultset = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE txn_id = '$txn_id' and email_address = '$emailaddress'", OBJECT);
	if($resultset){
		return true;
	}else{
		return false;
	}
}

function eStore_affiliate_capability_exists(){
	if (function_exists('wp_aff_platform_install') || function_exists('wp_aff_award_commission')){return true;}
	else {return false;}
}

function eStore_get_empty_product_object(){
	$editingproduct = new stdClass();
	$editingproduct->name = "";
	$editingproduct->price = "";
	$editingproduct->description = "";
	$editingproduct->thumbnail_url = "";
	$editingproduct->target_thumb_url = "";
	$editingproduct->old_price = "";
	$editingproduct->additional_images = "";
	$editingproduct->product_url = "";
	$editingproduct->button_image_url = "";
	$editingproduct->target_button_url = "";
	$editingproduct->show_qty = "";
	$editingproduct->custom_price_option = "";
	$editingproduct->custom_input = "";
	$editingproduct->custom_input_label = "";
	$editingproduct->commission = "";
	$editingproduct->tier2_commission = "";
	$editingproduct->ref_text = "";
	$editingproduct->product_download_url = "";
	$editingproduct->downloadable = "";
	$editingproduct->ppv_content = "";
	$editingproduct->variation1 = "";
	$editingproduct->variation2 = "";
	$editingproduct->variation3 = "";
	$editingproduct->variation4 = "";
	$editingproduct->shipping_cost = "";
	$editingproduct->weight = "";
	$editingproduct->tax = "";
	$editingproduct->available_copies = "";
	$editingproduct->sales_count = "";
	$editingproduct->per_customer_qty_limit = "";
	$editingproduct->use_pdf_stamper = "";
	$editingproduct->author_id = "";
	$editingproduct->rev_share_commission = "";
	$editingproduct->create_license = "";
	$editingproduct->aweber_list = "";
	$editingproduct->item_spec_instruction = "";
	$editingproduct->return_url = "";
	$editingproduct->paypal_email = "";
	$editingproduct->currency_code = "";
	$editingproduct->a1 = "";
	$editingproduct->p1 = "";
	$editingproduct->t1 = "";
	$editingproduct->a3 = "";
	$editingproduct->p3 = "";
	$editingproduct->t3 = "";
	$editingproduct->srt = "";
	$editingproduct->sra = "";
	return $editingproduct;
}

function wp_eStore_send_wp_mail($to, $subject, $message, $headers='', $attachments='') 
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $content_type = "text/plain";
    $eStore_email_content_type = $wp_eStore_config->getValue('eStore_email_content_type');
    switch ($eStore_email_content_type) 
    {
        case "html" :
            $content_type = 'text/html';
            break;
        case "text" :
            $content_type = 'text/plain';
            break;
        default :
            $content_type = 'text/plain';
            break;
    }
    
    //add_filter('wp_mail_from', 'eStore_get_from_address');
    //add_filter('wp_mail_from_name', 'eStore_get_from_name');

    //Massage the email body according to content type
    if($content_type == "text/html"){
        add_filter('wp_mail_content_type', create_function('', 'return "'.$content_type.'"; '));
        $message = nl2br($message);
        $message = wp_eStore_replace_url_in_string_with_link($message);
    }
    
    if(empty($attachments)){
        wp_mail($to, $subject, $message, $headers);
    }else{
        wp_mail($to, $subject, $message, $headers, $attachments);
    }

    //remove_filter( 'wp_mail_from', 'eStore_get_from_address');
    //remove_filter( 'wp_mail_from_name', 'eStore_get_from_name');
    //remove_filter( 'wp_mail_content_type', 'eStore_get_email_content_type');
}

function wp_eStore_get_subscription_summary_string($id,$name='',$a3='')
{
    if(empty($id)){
        eStore_payment_debug('eStore product ID needs to be passed to this function', false);
        return;     
    }
    global $wpdb;
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);

    if(empty($name)){$item_name = $ret_product->name;}
    else{ $item_name = $name;}
    if(empty($a3)){$a3 = $ret_product->a3;}
    $a1 = $ret_product->a1;  // trial amount (example: 5.00)
    $free_trial = false;
    $trial = false;
    if($a1 != "")
    {
        $a1_value = intval($a1);
        if($a1_value==0)
        {
            $free_trial = true;
        }
        else
        {
            $trial = true;
        }
    }
    //a3 is recurring amount (example: 7.00)
    $p3 = $ret_product->p3;  // recurring period (example: 30)
    $t3 = $ret_product->t3;  // recurring period unit (example: D, M, Y)
    $period_unit = "";
    if($t3=="D")
    {
        $period_unit = "day(s)";
    }
    else if($t3=="M")
    {
        $period_unit = "month(s)";
    }
    else if($t3=="Y")
    {
            $t3 = "days";
            $p3 = "365";
            $period_unit = "day(s)";
    }
    $installment = "";
    if($ret_product->srt > 1)
    {
        $srt = $ret_product->srt;
        $installment = ", for ".$srt." installment(s)";
    }

    if(!empty($ret_product->currency_code)){$item_currency = $ret_product->currency_code;}
    else{$item_currency = get_option('cart_payment_currency');}

    // forming the subscription details
    $terms = "";
    if($free_trial)
    {
        $terms .= "Free for the first ".$p3." ".$period_unit." Then ";	
    }
    else if($trial)
    {
        $terms .= number_format($a1,2)." ".$item_currency." for the first ".$p3." ".$period_unit." Then ";
    }         
    $period = '';
    if($p3=='1'){$period = "";}
    else{$period = $p3." ";}

    $terms .=  number_format($a3,2)." ".$item_currency." for each ".$period.$period_unit.$installment;
    return $terms;        
}

function eStore_get_store_action_page_obj()
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $page_id = $wp_eStore_config->getValue('eStore_template_store_action_page_id');
    $sp_obj = get_post($page_id);
    return $sp_obj;
}