<?php
function eStore_admin_css()
{
	if(is_admin()){
		echo '<link type="text/css" rel="stylesheet" href="'.WP_ESTORE_LIB_URL.'/eStore_admin_style.css" />';		
	}
    ?>
    <style type="text/css">
    .msg_head {    
    cursor: pointer;
    position: relative;
    font-size: 12px;
    font-weight: bold;
    color:#21759B;
    padding: 10px 10px 10px 30px;
    margin-top:10px;
    background-color:#E2E2E2;
    border:1px solid #DAD9D8;
    background-image: url('<?php echo WP_ESTORE_URL; ?>/images/estore_plus_icon.png');
    background-repeat: no-repeat;
    background-position: 5px 7px;
    }
    .msg_head:hover{
    background-color:#D6D6D6;
    }
    .msg_body {
    padding: 5px 10px 15px;
    background-color:#F4F4F8;
    border:1px solid #C2C2CF;
    }
    .section_head {
    padding: 5px 10px;
    position: relative;
    font-size: 12px;
    margin-bottom: 15px;
    background-color:#E9E9E9;
    border:1px solid #D5D8D9;
    }
    </style>
    <?php
}
function eStore_admin_submenu_css()
{
?>
<style type="text/css">
.eStoreSubMenu{border:1px solid #DDDDDD;font-size:12px;font-weight:bold;line-height: 24px;margin-bottom:15px;}
.eStoreSubMenuItem{float:left;}
.eStoreSubMenuItem a{display: block;padding: 0.818em 0.909em;text-decoration:none;}
.eStoreSubMenu .current{background-color:#EDEDED;}
</style>
<?php
}

function eStore_admin_js_scripts()
{
$output = '
<script type="text/javascript">
jQuery(document).ready(function($) {
$(function() {	
      //hide the all of the element with class msg_body
      $(".msg_body").hide();
      //toggle the componenet with class msg_body
      $(".msg_head").click(function()
      {
        $(this).next(".msg_body").animate({ "height": "toggle"});
      });
      
      $(".eStore_more_info_body").hide();//hide the more info body area
      $(".eStore_more_info_anchor").click(function()
      {
        $(this).next(".eStore_more_info_body").animate({ "height": "toggle"});
      });      
});      
});
</script>';
return $output;
}

function eStore_admin_datepicker_js()
{
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $(function() {
        var imgPath = '<?php echo get_option('siteurl').'/wp-content/plugins/'.
        dirname(plugin_basename(__FILE__)) . '/images/' ;?>';
		jQuery('#stat_start_date').datepicker({dateFormat: 'yy-mm-dd' ,showOn: 'button', buttonImage: imgPath+'calendar.gif', buttonImageOnly: true}).dateEntry({dateFormat: 'ymd-',spinnerImage: '', useMouseWheel:false});
		jQuery('#stat_end_date').datepicker({dateFormat: 'yy-mm-dd' ,showOn: 'button', buttonImage: imgPath+'calendar.gif', buttonImageOnly: true}).dateEntry({dateFormat: 'ymd-',spinnerImage: '', useMouseWheel:false});
    });
});
</script>	
<?php 
}

function wp_eStore_load_date_entry_js()
{
    return '<script type="text/javascript" src="'.WP_ESTORE_URL.'/lib/jquery.dateentry.pack.js"></script>';
}

function wp_eStore_show_file_upload_more_info()
{
?>
<span class="eStore_more_info_anchor"> [+] more info<br /></span>
<div class="eStore_more_info_body" style="color:#666666;">
<p>Uploading a file and using it is a 3 step process:</p>
<ol>
<li><i>Click the upload button</i></li> 
<li><i>Choose the file to upload which will upload that file to your media library</i></li> 
<li><i>Finally, click the <strong>Insert into Post</strong> button, this will populate the uploaded file's URL in the correct field.</i></li> 
</ol>
</div>	
<?php	
}

