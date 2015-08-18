<?php
include_once('admin_includes.php');
include_once('eStore_utility_functions.php');

$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
$cat_prod_rel_table_name = $wpdb->prefix . "wp_eStore_cat_prod_rel_tbl";
$cat_table_name = $wpdb->prefix . "wp_eStore_cat_tbl";

//WP Cart Product Management Menu
function wp_estore_product_management_menu()
{
	echo '<div class="wrap">
	<h2>'.__('Manage Products', 'wp_eStore').'</h2>';
	echo '<div id="poststuff"><div id="post-body">';

        global $wpdb;
        $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
        
	$eStore_products_per_page = get_option('eStore_products_per_page');
	if (empty($eStore_products_per_page)) 
	{
        echo '<div id="message" class="updated fade"><p>';
        echo 'It appears that you have never saved your settings after installing the plugin! Please visit the settings page of this plugin and save it.';
        echo '</p></div>';
	}
       
	?>
	<br />
	<div class="postbox">
	<h3><label for="title">Product Search</label></h3>
	<div class="inside">
	<br /><strong>Search for a product by entering the full or partial product Name</strong>
	<br /><br />
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />
    
    <input name="eStore_product_search" type="text" size="40" value=""/>
    <div class="submit">
        <input type="submit" name="info_update" class="button" value="Search &raquo;" />
    </div>   
    </form>
    </div></div>
	<?php

	if (isset($_POST['limit_update']))
	{
		update_option('eStore_manage_products_limit2', (string)$_POST["eStore_manage_products_limit2"]);
	}
    $limit = get_option('eStore_manage_products_limit2');
    if(empty($limit))
    {
        update_option('eStore_manage_products_limit2', 50);
        $limit = 50;
    }
    
    if(isset($_REQUEST['deleted'])){
    	echo '<div id="message" class="updated fade"><p><strong>';
	    echo $_REQUEST['msg'];
	    echo '</strong></p></div>';
    }
    if(isset($_REQUEST['Delete']))
    {
    	$prod_id = $_REQUEST['prod_id'];
        if(wp_eStore_delete_product_data($prod_id)){
            $message = "Product successfully deleted";
        }
        else{
            $message = "An error occurded while trying to delete the entry";
        }
        $redirect_url = 'admin.php?page=wp-cart-for-digital-products/wp_eStore1.php&deleted=1&msg='.urlencode($message);
        eStore_redirect_to_url($redirect_url);
    }

    if (isset($_POST['info_update']))
    {
            $search_term = (string)$_POST["eStore_product_search"];
            update_option('eStore_product_search', (string)$_POST["eStore_product_search"]);
            eStore_display_searched_products($search_term);
    }
    else
    {
            eStore_display_products($limit);
    }

    ?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="limit_update" id="limit_update" value="true" />
    <br />
    <strong>Product Display Limit Per Page : </strong>
    <input name="eStore_manage_products_limit2" type="text" size="6" value="<?php echo get_option('eStore_manage_products_limit2'); ?>"/>
    <input type="submit" name="limit_update" class="button" value="Update &raquo;" />
    </form>
        
    <?php
    echo '</div></div>';
    echo '</div>';
}
function eStore_display_searched_products($search_term)
{
	echo '
	<table class="widefat">
	<thead><tr>
	<th scope="col">'.__('ID', 'wp_eStore').'</th>
	<th scope="col">'.__('Image', 'wp_eStore').'</th>
	<th scope="col">'.__('Product Name', 'wp_eStore').'</th>
	<th scope="col">'.__('Price', 'wp_eStore').'</th>
	<th scope="col">'.__('Download Link', 'wp_eStore').'</th>
	<th scope="col">'.__('Sales Count', 'wp_eStore').'</th>
	<th scope="col">'.__('Available Copies', 'wp_eStore').'</th>
	<th scope="col"></th>
	</tr></thead>
	<tbody>';

	global $wpdb;
	global $products_table_name;

    $wp_eStore_db = $wpdb->get_results("SELECT * FROM $products_table_name WHERE name like '%".$search_term."%' or id like '%".$search_term."%'", OBJECT);
	if ($wp_eStore_db)
	{
		foreach ($wp_eStore_db as $wp_eStore_db)
		{
			echo '<tr>';
			echo '<td>'.$wp_eStore_db->id.'</td>';
			if(!empty($wp_eStore_db->thumbnail_url))
			{
				echo '<td><img src="'.$wp_eStore_db->thumbnail_url.'" width="50" height="50"></td>';
			}
			else
			{
				echo '<td><img src="'.WP_ESTORE_URL.'/images/no-image-specified.gif" width="50" height="50"></td>';
			}	
			echo '<td><strong>'.$wp_eStore_db->name.'</strong></td>';
			echo '<td><strong>'.$wp_eStore_db->price.'</strong></td>';
			echo '<td><strong>'.$wp_eStore_db->product_download_url.'</strong></td>';
			echo '<td><strong>'.$wp_eStore_db->sales_count.'</strong></td>';			
			$available_copies = $wp_eStore_db->available_copies;							
			if(empty($available_copies))
			{
				if($available_copies == ''){
					$available_copies = '&#8734;';					
				}
				else{
					$available_copies = '0';
				}
			}
			echo '<td><strong>'.$available_copies.'</strong></td>';	
			
			echo '<td style="text-align: center;"><a href="admin.php?page=wp_eStore_addedit&editproduct='.$wp_eStore_db->id.'">'.__('Edit', 'wp_eStore').'</a>';
			
			echo "<form method=\"post\" action=\"\" onSubmit=\"return confirm('Are you sure you want to delete this entry?');\">";				
			echo "<input type=\"hidden\" name=\"prod_id\" value=".$wp_eStore_db->id." />";
            echo '<input style="border: none; background-color: transparent; padding: 0; cursor:pointer;" type="submit" name="Delete" value="Delete">';
            echo "</form>";
           	echo "</td>";
           	
			echo '</tr>';
		}
	}
	else
	{
		echo '<tr> <td colspan="8">'.__('No Product found.', 'wp_eStore').'</td> </tr>';
	}

	echo '</tbody>
	</table>';
    
	// Add product button
	echo '<br /><br /><a href="admin.php?page=wp_eStore_addedit" class="button rbutton">'.__('Add New Product', 'wp_eStore').'</a>';
	echo ' <a href="admin.php?page=wp_eStore_admin" class="button rbutton">'.__('Admin Functions', 'wp_eStore').'</a>';
}

function eStore_display_products($limit)
{
	include_once('eStore_classes/eStore_list_products_table.php');
    //Create an instance of our package class...
    $products_list_table = new eStore_List_Products_Table();
    //Fetch, prepare, sort, and filter our data...
    $products_list_table->prepare_items();

    ?>
    <style type="text/css">
    .column-id {width:6%;}
    .column-thumbnail_url {width:6%;}
    .column-name {width:25%;}
    .column-price {width:6%;}
    .column-sales_count {width:6%;}
    .column-available_copies {width:8%;}
    .column-product_actions {width:10%;}
    </style>
    <div class="estore-manage-products">
    
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="estore-products-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $products_list_table->display() ?>
        </form>
        
    </div>
    <?php

	//Add Quick links
	echo '<br /><br /><a href="admin.php?page=wp_eStore_addedit" class="button rbutton">'.__('Add New Product', 'wp_eStore').'</a>';
	echo ' <a href="admin.php?page=wp_eStore_admin" class="button rbutton">'.__('Admin Functions', 'wp_eStore').'</a>';
}

