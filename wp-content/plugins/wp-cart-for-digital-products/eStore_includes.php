<?php
//TODO - compile the must load ones in one big blob and load in one file. The following should be compiled together
//eStore_load_t_and_c_jquery
//eStore_load_area_specific_tax_jquery - check WP_ESTORE_APPLY_TAX_FOR_CERTAIN_AREA
//eStore_load_shipping_var_change_warning_jquery
//eStore_load_cart_qty_change_jquery
//eStore_load_checkout_method_jquery - if (get_option('eStore_use_multiple_gateways'))

function eStore_load_save_retrieve_cart_jquery()
{
?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
$(function() {	
	var process_script_url = '<?php echo WP_ESTORE_URL; ?>' + '/ajax_process_cart_requests.php';
	var save_ret_cart_msg = $('div.eStore_save_retrieve_cart_message');
	
	$('.eStore_save_cart_section').on("click", function (){
		save_ret_cart_msg.html("").append("<?php echo ESTORE_PROCESSING_REQUEST; ?>");		
		var dataString = 'eStore_cart_action=eStore_save_cart';		
		$.ajax({
			type: "POST",
			url: process_script_url,
			data: dataString,
			dataType: "json",
			success: function(data) {				
				//var dataStr = JSON.stringify(data); console.log(dataStr);
				if(data.status == "success"){
					save_ret_cart_msg.html("").append('<?php echo ESTORE_SHOPPING_CART_SAVED . ESTORE_SHOPPING_CART_ID; ?> : ' + data.ID);
				}
				else{
					save_ret_cart_msg.html("").append('<span class="eStore_warning">' + data.details + '</span>');
				}
			}
		});				
		return false;
	});

	$('.eStore_retrieve_cart_section').on("click", function (){
		var cart_id = prompt("<?php echo ESTORE_SHOPPING_CART_ID; ?>", "");	
		if(cart_id == null){
			return false;
		}
		else if(cart_id ==""){
			save_ret_cart_msg.html("").append('<span class="eStore_warning"><?php echo ESTORE_WRONG_SHOPPING_CART_ID; ?></span>');
			return false;
		}
		cart_id = $.trim(cart_id);
		save_ret_cart_msg.html("").append("<?php echo ESTORE_PROCESSING_REQUEST; ?>");
		var dataString = 'eStore_cart_action=eStore_retrieve_cart&cart_id=' + cart_id;
		$.ajax({
			type: "POST",
			url: process_script_url,
			data: dataString,
			dataType: "json",
			success: function(data) {
				if(data.status == "success"){	
					save_ret_cart_msg.html("").append('<?php echo ESTORE_SUCCESS; ?>');	
					target_url = window.location.href.split("#")[0];
					window.location.href = target_url;					
				}
				else{
					save_ret_cart_msg.html("").append('<span class="eStore_warning"><?php echo ESTORE_WRONG_SHOPPING_CART_ID; ?></span>');	
				}	
			}
		});		
		return false;
	});
});
});
</script>
<?php
}

function eStore_load_free_download_ajax()
{
if(is_admin())return;	
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
$(function() {
  $('.error').hide();
  $(".button").click(function() {
	if($("input#ajax_force_t_c").val() == "1") {
	// Process Terms & Conditions checkbox...
		if(!$(".t-and-c").is(':checked')) {
			$('.t_and_c_error').show();
			return false;
		}
	}
		// validate and process form
		// first hide any error messages
    $('.error').hide();
		
	  var name = $("input#name").val();
		if (name == "") {
      $("label#name_error").show();
      $("input#name").focus();
      return false;
    }
		var email = $("input#eStore_ajax_email").val();
		if (email == "") {
      $("label#email_error").show();
      $("input#eStore_ajax_email").focus();
      return false;
    }
		var prod_id = $("input#free_download_product_id").val();
		var ap_id = $("input#free_download_ap_id").val();
		var clientip = $("input#free_download_clientip").val();
		
		var dataString = 'name='+ name + '&email=' + email + '&prod_id=' + prod_id + '&ap_id=' + ap_id + '&clientip=' + clientip;
		//alert (dataString);return false;
        var process_script_url = '<?php echo WP_ESTORE_URL; ?>' + '/ajax_process_download.php';

	// Because the server might be invoking the PDF Stamper, we want the user to have a "warm fuzzy feeling" while they
	// wait for a response.  Otherwise, they might get "click happy" with the submit button.
	// -- The Assurer, 2010-09-14.
	{
		$('.free_download_form').html("<div class='message'></div>");
		$('.message').html("")
		.append("<?php echo WP_ESTORE_PROCESSING_ORDER; ?>")
		.hide()
		.fadeIn(250, function() {
			$('.message').append("<img id='loader1' src='<?php echo WP_ESTORE_URL; ?>/images/ajax-loader1.gif' />");
		});
	}

	$.ajax({
      type: "POST",
      url: process_script_url,
      data: dataString,
      success: function() {
        $('.free_download_form').html("<div class='message'></div>");
        $('.message').html("")
        .append("<?php echo WP_ESTORE_EMAIL_SENT; ?>");
      }
	});
    return false;
	});
});
});
</script>
<?php
}
function eStore_load_t_and_c_jquery()
{
if(is_admin())return;	
?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
$(function() {
	if ($('.t-and-c').length ) {
	    //Terms and condtions is being used so apply validation
	}else{
		return;
	}
	$('.t_and_c_error').hide();
   $(".t-and-c").click(function(){
       if($(".t-and-c").is(':checked')){  
    	  $.cookie("eStore_submit_payment","true",{path: '/'}); 
          $('.t_and_c_error').hide();          
       }
       else{
    	   $.cookie("eStore_submit_payment","false",{path: '/'});	
       }                 
   });    
   $(".eStore_paypal_checkout_button").click(function(e){
       if(!$(".t-and-c").is(':checked')){
           $('.t_and_c_error').show();
           e.preventDefault();         
       }
   });   
   $(".eStore_buy_now_button").click(function(e){
       if(!$(".t-and-c").is(':checked')){
           $('.t_and_c_error').show();
           e.preventDefault();         
       }
   });   
   $(".eStore_subscribe_button").click(function(e){
       if(!$(".t-and-c").is(':checked')){
           $('.t_and_c_error').show();
           e.preventDefault();         
       }
   }); 
	$(".free_download_submit").click(function(e) {//Non-Ajax free download squeeze page button clicked	
		if(!$(".t-and-c").is(':checked')){
			$('.t_and_c_error').show();
			e.preventDefault();
		}
	});   
 });
 });
