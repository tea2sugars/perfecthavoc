<?php
//WP Cart Customer Management Menu
function wp_estore_customer_management_menu()
{
    
	echo '<div class="wrap">
    <h2>'.__('Manage Customers Menu', 'wp_eStore').'</h2>';
    echo '<div id="poststuff"><div id="post-body">';
    if(isset($_REQUEST['eStore_export_customer_data_to_csv']))
    {    	
    	$file_url = eStore_expport_customers_data_to_csv();
    	$export_message = 'Data exported to <a href="'.$file_url.'" target="_blank">Customer List File (Right click on this link and choose "Save As" to save the file to your computer)</a>';
    	echo '<div id="message" class="updated fade"><p>';
    	echo $export_message;
    	echo '</p></div>';   	
    }
    ?>
    
	<div class="postbox">
	<h3><label for="title">Customer Search</label></h3>
	<div class="inside">
	<br /><strong>Search for a customer by entering the First Name, Last Name, Transaction ID or Email Address</strong> (Full or Partial)
	<br /><br />
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="customer_search" id="customer_search" value="true" />
    <input name="eStore_customer_search" type="text" size="40" value=""/>
    <div class="submit">
        <input type="submit" name="customer_search" value="<?php _e('Search'); ?> &raquo;" />
    </div>   
    </form>
    </div></div>

	<div class="postbox">
	<h3><label for="title">Manage Customers Options</label></h3>
	<div class="inside">

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="display_customer_list" id="display_customer_list" value="true" />

    <table width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    <strong>1)</strong> Display Customer List for Product ID:
    </td><td align="left">
    <input name="wp_eStore_product_id" type="text" size="10" value="" />
    <input type="submit" name="display_customer_list" value="<?php _e('Display List'); ?> &raquo;" />
    <br /><i>Enter the product id of the product that you want to display the customer list for and hit Display List.</i><br />
    </td></tr>
    </table>
    </form>

    <br />
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="display_customer_email_list" id="display_customer_email_list" value="true" />

    <table width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    <strong>2)</strong> Display Email List of Customers for Product ID:
    </td><td align="left">
    <input name="wp_eStore_product_id1" type="text" size="10" value="" />
    <input type="submit" name="display_customer_email_list" value="<?php _e('Display Email List'); ?> &raquo;" />
    <br /><i>Use this to display a list of emails (comma separated) of the customers for this product for bulk emailing purpuse.</i><br />
    </td></tr>
    </table>
    </form>

    <br />
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="display_all_customer_email_list" id="display_all_customer_email_list" value="true" />

    <table width="100%" border="0" cellspacing="0" cellpadding="6">
    <tr valign="top"><td width="25%" align="left">
    <strong>3)</strong> Display Email List of All Customers:
    </td><td align="left">
    <input type="submit" name="display_all_customer_email_list" value="<?php _e('Display All Customers Email List'); ?> &raquo;" />
    <br /><i>Use this to display a list of emails (comma separated) of all the customers for bulk emailing purpuse.</i><br />
    </td></tr>
    </table>
    </form>
    </div></div>
    <?php
	if (isset($_POST['limit_update']))
	{
		update_option('eStore_manage_customers_limit', (string)$_POST["eStore_manage_customers_limit"]);
	}
    $limit = get_option('eStore_manage_customers_limit');
    if(empty($limit))
    {
        update_option('eStore_manage_customers_limit', 50);
        $limit = 50;
    }

    global $wpdb;
   	$customer_table_name = WP_ESTORE_CUSTOMER_TABLE_NAME;
       	
    $displaying_specific_list = false;
	if (isset($_POST['customer_search']))
	{
		$search_term = (string)trim($_POST["eStore_customer_search"]);
		update_option('eStore_customer_search', (string)$_POST["eStore_customer_search"]);
		$ret_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE first_name like '%".$search_term."%' OR last_name like '%".$search_term."%' OR txn_id like '%".$search_term."%' OR email_address like '%".$search_term."%'", OBJECT);
        echo display_customer_list($ret_customer_db);
        $displaying_specific_list = true;
		//eStore_display_searched_products($search_term);
	}
    if (isset($_POST['display_customer_list']))
    {
    	$selected_product_id = (string)$_POST["wp_eStore_product_id"];
    	//$ret_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE purchased_product_id = '$selected_product_id'", OBJECT);
    	$ret_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE purchased_product_id IN ($selected_product_id) ORDER BY date DESC", OBJECT);
        echo display_customer_list($ret_customer_db);
        $displaying_specific_list = true;
    }
    if (isset($_POST['display_customer_email_list']))
    {
    	$selected_product_id = (string)$_POST["wp_eStore_product_id1"];
    	//$ret_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE purchased_product_id = '$selected_product_id'", OBJECT);
		$ret_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE purchased_product_id IN ($selected_product_id)", OBJECT);    	
        echo display_customer_email_list($ret_customer_db);
        $displaying_specific_list = true;
    }
    if (isset($_POST['display_all_customer_email_list']))
    {
    	$wp_eStore_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name ORDER BY id DESC", OBJECT);
        echo display_customer_email_list($wp_eStore_customer_db);
        $displaying_specific_list = true;
    }    

    if (!$displaying_specific_list)
    {
        echo eStore_display_customer_list_limit($limit);
        ?>
    	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
        <input type="hidden" name="limit_update" id="limit_update" value="true" />
        <br />
        <strong>Customers Display Limit Per Page : </strong>
        <input name="eStore_manage_customers_limit" type="text" size="10" value="<?php echo get_option('eStore_manage_customers_limit'); ?>"/>
            <input type="submit" name="limit_update" value="<?php _e('Update'); ?> &raquo;" />
        </form>
        <?php
    }
	// Add Customer Button
	echo '<br /><br /><a href="admin.php?page=wp_eStore_customer_addedit" class="button rbutton">'.__('Add New', 'wp_eStore').'</a>';
	echo ' <a href="admin.php?page=wp_eStore_admin" class="button rbutton">'.__('Admin Functions', 'wp_eStore').'</a>';
