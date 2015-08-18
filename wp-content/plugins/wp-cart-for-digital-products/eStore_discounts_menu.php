<?php

function wp_estore_discounts_menu()
{
	echo '<div class="wrap">
	<h2>'.__('Manage Coupons/Discounts', 'wp_eStore').'</h2>';
	echo '<div id="poststuff"><div id="post-body">';

	global $wp_eStore_config,$wpdb;
	$currency_symbol = get_option('cart_currency_symbol');
	$coupon_table_name = WP_ESTORE_COUPON_TABLE_NAME;

    if (isset($_POST['info_update']))
    {
        update_option('eStore_use_coupon_system', ($_POST['eStore_use_coupon_system']=='1') ? '1':'' );
        echo '<div id="message" class="updated fade"><p>Coupon Settings Updated</p></div>';
    }
    if (isset($_POST['marketing_settings']))
    {
        $tmp_offer_text = htmlentities(stripslashes($_POST['eStore_special_offer_text']) , ENT_COMPAT, "UTF-8");
        update_option('eStore_special_offer_text', $tmp_offer_text);

        echo '<div id="message" class="updated fade">Marketing Options Updated</div>';
    }
    if (isset($_POST['auto_discount_settings']))
    {
        update_option('eStore_use_auto_discount', ($_POST['eStore_use_auto_discount']=='1') ? '1':'' );
        $wp_eStore_config->setValue('eStore_use_auto_discount', ($_POST['eStore_use_auto_discount']=='1') ? '1':'' );        
        update_option('eStore_amount_free_shipping_threshold', (string)$_POST["eStore_amount_free_shipping_threshold"]);
        $wp_eStore_config->setValue('eStore_amount_free_shipping_threshold', (string)$_POST["eStore_amount_free_shipping_threshold"]);
        update_option('eStore_qty_free_shipping_threshold', (string)$_POST["eStore_qty_free_shipping_threshold"]);
        $wp_eStore_config->setValue('eStore_qty_free_shipping_threshold', (string)$_POST["eStore_qty_free_shipping_threshold"]);
        
        update_option('eStore_amount_threshold_auto_coupon', (string)$_POST["eStore_amount_threshold_auto_coupon"]);
        $wp_eStore_config->setValue('eStore_amount_threshold_auto_coupon', (string)$_POST["eStore_amount_threshold_auto_coupon"]);
        update_option('eStore_amount_threshold_auto_coupon_code', (string)$_POST["eStore_amount_threshold_auto_coupon_code"]);
        $wp_eStore_config->setValue('eStore_amount_threshold_auto_coupon_code', (string)$_POST["eStore_amount_threshold_auto_coupon_code"]);
        update_option('eStore_qty_threshold_auto_coupon', (string)$_POST["eStore_qty_threshold_auto_coupon"]);
        $wp_eStore_config->setValue('eStore_qty_threshold_auto_coupon', (string)$_POST["eStore_qty_threshold_auto_coupon"]);
        update_option('eStore_qty_threshold_auto_coupon_code', (string)$_POST["eStore_qty_threshold_auto_coupon_code"]);
        $wp_eStore_config->setValue('eStore_qty_threshold_auto_coupon_code', (string)$_POST["eStore_qty_threshold_auto_coupon_code"]);
                
        $wp_eStore_config->saveConfig();  
        
        echo '<div id="message" class="updated fade"><p>Auto Discount Settings Updated</p></div>';
    }    
	//If product is being edited, grab current product info
	if (isset($_GET['editproduct']) && $_GET['editproduct']!='')
	{
		$theid = $_GET['editproduct'];
		$editingproduct = $wpdb->get_row("SELECT * FROM $coupon_table_name WHERE id = '$theid'", OBJECT);
	}

	if (isset($_POST['Submit']))
	{	
		//validate some of the entry fields
		$error_message = "";
		if(!empty($_POST['expiry_date'])){
			if(!wp_eStore_is_date_valid($_POST['expiry_date'])){
		    	$error_message .= "<br />The expiry date is not in the valid form. Please enter the date in yyyy-mm-dd form. Example: 2011-12-25";    	
		    }
		}
	    if(empty($_POST['coupon_code'])){
	    	$error_message .= "<br />Error! Coupon code field cannot be empty.";
	    }
	    if(empty($_POST['discount_value'])){
	    	$error_message .= "<br />Error! Discount value field cannot be empty.";
	    }
		if(empty($error_message))
		{
			if(!isset($_POST['editedproduct'])){$_POST['editedproduct']="";}
			//Get the post data
			$post_editedproduct = esc_sql($_POST['editedproduct']);
			$post_coupon_code = esc_sql($_POST['coupon_code']);
			$post_discount_value = esc_sql($_POST['discount_value']);
			$post_discount_type = esc_sql($_POST['discount_type']);
			$redemption_limit = esc_sql($_POST['redemption_limit']);
			if(empty($redemption_limit)){
				if($redemption_limit != '0'){
					$redemption_limit = '9999';
				}
			}
			$redemption_count = esc_sql($_POST['redemption_count']);
	        $post_coupon_active = esc_sql($_POST['coupon_active']);
	        $post_property = esc_sql($_POST['property']);
	        $post_logic = esc_sql($_POST['logic']);
	        $post_value = esc_sql($_POST['value']);
	        $curr_symbol = get_option('cart_currency_symbol');
                if(!empty($post_value)){
                    $post_value = str_replace($curr_symbol,"",$post_value);
		}
			$start_date = esc_sql($_POST['start_date']);
			if(empty($start_date)){$start_date="0000-00-00";}

	        $expiry_date = esc_sql($_POST['expiry_date']);
			if(empty($expiry_date)){$expiry_date="0000-00-00";}
				        
			$dynamic = "";			
	        if ($post_coupon_active == 1){
	            $post_coupon_active = 'Yes';
	        }
	        else{
	            $post_coupon_active = 'No';
	        }
	
			if ($post_editedproduct=='')
			{
				$updatedb = "INSERT INTO $coupon_table_name (coupon_code, discount_value, discount_type, active,redemption_limit,redemption_count,property,logic,value,expiry_date,dynamic,start_date) VALUES ('$post_coupon_code', '$post_discount_value','$post_discount_type','$post_coupon_active','$redemption_limit','$redemption_count','$post_property','$post_logic','$post_value','$expiry_date','$dynamic','$start_date')";
				$results = $wpdb->query($updatedb);
				echo '<div id="message" class="updated fade"><p>Coupon &quot;'.$post_coupon_code.'&quot; created.</p></div>';
			}
			else
			{
				$updatedb = "UPDATE $coupon_table_name SET coupon_code = '$post_coupon_code', discount_value = '$post_discount_value', discount_type = '$post_discount_type', active = '$post_coupon_active',redemption_limit='$redemption_limit',redemption_count='$redemption_count',property='$post_property',logic='$post_logic',value='$post_value',expiry_date='$expiry_date',dynamic='$dynamic',start_date='$start_date' WHERE id='$post_editedproduct'";
				$results = $wpdb->query($updatedb);
				echo '<div id="message" class="updated fade"><p>'.__('Coupon', 'wp_eStore').' &quot;'.$post_coupon_code.'&quot; '.__('updated.', 'wp_eStore').'</p></div>';
			}
		}
		else
		{
			echo '<div id="message" class="updated fade"><p>'.$error_message.'</p></div>';
		}
	}

	//Delete from the coupons list
	if(isset($_REQUEST['delete_record']) && isset($_REQUEST['record_id'])){
		$thecouponid = $_REQUEST['record_id'];
		$del_query = "DELETE FROM $coupon_table_name WHERE id='$thecouponid'";
		$results = $wpdb->query($del_query);
        $redirect_url = 'admin.php?page=wp_eStore_discounts&msg='.urlencode("Coupon deleted successfully!");
        eStore_redirect_to_url($redirect_url);		
	}

	if(isset($_REQUEST['msg'])){
		echo '<div id="message" class="updated fade"><p>'.$_REQUEST['msg'].'</p></div>';
	}
	
	?>
	<div class="postbox">
	<h3><label for="title">Coupons Settings</label></h3>
	<div class="inside">

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />
    <br /><strong>Use Coupon System: </strong>
    <input name="eStore_use_coupon_system" type="checkbox"<?php if(get_option('eStore_use_coupon_system')!='') echo ' checked="checked"'; ?> value="1"/>
    <span class="description"> When checked your customers will be able to enter a coupon code in the shopping cart before checkout.</span>
    <div class="submit">
        <input type="submit" class="button" name="info_update" value="Update &raquo;" />
    </div>
    </form>
	</div></div>

	<div class="postbox">
	<h3><label for="title">Automatic Discount Settings</label></h3>
	<div class="inside">

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="auto_discount_settings" id="auto_discount_settings" value="true" />
    
    <table class="form-table">

	<tr valign="top">
	<th scope="row">Use Automatic Discount Feature</th>
    <td><input name="eStore_use_auto_discount" type="checkbox"<?php if(get_option('eStore_use_auto_discount')!='') echo ' checked="checked"'; ?> value="1"/>
    <span class="description">When checked your customers will automatically get discounts in their shopping cart according to the conditions specified below.</span>
    </td>
    </tr>
        
	<tr valign="top">
	<th scope="row">Free Shipping for Sub-total Over</th>
	<td><?php echo $currency_symbol." "; ?><input name="eStore_amount_free_shipping_threshold" type="text" id="eStore_amount_free_shipping_threshold" value="<?php echo get_option('eStore_amount_free_shipping_threshold'); ?>" size="4" /> (Example: 50.00)
	<br/><p class="description">Customers who order more than this amount will get free shipping. Leave empty if you do not want to use it.</p></td>
	</tr>
	<tr valign="top">
	<th scope="row">Free Shipping for Quantity Over</th>
	<td><input name="eStore_qty_free_shipping_threshold" type="text" id="eStore_qty_free_shipping_threshold" value="<?php echo get_option('eStore_qty_free_shipping_threshold'); ?>" size="4" /> (Example: 5)
	<br/><p class="description">Customers who order more than this quantity will get free shipping. Leave empty if you do not want to use it.</p></td>
	</tr>

	<tr valign="top">
	<th scope="row">Apply Coupon Automatically for Sub-total Over</th>
	<td>Subtotal: <?php echo $currency_symbol." "; ?><input name="eStore_amount_threshold_auto_coupon" type="text" id="eStore_amount_threshold_auto_coupon" value="<?php echo get_option('eStore_amount_threshold_auto_coupon'); ?>" size="4" />
	&nbsp;&nbsp;Coupon Code to Apply: <input name="eStore_amount_threshold_auto_coupon_code" type="text" id="eStore_amount_threshold_auto_coupon_code" value="<?php echo get_option('eStore_amount_threshold_auto_coupon_code'); ?>" size="20" />
	<br/><p class="description">Customers who order more than this amount will automatically get the specified coupon applied to their cart. Leave empty if you do not want to use it.</p></td>
	</tr>
	<tr valign="top">
	<th scope="row">Apply Coupon Automatically for Quantity Over</th>
	<td>Quantity: <input name="eStore_qty_threshold_auto_coupon" type="text" id="eStore_qty_threshold_auto_coupon" value="<?php echo get_option('eStore_qty_threshold_auto_coupon'); ?>" size="4" />
	&nbsp;&nbsp;Coupon Code to Apply: <input name="eStore_qty_threshold_auto_coupon_code" type="text" id="eStore_qty_threshold_auto_coupon_code" value="<?php echo get_option('eStore_qty_threshold_auto_coupon_code'); ?>" size="20" />
	<br/><p class="description">Customers who order more than this quantity will automatically get the specified coupon applied to their cart. Leave empty if you do not want to use it.</p></td>
	</tr>
		    
	</table>
	
    <div class="submit">
        <input type="submit" class="button" name="auto_discount_settings" value="Update &raquo;" />
    </div>
    </form>
	</div></div>
	
	
	<div class="postbox">
	<h3><label for="title">Add A Coupon</label></h3>
	<div class="inside">

	<form method="post" action="admin.php?page=wp_eStore_discounts">
	<table width="850">

    <?php 
    if (isset($_GET['editproduct']) && $_GET['editproduct']!='') { 
    	echo '<input name="editedproduct" type="hidden" value="'.$_GET['editproduct'].'" />'; 
    }else if(isset($editingproduct)){
		//Copying an existing recored or this object is already loaded
	}else{//New record (initialize with empty data)
		$editingproduct = new stdClass();
		$editingproduct->id = "";
		$editingproduct->coupon_code = "";
		$editingproduct->discount_value = "";
		$editingproduct->discount_type = "0";
		$editingproduct->redemption_limit = "";
		$editingproduct->redemption_count = "";
		$editingproduct->start_date = "";
		$editingproduct->expiry_date = "";
		$editingproduct->property = "";
		$editingproduct->logic = "";
		$editingproduct->value = "";		
	}
    ?>
	<thead><tr>
	<th align="left"><strong>Coupon Code</strong></th>
	<th align="left"><strong>Discount Value</strong></th>
	<th align="left"><strong>Redemption Limit</strong></th>
	<th align="left"><strong>Redemption Count</strong></th>
	<th align="left"><strong>Start Date<br />(yyyy-mm-dd)</strong></th>
	<th align="left"><strong>Expiry Date<br />(yyyy-mm-dd)</strong></th>
	<th align="left"><strong>Active</strong></th>	
	</tr></thead>
	<tbody>

	<tr>
	<td width="160"><input name="coupon_code" type="text" id="coupon_code" value="<?php echo $editingproduct->coupon_code; ?>" size="20" /></td>
	<td width="120"><input name="discount_value" type="text" id="discount_value" value="<?php echo $editingproduct->discount_value; ?>" size="5" />
		<select name='discount_type'>
		<option value='0' <?php if($editingproduct->discount_type=='0')echo 'selected="selected"';?>>%</option>
		<option value='1' <?php if($editingproduct->discount_type=='1')echo 'selected="selected"';?>><?php echo $currency_symbol; ?></option>
		</select>
	</td>
	<td width="100"><input name="redemption_limit" type="text" id="redemption_limit" value="<?php echo $editingproduct->redemption_limit; ?>" size="4" /><br /></td>
	<td width="100"><input name="redemption_count" type="text" id="redemption_count" value="<?php echo $editingproduct->redemption_count; ?>" size="4" /></td>
	<td width="250"><input class="estore_date" name="start_date" type="text" id="start_date" value="<?php if($editingproduct->start_date!="0000-00-00"){echo $editingproduct->start_date;}else{echo "";} ?>" size="10" /></td>
	<td width="250"><input class="estore_date" name="expiry_date" type="text" id="expiry_date" value="<?php if($editingproduct->expiry_date!="0000-00-00"){echo $editingproduct->expiry_date;}else{echo "";} ?>" size="10" /></td>
	<td><input type='checkbox' value='1' checked='checked' name='coupon_active' /></td>	
	<td>
	<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Coupon" /></p>
	</td></tr>

	<tr>
	<th align="left"><strong>Conditions (Optional)</strong></th>
	</tr>

	<tr>
	  <td>
		<select name='property'>
		<option value='1' <?php if($editingproduct->property=='1')echo 'selected="selected"';?>>Individual Item Quantity</option>
		<option value='2' <?php if($editingproduct->property=='2')echo 'selected="selected"';?>>Total Quantity</option>
		<option value='3' <?php if($editingproduct->property=='3')echo 'selected="selected"';?>>Subtotal Amount</option>
		<option value='4' <?php if($editingproduct->property=='4')echo 'selected="selected"';?>>Total Amount</option>
		<option value='5' <?php if($editingproduct->property=='5')echo 'selected="selected"';?>>Item Name</option>
		<option value='6' <?php if($editingproduct->property=='6')echo 'selected="selected"';?>>Item ID</option>
		<option value='7' <?php if($editingproduct->property=='7')echo 'selected="selected"';?>>Free Shipping if total</option>
		<option value='8' <?php if($editingproduct->property=='8')echo 'selected="selected"';?>>Product Category ID</option>
		</select>
	  </td>
	  <td>
		<select name='logic'>
		<option value='1' <?php if($editingproduct->logic=='1')echo 'selected="selected"';?>>Is greater than</option>
		<option value='2' <?php if($editingproduct->logic=='2')echo 'selected="selected"';?>>Is equal to</option>
		<option value='3' <?php if($editingproduct->logic=='3')echo 'selected="selected"';?>>Contains</option>
		</select>
	  </td>
	  <td><input name="value" type="text" id="value" value="<?php echo $editingproduct->value; ?>" size="20" /></td>
	</tr>

	</tbody>
	</table>
	</form>
	</div></div>

	<?php
	//eStore_display_coupons_list($coupon_table_name);
	eStore_display_coupons_list_new();
    echo '<br /><br />';
    eStore_display_marketing_options();

    echo '</div></div>'; //end of poststuff div
	echo '</div>';//end of wrap div
}

