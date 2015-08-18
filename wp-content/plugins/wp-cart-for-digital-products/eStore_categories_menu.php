<?php
function wp_eStore_manage_categories_menu()
{
	echo '<div class="wrap">';
	echo "<h2>WP eStore - Manage Categories</h2>";
	echo '<div id="poststuff"><div id="post-body">';
	echo '<p>You can categorize your products into groups and display products from a certain category to help your customers browse them easily</p>';
	global $wpdb;
	$cat_table_name = $wpdb->prefix . "wp_eStore_cat_tbl";
	$cat_prod_rel_table_name = $wpdb->prefix . "wp_eStore_cat_prod_rel_tbl";
	
	//If being edited, grab current info
	if (isset($_GET['editrecord']) && $_GET['editrecord']!='')
	{
		$theid = $_GET['editrecord'];
		$editingrecord = $wpdb->get_row("SELECT * FROM $cat_table_name WHERE cat_id = '$theid'", OBJECT);
	}	
	if (isset($_POST['Submit']))
	{
		if(!isset($_POST['editedrecord'])){$_POST['editedrecord']="";}
		$post_editedrecord = esc_sql($_POST['editedrecord']);
		$cat_name = esc_sql(stripslashes($_POST['cat_name']));
		//$tmpdescription = htmlentities(stripslashes($_POST['cat_desc']) , ENT_COMPAT);
		$tmpdescription = htmlentities(stripslashes($_POST['cat_desc']) , ENT_COMPAT, "UTF-8");
		$cat_desc = esc_sql($tmpdescription);
		$cat_parent=0;//if ($cat_parent=="") $cat_parent=0;//$wpdb->escape($_POST['cat_parent']);
		$cat_url = esc_sql($_POST['cat_url']);	
		$cat_image = esc_sql($_POST['cat_image']);		

		if ($post_editedrecord=='')
		{
			// Add the record to the DB
			$updatedb = "INSERT INTO $cat_table_name (cat_name, cat_desc, cat_parent, cat_image, cat_url) VALUES ('$cat_name', '$cat_desc',$cat_parent,'$cat_image','$cat_url')";
			$results = $wpdb->query($updatedb);
			echo '<div id="message" class="updated fade"><p>Category &quot;'.$cat_name.'&quot; created.</p></div>';
		}
		else
		{
			// Update the info
			$updatedb = "UPDATE $cat_table_name SET cat_name = '$cat_name', cat_desc = '$cat_desc', cat_parent = '$cat_parent', cat_image = '$cat_image', cat_url = '$cat_url' WHERE cat_id='$post_editedrecord'";
			$results = $wpdb->query($updatedb);
			echo '<div id="message" class="updated fade"><p>'.__('Category', 'wp_eStore').' &quot;'.$cat_name.'&quot; '.__('updated.', 'wp_eStore').'</p></div>';
		}
	}
	// Delete
	if (isset($_POST['deleterecord']))
	{
		$post_editedrecord = esc_sql($_POST['editedrecord']);
		echo '<div id="message" class="updated fade"><p>'.__('Do you really want to delete this Category? This action cannot be undone.', 'wp_eStore').' <a href="admin.php?page=wp_eStore_categories&deleterecord='.$post_editedrecord.'">'.__('Yes', 'wp_eStore').'</a> &nbsp; <a href="admin.php?page=wp_eStore_categories&editrecord='.$post_editedrecord.'">'.__('No!', 'wp_eStore').'</a></p></div>';
	}
	if (isset($_GET['deleterecord']) && $_GET['deleterecord']!='')
	{
		$therecord=$_GET['deleterecord'];
		$updatedb = "DELETE FROM $cat_table_name WHERE cat_id='$therecord'";
		$results = $wpdb->query($updatedb);

		$updatedb = "DELETE FROM $cat_prod_rel_table_name WHERE cat_id='$therecord'";
		$results = $wpdb->query($updatedb);				
		echo '<div id="message" class="updated fade"><p>'.__('Category deleted.', 'wp_eStore').'</p></div>';
	}
?>
<div class="postbox">
<h3><label for="title">Add a Category</label></h3>
<div class="inside">
	
<form method="post" action="admin.php?page=wp_eStore_categories">
<table class="form-table">

<?php if (isset($_GET['editrecord']) && $_GET['editrecord']!='') {
	echo '<input name="editedrecord" type="hidden" value="'.$_GET['editrecord'].'" />';
}else{//New record (initialize with empty data)
	$editingrecord = new stdClass();
	$editingrecord->cat_name = "";
	$editingrecord->cat_desc = "";
	$editingrecord->cat_url = "";
	$editingrecord->cat_image = "";
} 
?>

<tr valign="top">
<th scope="row"><?php _e('Category Name', 'wp_eStore'); ?></th>
<td><input name="cat_name" type="text" id="cat_name" value="<?php echo htmlspecialchars($editingrecord->cat_name); ?>" size="40" />
<br/><p class="description">Name of the Category</p></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Category Description', 'wp_eStore'); ?></th>
<td><textarea name="cat_desc" cols="40" rows="5"><?php echo $editingrecord->cat_desc; ?></textarea>
<br/><p class="description">A Short Description of the Category</p></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Category Target URL', 'wp_eStore'); ?></th>
<td><input name="cat_url" type="text" id="cat_url" value="<?php echo $editingrecord->cat_url; ?>" size="100" />
<br /><p class="description">This is the page this category will be linked to. Leave empty if you do not want to use it.</p></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Category Thumbnail Image URL', 'wp_eStore'); ?></th>
<td><input name="cat_image" type="text" id="cat_image" value="<?php echo $editingrecord->cat_image; ?>" size="100" />
<br/><p class="description">The URL of the image to be used for this category. Leave empty if you do not want to use a category thumbnail.</p></td>
</tr>

</table>
<p class="submit"><input type="submit" name="Submit" value="Save Category" /> &nbsp; <?php if (isset($_GET['editrecord']) && $_GET['editrecord']!='') { ?><input type="submit" name="deleterecord" value="Delete Category" /><?php } ?></p>
</form>
</div></div>
<?php
/*
<tr valign="top">
<th scope="row"><?php _e('Category Parent', 'wp_eStore'); ?></th>
<td><input name="cat_parent" type="text" id="cat_parent" value="<?php echo $editingrecord->cat_parent; ?>" size="20" /><br/><?php _e('Category Parent', 'wp_eStore'); ?></td>
</tr>
*/
	eStore_display_categories_menu($cat_table_name);
	
	echo '</div></div>';
	echo '</div>';
}