?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <br /><input type="submit" class="button rbutton" name="eStore_export_customer_data_to_csv" value="<?php _e('Export Customers Data to a CSV File'); ?> &raquo;" />
    <br /><i>Use this to export all of your customers data to a CSV file (comma separated).</i><br /><br />
    </form>
<?php 
	
	echo '</div></div>';
    echo '</div>';
}

function wp_estore_add_customer_menu()
{
	echo '<div class="wrap">';
	echo "<h2>Add/Edit Customers</h2>";
	echo '<div id="poststuff"><div id="post-body">';

	global $wpdb;
	$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
	$sales_table_name = WP_ESTORE_DB_SALES_TABLE_NAME;

	//If Customer is being edited, grab current customer info
	if (isset($_GET['editcustomer']) && $_GET['editcustomer']!='')
	{
		$theid = $_GET['editcustomer'];
		$editingcustomer = $wpdb->get_row("SELECT * FROM $customer_table_name WHERE id = '$theid'", OBJECT);
	}

	if (isset($_POST['Submit']))
	{
		if(!isset($_POST['editedcustomer'])){$_POST['editedcustomer']="";}
		//Get the data from the post
		$post_editedcustomer = esc_sql($_POST['editedcustomer']);
		$post_firstname = esc_sql($_POST['firstname']);
		$post_lastname = esc_sql($_POST['lastname']);
		$post_emailaddress = esc_sql($_POST['emailaddress']);
		$post_purchasedproductid = esc_sql($_POST['purchasedproductid']);
		if($post_purchasedproductid=='')$post_purchasedproductid=0;
		
		$post_txn_id = esc_sql($_POST['txn_id']);
		$post_date = esc_sql($_POST['date']);
		if(empty($post_date)){
			$post_date = (date ("Y-m-d"));
		}
		$clienttime	= (date ("H:i:s"));
		$post_sale_amt = esc_sql($_POST['sale_amount']);
		$post_coupon_code_used = esc_sql($_POST['coupon_code_used']);
		$post_member_username = esc_sql($_POST['member_username']);
			
		$post_product_name = esc_sql(stripslashes($_POST['product_name']));
		$tmpaddress = htmlentities(stripslashes($_POST['address']) , ENT_COMPAT);
		$post_address = esc_sql($tmpaddress);
		$post_phone = esc_sql($_POST['phone']);
		$post_qty_purchased = esc_sql($_POST['purchase_qty']);	
		$post_subsc_id = "";	
		$post_ipaddress = esc_sql($_POST['ipaddress']);		
		$post_status = esc_sql($_POST['status']);
		$post_sr_number = esc_sql($_POST['serial_number']);
		$post_notes = esc_sql($_POST['notes']);
		
		if ($post_editedcustomer=='')
		{			
			$updatedb = "INSERT INTO $customer_table_name (first_name, last_name, email_address, purchased_product_id,txn_id,date,sale_amount,coupon_code_used,member_username,product_name,address,phone,subscr_id,purchase_qty,ipaddress,status,serial_number,notes) VALUES ('$post_firstname', '$post_lastname','$post_emailaddress','$post_purchasedproductid','$post_txn_id','$post_date','$post_sale_amt','$post_coupon_code_used','$post_member_username','$post_product_name','$post_address','$post_phone','$post_subsc_id','$post_qty_purchased','$post_ipaddress','$post_status','$post_sr_number','$post_notes')";
			$results = $wpdb->query($updatedb);

			$updatedb2 = "INSERT INTO $sales_table_name (cust_email, date, time, item_id, sale_price) VALUES ('$post_emailaddress','$post_date','$clienttime','$post_purchasedproductid','$post_sale_amt')";
			$results = $wpdb->query($updatedb2);			
			echo '<div id="message" class="updated fade"><p>Customer &quot;'.$post_firstname.'&quot; created.</p></div>';
		}
		else
		{
			$updatedb = "UPDATE $customer_table_name SET first_name = '$post_firstname', last_name = '$post_lastname', email_address = '$post_emailaddress', purchased_product_id = '$post_purchasedproductid', txn_id='$post_txn_id',date='$post_date',sale_amount='$post_sale_amt',coupon_code_used='$post_coupon_code_used',member_username='$post_member_username',product_name='$post_product_name',address='$post_address',phone='$post_phone',purchase_qty='$post_qty_purchased',ipaddress='$post_ipaddress',status='$post_status',serial_number='$post_sr_number',notes='$post_notes' WHERE id='$post_editedcustomer'";
			$results = $wpdb->query($updatedb);
			//Get the updated customer again
    		$_GET['editcustomer'] = $post_editedcustomer;
    		$editingcustomer = $wpdb->get_row("SELECT * FROM $customer_table_name WHERE id = '$post_editedcustomer'", OBJECT);			
			echo '<div id="message" class="updated fade"><p>'.__('Customer', 'wp_eStore').' &quot;'.$post_firstname.'&quot; '.__('updated.', 'wp_eStore').'</p></div>';
		}
	}
	// Copy Product Details
	if (isset($_POST['copy_customer']))
	{
            $post_orig_customer_id = esc_sql($_POST['orig_customer_id']);
	    $editingcustomer = $wpdb->get_row("SELECT * FROM $customer_table_name WHERE id = '$post_orig_customer_id'", OBJECT);
	    echo '<div id="message" class="updated fade"><p>'.__('Details from Customer ID', 'wp_eStore').' &quot;'.$post_orig_customer_id.'&quot; '.__('has been copied. Make your changes and save the new details.', 'wp_eStore').'</p></div>';
	}	
	// Delete Customer
	if (isset($_POST['deletecustomer']) && isset($_POST['record_id']))
	{
		$thecustomer = $_POST['record_id'];
		$updatedb = "DELETE FROM $customer_table_name WHERE id='$thecustomer'";
		$results = $wpdb->query($updatedb);
		echo '<div id="message" class="updated fade"><p>'.__('Customer deleted.', 'wp_eStore').'</p></div>';
	}
?>
	<div class="postbox">
	<h3><label for="title">Customer Details</label></h3>
	<div class="inside">
<form method="post" action="admin.php?page=wp_eStore_customer_addedit">
<table class="form-table">

<?php if (isset($_GET['editcustomer']) && $_GET['editcustomer']!=''){
	echo '<input name="editedcustomer" type="hidden" value="'.$_GET['editcustomer'].'" />';
	echo '<tr valign="top">';
	echo '<th scope="row">ID</th>';
	echo '<td><strong>'.$_GET['editcustomer'].'</strong> (This value is for internal use and cannot be changed)</td>';
	echo '</tr>';
	
}else if(isset($editingcustomer)){
	//Copying an existing customer or this object is already loaded
}else{//New record (initialize with empty data)
	$editingcustomer = new stdClass();
	$editingcustomer->id = "";
	$editingcustomer->first_name = "";
	$editingcustomer->last_name = "";
	$editingcustomer->email_address = "";
	$editingcustomer->address = "";
	$editingcustomer->phone = "";
	$editingcustomer->purchased_product_id = "";
	$editingcustomer->product_name = "";
	$editingcustomer->txn_id = "";
	$editingcustomer->date = "";
	$editingcustomer->sale_amount = "";
	$editingcustomer->purchase_qty = "";
	$editingcustomer->coupon_code_used = "";
	$editingcustomer->member_username = "";
	$editingcustomer->ipaddress = "";
	$editingcustomer->status = "";
	$editingcustomer->serial_number = "";
	$editingcustomer->notes = "";
}
?>

<tr valign="top">
<th scope="row"><?php _e('First Name', 'wp_eStore'); ?></th>
<td><input name="firstname" type="text" id="firstname" value="<?php echo $editingcustomer->first_name; ?>" size="40" /><br/><?php _e('First Name of the customer', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Last Name', 'wp_eStore'); ?></th>
<td><input name="lastname" type="text" id="lastname" value="<?php echo $editingcustomer->last_name; ?>" size="40" /><br/><?php _e('Last Name of the customer', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Email Address', 'wp_eStore'); ?></th>
<td><input name="emailaddress" type="text" id="emailaddress" value="<?php echo $editingcustomer->email_address; ?>" size="60" /><br/><?php _e('The Email Address of the customer', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Address', 'wp_eStore'); ?></th>
<td><textarea name="address" cols="50" rows="3"><?php echo $editingcustomer->address; ?></textarea>
<br/><?php _e('The address of the customer', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Phone', 'wp_eStore'); ?></th>
<td><input name="phone" type="text" id="phone" value="<?php echo $editingcustomer->phone; ?>" size="20" /><br/><?php _e('The phone number of the customer', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Purchased Product's ID", 'wp_eStore'); ?></th>
<td><input name="purchasedproductid" type="text" id="purchasedproductid" value="<?php echo $editingcustomer->purchased_product_id; ?>" size="5" /><br/><?php _e('The product ID of the purchased product', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Product Name', 'wp_eStore'); ?></th>
<td><input name="product_name" type="text" id="product_name" value="<?php echo $editingcustomer->product_name; ?>" size="60" /><br/><?php _e('The name of the purchased product', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Transaction ID', 'wp_eStore'); ?></th>
<td><input name="txn_id" type="text" id="txn_id" value="<?php echo $editingcustomer->txn_id; ?>" size="30" /><br/><?php _e('The transaction ID associated with this purchase', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Date', 'wp_eStore'); ?></th>
<td><input name="date" type="text" id="date" value="<?php echo $editingcustomer->date; ?>" size="20" /><br/><?php _e('The purchase date (yyyy-mm-dd)', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Price Paid', 'wp_eStore'); ?></th>
<td><input name="sale_amount" type="text" id="sale_amount" value="<?php echo $editingcustomer->sale_amount; ?>" size="10" /><br/><?php _e('Purchase amount', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Quantity Purchased', 'wp_eStore'); ?></th>
<td><input name="purchase_qty" type="text" id="purchase_qty" value="<?php echo $editingcustomer->purchase_qty; ?>" size="5" /><br/><?php _e('Item quantity', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Coupon Code Used', 'wp_eStore'); ?></th>
<td><input name="coupon_code_used" type="text" id="coupon_code_used" value="<?php echo $editingcustomer->coupon_code_used; ?>" size="20" /><br/><?php _e('Coupon code used for this purchase', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('eMember User ID', 'wp_eStore'); ?></th>
<td><input name="member_username" type="text" id="member_username" value="<?php echo $editingcustomer->member_username; ?>" size="5" /><br/><?php _e('The eMember user ID of this customer if any. If you are not using the <a href="http://www.tipsandtricks-hq.com/?p=1706" target="_blank">WP eMember</a> plugin then the value in this field does not matter so leave empty.', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('IP Address', 'wp_eStore'); ?></th>
<td><input name="ipaddress" type="text" id="ipaddress" value="<?php echo $editingcustomer->ipaddress; ?>" size="30" /><br/><?php _e('IP Address of the customer if available.', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Payment Status', 'wp_eStore'); ?></th>
<td><input name="status" type="text" id="status" value="<?php echo $editingcustomer->status; ?>" size="15" /><br/><?php _e('Payment status of this transaction. For manual payment this is set to "Unpaid" by default. You can set it to "Paid" after you actually receive the money from the buyer.', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Serial Number Data', 'wp_eStore'); ?></th>
<td><textarea name="serial_number" cols="50" rows="3"><?php echo $editingcustomer->serial_number; ?></textarea>
<br/><?php _e('Serial number/license key data (if any) for this purchase.', 'wp_eStore'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Admin Notes', 'wp_eStore'); ?></th>
<td><textarea name="notes" cols="50" rows="3"><?php echo $editingcustomer->notes; ?></textarea>
<br/><?php _e('You can use this field to add a special note for admin purpose. This is only visible to you (the admin).', 'wp_eStore'); ?></td>
</tr>

<?php 
if(!isset($_GET['editcustomer'])){$_GET['editcustomer']="";}
$data = array();
$data['record_id'] = $_GET['editcustomer'];
$data['txn_id'] = $editingcustomer->txn_id;
$data['payer_email'] = $editingcustomer->email_address;
$additional_customer_data = "";
$additional_customer_data = apply_filters('eStore_additional_customer_data_filter', $additional_customer_data, $data);
echo $additional_customer_data;
?>

</table>
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Customer', 'wp_eStore'); ?>" /></p>
</form>

<?php
if ($_GET['editcustomer']!='') {
echo "<form method=\"post\" action=\"admin.php?page=wp_eStore_customer_addedit\" onSubmit=\"return confirm('Are you sure you want to delete this entry?');\">";
echo "<input type=\"hidden\" name=\"record_id\" value=".$_GET['editcustomer']." />";
echo '<input type="submit" style="border: none; background-color:transparent; padding:0; cursor:pointer; color:red;text-decoration:underline" name="deletecustomer" value="Delete Customer">';
echo "</form>";
} 
?>

</div></div>

<div class="postbox">
<h3><label for="title">Copy Customer Details from an Existing Customer</label></h3>
<div class="inside">

To copy the details from an existing Customer simply enter the ID of the Customer whose details you wish to copy and hit the "Copy Customer Details" button
<br /><br />
<form method="post" action="">
Customer ID:
<input name="orig_customer_id" type="text" id="orig_customer_id" value="" size="5" />
<input type="submit" name="copy_customer" value="<?php _e('Copy Customer Details', 'wp_eStore'); ?>" />
</form>
</div>
</div>
<?php

	echo '<br /><a href="admin.php?page=wp_estore_customer_management" class="button rbutton">'.__('Manage Customers', 'wp_eStore').'</a>';
	echo '</div></div>';
	echo '</div>';
}

function display_customer_list($ret_customer_db)
{
    $output = "";
    $output .= '
    <table class="widefat">
    <thead><tr>
    <th scope="col">'.__('Customer ID', 'wp_eStore').'</th>
    <th scope="col">'.__('First Name', 'wp_eStore').'</th>
    <th scope="col">'.__('Last Name', 'wp_eStore').'</th>
    <th scope="col">'.__('Email Address', 'wp_eStore').'</th>
    <th scope="col">'.__("Product ID & Name", 'wp_eStore').'</th>
    <th scope="col">'.__('Transaction ID', 'wp_eStore').'</th>
    <th scope="col">'.__('Date', 'wp_eStore').'</th>
    <th scope="col">'.__('Price Paid', 'wp_eStore').'</th>
    <th scope="col">'.__('Coupon Used', 'wp_eStore').'</th>
    <th scope="col">'.__('Status', 'wp_eStore').'</th>
    <th scope="col"></th>
    </tr></thead>
    <tbody>';

    if ($ret_customer_db)
    {
        foreach ($ret_customer_db as $ret_customer_db)
        {
            $output .= '<tr>';
            $output .= '<td>'.$ret_customer_db->id.'</td>';
            $output .= '<td>'.$ret_customer_db->first_name.'</td>';
            $output .= '<td>'.$ret_customer_db->last_name.'</td>';
            $output .= '<td>'.$ret_customer_db->email_address.'</td>';
            $output .= '<td>['.$ret_customer_db->purchased_product_id.'] '. $ret_customer_db->product_name. '</td>';
            $output .= '<td>'.$ret_customer_db->txn_id.'</td>';
            $output .= '<td>'.$ret_customer_db->date.'</td>';
            $output .= '<td>'.$ret_customer_db->sale_amount.'</td>';
            if(!empty($ret_customer_db->coupon_code_used))
            {
                    $output .= '<td>'.$ret_customer_db->coupon_code_used.'</td>';
            }
            else
            {
                    $output .= '<td><strong>--</strong></td>';
            }
            $output .= '<td>'.$ret_customer_db->status.'</td>';
            $output .= '<td><a href="admin.php?page=wp_eStore_customer_addedit&editcustomer='.$ret_customer_db->id.'">'.__('Edit / View Details', 'wp_eStore').'</a></td>';
            $output .= '</tr>';
        }
    }
    else
    {
        $output .= '<tr> <td colspan="8">'.__('No Customers found.', 'wp_eStore').'</td> </tr>';
    }

    $output .= '</tbody>
    </table>';
    return $output;
}

function eStore_display_customer_list_limit($limit)
{
    $output = "";
    $output .= '
    <table class="widefat">
    <thead><tr>
    <th scope="col">'.__('Customer ID', 'wp_eStore').'</th>
    <th scope="col">'.__('First Name', 'wp_eStore').'</th>
    <th scope="col">'.__('Last Name', 'wp_eStore').'</th>
    <th scope="col">'.__('Email Address', 'wp_eStore').'</th>
    <th scope="col">'.__('Product ID & Name', 'wp_eStore').'</th>
    <th scope="col">'.__('Transaction ID', 'wp_eStore').'</th>
    <th scope="col">'.__('Date', 'wp_eStore').'</th>
    <th scope="col">'.__('Price Paid', 'wp_eStore').'</th>
    <th scope="col">'.__('Coupon Used', 'wp_eStore').'</th>
    <th scope="col">'.__('Status', 'wp_eStore').'</th>
    <th scope="col"></th>
    </tr></thead>
    <tbody>';

    if (isset($_GET['product_page'])){
        $page = $_GET['product_page'];
    }
    else{
        $page = 1;
    }
    $start = ($page - 1) * $limit;

    global $wpdb;
    //$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
    $customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";

    $wp_eStore_db = $wpdb->get_results("SELECT * FROM $customer_table_name ORDER BY id DESC LIMIT $start, $limit", OBJECT);
    //get total rows
    $totalrows = $wpdb->get_var("SELECT COUNT(*) FROM $customer_table_name;");

    if ($wp_eStore_db)
    {
        foreach ($wp_eStore_db as $wp_eStore_db)
        {
            $output .= '<tr>';
            $output .= '<td>'.$wp_eStore_db->id.'</td>';
            $output .= '<td>'.$wp_eStore_db->first_name.'</td>';
            $output .= '<td>'.$wp_eStore_db->last_name.'</td>';
            $output .= '<td>'.$wp_eStore_db->email_address.'</td>';
            $output .= '<td>['.$wp_eStore_db->purchased_product_id.'] '.$wp_eStore_db->product_name.'</td>';
            $output .= '<td>'.$wp_eStore_db->txn_id.'</td>';
            $output .= '<td>'.$wp_eStore_db->date.'</td>';
            $output .= '<td>'.$wp_eStore_db->sale_amount.'</td>';
            if(!empty($wp_eStore_db->coupon_code_used))
            {
                    $output .= '<td>'.$wp_eStore_db->coupon_code_used.'</td>';
            }
            else
            {
                    $output .= '<td><strong>--</strong></td>';
            }
            $output .= '<td>'.$wp_eStore_db->status.'</td>';	
            $output .= '<td><a href="admin.php?page=wp_eStore_customer_addedit&editcustomer='.$wp_eStore_db->id.'">'.__('Edit / View Details', 'wp_eStore').'</a></td>';
            $output .= '</tr>';
        }
    }
    else
    {
        $output .= '<tr> <td colspan="8">'.__('No Customers found.', 'wp_eStore').'</td> </tr>';
    }

    $output .= '</tbody>
    </table>';
    
    //Number of pages setup
    if($totalrows <= $limit){//No pagination needed
    	return $output;
    }
    $pages = ceil($totalrows / $limit);
    $output .= "<br /><strong>Pages</strong>&nbsp;&nbsp;";
    if($page==1){
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".$page."' class=\"button rbutton\">".$page."</a>&nbsp;";
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".($page+1)."' class=\"button rbutton\">".($page+1)."</a>&nbsp;";
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".($page+2)."' class=\"button rbutton\">".($page+2)."</a>&nbsp;";
    }
    else if($page==$pages){
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".($page-2)."' class=\"button rbutton\">".($page-2)."</a>&nbsp;";
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".($page-1)."' class=\"button rbutton\">".($page-1)."</a>&nbsp;";
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".$page."' class=\"button rbutton\">".$page."</a>&nbsp;";			
    }
    else{
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".($page-1)."' class=\"button rbutton\">".($page-1)."</a>&nbsp;";
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".$page."' class=\"button rbutton\">".$page."</a>&nbsp;";
            $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".($page+1)."' class=\"button rbutton\">".($page+1)."</a>&nbsp;";
    }

    //Last page
    $output .= " .......... ";
    $output .= "<a href='admin.php?page=wp_estore_customer_management&product_page=".$pages."' class=\"button rbutton\">".$pages."</a>&nbsp;";
    return $output;
}

function display_customer_email_list($ret_customer_db)
{
	$output = "";
	if ($ret_customer_db)
	{
		$customer_list = array();
		foreach ($ret_customer_db as $row){
			$customer_list[] = $row->email_address;
		}
		$customer_email_list_unique = array_unique($customer_list);
		foreach ($customer_email_list_unique as $email){
        	$output .= $email;
        	$output .= ', ';
		}
	}
	else{
		$output .= 'No Customers found.';
	}
	return $output;
}

function eStore_expport_customers_data_to_csv()
{
    global $wpdb;
    $customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
    $ret_customer_db = $wpdb->get_results("SELECT * FROM $customer_table_name ORDER BY id DESC", OBJECT);
    $csv_output = "Row ID, First Name, Last Name, Email, Purchased Product ID, Transaction ID, Date, Price Paid, Quantity Purchased, Unit Price, Coupon Code Used, eMember userid, Phone, Address, Product Name, Variations and Input, IP Address, Status, Admin Notes\n";

    foreach($ret_customer_db as $result){
    if(empty($result->purchase_qty)){$result->purchase_qty=1;}
    $unit_price = number_format(($result->sale_amount/$result->purchase_qty),2);
    $variations = eStore_get_all_string_inside("(", ")", $result->product_name);
    $var_and_custom_input = implode(", ",$variations);
    $csv_output .= eStore_escape_csv_value(stripslashes($result->id )). ','.
           eStore_escape_csv_value(stripslashes($result->first_name)). ', '. 
           eStore_escape_csv_value(stripslashes($result->last_name)) .  ','.
           eStore_escape_csv_value(stripslashes($result->email_address)). ', ' . 
           eStore_escape_csv_value(stripslashes($result->purchased_product_id)). ', ' . 
           eStore_escape_csv_value(stripslashes($result->txn_id)).', ' . 
           eStore_escape_csv_value(stripslashes($result->date)) . ', ' .
           eStore_escape_csv_value(stripslashes($result->sale_amount)) . ',' .
           eStore_escape_csv_value(stripslashes($result->purchase_qty)) . ',' .
           eStore_escape_csv_value(stripslashes($unit_price)) . ',' .
           eStore_escape_csv_value(stripslashes($result->coupon_code_used)) . ','.
           eStore_escape_csv_value(stripslashes($result->member_username)) .','.
           eStore_escape_csv_value(stripslashes($result->phone)) .','.
           eStore_escape_csv_value(stripslashes($result->address)) .','. 
           eStore_escape_csv_value(stripslashes($result->product_name)) .','. 
           eStore_escape_csv_value(stripslashes($var_and_custom_input)) .','.
           eStore_escape_csv_value(stripslashes($result->ipaddress)). ','.
           eStore_escape_csv_value(stripslashes($result->status)). ','.
           eStore_escape_csv_value(stripslashes($result->notes)). ','."\n"; 
    }
    $customer_list_file_path = dirname(__FILE__)."/customer_list.csv";
    file_put_contents($customer_list_file_path,$csv_output);
    $file_url = WP_ESTORE_URL.'/customer_list.csv';
    return $file_url;
}
