<?php
/* Finding the path to the wp-admin folder */
$iswin = preg_match('/:\\\/', dirname(__file__));
$slash = ($iswin) ? "\\" : "/";

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] != "") ? $wp_path[0] : $_SERVER["DOCUMENT_ROOT"];

/** Load WordPress Administration Bootstrap */
require_once($wp_path . $slash . 'wp-load.php');
require_once($wp_path . $slash . 'wp-admin' . $slash . 'admin.php');


// check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__( "You are not allowed to be here", 'post-snippets' ));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>WP eStore Shortcodes</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
    <style type="text/css">
    .shortcode_list{
    text-align:center;
    }
    #eStore_shortcodes_slct{
    font-size:13px;
    }
    </style>	
    	
	<script language="javascript" type="text/javascript" src="<?php echo WP_ESTORE_WP_SITE_URL; ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo WP_ESTORE_WP_SITE_URL; ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo WP_ESTORE_WP_SITE_URL; ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">
	function init() {
		tinyMCEPopup.resizeToInnerSize();
	}
	
	function insert_eStore_shortcode() {

		var insertString;
		var selected_option_text = document.getElementById("eStore_shortcodes_slct").value;	
		var pos = selected_option_text.indexOf('#');
		if(pos>0)
		{
			var prod_id = prompt("WP eStore Shortcode", "Enter the ID needed for this shortcode (eg. 1)");
			selected_option_text = selected_option_text.replace("#",prod_id);
		}	
		insertString = selected_option_text;
		//alert(selected_option_text);
		//alert("test");
				
		if(window.tinyMCE) {
			//window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, insertString);
			tinyMCEPopup.execCommand("mceBeginUndoLevel");
			tinyMCEPopup.execCommand('mceInsertContent', false, insertString);
			tinyMCEPopup.execCommand("mceEndUndoLevel");
			//Peforms a clean up of the current editor HTML. 
			//tinyMCEPopup.editor.execCommand('mceCleanup');
			//Repaints the editor. Sometimes the browser has graphic glitches. 
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.close();
		}
		return;
	}
	</script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">

<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="eStore_shortcode_popup" action="#">

	<div class="shortcode_list">
		<p><strong>Please select a shortcode that you wish to use then hit the insert button</strong></p>
		
		<select name="eStore_shortcodes_slct" id="eStore_shortcodes_slct">
			<option value="[wp_eStore_add_to_cart id=#]">[wp_eStore_add_to_cart id=##]</option>
			<option value="[wp_eStore:product_id:#:end]">[wp_eStore:product_id:##:end]</option>
			<option value="[wp_eStore_fancy1 id=#]">[wp_eStore_fancy1 id=##]</option>
			<option value="[wp_eStore_fancy2 id=#]">[wp_eStore_fancy2 id=##]</option>
			<option value="[wp_eStore_cart]">[wp_eStore_cart]</option>						
			<option value="[wp_eStore_cart_when_not_empty]">[wp_eStore_cart_when_not_empty]</option>						
			<option value="[wp_eStore_cart_fancy1]">[wp_eStore_cart_fancy1]</option>						
			<option value="[wp_eStore_cart_fancy1_when_not_empty]">[wp_eStore_cart_fancy1_when_not_empty]</option>						
			<option value="[wp_eStore_buy_now_button id=#]">[wp_eStore_buy_now_button id=##]</option>						
			<option value="[wp_eStore_buy_now_fancy id=#]">[wp_eStore_buy_now_fancy id=##]</option>						
			<option value="[wp_eStore_subscribe:product_id:#:end]">[wp_eStore_subscribe:product_id:##:end]</option>						
			<option value="[wp_eStore_subscribe_fancy id=#]">[wp_eStore_subscribe_fancy id=##]</option>						
			<option value="[wp_eStore_free_download_squeeze_form id=#]">[wp_eStore_free_download_squeeze_form id=##]</option>	
			<option value="[wp_eStore_free_download_ajax:product_id:#:end]">[wp_eStore_free_download_ajax:product_id:##:end]</option>								
			<option value="[wp_eStore_category_products:category_id:#:end]">[wp_eStore_category_products:category_id:##:end]</option>						
			<option value="[wp_eStore_category_fancy id=#]">[wp_eStore_category_fancy id=##]</option>						
			<option value="[wp_eStore_list_categories_fancy]">[wp_eStore_list_categories_fancy]</option>						
			<option value="[wp_eStore_all_products_stylish:end]">[wp_eStore_all_products_stylish:end]</option>						
			<option value="[wp_eStore_list_products]">[wp_eStore_list_products]</option>						
			<option value="[wp_eStore_sale_counter id=#]">[wp_eStore_sale_counter id=##]</option>						
			<option value="[wp_eStore_remaining_copies_counter id=#]">[wp_eStore_remaining_copies_counter id=##]</option>						
			<option value="[wp_eStore_download_now_button id=#]">[wp_eStore_download_now_button id=##]</option>						
			<option value="[wp_eStore_download_now_button_fancy id=#]">[wp_eStore_download_now_button_fancy id=##]</option>						
		</select>
		
		<div class="mceActionPanel">
				<input type="submit" class="button button-primary" id="estore_sc_insert" name="insert" value="Insert" onclick="insert_eStore_shortcode();" />
				<input type="button" class="button" id="estore_sc_cancel" name="cancel" value="Cancel" onclick="tinyMCEPopup.close();" />			
		</div>
		
	</div>

	<br /><i>The above is a list of only the most used shortcodes. A full list of Shortcodes and their descriptions can be found on the documentation site <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=460" target="_blank"><strong>here</strong></a></i>
	
</form>

</body>
</html>