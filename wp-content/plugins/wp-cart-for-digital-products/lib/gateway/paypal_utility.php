<?php
function eStore_paypal_validate_pdt_with_curl()
{
	global $tx_result_error_msg;
	
	$eStore_process_pdt = true;	
	eStore_payment_debug ("eStore PayPal PDT received. Processing the request using CURL method...",true);
	if(defined('WP_AFFILIATE_PLATFORM_VERSION')){
		eStore_payment_debug ("eStore PDT - Affiliate platform is installed.",true);
		$affiliate_auth_token = get_option('wp_aff_pdt_identity_token');	
		if (get_option('wp_aff_enable_3rd_party') != '' && !empty($affiliate_auth_token)){
			$eStore_process_pdt = false;
			eStore_payment_debug ("PDT - Error! you have enabled 3rd party cart integration settings in your affiliate plugin. You do not need to enable that when using the affiliate plugin with eStore.",false);
		}
	}

	if($eStore_process_pdt){
		$req = 'cmd=_notify-synch';
		$tx_token = strip_tags($_GET['tx']);	
		$auth_token = get_option('eStore_paypal_pdt_token');
		if(empty($auth_token)){
			$tx_result_error_msg .= "<br />The PDT identity token is empty. If you want to display the transaction result on the thank you page then you must specify a PDT identity token in the payment gateway settings!";
			eStore_payment_debug ("The PDT identity token is empty. If you want to display the transaction result on the thank you page then you must specify a PDT identity token in the payment gateway settings!",false);
			return;
		}	

		//TODO - keep option to use the non CURL method of PDT verification too
		$sandbox_enabled = get_option('eStore_cart_enable_sandbox');
		if($sandbox_enabled){
			$pp_hostname = "www.sandbox.paypal.com";
		}else{
			$pp_hostname = "www.paypal.com";
		}
	
		$req .= "&tx=$tx_token&at=$auth_token";
		if(!function_exists('curl_init')){
			$tx_result_error_msg .= "<br />PDT Error - CURL PHP library is missing on this server! Thank you page display will not work.";
			eStore_payment_debug("PDT Error - CURL PHP library is missing on this server! Thank you page display will not work.",false);				
			return;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://$pp_hostname/cgi-bin/webscr");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $pp_hostname"));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//set cacert.pem verisign certificate path in curl using 'CURLOPT_CAINFO' field here,
		//if your server does not bundled with default verisign certificates.
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		$res = curl_exec($ch);
		curl_close($ch);
		
		if(!$res){
			$tx_result_error_msg .= "<br />HTTP ERROR... could not establish a connection to PayPal for verification!";
			eStore_payment_debug("HTTP ERROR... could not establish a connection to PayPal for verification!",false);
		}else{
		     // parse the data
		    $lines = explode("\n", $res);
		    $keyarray = array();
		    if (strcmp ($lines[0], "SUCCESS") == 0) {
		        for ($i=1; $i<count($lines);$i++){
		        	$key_val_args_array = explode("=", $lines[$i]);
		        	if(count($key_val_args_array) > 1){
		        		list($key,$val) = $key_val_args_array;
		        		$keyarray[urldecode($key)] = urldecode($val);
		        	}
		    	}
		    	//Verification is success
		    }
		    else if (strcmp ($lines[0], "FAIL") == 0) {
				$tx_result_error_msg .= "<br />PDT verification failed! Could not verify the authenticity of the payment with PayPal!";
				eStore_payment_debug("PDT verification failed! Could not verify the authenticity of the payment with PayPal!",false);
				$eStore_process_pdt = false;
		    }
		}
		
		if($eStore_process_pdt){//Verify and process
			eStore_process_PDT_payment_data($keyarray);
		}
	}	
}

function eStore_paypal_validate_pdt_no_curl()
{
	global $tx_result_error_msg;
	
	$eStore_process_pdt = true;	
	eStore_payment_debug ("eStore PayPal PDT received. Processing the request using NO CURL method...",true);
	if(defined('WP_AFFILIATE_PLATFORM_VERSION')){
		eStore_payment_debug ("PDT - Affiliate platform is installed.",true);
		$affiliate_auth_token = get_option('wp_aff_pdt_identity_token');	
		if (get_option('wp_aff_enable_3rd_party') != '' && !empty($affiliate_auth_token)){
			$eStore_process_pdt = false;
			eStore_payment_debug ("PDT - Error! you have enabled 3rd party cart integration settings in your affiliate plugin. You do not need to enable that when using the affiliate plugin with eStore.",false);
		}
	}
	
	if($eStore_process_pdt){
		$req = 'cmd=_notify-synch';
		$tx_token = strip_tags($_GET['tx']);	
		$auth_token = get_option('eStore_paypal_pdt_token');	
		if(empty($auth_token))
		{
			$tx_result_error_msg .= "<br />The PDT identity token is empty. If you want to display the transaction result on the thank you page then you must specify a PDT identity token in the payment gateway settings!";
			eStore_payment_debug ("The PDT identity token is empty. If you want to display the transaction result on the thank you page then you must specify a PDT identity token in the payment gateway settings!",false);
			return;
		}
		$req .= "&tx=$tx_token&at=$auth_token";
		
		$sandbox_enabled = get_option('eStore_cart_enable_sandbox');
		if($sandbox_enabled)
		{
			$host_url = 'www.sandbox.paypal.com';
			$uri = 'ssl://'.$host_url;
			$port = '443';         	
			$fp = fsockopen($uri,$port,$err_num,$err_str,30);
		}
		else
		{
			$host_url = 'www.paypal.com';
			$fp = fsockopen ($host_url, 80, $errno, $errstr, 30);
		}
		//$fp = fsockopen ($host_url, 80, $errno, $errstr, 30);
		// If possible, securely post back to paypal using HTTPS
		// Your PHP server will need to be SSL enabled
		// $fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
		
		if (!$fp) 
		{
			$tx_result_error_msg .= "<br />HTTP ERROR... could not establish a connection to PayPal for verification!";
			eStore_payment_debug("HTTP ERROR... could not establish a connection to PayPal for verification!",false);
		} 
		else 
		{
			// post back to PayPal system to validate
			$header = "";
			$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
			$header .= "Host: ".$host_url."\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n";
			$header .= "Connection: close\r\n\r\n";
			fputs ($fp, $header . $req);
			// read the body data
			$eStore_res = '';
			$headerdone = false;
			while (!feof($fp)) 
			{
				$line = fgets ($fp, 1024);
				if (strcmp($line, "\r\n") == 0) 
				{
					// read the header
					$headerdone = true;
				}
				else if ($headerdone)
				{
					// header has been read. now read the contents
					$eStore_res .= $line;
				}
			}		
			// parse the data
			$eStore_lines = explode("\n", $eStore_res);
			$eStore_keyarray = array();
			if (strpos($eStore_res, "VERIFIED") !== false){
				for ($i=1; $i<count($eStore_lines);$i++)
				{
					$pdt_key_val_pieces = explode("=", $eStore_lines[$i]);
					if(!isset($pdt_key_val_pieces[0])){continue;}//don't even process it if key is not set
					if(!isset($pdt_key_val_pieces[1])){$pdt_key_val_pieces[1]='';}//set empty value
					list($key,$val) = $pdt_key_val_pieces;
					//list($key,$val) = explode("=", $eStore_lines[$i]);					
					$eStore_keyarray[urldecode($key)] = urldecode($val);
				}
			}
			else
			{
				$tx_result_error_msg .= "<br />PDT verification failed! Could not verify the authenticity of the payment with PayPal!";
				eStore_payment_debug("PDT verification failed! Could not verify the authenticity of the payment with PayPal!",false);
				$eStore_process_pdt = false;
			}	
		}	
		fclose ($fp);	
		if($eStore_process_pdt){
			eStore_process_PDT_payment_data($eStore_keyarray);
		}
	}
}