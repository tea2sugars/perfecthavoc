jQuery(document).ready(function() {
	function eStore_attach_media_uploader(key) {
		jQuery('#' + key + '_button').click(function() {
			if(key == 'thumbnail_url'){//Use an alternate send to editor for this type
				window.send_to_editor = SendToEditorForImage;
			}
			text_element = jQuery('#' + key).attr('name');
			button_element = jQuery('#' + key + '_button').attr('name');
			tb_show('', 'media-upload.php?TB_iframe=1&width=640&height=485');
			return false;
		});
		window.send_to_editor = function(html) {
			var self_element = text_element;
			imgurl = jQuery(html).attr('href');
			jQuery('#' + self_element).val(imgurl);
			tb_remove();
		};
		storeSendToEditor = window.send_to_editor;
		
		SendToEditorForImage = function(html) {
			var img_element = jQuery(html).find('img');//Get the img element
			var selected_img_src = img_element.attr('src');//selected img URL (small, medium, large etc)
			imgurl = jQuery(html).attr('href');//Full image URL
			var self_element = text_element;		
			jQuery('#' + self_element).val(selected_img_src);//Set to the selected image
			tb_remove();
			window.send_to_editor = storeSendToEditor;//Set it back to the default one
		};
	}
	
	eStore_attach_media_uploader('producturl');
	eStore_attach_media_uploader('thumbnail_url');
	eStore_attach_media_uploader('buttonimageurl');	
});