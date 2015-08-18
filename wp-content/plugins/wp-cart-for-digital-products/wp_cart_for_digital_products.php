<?php
/*
Plugin Name: WP eStore
Version: v7.1.3
Plugin URI: http://www.tipsandtricks-hq.com/?p=1059
Author: Tips and Tricks HQ, Ruhul Amin
Author URI: http://www.tipsandtricks-hq.com/
Description: A Robust Shopping Cart Plugin to sell digital (ebook, mp3, photos, videos, software etc.) and non digital products from your wordpress blog. The digital goods are automatically delivered to the buyer after purchase using encrypted and time limited download links.
*/

/*Copyright 2008-2014 by Tips and Tricks HQ (http://www.tipsandtricks-hq.com) All Rights Reserved*/

if (!defined('ABSPATH'))exit; //Exit if accessed directly

define('WP_ESTORE_VERSION', "7.1.3");
define('WP_ESTORE_DB_VERSION', "8.6"); //Holds the current db schema version. Only change this when schema changes.

include_once('eStore_configs.php');
$wp_eStore_config = WP_eStore_Config::getInstance();
include_once('wp_eStore1.php');

//Installer
require_once(dirname(__FILE__).'/eStore_installer.php');
function wp_eStore_install ()
{
	wp_eStore_run_activation();
}
register_activation_hook(__FILE__,'wp_eStore_install');

function wp_eStore_add_settings_link($links, $file) 
{
	if ($file == plugin_basename(__FILE__)){
		$settings_link = '<a href="admin.php?page=wp_eStore_settings">Settings</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'wp_eStore_add_settings_link', 10, 2 );

function eStore_handle_new_blog_creation($blog_id, $user_id, $domain, $path, $site_id, $meta ){
	global $wpdb; 	
	if (is_plugin_active_for_network(WP_ESTORE_FOLDER.'/wp_cart_for_digital_products.php')) {
		$old_blog = $wpdb->blogid;
		switch_to_blog($blog_id);
    	wp_eStore_run_installer();	
		switch_to_blog($old_blog);
	}	
}
add_action('wpmu_new_blog', 'eStore_handle_new_blog_creation', 10, 6);
