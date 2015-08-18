<?php
if ((isset($_REQUEST['name'])) && (strlen(trim($_REQUEST['name'])) > 0)) {
	$name = stripslashes(strip_tags($_REQUEST['name']));
} else {$name = 'No name entered';}
if ((isset($_REQUEST['email'])) && (strlen(trim($_REQUEST['email'])) > 0)) {
	$email = stripslashes(strip_tags($_REQUEST['email']));
} else {$email = 'No email entered';}
if ((isset($_REQUEST['prod_id'])) && (strlen(trim($_REQUEST['prod_id'])) > 0)) {
	$prod_id = stripslashes(strip_tags($_REQUEST['prod_id']));
} else {$prod_id = 'No product id found';}
if ((isset($_REQUEST['ap_id'])) && (strlen(trim($_REQUEST['ap_id'])) > 0)) {
	$ap_id = stripslashes(strip_tags($_REQUEST['ap_id']));
}
if ((isset($_REQUEST['clientip'])) && (strlen(trim($_REQUEST['clientip'])) > 0)) {
	$clientip = stripslashes(strip_tags($_REQUEST['clientip']));
}

if (!defined('ABSPATH')){include_once ('../../../wp-load.php');}
include('eStore_squeeze_form_functions.php');
eStore_process_squeeze_form_submission($name,$email,$prod_id,$ap_id,$clientip);