function eStore_display_marketing_options()
{
    $offer_text = html_entity_decode(get_option('eStore_special_offer_text'), ENT_COMPAT,"UTF-8");
?>
    <div class="postbox">
    <h3><label for="title">Marketing Options</label></h3>
    <div class="inside">

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

    <table class="form-table">

    <tr valign="top">
    <th scope="row"><strong>Special Thank You Page Offer Text</strong></th>
    
    <td><textarea name="eStore_special_offer_text" cols="100" rows="5"><?php echo $offer_text; ?></textarea>
    <br />Text that you enter here will be displayed to the customer upon successful checkout on the "Thank You" page (HTML code can be used in this field). You can put your special offer (e.g. a special discount coupon) here to upsell.
    You need to show the transaction result on the "Thank You" page using <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=499" target="_blank">this instruction</a> for this offer to show up on the "Thank You" page.
    </td>
    </tr>
    </table>

    <div class="submit">
        <input type="submit" class="button" name="marketing_settings" value="Update &raquo;" />
    </div>
    </form>
    </div></div>
<?php
}

function eStore_display_coupons_list_new()
{
	echo "<h2>Discount Coupons</h2>";

	include_once('eStore_classes/eStore_list_coupons_table.php');
    //Create an instance of our package class...
    $list_table = new eStore_List_Coupons_Table();
    //Fetch, prepare, sort, and filter our data...
    $list_table->prepare_items();

    ?>
    <style type="text/css">
    .column-id {width:6%;}
    .column-thumbnail_url {width:6%;}
    .column-name {width:25%;}
    .column-price {width:6%;}
    .column-sales_count {width:6%;}
    .column-available_copies {width:8%;}
    </style>
    <div class="estore-manage-coupons">
    
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="estore-coupons-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $list_table->display() ?>
        </form>
        
    </div>
    <?php
	echo '<br /><i>Redemption Limit = Maximum Number of times a coupon can be used</i>';
	echo '<br /><i>Redemption Count = Number of times a coupon has been used</i>';
	echo '<br /><i>Start Date = The discount can only be used after or on this date.</i>';
}
