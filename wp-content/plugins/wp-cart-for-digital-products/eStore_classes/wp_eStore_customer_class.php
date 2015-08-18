<?php
class WP_eStore_Customer
{
    var $customer_id;
    var $cart_id;
    var $first_name;
    var $last_name;
    var $email;
    var $phone;
    var $address;
    var $city;
    var $state;
    var $zip;
    var $country;

    var $shipping_first_name;
    var $shipping_last_name;
    var $shipping_email;
    var $shipping_phone;
    var $shipping_address;
    var $shipping_city;
    var $shipping_state;
    var $shipping_zip;
    var $shipping_country;

    var $gateway_selected;
    var $txn_id;
    var $transaction_type;
    var $transaction_subject;
    var $is_background_post;  // set this value to "yes" if receiving ipn. This is to avoid Google Analytics e-commerce tracking

    function WP_eStore_Customer($cart_id="")
    {
        $this->cart_id = $cart_id;
        $this->customer_id = uniqid();
        $this->is_background_post = "";		
    }	
    function setCartId($cart_id)
    {
        $this->cart_id = $cart_id;
    }	
    function set_customer_details($fname,$lname,$email,$phone,$address,$city,$state,$zip,$country)
    {
        $this->first_name = $fname;
        $this->last_name = $lname;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->zip = $zip;
        $this->country = $country;		
    }
    function set_customer_shipping_details($shipping_fname,$shipping_lname,$shipping_email,$shipping_phone,$shipping_address,$shipping_city,$shipping_state,$shipping_zip,$shipping_country)
    {
        $this->shipping_first_name = $shipping_fname;
        $this->shipping_last_name = $shipping_lname;
        $this->shipping_email = $shipping_email;
        $this->shipping_phone = $shipping_phone;
        $this->shipping_address = $shipping_address;
        $this->shipping_city = $shipping_city;
        $this->shipping_state = $shipping_state;
        $this->shipping_zip = $shipping_zip;
        $this->shipping_country = $shipping_country;
    }
    function SetCustomerID($id)
    {
        $this->customer_id = $id;
    }
    function SetGatewaySelected($gateway)
    {
            $this->gateway_selected = $gateway;
    }
    function SetTransactionID($txn_id)
    {
            $this->txn_id = $txn_id;
    }	
    function SetTransactionType($txn_type)
    {
            $this->transaction_type = $txn_type;
    }
    function SetTransactionSubject($transaction_subject)
    {
            $this->transaction_subject = $transaction_subject;
    }
    function SetISBackgroundPost($value)
    {
            $this->is_background_post = $value;	
    }
    function GetCustomerID()
    {
            return $this->customer_id;
    }
    function GetCartId()
    {
            return $this->cart_id;
    }	
    function GetFirstName()
    {
            return $this->first_name;
    }
    function GetLastName()
    {
            return $this->last_name;
    }	
    function GetEmail()
    {
            return $this->email;
    }	
    function GetPhone()
    {
            return $this->phone;
    }
    function GetStreetAddress()
    {
            return $this->address;
    }		
    function GetAddress()
    {
            return $this->address .", ".$this->city.", ".$this->state.", ".$this->zip.", ".$this->country;
    }
    function GetCity()
    {
            return $this->city;
    }
    function GetState()
    {
            return $this->state;
    }
    function GetZip()
    {
            return $this->zip;
    }
    function GetCountry()
    {
            return $this->country;
    }
    function GetShippingFirstName()
    {
            return $this->shipping_first_name;
    }
    function GetShippingLastName()
    {
            return $this->shipping_last_name;
    }	
    function GetShippingEmail()
    {
            return $this->shipping_email;
    }	
    function GetShippingPhone()
    {
            return $this->shipping_phone;
    }	
    function GetShippingStreetAddress()
    {
            return $this->shipping_address;
    }	
    function GetShippingAddress()
    {
            return $this->shipping_address .", ".$this->shipping_city.", ".$this->shipping_state.", ".$this->shipping_zip.", ".$this->shipping_country;
    }
    function GetShippingCity()
    {
            return $this->shipping_city;
    }
    function GetShippingState()
    {
            return $this->shipping_state;
    }
    function GetShippingZip()
    {
            return $this->shipping_zip;
    }
    function GetShippingCountry()
    {
            return $this->shipping_country;
    }		
    function GetGatewaySelected()
    {
            return $this->gateway_selected;
    }
    function GetTransactionID()
    {
            return $this->txn_id;
    }	
    function GetTransactionType()
    {
            return $this->transaction_type;
    }
    function GetTransactionSubject()
    {
            return $this->transaction_subject;
    }
    function GetISBackgroundPost()
    {
            return $this->is_background_post;	
    }	
		
