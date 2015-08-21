<?php
/**
Plugin Name: SoundCloud Master
Plugin URI: http://wordpress.techgasp.com/soundcloud-master/
Version: 4.4.2.0
Author: TechGasp
Author URI: http://wordpress.techgasp.com
Text Domain: soundcloud-master
Description: SoundCloud Master is a light weight and shiny clean code wordpress plugin WIDGET that you need to show off and sell your music.
License: GPL2 or later
*/
/*  Copyright 2013 TechGasp  (email : info@techgasp.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if(!class_exists('soundcloud_master')) :
///////DEFINE VERSION///////
define( 'SOUNDCLOUD_MASTER_VERSION', '4.4.2.0' );

global $soundcloud_master_version, $soundcloud_master_name;
$soundcloud_master_version = "4.4.2.0"; //for other pages
$soundcloud_master_name = "Soundcloud Master"; //pretty name
if( is_multisite() ) {
update_site_option( 'soundcloud_master_installed_version', $soundcloud_master_version );
update_site_option( 'soundcloud_master_name', $soundcloud_master_name );
}
else{
update_option( 'soundcloud_master_installed_version', $soundcloud_master_version );
update_option( 'soundcloud_master_name', $soundcloud_master_name );
}

class soundcloud_master{
public static function content_with_quote($content){
$quote = '<p>' . get_option('tsm_quote') . '</p>';
	return $content . $quote;
}
//SETTINGS LINK IN PLUGIN MANAGER
public static function soundcloud_master_links( $links, $file ) {
if ( $file == plugin_basename( dirname(__FILE__).'/soundcloud-master.php' ) ) {
		if( is_network_admin() ){
		$techgasp_plugin_url = network_admin_url( 'admin.php?page=soundcloud-master' );
		}
		else {
		$techgasp_plugin_url = admin_url( 'admin.php?page=soundcloud-master' );
		}
	$links[] = '<a href="' . $techgasp_plugin_url . '">'.__( 'Settings' ).'</a>';
	}
	return $links;
}

//END CLASS
}
add_filter('the_content', array('soundcloud_master', 'content_with_quote'));
add_filter( 'plugin_action_links', array('soundcloud_master', 'soundcloud_master_links'), 10, 2 );
endif;

// HOOK ADMIN
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin.php');
// HOOK ADMIN ADDONS
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin-addons.php');
// HOOK ADMIN WIDGETS
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin-widgets.php');
// HOOK WIDGET BUTTONS
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-widget-buttons.php');