function eStore_display_categories_menu($cat_table_name)
{
	
	echo '
	<table class="widefat">
	<thead><tr>
	<th scope="col">Category ID</th>';
	
	echo '<th scope="col">';	
	if(isset($_GET['order']) && $_GET['order'] == 'asc'){
		echo 'Category Name <a href="admin.php?page=wp_eStore_categories&orderby=cat_name&order=desc"><img src="'.WP_ESTORE_IMAGE_URL.'/sort-desc-icon.gif" title="Sort by category name"></a>';
	}	
	else{
		echo 'Category Name <a href="admin.php?page=wp_eStore_categories&orderby=cat_name&order=asc"><img src="'.WP_ESTORE_IMAGE_URL.'/sort-asc-icon.gif" title="Sort by category name"></a>';		
	}
	echo '</th>';
	
	echo '<th scope="col">Category Description</th>
    <th scope="col"></th>
	</tr></thead>
	<tbody>';

	global $wpdb;
	if(isset($_GET['order']))
	{		
		$order_by = $_GET['orderby'];
		$order = $_GET['order'];
		$wp_eStore_db = $wpdb->get_results("SELECT * FROM $cat_table_name ORDER BY $order_by $order", OBJECT);
	}
	else{
		$wp_eStore_db = $wpdb->get_results("SELECT * FROM $cat_table_name ORDER BY cat_id DESC", OBJECT);	
	}	

	if ($wp_eStore_db)
	{
		foreach ($wp_eStore_db as $wp_eStore_db)
		{
			echo '<tr>';
			echo '<td>'.$wp_eStore_db->cat_id.'</td>';
			echo '<td>'.$wp_eStore_db->cat_name.'</td>';
			echo '<td>'.html_entity_decode($wp_eStore_db->cat_desc, ENT_COMPAT).'</td>';
			echo '<td><a href="admin.php?page=wp_eStore_categories&editrecord='.$wp_eStore_db->cat_id.'">'.__('Edit', 'wp_eStore').'</a></td>';
			echo '</tr>';
		}
	}
	else
	{
		echo '<tr> <td colspan="4">'.__('No Categories found.', 'wp_eStore').'</td> </tr>';
	}

	echo '</tbody>
	</table>';
}
?>
