<?php
if (!defined('ABSPATH')){include_once ('../../../wp-load.php');}
include_once ('lib/gateway/Authorize.php');
include_once ('eStore_process_payment_data.php');
status_header(200);

// Create an instance of the authorize.net library
$myAuthorize = new Authorize();

// Log the IPN results
$debug_on = get_option('eStore_cart_enable_debug');
if ($debug_on)
{
    $myAuthorize->ipnLog = TRUE;
}

// Specify your authorize api and transaction id
$authorize_login_id = get_option('eStore_authorize_login');
$authorize_tx_key = get_option('eStore_authorize_tx_key');
$myAuthorize->setUserInfo($authorize_login_id, $authorize_tx_key);

// Enable test mode if needed
if(get_option('eStore_cart_enable_sandbox'))
{
    $myAuthorize->enableTestMode();
}

// Check validity and process
if ($myAuthorize->validateIpn())
{
    handle_payment_data($myAuthorize->ipnData,"authorize");
}
else
{
    $_SESSION['eStore_tx_result'] = $myAuthorize->lastError;
	
    //Ipn validation failed... redirect to the cancel URL
    $return = get_option('cart_cancel_from_paypal_url');
    if(empty($return))
    {
    	$return = get_bloginfo ('wpurl');
    }
    $redirection_parameter = 'Location: '.$return;
    header($redirection_parameter);
    exit;     
}