function wp_eStore_reset_settings_to_default()
{
		/**********************************/
		// === General settings ===
		/**********************************/
        update_option('eStore_cart_language', 'eng.php');
        update_option('cart_payment_currency', 'USD');
        update_option('cart_currency_symbol', '$');
        update_option('eStore_variation_add_symbol', '+');      
        update_option('eStore_products_per_page', '20');
        update_option('addToCartButtonName', 'Add to Cart');
        update_option('soldOutImage', WP_ESTORE_URL.'/images/sold_out.png');
        update_option('wp_eStore_widget_title', 'Shopping Cart');
        update_option('wp_cart_title', 'Items in Your Cart');
        update_option('wp_cart_empty_text', 'Your cart is empty');
        update_option('cart_return_from_paypal_url', '');
        update_option('cart_cancel_from_paypal_url', '');
        update_option('eStore_products_page_url', '');
        update_option('eStore_display_continue_shopping', '' );
        update_option('eStore_checkout_page_url', '');
        update_option('eStore_auto_checkout_redirection', '' );
        update_option('eStore_auto_cart_anchor', 'checked="checked"');
        update_option('eStore_shopping_cart_image_hide', '' );
        update_option('eStore_show_t_c', '' );
        update_option('eStore_show_t_c_for_buy_now', '' );
        update_option('eStore_show_t_c_for_squeeze_form', '' );
        update_option('eStore_t_c_url', ''); 
        update_option('eStore_show_compact_cart', '' ); 
        update_option('eStore_enable_fancy_redirection_on_checkout', '' );              
        update_option('eStore_enable_lightbox_effect', 'checked="checked"');
        update_option('eStore_enable_smart_thumb', '' );
                                           
        update_option('eStore_base_shipping', '');
        update_option('eStore_shipping_variation', '');
        update_option('eStore_always_display_shipping_variation', '' );
        update_option('eStore_enable_tax', '' );
        update_option('eStore_global_tax_rate', '');
        
        update_option('eStore_secondary_currency_code', '');
        update_option('eStore_secondary_currency_symbol', '');
        update_option('eStore_secondary_currency_conversion_rate', '');
        
        update_option('eStore_random_code', 'Uk3jshb#[G8973&g2I8RPjs8dy7');
        update_option('eStore_download_url_life', '24');
        update_option('eStore_download_url_limit_count', '');
        //update_option('eStore_download_enable_ip_address_lock', '' );
        update_option('eStore_download_script', WP_ESTORE_URL."/");
        
        update_option('eStore_ppv_verification_failed_url', '');       
        
        update_option('eStore_auto_product_delivery', 'checked="checked"');
        update_option('eStore_display_tx_result', '' );       
        update_option('eStore_strict_email_check', '' );
        update_option('eStore_auto_customer_removal', '' );

        update_option('eStore_use_wp_mail', 'checked="checked"');
        update_option('eStore_send_buyer_email', 'checked="checked"');
        update_option('eStore_download_email_address', get_bloginfo('name')." <sales@your-domain.com>");
        update_option('eStore_buyer_email_subj', "Thank you for the purchase");
		$eStore_buyer_email_body = "Dear {first_name} {last_name}".
			  "\n\nThank you for your purchase!".
			  "\n{product_details}".
			  "\n\nAny items to be shipped will be processed as soon as possible, any items that can be downloaded can be downloaded using the encrypted links below.".
			  "\n{product_link}".
			  "\n\nThanks";        
        update_option('eStore_buyer_email_body', $eStore_buyer_email_body);
        update_option('eStore_notify_email_address', get_bloginfo('admin_email'));
        update_option('eStore_seller_email_subj', "Notification of product sale");
		$eStore_seller_email_body = "Dear Seller".
				"\n\nThis mail is to notify you of a product sale. Product Name: {product_name} Product ID: {product_id}".
				"\nThe sale was made to {first_name} {last_name} ({payer_email})".
				"\n\nThanks";        
        update_option('eStore_seller_email_body', $eStore_seller_email_body);        
        update_option('eStore_cart_enable_debug', '');
        update_option('eStore_cart_enable_sandbox', '');
			
        /**********************************/
		// === Payment gateway settings ===
		/**********************************/
        update_option('eStore_use_multiple_gateways','' );
        update_option('eStore_use_manual_gateway_for_zero_dollar_co','' );
        
        update_option('eStore_use_paypal_gateway','1' );
        update_option('cart_paypal_email', get_bloginfo('admin_email'));
        update_option('eStore_paypal_profile_shipping','' );
        update_option('eStore_paypal_return_button_text','');
        update_option('eStore_paypal_co_page_style', '');
        update_option('eStore_paypal_pdt_token','');          
        
        update_option('eStore_use_manual_gateway', '' );
        update_option('eStore_manual_notify_email', '');
        update_option('eStore_manual_co_cust_direction','');
        update_option('eStore_manual_return_url', '');
        update_option('eStore_manual_co_give_aff_commission', '' );
        update_option('eStore_manual_co_auto_update_db', '' );   
        update_option('eStore_manual_co_do_autoresponder_signup', '' );          
        update_option('eStore_manual_co_give_download_links', '' );

        update_option('eStore_use_2co_gateway', '' );
        update_option('eStore_2co_vendor_id', '');
        update_option('eStore_2co_secret_word', '');

        update_option('eStore_use_authorize_gateway', '' );
        update_option('eStore_authorize_login', '');
        update_option('eStore_authorize_tx_key', '');

        /**********************************/
        // === Autoresponder settings === 
        /**********************************/
        update_option('eStore_enable_aweber_int', '' );
        update_option('eStore_aweber_list_name', '');
        
        update_option('eStore_use_mailchimp', '' );
        update_option('eStore_enable_global_chimp_int', '' );
        update_option('eStore_chimp_list_name', '');
        update_option('eStore_chimp_api_key', '');
        //update_option('eStore_chimp_user_name', '');
        //update_option('eStore_chimp_pass', '');

        update_option('eStore_use_getResponse', '' );
        update_option('eStore_enable_global_getResponse_int', '' );
        update_option('eStore_getResponse_campaign_name', '');
        update_option('eStore_getResponse_api_key', '');
        
        //update_option('eStore_enable_infusionsoft_int', '');
        //update_option('eStore_infusionsoft_group_number', '');  

        /**********************************/
        // === Addon settings ===
        /**********************************/        
        update_option('eStore_aff_allow_aff_id', '' );
        update_option('eStore_aff_link_coupon_aff_id', '' ); 
        update_option('eStore_aff_one_time_commission', '' ); 
        update_option('eStore_aff_enable_revenue_sharing', '' ); 
        update_option('eStore_aff_enable_lead_capture_for_sqeeze_form', '' );
        update_option('eStore_create_auto_affiliate_account', '' );
        update_option('eStore_aff_enable_commission_per_transaction', '' ); 
        update_option('eStore_eMember_must_be_logged_to_checkout', '' );
        update_option('eStore_eMember_redirection_url_when_not_logged', '');        
		update_option('eStore_auto_convert_to_relative_url', '0');
		update_option('eStore_download_method', '1');        
        update_option('eStore_lic_mgr_post_url', '');
        update_option('eStore_lic_mgr_secret_word', '');
        
        /**********************************/
        // === Third party settings ===
        /**********************************/
        update_option('eStore_enable_wishlist_int', '' );
        update_option('eStore_wishlist_post_url', '');
        update_option('eStore_wishlist_secret_word', '');        
        update_option('eStore_ngg_template_product_id', '');   
        update_option('eStore_enable_analytics_tracking', '' );        
        update_option('eStore_memberwing_ipn_post_url', '');         

        /**********************************/
        // === Misc settings ===
        /**********************************/        
        update_option("eStore_manage_products_limit2", 50);
}