//WP Cart Add Products Menu
function wp_estore_add_product_menu()
{
	echo '<div class="wrap">';
	echo '<h2>Add/Edit Products <a href="admin.php?page=wp_eStore_addedit" class="add-new-h2">Add New</a></h2>';
	echo '<div id="poststuff"><div id="post-body">';

	$eStore_products_per_page = get_option('eStore_products_per_page');
	if (empty($eStore_products_per_page)) 
	{
        echo '<div id="message" class="updated fade"><p>';
        echo 'It appears that you have never saved your settings after installing the plugin! Please visit the settings page of this plugin and save it.';
        echo '</p></div>';  			
	}
	
	global $wpdb;
	global $products_table_name;
	global $cat_prod_rel_table_name;
	global $cat_table_name;
	$product_meta_table_name = WP_ESTORE_PRODUCTS_META_TABLE_NAME;

	//If product is being edited, grab current product info
	if (isset($_GET['editproduct']) && $_GET['editproduct']!='')
	{
		$theid = $_GET['editproduct'];
		$editingproduct = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$theid'", OBJECT);
	}

	if (isset($_POST['Submit']))
	{
        if(empty($_POST['productname']))
        {
            echo '<div id="message" class="updated fade"><p>'.__('Product name cannot be empty!', 'wp_eStore').'</p></div>';
        }
        else
        {
		$chars_to_replace = array("\r\n", "\n", "\r");//Used to replaced hidden chars in various fields
		            	
        $curr_symbol = get_option('cart_currency_symbol');
        if(!isset($_POST['editedproduct'])){$_POST['editedproduct']="";}
        if(!isset($_POST['productid'])){$_POST['productid']="";}
        if(!isset($_POST['show_qty'])){$_POST['show_qty']="";}
        if(!isset($_POST['custom_input'])){$_POST['custom_input']="";}
        if(!isset($_POST['custom_price_option'])){$_POST['custom_price_option']="";}
        if(!isset($_POST['ppv_content'])){$_POST['ppv_content']="";}
        if(!isset($_POST['use_pdf_stamper'])){$_POST['use_pdf_stamper']="";}
        if(!isset($_POST['create_license'])){$_POST['create_license']="";}
        if(!isset($_POST['sra'])){$_POST['sra']="";}
        
		$post_editedproduct = esc_sql($_POST['editedproduct']);
		$post_productid = esc_sql($_POST['productid']);
		
		//$tmp_name = htmlentities(stripslashes($_POST['productname']) , ENT_COMPAT, "UTF-8");
		$tmp_name = strip_tags(stripslashes($_POST['productname']));
                $post_productname = esc_sql($tmp_name);
		$post_productprice = esc_sql($_POST['productprice']);		
		if(empty($_POST['a3'])){//filter the price field value if this is not a subscription product
			$post_productprice = str_replace($curr_symbol,"",$post_productprice);
		}
		
		$post_producturl = trim(esc_sql($_POST['producturl']));
		$post_product_downloadable = esc_sql($_POST['productdownloadable']);
		$post_product_shipping = esc_sql($_POST['shippingcost']);
		$post_product_available_copies = esc_sql($_POST['availablecopies']);
		$post_product_button_image_url = trim(esc_sql($_POST['buttonimageurl']));
		$post_product_return_url = trim(esc_sql($_POST['returnurl']));
		$paypal_email = esc_sql($_POST['paypal_email']);
		$post_product_sales_count = esc_sql($_POST['salescount']);	
		//$post_product_description = $wpdb->escape($_POST['productdesc']);
		$tmpdescription = htmlentities(stripslashes($_POST['productdesc']) , ENT_COMPAT, "UTF-8");

		$post_product_description = esc_sql($tmpdescription);
		$post_product_thumbnail = trim(esc_sql($_POST['thumbnail_url']));

        $post_product_variation1 = esc_sql(stripslashes($_POST['variation1']));
        $post_product_variation2 = esc_sql(stripslashes($_POST['variation2']));
        $post_product_variation3 = esc_sql(stripslashes($_POST['variation3']));
        $post_product_variation3 = str_replace($chars_to_replace,"",$post_product_variation3);//replace any hidden newlines
		$variation4 = esc_sql(stripslashes($_POST['variation4']));

		$post_product_commission = trim(esc_sql($_POST['productcommission']));
		
		if ($post_product_downloadable=='on'){
			$post_product_downloadable = 'yes';
		}
		else{
			$post_product_downloadable = 'no';
		}
		// Subscription related fields
                $post_a1 = str_replace($curr_symbol,"",$_POST['a1']);
		$a1 = esc_sql($post_a1);
		$p1 = esc_sql($_POST['p1']);
		$t1 = esc_sql($_POST['t1']);
                $post_a3 = str_replace($curr_symbol,"",$_POST['a3']);
		$a3 = esc_sql($post_a3);
		$p3 = esc_sql($_POST['p3']);
		$t3 = esc_sql($_POST['t3']);
		$sra = esc_sql($_POST['sra']);
		$srt = esc_sql($_POST['srt']);
		$ref_text = esc_sql($_POST['ref_text']);
		if ($sra=='on'){
            $sra='1';
        }
        else{
            $sra='0';
        }
		$custom_input = esc_sql($_POST['custom_input']);
		if ($custom_input=='on'){
            $custom_input='1';
        }
        else{
            $custom_input='0';
        }
        $custom_input_label = esc_sql($_POST['custom_input_label']);
        $aweber_list = esc_sql(trim(stripslashes($_POST['aweber_list'])));
        $currency_code = esc_sql($_POST['currency_code']);
        $target_thumb_url = trim(esc_sql($_POST['target_thumb_url']));
        $target_button_url = trim(esc_sql($_POST['target_button_url']));
        $weight = esc_sql($_POST['itemweight']);
        $product_url = trim(esc_sql($_POST['product_url']));
	$tmp_item_spec_instruction = stripslashes($_POST['item_spec_instruction']);
        $post_item_spec_instruction = esc_sql($tmp_item_spec_instruction);      
        
        $ppv_content = esc_sql($_POST['ppv_content']);
		if ($ppv_content=='on'){
            $ppv_content='1';
        }
        else{
            $ppv_content='0';
        }              
        $use_pdf_stamper = esc_sql($_POST['use_pdf_stamper']);
		if ($use_pdf_stamper=='on'){
            $use_pdf_stamper='1';
        }
        else{
            $use_pdf_stamper='0';
        }   
           
        $create_license = esc_sql($_POST['create_license']);
		if ($create_license=='on'){
            $create_license='1';
        }
        else{
            $create_license='0';
        }      
        $post_tax = esc_sql($_POST['tax']);    
        $post_author_id = trim(esc_sql($_POST['author_id']));   
        $show_qty = esc_sql($_POST['show_qty']);
		if ($show_qty=='on'){
            $show_qty='1';
        }
        else{
            $show_qty='0';
        }    
        $tier2_commission = esc_sql($_POST['tier2_commission']);   
        $custom_price_option = esc_sql($_POST['custom_price_option']);
		if ($custom_price_option=='on'){
            $custom_price_option='1';
        }
        else{
            $custom_price_option='0';
        }
        $post_additional_images = esc_sql($_POST['additional_images']);  
        $post_additional_images = str_replace($chars_to_replace,"",$post_additional_images);//replace any hidden newlines 
         
        $post_oldprice = esc_sql($_POST['old_price']);
        $post_rev_share_commission = esc_sql($_POST['rev_share_commission']);
        $post_rev_share_commission = str_replace($curr_symbol,"",$post_rev_share_commission);
        $post_rev_share_commission = str_replace("%","",$post_rev_share_commission);        
        $post_per_customer_qty_limit = esc_sql($_POST['per_customer_qty_limit']);
                             
		//----- Some default input values ----------
        if ($post_product_sales_count=='') $post_product_sales_count=0;        
        if ($p1=='') $p1=0;        
        if ($p3=='' || $p3<1) $p3=1;        
        if ($srt=='') $srt=0;

        if(!isset($_POST['category'])){$_POST['category']="";}
        
        //Validate the form URL inputs
        $form_url_fields_validated = true;
        $validation_error_message = "";
        $url_validation_error_msg_ignore = "<p><i>If you know for sure that the URL is correct then ignore this message. You can copy and paste the URL in a browser's address bar to make sure the URL is correct.</i></p>";
        if(!eStore_is_valid_url_if_not_empty($post_product_thumbnail))
        {
        	$validation_error_message .= "<br /><strong>The URL specified in the \"Thumbnail Image URL\" field does not seem to be a valid URL! Please check this value again:</strong>";
        	$validation_error_message .= "<br />".$post_product_thumbnail."<br />";
        	$form_url_fields_validated = false;
        }
        if(!eStore_is_valid_url_if_not_empty($target_thumb_url))
        {
        	$validation_error_message .= "<br /><strong>The URL specified in the \"Thumbnail Target URL\" field does not seem to be a valid URL! Please check this value again:</strong>";
        	$validation_error_message .= "<br />".$target_thumb_url."<br />";
        	$form_url_fields_validated = false;
        }  
        if(!eStore_is_valid_url_if_not_empty($product_url))
        {
        	$validation_error_message .= "<br /><strong>The URL specified in the \"Product Page URL\" field does not seem to be a valid URL! Please check this value again:</strong>";
        	$validation_error_message .= "<br />".$product_url."<br />";
        	$form_url_fields_validated = false;
        } 
        if(!eStore_is_valid_url_if_not_empty($post_product_button_image_url))
        {
        	$validation_error_message .= "<br /><strong>The URL specified in the \"Button Image URL\" field does not seem to be a valid URL! Please check this value again:</strong>";
        	$validation_error_message .= "<br />".$post_product_button_image_url."<br />";
        	$form_url_fields_validated = false;
        }    
        if(!eStore_is_valid_url_if_not_empty($target_button_url))
        {
        	$validation_error_message .= "<br /><strong>The URL specified in the \"Button Redirect Target URL\" field does not seem to be a valid URL! Please check this value again:</strong>";
        	$validation_error_message .= "<br />".$target_button_url."<br />";
        	$form_url_fields_validated = false;
        }                            
        if(!$form_url_fields_validated)
        {
			//Get the updated product again
			$_GET['editproduct'] = $post_editedproduct;
			$editingproduct = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$post_editedproduct'", OBJECT);      
			        	
        	echo '<div id="message" class="error fade"><p>';
        	echo $validation_error_message;
        	echo $url_validation_error_msg_ignore;
        	echo '</p></div>';  	
        }  
         
        //Insert or Update the database          		
		if ($post_editedproduct=='')//Insert the new product
		{
			$updatedb = "INSERT INTO $products_table_name (name, price, product_download_url, downloadable, shipping_cost, available_copies, button_image_url, return_url, sales_count,description,thumbnail_url,variation1,variation2,variation3,commission,a1,p1,t1,a3,p3,t3,sra,srt,ref_text,paypal_email,custom_input,custom_input_label,variation4,aweber_list,currency_code,target_thumb_url,target_button_url,weight,product_url,item_spec_instruction,ppv_content,use_pdf_stamper,create_license,tax,author_id,show_qty,tier2_commission,custom_price_option,additional_images,old_price,rev_share_commission,per_customer_qty_limit) VALUES ('$post_productname', '$post_productprice','$post_producturl','$post_product_downloadable','$post_product_shipping','$post_product_available_copies','$post_product_button_image_url','$post_product_return_url','$post_product_sales_count','$post_product_description','$post_product_thumbnail','$post_product_variation1','$post_product_variation2','$post_product_variation3','$post_product_commission','$a1','$p1','$t1','$a3','$p3','$t3','$sra','$srt','$ref_text','$paypal_email','$custom_input','$custom_input_label','$variation4','$aweber_list','$currency_code','$target_thumb_url','$target_button_url','$weight','$product_url','$post_item_spec_instruction','$ppv_content','$use_pdf_stamper','$create_license','$post_tax','$post_author_id','$show_qty','$tier2_commission','$custom_price_option','$post_additional_images','$post_oldprice','$post_rev_share_commission','$post_per_customer_qty_limit')";
			$results = $wpdb->query($updatedb);
	
			$wp_eStore_product_ret = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = LAST_INSERT_ID()", OBJECT);
			$cur_product_id = $wp_eStore_product_ret->id;		
			
			//Add the new category relationship
			$categories = $_POST['category'];
			if(!empty($categories))
			{
				while (list ($key,$val) = @each ($categories)) { 
					$updatedb = "INSERT INTO $cat_prod_rel_table_name (cat_id, prod_id) VALUES ('$val', '$cur_product_id')";
					$results = $wpdb->query($updatedb);	
				}	
			}
                        //Add download meta data
                        $download_limit_time = esc_sql($_POST['download_limit_time']);			
			if(!empty($download_limit_time)){
				$updatedb = "INSERT INTO $product_meta_table_name (prod_id, meta_key, meta_value) VALUES ('$cur_product_id', 'download_limit_time','$download_limit_time')";
				$results = $wpdb->query($updatedb);
			}
                        $download_limit_count = esc_sql($_POST['download_limit_count']);			
			if(!empty($download_limit_count)){
				$updatedb = "INSERT INTO $product_meta_table_name (prod_id, meta_key, meta_value) VALUES ('$cur_product_id', 'download_limit_count','$download_limit_count')";
				$results = $wpdb->query($updatedb);
			}
			//Add product meta data			
			$available_key_codes = esc_sql($_POST['available_key_codes']);			
			if(!empty($available_key_codes)){
				$updatedb = "INSERT INTO $product_meta_table_name (prod_id, meta_key, meta_value) VALUES ('$cur_product_id', 'available_key_codes','$available_key_codes')";
				$results = $wpdb->query($updatedb);
			}
			
			//Get the handle to the inserted product
			$_GET['editproduct'] = $wp_eStore_product_ret->id;
			$editingproduct = $wp_eStore_product_ret;
			echo '<div id="message" class="updated fade"><p>Product &quot;'.$post_productname.'&quot; created.</p></div>';
			do_action('eStore_new_product_added',$_POST,$_GET['editproduct']);
		}
		else//Update existing product
		{
			$updatedb = "UPDATE $products_table_name SET name = '$post_productname', price = '$post_productprice', product_download_url = '$post_producturl', downloadable = '$post_product_downloadable', shipping_cost = '$post_product_shipping', available_copies = '$post_product_available_copies', button_image_url='$post_product_button_image_url', return_url = '$post_product_return_url', sales_count = '$post_product_sales_count', description = '$post_product_description', thumbnail_url = '$post_product_thumbnail', variation1='$post_product_variation1', variation2='$post_product_variation2',variation3='$post_product_variation3',commission='$post_product_commission',a1='$a1',p1='$p1',t1='$t1',a3='$a3',p3='$p3',t3='$t3',sra='$sra',srt='$srt',ref_text='$ref_text',paypal_email='$paypal_email',custom_input='$custom_input',custom_input_label='$custom_input_label',variation4='$variation4',aweber_list='$aweber_list',currency_code='$currency_code',target_thumb_url='$target_thumb_url',target_button_url='$target_button_url',weight='$weight',product_url='$product_url',item_spec_instruction='$post_item_spec_instruction',ppv_content='$ppv_content',use_pdf_stamper='$use_pdf_stamper',create_license='$create_license',tax='$post_tax',author_id='$post_author_id',show_qty='$show_qty',tier2_commission='$tier2_commission',custom_price_option='$custom_price_option',additional_images='$post_additional_images',old_price='$post_oldprice',rev_share_commission='$post_rev_share_commission',per_customer_qty_limit='$post_per_customer_qty_limit' WHERE id='$post_editedproduct'";
			$results = $wpdb->query($updatedb);

			//Delete the existing category relationship
			$updatedb = "DELETE FROM $cat_prod_rel_table_name WHERE prod_id='$post_editedproduct'";
			$results = $wpdb->query($updatedb);	
			
			//Add the new relationship
			$categories = $_POST['category'];
			if(!empty($categories))
			{
				while (list ($key,$val) = @each ($categories)) { 
					$updatedb = "INSERT INTO $cat_prod_rel_table_name (cat_id, prod_id) VALUES ('$val', '$post_editedproduct')";
					$results = $wpdb->query($updatedb);	
				}	
			}	
			
			//Update product meta data
                        
                        $download_limit_time = esc_sql($_POST['download_limit_time']);		
			//check if download time is specified
			$editingproductmeta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$post_editedproduct' AND meta_key='download_limit_time'", OBJECT);
			if($editingproductmeta){//update existing meta record
				$meta_key_name = "download_limit_time";			
				$updatedb_meta = "UPDATE $product_meta_table_name SET meta_value='$download_limit_time' WHERE prod_id='$post_editedproduct' AND meta_key='$meta_key_name'";
			}else{//Add new meta record
				$updatedb_meta = "INSERT INTO $product_meta_table_name (prod_id, meta_key, meta_value) VALUES ('$post_editedproduct', 'download_limit_time','$download_limit_time')";
			}
			$results = $wpdb->query($updatedb_meta);
                        
                        $download_limit_count = esc_sql($_POST['download_limit_count']);		
			//check if download count is specified
			$editingproductmeta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$post_editedproduct' AND meta_key='download_limit_count'", OBJECT);
			if($editingproductmeta){//update existing meta record
				$meta_key_name = "download_limit_count";			
				$updatedb_meta = "UPDATE $product_meta_table_name SET meta_value='$download_limit_count' WHERE prod_id='$post_editedproduct' AND meta_key='$meta_key_name'";
			}else{//Add new meta record
				$updatedb_meta = "INSERT INTO $product_meta_table_name (prod_id, meta_key, meta_value) VALUES ('$post_editedproduct', 'download_limit_count','$download_limit_count')";
			}
			$results = $wpdb->query($updatedb_meta);
                        
                        
			$available_key_codes = esc_sql($_POST['available_key_codes']);		
			//check if a serial key meta value for this product exists
			$editingproductmeta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$post_editedproduct' AND meta_key='available_key_codes'", OBJECT);
			if($editingproductmeta){//update existing meta record
				$meta_key_name = "available_key_codes";			
				$updatedb_meta = "UPDATE $product_meta_table_name SET meta_value='$available_key_codes' WHERE prod_id='$post_editedproduct' AND meta_key='$meta_key_name'";
			}else{//Add new meta record
				$updatedb_meta = "INSERT INTO $product_meta_table_name (prod_id, meta_key, meta_value) VALUES ('$post_editedproduct', 'available_key_codes','$available_key_codes')";
			}
			$results = $wpdb->query($updatedb_meta);				
	
			//Get the handle to the updated product
			$_GET['editproduct'] = $post_editedproduct;
			$editingproduct = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$post_editedproduct'", OBJECT);
			echo '<div id="message" class="updated fade"><p>'.__('Product', 'wp_eStore').' &quot;'.$post_productname.'&quot; '.__('updated.', 'wp_eStore').'</p></div>';
			do_action('eStore_product_updated',$_POST,$_GET['editproduct']);
		}
      }
	}
	// Copy Product Details
	if (isset($_REQUEST['copy_product']))
	{
            $post_orig_product_id = esc_sql($_REQUEST['orig_product_id']);
	    $editingproduct = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$post_orig_product_id'", OBJECT);
	    echo '<div id="message" class="updated fade"><p>'.__('Details from Product ID', 'wp_eStore').' &quot;'.$post_orig_product_id.'&quot; '.__('has been copied. Make your changes and save the new product.', 'wp_eStore').'</p></div>';
	}

	//Delete Product
	if (isset($_POST['deleteproduct']) && isset($_POST['prod_id']))
	{
		$theproduct = esc_sql($_POST['prod_id']);
		$updatedb = "DELETE FROM $products_table_name WHERE id='$theproduct'";
		$results = $wpdb->query($updatedb);

		$updatedb = "DELETE FROM $cat_prod_rel_table_name WHERE prod_id='$theproduct'";
		$results = $wpdb->query($updatedb);
                
                $del_meta_db_val = "DELETE FROM $product_meta_table_name WHERE prod_id='$theproduct' AND meta_key='download_limit_time'";
		$results = $wpdb->query($del_meta_db_val);
                
                $del_meta_db_val = "DELETE FROM $product_meta_table_name WHERE prod_id='$theproduct' AND meta_key='download_limit_count'";
		$results = $wpdb->query($del_meta_db_val);

		$del_meta_db_val = "DELETE FROM $product_meta_table_name WHERE prod_id='$theproduct' AND meta_key='available_key_codes'";
		$results = $wpdb->query($del_meta_db_val);	
		echo '<div id="message" class="updated fade"><p>'.__('Product deleted.', 'wp_eStore').'</p></div>';
		do_action('eStore_product_deleted',$theproduct);
	}
    eStore_admin_css();
    echo eStore_admin_js_scripts();
?>
<div class="eStore_grey_box">
You can add a new product or edit an existing product from this interface. When creating a new product you can choose to copy the details from an existing product too (see the option below). This option is helpful when creating multiple products with similar details.
</div>

	<div class="postbox">
	<h3><label for="title">Product Details (Not sure how to add a product? <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=593" target="_blank">Watch the video tutorial</a>)</label></h3>
	<div class="inside">
<form method="post" action="admin.php?page=wp_eStore_addedit">
<table class="form-table">

<?php 
$current_prod_id = "";
if (isset($_GET['editproduct']) && $_GET['editproduct']!='')
{
	$current_prod_id = $_GET['editproduct'];
	echo '<input name="editedproduct" type="hidden" value="'.$_GET['editproduct'].'" />'; 
	echo '<tr valign="top"><th scope="row">Product ID </th>';
	echo '<td><strong>'.$_GET['editproduct'].'</strong> (This value is for internal use and cannot be changed)</td>';
	echo '</tr>';
}else if(isset($editingproduct)){
	//Copying an existing product
	$current_prod_id = $editingproduct->id;
}else{//New record (initialize with empty data)
	$editingproduct = eStore_get_empty_product_object();
}
?>

<tr valign="top">
<th scope="row">Product Name</th>
<td><input name="productname" type="text" id="productname" value="<?php echo htmlspecialchars($editingproduct->name); ?>" size="40" />
<br /><p class="description">Name of the Product</p></td>
</tr>

<tr valign="top">
<th scope="row">Product Price</th>
<td><input name="productprice" type="text" id="productprice" value="<?php echo $editingproduct->price; ?>" size="20" />
<br/><p class="description">Enter Price to two decimal places. Examples: 10.00 or 6.70 or 1999.95 etc (<strong><i>Do not put currency symbol in the price</i></strong>). See the Subscription payment section below if you are configuring a subscribe button</p></td>
</tr>
</table>

<div class=eStore_blue_box>
<i><strong>Optional Product Details</strong></i> (If any of the following options is not needed for your product you can leave the field empty)
</div>

<div class="msg_head">Additional Product Details (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row">Product Description</th>
<td><textarea name="productdesc" cols="83" rows="3"><?php echo $editingproduct->description; ?></textarea>
<br/><p class="description">This description is used when displaying products using the fancy display option.</p></td>
</tr>

<tr valign="top">
<th scope="row">Thumbnail Image URL</th>
<td><input name="thumbnail_url" type="text" id="thumbnail_url" value="<?php if ($editingproduct->thumbnail_url!='') { echo $editingproduct->thumbnail_url; } else { echo ''; } ?>" size="100" />
<input type="button" id="thumbnail_url_button" name="thumbnail_url_button" class="button rbutton" value="Upload File" />
<?php wp_eStore_show_file_upload_more_info(); ?>
<p class="description">This thumbnail image is used when displaying products using the fancy display option.</p></td>
</tr>

<tr valign="top">
<th scope="row">Thumbnail Target URL</th>
<td><input name="target_thumb_url" type="text" id="target_thumb_url" value="<?php if ($editingproduct->target_thumb_url!='') { echo $editingproduct->target_thumb_url; } else { echo ''; } ?>" size="100" />
<br/><p class="description">If you want to link the thumbnail image to a URL (clicking on this thumbnail will take the visitor to this URL) then specify the target URL in the above field, otherwise leave empty.</p></td>
</tr>

<tr valign="top">
<th scope="row">Old Price</th>
<td><input name="old_price" type="text" id="old_price" value="<?php echo $editingproduct->old_price; ?>" size="10" />
<br/><p class="description">The original price (for display purpose only). This price will be slashed out in some of the fancy displays (not available in all the fancy display options)</p></td>
</tr>

<tr valign="top">
<th scope="row">Additional Product Images</th>
<td><textarea name="additional_images" cols="83" rows="2"><?php echo $editingproduct->additional_images; ?></textarea>
<br/><p class="description">Enter the image URLs separated by comma. When you display your product using a fancy display with lightbox option, your customers will be able to view these images in the lightbox by clicking the next or previous buttons.</p></td>
</tr>

<tr valign="top">
<th scope="row">Product Page URL</th>
<td><input name="product_url" type="text" id="product_url" value="<?php if ($editingproduct->product_url!='') { echo $editingproduct->product_url; } else { echo ''; } ?>" size="100" />
<br/><p class="description">If you have a specific page for detailed description of this product then specify the URL here otherwise leave empty. The product name will be linked to this page when using the fancy display option.</p></td>
</tr>

<tr valign="top">
<th scope="row">Product Category</th>
<td>
<?php
	$wp_eStore_cat_db = $wpdb->get_results("SELECT * FROM $cat_table_name ORDER BY cat_name ASC", OBJECT);
	if ($wp_eStore_cat_db)
	{
		$existing_categories = array();
		if($_GET['editproduct']!='')
		{
            $theid = $_GET['editproduct'];            
		    $editingproduct_cat_db = $wpdb->get_results("SELECT * FROM $cat_prod_rel_table_name WHERE prod_id = '$theid'", OBJECT);
		    if ($editingproduct_cat_db)
		    {		    	
		    	foreach ($editingproduct_cat_db as $existing_product_cat)
		    	{
		    		array_push($existing_categories,$existing_product_cat->cat_id);
		    	}
		    }   
		}		
		else if(isset($_POST['copy_product']))
		{
		    $theid = $_POST['orig_product_id'];            
		    $editingproduct_cat_db = $wpdb->get_results("SELECT * FROM $cat_prod_rel_table_name WHERE prod_id = '$theid'", OBJECT);
		    if ($editingproduct_cat_db)
		    {		    	
		    	foreach ($editingproduct_cat_db as $existing_product_cat)
		    	{
		    		array_push($existing_categories,$existing_product_cat->cat_id);
		    	}
		    }			
		}
		
		foreach ($wp_eStore_cat_db as $cat_item)
		{
			$checked = "";
			if(in_array($cat_item->cat_id,$existing_categories)){
				$checked = "checked='checked'";
			}			
			echo "<input type='checkbox' name='category[]' value='".$cat_item->cat_id."' ".$checked."/> ".$cat_item->cat_name."<br />";						
		}
	}
	else
	{
		echo 'No Categories Found! <a href="admin.php?page=wp_eStore_categories"><strong>Add a Category</strong></a>';
	}
?>
</td>
</tr>
	
<tr valign="top">
<th scope="row">Button Image URL</th>
<td><input name="buttonimageurl" type="text" id="buttonimageurl" value="<?php if ($editingproduct->button_image_url!='') { echo $editingproduct->button_image_url; } else { echo ''; } ?>" size="100" />
<input type="button" id="buttonimageurl_button" name="buttonimageurl_button" class="button rbutton" value="Upload File" />
<?php wp_eStore_show_file_upload_more_info(); ?>
<p class="description">This is useful when you want to customize the look of your payment button using a custom button image for this product.</p></td>
</tr>

<tr valign="top">
<th scope="row">Button Redirect Target URL</th>
<td><input name="target_button_url" type="text" id="target_button_url" value="<?php if ($editingproduct->target_button_url!='') { echo $editingproduct->target_button_url; } else { echo ''; } ?>" size="100" />
<br/><p class="description">Only use this if you want the Add to Cart button for this product to go to the specified URL above (example: a landing page, sales page) instead of adding the product to the shopping cart. Useful when you are selling/promoting product of others.</p></td>
</tr>

<tr valign="top">
<th scope="row">Display Quantity Field</th>
<td><input type="checkbox" name="show_qty" <?php if ($editingproduct->show_qty=='1'){echo 'checked="checked"';} ?> />
<br /><p class="description">When checked, it will display a text box next to the Add to Cart button so the customers can enter a quantity amount for the item.</p></td>
</tr>

<tr valign="top">
<th scope="row">Allow Customers to Specify a Price</th>
<td><input type="checkbox" name="custom_price_option" <?php if ($editingproduct->custom_price_option=='1'){echo 'checked="checked"';} ?> />
<br /><p class="description">When checked, it will display a text box next to the Add to Cart button so the customers can specify a price amount for this item. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=994" target="_blank">Read More Here</a></p></td>
</tr>

<tr valign="top">
<th scope="row">Collect Customer Input</th>
<td><input type="checkbox" name="custom_input" <?php if ($editingproduct->custom_input=='1'){echo 'checked="checked"';} ?> />
&nbsp;&nbsp;Field Label: <input name="custom_input_label" type="text" id="custom_input_label" value="<?php echo $editingproduct->custom_input_label; ?>" size="40" />
<br /><p class="description">When checked, it will display a text box next to the Add to Cart button where the customer can enter special instruction for that product (eg. a Name if selling Engraving).</p></td>
</tr>

<tr valign="top">
<th scope="row">Product Specific Commission</th>
<td>Primary Commission: <input name="productcommission" type="text" id="productcommission" value="<?php if ($editingproduct->commission!='') { echo $editingproduct->commission; } else { echo ''; } ?>" size="3" />
&nbsp;&nbsp;2nd Tier Commission: <input name="tier2_commission" type="text" id="tier2_commission" value="<?php if ($editingproduct->tier2_commission!='') { echo $editingproduct->tier2_commission; } else { echo ''; } ?>" size="3" /> (optional)
<br/><p class="description">Use this option when you want to offer a special affiliate commision rate for this product when using with the <a href="http://www.tipsandtricks-hq.com/?p=1474" target="_blank">WP Affiliate Platform</a> plugin. Only specify the amount (do not include the % or $ sign as it is already specified in the settings menu of the affiliate plugin).</p></td>
</tr>

<tr valign="top">
<th scope="row">Reference Text</th>
<td><input name="ref_text" type="text" id="ref_text" value="<?php echo $editingproduct->ref_text; ?>" size="20" />
<?php 
if(defined('WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE')){//eMember is installed
	$eMember_levels = array();
	$eMember_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE);
	$level_details_output = "";
        if ($eMember_levels != null) {
            $level_details_output .= '<table>';
            $level_details_output .= '<tr><th>Membership Level Name</th><th>Level ID</th></tr>';
            foreach ($eMember_levels as $level) {
                if($level->id == 1){continue;}
                $level_details_output .= '<tr><td>'.$level->alias.'</td><td>'.$level->id.'</td></tr>';
            }
            $level_details_output .= '</table>';
        } else if(empty($eMember_levels)) {
            $level_details_output .= '<p>No membership levels found! Go to membership level menu and create a level.</p>';
        }
	echo '<span class="eStore_more_info_anchor"> [+] See your membership level IDs</span>';
	echo '<div class="eStore_more_info_body" style="color:#666666;">';
	echo $level_details_output;
	echo '</div>';
}
?>
<p class="description">Reference Text field can be useful when integrating with a membership plugin. If you are configuring a payment button for the WP eMember plugin then this is where you specify the membership level ID. <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=60" target="_blank">Read More Here</a></p></td>
</tr>
</table>
</div>

<div class="msg_head">Digital Content Details (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row">Digital Product URL</th>
<td>
<input id="producturl_button" class="button rbutton" type="button" value="Upload File" />
<span class="eStore_more_info_anchor"> [+] more info<br /></span>
<div class="eStore_more_info_body" style="color:#666666;">
<p>Uploading a file and using it as the digital content of this product is a 3 step process:</p>
<ol>
<li><i>Click the above upload button</i></li> 
<li><i>Choose the file to upload which will upload that file to your media library</i></li> 
<li><i>Finally, click the <strong>Insert into Post</strong> button, this will populate the uploaded file's URL in the following field.</i></li> 
</ol>
</div>
<br />
<input name="producturl" type="text" id="producturl" value="<?php if ($editingproduct->product_download_url!='') { echo $editingproduct->product_download_url; } else { echo ''; } ?>" size="120" />
<p class="description">The URL of the digital product that you are selling (this content will be given to your customer via an encrypted link), example: <code>http://www.example.com/downloads/ebook/superman.zip</code></p> 
<div class="eStore_more_info_anchor">&nbsp;[+] more info</div>
<div class="eStore_more_info_body">
<li style="margin-left:15px;margin-top:10px;color:#666666;"><i>If you haven't uploaded the file to your server yet then you can do so using the <a href="media-new.php" target="_blank">WordPress's Media Uploader</a> or an FTP software. Simply copy the "File URL" after you upload the file and paste it in the above field.</i></li> 
<li style="margin-left:15px;color:#666666;"><i>If you are making a bundled product then enter the product IDs separated by comma (example: 3,8,18). If the product has multiple files then enter the file URLs separated by comma.</i></li> 
<li style="margin-left:15px;color:#666666;"><i>If you want to integrate with Amazon S3 then read <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=1101" target="_blank">this instruction</a> first.</i></li>
<li style="margin-left:15px;color:#666666;"><i>Please note that the file can be kept on any accessible location on the web (<a href="http://www.tipsandtricks-hq.com/forum/topic/download-directory-protection" target="_blank">more explanation here</a>). The buyer will receive an encrypted link which will let them download this product.</i></li>
</div>
</td></tr>

<tr valign="top">
<th scope="row">Downloadable</th>
<td><input type="checkbox" name="productdownloadable" <?php if ($editingproduct->downloadable!='no'){echo 'checked="checked"';} ?> /> 
<p class="description">If checked the digital content will be delivered via an anonymous encrypted download process (the customer won't know where it is coming from). If unchecked the buyers will be redirected to the above URL when they click on the encrypted link.</p></td>
</tr>
<?php 
$download_limit_time = "";
if(isset($_GET['editproduct'])){
    $theid = $_GET['editproduct'];
    $editingproductmeta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$theid' AND meta_key='download_limit_time'", OBJECT);
    if($editingproductmeta){
        $download_limit_time = $editingproductmeta->meta_value;
    }
}

$download_limit_count = "";
if(isset($_GET['editproduct'])){
    $theid = $_GET['editproduct'];
    $editingproductmeta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$theid' AND meta_key='download_limit_count'", OBJECT);
    if($editingproductmeta){
        $download_limit_count = $editingproductmeta->meta_value;
    }
}
?>
<tr valign="top">
<th scope="row">Selling Pay Per View Content?</th>
<td><p>If you are selling Pay Per View content (example: a streaming video embedded on a page) then <a href="http://www.tipsandtricks-hq.com/ecommerce/using-wordpress-permalinks-as-digital-products-apr-1217" target="_blank">read our pay per view setup documentation</a> to learn how to set it up.</p>
</td>
</tr>

<tr valign="top">
<th scope="row">Duration of Download Link</th>
<td><input name="download_limit_time" type="text" id="download_limit_time" value="<?php echo $download_limit_time; ?>" size="3" /> &nbsp;Hours
<br/><p class="description">This is the duration of time the encrypted links for this product will remain active. If you do not specify a value in this field, it will default to the values set in the settings menu.</p></td>
</tr>

<tr valign="top">
<th scope="row">Download Limit Count</th>
<td><input name="download_limit_count" type="text" id="itemweight" value="<?php echo $download_limit_count; ?>" size="3" /> &nbsp;Times
<br/><p class="description">Number of times an encrypted download link can be used before the link expires. If you do not specify a value in this field, it will default to the values set in the settings menu.</p></td>
</tr>

</table>
</div>

<div class="msg_head">Variations (Click to Expand)</div>
<div class="msg_body">
&nbsp;&nbsp;<strong>Please make sure you have specified a base price for the product in the "Product Price" field above</strong>
<?php 
$variation_settings = "";
$variation_settings = apply_filters('eStore_product_variation_settings_filter', $variation_settings, $current_prod_id);
if(!empty($variation_settings)){//Show the advanced variation UI from the addon
	echo $variation_settings;
}
else{//Show the standard variation UI
?>
<table class="form-table">
<tr valign="top">
<th scope="row">Product Variation 1</th>
<td><textarea name="variation1" cols="83" rows="3"><?php echo $editingproduct->variation1; ?></textarea>
<br/><p class="description">Useful if you want to use variation with your product eg. Small, Medium, Large. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=345" target="_blank">Learn How To</a></p></td>
</tr>
<tr valign="top">
<th scope="row">Product Variation 2</th>
<td><textarea name="variation2" cols="83" rows="3"><?php echo $editingproduct->variation2; ?></textarea>
<br/><p class="description">Useful when adding additional variation with your product eg. Red, Green. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=345" target="_blank">Learn How To</a></p></td>
</tr>
<tr valign="top">
<th scope="row">Product Variation 3</th>
<td><textarea name="variation4" cols="83" rows="3"><?php echo $editingproduct->variation4; ?></textarea>
<br/><p class="description">Useful when adding additional variation with your product eg. Short, Full. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=345" target="_blank">Learn How To</a></p></td>
</tr>
<tr valign="top">
<th scope="row">Digital Product Variation</th>
<td><textarea name="variation3" cols="83" rows="3"><?php echo $editingproduct->variation3; ?></textarea>
<br/><p class="description">Can be used for digital delivery of different files depending on the selection (eg. Personal use, Business use). Please note that you need to enter a value (any URL value will do) in the "Digital Product URL" field to trigger the digital variation. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=345" target="_blank">Learn How To Use Digital Product Variation</a></p></td>
</tr>
</table>
<?php } ?>

</div>

<div class="msg_head">Shipping &amp; Tax (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row">Item Shipping Cost</th>
<td><input name="shippingcost" type="text" id="shippingcost" value="<?php echo $editingproduct->shipping_cost; ?>" size="3" />
<br/><p class="description">Enter the Shipping Cost for this item (eg. 5.00). Leave blank if shipping cost does not apply.</p></td>
</tr>
<tr valign="top">
<th scope="row">Item Weight</th>
<td><input name="itemweight" type="text" id="itemweight" value="<?php echo $editingproduct->weight; ?>" size="3" />
<br/><p class="description">Enter the Weight of the item in lbs. This is only used if you are using <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=50" target="_blank">PayPal profile based shipping</a>.</p></td>
</tr>
<tr valign="top">
<th scope="row">Item Specific Tax</th>
<td><input name="tax" type="text" id="tax" value="<?php echo $editingproduct->tax; ?>" size="3" />%
<br/><p class="description">If you want to charge a different tax for this item than the one specified in the settings menu then enter the tax rate for this item here.</p></td>
</tr>
</table>
</div>

<div class="msg_head">Inventory Control (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row">Available Copies</th>
<td><input name="availablecopies" type="text" id="availablecopies" value="<?php echo $editingproduct->available_copies; ?>" size="10" />
<br/><p class="description">Enter the numer of available copies (example: 50). Leave blank if unlimited. This is useful when you only want to sell only 50 copies of a product for example</p></td>
</tr>

<tr valign="top">
<th scope="row">Sales Count</th>
<td><input name="salescount" type="text" id="salescount" value="<?php echo $editingproduct->sales_count; ?>" size="10" />
<br/><p class="description">This is the total sales count. This number gets incremented by the quantity sold when you make a sale</p></td>
</tr>

<tr valign="top">
<th scope="row">Quantity Limit Per Customer</th>
<td><input name="per_customer_qty_limit" type="text" id="per_customer_qty_limit" value="<?php echo $editingproduct->per_customer_qty_limit; ?>" size="10" />
<br/><p class="description">If you want to limit the number of quantity a customer can purchase (example: 1) then enter that number here, otherwise leave this field empty.</p></td>
</tr>
</table>
</div>

<div class="msg_head">Serial Number/License Key Settings (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row">Your Codes</th>
<td>
<strong>Read the <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=1618" target="_blank">serial key feature documentation</a> to learn how to use this feature</strong>
<br /><br />
<?php 
if(isset($_GET['editproduct'])){
	$theid = $_GET['editproduct'];
	$editingproductmeta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$theid' AND meta_key='available_key_codes'", OBJECT);
	$available_key_codes = $editingproductmeta->meta_value;
}else{
	$available_key_codes = "";
}
?>
<textarea name="available_key_codes" cols="100" rows="7"><?php echo $available_key_codes; ?></textarea>
<br/><p class="description">Enter your Serial keys/License Keys/Ticket Numbers/Barcodes etc. separated by comma (,) in the above field. One key/number will be given to the customer after the purchase of this product.</p></td>
</tr>
</table>
</div>

<div class="msg_head">AddOn Settings (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row"></th><td><strong><i>Use the following section only if you are using the <a href="http://www.tipsandtricks-hq.com/wp-pdf-stamper-plugin-2332" target="_blank">WP PDF Stamper Plugin</a></i></strong></td>
</tr>
<tr valign="top">
<th scope="row">Stamp the PDF File</th>
<td><input type="checkbox" name="use_pdf_stamper" <?php if ($editingproduct->use_pdf_stamper=='1'){echo 'checked="checked"';} ?> />
<p class="description">If this product is an eBook and you want to stamp this PDF file with customer details upon purchase then check this option.</p></td>
</tr>

<tr valign="top">
<th scope="row"></th><td><strong><i>Use the following section only if you are using the <a href="http://www.tipsandtricks-hq.com/?p=1474" target="_blank">WP Affiliate Platform Plugin</a></i></strong></td>
</tr>
<tr valign="top">
<th scope="row">Author ID for Revenue Sharing</th>
<td><input name="author_id" type="text" id="author_id" value="<?php echo $editingproduct->author_id; ?>" size="10" /><br />
If you want to share revenue with the author of this product then enter the affiliate ID of this author in this field. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=930" target="_blnak">Read More Here</a></td>
</tr>

<tr valign="top">
<th scope="row">Revenue Sharing Commission Level</th>
<td><input name="rev_share_commission" type="text" id="rev_share_commission" value="<?php echo $editingproduct->rev_share_commission; ?>" size="4" /><br />
Example Value: 25. By default the commission level specified in the affiliate/author's profile will be used for revenue sharing amount calculation. However, you can choose to override the commission level for this product by specifying a value in the above field. <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=930" target="_blnak">Read More Here</a></td>
</tr>

<?php if (function_exists('wp_lic_manager_install')){ ?>
<tr valign="top">
<th scope="row"></th><td><strong><i>Use the following section only if you are using the <a href="http://www.tipsandtricks-hq.com" target="_blank">WP License Manager Plugin</a></i></strong></td>
</tr>
<tr valign="top">
<th scope="row">Create License</th>
<td><input type="checkbox" name="create_license" <?php if ($editingproduct->create_license=='1'){echo 'checked="checked"';} ?> />
<p class="description">If this product is a piece of software that has been integrated with the WP License Manage plugin then checking this box will create a license for the customer who purchase this product.</p></td>
</tr>
<?php } ?>
</table>
</div>

<div class="msg_head">Autoresponder Settings (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row">List Name</th>
<td><input name="aweber_list" type="text" id="aweber_list" value="<?php echo $editingproduct->aweber_list; ?>" size="100" /><br/>
<p class="description">The name of the list where the customers of this product will be signed up to (example: "listname@aweber.com" if you are using AWeber or "sample_marketing" if you are using GetResponse or "My Customers" if you are using MailChimp). You can find the list/campaign name inside your autoresponder account. Use this if you want the customer of this product to be signed up to a specific list.</p></td>
</tr>
</table>
</div>

<div class="msg_head">Product Specific Instructions for Buyer (Click to Expand)</div>
<div class="msg_body">
<table class="form-table">
<tr valign="top">
<th scope="row">Instructions for Buyer</th>
<td><textarea name="item_spec_instruction" cols="83" rows="3"><?php echo $editingproduct->item_spec_instruction; ?></textarea>
<br/><p class="description">This option is useful when you need to give your customer some specific instruction that applies only to this product (e.g. a secret password for the PDF file). This instruction will be added to the buyer's email body when this product is purchased. Use the {product_specific_instructions} tag in the "Buyers Email Body" field in the settings menu to dynamically place this information in the email body.</p></td>
</tr>
</table>
</div>

<div class="msg_head">Buy Now, Subscription or Donation Type Button Specific Settings (Click to Expand)</div>
<div class="msg_body">
<p style="color:red">
<strong>The options in this section are only used for "Buy Now", "Subscription" or "Donation" type buttons. <a href="http://www.tipsandtricks-hq.com/forum/topic/different-types-of-payment-buttons-and-their-behaviour" target="_blank">Explanation on the different types of payment buttons</a></strong>
</p>
<br />This can be useful when you want to use a different setting than the one specified in the Settings menu for this product. For example you might be using USD for your store but you may want to create a subscription button in Euro for one product. 
<br /><br />
<table class="form-table">
<tr valign="top">
<th scope="row">Return URL</th>
<td><input name="returnurl" type="text" id="returnurl" value="<?php if ($editingproduct->return_url!='') { echo $editingproduct->return_url; } else { echo ''; } ?>" size="50" />
<br/><p class="description">Can be used to redirect customers to a different URL for this item after a successful payment</p></td>
</tr>
<tr valign="top">
<th scope="row">PayPal Email</th>
<td><input name="paypal_email" type="text" id="paypal_email" value="<?php if ($editingproduct->paypal_email!='') { echo $editingproduct->paypal_email; } else { echo ''; } ?>" size="50" />
<br/><p class="description">This is useful when you want to allow other blog authors to sell their products on your blog and the product owner gets the money directly into his/her PayPal account</p></td>
</tr>
<tr valign="top">
<th scope="row">Currency Code</th>
<td><input name="currency_code" type="text" id="currency_code" value="<?php if ($editingproduct->currency_code!='') { echo $editingproduct->currency_code; } else { echo ''; } ?>" size="6" />
<br/><p class="description">This is useful when you want to sell a specific product in a different currency than the one specified in the settings menu. (e.g. EUR, GBP, AUD, USD) </p></td>
</tr>
</table>
</div>

<div class="msg_head">Subscription/Recurring Payment Specific Settings (Click to Expand)</div>
<div class="msg_body">
<p>
<strong>Make sure to read the <a href="http://www.tipsandtricks-hq.com/ecommerce/how-to-add-a-subscription-button-for-recurring-payment-400" target="_blank">subscription product creation documentation</a> (there is a video tutorial too)</strong>
</p>

<div class="postbox">
<h3><label for="title">Trial Period (Leave Empty if you are not offfering a Trial Period)</label></h3>
<div class="inside">
<table class="form-table">
<tr valign="top">
<th scope="row">Trial Billing Amount</th>
<td><input name="a1" type="text" id="a1" value="<?php echo $editingproduct->a1; ?>" size="10" />
<br/><p class="description">Amount to be charged for the Trail period. Enter 0 if you want to offer a free trial period</p></td>
</tr>
<tr valign="top">
<th scope="row">Trial Billing Period</th>
<td><input name="p1" type="text" id="p1" value="<?php echo $editingproduct->p1; ?>" size="5" />
		<select name='t1'>
		<option value='D' <?php if($editingproduct->t1=='D')echo 'selected="selected"';?>>Day</option>
		<option value='M' <?php if($editingproduct->t1=='M')echo 'selected="selected"';?>>Month</option>
		<option value='Y' <?php if($editingproduct->t1=='Y')echo 'selected="selected"';?>>Year</option>
		</select>
<br/><p class="description">Length of the Trial Period</p></td>
</tr>
</table>
</div></div>

	<div class="postbox">
	<h3><label for="title">Recurring Billing</label></h3>
	<div class="inside">
<table class="form-table">
<tr valign="top">
<th scope="row">Recurring Billing Amount</th>
<td><input name="a3" type="text" id="a3" value="<?php echo $editingproduct->a3; ?>" size="10" />
<br/><p class="description">Amount to be charged on every billing cycle. If used with a trial period then this amount will be charged after the trial period is over</p></td>
</tr>

<tr valign="top">
<th scope="row">Recurring Billing Cycle</th>
<td><input name="p3" type="text" id="p3" value="<?php echo $editingproduct->p3; ?>" size="5" />
		<select name='t3'>
		<option value='D' <?php if($editingproduct->t3=='D')echo 'selected="selected"';?>>Day</option>
		<option value='M' <?php if($editingproduct->t3=='M')echo 'selected="selected"';?>>Month</option>
		<option value='Y' <?php if($editingproduct->t3=='Y')echo 'selected="selected"';?>>Year</option>
		</select>
</tr>
<tr valign="top">
<th scope="row">Recurring Billing Count</th>
<td><input name="srt" type="text" id="srt" value="<?php echo $editingproduct->srt; ?>" size="5" />
<br/><p class="description">This is the number of payments which will occur at the regular rate. Leave this field empty (or enter 0) if you want the payment to continue to recur at the regular rate until the subscription is canceled. Enter -1 if you want to configure a once off payment.</p></td>
</tr>
<tr valign="top">
<th scope="row">Reattempt on failure</th>
<td><input type="checkbox" name="sra" <?php if ($editingproduct->sra=='1'){echo 'checked="checked"';} ?> />
<p class="description">When checked, the payment will be reattempted two more times if the payment fails. After the third failure, the subscription will be cancelled.</p></td>
</tr>
</table>
</div></div>
</div>

<?php 
if(!isset($_GET['editproduct'])){$_GET['editproduct']="";}
$additional_addon_settings = "";
$additional_addon_settings = apply_filters('eStore_addon_product_settings_filter', $additional_addon_settings, $_GET['editproduct']);
echo $additional_addon_settings;
?>
<p class="submit"><input type="submit" class="button-primary" name="Submit" value="Save Product" /></p>

</form>

<?php
if (isset($_GET['editproduct']) && $_GET['editproduct']!='') {//Show delete product link
echo "<form method=\"post\" action=\"admin.php?page=wp_eStore_addedit\" onSubmit=\"return confirm('Are you sure you want to delete this entry?');\">";
echo "<input type=\"hidden\" name=\"prod_id\" value=".$_GET['editproduct']." />";
echo '<input type="submit" style="border: none; background-color:transparent; padding:0; cursor:pointer; color:red;text-decoration:underline" name="deleteproduct" value="Delete Product">';
echo "</form>";
}
?>

</div></div>

<div class="postbox">
<h3><label for="title">Copy Product Details from an Existing Product</label></h3>
<div class="inside">

To copy the details from an existing product simply enter the ID of the product whose details you wish to copy and hit the "Copy Product Details" button
<br /><br />
<form method="post" action="admin.php?page=wp_eStore_addedit">
Product ID:
<input name="orig_product_id" type="text" id="orig_product_id" value="" size="5" />
<input type="submit" name="copy_product" class="button" value="Copy Product Details" />
</form>
</div>
</div>


<?php

    echo 'Want to bulk upload product details from CSV file? <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=775" target="_blank">Click Here to Learn More</a><br /><br />';
    echo '<div class="button-group">';
    echo '<a href="admin.php?page=wp-cart-for-digital-products/wp_eStore1.php" class="button">Manage Products</a>&nbsp;&nbsp;';
    echo '<a href="admin.php?page=wp_eStore_addedit" class="button">Create New Product</a>';
    echo '</div>';

    if(!empty($current_prod_id))
    {
        $previous_prod = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id < '$current_prod_id' ORDER BY id DESC", OBJECT);
        if($previous_prod){
            echo '<a href="admin.php?page=wp_eStore_addedit&editproduct='.$previous_prod->id.'" class="button rbutton">&laquo; Previous Product</a>&nbsp;&nbsp;';
        }
        $next_prod = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id > '$current_prod_id' ORDER BY id ASC", OBJECT);
        if($next_prod){
            echo '<a href="admin.php?page=wp_eStore_addedit&editproduct='.$next_prod->id.'" class="button rbutton">Next Product &raquo;</a><br /><br />';
        }
    }
    echo '</div></div>';
    echo '</div>';//End of wrap
}

function wp_eStore_delete_product_data($product_id)
{
    global $wpdb;
	global $products_table_name;
	global $cat_prod_rel_table_name;
	$product_meta_table_name = WP_ESTORE_PRODUCTS_META_TABLE_NAME;
	
	$updatedb = "DELETE FROM $products_table_name WHERE id='$product_id'";
	$results = $wpdb->query($updatedb);

	$updatedb = "DELETE FROM $cat_prod_rel_table_name WHERE prod_id='$product_id'";
	$resultsTwo = $wpdb->query($updatedb);		

	$del_meta_db_val = "DELETE FROM $product_meta_table_name WHERE prod_id='$product_id' AND meta_key='available_key_codes'";
	$resultsThree = $wpdb->query($del_meta_db_val);
        
        $del_meta_db_val = "DELETE FROM $product_meta_table_name WHERE prod_id='$product_id' AND meta_key='download_limit_time'";
	$resultsThree = $wpdb->query($del_meta_db_val);
        
        $del_meta_db_val = "DELETE FROM $product_meta_table_name WHERE prod_id='$product_id' AND meta_key='download_limit_count'";
	$resultsThree = $wpdb->query($del_meta_db_val);
	
	do_action('eStore_product_deleted',$product_id);
    if($results>0){
        return true;
    }
    else{
        return false;
    }	
}
?>