<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class soundcloud_master_admin_addons_table extends WP_List_Table {
	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display() {
	$path = ABSPATH . 'wp-content/plugins/soundcloud-master-addons/';
	if ( is_plugin_active( 'soundcloud-master-addons/soundcloud-master-addons.php' ) && file_exists($path) ) {
		$soundcloud_master_addon = "yes";
		$soundcloud_master_addon_get = '<b>All add-ons Installed</b>';
	}
	else{
		$soundcloud_master_addon = "no";
		$soundcloud_master_addon_get = '<a class="button-primary" href="http://wordpress.techgasp.com/soundcloud-master/" target="_blank" title="Get Add-ons" style="float:left;">Get Add-ons</a>';
	}
?>
<table class="widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th id="columnname" class="manage-column column-columnname" scope="col" width="300"><legend><h3><img src="<?php echo plugins_url('images/techgasp-minilogo-16.png', dirname(__FILE__)); ?>" style="float:left; height:16px; vertical-align:middle;" /><?php _e('&nbsp;Screenshot', 'soundcloud_master'); ?></h3></legend></th>
			<th id="columnname" class="manage-column column-columnname" scope="col"><h3><img src="<?php echo plugins_url('images/techgasp-minilogo-16.png', dirname(__FILE__)); ?>" style="float:left; height:16px; vertical-align:middle;" /><?php _e('&nbsp;Description', 'soundcloud_master'); ?></h3></legend></th>
			<th id="columnname" class="manage-column column-columnname" scope="col"><h3><img src="<?php echo plugins_url('images/techgasp-minilogo-16.png', dirname(__FILE__)); ?>" style="float:left; height:16px; vertical-align:middle;" /><?php _e('&nbsp;Installed', 'soundcloud_master'); ?></h3></legend></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<th class="manage-column column-columnname" scope="col" width="300"><?php echo $soundcloud_master_addon_get; ?></th>
			<th class="manage-column column-columnname" scope="col"></th>
			<th class="manage-column column-columnname" scope="col"></th>
		</tr>
	</tfoot>

	<tbody>
		<tr class="alternate">
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-widget-front-buttons.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Buttons Widget</h3><p>The perfect widget if you only want to display the Soundcloud Connect Button or the Lyrics Button. A great way to connect people to your Soundcloud profile or to display your cool lyrics page.</p><p>This widget works great when published under any of the below players. You can activate both buttons or a single one, navigate to your wordpress widgets page and start using it.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-yes.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr>
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-widget-back-dashboard.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Administrator Dashboard Widget</h3><p>Cool player widget to listen to your favourite Soundcloud Tracks while working on your Wordpress Administrator. Go to your Wordpress Dashboard page and start using it!!!</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr class="alternate">
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-widget-front-profile.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Soundcloud Profile Widget</h3><p>Fast loading widget to display the Soundcloud User Profiles. A great way to connect people to your Soundcloud Profile, show off your work, sell it and gather followers and likes.</p><p>Navigate to your wordpress widgets page and start using it.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr>
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-widget-front-basic.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Basic Player Fast Loading Widget</h3><p>The Basic Soundcloud Player Widget was specially designed for fast loading times and is perfect to display a single track. All player options are on automatic settings so it's easy and fast to deploy by any wordpress administrator.</p><p>This widget is fully <b>Mobile Responsive</b>, navigate to your wordpress widgets page and start using it.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr class="alternate">
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-widget-front-advanced.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Advanced Responsive Player Widget</h3><p>The "top of the line" Advanced Soundcloud Player Widget was specially designed to grant you all Soundcloud Player Options, you have full control over Size, Color, Auto-Play, Display Username, Display Artwork, Display Comments, Display Playcount, Display Share, Display Like, Display Download and Display Buy. All player options are customizable, still extremely easy to use.</p><p>This widget is fully <b>Mobile Responsive</b>, navigate to your wordpress widgets page and start using it.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr>
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-shortcode-admin-in.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Individual Shortcode</h3><p>Soundcloud Master uses TechGasp Wordpress Framework. The <b>Individual Shortcode</b> allows you to have a different customized Soundcloud Player in each page or post. Easy to use it can be found when you edit a page or a post under the wordpress text editor. Once you have created your shortcode, Just insert the shortcode <b>[soundcloud-master]</b> anywhere inside that text.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr class="alternate">
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-shortcode-admin-un.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Universal Shortcode</h3><p>Soundcloud Master uses TechGasp Wordpress Framework. The <b>Universal Shortcode</b> allows you to have the same Soundcloud Player across different pages or posts. Easy to use it can be found right here under the Shortcodes menu. Once you have created your shortcode, Just insert the shortcode <b>[soundcloud-master-un]</b> anywhere inside the text of your pages or posts.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr>
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-updater-admin.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Advanced Updater</h3><p>The Advanced Updater allows you to easily Update / Upgrade your Advanced Plugin. You can instantly update your plugin after we release a new version with more goodies without having to wait for the nightly native wordpress updater that runs every 24/48 hours. Get it fresh, get it fast.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
		<tr class="alternate">
			<td class="column-columnname" width="300" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-soundcloudmaster-admin-addons-support.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="300px" height="139px" style="padding:5px;"/></td>
			<td class="column-columnname"style="vertical-align:middle"><h3>Award Winning Professional Support</h3><p>Need professional help deploying this plugin? TechGasp provides 24/7 award winning professional wordpress support for all advanced version costumers and replies to support tickets usually within minutes of being received. Support Us and we will Support You.</p></td>
			<td class="column-columnname" style="vertical-align:middle"><img src="<?php echo plugins_url('images/techgasp-check-'.$soundcloud_master_addon.'.png', dirname(__FILE__)); ?>" alt="<?php echo get_option('soundcloud_master_name'); ?>" align="left" width="200px" height="121px" style="padding:5px;"/></td>
		</tr>
	</tbody>
</table>
<?php
		}
}