</script>
<?php
}
function eStore_load_area_specific_tax_jquery()
{
?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
$(function() {		
	$('.eStore_area_tax_submit').hide();
	var area_tax_chkbox_class = $('.eStore_area_tax_chkbox');
	area_tax_chkbox_class.on("click",function(event){
		if(area_tax_chkbox_class.is(':checked')){
			$('.eStore_area_tax_form').submit();
		}
	});
 });
 });
</script>
<?php
}
function eStore_load_store_pickup_jquery()
{
?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
$(function() {
        $('.eStore_store_pickup_submit_btn').hide();
	var store_pickup_chkbx = $('.eStore_store_pickup_chkbox');
	store_pickup_chkbx.on("click",function(event){
		if(store_pickup_chkbx.is(':checked')){
			$('.eStore_store_pickup_form').submit();
		}
	});
 });
 });
</script>
<?php
}
function eStore_load_shipping_var_change_warning_jquery()
{
?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
$(function() {
	var shipping_var_warning_class = $('.shipping_var_changed');
	var shipping_var_warning_default_class = $('.shipping_var_changed_default');
	shipping_var_warning_class.hide();
	$('.shipping_variation').change(function(){
		shipping_var_warning_default_class.hide();
		shipping_var_warning_class.show();
	});
	var eStore_shipping_var_needs_update = false;
	if(shipping_var_warning_class.is(":visible")){eStore_shipping_var_needs_update = true;}
	else if(shipping_var_warning_default_class.is(":visible")){eStore_shipping_var_needs_update = true;}	
	$(".eStore_paypal_checkout_button").click(function(e){//Check if shipping variation has been selected
		if(eStore_shipping_var_needs_update){
	    	shipping_var_warning_class.css({'border':'1px solid red','padding':'5px'});
	    	shipping_var_warning_default_class.css({'border':'1px solid red','padding':'5px'});
	    	e.preventDefault();
		}
	});	
 });
 });
</script>
<?php
}
function eStore_load_cart_qty_change_jquery()
{
?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
$(function(){
	var eStore_cart_item_qty = $('.eStore_cart_item_qty');
	eStore_cart_item_qty.keypress(function(){
		$('.eStore_qty_change_pinfo').show();
	});
 });
 });
</script>
<?php
}

function eStore_load_fancy_overlay_jquery2()
{		
?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
$(function() {
	//set this cookie value according to the T and C settings.
	if ($(".t-and-c").length > 0){	
		if(!$(".t-and-c").is(':checked')){
			$.cookie("eStore_submit_payment","false",{path: '/'});						
		}
	}
	else{
		$.cookie("eStore_submit_payment","true",{path: '/'});
	}	
	$(".redirect_trigger").overlay({
		mask: '#3D4752',
		effect: 'apple',
		target:$('.eStore_apple_overlay'),
		top: '20%',		
 
		onBeforeLoad: function() {
			// grab wrapper element inside content
			var wrap = this.getOverlay().find(".cart_redirection_contentWrap"); 			
			// load the page specified in the trigger
			wrap.load(this.getTrigger().attr("href"));	

			if ($(".t-and-c").length > 0){	
				if(!$(".t-and-c").is(':checked'))
				{
					$.cookie("eStore_submit_payment","false",{path: '/'});	
					wrap.close();						
				}
			}	 
		} 
	}); 
 });
 });
</script>
<?php
}

function eStore_load_lightbox()
{
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $(function() {
    	$('[class*=eStore]').find('a[rel*=lightbox]').lightBox({
    	imageLoading: '<?php echo WP_ESTORE_IMAGE_URL.'/lightbox/lightbox-ico-loading.gif'; ?>',
    	imageBtnClose: '<?php echo WP_ESTORE_IMAGE_URL.'/lightbox/lightbox-btn-close.gif'; ?>',
    	imageBtnPrev: '<?php echo WP_ESTORE_IMAGE_URL.'/lightbox/lightbox-btn-prev.gif'; ?>',
    	imageBtnNext: '<?php echo WP_ESTORE_IMAGE_URL.'/lightbox/lightbox-btn-next.gif'; ?>',
    	imageBlank: '<?php echo WP_ESTORE_IMAGE_URL.'/lightbox/lightbox-blank.gif'; ?>',
    	txtImage: 'Image',
    	txtOf: 'of'
        });
    });    
});
</script>
<?php
}
?>