    function print_eStore_customer_details()
    {
        $output = "";
        $output .= "<br />Cart ID: ".$this->cart_id;
        $output .= "<br />Customer ID: ".$this->customer_id;
        $output .= "<br />First Name: ".$this->first_name;
        $output .= "<br />Last Name: ".$this->last_name;
        $output .= "<br />Email: ".$this->email;
        $output .= "<br />Phone: ".$this->phone;
        $output .= "<br />Address: ".$this->address;
        $output .= "<br />City: ".$this->city;
        $output .= "<br />State: ".$this->state;
        $output .= "<br />Zip: ".$this->zip;
        $output .= "<br />Country: ".$this->country;
        $output .= "<br />First Name: ".$this->shipping_first_name;
        $output .= "<br />Last Name: ".$this->shipping_last_name;
        $output .= "<br />Email: ".$this->shipping_email;
        $output .= "<br />Phone: ".$this->shipping_phone;
        $output .= "<br />Address: ".$this->shipping_address;
        $output .= "<br />City: ".$this->shipping_city;
        $output .= "<br />State: ".$this->shipping_state;
        $output .= "<br />Zip: ".$this->shipping_zip;
        $output .= "<br />Country: ".$this->shipping_country;
        $output .= "<br />Gateway: ".$this->gateway_selected;
        return $output;
    }
}

function wp_eStore_save_customer_and_cart_details_to_db($eStore_cart, $eStore_customer)
{
	global $wpdb;
	$save_cart_table_name = WP_ESTORE_SAVE_CART_TABLE_NAME;
	
	$cart_id = $eStore_cart->GetCartId();
	$serialized_cart = esc_sql(serialize($eStore_cart));
	$serialized_customer = esc_sql(serialize($eStore_customer)); 
        
	$resultset = $wpdb->get_row("SELECT * FROM $save_cart_table_name WHERE cart_id = '$cart_id'", OBJECT);
	if($resultset){//Update the record	
            $updatedb = "UPDATE $save_cart_table_name SET serialized_eStore_cart = '$serialized_cart', serialized_eStore_customer = '$serialized_customer'  WHERE cart_id = '$cart_id'";
            $results = $wpdb->query($updatedb);			
	}
	else{//Insert new record
            $updatedb = "INSERT INTO $save_cart_table_name (cart_id, serialized_eStore_cart, serialized_estore_customer) VALUES ('$cart_id','$serialized_cart','$serialized_customer')";
            $results = $wpdb->query($updatedb);			
	}
//	$wp_eStore_resultset = $wpdb->get_row("SELECT * FROM $save_cart_table_name WHERE cart_id = '$cart_id'", OBJECT);
//	echo "<br />======Retrieved:=====<br />";
//	print_r($wp_eStore_resultset);	
        
}

function wp_eStore_get_customer_details_from_db($cart_id)
{
	global $wpdb;	
	$save_cart_table_name = WP_ESTORE_SAVE_CART_TABLE_NAME;
	$wp_eStore_resultset = $wpdb->get_row("SELECT * FROM $save_cart_table_name WHERE cart_id = '$cart_id'", OBJECT);
	if($wp_eStore_resultset)
	{
		$eStore_customer = unserialize($wp_eStore_resultset->serialized_estore_customer);
		return $eStore_customer;
	}
	return "-1";//could not retrieve data			
}