function wp_eStore_reset_sales_data()
{
	global $wpdb;
	$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
	$sales_table_name = $wpdb->prefix . "wp_eStore_sales_tbl";
	$pending_payment_table_name = $wpdb->prefix . "wp_eStore_pending_payment_tbl";
	
	$updatedb = "TRUNCATE $customer_table_name";
	$results = $wpdb->query($updatedb);	
	$updatedb = "TRUNCATE $sales_table_name";
	$results = $wpdb->query($updatedb);	
	$updatedb = "TRUNCATE $pending_payment_table_name";
	$results = $wpdb->query($updatedb);
}

function wp_eStore_reset_product_sales_counter()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "wp_eStore_tbl";	
	$updatedb = "UPDATE $table_name set sales_count=0";
	$results = $wpdb->query($updatedb);		
}

function wp_eStore_reset_receipt_counter()
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $wp_eStore_config->setValue('eStore_custom_receipt_counter', 0);
    $wp_eStore_config->saveConfig();
}

function wp_eStore_refresh_product_category_relation_tbl()
{
	global $wpdb;
	$products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
	$cat_prod_rel_table_name = WP_ESTORE_CATEGORY_RELATIONS_TABLE_NAME;
	$relations_resultset = $wpdb->get_results("SELECT * FROM $cat_prod_rel_table_name ORDER BY cat_id ASC", OBJECT);
	if(!$relations_resultset){//Nothing to do
		return;
	}
	
	foreach($relations_resultset as $row){
		$prod_id = $row->prod_id;
		$ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$prod_id'", OBJECT);
		if(!$ret_product){//This product does not exist so delete this entry
			$del_query = "DELETE FROM $cat_prod_rel_table_name WHERE prod_id='$prod_id'";
			$results = $wpdb->query($del_query);	
		}
	}
}

