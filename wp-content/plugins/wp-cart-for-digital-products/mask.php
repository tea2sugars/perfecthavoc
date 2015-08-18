<?php
include_once('../../../wp-load.php');
//session_start();
//echo "<br />Session Value: ".$_SESSION['eStore_vod_authorized'];
$eStore_use_iframe_for_mask = false;

if ($_SESSION['eStore_vod_authorized'] != 'true')
{
    $redirection_path = get_option('eStore_ppv_verification_failed_url');;
    //$redirection_parameter = 'Location: '.$redirection_path;
    //header($redirection_parameter);
    echo '<meta http-equiv="refresh" content="0;url='.$redirection_path.'" />'; 
    exit;
}
else
{
	$URL = $_SESSION['eStore_vod_url'];
	if($eStore_use_iframe_for_mask)
	{
	    echo '<iframe src="'.$URL.'" width="100%" height="100%">
	    <p>Your browser does not support iframes.</p>
	    </iframe>';	
	}
	else
	{		
		$base = '<base href="'.$URL.'">';
		$host = preg_replace('/^[^\/]+\/\//','',$URL);
		$tarray = explode('/',$host);
		$host = array_shift($tarray);
		$URI = '/' . implode('/',$tarray);
		$content = '';
		$fp = @fsockopen($host,80,$errno,$errstr,30);
		if(!$fp) 
		{ 
			echo "Unable to open socket: $errstr ($errno)\n"; 
			exit; 
		} 
		fwrite($fp,"GET $URI HTTP/1.0\r\n");
		fwrite($fp,"Host: $host\r\n");
		if( isset($_SERVER["HTTP_USER_AGENT"]) ) 
		{ 
			fwrite($fp,'User-Agent: '.$_SERVER["HTTP_USER_AGENT"]."\r\n"); 
		}
		fwrite($fp,"Connection: Close\r\n");
		fwrite($fp,"\r\n");
		while (!feof($fp)) 
		{ 
			$content .= fgets($fp, 128); 
		}
		fclose($fp);
		if( strpos($content,"\r\n") > 0 ) 
		{ 
			$eolchar = "\r\n"; 
		}
		else 
		{ 
			$eolchar = "\n"; 
		}
		$eolpos = strpos($content,"$eolchar$eolchar");
		$content = substr($content,($eolpos + strlen("$eolchar$eolchar")));
		if( preg_match('/<head\s*>/i',$content) ) 
		{ 
			echo( preg_replace('/<head\s*>/i','<head>'.$base,$content,1) ); 
		}
		else 
		{ 
			echo( preg_replace('/<([a-z])([^>]+)>/i',"<\\1\\2>".$base,$content,1) );
		}
	}
}

?>
