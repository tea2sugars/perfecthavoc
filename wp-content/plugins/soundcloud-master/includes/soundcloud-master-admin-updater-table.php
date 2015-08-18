<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class soundcloud_master_admin_updater_table extends WP_List_Table {
	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display() {
?>
<table class="widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th id="columnname" class="manage-column column-columnname" scope="col" width="387"><legend><h3><img src="<?php echo plugins_url('../images/techgasp-minilogo-16.png', __FILE__); ?>" style="float:left; height:16px; vertical-align:middle;" /><?php _e('&nbsp;Advanced Version Updater:', 'soundcloud_master'); ?></h3></legend></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<th class="manage-column column-columnname" scope="col" width="387"></th>
		</tr>
	</tfoot>

	<tbody>
		<tr class="alternate" valign="top">
			<td class="column-columnname" width="387">
<p>The Advanced Updater allows you to easily Update / Upgrade your Advanced plugin.</p>
<p>Check the Add-ons Page.</p>
			</td>
		</tr>
	</tbody>
</table>
<?php
	}
}