function wp_eStore_remove_all_db_tables()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "wp_eStore_tbl";
	$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
	$coupon_table_name = $wpdb->prefix . "wp_eStore_coupon_tbl";
	$sales_table_name = $wpdb->prefix . "wp_eStore_sales_tbl";
	$cat_prod_rel_table_name = $wpdb->prefix . "wp_eStore_cat_prod_rel_tbl";
	$cat_table_name = $wpdb->prefix . "wp_eStore_cat_tbl";
	$pending_payment_table_name = $wpdb->prefix . "wp_eStore_pending_payment_tbl";
	$download_links_table_name = $wpdb->prefix . "wp_eStore_download_links_tbl";
	$save_cart_table_name = $wpdb->prefix . "wp_eStore_save_cart_tbl";
	$product_meta_table_name = $wpdb->prefix . "wp_eStore_products_meta_tbl";
	$global_meta_table_name = $wpdb->prefix . "wp_eStore_meta_tbl";
		
	$updatedb = "DROP TABLE $table_name";
	$results = $wpdb->query($updatedb);	
	$updatedb = "DROP TABLE $customer_table_name";
	$results = $wpdb->query($updatedb);	
	$updatedb = "DROP TABLE $coupon_table_name";
	$results = $wpdb->query($updatedb);	
	$updatedb = "DROP TABLE $sales_table_name";
	$results = $wpdb->query($updatedb);		
	$updatedb = "DROP TABLE $cat_prod_rel_table_name";
	$results = $wpdb->query($updatedb);		
	$updatedb = "DROP TABLE $cat_table_name";
	$results = $wpdb->query($updatedb);		
	$updatedb = "DROP TABLE $pending_payment_table_name";
	$results = $wpdb->query($updatedb);
	$updatedb = "DROP TABLE $download_links_table_name";
	$results = $wpdb->query($updatedb);	
	$updatedb = "DROP TABLE $save_cart_table_name";
	$results = $wpdb->query($updatedb);
	$updatedb = "DROP TABLE $product_meta_table_name";
	$results = $wpdb->query($updatedb);	
	$updatedb = "DROP TABLE $global_meta_table_name";
	$results = $wpdb->query($updatedb);			
}
