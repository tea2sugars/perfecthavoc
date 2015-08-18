<?php

$eStore_debug_enabled = false;
$eStore_debug_log_file_name = WP_ESTORE_PATH.'eStore_post_payment_debug.log';//realpath(dirname(__FILE__)).'/eStore_post_payment_debug.log';
$debug_on = get_option('eStore_cart_enable_debug');
if ($debug_on){
	$eStore_debug_enabled = true;
}
function eStore_payment_debug($message,$success,$end=false)
{
    global $eStore_debug_enabled,$eStore_debug_log_file_name;
    if (!$eStore_debug_enabled) return;
    // Timestamp
    $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :').$message. "\n";
    if ($end) {
    	$text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log
    $fp=fopen($eStore_debug_log_file_name,'a');
    fwrite($fp, $text );
    fclose($fp);
}

function eStore_general_debug($message,$success,$end=false)
{
    global $eStore_debug_enabled;
    if (!$eStore_debug_enabled) return;	
    // Timestamp
    $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :').$message. "\n";
    if ($end) {
    	$text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log
    $debug_log_file_name = WP_ESTORE_PATH.'eStore_debug.log'; //TODO - replace with WP_ESTORE_PATH
    $fp=fopen($debug_log_file_name,'a');
    fwrite($fp, $text );
    fclose($fp);
}

function wp_eStore_write_debug_array($array_to_write,$success,$end=false,$debug_log_file_name='')
{
	global $eStore_debug_enabled;
    if (!$eStore_debug_enabled) return;
    // Timestamp
    $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :'). "\n";
	ob_start(); 
	print_r($array_to_write); 
	$var = ob_get_contents(); 
	ob_end_clean();     
    $text .= $var;
    
    if ($end) 
    {
    	$text .= "\n------------------------------------------------------------------\n\n";
    }

    if(empty($debug_log_file_name)){
        $debug_log_file_name = WP_ESTORE_PATH.'eStore_post_payment_debug.log';
    }else{
        $debug_log_file_name = WP_ESTORE_PATH.$debug_log_file_name;
    }
    // Write to log
    $fp=fopen($debug_log_file_name,'a');
    fwrite($fp, $text );
    fclose($fp);
}

function estore_write_debug_msg_or_array($msg_data,$success,$end=false,$debug_log_file_name='')
{
    if(is_array($msg_data)){
        wp_eStore_write_debug_array($msg_data,$success,$end,$debug_log_file_name);
    }
    else{
        eStore_payment_debug($msg_data,$success,$end);
    }
}
