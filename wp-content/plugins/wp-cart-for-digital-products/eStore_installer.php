<?php
//***** Installer *****/
function wp_eStore_run_activation()
{	
	global $wpdb;
    if (function_exists('is_multisite') && is_multisite()) {
    	// check if it is a network activation - if so, run the activation function for each blog id
    	if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
                    $old_blog = $wpdb->blogid;
    		// Get all blog ids
    		$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    		foreach ($blogids as $blog_id) {
    			switch_to_blog($blog_id);
    			wp_eStore_run_installer();
    		}
    		switch_to_blog($old_blog);
    		return;
    	}	
    } 
    wp_eStore_run_installer();
}

function wp_eStore_run_installer()
{
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');	
	//***Installer variables***/
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
	
	$wp_eStore_db_version = "8.6";//change the value of "WP_ESTORE_DB_VERSION" if needed.
	//***Installer***/
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
	{
	$sql = "CREATE TABLE " . $table_name . " (
		  id int(12) NOT NULL auto_increment,
		  name text NOT NULL,
		  price varchar(128) NOT NULL,
		  product_download_url text NOT NULL,
		  downloadable text NOT NULL,
		  shipping_cost varchar(128) NOT NULL,
		  available_copies varchar(128) NOT NULL,
		  button_image_url text NOT NULL,
		  return_url text NOT NULL,
		  sales_count int(12) NOT NULL,
		  description text NOT NULL,
		  thumbnail_url text NOT NULL,
		  variation1 text NOT NULL,
		  variation2 text NOT NULL,
		  variation3 text NOT NULL,
		  commission varchar(10) NOT NULL default '',
		  a1 varchar(128) NOT NULL,
		  p1 int(12) NOT NULL,
		  t1 varchar(8) NOT NULL,
		  a3 varchar(128) NOT NULL,
		  p3 int(12) NOT NULL,
		  t3 varchar(8) NOT NULL,
		  sra SMALLINT NOT NULL,
		  srt SMALLINT NOT NULL,
		  ref_text varchar(255) NOT NULL,
		  paypal_email varchar(128) NOT NULL,
		  custom_input SMALLINT NOT NULL,
		  custom_input_label varchar(128) NOT NULL,
		  variation4 text NOT NULL,
		  aweber_list varchar(255) NOT NULL,
		  currency_code varchar(8) NOT NULL,
		  target_thumb_url varchar(255) NOT NULL,
		  target_button_url varchar(255) NOT NULL,
		  weight varchar(6) NOT NULL,
		  product_url varchar(255) NOT NULL,
		  item_spec_instruction text NOT NULL,
		  ppv_content SMALLINT NOT NULL,
		  use_pdf_stamper SMALLINT NOT NULL,
		  create_license SMALLINT NOT NULL,
		  tax varchar(6) NOT NULL,
		  author_id varchar(30) NOT NULL default '',
		  show_qty SMALLINT NOT NULL,
		  tier2_commission varchar(10) NOT NULL default '',
		  custom_price_option SMALLINT NOT NULL,
		  additional_images text NOT NULL,
		  old_price varchar(128) NOT NULL,
		  rev_share_commission varchar(10) NOT NULL default '',
		  per_customer_qty_limit varchar(128) NOT NULL,
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	dbDelta($sql);
	
	add_option("wp_eStore_db_version", $wp_eStore_db_version);
	}
	// Install the customer DB
	if($wpdb->get_var("SHOW TABLES LIKE '$customer_table_name'") != $customer_table_name)
	{
	$sql = "CREATE TABLE " . $customer_table_name . " (
		  id int(12) NOT NULL auto_increment,
		  first_name text NOT NULL,
		  last_name text NOT NULL,
		  email_address text NOT NULL,
		  purchased_product_id int(12) NOT NULL,
	      txn_id varchar(64) NOT NULL default '',
	      date date NOT NULL default '0000-00-00',
	      sale_amount varchar(10) NOT NULL default '',      
	      coupon_code_used varchar(64) NOT NULL default '',
	      member_username varchar(32) NOT NULL,
	      product_name varchar(255) NOT NULL,
		  address text NOT NULL,	  
		  phone varchar(32) NOT NULL,
		  subscr_id varchar(64) DEFAULT '',
		  purchase_qty varchar(64) NOT NULL,
		  ipaddress varchar(50) default 'No information',
		  status varchar(50) default 'Paid',
		  serial_number text NOT NULL,
		  notes text NOT NULL,
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	dbDelta($sql);
	
	// Add default options
	add_option("wp_eStore_db2_version", $wp_eStore_db_version);
	}
	// Install the Coupons/Discounts DB
	if($wpdb->get_var("SHOW TABLES LIKE '$coupon_table_name'") != $coupon_table_name)
	{
	$sql = "CREATE TABLE " . $coupon_table_name . " (
		  id int(12) NOT NULL auto_increment,
		  coupon_code text NOT NULL,
		  discount_value varchar(128) NOT NULL,
		  discount_type text NOT NULL,
		  active text NOT NULL,
		  redemption_limit varchar(12) NOT NULL,
		  redemption_count varchar(12) NOT NULL,
		  property varchar(8) NOT NULL,
		  logic varchar(8) NOT NULL,
		  value varchar(128) NOT NULL,
		  expiry_date date NOT NULL default '0000-00-00',
		  dynamic varchar(32) NOT NULL,
		  start_date date NOT NULL default '0000-00-00',
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	dbDelta($sql);
	
	// Add default options
	add_option("wp_eStore_db3_version", $wp_eStore_db_version);
	}
	
	// Install the Sales DB
	if($wpdb->get_var("SHOW TABLES LIKE '$sales_table_name'") != $sales_table_name)
	{
	$sql = "CREATE TABLE " . $sales_table_name . " (
	    cust_email varchar(128) NOT NULL default '',
	    date date NOT NULL default '0000-00-00',
	    time time NOT NULL default '00:00:00',
	    item_id varchar(10) NOT NULL default '',
	    sale_price varchar(10) NOT NULL default ''
		);";
	dbDelta($sql);
	// Add default options
	add_option("wp_eStore_db4_version", $wp_eStore_db_version);
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '$cat_prod_rel_table_name'") != $cat_prod_rel_table_name)
	{
	$sql = "CREATE TABLE " . $cat_prod_rel_table_name . " (
	    cat_id int(12) NOT NULL,
	    prod_id int(12) NOT NULL,
	    PRIMARY KEY  (cat_id, prod_id)
		);";
	dbDelta($sql);
	// Add default options
	add_option("wp_eStore_db5_version", $wp_eStore_db_version);
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '$cat_table_name'") != $cat_table_name)
	{
	$sql = "CREATE TABLE " . $cat_table_name . " (
	    cat_id int(12) NOT NULL auto_increment,
	    cat_name varchar(64) NOT NULL default 'Uncategorized',
	    cat_desc text NOT NULL,
	    cat_parent int(12) NOT NULL,
	    cat_image varchar(255) NOT NULL,
	    cat_url varchar(255) NOT NULL,
	    PRIMARY KEY  (cat_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	dbDelta($sql);
	// Add default options
	add_option("wp_eStore_db6_version", $wp_eStore_db_version);
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '$pending_payment_table_name'") != $pending_payment_table_name)
	{
	$sql = "CREATE TABLE " . $pending_payment_table_name . " (
		customer_id varchar(64) NOT NULL,
		item_number int(12) NOT NULL,
		name varchar(255) NOT NULL,
		price varchar(128) NOT NULL,
		quantity int(12) NOT NULL,
		shipping varchar(128) NOT NULL,
		custom varchar(255) NOT NULL,
		total_shipping varchar(128) NOT NULL,
		total_tax varchar(128) NOT NULL,
		subtotal varchar(128) NOT NULL
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	dbDelta($sql);
	// Add default options
	add_option("wp_eStore_db7_version", $wp_eStore_db_version);
	}
	// Install the Download Links DB
	if($wpdb->get_var("SHOW TABLES LIKE '$download_links_table_name'") != $download_links_table_name)
	{
	$sql = "CREATE TABLE " . $download_links_table_name . " (
		  id bigint(20) unsigned NOT NULL auto_increment,
		  creation_time datetime NOT NULL default '0000-00-00 00:00:00',
		  download_key varchar(255) NOT NULL default '',
		  download_item text NOT NULL,
		  download_limit_count int(12) NOT NULL default '3',
	      download_limit_time int(12) NOT NULL default '48',
	      download_limit_ip varchar(15) NOT NULL default '0.0.0.0',
	      access_count int(12) NOT NULL default '0',
	      txn_id varchar(64) NOT NULL default '',
              user_id varchar(128) NOT NULL default '',
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	dbDelta($sql);
	
	// Add default options
	add_option("wp_eStore_db8_version", $wp_eStore_db_version);
	}
	
	// Install the Save Cart DB
	if($wpdb->get_var("SHOW TABLES LIKE '$save_cart_table_name'") != $save_cart_table_name)
	{
		$sql = "CREATE TABLE " . $save_cart_table_name . " (
		cart_id varchar(64) NOT NULL,
		serialized_eStore_cart text NOT NULL,
                serialized_estore_customer text NOT NULL,
		KEY cart_id  (cart_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		dbDelta($sql);
	
		// Add default options
		add_option("wp_eStore_db9_version", $wp_eStore_db_version);
	}

	// Install the products meta DB
	if($wpdb->get_var("SHOW TABLES LIKE '$product_meta_table_name'") != $product_meta_table_name)
	{
		$sql = "CREATE TABLE " . $product_meta_table_name . " (
		meta_id int(12) NOT NULL auto_increment,
		prod_id int(12) NOT NULL,
		meta_key varchar(255) NOT NULL,
		meta_value text NOT NULL,
		PRIMARY KEY  (meta_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		dbDelta($sql);
		// Add default options
		add_option("wp_eStore_db10_version", $wp_eStore_db_version);
	}	
	
	if($wpdb->get_var("SHOW TABLES LIKE '$global_meta_table_name'") != $global_meta_table_name)
	{
		$sql = "CREATE TABLE " . $global_meta_table_name . " (
		meta_id int(12) NOT NULL auto_increment,
		tbl_reference int(12) NOT NULL,
		date_time datetime NOT NULL default '0000-00-00 00:00:00',
		meta_key1 varchar(255) NOT NULL,
		meta_key2 varchar(255) NOT NULL,
		meta_key3 varchar(255) NOT NULL,
		meta_key4 varchar(255) NOT NULL,
		meta_key5 varchar(255) NOT NULL,
		meta_value1 varchar(255) NOT NULL,
		meta_value2 varchar(255) NOT NULL,
		meta_value3 text NOT NULL,
		meta_value4 text NOT NULL,
		meta_value5 text NOT NULL,
		PRIMARY KEY  (meta_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		dbDelta($sql);
		// Add default options
		add_option("wp_eStore_db11_version", $wp_eStore_db_version);
	}	
	//**********************************
	//********** Upgrader **************
	//***********************************/
	
	$installed_ver = get_option( "wp_eStore_db_version" );
	if( $installed_ver != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $table_name . " (
		  id int(12) NOT NULL auto_increment,
		  name text NOT NULL,
		  price varchar(128) NOT NULL,
		  product_download_url text NOT NULL,
		  downloadable text NOT NULL,
		  shipping_cost varchar(128) NOT NULL,
		  available_copies varchar(128) NOT NULL,
		  button_image_url text NOT NULL,
		  return_url text NOT NULL,
		  sales_count int(12) NOT NULL,
		  description text NOT NULL,
		  thumbnail_url text NOT NULL,
		  variation1 text NOT NULL,
		  variation2 text NOT NULL,
		  variation3 text NOT NULL,
		  commission varchar(10) NOT NULL default '',
		  a1 varchar(128) NOT NULL,
		  p1 int(12) NOT NULL,
		  t1 varchar(8) NOT NULL,
		  a3 varchar(128) NOT NULL,
		  p3 int(12) NOT NULL,
		  t3 varchar(8) NOT NULL,
		  sra SMALLINT NOT NULL,
		  srt SMALLINT NOT NULL,
		  ref_text varchar(255) NOT NULL,
		  paypal_email varchar(128) NOT NULL,
		  custom_input SMALLINT NOT NULL,
		  custom_input_label varchar(128) NOT NULL,
		  variation4 text NOT NULL,
		  aweber_list varchar(255) NOT NULL,
		  currency_code varchar(8) NOT NULL,
		  target_thumb_url varchar(255) NOT NULL,
		  target_button_url varchar(255) NOT NULL,
		  weight varchar(6) NOT NULL,
		  product_url varchar(255) NOT NULL,
		  item_spec_instruction text NOT NULL,
		  ppv_content SMALLINT NOT NULL,
		  use_pdf_stamper SMALLINT NOT NULL,
		  create_license SMALLINT NOT NULL,
		  tax varchar(6) NOT NULL,
		  author_id varchar(30) NOT NULL default '',
		  show_qty SMALLINT NOT NULL,
		  tier2_commission varchar(10) NOT NULL default '',
		  custom_price_option SMALLINT NOT NULL,
		  additional_images text NOT NULL,
		  old_price varchar(128) NOT NULL,
		  rev_share_commission varchar(10) NOT NULL default '',
		  per_customer_qty_limit varchar(128) NOT NULL,
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$table_name);
	
		update_option("wp_eStore_db_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db2 = get_option( "wp_eStore_db2_version" );
	if( $installed_ver_db2 != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $customer_table_name . " (
		  id int(12) NOT NULL auto_increment,
		  first_name text NOT NULL,
		  last_name text NOT NULL,
		  email_address text NOT NULL,
		  purchased_product_id int(12) NOT NULL,
	      txn_id varchar(64) NOT NULL default '',
	      date date NOT NULL default '0000-00-00',
	      sale_amount varchar(10) NOT NULL default '',      
	      coupon_code_used varchar(64) NOT NULL default '',
	      member_username varchar(32) NOT NULL,
	      product_name varchar(255) NOT NULL,
		  address text NOT NULL,	  
		  phone varchar(32) NOT NULL,
		  subscr_id varchar(64) DEFAULT '',
		  purchase_qty varchar(64) NOT NULL,	
		  ipaddress varchar(50) default 'No information',
		  status varchar(50) default 'Paid',
		  serial_number text NOT NULL,
		  notes text NOT NULL,  
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$customer_table_name);
		// Add default options
		update_option("wp_eStore_db2_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db3 = get_option( "wp_eStore_db3_version" );
	if( $installed_ver_db3 != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $coupon_table_name . " (
		  id int(12) NOT NULL auto_increment,
		  coupon_code text NOT NULL,
		  discount_value varchar(128) NOT NULL,
		  discount_type text NOT NULL,
		  active text NOT NULL,
		  redemption_limit varchar(12) NOT NULL,
		  redemption_count varchar(12) NOT NULL,
		  property varchar(8) NOT NULL,
		  logic varchar(8) NOT NULL,
		  value varchar(128) NOT NULL,
		  expiry_date date NOT NULL default '0000-00-00',
		  dynamic varchar(32) NOT NULL,
		  start_date date NOT NULL default '0000-00-00',
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$coupon_table_name);
		// Add default options
		update_option("wp_eStore_db3_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db4 = get_option( "wp_eStore_db4_version" );
	if( $installed_ver_db4 != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $sales_table_name . " (
	    cust_email varchar(128) NOT NULL default '',
	    date date NOT NULL default '0000-00-00',
	    time time NOT NULL default '00:00:00',
	    item_id varchar(10) NOT NULL default '',
	    sale_price varchar(10) NOT NULL default ''
		);";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$sales_table_name);
	
	// Add default options
	update_option("wp_eStore_db4_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db5 = get_option( "wp_eStore_db5_version" );
	if( $installed_ver_db5 != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $cat_prod_rel_table_name . " (
	    cat_id int(12) NOT NULL,
	    prod_id int(12) NOT NULL,
	    PRIMARY KEY  (cat_id, prod_id)
		);";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$cat_prod_rel_table_name);
	// Add default options
	update_option("wp_eStore_db5_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db6 = get_option( "wp_eStore_db6_version" );
	if( $installed_ver_db6 != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $cat_table_name . " (
	    cat_id int(12) NOT NULL auto_increment,
	    cat_name varchar(64) NOT NULL,
	    cat_desc text NOT NULL,
	    cat_parent int(12) NOT NULL,
	    cat_image varchar(255) NOT NULL,
	    cat_url varchar(255) NOT NULL,
	    PRIMARY KEY  (cat_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$cat_table_name);
	// Add default options
	update_option("wp_eStore_db6_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db7 = get_option( "wp_eStore_db7_version" );
	if( $installed_ver_db7 != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $pending_payment_table_name . " (
		customer_id varchar(64) NOT NULL,
		item_number int(12) NOT NULL,
		name varchar(255) NOT NULL,
		price varchar(128) NOT NULL,
		quantity int(12) NOT NULL,
		shipping varchar(128) NOT NULL,
		custom varchar(255) NOT NULL,
		total_shipping varchar(128) NOT NULL,
		total_tax varchar(128) NOT NULL,
		subtotal varchar(128) NOT NULL
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$pending_payment_table_name);
	// Add default options
	update_option("wp_eStore_db7_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db8 = get_option( "wp_eStore_db8_version" );
	if( $installed_ver_db8 != $wp_eStore_db_version )
	{
	$sql = "CREATE TABLE " . $download_links_table_name . " (
		  id bigint(20) unsigned NOT NULL auto_increment,
		  creation_time datetime NOT NULL default '0000-00-00 00:00:00',
		  download_key varchar(255) NOT NULL default '',
		  download_item text NOT NULL,
		  download_limit_count int(12) NOT NULL default '3',
	      download_limit_time int(12) NOT NULL default '48',
	      download_limit_ip varchar(15) NOT NULL default '0.0.0.0',
	      access_count int(12) NOT NULL default '0',
	      txn_id varchar(64) NOT NULL default '',
              user_id varchar(128) NOT NULL default '',
		  PRIMARY KEY  (id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	//dbDelta($sql);
	eStore_custom_dbDelta($sql,true,$download_links_table_name);
	
	// Add default options
	update_option("wp_eStore_db8_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db9 = get_option( "wp_eStore_db9_version" );
	if( $installed_ver_db9 != $wp_eStore_db_version )	
	{
		$sql = "CREATE TABLE " . $save_cart_table_name . " (
		cart_id varchar(64) NOT NULL,
		serialized_eStore_cart text NOT NULL,
                serialized_estore_customer text NOT NULL,
		KEY cart_id  (cart_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		//dbDelta($sql);
		eStore_custom_dbDelta($sql,true,$save_cart_table_name);	
		// Add default options
		update_option("wp_eStore_db9_version", $wp_eStore_db_version);
	}
	
	$installed_ver_db10 = get_option( "wp_eStore_db10_version" );
	if( $installed_ver_db10 != $wp_eStore_db_version )
	{
		$sql = "CREATE TABLE " . $product_meta_table_name . " (
		meta_id int(12) NOT NULL auto_increment,
		prod_id int(12) NOT NULL,
		meta_key varchar(255) NOT NULL,
		meta_value text NOT NULL,
		PRIMARY KEY  (meta_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		//dbDelta($sql);
		eStore_custom_dbDelta($sql,true,$product_meta_table_name);	
		// update db version
		update_option("wp_eStore_db10_version", $wp_eStore_db_version);
	}	

	$installed_ver_db11 = get_option( "wp_eStore_db11_version" );
	if( $installed_ver_db11 != $wp_eStore_db_version )
	{
		$sql = "CREATE TABLE " . $global_meta_table_name . " (
		meta_id int(12) NOT NULL auto_increment,
		tbl_reference int(12) NOT NULL,
		date_time datetime NOT NULL default '0000-00-00 00:00:00',
		meta_key1 varchar(255) NOT NULL,
		meta_key2 varchar(255) NOT NULL,
		meta_key3 varchar(255) NOT NULL,
		meta_key4 varchar(255) NOT NULL,
		meta_key5 varchar(255) NOT NULL,
		meta_value1 varchar(255) NOT NULL,
		meta_value2 varchar(255) NOT NULL,
		meta_value3 text NOT NULL,
		meta_value4 text NOT NULL,
		meta_value5 text NOT NULL,
		PRIMARY KEY  (meta_id)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		eStore_custom_dbDelta($sql,true,$global_meta_table_name);
		update_option("wp_eStore_db11_version", $wp_eStore_db_version);
	}	
	//***** End DB Installer *****/
	
	/*** Add Default Options at first time install and upgrade time ***/
	WP_eStore_Config_Helper::add_options_config_values();
}
//End of installer function

/*** Custom dbdelta fuction since the core WordPress one is messing something up ***/
function eStore_custom_dbDelta( $queries = '', $execute = true , $table_name = '') 
{
	global $wpdb;

	if ( in_array( $queries, array( '', 'all', 'blog', 'global', 'ms_global' ), true ) )
	    $queries = wp_get_db_schema( $queries );

	// Separate individual queries into an array
	if ( !is_array($queries) ) {
		$queries = explode( ';', $queries );
		if ('' == $queries[count($queries) - 1]) array_pop($queries);
	}
	$queries = apply_filters( 'dbdelta_queries', $queries );

	$cqueries = array(); // Creation Queries
	$iqueries = array(); // Insertion Queries
	$for_update = array();

	// Create a tablename index for an array ($cqueries) of queries
	foreach($queries as $qry) {
		if (preg_match("|CREATE TABLE ([^ ]*)|", $qry, $matches)) {
			$cqueries[trim( strtolower($matches[1]), '`' )] = $qry;
			$for_update[$matches[1]] = 'Created table '.$matches[1];
		} else if (preg_match("|CREATE DATABASE ([^ ]*)|", $qry, $matches)) {
			array_unshift($cqueries, $qry);
		} else if (preg_match("|INSERT INTO ([^ ]*)|", $qry, $matches)) {
			$iqueries[] = $qry;
		} else if (preg_match("|UPDATE ([^ ]*)|", $qry, $matches)) {
			$iqueries[] = $qry;
		} else {
			// Unrecognized query type
		}
	}
		
	$cqueries = apply_filters( 'dbdelta_create_queries', $cqueries );
	$iqueries = apply_filters( 'dbdelta_insert_queries', $iqueries );

	//TODO - remove this function when WordPress fixes the issue
	if(!empty($table_name)){//Apply our custom massaging as this is a custom db upgrade for a specific table
		foreach ($cqueries as $k => $v) {
		   unset ($cqueries[$k]);	
		   $new_key =  $table_name;	
		   $cqueries[$new_key] = $v;
		}	
	}
			
	$global_tables = $wpdb->tables( 'global' );
	foreach ( $cqueries as $table => $qry ) {
		// Upgrade global tables only for the main site. Don't upgrade at all if DO_NOT_UPGRADE_GLOBAL_TABLES is defined.
		if ( in_array( $table, $global_tables ) && ( !is_main_site() || defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) )
			continue;

		// Fetch the table column structure from the database
		$wpdb->suppress_errors();
		$tablefields = $wpdb->get_results("DESCRIBE {$table};");
		$wpdb->suppress_errors( false );

		if ( ! $tablefields )
			continue;

		// Clear the field and index arrays
		$cfields = $indices = array();
		// Get all of the field names in the query from between the parens
		preg_match("|\((.*)\)|ms", $qry, $match2);
		$qryline = trim($match2[1]);

		// Separate field lines into an array
		$flds = explode("\n", $qryline);

		//echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($cqueries, true)."</pre><hr/>";

		// For every field line specified in the query
		foreach ($flds as $fld) {
			// Extract the field name
			preg_match("|^([^ ]*)|", trim($fld), $fvals);
			$fieldname = trim( $fvals[1], '`' );

			// Verify the found field name
			$validfield = true;
			switch (strtolower($fieldname)) {
			case '':
			case 'primary':
			case 'index':
			case 'fulltext':
			case 'unique':
			case 'key':
				$validfield = false;
				$indices[] = trim(trim($fld), ", \n");
				break;
			}
			$fld = trim($fld);

			// If it's a valid field, add it to the field array
			if ($validfield) {
				$cfields[strtolower($fieldname)] = trim($fld, ", \n");
			}
		}

		// For every field in the table
		foreach ($tablefields as $tablefield) {
			// If the table field exists in the field array...
			if (array_key_exists(strtolower($tablefield->Field), $cfields)) {
				// Get the field type from the query
				preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", $cfields[strtolower($tablefield->Field)], $matches);
				$fieldtype = $matches[1];

				// Is actual field type different from the field type in query?
				if ($tablefield->Type != $fieldtype) {
					// Add a query to change the column type
					$cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " . $cfields[strtolower($tablefield->Field)];
					$for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
				}

				// Get the default value from the array
					//echo "{$cfields[strtolower($tablefield->Field)]}<br>";
				if (preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
					$default_value = $matches[1];
					if ($tablefield->Default != $default_value) {
						// Add a query to change the column's default value
						$cqueries[] = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} SET DEFAULT '{$default_value}'";
						$for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
					}
				}

				// Remove the field from the array (so it's not added)
				unset($cfields[strtolower($tablefield->Field)]);
			} else {
				// This field exists in the table, but not in the creation queries?
			}
		}

		// For every remaining field specified for the table
		foreach ($cfields as $fieldname => $fielddef) {
			// Push a query line into $cqueries that adds the field to that table
			$cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
			$for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
		}

		// Index stuff goes here
		// Fetch the table index structure from the database
		$tableindices = $wpdb->get_results("SHOW INDEX FROM {$table};");

		if ($tableindices) {
			// Clear the index array
			unset($index_ary);

			// For every index in the table
			foreach ($tableindices as $tableindex) {
				// Add the index to the index data array
				$keyname = $tableindex->Key_name;
				$index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
				$index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
			}

			// For each actual index in the index array
			foreach ($index_ary as $index_name => $index_data) {
				// Build a create string to compare to the query
				$index_string = '';
				if ($index_name == 'PRIMARY') {
					$index_string .= 'PRIMARY ';
				} else if($index_data['unique']) {
					$index_string .= 'UNIQUE ';
				}
				$index_string .= 'KEY ';
				if ($index_name != 'PRIMARY') {
					$index_string .= $index_name;
				}
				$index_columns = '';
				// For each column in the index
				foreach ($index_data['columns'] as $column_data) {
					if ($index_columns != '') $index_columns .= ',';
					// Add the field to the column list string
					$index_columns .= $column_data['fieldname'];
					if ($column_data['subpart'] != '') {
						$index_columns .= '('.$column_data['subpart'].')';
					}
				}
				// Add the column list to the index create string
				$index_string .= ' ('.$index_columns.')';
				if (!(($aindex = array_search($index_string, $indices)) === false)) {
					unset($indices[$aindex]);
					//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br />Found index:".$index_string."</pre>\n";
				}
				//else echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br /><b>Did not find index:</b>".$index_string."<br />".print_r($indices, true)."</pre>\n";
			}
		}

		// For every remaining index specified for the table
		foreach ( (array) $indices as $index ) {
			// Push a query line into $cqueries that adds the index to that table
			$cqueries[] = "ALTER TABLE {$table} ADD $index";
			$for_update[$table.'.'.$fieldname] = 'Added index '.$table.' '.$index;
		}

		// Remove the original table creation query from processing
		unset( $cqueries[ $table ], $for_update[ $table ] );
	}

	$allqueries = array_merge($cqueries, $iqueries);
	if ($execute) {
		foreach ($allqueries as $query) {
			//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">".print_r($query, true)."</pre>\n";
			$wpdb->query($query);
		}
	}

	return $for_update;
}
