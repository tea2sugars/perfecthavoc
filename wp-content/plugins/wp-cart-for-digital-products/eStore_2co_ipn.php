<?php
include_once ('../../../wp-load.php');
include_once ('lib/gateway/TwoCo.php');
include_once ('eStore_process_payment_data.php');

$my2CO = new TwoCo();

// Log the INS results
$debug_on = get_option('eStore_cart_enable_debug');
if ($debug_on)
{
    $my2CO->ipnLog = TRUE;
}

// Specify your 2co login and secret
$tco_secret_word = get_option('eStore_2co_secret_word');
$my2CO->setSecret($tco_secret_word);

// Enable test mode if needed
if(get_option('eStore_cart_enable_sandbox'))
    $my2CO->enableTestMode();

// Check validity and write down it
if ($my2CO->validateIpn()){
    $txn_id = $my2CO->ipnData['invoice_id'];
    if(eStore_txn_prcoessed($txn_id)){
    	eStore_do_thank_you_page_display_tasks_with_txn_id($txn_id);
    }
    else{
    	handle_payment_data($my2CO->ipnData,"2co");
    }
}else{
    handle_payment_data($my2CO->ipnData,"2co");
}

?>