<?php

function show_shipping_details_form_new($gateway="manual")
{

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo ESTORE_COLLECT_DETAILS; ?></title>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="view/eStore_form_css.css" />
</head>
<body>

<div id="canvas">
<div id="paymentform_body">
<div id="paymentform_inside">

<div id="order_summary_body">
<?php 
if(get_option('eStore_manual_co_do_not_collect_shipping_charge')!=''){
	//do not charge shipping
	$_SESSION['eStore_cart_postage_cost'] = 0;
}

$defaultSymbol = get_option('cart_currency_symbol');
//$defaultCurrency = get_option('cart_payment_currency');
echo '<div class="summary_title">'.WP_ESTORE_ORDER_SUMMARY.'</div>';
echo '<table class="order_summary">';
echo '<th>'.WP_ESTORE_DESCRIPTION.'</th><th>'.ESTORE_PRICE.'</th>';

foreach ($_SESSION['eStore_cart'] as $item)
{	
	$item_price = $item['price']*$item['quantity'];
	//$rounded_price = number_format($item_price, 2);
	$truncated_item_name = substr($item['name'], 0, 28);
	echo '<tr><td>';
	echo $truncated_item_name."...";
	echo '<br />'.ESTORE_PRICE.': '.print_digi_cart_payment_currency($item_price,$defaultSymbol);
	echo '<br />'.ESTORE_QUANTITY.': '.$item['quantity'];
	echo '</td><td>'.print_digi_cart_payment_currency($item_price,$defaultSymbol).'</td></tr>';	
}
$raw_total = ($_SESSION['eStore_cart_sub_total'] + $_SESSION['eStore_cart_postage_cost'] + $_SESSION['eStore_cart_total_tax']);
$shipping_total = (float)$_SESSION['eStore_cart_postage_cost'];
$tax_total = $_SESSION['eStore_cart_total_tax'];
//$total = number_format(($_SESSION['eStore_cart_sub_total'] + $_SESSION['eStore_cart_postage_cost'] + $_SESSION['eStore_cart_total_tax']),2);
echo '<tr><td>';
echo ESTORE_SUB_TOTAL.':<br />';
if($shipping_total > 1){echo ESTORE_SHIPPING.':<br />';}
if(!empty($tax_total)){echo WP_ESTORE_TAX.':<br />';}
echo '</td><td>';
echo print_digi_cart_payment_currency($_SESSION['eStore_cart_sub_total'],$defaultSymbol).'<br />';
if($shipping_total > 1){echo print_digi_cart_payment_currency($shipping_total,$defaultSymbol).'<br />';}
if(!empty($tax_total)){echo print_digi_cart_payment_currency($tax_total,$defaultSymbol);}
echo '</td></tr>';

echo '<tr><td>'.ESTORE_TOTAL.': </td><td>'.print_digi_cart_payment_currency($raw_total,$defaultSymbol).'</td></tr>';
$conversion_rate = get_option('eStore_secondary_currency_conversion_rate');
if (!empty($conversion_rate))
{
	$secondary_total = $raw_total*$conversion_rate;
	$secondary_curr_symbol = get_option('eStore_secondary_currency_symbol');
	echo '<tr><td>'.ESTORE_TOTAL.' ('.get_option('eStore_secondary_currency_code').'): </td><td>'.print_digi_cart_payment_currency($secondary_total,$secondary_curr_symbol).'</td></tr>';
}
echo '</table>';
if (get_option('eStore_display_continue_shopping')) 
{		
   	$products_page = get_option('eStore_products_page_url');
	echo '<br /><a href="'.$products_page.'"><strong>'.ESTORE_CONTINUE_SHOPPING.'</strong></a>';
}

if (function_exists('wp_eMember_install'))
{
	global $auth;
	$auth = Emember_Auth::getInstance();
	$user_id = $auth->getUserInfo('member_id');
	if (!empty($user_id))
	{
		//eMember user is logged in... load member's details into the fields.
		if(empty($_POST['email'])){
			$_POST['email'] = $auth->getUserInfo('email');
			$_POST['firstname'] = $auth->getUserInfo('first_name');
			$_POST['lastname'] = $auth->getUserInfo('last_name');
			$_POST['address'] = $auth->getUserInfo('address_street');
			$_POST['city'] = $auth->getUserInfo('address_city');
			$_POST['state'] = $auth->getUserInfo('address_state');
			$_POST['postcode'] = $auth->getUserInfo('address_zipcode');
			$_POST['country'] = $auth->getUserInfo('country');
			$_POST['phone'] = $auth->getUserInfo('phone');
		}
	}
}
?>
</div>

<form id="payment" action="" method="post">

<h3><?php echo ESTORE_FILL_IN_SHIPPING_DETAILS; ?></h3>

<input type="hidden" name="eStore_gateway" id="eStore_gateway" value="<?php echo $gateway; ?>" />
<input type="hidden" name="submit_shipping" id="submit_shipping" value="true" />

	<fieldset>
		<ol>
			<li>
				<label for=firstname><?php echo ESTORE_FIRST_NAME; ?> *</label>
				<input id="firstname" name="firstname" type="text" value="<?php echo isset($_POST['firstname'])?$_POST['firstname']:''; ?>" required autofocus>
			</li>
			<li>
				<label for=lastname><?php echo ESTORE_LAST_NAME; ?> *</label>
				<input id="lastname" name="lastname" type="text" value="<?php echo isset($_POST['lastname'])?$_POST['lastname']:''; ?>" required>
			</li>
			<li>
				<label for=address><?php echo ESTORE_ADDRESS; ?> *</label>
				<textarea id="address" name="address" rows=5 required><?php echo isset($_POST['address'])?$_POST['address']:''; ?></textarea>
			</li>
			<li>
				<label for=city><?php echo ESTORE_CITY; ?> *</label>
				<input id="city" name="city" type="text" value="<?php echo isset($_POST['city'])?$_POST['city']:''; ?>" required>
			</li>
			<li>
				<label for=state><?php echo ESTORE_STATE; ?> *</label>
				<input id="state" name="state" type="text" value="<?php echo isset($_POST['state'])?$_POST['state']:''; ?>" required>
			</li>
			<li>
				<label for=postcode><?php echo ESTORE_POSTCODE; ?> *</label>
				<input id="postcode" name="postcode" type="text" value="<?php echo isset($_POST['postcode'])?$_POST['postcode']:''; ?>" required>
			</li>
			<li>
				<label for=country><?php echo ESTORE_COUNTRY; ?> *</label>
				<input id="country" name="country" type="text" value="<?php echo isset($_POST['country'])?$_POST['country']:''; ?>" required>
			</li>
			<li>
				<label for=phone><?php echo ESTORE_PHONE; ?></label>
				<input id="phone" name="phone" type="text" value="<?php echo isset($_POST['phone'])?$_POST['phone']:''; ?>">
			</li>
			<li>
				<label for=email><?php echo ESTORE_EMAIL; ?> *</label>
				<input id="email" name="email" type="email" value="<?php echo isset($_POST['email'])?$_POST['email']:''; ?>" required>
			</li>
			<?php if ($gateway == "manual"){ ?>
			    <li>
				<label for=additional_comment><?php echo ESTORE_ADDITIONAL_COMMENT; ?></label>
				<textarea id="additional_comment" name="additional_comment" rows=5><?php echo isset($_POST['additional_comment'])?$_POST['additional_comment']:''; ?></textarea>
			    </li>
                        <?php } ?>
		</ol>
	</fieldset>
	<fieldset>
	    <input type="hidden" name="eStore_manaul_gateway" id="eStore_manaul_gateway" value="process" />
		<button type="submit" name="confirm"><?php echo ESTORE_CONFIRM_ORDER; ?></button>
	</fieldset>
</form>

</div></div>
</div>

</body>
</html>

<?php
}
?>