function wp_eStore_create_payment_data_array($eStore_customer,$eStore_cart)
{
	$cart_custom_data = $eStore_cart->GetCustomData();
	$cart_customvariables = wp_eStore_get_custom_var($cart_custom_data);	
	$cart_eMember_id = $cart_customvariables['eMember_id'];
	$cart_coupon = $cart_customvariables['coupon'];
			
	$gateway = $eStore_customer->GetGatewaySelected();
	$txn_id = $eStore_customer->GetTransactionID();
	if(empty($txn_id)){
		eStore_payment_debug("Error! Cart Transaction ID is empty: ".$txn_id,false);
	}
	$txn_type = $eStore_customer->GetTransactionType();
	$txn_subject = $eStore_customer->GetTransactionSubject();
	
	$shipping_street_address = $eStore_customer->GetShippingStreetAddress();
	$shipping_city = $eStore_customer->GetShippingCity();
	$shipping_state = $eStore_customer->GetShippingState();
	$shipping_country = $eStore_customer->GetShippingCountry();
	$shipping_address = $eStore_customer->GetShippingAddress();
	$street_address = "";
	$city = "";
	$state = "";
	$country = "";
	$address = "";
	if(empty($shipping_city) && empty($shipping_state) && empty($shipping_country) && empty($shipping_street_address)){
		$street_address = $eStore_customer->GetStreetAddress();
		$city = $eStore_customer->GetCity();
		$state = $eStore_customer->GetState();
		$country = $eStore_customer->GetCountry();
		$address = $eStore_customer->GetAddress();
	}
	else
	{
		$street_address = $shipping_street_address;
		$city = $shipping_city;
		$state = $shipping_state;
		$country = $shipping_country;
		$address = $shipping_address;
	}
	
	$payment_data = array(
	'gateway' => $gateway,
	'custom' => $cart_custom_data,
	'txn_id' => $txn_id,
	'txn_type' => $txn_type,
	'transaction_subject' => $txn_subject,
	'first_name' => $eStore_customer->GetFirstName(),
	'last_name' => $eStore_customer->GetLastName(),
	'payer_email' => $eStore_customer->GetEmail(),
	'num_cart_items' => $eStore_cart->GetNumberOfCartItems(),
	'subscr_id' => $txn_id,
	'address' => $address,
	'phone' => $eStore_customer->GetPhone(),
	'coupon_used' => $cart_coupon,
	'eMember_username' => $cart_eMember_id,
	'eMember_userid' => $cart_eMember_id,
	'mc_gross' => $eStore_cart->CalculateCartTotal(),
	'mc_shipping' => $eStore_cart->GetCartShipping(),
	'mc_tax' => $eStore_cart->CalculateCartTotalTax(),
	'address_street' => $street_address,
	'address_city' => $city,
	'address_state' => $state,
	'address_country' => $country,	
	);
	$is_background_post = $eStore_customer->GetISBackgroundPost();
	if($is_background_post==="yes")
	{
		$payment_data['background_post'] = 'yes';
	}
	return $payment_data; 
}

function wp_eStore_get_custom_var($custom)
{
    $delimiter = "&";
    $customvariables = array();
    $namevaluecombos = explode($delimiter, $custom);
    foreach ($namevaluecombos as $keyval_unparsed)
    {
            $equalsignposition = strpos($keyval_unparsed, '=');
            if ($equalsignposition === false)
            {
                $customvariables[$keyval_unparsed] = '';
                continue;
            }
            $key = substr($keyval_unparsed, 0, $equalsignposition);
            $value = substr($keyval_unparsed, $equalsignposition + 1);
            $customvariables[$key] = $value;
    }
    return $customvariables;
}
